<?php
if(!defined('ABSPATH'))exit;
function sk_types(){return ['pt'=>sk_opt('ty_pt'),'hall'=>sk_opt('ty_hall'),'membership'=>sk_opt('ty_membership')];}
function sk_form_types(){return ['pt'=>sk_opt('ty_pt'),'hall'=>sk_opt('ty_hall')];}
function sk_slotkey($ref,$date){return (int)$ref.'|'.$date;}
// Aynı saate eşzamanlı iki rezervasyonu engelleyen kısa kilit
function sk_lock($key){$o='sk_lk_'.md5($key);for($i=0;$i<25;$i++){if(add_option($o,time(),'','no'))return $o;$t=(int)get_option($o);if($t&&time()-$t>15)delete_option($o);usleep(100000);}return false;}
function sk_statuses(){return ['pending'=>'Beklemede','approved'=>'Onaylandı','rejected'=>'Reddedildi','cancelled'=>'İptal'];}
function sk_min($t){$p=explode(':',$t.':0');return intval($p[0])*60+intval($p[1]);}
function sk_hm($m){return sprintf('%02d:%02d',intdiv($m,60),$m%60);}

function sk_busy($ref,$date){
 $out=[];$ids=get_posts(['post_type'=>'booking','numberposts'=>-1,'post_status'=>'any','fields'=>'ids','no_found_rows'=>true,'meta_query'=>[['key'=>'slotkey','value'=>sk_slotkey($ref,$date)],['key'=>'status','value'=>['pending','approved'],'compare'=>'IN']]]);
 if($ids)update_meta_cache('post',$ids);
 foreach($ids as $b){$s=sk_min(get_post_meta($b,'start',true));$e=get_post_meta($b,'end',true);$out[]=[$s,$e?sk_min($e):$s+(int)sk_opt('slot_min')];}
 return $out;
}
function sk_slots($kind,$ref,$date){
 $d=DateTime::createFromFormat('!Y-m-d',$date);if(!$d||$d->format('Y-m-d')!==$date)return [];
 $today=new DateTime(current_time('Y-m-d'));if($d<$today||$d>(clone $today)->modify('+'.max(1,(int)sk_opt('max_days')).' days'))return [];
 if($kind==='pt'){if(get_post_type($ref)!=='trainer')return [];$a=sk_meta($ref,'avail');$r=$a[(int)$d->format('N')]??null;if(!$r||empty($r['on']))return [];
  $from=sk_min($r['from']);$to=sk_min($r['to']);$step=max(15,(int)sk_opt('slot_min'));}
 elseif($kind==='hall'){if(get_post_type($ref)!=='hall')return [];$from=(int)sk_opt('open_hour')*60;$to=(int)sk_opt('close_hour')*60;$step=60;}
 else return [];
 $busy=sk_busy($ref,$date);$now=($d==$today)?sk_min(current_time('H:i')):-1;$o=[];
 for($m=$from;$m+$step<=$to;$m+=$step){$free=$m>$now;foreach($busy as $b){if($m<$b[1]&&$m+$step>$b[0])$free=false;}$o[]=['t'=>sk_hm($m),'free'=>$free];}
 return $o;
}
add_action('wp_ajax_sk_slots','sk_ajax_slots');add_action('wp_ajax_nopriv_sk_slots','sk_ajax_slots');
function sk_ajax_slots(){ wp_send_json_success(sk_slots(sanitize_key($_POST['kind']??''),absint($_POST['ref_id']??0),sanitize_text_field($_POST['date']??'')));}

add_action('wp_ajax_sk_book','sk_ajax_book');add_action('wp_ajax_nopriv_sk_book','sk_ajax_book');
function sk_ajax_book(){
 if(!empty($_POST['website']))wp_send_json_success(['msg'=>'Teşekkürler.']);
 $ip='sk_rl_'.md5($_SERVER['REMOTE_ADDR']??'x');$n=(int)get_transient($ip);if($n>=8)wp_send_json_error(['msg'=>'Çok fazla deneme. Lütfen daha sonra tekrar deneyin.']);set_transient($ip,$n+1,HOUR_IN_SECONDS);
 $type=sanitize_key($_POST['type']??'');$ref=absint($_POST['ref_id']??0);
 $name=sanitize_text_field($_POST['name']??'');$phone=sanitize_text_field($_POST['phone']??'');$email=sanitize_email($_POST['email']??'');$note=sanitize_textarea_field($_POST['note']??'');
 $err=fn($m)=>wp_send_json_error(['msg'=>$m]);
 if(!isset(sk_form_types()[$type]))$err('Geçersiz işlem türü.');
 if($name===''||strlen(preg_replace('/\D/','',$phone))<10)$err(sk_opt('m_err_req'));
 if(!empty($_POST['email'])&&!$email)$err('E-posta adresi geçersiz.');
 $want=['pt'=>'trainer','hall'=>'hall'][$type];
 if(!$ref||get_post_type($ref)!==$want||get_post_status($ref)!=='publish')$err('Lütfen listeden seçim yapın.');
 $lock=null;$date=$start=$end='';$total='';$referee=0;$extra='';
 if(in_array($type,['pt','hall'],true)){
  $date=sanitize_text_field($_POST['date']??'');$start=sanitize_text_field($_POST['start']??'');
   $lock=sk_lock($ref.'|'.$date);if(!$lock)$err('Sistem yoğun, lütfen birkaç saniye sonra tekrar deneyin.');register_shutdown_function(function()use($lock){delete_option($lock);});
  $slots=sk_slots($type,$ref,$date);$free=[];foreach($slots as $s)$free[$s['t']]=$s['free'];
  if(!isset($free[$start])||!$free[$start])$err(sk_opt('m_err_slot'));
  if($type==='pt'){
   $step=max(15,(int)sk_opt('slot_min'));$end=sk_hm(sk_min($start)+$step);
   $pk=absint($_POST['package_id']??0);
   if($pk&&get_post_type($pk)==='pt_package'){$extra='Paket: '.get_the_title($pk);$total=sk_meta($pk,'price');}else{$total=sk_meta($ref,'price_ind');$extra='Tek seans';}
  }else{
   $dur=min(6,max(1,absint($_POST['duration']??1)));$e=sk_min($start)+$dur*60;$end=sk_hm($e);
   for($m=sk_min($start);$m<$e;$m+=60){$k=sk_hm($m);if(!isset($free[$k])||!$free[$k])$err('Seçilen süre boyunca salon müsait değil. Süreyi kısaltın veya başka saat seçin.');}
   $referee=(!empty($_POST['referee'])&&sk_meta($ref,'referee')==='1')?1:0;
   $total=(float)sk_meta($ref,'price')*$dur+($referee?(float)sk_opt('ref_fee'):0);
   $extra=$dur.' saat'.($referee?' + hakem':'');
  }
 }else{$total=sk_meta($ref,'price');$extra=get_the_title($ref);}
 $status=sk_opt('auto_approve')==='1'?'approved':'pending';
 $id=wp_insert_post(['post_type'=>'booking','post_status'=>'publish','post_title'=>sk_types()[$type].' - '.$name,'meta_input'=>['type'=>$type,'ref_id'=>$ref,'ref_title'=>get_the_title($ref),'date'=>$date,'start'=>$start,'end'=>$end,'name'=>$name,'phone'=>$phone,'email'=>$email,'note'=>$note,'referee'=>$referee,'total'=>$total,'extra'=>$extra,'status'=>$status,'slotkey'=>$date?sk_slotkey($ref,$date):'']]);
 if($lock)delete_option($lock);
  if(!$id)$err('Kayıt oluşturulamadı. Lütfen tekrar deneyin.');
 $to=sk_opt('booking_email')?:get_option('admin_email');
 wp_mail($to,'Yeni '.sk_types()[$type].': '.$name,"Tür: ".sk_types()[$type]."\nKonu: ".get_the_title($ref)."\nTarih/Saat: $date $start-$end\nDetay: $extra\nAd: $name\nTelefon: $phone\nE-posta: $email\nNot: $note\n\nYönet: ".admin_url('post.php?post='.$id.'&action=edit'));
 if($email)sk_mail_customer($id);
 sk_sync_income($id);
 wp_send_json_success(['msg'=>$status==='approved'?sk_opt('m_ok_approved'):sk_opt('m_ok_pending')]);
}
function sk_mail_customer($id){
 $e=get_post_meta($id,'email',true);if(!$e)return;$s=get_post_meta($id,'status',true);$l=sk_statuses()[$s]??$s;
 wp_mail($e,get_bloginfo('name').' - Rezervasyon durumunuz: '.$l,'Merhaba '.get_post_meta($id,'name',true).",\n\n".get_post_meta($id,'ref_title',true).' için '.trim(get_post_meta($id,'date',true).' '.get_post_meta($id,'start',true))." talebinizin durumu: $l\n\n".get_bloginfo('name'));
}

function sk_booking_form($a=[]){
 $a=wp_parse_args($a,['type'=>'','ref'=>0,'lock'=>false]);
 $type=$a['type']?:sanitize_key($_GET['type']??'pt');if(!isset(sk_form_types()[$type]))$type='pt';
 $ref=$a['ref']?:absint($_GET['ref']??0);$map=['pt'=>'trainer','hall'=>'hall'];
 $pk=get_posts(['post_type'=>'pt_package','numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC']);
 ob_start();?>
<form class="sk-form" data-type="<?php echo esc_attr($type);?>" novalidate>
 <h3><?php echo $a['lock']?esc_html(sk_types()[$type]):sk_x('f_title');?></h3>
 <?php if(!$a['lock']):?><div class="sk-tabs"><?php foreach(sk_form_types() as $k=>$l):?><button type="button" class="sk-tab" data-type="<?php echo esc_attr($k);?>"><?php echo esc_html($l);?></button><?php endforeach;?></div><?php endif;?>
 <input type="hidden" name="type" value="<?php echo esc_attr($type);?>">
 <?php if($a['lock']):?><input type="hidden" name="ref_id" value="<?php echo (int)$ref;?>">
 <?php else:?><div class="f"><label><?php echo sk_x('f_choice');?></label><select name="ref_id" required><option value=""><?php echo sk_x('f_choose');?></option>
 <?php foreach($map as $k=>$pt):foreach(get_posts(['post_type'=>$pt,'numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC']) as $p):
  $x=$k==='hall'?' - '.sk_money(sk_meta($p->ID,'price')).'/'.sk_t('t_hour'):'';?>
  <option value="<?php echo (int)$p->ID;?>" data-type="<?php echo esc_attr($k);?>" <?php selected($ref,$p->ID);?>><?php echo esc_html($p->post_title.$x);?></option><?php endforeach;endforeach;?></select></div><?php endif;?>
 <?php if($pk):?><div class="f" data-for="pt"><label><?php echo sk_x('f_package');?></label><select name="package_id"><option value="0"><?php echo sk_x('f_single');?></option><?php foreach($pk as $p):?><option value="<?php echo (int)$p->ID;?>"><?php echo esc_html($p->post_title.' - '.sk_money(sk_meta($p->ID,'price')));?></option><?php endforeach;?></select></div><?php endif;?>
 <div class="f" data-for="pt hall"><label><?php echo sk_x('f_date');?></label><input type="date" name="date" min="<?php echo esc_attr(current_time('Y-m-d'));?>" max="<?php echo esc_attr(date('Y-m-d',strtotime(current_time('Y-m-d').' +'.(int)sk_opt('max_days').' days')));?>" required></div>
 <div class="f" data-for="pt hall"><label><?php echo sk_x('f_time');?></label><div class="sk-slots"><p class="sk-muted"><?php echo sk_x('m_hint');?></p></div><input type="hidden" name="start"></div>
 <div class="f" data-for="hall"><label><?php echo sk_x('f_duration');?></label><select name="duration"><?php for($i=1;$i<=6;$i++):?><option value="<?php echo $i;?>"><?php echo $i.' '.sk_x('f_hours');?></option><?php endfor;?></select></div>
 <div class="f chk" data-for="hall"><label class="chk"><input type="checkbox" name="referee" value="1"> <?php echo sk_x('f_referee');?><?php echo (float)sk_opt('ref_fee')>0?' (+'.esc_html(sk_money(sk_opt('ref_fee'))).')':'';?></label></div>
 <div class="f"><label><?php echo sk_x('f_name');?></label><input name="name" required autocomplete="name"></div>
 <div class="f"><label><?php echo sk_x('f_phone');?></label><input name="phone" type="tel" required autocomplete="tel"></div>
 <div class="f"><label><?php echo sk_x('f_email');?></label><input name="email" type="email" autocomplete="email"></div>
 <div class="f"><label><?php echo sk_x('f_note');?></label><textarea name="note" rows="2"></textarea></div>
 <input class="sk-hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
 <button class="sk-btn" type="submit"><?php echo sk_x('f_submit');?></button>
 <div class="sk-msg" role="status"></div>
 <?php if(sk_opt('terms_note')):?><p class="sk-muted" style="font-size:.8rem;margin:1em 0 0"><?php echo sk_x('terms_note');?></p><?php endif;?>
</form><?php return ob_get_clean();
}
add_shortcode('sk_booking',function($a){$a=(array)$a;$f=sk_booking_form($a);if(!empty($a['type'])||!empty($a['ref']))return $f;
 return '<div class="sk-resv"><div>'.$f.'</div><aside>'.do_shortcode('[sk_uye_ol compact="1"]').'</aside></div>';});

/* ---- Yönetim: liste, durum, e-posta ---- */
add_filter('manage_booking_posts_columns',fn()=>['cb'=>'<input type="checkbox">','title'=>'Rezervasyon','bk_ref'=>'Konu','bk_when'=>'Tarih / Saat','bk_who'=>'Kişi','bk_total'=>'Tutar','bk_status'=>'Durum','date'=>'Oluşturma']);
add_action('manage_booking_posts_custom_column',function($c,$id){
 $m=fn($k)=>get_post_meta($id,$k,true);
 switch($c){case 'bk_ref':echo esc_html($m('ref_title').' '.$m('extra'));break;
  case 'bk_when':echo esc_html(trim($m('date').' '.$m('start').($m('end')?'-'.$m('end'):'')))?:'—';break;
  case 'bk_who':echo esc_html($m('name')).'<br>'.esc_html($m('phone')).'<br>'.esc_html($m('email'));break;
  case 'bk_total':echo esc_html(sk_money($m('total')))?:'—';break;
  case 'bk_status':$s=$m('status');echo '<strong style="color:'.['pending'=>'#b45309','approved'=>'#15803d','rejected'=>'#b91c1c','cancelled'=>'#6b7280'][$s].'">'.esc_html(sk_statuses()[$s]??$s).'</strong>';}
},10,2);
add_filter('post_row_actions',function($a,$p){if($p->post_type!=='booking')return $a;unset($a['inline hide-if-no-js'],$a['view']);
 foreach(['approved'=>'Onayla','rejected'=>'Reddet'] as $s=>$l)$a['sk_'.$s]='<a href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=sk_status&id='.$p->ID.'&s='.$s),'sk_status_'.$p->ID)).'">'.$l.'</a>';return $a;},10,2);
add_action('admin_post_sk_status',function(){$id=absint($_GET['id']??0);$s=sanitize_key($_GET['s']??'');
 if(!$id||!isset(sk_statuses()[$s])||!current_user_can('edit_post',$id)||!wp_verify_nonce($_GET['_wpnonce']??'','sk_status_'.$id))wp_die('Yetkisiz işlem.');
 sk_set_status($id,$s);wp_safe_redirect(admin_url('edit.php?post_type=booking'));exit;});
function sk_set_status($id,$s){$old=get_post_meta($id,'status',true);update_post_meta($id,'status',$s);sk_sync_income($id);sk_activate_from_booking($id);if($old!==$s)sk_mail_customer($id);}
add_action('add_meta_boxes',function(){add_meta_box('sk_bk','Rezervasyon bilgileri','sk_bk_box','booking','normal','high');});
function sk_bk_box($p){wp_nonce_field('sk_bk','sk_bk_nonce');$f=['status'=>'Durum','name'=>'Ad soyad','phone'=>'Telefon','email'=>'E-posta','ref_title'=>'Konu','date'=>'Tarih (YYYY-AA-GG)','start'=>'Başlangıç','end'=>'Bitiş','total'=>'Tutar','extra'=>'Detay','note'=>'Not'];
 echo '<table class="form-table">';foreach($f as $k=>$l){$v=get_post_meta($p->ID,$k,true);echo '<tr><th>'.esc_html($l).'</th><td>';
  if($k==='status')sk_input('sk_b[status]','select',$v,sk_statuses());else sk_input("sk_b[$k]",$k==='note'?'textarea':'text',$v);echo '</td></tr>';}
 echo '</table>';}
add_action('save_post_booking',function($id){
 if(!isset($_POST['sk_bk_nonce'])||!wp_verify_nonce($_POST['sk_bk_nonce'],'sk_bk')||!current_user_can('edit_post',$id)||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE))return;
 $in=(array)($_POST['sk_b']??[]);foreach(['name','phone','email','ref_title','date','start','end','total','extra'] as $k)update_post_meta($id,$k,sanitize_text_field($in[$k]??''));
 update_post_meta($id,'note',sanitize_textarea_field($in['note']??''));
  $rid=(int)get_post_meta($id,'ref_id',true);$dt=sanitize_text_field($in['date']??'');update_post_meta($id,'slotkey',($rid&&$dt)?sk_slotkey($rid,$dt):'');
 $s=sanitize_key($in['status']??'pending');if(isset(sk_statuses()[$s]))sk_set_status($id,$s);
});
add_action('admin_menu',function(){global $menu;$n=count(get_posts(['post_type'=>'booking','numberposts'=>-1,'fields'=>'ids','meta_key'=>'status','meta_value'=>'pending']));
 if(!$n)return;foreach($menu as $k=>$m)if(isset($m[2])&&$m[2]==='edit.php?post_type=booking')$menu[$k][0].=' <span class="awaiting-mod">'.$n.'</span>';},99);

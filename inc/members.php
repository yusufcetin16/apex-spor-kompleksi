<?php
if(!defined('ABSPATH'))exit;
// ===== Üyeler, Dersler, Üye Paneli =====
add_action('init',function(){if(!get_role('sk_member'))add_role('sk_member','Üye',['read'=>true]);});
function sk_member_url(){$p=get_page_by_path('uye-paneli');return $p?get_permalink($p):home_url('/uye-paneli/');}
function sk_days_left($uid){$e=get_user_meta($uid,'sk_end',true);if(!$e)return null;$d=DateTime::createFromFormat('!Y-m-d',$e);if(!$d)return null;return (int)(new DateTime(current_time('Y-m-d')))->diff($d)->format('%r%a');}
function sk_member_lessons($uid){return array_values(array_filter(array_map('intval',explode(',',(string)get_user_meta($uid,'sk_lessons',true)))));}
function sk_member_state($l){if($l===null)return ['none','Üyelik yok','#6b7280'];if($l<0)return ['expired','Süresi doldu','#b91c1c'];if($l<=7)return ['soon','Bitmek üzere','#b45309'];return ['active','Aktif','#15803d'];}
function sk_member_counts(){$c=['active'=>0,'soon'=>0,'expired'=>0,'none'=>0,'total'=>0];foreach(get_users(['role'=>'sk_member','fields'=>'ID']) as $id){$c[sk_member_state(sk_days_left($id))[0]]++;$c['total']++;}return $c;}
// ---- Dersler
function sk_lesson_when($id){$sh=[1=>'Pzt',2=>'Sal',3=>'Çar',4=>'Per',5=>'Cum',6=>'Cmt',7=>'Paz'];$d=implode(', ',array_map(function($x)use($sh){return $sh[$x]??'';},array_map('intval',(array)sk_meta($id,'days'))));
 $t=trim(sk_meta($id,'start').(sk_meta($id,'end')?' - '.sk_meta($id,'end'):''));return trim($d.' '.$t);}
function sk_lessons_of_branch($bid){return array_values(array_filter(sk_q('lesson'),function($l)use($bid){return (int)sk_meta($l->ID,'branch')===(int)$bid;}));}
function sk_lesson_members($id){return get_users(['role'=>'sk_member','meta_key'=>'sk_lessons','meta_value'=>','.$id.',','meta_compare'=>'LIKE','fields'=>'ID']);}
add_filter('manage_lesson_posts_columns',function($c){return ['cb'=>$c['cb']??'','title'=>'Ders','l_branch'=>'Branş','l_trainer'=>'Eğitmen','l_when'=>'Gün / Saat','l_hall'=>'Salon / Alan','l_cap'=>'Kayıtlı / Kontenjan'];});
add_action('manage_lesson_posts_custom_column',function($c,$id){
 switch($c){case 'l_branch':echo esc_html(get_the_title((int)sk_meta($id,'branch')));break;case 'l_trainer':echo esc_html(get_the_title((int)sk_meta($id,'trainer')));break;
  case 'l_when':echo esc_html(sk_lesson_when($id));break;case 'l_hall':echo esc_html(get_the_title((int)sk_meta($id,'hall')));break;
  case 'l_cap':$cap=(int)sk_meta($id,'capacity');echo count(sk_lesson_members($id)).($cap?' / '.$cap:'');}},10,2);
add_shortcode('sk_ders_programi',function(){
 $by=array_fill(1,7,[]);foreach(sk_q('lesson') as $l)foreach(array_map('intval',(array)sk_meta($l->ID,'days')) as $d)if(isset($by[$d]))$by[$d][]=$l;
 $o='<div class="week">';foreach(sk_days() as $i=>$n){usort($by[$i],function($a,$b){return strcmp((string)sk_meta($a->ID,'start'),(string)sk_meta($b->ID,'start'));});
  $o.='<div class="day"><h4>'.esc_html($n).'</h4>';
  foreach($by[$i] as $l){$tr=(int)sk_meta($l->ID,'trainer');$b=(int)sk_meta($l->ID,'branch');$o.='<div class="ls"><b>'.esc_html(trim(sk_meta($l->ID,'start').(sk_meta($l->ID,'end')?' - '.sk_meta($l->ID,'end'):''))).'</b><br>'.esc_html($l->post_title).'<br><span class="sk-muted">'.esc_html(trim(($b?get_the_title($b):'').($tr?' • '.get_the_title($tr):''))).'</span></div>';}
  if(!$by[$i])$o.='<span class="sk-muted">—</span>';$o.='</div>';}
 return $o.'</div>';});
// ---- Yönetim: Üyeler sayfası
add_action('admin_menu',function(){add_menu_page('Üyeler','Üyeler','manage_options','sk-members','sk_members_page','dashicons-groups','3.9');},6);
function sk_members_page(){
 if(!current_user_can('manage_options'))return;
 $users=get_users(['role'=>'sk_member','orderby'=>'display_name']);$st=sanitize_key($_GET['st']??'');$ren=absint($_GET['renew']??0);$c=sk_member_counts();
 echo '<div class="wrap"><h1>Üyeler</h1>';
 if(isset($_GET['done']))echo '<div class="notice notice-success"><p>Üye kaydı kaydedildi.</p></div>';
 if(isset($_GET['err']))echo '<div class="notice notice-error"><p>Ad soyad ve geçerli bir e-posta gerekli.</p></div>';
 echo '<p>';foreach([''=>'Tümü ('.$c['total'].')','active'=>'Aktif ('.$c['active'].')','soon'=>'Bitmek üzere ('.$c['soon'].')','expired'=>'Süresi dolmuş ('.$c['expired'].')'] as $k=>$l)echo '<a class="button '.($st===$k?'button-primary':'').'" href="'.esc_url(admin_url('admin.php?page=sk-members'.($k?'&st='.$k:''))).'">'.esc_html($l).'</a> ';
 echo '<a class="button button-primary" style="margin-left:10px" href="#sk-form">+ Yeni üye / üyelik yenile</a></p>';
 echo '<table class="widefat striped"><thead><tr><th>Üye</th><th>İletişim</th><th>Plan</th><th>Başlangıç</th><th>Bitiş</th><th>Kalan gün</th><th>Dersler</th><th></th></tr></thead><tbody>';
 $n=0;foreach($users as $u){$l=sk_days_left($u->ID);$s=sk_member_state($l);if($st&&$s[0]!==$st)continue;$n++;
  $ls=array_map(function($i){return get_the_title($i);},sk_member_lessons($u->ID));
  echo '<tr><td><b>'.esc_html($u->display_name).'</b></td><td>'.esc_html($u->user_email).'<br>'.esc_html(get_user_meta($u->ID,'sk_phone',true)).'</td><td>'.esc_html(get_the_title((int)get_user_meta($u->ID,'sk_plan',true))).'</td><td>'.esc_html(get_user_meta($u->ID,'sk_start',true)).'</td><td>'.esc_html(get_user_meta($u->ID,'sk_end',true)).'</td>';
  echo '<td><b style="color:'.$s[2].'">'.($l===null?'—':esc_html(max($l,0).' gün')).'</b><br><small style="color:'.$s[2].'">'.esc_html($s[1]).'</small></td><td>'.esc_html(implode(', ',$ls)?:'—').'</td>';
  echo '<td><a class="button" href="'.esc_url(admin_url('admin.php?page=sk-members&renew='.$u->ID.'#sk-form')).'">Yenile</a> <a href="'.esc_url(get_edit_user_link($u->ID)).'">Düzenle</a></td></tr>';}
 if(!$n)echo '<tr><td colspan="8">Üye yok.</td></tr>';echo '</tbody></table>';
 // form
 $ru=$ren?get_userdata($ren):null;$plan=$ru?(int)get_user_meta($ren,'sk_plan',true):0;$e=$ru?get_user_meta($ren,'sk_end',true):'';$today=current_time('Y-m-d');
 $start=($e&&$e>$today)?$e:$today;$mine=$ru?sk_member_lessons($ren):[];$days=$plan?((int)sk_meta($plan,'days')?:30):30;
 echo '<h2 id="sk-form" style="margin-top:30px">'.($ru?'Üyelik yenile: '.esc_html($ru->display_name):'Yeni üye / üyelik yenile').'</h2>';
 echo '<form id="sk-mform" method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="max-width:820px;background:#fff;border:1px solid #dcdcde;padding:10px 20px"><input type="hidden" name="action" value="sk_member_save">'.wp_nonce_field('sk_member_save','_wpnonce',true,false).'<table class="form-table">';
 echo '<tr><th>Üye</th><td><select name="m_user"><option value="0">+ Yeni üye ekle</option>';foreach($users as $u)echo '<option value="'.$u->ID.'" '.selected($ren,$u->ID,false).'>'.esc_html($u->display_name.' ('.$u->user_email.')').'</option>';echo '</select></td></tr>';
 echo '<tr class="sk-newm"><th>Ad soyad</th><td><input name="m_name" class="regular-text"></td></tr><tr class="sk-newm"><th>E-posta</th><td><input type="email" name="m_email" class="regular-text"><p class="description">Üye bu e-posta ile siteye girer; şifre belirleme bağlantısı e-postayla gönderilir.</p></td></tr><tr class="sk-newm"><th>Telefon</th><td><input name="m_phone" class="regular-text"></td></tr>';
 echo '<tr><th>Üyelik planı</th><td><select name="m_plan"><option value="0">—</option>';foreach(sk_q('membership') as $p)echo '<option value="'.$p->ID.'" data-days="'.((int)sk_meta($p->ID,'days')?:30).'" data-price="'.esc_attr(sk_meta($p->ID,'price')).'" '.selected($plan,$p->ID,false).'>'.esc_html($p->post_title).'</option>';echo '</select></td></tr>';
 echo '<tr><th>Başlangıç tarihi</th><td><input type="date" name="m_start" value="'.esc_attr($start).'"></td></tr><tr><th>Süre (gün)</th><td><input type="number" name="m_days" min="0" value="'.(int)$days.'"> <span class="description">Bitiş = başlangıç + gün</span></td></tr>';
 echo '<tr><th>Alınan ödeme ('.esc_html(sk_opt('currency')).')</th><td><input type="number" step="0.01" min="0" name="m_amount" value="'.esc_attr($plan?sk_meta($plan,'price'):'').'"> <span class="description">Doldurursanız Bütçe & Kazanç raporuna gelir olarak yazılır.</span></td></tr>';
 echo '<tr><th>Gelir hangi antrenöre yazılsın?</th><td><select name="m_trainer"><option value="0">— Antrenörsüz —</option>';foreach(sk_q('trainer') as $t)echo '<option value="'.$t->ID.'">'.esc_html($t->post_title).'</option>';echo '</select></td></tr>';
 echo '<tr><th>Katıldığı dersler</th><td>';foreach(sk_q('lesson') as $l)echo '<label style="display:block"><input type="checkbox" name="m_lessons[]" value="'.$l->ID.'" '.checked(in_array($l->ID,$mine),true,false).'> '.esc_html($l->post_title.' — '.sk_lesson_when($l->ID)).'</label>';if(!sk_q('lesson',1))echo 'Önce Spor Kompleksi > Dersler bölümünden ders ekleyin.';echo '</td></tr>';
 echo '<tr><th>Not</th><td><textarea name="m_note" rows="2" class="large-text"></textarea></td></tr><tr><th>E-posta</th><td><label><input type="checkbox" name="m_mail" value="1" checked> Üyeye üyelik bilgisini e-postayla gönder</label></td></tr></table><p><button class="button button-primary button-large">Kaydet</button></p></form>';
 echo '<script>(function(){var f=document.getElementById("sk-mform");if(!f)return;var p=f.m_plan,d=f.m_days,a=f.m_amount,u=f.m_user,n=document.querySelectorAll(".sk-newm");function np(){var o=p.options[p.selectedIndex];if(o&&o.dataset.days){d.value=o.dataset.days;a.value=o.dataset.price||"";}}function tg(){n.forEach(function(r){r.style.display=u.value==="0"?"":"none";});}p.addEventListener("change",np);u.addEventListener("change",tg);tg();})();</script></div>';
}
add_action('admin_post_sk_member_save',function(){
 if(!current_user_can('manage_options')||!wp_verify_nonce($_POST['_wpnonce']??'','sk_member_save'))wp_die('Yetkisiz işlem.');
 $back=admin_url('admin.php?page=sk-members');$uid=absint($_POST['m_user']??0);$plan=absint($_POST['m_plan']??0);$days=absint($_POST['m_days']??0);$amt=(float)($_POST['m_amount']??0);
 $start=sanitize_text_field($_POST['m_start']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$start))$start=current_time('Y-m-d');
 $name=sanitize_text_field($_POST['m_name']??'');$email=sanitize_email($_POST['m_email']??'');$new=false;
 if(!$uid){
  if($name===''||!is_email($email)){wp_safe_redirect($back.'&err=1');exit;}
  $ex=get_user_by('email',$email);
  if($ex){$uid=$ex->ID;if(!user_can($uid,'manage_options')){$u=new WP_User($uid);if(!in_array('sk_member',$u->roles,true))$u->add_role('sk_member');}}
  else{$login=sanitize_user(strstr($email,'@',true),true)?:'uye';$b=$login;$i=1;while(username_exists($login))$login=$b.(++$i);
   $uid=wp_insert_user(['user_login'=>$login,'user_email'=>$email,'user_pass'=>wp_generate_password(16,false),'display_name'=>$name,'first_name'=>$name,'role'=>'sk_member']);
   if(is_wp_error($uid))wp_die(esc_html($uid->get_error_message()));$new=true;}
  update_user_meta($uid,'sk_phone',sanitize_text_field($_POST['m_phone']??''));
 }
 $end=$days>0?date('Y-m-d',strtotime($start.' +'.$days.' days')):'';
 update_user_meta($uid,'sk_plan',$plan);update_user_meta($uid,'sk_start',$start);update_user_meta($uid,'sk_end',$end);
 $ls=array_filter(array_map('absint',(array)($_POST['m_lessons']??[])));update_user_meta($uid,'sk_lessons',$ls?','.implode(',',$ls).',':'');
 if(!empty($_POST['m_note']))update_user_meta($uid,'sk_note',sanitize_textarea_field($_POST['m_note']));
 if($amt>0)sk_add_entry(['type'=>'income','amount'=>$amt,'category'=>'membership','trainer'=>absint($_POST['m_trainer']??0),'member'=>$uid,'note'=>trim(get_the_title($plan).' '.sanitize_text_field($_POST['m_note']??''))]);
 $u=get_userdata($uid);
 if(!empty($_POST['m_mail'])&&$u){$msg="Merhaba {$u->display_name},\n\nÜyeliğiniz kaydedildi.\nPlan: ".get_the_title($plan)."\nBitiş tarihi: ".($end?:'-')."\nÜye paneli: ".sk_member_url()."\nKullanıcı adı: {$u->user_login}\n";
  if($new){$k=get_password_reset_key($u);if(!is_wp_error($k))$msg.="\nŞifrenizi belirlemek için: ".network_site_url('wp-login.php?action=rp&key='.$k.'&login='.rawurlencode($u->user_login),'login')."\n";}
  wp_mail($u->user_email,get_bloginfo('name').' - Üyelik bilgileriniz',$msg);}
 wp_safe_redirect($back.'&done=1');exit;});
// ---- Kullanıcı profilinde üye alanları + kullanıcı listesinde kalan gün
foreach(['show_user_profile','edit_user_profile'] as $h)add_action($h,function($u){
 if(!current_user_can('manage_options')||!in_array('sk_member',(array)$u->roles,true))return;wp_nonce_field('sk_uf','sk_uf_nonce');$g=function($k)use($u){return get_user_meta($u->ID,$k,true);};$mine=sk_member_lessons($u->ID);
 echo '<h2>Üyelik bilgileri</h2><table class="form-table"><tr><th>Plan</th><td><select name="sk_plan"><option value="0">—</option>';foreach(sk_q('membership') as $p)echo '<option value="'.$p->ID.'" '.selected((int)$g('sk_plan'),$p->ID,false).'>'.esc_html($p->post_title).'</option>';echo '</select></td></tr>';
 echo '<tr><th>Başlangıç</th><td><input type="date" name="sk_start" value="'.esc_attr($g('sk_start')).'"></td></tr><tr><th>Bitiş</th><td><input type="date" name="sk_end" value="'.esc_attr($g('sk_end')).'"></td></tr><tr><th>Telefon</th><td><input name="sk_phone" value="'.esc_attr($g('sk_phone')).'"></td></tr><tr><th>Dersler</th><td>';
 foreach(sk_q('lesson') as $l)echo '<label style="display:block"><input type="checkbox" name="sk_lessons[]" value="'.$l->ID.'" '.checked(in_array($l->ID,$mine),true,false).'> '.esc_html($l->post_title.' — '.sk_lesson_when($l->ID)).'</label>';
 echo '</td></tr><tr><th>Not</th><td><textarea name="sk_note" rows="2" class="large-text">'.esc_textarea($g('sk_note')).'</textarea></td></tr></table>';});
foreach(['personal_options_update','edit_user_profile_update'] as $h)add_action($h,function($uid){
 if(!current_user_can('manage_options')||!isset($_POST['sk_uf_nonce'])||!wp_verify_nonce($_POST['sk_uf_nonce'],'sk_uf'))return;
 foreach(['sk_start','sk_end'] as $k){$v=sanitize_text_field($_POST[$k]??'');update_user_meta($uid,$k,preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)?$v:'');}
 update_user_meta($uid,'sk_plan',absint($_POST['sk_plan']??0));update_user_meta($uid,'sk_phone',sanitize_text_field($_POST['sk_phone']??''));update_user_meta($uid,'sk_note',sanitize_textarea_field($_POST['sk_note']??''));
 $ls=array_filter(array_map('absint',(array)($_POST['sk_lessons']??[])));update_user_meta($uid,'sk_lessons',$ls?','.implode(',',$ls).',':'');});
add_filter('manage_users_columns',function($c){$c['sk_left']='Kalan gün';return $c;});
add_filter('manage_users_custom_column',function($v,$col,$uid){if($col!=='sk_left')return $v;$u=get_userdata($uid);if(!in_array('sk_member',(array)$u->roles,true))return '';$l=sk_days_left($uid);$s=sk_member_state($l);return '<b style="color:'.$s[2].'">'.($l===null?'—':esc_html(max($l,0).' gün')).'</b>';},10,3);
// ---- Üye paneli (sitede)
add_shortcode('sk_uye_paneli','sk_member_panel');
function sk_member_panel(){
 ob_start();
 if(!is_user_logged_in()){
  echo '<div class="sk-form sk-login" style="max-width:480px"><h3>'.esc_html(sk_t('u_login')).'</h3>'.wp_login_form(['echo'=>false,'redirect'=>get_permalink(),'remember'=>true,'label_username'=>'E-posta / kullanıcı adı','label_password'=>'Şifre','label_remember'=>'Beni hatırla','label_log_in'=>'Giriş yap']).'<p><a href="'.esc_url(wp_lostpassword_url(get_permalink())).'">'.esc_html(sk_t('u_lost')).'</a> · <a href="'.esc_url(sk_signup_url()).'">'.esc_html(sk_t('s_title')).'</a></p><p class="sk-muted" style="font-size:.85rem">'.esc_html(sk_t('u_login_note')).'</p></div>';
  return ob_get_clean();}
 $u=wp_get_current_user();$out=' <a class="sk-btn ghost" href="'.esc_url(wp_logout_url(sk_member_url())).'">'.esc_html(sk_t('u_logout')).'</a>';
 if(!in_array('sk_member',(array)$u->roles,true)){echo '<p>Yönetici hesabıyla giriş yaptınız; üye paneli üyelere özeldir. <a href="'.esc_url(admin_url('admin.php?page=sk-members')).'">Üyeleri yönet</a>'.$out.'</p>';return ob_get_clean();}
 $l=sk_days_left($u->ID);$s=sk_member_state($l);$plan=(int)get_user_meta($u->ID,'sk_plan',true);$st=get_user_meta($u->ID,'sk_start',true);$en=get_user_meta($u->ID,'sk_end',true);
 $tot=($st&&$en)?max(1,(int)((strtotime($en)-strtotime($st))/DAY_IN_SECONDS)):0;$pct=($l!==null&&$tot)?max(0,min(100,round($l/$tot*100))):0;
 $lab=['none'=>sk_t('u_expired'),'expired'=>sk_t('u_expired'),'soon'=>sk_t('u_soon'),'active'=>sk_t('u_active')][$s[0]];
 echo '<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1em;align-items:center"><h2 style="margin:0">'.esc_html(sk_t('u_hello').' '.$u->display_name).'</h2><div>'.$out.'</div></div>';
 if($l===null){echo '<p class="sk-muted" style="margin-top:2em">'.esc_html(sk_t('u_no_member')).'</p>';}
 else{echo '<div class="mcard"><div><span class="sk-muted">'.esc_html(sk_t('u_left')).'</span><div class="big" style="color:'.($s[0]==='expired'?'#ef4444':($s[0]==='soon'?'#f59e0b':'var(--accent)')).'">'.(int)max($l,0).'<small> '.esc_html(sk_t('u_days')).'</small></div><span class="sk-tag red">'.esc_html($lab).'</span></div>
  <div><p><b>'.esc_html(sk_t('u_plan')).':</b> '.esc_html($plan?get_the_title($plan):'-').'</p><p><b>'.esc_html(sk_t('u_period')).':</b> '.esc_html(trim($st.' → '.$en)).'</p><div class="bar"><i style="width:'.$pct.'%"></i></div></div></div>';}
 $ls=sk_member_lessons($u->ID);echo '<h3 style="margin-top:2em">'.esc_html(sk_t('u_lessons')).'</h3>';
 if(!$ls)echo '<p class="sk-muted">'.esc_html(sk_t('u_no_lessons')).'</p>';
 else{echo '<div class="grid">';foreach($ls as $id){if(get_post_type($id)!=='lesson')continue;$tr=(int)sk_meta($id,'trainer');$b=(int)sk_meta($id,'branch');$h=(int)sk_meta($id,'hall');
  echo '<div class="card"><div class="bd"><span class="sk-tag red" style="align-self:flex-start">'.esc_html($b?get_the_title($b):'').'</span><h3>'.esc_html(get_the_title($id)).'</h3><b>'.esc_html(sk_lesson_when($id)).'</b><span class="sk-muted">'.esc_html(trim(($tr?get_the_title($tr):'').($h?' • '.get_the_title($h):''))).'</span></div></div>';}echo '</div>';}
 $bk=get_posts(['post_type'=>'booking','numberposts'=>20,'post_status'=>'any','meta_key'=>'email','meta_value'=>$u->user_email,'orderby'=>'date','order'=>'DESC']);
 if($bk){echo '<h3 style="margin-top:2em">'.esc_html(sk_t('u_appts')).'</h3><table class="av"><tbody>';foreach($bk as $b){$m=function($k)use($b){return get_post_meta($b->ID,$k,true);};
  echo '<tr><td>'.esc_html(sk_types()[$m('type')]??'').'</td><td>'.esc_html($m('ref_title')).'</td><td>'.esc_html(trim($m('date').' '.$m('start'))).'</td><td>'.esc_html(sk_statuses()[$m('status')]??'').'</td></tr>';}echo '</tbody></table>';}
 return ob_get_clean();}
// Üyeler yönetim paneline girmesin, üst çubuğu görmesin
add_filter('show_admin_bar',function($s){return current_user_can('edit_posts')?$s:false;});
add_action('admin_init',function(){if(wp_doing_ajax()||defined('DOING_CRON'))return;if(!current_user_can('edit_posts')&&!current_user_can('manage_options')){wp_safe_redirect(sk_member_url());exit;}});
add_filter('login_redirect',function($r,$req,$user){if($user&&!is_wp_error($user)&&in_array('sk_member',(array)$user->roles,true))return sk_member_url();return $r;},10,3);

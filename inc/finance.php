<?php
if(!defined('ABSPATH'))exit;
// ===== Bütçe & Kazanç (yalnızca yönetici) =====
function sk_fin_cats(){return ['income'=>['pt'=>'PT / özel ders','branch'=>'Branş / ders ücreti','membership'=>'Üyelik','hall'=>'Salon / alan kiralama','other_in'=>'Diğer gelir'],'expense'=>['rent'=>'Kira','salary'=>'Maaş / antrenör ödemesi','equip'=>'Ekipman','bill'=>'Fatura','mkt'=>'Reklam','other_out'=>'Diğer gider']];}
function sk_fin_cat_label($c){foreach(sk_fin_cats() as $g)if(isset($g[$c]))return $g[$c];return $c;}
add_action('init',function(){
 $m='manage_options';$cap=[];foreach(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','delete_posts','publish_posts','read_private_posts','create_posts','delete_others_posts','delete_private_posts','delete_published_posts','edit_private_posts','edit_published_posts'] as $c)$cap[$c]=$m;
 register_post_type('sk_entry',['labels'=>['name'=>'Gelir / Gider Kayıtları','singular_name'=>'Kayıt','menu_name'=>'Kayıtlar','all_items'=>'Gelir / Gider Kayıtları','add_new'=>'Yeni Kayıt Ekle','add_new_item'=>'Gelir / gider ekle','edit_item'=>'Kaydı düzenle','not_found'=>'Kayıt yok'],
  'public'=>false,'show_ui'=>true,'show_in_menu'=>'sk-finance','supports'=>['title'],'capabilities'=>$cap,'map_meta_cap'=>false]);
});
add_action('admin_menu',function(){
 add_menu_page('Bütçe & Kazanç','Bütçe & Kazanç','manage_options','sk-finance','sk_finance_page','dashicons-chart-bar','3.96');
 add_submenu_page('sk-finance','Kazanç Raporu','Kazanç Raporu','manage_options','sk-finance','sk_finance_page');
},8);
function sk_add_entry($a){
 $ty=($a['type']??'income')==='expense'?'expense':'income';$cat=$a['category']??($ty==='income'?'other_in':'other_out');
 return wp_insert_post(['post_type'=>'sk_entry','post_status'=>'publish','post_title'=>($ty==='income'?'Gelir':'Gider').' - '.sk_fin_cat_label($cat),
  'meta_input'=>['type'=>$ty,'category'=>$cat,'amount'=>(float)($a['amount']??0),'date'=>$a['date']??current_time('Y-m-d'),'trainer'=>(int)($a['trainer']??0),'member'=>(int)($a['member']??0),'note'=>$a['note']??'','booking_id'=>(int)($a['booking_id']??0)]]);}
// Onaylanan rezervasyon -> otomatik gelir kaydı (antrenör randevusunda antrenöre yazılır)
function sk_sync_income($bid){
 $s=get_post_meta($bid,'status',true);$type=get_post_meta($bid,'type',true);$amt=(float)get_post_meta($bid,'total',true);
 $ex=get_posts(['post_type'=>'sk_entry','numberposts'=>1,'post_status'=>'any','meta_key'=>'booking_id','meta_value'=>$bid]);
 if($s==='approved'&&$amt>0&&in_array($type,['pt','hall','membership'],true)){
  if($ex){update_post_meta($ex[0]->ID,'amount',$amt);return;}
  sk_add_entry(['type'=>'income','amount'=>$amt,'category'=>$type,'trainer'=>$type==='pt'?(int)get_post_meta($bid,'ref_id',true):0,'note'=>'Rezervasyon: '.get_post_meta($bid,'name',true),'booking_id'=>$bid]);
 }elseif($ex)wp_delete_post($ex[0]->ID,true);
}
// ---- kayıt ekranı
add_action('add_meta_boxes',function(){add_meta_box('sk_fin','Kayıt Bilgileri','sk_fin_box','sk_entry','normal','high');});
function sk_fin_box($p){
 wp_nonce_field('sk_fin','sk_fin_nonce');$g=function($k)use($p){return get_post_meta($p->ID,$k,true);};$ty=$g('type')?:'income';$dt=$g('date')?:current_time('Y-m-d');
 echo '<table class="form-table"><tr><th>Tür</th><td><select name="f_type"><option value="income" '.selected($ty,'income',false).'>Gelir</option><option value="expense" '.selected($ty,'expense',false).'>Gider</option></select></td></tr>';
 echo '<tr><th>Kategori</th><td><select name="f_cat">';foreach(sk_fin_cats() as $t=>$cs){echo '<optgroup label="'.($t==='income'?'Gelir':'Gider').'">';foreach($cs as $k=>$l)echo '<option value="'.esc_attr($k).'" '.selected($g('category'),$k,false).'>'.esc_html($l).'</option>';echo '</optgroup>';}echo '</select></td></tr>';
 echo '<tr><th>Tutar</th><td><input type="number" step="0.01" min="0" name="f_amount" value="'.esc_attr($g('amount')).'" class="regular-text"> '.esc_html(sk_opt('currency')).'</td></tr>';
 echo '<tr><th>Tarih</th><td><input type="date" name="f_date" value="'.esc_attr($dt).'"></td></tr>';
 echo '<tr><th>Antrenör (isteğe bağlı)</th><td><select name="f_trainer"><option value="0">— Antrenörsüz —</option>';foreach(get_posts(['post_type'=>'trainer','numberposts'=>-1,'orderby'=>'title','order'=>'ASC','post_status'=>'any']) as $t)echo '<option value="'.$t->ID.'" '.selected((int)$g('trainer'),$t->ID,false).'>'.esc_html($t->post_title).'</option>';echo '</select><p class="description">Bu gelir hangi antrenörden geldiyse seçin; raporda o antrenörün kazancına yazılır.</p></td></tr>';
 echo '<tr><th>Üye (isteğe bağlı)</th><td><select name="f_member"><option value="0">—</option>';foreach(get_users(['role'=>'sk_member','orderby'=>'display_name']) as $u)echo '<option value="'.$u->ID.'" '.selected((int)$g('member'),$u->ID,false).'>'.esc_html($u->display_name).'</option>';echo '</select></td></tr>';
 echo '<tr><th>Not</th><td><textarea name="f_note" rows="3" class="large-text">'.esc_textarea($g('note')).'</textarea></td></tr></table>';
}
add_action('save_post_sk_entry','sk_fin_save');
function sk_fin_save($id){
 if(!isset($_POST['sk_fin_nonce'])||!wp_verify_nonce($_POST['sk_fin_nonce'],'sk_fin')||!current_user_can('manage_options')||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE))return;
 $ty=($_POST['f_type']??'')==='expense'?'expense':'income';$cat=sanitize_key($_POST['f_cat']??'');if(!isset(sk_fin_cats()[$ty][$cat]))$cat=array_key_first(sk_fin_cats()[$ty]);
 $d=sanitize_text_field($_POST['f_date']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d))$d=current_time('Y-m-d');
 foreach(['type'=>$ty,'category'=>$cat,'amount'=>(float)($_POST['f_amount']??0),'date'=>$d,'trainer'=>absint($_POST['f_trainer']??0),'member'=>absint($_POST['f_member']??0),'note'=>sanitize_textarea_field($_POST['f_note']??'')] as $k=>$v)update_post_meta($id,$k,$v);
 remove_action('save_post_sk_entry','sk_fin_save');wp_update_post(['ID'=>$id,'post_title'=>($ty==='income'?'Gelir':'Gider').' - '.sk_fin_cat_label($cat)]);add_action('save_post_sk_entry','sk_fin_save');
}
add_filter('manage_sk_entry_posts_columns',function(){return ['cb'=>'<input type="checkbox">','title'=>'Kayıt','f_date'=>'Tarih','f_type'=>'Tür','f_amt'=>'Tutar','f_tr'=>'Antrenör','f_mem'=>'Üye','f_note'=>'Not'];});
add_action('manage_sk_entry_posts_custom_column',function($c,$id){$m=function($k)use($id){return get_post_meta($id,$k,true);};
 switch($c){case 'f_date':echo esc_html($m('date'));break;
  case 'f_type':echo $m('type')==='income'?'<b style="color:#15803d">Gelir</b>':'<b style="color:#b91c1c">Gider</b>';echo '<br>'.esc_html(sk_fin_cat_label($m('category')));break;
  case 'f_amt':echo '<b>'.esc_html(sk_money($m('amount'))).'</b>';break;case 'f_tr':echo $m('trainer')?esc_html(get_the_title((int)$m('trainer'))):'—';break;
  case 'f_mem':$u=$m('member')?get_userdata((int)$m('member')):null;echo $u?esc_html($u->display_name):'—';break;case 'f_note':echo esc_html($m('note'));}},10,2);
add_action('pre_get_posts',function($q){if(is_admin()&&$q->is_main_query()&&$q->get('post_type')==='sk_entry'&&!$q->get('orderby')){$q->set('meta_key','date');$q->set('orderby','meta_value');$q->set('order','DESC');}});
// ---- rapor
function sk_fin_range(){$r=sanitize_key($_GET['range']??'month');$t=current_time('Y-m-d');
 switch($r){
  case 'all':return ['all',null,null];
  case 'year':return ['year',date('Y-01-01',strtotime($t)),date('Y-12-31',strtotime($t))];
  case 'last':$f=date('Y-m-01',strtotime($t.' first day of last month'));return ['last',$f,date('Y-m-t',strtotime($f))];
  case 'custom':$f=sanitize_text_field($_GET['from']??'');$to=sanitize_text_field($_GET['to']??'');if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$f)&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$to))return ['custom',$f,$to];
  default:return ['month',date('Y-m-01',strtotime($t)),date('Y-m-t',strtotime($t))];}}
function sk_fin_entries($f,$t){$a=['post_type'=>'sk_entry','numberposts'=>-1,'post_status'=>'publish'];if($f)$a['meta_query']=[['key'=>'date','value'=>[$f,$t],'compare'=>'BETWEEN','type'=>'DATE']];return get_posts($a);}
function sk_finance_page(){
 if(!current_user_can('manage_options'))return;
 [$r,$f,$t]=sk_fin_range();$es=sk_fin_entries($f,$t);
 $inc=$exp=0;$tr=[];$cat=['income'=>[],'expense'=>[]];$mon=[];
 foreach($es as $e){$m=function($k)use($e){return get_post_meta($e->ID,$k,true);};$a=(float)$m('amount');$ty=$m('type')==='expense'?'expense':'income';$c=$m('category');$mo=substr((string)$m('date'),0,7);
  $cat[$ty][$c]=($cat[$ty][$c]??0)+$a;$mon[$mo][$ty]=($mon[$mo][$ty]??0)+$a;
  if($ty==='income'){$inc+=$a;$ti=(int)$m('trainer');$tr[$ti]['inc']=($tr[$ti]['inc']??0)+$a;$tr[$ti]['n']=($tr[$ti]['n']??0)+1;}else $exp+=$a;}
 $all=0;foreach(sk_fin_entries(null,null) as $e)if(get_post_meta($e->ID,'type',true)!=='expense')$all+=(float)get_post_meta($e->ID,'amount',true);
 $rows=[];foreach(get_posts(['post_type'=>'trainer','numberposts'=>-1,'post_status'=>'any']) as $p){$i=$tr[$p->ID]['inc']??0;$pc=(float)get_post_meta($p->ID,'commission',true);$rows[]=['name'=>$p->post_title,'n'=>$tr[$p->ID]['n']??0,'inc'=>$i,'pc'=>$pc];}
 $rows[]=['name'=>'Antrenörsüz (üyelik, salon kiralama vb.)','n'=>$tr[0]['n']??0,'inc'=>$tr[0]['inc']??0,'pc'=>0];
 usort($rows,function($a,$b){return $b['inc']<=>$a['inc'];});
 $box=function($l,$v,$c){return '<div style="background:#fff;border:1px solid #dcdcde;border-left:4px solid '.$c.';padding:14px 18px;min-width:200px"><div style="color:#646970">'.esc_html($l).'</div><div style="font-size:26px;font-weight:700">'.esc_html(sk_money($v)).'</div></div>';};
 $tabs=['month'=>'Bu ay','last'=>'Geçen ay','year'=>'Bu yıl','all'=>'Tümü'];
 echo '<div class="wrap"><h1>Bütçe & Kazanç Raporu <a class="page-title-action" href="'.esc_url(admin_url('post-new.php?post_type=sk_entry')).'">+ Gelir / Gider Ekle</a> <a class="page-title-action" href="'.esc_url(admin_url('edit.php?post_type=sk_entry')).'">Tüm Kayıtlar</a></h1>';
 echo '<p>';foreach($tabs as $k=>$l)echo '<a class="button '.($r===$k?'button-primary':'').'" href="'.esc_url(admin_url('admin.php?page=sk-finance&range='.$k)).'">'.esc_html($l).'</a> ';
 echo '<form method="get" style="display:inline-block;margin-left:10px"><input type="hidden" name="page" value="sk-finance"><input type="hidden" name="range" value="custom"><input type="date" name="from" value="'.esc_attr($r==='custom'?$f:'').'"> – <input type="date" name="to" value="'.esc_attr($r==='custom'?$t:'').'"> <button class="button">Uygula</button></form> ';
 echo '<a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=sk_fin_csv&range='.$r.'&from='.urlencode((string)($r==='custom'?$f:'')).'&to='.urlencode((string)($r==='custom'?$t:''))),'sk_fin_csv')).'">CSV indir</a></p>';
 echo '<p style="color:#646970">Dönem: '.esc_html($f?"$f / $t":'Tüm zamanlar').'</p><div style="display:flex;gap:14px;flex-wrap:wrap;margin:14px 0">'.$box('Dönem geliri',$inc,'#15803d').$box('Dönem gideri',$exp,'#b91c1c').$box('Net kâr',$inc-$exp,'#2563eb').$box('Bugüne kadarki toplam gelir',$all,'#e11d2e').'</div>';
 echo '<h2>Antrenör bazında kazanç</h2><p class="description">Antrenör payı yüzdesini Antrenörler > düzenle > "Antrenör payı %" alanından girersiniz; salona kalan tutar buna göre hesaplanır.</p>';
 echo '<table class="widefat striped" style="max-width:1000px"><thead><tr><th>Antrenör</th><th>İşlem</th><th>Getirdiği gelir</th><th>Toplam içindeki pay</th><th>Antrenör payı</th><th>Salona kalan</th></tr></thead><tbody>';
 $tp=0;$tk=0;foreach($rows as $i=>$x){$pay=$x['inc']*$x['pc']/100;$tp+=$pay;$tk+=$x['inc']-$pay;
  echo '<tr><td>'.($i===0&&$x['inc']>0?'🏆 ':'').'<b>'.esc_html($x['name']).'</b></td><td>'.(int)$x['n'].'</td><td><b>'.esc_html(sk_money($x['inc'])).'</b></td><td>'.($inc?round($x['inc']/$inc*100,1):0).'%</td><td>'.($x['pc']?esc_html(sk_money($pay)).' (%'.$x['pc'].')':'—').'</td><td>'.esc_html(sk_money($x['inc']-$pay)).'</td></tr>';}
 echo '</tbody><tfoot><tr><th>Toplam</th><th></th><th>'.esc_html(sk_money($inc)).'</th><th>100%</th><th>'.esc_html(sk_money($tp)).'</th><th>'.esc_html(sk_money($tk)).'</th></tr></tfoot></table>';
 echo '<div style="display:flex;gap:30px;flex-wrap:wrap;max-width:1000px"><div style="flex:1;min-width:300px"><h2>Gelir kategorileri</h2><table class="widefat striped"><tbody>';
 foreach($cat['income'] as $k=>$v)echo '<tr><td>'.esc_html(sk_fin_cat_label($k)).'</td><td align="right"><b>'.esc_html(sk_money($v)).'</b></td></tr>';if(!$cat['income'])echo '<tr><td>Kayıt yok</td></tr>';
 echo '</tbody></table></div><div style="flex:1;min-width:300px"><h2>Gider kategorileri</h2><table class="widefat striped"><tbody>';
 foreach($cat['expense'] as $k=>$v)echo '<tr><td>'.esc_html(sk_fin_cat_label($k)).'</td><td align="right"><b>'.esc_html(sk_money($v)).'</b></td></tr>';if(!$cat['expense'])echo '<tr><td>Kayıt yok</td></tr>';
 echo '</tbody></table></div></div><h2>Aylık özet</h2><table class="widefat striped" style="max-width:1000px"><thead><tr><th>Ay</th><th>Gelir</th><th>Gider</th><th>Net</th><th style="width:35%"></th></tr></thead><tbody>';
 krsort($mon);$mx=1;foreach($mon as $v)$mx=max($mx,$v['income']??0);
 foreach($mon as $k=>$v){$i=$v['income']??0;$g=$v['expense']??0;echo '<tr><td>'.esc_html($k).'</td><td>'.esc_html(sk_money($i)).'</td><td>'.esc_html(sk_money($g)).'</td><td><b>'.esc_html(sk_money($i-$g)).'</b></td><td><div style="background:#e11d2e;height:10px;width:'.round($i/$mx*100).'%"></div></td></tr>';}
 if(!$mon)echo '<tr><td colspan="5">Bu dönemde kayıt yok.</td></tr>';
 echo '</tbody></table><p class="description" style="margin-top:14px">Onaylanan PT, salon ve üyelik rezervasyonları otomatik gelir olarak eklenir. Üye ekleme/yenileme ödemeleri de buraya yazılır. Diğer gelir ve giderleri elle ekleyebilirsiniz.</p></div>';
}
add_action('admin_post_sk_fin_csv',function(){
 if(!current_user_can('manage_options')||!wp_verify_nonce($_GET['_wpnonce']??'','sk_fin_csv'))wp_die('Yetkisiz işlem.');
 [$r,$f,$t]=sk_fin_range();header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename=kazanc-'.date('Ymd').'.csv');
 $o=fopen('php://output','w');fwrite($o,"\xEF\xBB\xBF");fputcsv($o,['Tarih','Tür','Kategori','Tutar','Antrenör','Üye','Not']);
 foreach(sk_fin_entries($f,$t) as $e){$m=function($k)use($e){return get_post_meta($e->ID,$k,true);};$u=$m('member')?get_userdata((int)$m('member')):null;
  fputcsv($o,[$m('date'),$m('type')==='income'?'Gelir':'Gider',sk_fin_cat_label($m('category')),$m('amount'),$m('trainer')?get_the_title((int)$m('trainer')):'',$u?$u->display_name:'',$m('note')]);}
 fclose($o);exit;});

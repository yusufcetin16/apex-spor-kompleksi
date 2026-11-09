<?php
if(!defined('ABSPATH'))exit;
function sk_register_cpts(){
 $t=[
  'branch'=>['Branş','Branşlar','branslar','dashicons-superhero',['title','editor','excerpt','thumbnail','page-attributes']],
  'trainer'=>['Antrenör','Antrenörler','antrenorler','dashicons-businessperson',['title','editor','excerpt','thumbnail','page-attributes']],
  'pt_package'=>['PT Paketi','PT Paketleri','pt-paketleri','dashicons-tickets-alt',['title','editor','excerpt','thumbnail','page-attributes']],
  'hall'=>['Salon / Alan','Salonlar & Alanlar','salonlar','dashicons-admin-multisite',['title','editor','excerpt','thumbnail','page-attributes']],
  'facility'=>['Tesis Olanağı','Tesis Olanakları','tesis-olanaklari','dashicons-store',['title','excerpt','thumbnail','page-attributes']],
  'membership'=>['Üyelik Planı','Üyelik Planları','uyelik','dashicons-id',['title','editor','excerpt','thumbnail','page-attributes']],
 ];
 $pos=['branch'=>3.2,'trainer'=>3.3,'hall'=>3.5,'pt_package'=>3.6,'facility'=>3.7,'membership'=>3.8];
 foreach($t as $k=>$c){register_post_type($k,['labels'=>['name'=>$c[1],'singular_name'=>$c[0],'add_new'=>'Yeni ekle','add_new_item'=>$c[0].' ekle','edit_item'=>$c[0].' düzenle','all_items'=>$c[1],'menu_name'=>$c[1],'not_found'=>'Kayıt yok'],
  'public'=>true,'has_archive'=>$c[2],'rewrite'=>['slug'=>$c[2]],'menu_icon'=>$c[3],'supports'=>$c[4],'show_in_rest'=>true,'show_in_menu'=>true,'menu_position'=>$pos[$k]]);}
 register_post_type('booking',['labels'=>['name'=>'Rezervasyonlar','singular_name'=>'Rezervasyon','menu_name'=>'Rezervasyonlar','edit_item'=>'Rezervasyon düzenle','all_items'=>'Tüm rezervasyonlar','not_found'=>'Rezervasyon yok','search_items'=>'Rezervasyon ara'],
  'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_position'=>3.95,'menu_icon'=>'dashicons-calendar-alt','supports'=>['title'],'capability_type'=>'post','capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true]);
 register_post_type('lesson',['labels'=>['name'=>'Dersler','singular_name'=>'Ders','menu_name'=>'Dersler','all_items'=>'Dersler','add_new'=>'Yeni ders ekle','add_new_item'=>'Ders ekle','edit_item'=>'Dersi düzenle','not_found'=>'Ders yok'],'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_position'=>3.4,'menu_icon'=>'dashicons-calendar','supports'=>['title','editor','page-attributes']]);
}
add_action('init','sk_register_cpts');

function sk_meta_defs(){return [
 'branch'=>[
  'level'=>['Seviye (ör. Başlangıçtan ileriye)','text'],'age'=>['Yaş grubu','text'],
  'schedule'=>['Ders programı (her satıra bir ders, ör. Pzt-Çar 19:00-20:00)','textarea'],
  'price'=>['Aylık ücret','number'],'gear'=>['Gerekli ekipman','text'],'trainers'=>['Eğitmenler','trainers'],'highlight'=>['Öne çıkar','checkbox']],
 'trainer'=>[
  'role'=>['Uzmanlık alanı','text'],'official'=>['Resmî unvan / lisans','text'],'exp'=>['Deneyim (yıl)','number'],
  'ind'=>['Bireysel antrenman veriyor','checkbox'],'grp'=>['Grupsal antrenman veriyor','checkbox'],
  'price_ind'=>['Bireysel seans ücreti','number'],'price_grp'=>['Grupsal seans ücreti (kişi başı)','number'],
  'certs'=>['Sertifikalar (her satıra bir tane)','textarea'],'cert_gallery'=>['Sertifika / belge görselleri','gallery'],
  'commission'=>['Antrenör payı % (yalnızca Bütçe raporunda kullanılır, sitede görünmez)','number'],
  'avail'=>['Haftalık müsaitlik (online takvim)','avail']],
 'pt_package'=>[
  'ptype'=>['Antrenman türü','select',['ind'=>'Bireysel','grp'=>'Grupsal']],'sessions'=>['Seans sayısı','number'],'price'=>['Paket ücreti','number'],
  'validity'=>['Geçerlilik süresi (ör. 2 ay)','text'],'days'=>['Antrenman günleri (ör. Pzt-Çar-Cum)','text'],
  'trainers'=>['Eğitmenler','trainers'],'branch'=>['Branş (isteğe bağlı)','branchsel'],'highlight'=>['Öne çıkar','checkbox']],
 'hall'=>[
  'sport'=>['Spor / alan türü (ör. Basketbol, Boks, MMA)','text'],'price'=>['Saatlik kiralama ücreti','number'],
  'capacity'=>['Kapasite','text'],'size'=>['Boyut / zemin','text'],'referee'=>['Hakem ayarlama hizmeti var','checkbox'],
  'features'=>['Özellikler (her satıra bir tane)','textarea'],'gallery'=>['Salon fotoğrafları','gallery']],
 'lesson'=>[
  'branch'=>['Branş','postsel','branch'],'trainer'=>['Eğitmen','postsel','trainer'],'hall'=>['Salon / Alan','postsel','hall'],'days'=>['Günler','daysel'],'start'=>['Başlangıç saati','time'],'end'=>['Bitiş saati','time'],'capacity'=>['Kontenjan (kişi)','number']],
 'facility'=>['icon'=>['İkon (emoji, ör. 🚿)','text']],
 'membership'=>[
  'price'=>['Ücret','number'],'period'=>['Süre (ör. Aylık)','text'],'days'=>['Üyelik süresi (gün) — üye panelinde kalan gün bundan hesaplanır','number'],'features'=>['Özellikler (her satıra bir tane)','textarea'],'highlight'=>['Öne çıkar','checkbox']],
];}
function sk_days(){return [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];}
add_action('add_meta_boxes',function(){
 foreach(sk_meta_defs() as $pt=>$d)add_meta_box('sk_meta','Detaylar','sk_meta_box',$pt,'normal','high');
});
function sk_meta_box($post){
 wp_nonce_field('sk_meta','sk_meta_nonce');$d=sk_meta_defs()[$post->post_type];echo '<table class="sk-mt" style="width:100%">';
 foreach($d as $k=>$f){$v=get_post_meta($post->ID,$k,true);echo '<tr><th>'.esc_html($f[0]).'</th><td>';
  if($f[1]==='avail'){$v=is_array($v)?$v:[];echo '<table>';
   foreach(sk_days() as $i=>$n){$a=$v[$i]??['on'=>0,'from'=>'09:00','to'=>'18:00'];
    echo '<tr><td><label><input type="checkbox" name="sk_m[avail]['.$i.'][on]" value="1" '.checked(!empty($a['on']),true,false).'> '.esc_html($n).'</label></td><td><input type="time" name="sk_m[avail]['.$i.'][from]" value="'.esc_attr($a['from']).'"> – <input type="time" name="sk_m[avail]['.$i.'][to]" value="'.esc_attr($a['to']).'"></td></tr>';}
   echo '</table>';}
  elseif($f[1]==='trainers'){$v=array_map('intval',(array)$v);foreach(get_posts(['post_type'=>'trainer','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']) as $t)echo '<label style="display:block"><input type="checkbox" name="sk_m['.$k.'][]" value="'.$t->ID.'" '.checked(in_array($t->ID,$v),true,false).'> '.esc_html($t->post_title).'</label>';}
  elseif($f[1]==='branchsel'){echo '<select name="sk_m['.$k.']"><option value="0">—</option>';foreach(get_posts(['post_type'=>'branch','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']) as $b)echo '<option value="'.$b->ID.'" '.selected((int)$v,$b->ID,false).'>'.esc_html($b->post_title).'</option>';echo '</select>';}
  elseif($f[1]==='postsel'){echo '<select name="sk_m['.$k.']"><option value="0">—</option>';foreach(get_posts(['post_type'=>$f[2],'numberposts'=>-1,'orderby'=>'title','order'=>'ASC']) as $b)echo '<option value="'.$b->ID.'" '.selected((int)$v,$b->ID,false).'>'.esc_html($b->post_title).'</option>';echo '</select>';}
  elseif($f[1]==='daysel'){$v=array_map('intval',(array)$v);foreach(sk_days() as $i=>$n)echo '<label style="margin-right:12px"><input type="checkbox" name="sk_m['.$k.'][]" value="'.$i.'" '.checked(in_array($i,$v),true,false).'> '.esc_html($n).'</label>';}
  else sk_input("sk_m[$k]",$f[1],$v,$f[2]??[]);
  echo '</td></tr>';}
 echo '</table>';
}
function sk_clean_avail($a){$o=[];foreach(sk_days() as $i=>$n){$r=(array)($a[$i]??[]);$f=preg_match('/^\d{2}:\d{2}$/',$r['from']??'')?$r['from']:'09:00';$t=preg_match('/^\d{2}:\d{2}$/',$r['to']??'')?$r['to']:'18:00';$o[$i]=['on'=>empty($r['on'])?0:1,'from'=>$f,'to'=>$t];}return $o;}
add_action('save_post',function($id){
 if(!isset($_POST['sk_meta_nonce'])||!wp_verify_nonce($_POST['sk_meta_nonce'],'sk_meta')||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||!current_user_can('edit_post',$id))return;
 $d=sk_meta_defs()[get_post_type($id)]??null;if(!$d)return;$in=(array)($_POST['sk_m']??[]);
 foreach($d as $k=>$f){$v=$in[$k]??'';
  switch($f[1]){
   case 'checkbox':$v=empty($in[$k])?'0':'1';break;
   case 'textarea':$v=sanitize_textarea_field($v);break;
   case 'number':$v=is_numeric($v)?0+$v:'';break;
   case 'gallery':$v=implode(',',array_filter(array_map('absint',explode(',',(string)$v))));break;
   case 'avail':$v=sk_clean_avail((array)$v);break;
   case 'trainers':$v=array_map('absint',(array)$v);break;
   case 'branchsel':$v=absint($v);break;
   case 'postsel':$v=absint($v);break;
   case 'daysel':$v=array_values(array_filter(array_map('absint',(array)$v),function($d){return $d>=1&&$d<=7;}));break;
   case 'time':$v=preg_match('/^\d{2}:\d{2}$/',(string)$v)?$v:'';break;
   case 'select':$v=array_key_exists($v,$f[2])?$v:array_key_first($f[2]);break;
   default:$v=sanitize_text_field($v);}
  update_post_meta($id,$k,$v);}
});

add_action('init',function(){
 register_taxonomy('branch_cat','branch',['labels'=>['name'=>'Branş Kategorileri','singular_name'=>'Branş Kategorisi','menu_name'=>'Branş Kategorileri','add_new_item'=>'Kategori ekle','edit_item'=>'Kategoriyi düzenle','all_items'=>'Tüm kategoriler'],
  'hierarchical'=>true,'public'=>true,'show_ui'=>true,'show_in_menu'=>true,'show_in_rest'=>true,'show_admin_column'=>true,'rewrite'=>['slug'=>'brans-kategori']]);
});
add_action('branch_cat_add_form_fields',function(){wp_nonce_field('sk_term','sk_term_nonce');echo '<div class="form-field"><label>Kategori görseli</label>';sk_input('sk_term[image]','image','');echo '<p>Ana sayfadaki kategori kartında ve görseli olmayan branşlarda kullanılır.</p></div><div class="form-field"><label>Sıra (küçük önce)</label>';sk_input('sk_term[order]','number','0');echo '</div>';});
add_action('branch_cat_edit_form_fields',function($t){wp_nonce_field('sk_term','sk_term_nonce');
 echo '<tr class="form-field"><th>Kategori görseli</th><td>';sk_input('sk_term[image]','image',get_term_meta($t->term_id,'sk_image',true));echo '</td></tr><tr class="form-field"><th>Sıra</th><td>';sk_input('sk_term[order]','number',get_term_meta($t->term_id,'sk_order',true));echo '</td></tr>';});
foreach(['created_branch_cat','edited_branch_cat'] as $h)add_action($h,function($id){
 if(!isset($_POST['sk_term_nonce'])||!wp_verify_nonce($_POST['sk_term_nonce'],'sk_term')||!current_user_can('manage_categories'))return;
 $in=(array)($_POST['sk_term']??[]);update_term_meta($id,'sk_image',esc_url_raw($in['image']??''));update_term_meta($id,'sk_order',absint($in['order']??0));});

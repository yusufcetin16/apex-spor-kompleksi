<?php
if(!defined('ABSPATH'))exit;
// Tüm kutular (kategori, branş, antrenör, salon, paket, tesis, üyelik) tek sayfada, satır satır düzenlenir
add_action('admin_menu',function(){add_submenu_page('sk-settings','Kartlar & Görseller','★ Kartlar & Görseller','edit_posts','sk-cards','sk_cards_page');},5);
add_action('admin_enqueue_scripts',function($h){
 if(strpos($h,'sk-cards')===false)return;wp_enqueue_media();
 wp_enqueue_script('sk-admin',get_theme_file_uri('assets/js/admin.js'),['jquery'],SK_VER,true);
 wp_localize_script('sk-admin','SKA',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('sk_cards')]);
 wp_register_style('sk-cards',false);wp_enqueue_style('sk-cards');
 wp_add_inline_style('sk-cards','.sk-row{display:grid;grid-template-columns:150px 1fr;gap:16px;background:#fff;border:1px solid #dcdcde;padding:12px;margin:0 0 10px;max-width:980px}.sk-rprev{width:150px;height:110px;background:#f0f0f1 center/cover;display:flex;align-items:center;justify-content:center;color:#787c82;font-size:12px}.sk-row input.sk-rtitle,.sk-row input.sk-rtext{width:100%;margin-bottom:6px}.sk-rmsg{display:none;color:#00a32a;margin-left:8px}.sk-cards-h{margin:28px 0 10px}');
});
function sk_cards_groups(){return ['term'=>'Kategoriler (Dövüş Sanatları, Takım Sporları…)','branch'=>'Branşlar','trainer'=>'Antrenörler','hall'=>'Salonlar & Alanlar','pt_package'=>'PT Paketleri','facility'=>'Tesis Olanakları','membership'=>'Üyelik Planları'];}
function sk_card_text_label($k){return ['trainer'=>'Uzmanlık alanı','hall'=>'Zemin / boyut','term'=>'Açıklama'][$k]??'Kısa açıklama (kartta görünür)';}
function sk_cards_page(){
 echo '<div class="wrap"><h1>Kartlar & Görseller</h1><p>Sitedeki her kutunun görselini, başlığını ve kısa yazısını buradan tek tek değiştirin. Her satırın kendi <b>Kaydet</b> butonu var. Ayrıntılı içerik (özgeçmiş, ücret, program…) için "Tam düzenle"yi kullanın.</p>';
 foreach(sk_cards_groups() as $k=>$title){echo '<h2 class="sk-cards-h">'.esc_html($title).'</h2>';
  if($k==='term'){$items=get_terms(['taxonomy'=>'branch_cat','hide_empty'=>false]);if(is_wp_error($items))$items=[];}
  else $items=get_posts(['post_type'=>$k,'numberposts'=>-1,'post_status'=>['publish','draft','private'],'orderby'=>'menu_order title','order'=>'ASC']);
  if(!$items)echo '<p>Kayıt yok.</p>';
  foreach($items as $it){
   if($k==='term'){$id=$it->term_id;$t=$it->name;$img=get_term_meta($id,'sk_image',true);$tx=$it->description;$edit=get_edit_term_link($id,'branch_cat','branch');}
   else{$id=$it->ID;$t=$it->post_title;$img=get_post_meta($id,'card_img',true);$edit=get_edit_post_link($id);
    $tx=$k==='trainer'?get_post_meta($id,'role',true):($k==='hall'?get_post_meta($id,'size',true):$it->post_excerpt);
    if(!$img&&has_post_thumbnail($id))$img=get_the_post_thumbnail_url($id,'medium');}
   echo '<div class="sk-row" data-id="'.(int)$id.'" data-kind="'.esc_attr($k).'"><div class="sk-rprev" style="'.($img?'background-image:url('.esc_url($img).')':'').'">'.($img?'':'Görsel yok').'</div><div>';
   echo '<input class="sk-rtitle" value="'.esc_attr($t).'" placeholder="Başlık"><input class="sk-rtext" value="'.esc_attr($tx).'" placeholder="'.esc_attr(sk_card_text_label($k)).'">';
   echo '<input type="hidden" class="sk-rimg-url" value="'.esc_attr($img).'"><button class="button sk-rimg">Görsel seç / değiştir</button> <button class="button sk-rclr">Görseli kaldır</button> <button class="button button-primary sk-rsave">Kaydet</button><span class="sk-rmsg"></span>';
   if($edit)echo ' <a href="'.esc_url($edit).'" style="margin-left:10px">Tam düzenle ↗</a>';
   echo '</div></div>';}}
 echo '</div>';
}
add_action('wp_ajax_sk_card_save',function(){
 check_ajax_referer('sk_cards','nonce');
 $id=absint($_POST['id']??0);$k=sanitize_key($_POST['kind']??'');$t=sanitize_text_field($_POST['title']??'');$tx=sanitize_textarea_field($_POST['text']??'');$img=esc_url_raw($_POST['img']??'');
 if(!isset(sk_cards_groups()[$k])||!$id||$t==='')wp_send_json_error();
 if($k==='term'){if(!current_user_can('manage_categories'))wp_send_json_error();wp_update_term($id,'branch_cat',['name'=>$t,'description'=>$tx]);update_term_meta($id,'sk_image',$img);wp_send_json_success();}
 if(get_post_type($id)!==$k||!current_user_can('edit_post',$id))wp_send_json_error();
 $a=['ID'=>$id,'post_title'=>$t];if(!in_array($k,['trainer','hall'],true))$a['post_excerpt']=$tx;wp_update_post($a);
 if($k==='trainer')update_post_meta($id,'role',sanitize_text_field($tx));if($k==='hall')update_post_meta($id,'size',sanitize_text_field($tx));
 update_post_meta($id,'card_img',$img);wp_send_json_success();
});
// Her kaydın düzenleme ekranına belirgin "Kart Görseli" kutusu + liste sütunu
$sk_pts=['trainer','branch','hall','pt_package','facility','membership'];
add_action('add_meta_boxes',function()use($sk_pts){foreach($sk_pts as $pt)add_meta_box('sk_card','Kart Görseli (listelerde ve sayfanın üstünde görünür)','sk_card_box',$pt,'normal','high');});
function sk_card_box($p){wp_nonce_field('sk_card','sk_card_nonce');sk_input('sk_card_img','image',get_post_meta($p->ID,'card_img',true));echo '<p class="description">Bu kaydın kutusunda ve sayfasında görünecek görsel. Boş bırakırsanız "Öne çıkan görsel" kullanılır.</p>';}
add_action('save_post',function($id){
 if(!isset($_POST['sk_card_nonce'])||!wp_verify_nonce($_POST['sk_card_nonce'],'sk_card')||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||!current_user_can('edit_post',$id))return;
 update_post_meta($id,'card_img',esc_url_raw($_POST['sk_card_img']??''));});
foreach($sk_pts as $pt){
 add_filter("manage_{$pt}_posts_columns",function($c){$n=[];foreach($c as $k=>$v){if($k==='title')$n['sk_thumb']='Görsel';$n[$k]=$v;}return $n;});
 add_action("manage_{$pt}_posts_custom_column",function($c,$id){if($c!=='sk_thumb')return;$u=get_post_meta($id,'card_img',true);echo $u?'<img src="'.esc_url($u).'" style="width:60px;height:45px;object-fit:cover">':(has_post_thumbnail($id)?get_the_post_thumbnail($id,[60,45]):'—');},10,2);
}

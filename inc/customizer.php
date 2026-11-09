<?php
if(!defined('ABSPATH'))exit;
function sk_clean($v,$f){
 switch($f[2]){
  case 'checkbox':return filter_var($v,FILTER_VALIDATE_BOOLEAN)?'1':'0';
  case 'select':return isset($f[4][$v])?$v:$f[3];
  case 'textarea':return sanitize_textarea_field($v);
  case 'url':case 'image':return esc_url_raw($v);
  case 'email':return sanitize_email($v);
  case 'color':return sanitize_hex_color($v)?:$f[3];
  case 'number':return is_numeric($v)?0+$v:$f[3];
  case 'html':return wp_kses($v,['iframe'=>['src'=>1,'width'=>1,'height'=>1,'style'=>1,'loading'=>1,'allowfullscreen'=>1,'referrerpolicy'=>1,'frameborder'=>1]]);
  default:return sanitize_text_field($v);}
}
add_action('customize_register',function($wp){
 if(!class_exists('SK_Links_Control')){
  class SK_Links_Control extends WP_Customize_Control{
   public $type='sk_links';
   public function render_content(){
    echo '<span class="customize-control-title">'.esc_html($this->label).'</span><p class="description">'.esc_html($this->description).'</p>';
    $l=['★ Kartlar & Görseller (her kutunun görseli tek tek)'=>'admin.php?page=sk-cards','Antrenörler (fotoğraf, özgeçmiş, belgeler)'=>'edit.php?post_type=trainer','Branşlar (MMA, boks, basketbol, voleybol…)'=>'edit.php?post_type=branch','Branş Kategorileri (kategori görselleri)'=>'edit-tags.php?taxonomy=branch_cat&post_type=branch',
     'Salonlar & Alanlar (ring, kafes, saha)'=>'edit.php?post_type=hall','PT Paketleri'=>'edit.php?post_type=pt_package','Tesis Olanakları'=>'edit.php?post_type=facility','Üyelik Planları'=>'edit.php?post_type=membership','Rezervasyonlar'=>'edit.php?post_type=booking','Menüler'=>'nav-menus.php'];
    foreach($l as $t=>$u)echo '<p><a class="button button-secondary" style="width:100%;text-align:left" target="_blank" href="'.esc_url(admin_url($u)).'">'.esc_html($t).' ↗</a></p>';
   }
  }
 }
 $wp->add_section('sk_s_content',['title'=>'★ İçerik Yönetimi (Antrenör, Branş, Salon…)','priority'=>1,'description'=>'Antrenör, branş, salon gibi kayıtlar burada düzenlenir.']);
 $wp->add_setting('sk_links_dummy',['type'=>'option','sanitize_callback'=>'__return_empty_string']);
 $wp->add_control(new SK_Links_Control($wp,'sk_links',['label'=>'Kayıtları düzenle','description'=>'Fotoğraf eklemek için kaydı açın, sağdaki "Öne çıkan görsel" kutusunu kullanın.','section'=>'sk_s_content','settings'=>'sk_links_dummy']));
 $g=[];foreach(sk_fields() as $k=>$f)$g[$f[0]][$k]=$f;$pr=10;
 foreach($g as $name=>$fs){$sid='sk_s_'.sanitize_title($name);$wp->add_section($sid,['title'=>$name,'priority'=>1]);
  foreach($fs as $k=>$f){$id="sk_options[$k]";$tr='refresh';
   if(in_array($k,sk_xkeys(),true)){$tr='postMessage';$wp->selective_refresh->add_partial("sk_o_$k",['selector'=>'[data-sk="'.$k.'"]','settings'=>[$id],'fallback_refresh'=>true,'render_callback'=>function()use($k){return nl2br(esc_html(sk_opt($k)));}]);}
   elseif(in_array($k,sk_mkeys(),true)){$tr='postMessage';$wp->selective_refresh->add_partial("sk_o_$k",['selector'=>'[data-sk="'.$k.'"]','settings'=>[$id],'fallback_refresh'=>true]);}
   $wp->add_setting($id,['type'=>'option','default'=>$f[3],'transport'=>$tr,'sanitize_callback'=>function($v)use($f){return sk_clean($v,$f);}]);
   $a=['label'=>$f[1],'section'=>$sid,'settings'=>$id];
   switch($f[2]){
    case 'image':$wp->add_control(new WP_Customize_Image_Control($wp,"sk_c_$k",$a));break;
    case 'color':$wp->add_control(new WP_Customize_Color_Control($wp,"sk_c_$k",$a));break;
    case 'textarea':case 'html':$wp->add_control("sk_c_$k",$a+['type'=>'textarea']);break;
    case 'checkbox':$wp->add_control("sk_c_$k",$a+['type'=>'checkbox']);break;
    case 'select':$wp->add_control("sk_c_$k",$a+['type'=>'select','choices'=>$f[4]??[]]);break;
    case 'number':$wp->add_control("sk_c_$k",$a+['type'=>'number']);break;
    case 'url':$wp->add_control("sk_c_$k",$a+['type'=>'url']);break;
    case 'email':$wp->add_control("sk_c_$k",$a+['type'=>'email']);break;
    default:$wp->add_control("sk_c_$k",$a+['type'=>'text']);}
  }}
});
add_action('admin_menu',function(){add_submenu_page('sk-settings','Canlı Düzenleyici','Canlı Düzenleyici (Özelleştir)','edit_theme_options','customize.php');},30);

// ---- Her kutu için ayrı bölüm; kutunun her parçasında ayrı kalem
function sk_post_save($kind,$id,$f,$v){
 if($kind==='term'){if($f==='title'&&$v!=='')wp_update_term($id,'branch_cat',['name'=>$v]);elseif($f==='text')wp_update_term($id,'branch_cat',['description'=>$v]);elseif($f==='img')update_term_meta($id,'sk_image',$v);elseif($f==='count')update_term_meta($id,'sk_count_txt',$v);return;}
 $t=get_post_type($id);
 switch($f){
  case 'title':if($v!=='')wp_update_post(['ID'=>$id,'post_title'=>$v]);break;
  case 'text':if($t==='trainer')update_post_meta($id,'role',$v);elseif($t==='hall')update_post_meta($id,'size',$v);else wp_update_post(['ID'=>$id,'post_excerpt'=>$v]);break;
  case 'img':update_post_meta($id,'card_img',$v);break;case 'link':update_post_meta($id,'card_link',$v);break;case 'btn':update_post_meta($id,'card_btn',$v);break;
  default:update_post_meta($id,$f,$v);}
}
function sk_card_extra($kind){
 $pt=['Fiyat yazısı (doluysa ücret yerine bu görünür, ör. 800 ₺ / seans)','text'];$xt=['Ek etiketler (virgülle ayırın)','text'];$hl=['Öne çıkar (kırmızı çerçeve)','checkbox'];
 switch($kind){
  case 'trainer':return ['price_ind'=>['Bireysel seans ücreti (sayı)','number'],'ind'=>['"Bireysel" etiketini göster','checkbox'],'grp'=>['"Grup" etiketini göster','checkbox'],'price_txt'=>$pt,'xtags'=>$xt];
  case 'hall':return ['sport'=>['Etiket: spor / alan türü','text'],'capacity'=>['Kapasite','text'],'price'=>['Saatlik ücret (sayı)','number'],'referee'=>['"Hakem hizmeti" etiketini göster','checkbox'],'price_txt'=>$pt,'xtags'=>$xt];
  case 'pt_package':return ['ptype'=>['Antrenman türü','select',['ind'=>'Bireysel','grp'=>'Grupsal']],'sessions'=>['Seans sayısı','number'],'price'=>['Paket ücreti (sayı)','number'],'validity'=>['Geçerlilik süresi','text'],'days'=>['Antrenman günleri','text'],'highlight'=>$hl,'price_txt'=>$pt,'xtags'=>$xt];
  case 'membership':return ['price'=>['Ücret (sayı)','number'],'period'=>['Süre (ör. aylık)','text'],'features'=>['Özellikler (her satıra bir tane)','textarea'],'highlight'=>$hl,'price_txt'=>$pt];
  case 'facility':return ['icon'=>['İkon (emoji)','text']];
  case 'branch':return ['age'=>['Etiket: yaş grubu','text'],'price'=>['Aylık ücret (sayı)','number'],'highlight'=>$hl,'price_txt'=>$pt,'xtags'=>$xt];
  case 'term':return ['count'=>['Sayaç yazısı (boşsa genel yazı; %d branş sayısını gösterir)','text']];}
 return [];}
function sk_card_parts($kind){
 $c=['img'=>['img'],'title'=>['title'],'text'=>['text']];
 switch($kind){
  case 'trainer':return $c+['tags'=>['ind','grp','xtags'],'price'=>['price_ind','price_txt'],'btn'=>['btn']];
  case 'hall':$c['text']=['text','capacity'];return $c+['tags'=>['sport','referee','xtags'],'price'=>['price','price_txt'],'btn'=>['btn']];
  case 'pt_package':return $c+['tags'=>['ptype','xtags'],'price'=>['price','sessions','price_txt'],'list'=>['sessions','validity','days'],'btn'=>['btn']];
  case 'membership':return $c+['price'=>['price','period','price_txt'],'list'=>['features'],'btn'=>['btn']];
  case 'facility':return $c+['icon'=>['icon']];
  case 'branch':return $c+['tags'=>['age','xtags'],'price'=>['price','price_txt'],'btn'=>['btn']];
  case 'term':return ['title'=>['title'],'text'=>['text'],'count'=>['count']];}
 return $c;}
add_action('customize_register','sk_cz_cards',20);
function sk_cz_cards($wp){
 if(!class_exists('SK_Post_Setting')){
  class SK_Post_Setting extends WP_Customize_Setting{
   public $type='sk_post';public $sk_kind='post';public $sk_id=0;public $sk_field='';
   public function value(){return $this->sk_kind==='term'?sk_term_raw($this->sk_id,$this->sk_field):sk_post_raw($this->sk_id,$this->sk_field);}
   public function preview(){if($this->is_previewed)return true;$this->is_previewed=true;add_filter('sk_post_field',[$this,'sk_filter'],10,4);return true;}
   public function sk_filter($val,$id,$field,$kind){if($kind===$this->sk_kind&&(int)$id===(int)$this->sk_id&&$field===$this->sk_field){$v=$this->post_value(null);if($v!==null)return $v;}return $val;}
   protected function update($value){sk_post_save($this->sk_kind,$this->sk_id,$this->sk_field,$value);return true;}
  }
 }
 $groups=['term'=>'Kartlar: Kategoriler','branch'=>'Kartlar: Branşlar','trainer'=>'Kartlar: Antrenörler','hall'=>'Kartlar: Salonlar & Alanlar','pt_package'=>'Kartlar: PT Paketleri','facility'=>'Kartlar: Tesis Olanakları','membership'=>'Kartlar: Üyelik Planları'];
 $cards=['branch'=>'sk_branch_card','trainer'=>'sk_trainer_card','hall'=>'sk_hall_card','pt_package'=>'sk_pkg_card','facility'=>'sk_fac_card','membership'=>'sk_mem_card'];
 foreach($groups as $kind=>$gt){
  $pn='sk_pn_'.$kind;$wp->add_panel($pn,['title'=>$gt,'description'=>'Her kutu ayrı bir bölümdür. Sitede kutunun her parçasındaki mavi kaleme de tıklayabilirsiniz.','priority'=>2]);
  $items=$kind==='term'?get_terms(['taxonomy'=>'branch_cat','hide_empty'=>false]):get_posts(['post_type'=>$kind,'numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC']);
  if(is_wp_error($items))continue;$n=0;
  foreach($items as $it){
   $id=$kind==='term'?$it->term_id:$it->ID;$name=$kind==='term'?$it->name:$it->post_title;$sk=$kind==='term'?'term':'post';$sid=($kind==='term'?'sk_tm_':'sk_p_').$id;
   $edit=$kind==='term'?get_edit_term_link($id,'branch_cat','branch'):get_edit_post_link($id,'raw');
   $wp->add_section($sid,['title'=>$name,'panel'=>$pn,'priority'=>10+$n++,'description'=>$kind==='term'?'':'Özgeçmiş, program gibi uzun içerikler için <a target="_blank" href="'.esc_url($edit).'">tam düzenle ↗</a>']);
   $textlab=$kind==='trainer'?'Uzmanlık alanı':($kind==='hall'?'Zemin / boyut':($kind==='term'?'Açıklama':'Kısa açıklama'));
   $fields=['title'=>['Başlık','text'],'text'=>[$textlab,'textarea'],'img'=>['Görsel','img']]+sk_card_extra($kind);
   if($kind!=='term')$fields+=['btn'=>['Buton yazısı (boşsa genel buton yazısı)','text'],'link'=>['Özel bağlantı (boşsa kutu kendi sayfasına gider)','url']];
   foreach($fields as $f=>$d){$setid="{$sid}_{$f}";$ty=$d[1];
    switch($ty){case 'number':$san=function($v){return is_numeric($v)?0+$v:'';};break;case 'checkbox':$san=function($v){return filter_var($v,FILTER_VALIDATE_BOOLEAN)?'1':'0';};break;
     case 'select':$op=$d[2];$san=function($v)use($op){return isset($op[$v])?$v:array_key_first($op);};break;
     case 'textarea':$san='sanitize_textarea_field';break;case 'img':case 'url':$san='esc_url_raw';break;default:$san='sanitize_text_field';}
    $wp->add_setting(new SK_Post_Setting($wp,$setid,['sk_kind'=>$sk,'sk_id'=>$id,'sk_field'=>$f,'default'=>'','transport'=>'postMessage','sanitize_callback'=>$san]));
    $a=['label'=>$d[0],'section'=>$sid,'settings'=>$setid];
    if($ty==='img')$wp->add_control(new WP_Customize_Image_Control($wp,"c_$setid",$a));
    elseif($ty==='select')$wp->add_control("c_$setid",$a+['type'=>'select','choices'=>$d[2]]);
    else $wp->add_control("c_$setid",$a+['type'=>$ty]);}
   // kutunun kendisi (bağlantı / görsel) + her parça için ayrı kalem
   $card=$cards[$kind]??'sk_cat_tile';
   $wp->selective_refresh->add_partial("sk_card_{$sk}_{$id}",['selector'=>'[data-sk-'.$sk.'="'.$id.'"]','settings'=>[$kind==='term'?"{$sid}_img":"{$sid}_link"],'container_inclusive'=>true,'fallback_refresh'=>true,
    'render_callback'=>function()use($card,$kind,$id){ob_start();if($kind==='term'){$t=get_term($id,'branch_cat');if($t&&!is_wp_error($t))$card($t);}else $card($id);return ob_get_clean();}]);
   foreach(sk_card_parts($kind) as $part=>$fs){
    $wp->selective_refresh->add_partial("sk_pf_{$sk}{$id}_{$part}",['selector'=>'[data-sk-pf="'.($kind==='term'?'t':'').$id.':'.$part.'"]','settings'=>array_map(function($f)use($sid){return "{$sid}_{$f}";},$fs),'container_inclusive'=>true,'fallback_refresh'=>true,
     'render_callback'=>function()use($kind,$id,$part){if($kind==='term'){$t=get_term($id,'branch_cat');return($t&&!is_wp_error($t))?sk_tpart($part,$t):'';}return sk_part($part,$id);}]);}
  }
 }
}

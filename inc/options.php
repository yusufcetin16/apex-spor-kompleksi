<?php
if(!defined('ABSPATH'))exit;
// [grup, etiket, tip, varsayılan]
function sk_fields(){static $c;if($c===null)$c=sk_regroup(array_merge(sk_base_fields(),sk_text_fields()));return $c;}
function sk_regroup($fs){
 $m=[
 'İletişim & Saatler'=>['phone','whatsapp','email','address','hours','map','show_map'],
 'Sosyal Medya'=>['instagram','facebook','youtube','tiktok'],
 'Menü Butonu'=>['cta_label','booking_url'],
 'Ana Sayfa: Kapak (Hero)'=>['hero_eyebrow','hero_title','hero_sub','hero_image','hero_video','hero_b1_label','hero_b1_url','hero_b2_label','hero_b2_url'],
 'Ana Sayfa: Hakkımızda'=>['show_about','about_title','about_text','about_image'],
 'Ana Sayfa: Bölüm Sırası'=>['home_order'],
 'Bölüm: Kategoriler'=>['show_cats','cats_title','cats_sub','bg_cats'],
 'Bölüm: Branşlar'=>['show_branches','branches_title','branches_sub','bg_branches'],
 'Bölüm: PT Eğitimleri'=>['show_pt','pt_title','pt_sub','pt_ind_title','pt_ind_text','pt_grp_title','pt_grp_text','bg_pt'],
 'Bölüm: Antrenörler'=>['show_trainers','trainers_title','trainers_sub','bg_trainers'],
 'Bölüm: Salonlar & Alanlar'=>['show_halls','halls_title','halls_sub','referee_note','bg_halls'],
 'Bölüm: Tesis Olanakları'=>['show_fac','fac_title','fac_sub','bg_facilities'],
 'Bölüm: Üyelik'=>['show_mem','mem_title','mem_sub','bg_membership'],
 'Bölüm: Alt Çağrı (CTA)'=>['cta_title','cta_text','bg_cta'],
 'Rezervasyon Kuralları'=>['booking_email','auto_approve','slot_min','open_hour','close_hour','ref_fee','max_days','currency','terms_note'],
 'Renkler & Yazı Tipleri'=>['heading_font','body_font','accent','bg','surface','text_col'],
 'Alt Bilgi (Footer)'=>['footer_text','kvkk_url'],
 'Erişilebilirlik'=>['show_a11y'],
 'İç Sayfalar: Üst Görsel'=>['page_head_img'],
 ];
 $pg=['branch'=>'Branşlar','trainer'=>'Antrenörler','hall'=>'Salonlar & Alanlar','pt_package'=>'PT Paketleri','facility'=>'Tesis Olanakları','membership'=>'Üyelik'];
 foreach($pg as $pt=>$n){$m['Sayfa: '.$n]=["ar_{$pt}_t","ar_{$pt}_s"];}
 $out=[];foreach($m as $g=>$keys)foreach($keys as $k)if(isset($fs[$k])){$fs[$k][0]=$g;$out[$k]=$fs[$k];}
 foreach($fs as $k=>$f)if(!isset($out[$k]))$out[$k]=$f;
 return $out;}
function sk_base_fields(){return [
'phone'=>['Genel','Telefon','text','+90 5xx xxx xx xx'],
'whatsapp'=>['Genel','WhatsApp (90 ile başlayan, boşluksuz)','text',''],
'email'=>['Genel','E-posta','email',''],
'address'=>['Genel','Adres','textarea','Adresinizi Spor Kompleksi > Ayarlar bölümünden düzenleyin.'],
'hours'=>['Genel','Çalışma saatleri','text','Her gün 07:00 - 23:00'],
'map'=>['Genel','Google Harita gömme kodu (iframe)','html',''],
'instagram'=>['Genel','Instagram adresi','url',''],'facebook'=>['Genel','Facebook adresi','url',''],'youtube'=>['Genel','YouTube adresi','url',''],'tiktok'=>['Genel','TikTok adresi','url',''],
'cta_label'=>['Genel','Menüdeki buton yazısı','text','Rezervasyon Yap'],
'booking_url'=>['Genel','Rezervasyon sayfası adresi (boşsa otomatik)','url',''],
'hero_eyebrow'=>['Ana Sayfa','Üst küçük yazı','text','Spor Kompleksi'],
'hero_title'=>['Ana Sayfa','Ana başlık','text','Güçlü Ol. Disiplinli Kal.'],
'hero_sub'=>['Ana Sayfa','Alt metin','textarea','Dövüş sanatları, PT eğitimleri, salonlar ve online rezervasyon tek çatı altında.'],
'hero_image'=>['Ana Sayfa','Kapak görseli','image',''],'hero_video'=>['Ana Sayfa','Kapak videosu adresi (mp4, isteğe bağlı)','url',''],
'hero_b1_label'=>['Ana Sayfa','1. buton yazısı','text','Randevu Al'],'hero_b1_url'=>['Ana Sayfa','1. buton adresi (boşsa PT randevu)','url',''],
'hero_b2_label'=>['Ana Sayfa','2. buton yazısı','text','Salon Kirala'],'hero_b2_url'=>['Ana Sayfa','2. buton adresi (boşsa salon kiralama)','url',''],
'show_about'=>['Ana Sayfa','Hakkımızda bölümünü göster','checkbox','0'],
'about_title'=>['Ana Sayfa','Hakkımızda başlığı','text','Hakkımızda'],'about_text'=>['Ana Sayfa','Hakkımızda metni','textarea',''],'about_image'=>['Ana Sayfa','Hakkımızda görseli','image',''],
'show_cats'=>['Bölümler','Branş kategorilerini göster (görselli kartlar)','checkbox','1'],'cats_title'=>['Bölümler','Kategori başlığı','text','Branş Kategorileri'],'cats_sub'=>['Bölümler','Kategori alt metni','text','Size uygun antrenman dünyasını seçin.'],
'home_order'=>['Bölümler','Ana sayfa bölüm sırası (virgülle: cats,branches,pt,trainers,halls,facilities,memberships)','text','cats,branches,pt,trainers,halls,facilities,memberships'],
'show_branches'=>['Bölümler','Branşları göster (MMA, boks vb.)','checkbox','1'],'branches_title'=>['Bölümler','Branş başlığı','text','Dövüş Sanatları & Branşlar'],'branches_sub'=>['Bölümler','Branş alt metni','text','MMA, boks ve daha fazlası; uzman antrenörlerle başlangıçtan ileri seviyeye.'],
'show_pt'=>['Bölümler','PT bölümünü göster','checkbox','1'],'pt_title'=>['Bölümler','PT başlığı','text','PT Eğitimleri'],'pt_sub'=>['Bölümler','PT alt metni','text','Bireysel ve grupsal antrenman paketleri.'],
'pt_ind_title'=>['Bölümler','Bireysel antrenman başlığı','text','Bireysel Antrenman'],'pt_ind_text'=>['Bölümler','Bireysel antrenman metni','textarea','Hedefinize özel program, birebir ilgi ve seans seans takip.'],
'pt_grp_title'=>['Bölümler','Grupsal antrenman başlığı','text','Grupsal Antrenman'],'pt_grp_text'=>['Bölümler','Grupsal antrenman metni','textarea','Arkadaşlarınızla küçük gruplarda, motivasyonu yüksek antrenmanlar.'],
'show_trainers'=>['Bölümler','Antrenörleri göster','checkbox','1'],'trainers_title'=>['Bölümler','Antrenör başlığı','text','Antrenörlerimiz'],'trainers_sub'=>['Bölümler','Antrenör alt metni','text','Uzmanlık alanlarına ve belgelerine göre eğitmeninizi seçin.'],
'show_halls'=>['Bölümler','Salonları göster','checkbox','1'],'halls_title'=>['Bölümler','Salon başlığı','text','Salon Kiralama'],'halls_sub'=>['Bölümler','Salon alt metni','text','Basketbol ve voleybol salonlarını saatlik kiralayın.'],
'referee_note'=>['Bölümler','Hakem hizmeti notu','text','İsteğe bağlı hakem ayarlama hizmeti sunuyoruz.'],
'show_fac'=>['Bölümler','Tesis olanaklarını göster','checkbox','1'],'fac_title'=>['Bölümler','Tesis başlığı','text','Tesis Olanakları'],'fac_sub'=>['Bölümler','Tesis alt metni','text','Antrenman öncesi ve sonrası ihtiyacınız olan her şey.'],
'show_mem'=>['Bölümler','Üyelikleri göster','checkbox','1'],'mem_title'=>['Bölümler','Üyelik başlığı','text','Üyelik'],'mem_sub'=>['Bölümler','Üyelik alt metni','text','Size uygun planı seçin, online başvurun.'],
'show_map'=>['Bölümler','Haritayı göster','checkbox','1'],
'cta_title'=>['Bölümler','Alt çağrı başlığı','text','Hemen Başla'],'cta_text'=>['Bölümler','Alt çağrı metni','text','Randevunuzu veya salon rezervasyonunuzu birkaç dakikada oluşturun.'],
'booking_email'=>['Rezervasyon','Bildirim e-postası (boşsa yönetici e-postası)','email',''],
'auto_approve'=>['Rezervasyon','Rezervasyonları otomatik onayla','checkbox','0'],
'slot_min'=>['Rezervasyon','PT seans süresi (dakika)','number','60'],
'open_hour'=>['Rezervasyon','Salon açılış saati (0-23)','number','8'],'close_hour'=>['Rezervasyon','Salon kapanış saati (1-24)','number','23'],
'ref_fee'=>['Rezervasyon','Hakem hizmeti ücreti (tek seferlik)','number','0'],'max_days'=>['Rezervasyon','En fazla kaç gün sonrasına rezervasyon','number','60'],
'currency'=>['Rezervasyon','Para birimi simgesi','text','₺'],'terms_note'=>['Rezervasyon','Form altı bilgilendirme notu','textarea','Rezervasyonunuz onaylandığında e-posta/telefon ile bilgilendirilirsiniz.'],
'bg_cats'=>['Bölüm Arka Plan Görselleri','Kategoriler bölümü arka planı','image',''],'bg_branches'=>['Bölüm Arka Plan Görselleri','Branşlar bölümü arka planı','image',''],'bg_pt'=>['Bölüm Arka Plan Görselleri','PT bölümü arka planı','image',''],'bg_trainers'=>['Bölüm Arka Plan Görselleri','Antrenörler bölümü arka planı','image',''],'bg_halls'=>['Bölüm Arka Plan Görselleri','Salonlar bölümü arka planı','image',''],'bg_facilities'=>['Bölüm Arka Plan Görselleri','Tesis bölümü arka planı','image',''],'bg_membership'=>['Bölüm Arka Plan Görselleri','Üyelik bölümü arka planı','image',''],'bg_cta'=>['Bölüm Arka Plan Görselleri','Alt çağrı bölümü arka planı','image',''],
'show_a11y'=>['Erişilebilirlik','Erişilebilirlik araç çubuğunu göster','checkbox','1'],
'heading_font'=>['Görünüm','Başlık yazı tipi','select','Barlow Condensed',['Barlow Condensed'=>'Barlow Condensed','Oswald'=>'Oswald','Bebas Neue'=>'Bebas Neue','Anton'=>'Anton','Teko'=>'Teko']],
'body_font'=>['Görünüm','Metin yazı tipi','select','Inter',['Inter'=>'Inter','Poppins'=>'Poppins','Roboto'=>'Roboto','Montserrat'=>'Montserrat','Open Sans'=>'Open Sans']],
'page_head_img'=>['Görünüm','Alt sayfa başlık görseli (tüm iç sayfaların üstü)','image',''],
'accent'=>['Görünüm','Vurgu rengi','color','#e11d2e'],'bg'=>['Görünüm','Arka plan rengi','color','#0b0b0d'],'surface'=>['Görünüm','Kart/bölüm rengi','color','#16161a'],'text_col'=>['Görünüm','Yazı rengi','color','#f3f3f5'],
'footer_text'=>['Görünüm','Alt bilgi metni','textarea','Spor, disiplin ve topluluk.'],'kvkk_url'=>['Görünüm','KVKK sayfası adresi','url',''],
];}
function sk_opt($k){static $o;if($o===null)$o=(array)get_option('sk_options',[]);$f=sk_fields();return(isset($o[$k])&&$o[$k]!=='')?$o[$k]:($f[$k][3]??'');}

function sk_input($n,$t,$v,$o=[]){
 $e=esc_attr($n);$u=uniqid('f');
 switch($t){
  case 'textarea':case 'html':echo '<textarea class="large-text" rows="3" name="'.$e.'">'.esc_textarea($v).'</textarea>';break;
  case 'checkbox':echo '<label><input type="checkbox" name="'.$e.'" value="1" '.checked($v,'1',false).'> Evet</label>';break;
  case 'select':echo '<select name="'.$e.'">';foreach($o as $k=>$l)echo '<option value="'.esc_attr($k).'" '.selected($v,$k,false).'>'.esc_html($l).'</option>';echo '</select>';break;
  case 'color':echo '<input type="color" name="'.$e.'" value="'.esc_attr($v).'">';break;
  case 'image':echo '<input type="text" class="regular-text" id="'.$u.'" name="'.$e.'" value="'.esc_attr($v).'"> <button class="button sk-pick" data-target="#'.$u.'">Görsel seç</button> <button class="button sk-clear" data-target="#'.$u.'">Temizle</button><div class="sk-thumbs">'.($v?'<img src="'.esc_url($v).'">':'').'</div>';break;
  case 'gallery':echo '<input type="hidden" id="'.$u.'" name="'.$e.'" value="'.esc_attr($v).'"><button class="button sk-pick" data-multi="1" data-target="#'.$u.'">Görselleri seç</button> <button class="button sk-clear" data-target="#'.$u.'">Temizle</button><div class="sk-thumbs">';foreach(array_filter(explode(',',(string)$v)) as $i)echo wp_get_attachment_image($i,'thumbnail');echo '</div>';break;
  default:echo '<input type="'.($t==='number'?'number':($t==='url'?'url':($t==='email'?'email':(in_array($t,['date','time'],true)?$t:'text')))).'" class="regular-text" name="'.$e.'" value="'.esc_attr($v).'" '.($t==='number'?'step="any"':'').'>';
 }
}
add_action('admin_menu',function(){
 add_menu_page('Spor Kompleksi','Spor Kompleksi','manage_options','sk-settings','sk_overview_page','dashicons-awards',3);
 add_submenu_page('sk-settings','Genel Bakış','Genel Bakış','manage_options','sk-settings','sk_overview_page');
},1);
add_action('admin_menu',function(){add_submenu_page('sk-settings','Ayarlar','Ayarlar','manage_options','sk-options','sk_settings_page');},40);
add_action('admin_init',function(){register_setting('sk_group','sk_options',['sanitize_callback'=>'sk_sanitize']);});
function sk_sanitize($in){
 $in=(array)$in;$full=!empty($in['__full']);unset($in['__full']);$out=$in;
 foreach(sk_fields() as $k=>$f){
  if(!$full&&!array_key_exists($k,$in))continue;
  $v=$in[$k]??'';
  switch($f[2]){
   case 'checkbox':$v=$full?(empty($in[$k])?'0':'1'):(($v==='0'||$v===0||$v===''||$v===false||$v===null)?'0':'1');break;
   case 'select':$v=isset($f[4][$v])?$v:$f[3];break;
   case 'textarea':$v=sanitize_textarea_field($v);break;
   case 'url':case 'image':$v=esc_url_raw($v);break;
   case 'email':$v=sanitize_email($v);break;
   case 'color':$v=sanitize_hex_color($v)?:$f[3];break;
   case 'number':$v=is_numeric($v)?0+$v:$f[3];break;
   case 'html':$v=wp_kses($v,['iframe'=>['src'=>1,'width'=>1,'height'=>1,'style'=>1,'loading'=>1,'allowfullscreen'=>1,'referrerpolicy'=>1,'frameborder'=>1]]);break;
   default:$v=sanitize_text_field($v);}
  $out[$k]=$v;}
 return $out;}
// Eski hatadan kapanmış "bölümü göster" ayarlarını bir kez varsayılana döndür
add_action('init',function(){
 if(get_option('sk_fix1'))return;update_option('sk_fix1',1);$o=(array)get_option('sk_options',[]);$ch=false;
 foreach(sk_fields() as $k=>$f)if($f[2]==='checkbox'&&$f[3]==='1'&&isset($o[$k])&&$o[$k]==='0'){unset($o[$k]);$ch=true;}
 if($ch)update_option('sk_options',$o);},1);
function sk_settings_page(){
 if(!current_user_can('manage_options'))return;
 $o=(array)get_option('sk_options',[]);$g=[];foreach(sk_fields() as $k=>$f)$g[$f[0]][$k]=$f;
 echo '<div class="wrap"><h1>Spor Kompleksi Ayarları</h1><p>Sitedeki tüm metin, görsel, renk, yazı tipi ve bölümleri buradan değiştirebilirsiniz. Antrenör, salon, branş, paket, tesis ve üyelik içerikleri bu menünün altındaki bölümlerden eklenir. Aynı ayarlar Görünüm > Özelleştir bölümünde canlı önizlemeyle de düzenlenebilir. Alt sayfalardaki buton/etiket yazıları "Metinler" gruplarındadır.</p><p>';
 foreach($g as $n=>$fs)echo '<a class="button" style="margin:0 4px 4px 0" href="#g-'.esc_attr(sanitize_title($n)).'">'.esc_html($n).'</a>';
 echo '</p><form method="post" action="options.php">';settings_fields('sk_group');echo '<input type="hidden" name="sk_options[__full]" value="1">';
 foreach($g as $name=>$fs){echo '<h2 class="title" id="g-'.esc_attr(sanitize_title($name)).'">'.esc_html($name).'</h2><table class="form-table">';
  foreach($fs as $k=>$f){$v=$o[$k]??$f[3];echo '<tr><th>'.esc_html($f[1]).'</th><td>';sk_input("sk_options[$k]",$f[2],$v,$f[4]??[]);echo '</td></tr>';}
  echo '</table>';}
 submit_button('Ayarları kaydet');echo '</form></div>';
}
add_action('admin_enqueue_scripts',function($h){
 $s=get_current_screen();$ok=(strpos($h,'sk-options')!==false)||($s&&($s->taxonomy??'')==='branch_cat')||($s&&in_array($s->post_type,['trainer','hall','pt_package','facility','membership','branch'],true));
 if(!$ok)return;wp_enqueue_media();wp_enqueue_script('sk-admin',get_theme_file_uri('assets/js/admin.js'),['jquery'],SK_VER,true);
 wp_register_style('sk-admin',false);wp_enqueue_style('sk-admin');
 wp_add_inline_style('sk-admin','.sk-thumbs img{width:70px;height:70px;object-fit:cover;margin:6px 6px 0 0}.sk-mt th{width:220px;text-align:left;vertical-align:top;padding:8px 0}.sk-mt td{padding:6px 0}');
});

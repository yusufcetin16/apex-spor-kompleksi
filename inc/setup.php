<?php
if(!defined('ABSPATH'))exit;
add_action('after_switch_theme',function(){
 sk_register_cpts();
 if(!get_option('sk_seeded')){sk_seed();update_option('sk_seeded',1);}
 flush_rewrite_rules();
});
function sk_seed(){
 $av=[];for($i=1;$i<=7;$i++)$av[$i]=['on'=>$i<7?1:0,'from'=>'09:00','to'=>'18:00'];
 $mk=function($pt,$title,$meta=[],$extra=[])use(&$mk){return wp_insert_post(array_merge(['post_type'=>$pt,'post_title'=>$title,'post_status'=>'publish','meta_input'=>$meta],$extra));};
 $t1=$mk('trainer','Örnek Antrenör 1',['role'=>'Güç & Kondisyon','official'=>'Spor Bilimleri Fakültesi, Antrenörlük Belgesi','exp'=>8,'ind'=>'1','grp'=>'1','price_ind'=>800,'price_grp'=>350,'certs'=>"Antrenörlük Belgesi\nİlk Yardım Sertifikası",'avail'=>$av],['post_content'=>'Özgeçmişinizi buraya yazın. Bu içerik Antrenörler > Düzenle bölümünden değiştirilir.']);
 $t2=$mk('trainer','Örnek Antrenör 2',['role'=>'Fonksiyonel Antrenman','official'=>'Resmî Antrenör Lisansı','exp'=>5,'ind'=>'1','grp'=>'0','price_ind'=>700,'certs'=>'Fonksiyonel Antrenör Sertifikası','avail'=>$av],['post_content'=>'Özgeçmiş metni.']);
 $mk('pt_package','Başlangıç Paketi',['ptype'=>'ind','sessions'=>8,'price'=>5600,'validity'=>'2 ay','days'=>'Hafta içi','trainers'=>[$t1,$t2],'highlight'=>'0'],['post_content'=>'Ölçüm, program hazırlama ve seans takibi.']);
 $mk('pt_package','Dönüşüm Paketi',['ptype'=>'ind','sessions'=>24,'price'=>15600,'validity'=>'4 ay','days'=>'Haftada 2-3 gün','trainers'=>[$t1],'highlight'=>'1'],['post_content'=>'Beslenme önerisi ve düzenli ölçümler dahil.']);
 $mk('pt_package','Mini Grup (4 Kişi)',['ptype'=>'grp','sessions'=>12,'price'=>3600,'validity'=>'6 hafta','days'=>'Salı-Perşembe','trainers'=>[$t1],'highlight'=>'0']);
 $mk('hall','Basketbol Salonu 1',['sport'=>'Basketbol','price'=>1200,'capacity'=>'10-20 kişi','size'=>'Parke zemin','referee'=>'1','features'=>"Skorbord\nAydınlatma\nSoyunma odası erişimi"]);
 $mk('hall','Basketbol Salonu 2',['sport'=>'Basketbol','price'=>1200,'capacity'=>'10-20 kişi','size'=>'Parke zemin','referee'=>'1','features'=>"Skorbord\nAydınlatma"]);
 $mk('hall','Voleybol Salonu',['sport'=>'Voleybol','price'=>1000,'capacity'=>'12-24 kişi','size'=>'Voleybol zemini','referee'=>'1','features'=>"Ayarlanabilir file\nAydınlatma"]);
 foreach([['🚿','Soyunma Odası & Duş','Temiz, geniş ve dolaplı soyunma odaları ile sıcak duş.'],['🅿️','Otopark','Üyelerimiz ve salon kiralayanlar için ücretsiz otopark.'],['☕','Kafe','Antrenman sonrası protein, kahve ve atıştırmalıklar.']] as $i=>$f)$mk('facility',$f[1],['icon'=>$f[0]],['post_excerpt'=>$f[2],'menu_order'=>$i]);
 $mk('membership','Aylık Üyelik',['price'=>1500,'period'=>'aylık','features'=>"Tüm tesis kullanımı\nSoyunma odası & duş\nGrup dersleri",'highlight'=>'0']);
 $mk('membership','Yıllık Üyelik',['price'=>12000,'period'=>'yıllık','features'=>"Tüm tesis kullanımı\nSoyunma odası & duş\nGrup dersleri\nÜcretsiz 1 PT seansı",'highlight'=>'1']);
 $pg=wp_insert_post(['post_type'=>'page','post_title'=>'Rezervasyon','post_name'=>'rezervasyon','post_status'=>'publish','post_content'=>'[sk_booking]']);
 $m=wp_create_nav_menu('Ana Menü');
 if(!is_wp_error($m)){$o=0;
  $items=[['Ana Sayfa',home_url('/')],['PT Eğitimleri',get_post_type_archive_link('pt_package')],['Antrenörler',get_post_type_archive_link('trainer')],['Salonlar',get_post_type_archive_link('hall')],['Tesis',get_post_type_archive_link('facility')],['Üyelik',get_post_type_archive_link('membership')],['Rezervasyon',get_permalink($pg)]];
  foreach($items as $it)wp_update_nav_menu_item($m,0,['menu-item-title'=>$it[0],'menu-item-url'=>$it[1],'menu-item-status'=>'publish','menu-item-type'=>'custom','menu-item-position'=>++$o]);
  $loc=get_theme_mod('nav_menu_locations',[]);$loc['primary']=$m;set_theme_mod('nav_menu_locations',$loc);}
}

// Dövüş sanatları branşları ve örnek antrenörler (bir kez, mevcut kurulumlara da eklenir)
add_action('admin_init','sk_seed_branches');
function sk_seed_branches(){
 if(get_option('sk_seeded_b'))return;update_option('sk_seeded_b',1);
 $av=[];for($i=1;$i<=7;$i++)$av[$i]=['on'=>$i<7?1:0,'from'=>'10:00','to'=>'20:00'];
 $mk=function($pt,$t,$m=[],$e=[]){return wp_insert_post(array_merge(['post_type'=>$pt,'post_title'=>$t,'post_status'=>'publish','meta_input'=>$m],$e));};
 $a=$mk('trainer','Örnek Dövüş Antrenörü 1',['role'=>'MMA & Brezilya Jiu-Jitsu','official'=>'Federasyon Antrenör Lisansı','exp'=>10,'ind'=>'1','grp'=>'1','price_ind'=>900,'price_grp'=>400,'certs'=>"MMA Antrenörlük Belgesi\nİlk Yardım Sertifikası",'avail'=>$av],['post_content'=>'Müsabaka geçmişinizi ve özgeçmişinizi buraya yazın.']);
 $b=$mk('trainer','Örnek Dövüş Antrenörü 2',['role'=>'Boks, Kick Boks & Muay Thai','official'=>'Federasyon Antrenör Lisansı','exp'=>8,'ind'=>'1','grp'=>'1','price_ind'=>850,'price_grp'=>380,'certs'=>"Boks Antrenörlük Belgesi\nKick Boks Hakemlik Belgesi",'avail'=>$av],['post_content'=>'Özgeçmiş metni.']);
 $br=[['MMA',[$a],'Vuruş, güreş ve yer mücadelesini birleştiren tam kapsamlı dövüş sanatı.','Pzt-Çar-Cum 19:00-20:30','Başlangıçtan ileriye','14+',1800,'1'],
  ['Boks',[$b],'Ayak oyunu, yumruk teknikleri ve kondisyon odaklı klasik boks antrenmanı.','Sal-Per 19:00-20:00','Tüm seviyeler','12+',1500,'0'],
  ['Kick Boks',[$b],'Yumruk ve tekme kombinasyonlarıyla yüksek tempolu antrenman.','Pzt-Çar 20:00-21:00','Tüm seviyeler','12+',1500,'0'],
  ['Muay Thai',[$b],'Sekiz uzvun sanatı: yumruk, tekme, diz ve dirsek teknikleri.','Sal-Per 20:00-21:00','Başlangıçtan ileriye','14+',1600,'0'],
  ['Brezilya Jiu-Jitsu',[$a],'Yer mücadelesi, kilit ve boğma teknikleri.','Cmt 11:00-12:30','Başlangıçtan ileriye','14+',1600,'0']];
 foreach($br as $i=>$r)$mk('branch',$r[0],['cat'=>'Dövüş Sanatları','trainers'=>$r[1],'schedule'=>$r[3]."\nCumartesi deneme dersi: 11:00",'level'=>$r[4],'age'=>$r[5],'price'=>$r[6],'gear'=>'Spor kıyafet','highlight'=>$r[7]],['post_content'=>$r[2],'menu_order'=>$i]);
 $m=wp_get_nav_menu_object('Ana Menü');
 if($m)wp_update_nav_menu_item($m->term_id,0,['menu-item-title'=>'Branşlar','menu-item-url'=>get_post_type_archive_link('branch'),'menu-item-status'=>'publish','menu-item-type'=>'custom','menu-item-position'=>2]);
 flush_rewrite_rules();
}

// v1.2: branş kategorileri + basketbol/voleybol + boks ringi / MMA kafesi + antrenörler ve PT paketleri
add_action('admin_init','sk_migrate_v2');
function sk_migrate_v2(){
 if(get_option('sk_mig2'))return;update_option('sk_mig2',1);
 foreach(['Dövüş Sanatları'=>'Boks, MMA, kick boks ve daha fazlası.','Takım Sporları'=>'Basketbol ve voleybol; takım ve bireysel gelişim.','Fitness & Kardiyo'=>'','Stüdyo & Esneklik'=>'','Çocuk'=>''] as $n=>$d)if(!term_exists($n,'branch_cat'))wp_insert_term($n,'branch_cat',['description'=>$d]);
 foreach(get_posts(['post_type'=>'branch','numberposts'=>-1,'post_status'=>'any']) as $b){$c=get_post_meta($b->ID,'cat',true);if($c&&!wp_get_object_terms($b->ID,'branch_cat'))wp_set_object_terms($b->ID,$c,'branch_cat');}
}
add_action('admin_init','sk_seed_v3');
function sk_seed_v3(){
 if(!get_option('sk_mig2')||get_option('sk_seeded_c'))return;update_option('sk_seeded_c',1);
 $find=function($pt,$t){foreach(get_posts(['post_type'=>$pt,'numberposts'=>-1,'post_status'=>'any']) as $p)if($p->post_title===$t)return $p->ID;return 0;};
 $mk=function($pt,$t,$m=[],$e=[]){return wp_insert_post(array_merge(['post_type'=>$pt,'post_title'=>$t,'post_status'=>'publish','meta_input'=>$m],$e));};
 $av=[];for($i=1;$i<=7;$i++)$av[$i]=['on'=>$i<7?1:0,'from'=>'10:00','to'=>'21:00'];
 $tb=$mk('trainer','Örnek Basketbol Antrenörü',['role'=>'Basketbol - Bireysel Gelişim & Takım','official'=>'Basketbol Antrenörlük Belgesi','exp'=>9,'ind'=>'1','grp'=>'1','price_ind'=>800,'price_grp'=>350,'certs'=>"Basketbol Federasyonu Antrenör Belgesi\nİlk Yardım Sertifikası",'avail'=>$av],['post_content'=>'Özgeçmiş metni.']);
 $tv=$mk('trainer','Örnek Voleybol Antrenörü',['role'=>'Voleybol - Teknik & Takım','official'=>'Voleybol Antrenörlük Belgesi','exp'=>7,'ind'=>'1','grp'=>'1','price_ind'=>750,'price_grp'=>330,'certs'=>"Voleybol Federasyonu Antrenör Belgesi",'avail'=>$av],['post_content'=>'Özgeçmiş metni.']);
 $bb=$mk('branch','Basketbol',['trainers'=>[$tb],'schedule'=>"Pzt-Çar 18:00-19:30\nCmt 10:00-11:30",'level'=>'Tüm seviyeler','age'=>'8+','price'=>1400,'gear'=>'Spor ayakkabı','highlight'=>'0'],['post_content'=>'Teknik, şut ve takım oyunu; bireysel gelişim ve takım antrenmanları.','menu_order'=>10]);
 $bv=$mk('branch','Voleybol',['trainers'=>[$tv],'schedule'=>"Sal-Per 18:00-19:30\nCmt 12:00-13:30",'level'=>'Tüm seviyeler','age'=>'10+','price'=>1400,'gear'=>'Dizlik, spor ayakkabı','highlight'=>'0'],['post_content'=>'Pas, servis, hücum ve savunma; takım ve bireysel gelişim.','menu_order'=>11]);
 wp_set_object_terms($bb,'Takım Sporları','branch_cat');wp_set_object_terms($bv,'Takım Sporları','branch_cat');
 $mma=$find('branch','MMA');$box=$find('branch','Boks');$d1=$find('trainer','Örnek Dövüş Antrenörü 1');$d2=$find('trainer','Örnek Dövüş Antrenörü 2');
 $mk('pt_package','Basketbol Bireysel Gelişim',['ptype'=>'ind','sessions'=>8,'price'=>5600,'validity'=>'2 ay','days'=>'Esnek','trainers'=>[$tb],'branch'=>$bb,'highlight'=>'0'],['post_content'=>'Şut, top kontrolü ve oyun okuma çalışmaları.']);
 $mk('pt_package','Voleybol Mini Grup',['ptype'=>'grp','sessions'=>12,'price'=>3600,'validity'=>'6 hafta','days'=>'Salı-Perşembe','trainers'=>[$tv],'branch'=>$bv,'highlight'=>'0']);
 if($mma&&$d1)$mk('pt_package','MMA Bireysel PT',['ptype'=>'ind','sessions'=>8,'price'=>7200,'validity'=>'2 ay','days'=>'Esnek','trainers'=>[$d1],'branch'=>$mma,'highlight'=>'1'],['post_content'=>'Birebir teknik, pedwork ve sparring hazırlığı.']);
 if($box&&$d2)$mk('pt_package','Boks Bireysel PT',['ptype'=>'ind','sessions'=>8,'price'=>6800,'validity'=>'2 ay','days'=>'Esnek','trainers'=>[$d2],'branch'=>$box,'highlight'=>'0'],['post_content'=>'Ayak oyunu, kombinasyon ve kondisyon.']);
 $mk('hall','Boks Ringi',['sport'=>'Boks','price'=>800,'capacity'=>'6-10 kişi','size'=>'Standart ring','referee'=>'1','features'=>"Resmî ölçü ring\nTorba ve pedwork alanı\nAntrenör / hakem desteği"],['post_content'=>'Sparring ve özel antrenmanlar için saatlik kiralanabilir boks ringi.']);
 $mk('hall','MMA Kafesi',['sport'=>'MMA','price'=>900,'capacity'=>'6-12 kişi','size'=>'Sekizgen kafes','referee'=>'1','features'=>"Sekizgen kafes\nYer mücadelesi minderi\nAntrenör / hakem desteği"],['post_content'=>'MMA sparring ve takım antrenmanları için saatlik kiralanabilir kafes.']);
 flush_rewrite_rules();
}

add_action('admin_init','sk_migrate_v4');
function sk_migrate_v4(){
 if(!get_option('sk_seeded_c')||get_option('sk_mig4'))return;update_option('sk_mig4',1);
 $m=wp_get_nav_menu_object('Ana Menü');if(!$m)return;
 $items=wp_get_nav_menu_items($m->term_id);$par=0;foreach((array)$items as $i)if($i->title==='Branşlar')$par=$i->ID;
 if(!$par)return;foreach($items as $i)if((int)$i->menu_item_parent===$par)return;
 foreach(sk_cats() as $t)wp_update_nav_menu_item($m->term_id,0,['menu-item-title'=>$t->name,'menu-item-object'=>'branch_cat','menu-item-object-id'=>$t->term_id,'menu-item-type'=>'taxonomy','menu-item-status'=>'publish','menu-item-parent-id'=>$par]);
}

// v1.8: üye paneli + ders programı sayfaları, üyelik süreleri, örnek dersler, menü
add_action('admin_init','sk_migrate_v5');
function sk_migrate_v5(){
 if(get_option('sk_mig5'))return;update_option('sk_mig5',1);
 foreach([['Üye Paneli','uye-paneli','[sk_uye_paneli]'],['Ders Programı','ders-programi','[sk_ders_programi]']] as $p)if(!get_page_by_path($p[1]))wp_insert_post(['post_type'=>'page','post_title'=>$p[0],'post_name'=>$p[1],'post_status'=>'publish','post_content'=>$p[2]]);
 foreach(sk_q('membership') as $m)if(sk_meta($m->ID,'days')==='')update_post_meta($m->ID,'days',stripos($m->post_title,'yıllık')!==false?365:30);
 if(!sk_q('lesson',1)){$d=[[1,3,5],[2,4]];$i=0;
  foreach(sk_q('branch',6) as $b){$tr=sk_branch_trainers($b->ID);$k=$i%2;
   wp_insert_post(['post_type'=>'lesson','post_title'=>$b->post_title.' Grubu','post_status'=>'publish','meta_input'=>['branch'=>$b->ID,'trainer'=>$tr?$tr[0]:0,'days'=>$d[$k],'start'=>$k?'20:00':'19:00','end'=>$k?'21:00':'20:30','capacity'=>20]]);$i++;}}
 $m=wp_get_nav_menu_object('Ana Menü');
 if($m){$par=0;$have=[];foreach((array)wp_get_nav_menu_items($m->term_id) as $it){if($it->title==='Branşlar')$par=$it->ID;$have[]=$it->title;}
  $add=function($title,$page,$parent)use($m){wp_update_nav_menu_item($m->term_id,0,['menu-item-title'=>$title,'menu-item-object'=>'page','menu-item-object-id'=>$page->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent]);};
  $pg=get_page_by_path('ders-programi');if($pg&&!in_array('Ders Programı',$have))$add('Ders Programı',$pg,$par);
  $pu=get_page_by_path('uye-paneli');if($pu&&!in_array('Üye Girişi',$have))$add('Üye Girişi',$pu,0);}
}

// v1.9: üye ol sayfası, deneme dersi satırlarını temizle, rezervasyon saat anahtarı
add_action('init',function(){
 if(get_option('sk_bf1'))return;update_option('sk_bf1',1);
 foreach(get_posts(['post_type'=>'booking','numberposts'=>-1,'post_status'=>'any','fields'=>'ids']) as $id){$t=get_post_meta($id,'type',true);$d=get_post_meta($id,'date',true);$r=(int)get_post_meta($id,'ref_id',true);if(in_array($t,['pt','hall'],true)&&$d&&$r)update_post_meta($id,'slotkey',sk_slotkey($r,$d));}
},3);
add_action('admin_init','sk_migrate_v6');
function sk_migrate_v6(){
 if(get_option('sk_mig6'))return;update_option('sk_mig6',1);
 if(!get_page_by_path('uye-ol'))wp_insert_post(['post_type'=>'page','post_title'=>'Üye Ol','post_name'=>'uye-ol','post_status'=>'publish','post_content'=>'[sk_uye_ol]']);
 $m=wp_get_nav_menu_object('Ana Menü');
 if($m){$par=0;$have=[];foreach((array)wp_get_nav_menu_items($m->term_id) as $it){if($it->title==='Üyelik')$par=$it->ID;$have[]=$it->title;}
  $pg=get_page_by_path('uye-ol');if($pg&&!in_array('Üye Ol',$have))wp_update_nav_menu_item($m->term_id,0,['menu-item-title'=>'Üye Ol','menu-item-object'=>'page','menu-item-object-id'=>$pg->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$par]);}
 foreach(get_posts(['post_type'=>'branch','numberposts'=>-1,'post_status'=>'any']) as $b){$s=(string)get_post_meta($b->ID,'schedule',true);
  $n=implode("\n",array_filter(array_map('trim',preg_split('/\R/',$s)),function($l){return stripos($l,'deneme')===false;}));if($n!==trim($s))update_post_meta($b->ID,'schedule',$n);}
}

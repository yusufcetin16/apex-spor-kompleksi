<?php
if(!defined('ABSPATH'))exit;
define('SK_VER','1.9.3');
foreach(['options','texts','keys','customizer','cards','finance','members','signup','adminhome','cpt','booking','render','setup'] as $f)require_once get_theme_file_path("inc/$f.php");

add_action('after_setup_theme',function(){
 add_theme_support('title-tag');add_theme_support('post-thumbnails');
 add_theme_support('custom-logo',['height'=>60,'width'=>240,'flex-width'=>true,'flex-height'=>true]);
 add_theme_support('html5',['search-form','gallery','caption','style','script']);
 register_nav_menus(['primary'=>'Ana Menü','footer'=>'Alt Menü']);
 add_image_size('sk-card',720,540,true);
});
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('sk-fonts',sk_font_url(),[],null);
 wp_enqueue_style('sk-style',get_stylesheet_uri(),[],SK_VER);
 wp_add_inline_style('sk-style',sk_root_css());
 wp_enqueue_script('sk-main',get_theme_file_uri('assets/js/main.js'),[],SK_VER,['in_footer'=>true,'strategy'=>'defer']);
 wp_localize_script('sk-main','SK',['ajax'=>admin_url('admin-ajax.php'),'hint'=>sk_opt('m_hint'),'none'=>sk_opt('m_none'),'wait'=>sk_opt('m_wait'),'pick'=>sk_opt('m_pick'),'full'=>sk_opt('m_full'),'show'=>sk_opt('s_show'),'hide'=>sk_opt('s_hide')]);
});
function sk_meta($id,$k){return get_post_meta($id,$k,true);}
function sk_lines($t){return array_values(array_filter(array_map('trim',preg_split('/\R/',(string)$t))));}
function sk_money($n){return($n===''||$n===null||!is_numeric($n))?'':number_format_i18n((float)$n,0).' '.sk_opt('currency');}
function sk_booking_url($a=[]){$u=sk_opt('booking_url');if(!$u){$p=get_page_by_path('rezervasyon');$u=$p?get_permalink($p):home_url('/rezervasyon/');}return $a?add_query_arg($a,$u):$u;}
function sk_gallery($csv){$o='';foreach(array_filter(explode(',',(string)$csv)) as $i){$o.='<a href="'.esc_url(wp_get_attachment_url($i)).'" target="_blank" rel="noopener">'.wp_get_attachment_image($i,'sk-card',false,['loading'=>'lazy']).'</a>';}return $o?'<div class="gal">'.$o.'</div>':'';}
function sk_nav_fallback(){echo '<ul><li><a href="'.esc_url(home_url('/')).'">Ana Sayfa</a></li></ul>';}

function sk_t($k){return sk_opt($k);}
function sk_font_names(){
 $h=sk_opt('heading_font');$b=sk_opt('body_font');
 return [in_array($h,['Barlow Condensed','Oswald','Bebas Neue','Anton','Teko'],true)?$h:'Barlow Condensed',in_array($b,['Inter','Poppins','Roboto','Montserrat','Open Sans'],true)?$b:'Inter'];}
function sk_font_url(){$m=['Barlow Condensed'=>'Barlow+Condensed:wght@600;700;800','Oswald'=>'Oswald:wght@500;700','Bebas Neue'=>'Bebas+Neue','Anton'=>'Anton','Teko'=>'Teko:wght@500;700','Inter'=>'Inter:wght@400;500;600','Poppins'=>'Poppins:wght@400;500;600','Roboto'=>'Roboto:wght@400;500;700','Montserrat'=>'Montserrat:wght@400;500;600','Open Sans'=>'Open+Sans:wght@400;600'];
 $q=[];foreach(array_unique(sk_font_names()) as $n)$q[]='family='.$m[$n];return 'https://fonts.googleapis.com/css2?'.implode('&',$q).'&display=swap';}
function sk_root_css(){
 $c=function($k,$d){$v=sanitize_hex_color(sk_opt($k));return $v?:$d;};$f=sk_font_names();
 return ':root{--fh:\''.$f[0].'\',Impact,sans-serif;--fb:\''.$f[1].'\',system-ui,sans-serif;--accent:'.$c('accent','#e11d2e').';--bg:'.$c('bg','#0b0b0d').';--surface:'.$c('surface','#16161a').';--text:'.$c('text_col','#f3f3f5').'}';}

add_action('pre_get_posts',function($q){if(is_admin()||!$q->is_main_query())return;
 if($q->is_post_type_archive(['trainer','hall','pt_package','facility','membership','branch'])||$q->is_tax('branch_cat')){$q->set('orderby','menu_order title');$q->set('order','ASC');$q->set('posts_per_page',24);
  if($q->is_post_type_archive('trainer')&&!empty($_GET['brans']))$q->set('post__in',sk_branch_trainers(absint($_GET['brans']))?:[0]);
  if($q->is_post_type_archive('trainer')&&!empty($_GET['kat']))$q->set('post__in',sk_term_trainers(absint($_GET['kat']))?:[0]);}});

add_action('init',function(){if(get_option('sk_rw')!==SK_VER){flush_rewrite_rules(false);update_option('sk_rw',SK_VER);}},99);

// ---- Hız: gereksiz başlık çıktıları, blok CSS (liste/ana sayfada), yazı tipi ön bağlantısı
add_action('init',function(){
 remove_action('wp_head','print_emoji_detection_script',7);remove_action('wp_print_styles','print_emoji_styles');
 remove_action('wp_head','wp_generator');remove_action('wp_head','rsd_link');remove_action('wp_head','wlwmanifest_link');remove_action('wp_head','wp_shortlink_wp_head');
 remove_action('wp_head','wp_oembed_add_discovery_links');remove_action('wp_head','rest_output_link_wp_head');});
add_action('wp_enqueue_scripts',function(){if(is_singular()&&!is_front_page())return;foreach(['wp-block-library','wp-block-library-theme','global-styles','classic-theme-styles'] as $h)wp_dequeue_style($h);},100);
add_filter('wp_resource_hints',function($u,$r){if($r==='preconnect'){$u[]='https://fonts.googleapis.com';$u[]=['href'=>'https://fonts.gstatic.com','crossorigin'=>'anonymous'];}return $u;},10,2);
// ---- Erişilebilirlik: geçerli menü öğesi
add_filter('nav_menu_link_attributes',function($a,$i){if(!empty($i->current))$a['aria-current']='page';return $a;},10,2);

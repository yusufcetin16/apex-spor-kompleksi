<?php
if(!defined('ABSPATH'))exit;
// ===== Genel Bakış (Spor Kompleksi ana sayfası) + pano özeti =====
function sk_overview_page(){
 if(!current_user_can('manage_options'))return;$c=sk_member_counts();
 $btn=[['Antrenör ekle','post-new.php?post_type=trainer'],['Branş ekle','post-new.php?post_type=branch'],['Ders ekle','post-new.php?post_type=lesson'],['Salon / alan ekle','post-new.php?post_type=hall'],['PT paketi ekle','post-new.php?post_type=pt_package'],['Tesis olanağı ekle','post-new.php?post_type=facility'],['Üyelik planı ekle','post-new.php?post_type=membership'],['Üye ekle / yenile','admin.php?page=sk-members#sk-form'],['Gelir / gider ekle','post-new.php?post_type=sk_entry']];
 $lnk=[['Kartlar & Görseller','admin.php?page=sk-cards'],['Branş Kategorileri','edit-tags.php?taxonomy=branch_cat&post_type=branch'],['Rezervasyonlar','edit.php?post_type=booking'],['Üyeler','admin.php?page=sk-members'],['Bütçe & Kazanç raporu','admin.php?page=sk-finance'],['Site ayarları','admin.php?page=sk-options'],['Canlı düzenleyici (Özelleştir)','customize.php'],['Menüler','nav-menus.php']];
 echo '<div class="wrap"><h1>Spor Kompleksi — Genel Bakış</h1><p>Sitedeki her şeyi buradan ekleyip düzenleyebilirsiniz.</p><h2>Hızlı ekle</h2><p>';
 foreach($btn as $b)echo '<a class="button button-primary button-large" style="margin:0 8px 8px 0" href="'.esc_url(admin_url($b[1])).'">+ '.esc_html($b[0]).'</a>';
 echo '</p><h2>Yönet</h2><p>';foreach($lnk as $b)echo '<a class="button button-large" style="margin:0 8px 8px 0" href="'.esc_url(admin_url($b[1])).'">'.esc_html($b[0]).'</a>';
 echo '</p><h2>Durum</h2>';sk_dash_widget();echo '</div>';
}
function sk_dash_widget(){
 $c=sk_member_counts();$pend=count(get_posts(['post_type'=>'booking','numberposts'=>-1,'fields'=>'ids','meta_key'=>'status','meta_value'=>'pending']));
 $soon=[];foreach(get_users(['role'=>'sk_member']) as $u){$l=sk_days_left($u->ID);if($l!==null&&$l>=0&&$l<=7)$soon[]=$u->display_name.' ('.$l.' gün)';}
 $m=date('Y-m-01',strtotime(current_time('Y-m-d')));$inc=0;foreach(sk_fin_entries($m,date('Y-m-t',strtotime($m))) as $e)if(get_post_meta($e->ID,'type',true)!=='expense')$inc+=(float)get_post_meta($e->ID,'amount',true);
 echo '<ul style="list-style:disc;margin-left:18px"><li>Aktif üye: <b>'.(int)$c['active'].'</b> · Bitmek üzere: <b>'.(int)$c['soon'].'</b> · Süresi dolmuş: <b>'.(int)$c['expired'].'</b></li>';
 echo '<li>Bekleyen rezervasyon: <b>'.$pend.'</b> (<a href="'.esc_url(admin_url('edit.php?post_type=booking')).'">gör</a>)</li><li>Bu ay gelir: <b>'.esc_html(sk_money($inc)).'</b> (<a href="'.esc_url(admin_url('admin.php?page=sk-finance')).'">rapor</a>)</li>';
 if($soon)echo '<li>7 gün içinde bitecek: '.esc_html(implode(', ',$soon)).'</li>';echo '</ul>';
}
add_action('wp_dashboard_setup',function(){if(current_user_can('manage_options'))wp_add_dashboard_widget('sk_dash','Spor Kompleksi Özeti','sk_dash_widget');});

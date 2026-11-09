<?php get_header();$img=sk_opt('hero_image');?>
<section class="sk-hero"<?php echo sk_da('hero_image');?> <?php if($img)echo 'style="background-image:url('.esc_url($img).')"';?>>
 <?php if(sk_opt('hero_video'))echo '<video aria-hidden="true" src="'.esc_url(sk_opt('hero_video')).'" autoplay muted loop playsinline></video><button type="button" class="sk-vpause" aria-pressed="false">'.esc_html(sk_t('a_vpause')).'</button>';?>
 <div class="sk-wrap"><span class="sk-eyebrow"><?php echo sk_x('hero_eyebrow');?></span><h1><?php echo sk_x('hero_title');?></h1>
 <p><?php echo sk_x('hero_sub');?></p>
 <a class="sk-btn" href="<?php echo esc_url(sk_opt('hero_b1_url')?:sk_booking_url(['type'=>'pt']));?>"><?php echo sk_x('hero_b1_label');?></a>
 <a class="sk-btn ghost" href="<?php echo esc_url(sk_opt('hero_b2_url')?:sk_booking_url(['type'=>'hall']));?>"><?php echo sk_x('hero_b2_label');?></a></div></section>
<?php if(sk_opt('show_about')==='1'&&sk_opt('about_text')):?><section class="sec"><div class="sk-wrap about"><div><h2><?php echo sk_x('about_title');?></h2><?php echo wpautop(sk_x('about_text'));?></div><?php if(sk_opt('about_image'))echo '<img'.sk_da('about_image').' src="'.esc_url(sk_opt('about_image')).'" alt="" loading="lazy">';?></div></section><?php endif;?>
<?php
$S=[
'cats'=>function(){$c=sk_cats();if(sk_opt('show_cats')!=='1'||!$c)return;?><section <?php echo sk_sec_attr('cats');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('cats_title'),sk_x('cats_sub'));?><div class="grid tiles"><?php foreach($c as $t)sk_cat_tile($t);?></div></div></section><?php },
'branches'=>function(){if(sk_opt('show_branches')!=='1'||!sk_q('branch',1))return;?><section <?php echo sk_sec_attr('branches');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('branches_title'),sk_x('branches_sub'));sk_grid('branch','sk_branch_card',8);?><p style="margin-top:2em"><a class="sk-btn ghost" href="<?php echo esc_url(get_post_type_archive_link('branch'));?>"><?php echo sk_x('t_all_branches');?></a></p></div></section><?php },
'pt'=>function(){if(sk_opt('show_pt')!=='1')return;?><section <?php echo sk_sec_attr('pt');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('pt_title'),sk_x('pt_sub'));?>
 <div class="two"><div class="box"><h3><?php echo sk_x('pt_ind_title');?></h3><p class="sk-muted"><?php echo sk_x('pt_ind_text');?></p></div><div class="box"><h3><?php echo sk_x('pt_grp_title');?></h3><p class="sk-muted"><?php echo sk_x('pt_grp_text');?></p></div></div>
 <?php sk_grid('pt_package','sk_pkg_card',9);?></div></section><?php },
'trainers'=>function(){if(sk_opt('show_trainers')!=='1'||!sk_q('trainer',1))return;?><section <?php echo sk_sec_attr('trainers');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('trainers_title'),sk_x('trainers_sub'));sk_grid('trainer','sk_trainer_card',8);?><p style="margin-top:2em"><a class="sk-btn ghost" href="<?php echo esc_url(get_post_type_archive_link('trainer'));?>"><?php echo sk_x('t_all_trainers');?></a></p></div></section><?php },
'halls'=>function(){if(sk_opt('show_halls')!=='1'||!sk_q('hall',1))return;?><section <?php echo sk_sec_attr('halls');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('halls_title'),sk_x('halls_sub'));sk_grid('hall','sk_hall_card');if(sk_opt('referee_note'))echo '<p class="sk-muted" style="margin-top:1.5em">'.sk_x('referee_note').'</p>';?></div></section><?php },
'facilities'=>function(){if(sk_opt('show_fac')!=='1'||!sk_q('facility',1))return;?><section <?php echo sk_sec_attr('facilities');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('fac_title'),sk_x('fac_sub'));sk_grid('facility','sk_fac_card');?></div></section><?php },
'memberships'=>function(){if(sk_opt('show_mem')!=='1'||!sk_q('membership',1))return;?><section <?php echo sk_sec_attr('membership');?>><div class="sk-wrap"><?php sk_sec_head(sk_x('mem_title'),sk_x('mem_sub'));sk_grid('membership','sk_mem_card');?></div></section><?php },
];
foreach(array_map('trim',explode(',',sk_opt('home_order'))) as $k)if(isset($S[$k]))$S[$k]();
?>
<section class="cta"<?php echo sk_da('bg_cta');?><?php if(sk_opt('bg_cta'))echo ' style="background-image:url('.esc_url(sk_opt('bg_cta')).');background-size:cover;background-blend-mode:multiply"';?>><div class="sk-wrap"><h2><?php echo sk_x('cta_title');?></h2><p><?php echo sk_x('cta_text');?></p><a class="sk-btn" href="<?php echo esc_url(sk_booking_url());?>"><?php echo sk_x('cta_label');?></a></div></section>
<?php if(sk_opt('show_map')==='1'&&sk_opt('map'))echo '<div class="sk-map" style="line-height:0">'.sk_opt('map').'</div>';?>
<?php get_footer();

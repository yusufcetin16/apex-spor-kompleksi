<?php get_header();while(have_posts()):the_post();$id=get_the_ID();sk_page_head(get_the_title(),sk_meta($id,'sport'));?>
<div class="sk-wrap sk-single"><div>
 <?php echo sk_big_img($id);
 echo sk_info([[sk_meta($id,'price')!==''?sk_money(sk_meta($id,'price')):'',sk_t('l_hourly')],[sk_meta($id,'capacity'),sk_t('l_capacity')],[sk_meta($id,'size'),sk_t('l_floor')],[sk_meta($id,'referee')==='1'?sk_t('l_yes'):'',sk_t('l_referee')]]);?>
 <div class="entry"><?php the_content();?></div>
 <?php $f=sk_lines(sk_meta($id,'features'));if($f){echo '<h2>'.sk_x('h_features').'</h2><ul class="ul">';foreach($f as $l)echo '<li>'.esc_html($l).'</li>';echo '</ul>';}
 echo sk_gallery(sk_meta($id,'gallery'));?>
</div><aside class="side"><?php echo sk_booking_form(['type'=>'hall','ref'=>$id,'lock'=>true]);?></aside></div>
<?php endwhile;get_footer();

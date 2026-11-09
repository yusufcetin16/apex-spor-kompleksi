<?php get_header();while(have_posts()):the_post();sk_page_head(get_the_title());?>
<section class="sec"><div class="sk-wrap"><?php $wide=false;foreach(['sk_uye_ol','sk_uye_paneli','sk_ders_programi','sk_booking'] as $sc)if(has_shortcode(get_the_content(),$sc))$wide=true;?><div class="entry<?php echo $wide?' wide':'';?>"><?php the_content();?></div></div></section>
<?php endwhile;get_footer();

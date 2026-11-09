<?php get_header();sk_page_head(is_home()?sk_t('n_blog'):get_the_archive_title());?>
<section class="sec"><div class="sk-wrap"><?php if(have_posts()):?><div class="grid"><?php while(have_posts()):the_post();?>
<a class="card" href="<?php the_permalink();?>"><div class="im"><?php echo sk_img(get_the_ID());?></div><div class="bd"><h3><?php the_title();?></h3><p class="sk-muted"><?php echo esc_html(wp_trim_words(get_the_excerpt(),24));?></p></div></a>
<?php endwhile;?></div><div class="sk-pager"><?php the_posts_pagination();?></div><?php else:?><p><?php echo sk_x('n_empty');?></p><?php endif;?></div></section>
<?php get_footer();

<?php get_header();while(have_posts()):the_post();$pt=get_post_type();sk_page_head(get_the_title());?>
<section class="sec"><div class="sk-wrap"><div class="entry">
<?php echo sk_big_img(get_the_ID());the_content();
if($pt==='pt_package')echo '<p><a class="sk-btn" href="'.esc_url(sk_booking_url(['type'=>'pt'])).'">'.sk_x('t_pt_btn').'</a></p>';
if($pt==='membership')echo '<p><a class="sk-btn" href="'.esc_url(sk_signup_url(['plan'=>get_the_ID()])).'">'.sk_x('t_apply_btn').'</a></p>';?>
</div></div></section>
<?php endwhile;get_footer();

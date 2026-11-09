</main>
<footer class="sk-foot"><div class="sk-wrap"><div class="cols">
 <div><h4><?php bloginfo('name');?></h4><p><?php echo sk_x('footer_text');?></p>
  <p><?php foreach(['instagram'=>'Instagram','facebook'=>'Facebook','youtube'=>'YouTube','tiktok'=>'TikTok'] as $k=>$l)if(sk_opt($k))echo '<a'.sk_da($k).' href="'.esc_url(sk_opt($k)).'" rel="noopener" target="_blank">'.$l.'</a> &nbsp; ';?></p></div>
 <div><h4><?php echo sk_x('ft_links');?></h4><?php if(has_nav_menu('footer'))wp_nav_menu(['theme_location'=>'footer','container'=>false]);else wp_nav_menu(['theme_location'=>'primary','container'=>false,'depth'=>1,'fallback_cb'=>'__return_false']);?></div>
 <div><h4><?php echo sk_x('ft_contact');?></h4><ul><?php if(sk_opt('address'))echo '<li>'.sk_x('address').'</li>';if(sk_opt('phone'))echo '<li>'.sk_x('phone').'</li>';if(sk_opt('email'))echo '<li><a href="mailto:'.esc_attr(sk_opt('email')).'">'.sk_x('email').'</a></li>';?></ul></div>
 <div><h4><?php echo sk_x('ft_hours');?></h4><p><?php echo sk_x('hours');?></p></div>
</div><div class="sk-copy"><span>© <?php echo esc_html(date('Y').' '.get_bloginfo('name'));?></span><?php if(sk_opt('kvkk_url'))echo '<a href="'.esc_url(sk_opt('kvkk_url')).'">KVKK</a>';?></div></div></footer>
<?php if(sk_opt('show_a11y')==='1'):$aa=[['font-up','a_font_up'],['font-down','a_font_down'],['contrast','a_contrast'],['links','a_links'],['font','a_font'],['motion','a_motion'],['reset','a_reset']];?>
<div class="sk-a11y"><button type="button" class="sk-a11y-btn" aria-expanded="false" aria-controls="sk-a11y-panel"><span aria-hidden="true">&#9855;</span><span class="sk-sr"><?php echo esc_html(sk_t('a_open'));?></span></button>
<div id="sk-a11y-panel" class="sk-a11y-panel" role="group" aria-label="<?php echo esc_attr(sk_t('a_title'));?>" hidden><?php foreach($aa as $x)echo '<button type="button" data-a="'.esc_attr($x[0]).'">'.esc_html(sk_t($x[1])).'</button>';?></div></div>
<?php endif;?>
<?php wp_footer();?></body></html>

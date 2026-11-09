<?php get_header();while(have_posts()):the_post();$id=get_the_ID();sk_page_head(get_the_title(),sk_meta($id,'role'));?>
<div class="sk-wrap sk-single"><div>
 <?php echo sk_big_img($id,'photo');
 $br=sk_trainer_branches($id);if($br){echo '<p>';foreach($br as $b)echo '<a class="sk-tag red" href="'.esc_url(get_permalink($b)).'">'.esc_html(get_the_title($b)).'</a>';echo '</p>';}
 echo sk_info([[sk_meta($id,'official'),sk_t('l_official')],[sk_meta($id,'exp')!==''?sk_meta($id,'exp').' '.sk_t('l_year'):'',sk_t('l_exp')],
  [(sk_meta($id,'ind')==='1'&&sk_meta($id,'price_ind')!=='')?sk_money(sk_meta($id,'price_ind')):'',sk_t('l_ind_session')],[(sk_meta($id,'grp')==='1'&&sk_meta($id,'price_grp')!=='')?sk_money(sk_meta($id,'price_grp')):'',sk_t('l_grp_session')]]);?>
 <h2><?php echo sk_x('h_bio');?></h2><div class="entry"><?php the_content();?></div>
 <?php $c=sk_lines(sk_meta($id,'certs'));if($c){echo '<h2>'.sk_x('h_certs').'</h2><ul class="ul">';foreach($c as $l)echo '<li>'.esc_html($l).'</li>';echo '</ul>';}
 echo sk_gallery(sk_meta($id,'cert_gallery'));
 $pk=sk_pkgs_of_trainer($id);if($pk){echo '<h2>'.sk_x('h_pkgs').'</h2><div class="grid">';foreach($pk as $p)sk_pkg_card($p->ID);echo '</div>';}
 $av=sk_meta($id,'avail');if(is_array($av)){echo '<h2>'.sk_x('h_avail').'</h2><table class="av">';foreach(sk_days() as $i=>$n){$r=$av[$i]??[];echo '<tr><td>'.esc_html($n).'</td><td'.(empty($r['on'])?' class="off"':'').'>'.(empty($r['on'])?sk_x('av_closed'):esc_html($r['from'].' - '.$r['to'])).'</td></tr>';}echo '</table>';}?>
</div><aside class="side"><?php echo sk_booking_form(['type'=>'pt','ref'=>$id,'lock'=>true]);?></aside></div>
<?php endwhile;get_footer();

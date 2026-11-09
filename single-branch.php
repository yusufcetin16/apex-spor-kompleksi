<?php get_header();while(have_posts()):the_post();$id=get_the_ID();$t=get_the_terms($id,'branch_cat');sk_page_head(get_the_title(),($t&&!is_wp_error($t))?$t[0]->name:'');?>
<div class="sk-wrap sk-single"><div>
 <?php echo sk_big_img($id);
 echo sk_info([[sk_meta($id,'level'),sk_t('l_level')],[sk_meta($id,'age'),sk_t('l_age')],[sk_meta($id,'price')!==''?sk_money(sk_meta($id,'price')):'',sk_t('l_monthly')],[sk_meta($id,'gear'),sk_t('l_gear')]]);?>
 <div class="entry"><?php the_content();?></div>
 <?php $s=sk_lines(sk_meta($id,'schedule'));if($s){echo '<h2>'.sk_x('h_schedule').'</h2><ul class="ul">';foreach($s as $l)echo '<li>'.esc_html($l).'</li>';echo '</ul>';}
 $ls=sk_lessons_of_branch($id);if($ls){echo '<h2>'.sk_x('h_lessons').'</h2><ul class="ul">';foreach($ls as $l){$tt=(int)sk_meta($l->ID,'trainer');echo '<li><b>'.esc_html($l->post_title).'</b> — '.esc_html(sk_lesson_when($l->ID)).($tt?' • '.esc_html(get_the_title($tt)):'').'</li>';}echo '</ul>';}
 $tr=sk_branch_trainers($id);if($tr){echo '<h2>'.sk_x('h_branch_trainers').'</h2><div class="grid">';foreach($tr as $x)sk_trainer_card($x);echo '</div>';}
 $pk=sk_pkgs_of_branch($id);if($pk){echo '<h2>'.sk_x('h_pkgs').'</h2><div class="grid">';foreach($pk as $p)sk_pkg_card($p->ID);echo '</div>';}?>
</div><aside class="side"><div class="sk-form"><h3><?php echo sk_x('b_cta_t');?></h3><p class="sk-muted"><?php echo sk_x('b_cta_p');?></p><p><a class="sk-btn" href="<?php echo esc_url(sk_signup_url());?>"><?php echo sk_x('b_cta_btn');?></a></p><?php if(sk_branch_trainers($id))echo '<p><a class="sk-btn ghost" href="'.esc_url(sk_booking_url(['type'=>'pt'])).'">'.sk_x('b_cta_pt').'</a></p>';?></div></aside></div>
<?php endwhile;get_footer();

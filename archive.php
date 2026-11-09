<?php get_header();
$tax=is_tax('branch_cat');
if($tax){$q=get_queried_object();sk_page_head($q->name,sk_opt('ar_branch_t'),$q->description,get_term_meta($q->term_id,'sk_image',true));$pt='branch';}
else{$pt=get_post_type()?:'post';sk_page_head(sk_opt("ar_{$pt}_t")?:(post_type_archive_title('',false)?:get_the_archive_title()),'',sk_opt("ar_{$pt}_s"),'',"ar_{$pt}");}
$fn=['trainer'=>'sk_trainer_card','hall'=>'sk_hall_card','pt_package'=>'sk_pkg_card','membership'=>'sk_mem_card','facility'=>'sk_fac_card','branch'=>'sk_branch_card'][$pt]??null;?>
<section class="sec"><div class="sk-wrap">
<?php if(is_post_type_archive('branch')&&($c=sk_cats())){echo '<div class="grid tiles" style="margin-bottom:3em">';foreach($c as $t)sk_cat_tile($t);echo '</div>';}
if($tax){echo '<p class="chips"><a class="sk-tag" href="'.esc_url(get_post_type_archive_link('branch')).'">'.sk_x('t_all').'</a>';foreach(sk_cats() as $t)echo '<a class="sk-tag '.($t->term_id===$q->term_id?'red':'').'" href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a>';echo '</p>';}
if(is_post_type_archive('membership'))echo '<p style="margin-bottom:2em"><a class="sk-btn" href="'.esc_url(sk_signup_url()).'">'.esc_html(sk_t('s_title')).'</a> <a class="sk-btn ghost" href="'.esc_url(sk_member_url()).'">'.esc_html(sk_t('u_login')).'</a></p>';
if(is_post_type_archive('trainer')){$cb=absint($_GET['brans']??0);$ck=absint($_GET['kat']??0);$base=get_post_type_archive_link('trainer');
 echo '<span class="chips-l">'.sk_x('ar_branch_t').'</span><p class="chips"><a class="sk-tag '.(($cb||$ck)?'':'red').'" href="'.esc_url($base).'">'.sk_x('t_all').'</a>';
 foreach(sk_cats() as $t)if(sk_term_trainers($t->term_id))echo '<a class="sk-tag '.($ck===$t->term_id?'red':'').'" href="'.esc_url(add_query_arg('kat',$t->term_id,$base)).'">'.esc_html($t->name).'</a>';
 foreach(sk_q('branch') as $b)if(sk_branch_trainers($b->ID))echo '<a class="sk-tag '.($cb===$b->ID?'red':'').'" href="'.esc_url(add_query_arg('brans',$b->ID,$base)).'">'.esc_html($b->post_title).'</a>';echo '</p>';}
if($fn&&have_posts()):?><div class="grid"><?php while(have_posts()):the_post();$fn(get_the_ID());endwhile;?></div><div class="sk-pager"><?php the_posts_pagination();?></div>
<?php elseif(have_posts()):?><div class="grid"><?php while(have_posts()):the_post();?><a class="card" href="<?php the_permalink();?>"><div class="im"><?php echo sk_img(get_the_ID());?></div><div class="bd"><h3><?php the_title();?></h3></div></a><?php endwhile;?></div>
<?php else:?><p><?php echo sk_x('n_empty');?></p><?php endif;
if($pt==='hall'&&sk_opt('referee_note'))echo '<p class="sk-muted" style="margin-top:2em">'.sk_x('referee_note').'</p>';?></div></section>
<?php if($tax):$tr=sk_term_trainers($q->term_id);$pk=sk_term_pkgs($q->term_id);
 if($tr):?><section class="sec alt"><div class="sk-wrap"><?php sk_sec_head(sk_x('h_branch_trainers'));?><div class="grid"><?php foreach($tr as $t)sk_trainer_card($t);?></div></div></section><?php endif;
 if($pk):?><section class="sec"><div class="sk-wrap"><?php sk_sec_head(sk_x('h_pkgs'));?><div class="grid"><?php foreach($pk as $p)sk_pkg_card($p->ID);?></div></div></section><?php endif;?>
<section class="cta"><div class="sk-wrap"><h2><?php echo sk_x('cta_title');?></h2><p><?php echo sk_x('cta_text');?></p><a class="sk-btn" href="<?php echo esc_url(sk_signup_url());?>"><?php echo esc_html(sk_t('s_title'));?></a></div></section>
<?php endif;?>
<?php get_footer();

<?php
if(!defined('ABSPATH'))exit;
// ---- Özelleştir kalem (düzenleme kısayolu) yardımcıları
function sk_x($k){$v=nl2br(esc_html(sk_opt($k)));return is_customize_preview()?'<span data-sk="'.esc_attr($k).'">'.$v.'</span>':$v;}
function sk_da($k){return is_customize_preview()?' data-sk="'.esc_attr($k).'"':'';}
function sk_cattr($id,$term=false){return is_customize_preview()?' data-sk-'.($term?'term':'post').'="'.(int)$id.'"':'';}
// ---- Kart verileri (Özelleştir önizlemesinde kaydedilmemiş değişiklikleri de gösterir)
function sk_post_raw($id,$f){
 switch($f){
  case 'title':return get_post_field('post_title',$id);
  case 'text':$t=get_post_type($id);return $t==='trainer'?sk_meta($id,'role'):($t==='hall'?sk_meta($id,'size'):get_post_field('post_excerpt',$id));
  case 'img':return (string)sk_meta($id,'card_img');case 'link':return (string)sk_meta($id,'card_link');case 'btn':return (string)sk_meta($id,'card_btn');}
 return (string)get_post_meta($id,$f,true);}
function sk_pget($id,$f){return apply_filters('sk_post_field',sk_post_raw($id,$f),$id,$f,'post');}
function sk_term_raw($id,$f){$t=get_term($id,'branch_cat');if(!$t||is_wp_error($t))return '';
 switch($f){case 'title':return $t->name;case 'text':return $t->description;case 'img':return (string)get_term_meta($id,'sk_image',true);case 'count':return (string)get_term_meta($id,'sk_count_txt',true);}return '';}
function sk_tget($id,$f){return apply_filters('sk_post_field',sk_term_raw($id,$f),$id,$f,'term');}
function sk_link($id,$d){$l=sk_pget($id,'link');return $l?:$d;}
function sk_has_img($id){return sk_pget($id,'img')||has_post_thumbnail($id);}
function sk_card_text($id,$n=16){$e=trim((string)sk_pget($id,'text'));return $e!==''?$e:wp_trim_words(trim(wp_strip_all_tags(get_post_field('post_content',$id))),$n);}
function sk_big_img($id,$c=''){$u=sk_pget($id,'img');if($u)return '<img src="'.esc_url($u).'" alt="'.esc_attr(sk_pget($id,'title')).'"'.($c?' class="'.esc_attr($c).'"':'').'>';return has_post_thumbnail($id)?get_the_post_thumbnail($id,'large',$c?['class'=>$c]:[]):'';}
function sk_img($id){
 $u=sk_pget($id,'img');if($u)return '<img src="'.esc_url($u).'" alt="" loading="lazy" decoding="async">';
 if(has_post_thumbnail($id))return get_the_post_thumbnail($id,'sk-card',['loading'=>'lazy']);
 if(get_post_type($id)==='branch'){$t=get_the_terms($id,'branch_cat');if($t&&!is_wp_error($t)){$u=sk_tget($t[0]->term_id,'img');if($u)return '<img src="'.esc_url($u).'" alt="" loading="lazy">';}}
 return '<div class="sk-ph" aria-hidden="true"><span>'.esc_html(mb_strtoupper(mb_substr(sk_pget($id,'title'),0,2))).'</span></div>';}
function sk_page_head($title,$eyebrow='',$sub='',$img='',$k=''){$i=$img?:sk_opt('page_head_img');
 echo '<div class="page-h"'.sk_da('page_head_img').($i?' style="background-image:linear-gradient(#0b0b0dcc,#0b0b0dee),url('.esc_url($i).')"':'').'><div class="sk-wrap">'.($eyebrow?'<span class="sk-eyebrow">'.esc_html($eyebrow).'</span>':'').'<h1'.($k?sk_da($k.'_t'):'').'>'.esc_html($title).'</h1>'.($sub?'<p class="sk-muted"'.($k?sk_da($k.'_s'):'').'>'.esc_html($sub).'</p>':'').'</div></div>';}
function sk_sec_head($t,$s=''){echo '<div class="sec-h"><h2>'.$t.'</h2>'.($s?'<p>'.$s.'</p>':'').'</div>';}
function sk_sec_attr($k){$i=sk_opt('bg_'.$k);return 'class="'.sk_sec_cls().($i?' has-bg':'').'" id="'.esc_attr($k).'"'.sk_da('bg_'.$k).($i?' style="background-image:linear-gradient(#0b0b0ddb,#0b0b0ddb),url('.esc_url($i).')"':'');}
function sk_sec_cls(){static $i=0;return($i++%2)?'sec alt':'sec';}
function sk_q($pt,$n=-1){static $c=[];if(!isset($c[$pt]))$c[$pt]=get_posts(['post_type'=>$pt,'numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC','no_found_rows'=>true,'update_post_term_cache'=>false]);return $n>0?array_slice($c[$pt],0,$n):$c[$pt];}
function sk_cats(){static $c=null;if($c!==null)return $c;$t=get_terms(['taxonomy'=>'branch_cat','hide_empty'=>true]);if(is_wp_error($t))return $c=[];
 usort($t,fn($a,$b)=>[(int)get_term_meta($a->term_id,'sk_order',true),$a->name]<=>[(int)get_term_meta($b->term_id,'sk_order',true),$b->name]);return $c=$t;}
function sk_branch_trainers($bid){return array_values(array_filter(array_map('intval',(array)sk_meta($bid,'trainers')),fn($t)=>get_post_status($t)==='publish'));}
function sk_branch_map(){static $m=null;if($m===null){$m=[];foreach(sk_q('branch') as $b)foreach(array_map('intval',(array)sk_meta($b->ID,'trainers')) as $t)$m[$t][]=$b->ID;}return $m;}
function sk_trainer_branches($tid){return sk_branch_map()[(int)$tid]??[];}
function sk_pkgs_of_branch($bid){return array_values(array_filter(sk_q('pt_package'),fn($p)=>(int)sk_meta($p->ID,'branch')===(int)$bid));}
function sk_pkgs_of_trainer($tid){return array_values(array_filter(sk_q('pt_package'),fn($p)=>in_array((int)$tid,array_map('intval',(array)sk_meta($p->ID,'trainers')),true)));}







function sk_grid($pt,$fn,$n=-1){$q=sk_q($pt,$n);if(!$q)return false;echo '<div class="grid">';foreach($q as $p)$fn($p->ID);echo '</div>';return true;}
function sk_info($pairs){$o='';foreach($pairs as $p)if($p[0]!==''&&$p[0]!==null)$o.='<div><b>'.esc_html($p[0]).'</b><span>'.esc_html($p[1]).'</span></div>';return $o?'<div class="info">'.$o.'</div>':'';}
function sk_term_branches($tid){static $c=[];$tid=(int)$tid;if(!isset($c[$tid]))$c[$tid]=get_posts(['post_type'=>'branch','numberposts'=>-1,'no_found_rows'=>true,'tax_query'=>[['taxonomy'=>'branch_cat','terms'=>$tid]],'orderby'=>'menu_order title','order'=>'ASC']);return $c[$tid];}
function sk_term_trainers($tid){$o=[];foreach(sk_term_branches($tid) as $b)foreach(sk_branch_trainers($b->ID) as $t)$o[$t]=$t;return array_values($o);}
function sk_term_pkgs($tid){$ids=array_map(fn($b)=>$b->ID,sk_term_branches($tid));return array_values(array_filter(sk_q('pt_package'),fn($p)=>in_array((int)sk_meta($p->ID,'branch'),$ids,true)));}

// ---- Kutu parçaları: her parça ayrı düzenlenebilir (Özelleştir'de kendi kalemi var)
function sk_pfa($id,$p,$pre=''){return is_customize_preview()?' data-sk-pf="'.$pre.(int)$id.':'.$p.'"':'';}
function sk_part($part,$id){
 $t=get_post_type($id);$pf=sk_pfa($id,$part);$pv=is_customize_preview();$o='';
 switch($part){
  case 'img':
   if(in_array($t,['pt_package','membership','facility'],true)&&!sk_has_img($id)&&!$pv)return '';
   return '<div class="im"'.$pf.'>'.sk_img($id).'</div>';
  case 'icon':return '<div class="ic"'.$pf.'>'.esc_html(sk_pget($id,'icon')?:'✔').'</div>';
  case 'title':return '<h3'.$pf.'>'.esc_html(sk_pget($id,'title')).'</h3>';
  case 'text':
   if($t==='trainer')return '<span class="sk-muted"'.$pf.'>'.esc_html(sk_pget($id,'text')).'</span>';
   if($t==='hall')return '<span class="sk-muted"'.$pf.'>'.esc_html(trim(sk_pget($id,'text').' '.sk_pget($id,'capacity'))).'</span>';
   if($t==='facility')return '<p class="sk-muted"'.$pf.'>'.esc_html(sk_pget($id,'text')).'</p>';
   $c=sk_card_text($id,$t==='pt_package'?28:16);if($c===''&&!$pv)return '';
   return ($t==='branch'?'<span class="sk-muted"'.$pf.'>':'<p class="sk-muted"'.$pf.'>').esc_html($c).($t==='branch'?'</span>':'</p>');
  case 'tags':
   $tg=[];
   if($t==='trainer'){foreach(array_slice(sk_trainer_branches($id),0,3) as $b)$tg[]=['red',get_the_title($b)];if(sk_pget($id,'ind')==='1')$tg[]=['',sk_t('tag_ind')];if(sk_pget($id,'grp')==='1')$tg[]=['',sk_t('tag_grp')];}
   elseif($t==='hall'){if(sk_pget($id,'sport')!=='')$tg[]=['red',sk_pget($id,'sport')];if(sk_pget($id,'referee')==='1')$tg[]=['',sk_t('tag_referee')];}
   elseif($t==='pt_package'){$tg[]=['red',sk_pget($id,'ptype')==='grp'?sk_t('tag_grp_pkg'):sk_t('tag_ind')];$b=(int)sk_meta($id,'branch');if($b)$tg[]=['',get_the_title($b)];}
   elseif($t==='branch'){$m=get_the_terms($id,'branch_cat');if($m&&!is_wp_error($m))$tg[]=['red',sk_tget($m[0]->term_id,'title')];if(sk_pget($id,'age')!=='')$tg[]=['',sk_pget($id,'age')];}
   else return '';
   foreach(array_filter(array_map('trim',explode(',',(string)sk_pget($id,'xtags')))) as $x)$tg[]=['',$x];
   if(!$tg&&!$pv)return '';
   foreach($tg as $g)$o.='<span class="sk-tag'.($g[0]?' red':'').'">'.esc_html($g[1]).'</span>';
   return '<div'.$pf.'>'.$o.'</div>';
  case 'price':
   $x=trim((string)sk_pget($id,'price_txt'));$in='';
   if($t==='trainer'){$p=sk_pget($id,'price_ind');$in=$x!==''?esc_html($x):($p!==''?'<small>'.esc_html(sk_t('t_session_price')).' </small>'.esc_html(sk_money($p)):'');}
   elseif($t==='hall'){$p=sk_pget($id,'price');$in=$x!==''?esc_html($x):($p!==''?esc_html(sk_money($p)).'<small> / '.esc_html(sk_t('t_hour')).'</small>':'');}
   elseif($t==='branch'){$p=sk_pget($id,'price');$in=$x!==''?esc_html($x):($p!==''?esc_html(sk_money($p)).'<small> / '.esc_html(sk_t('t_month')).'</small>':'');}
   elseif($t==='pt_package'){$pr=sk_pget($id,'price');$s=(float)sk_pget($id,'sessions');$ps=($s>0&&$pr!=='')?$pr/$s:0;$in=$x!==''?esc_html($x):esc_html(sk_money($pr)).($ps?'<small> · '.esc_html(sk_t('t_per_session').' '.sk_money($ps)).'</small>':'');}
   elseif($t==='membership'){$p=sk_pget($id,'price');$in=$x!==''?esc_html($x):($p!==''?esc_html(sk_money($p)).'<small> '.esc_html(sk_pget($id,'period')).'</small>':'');}
   else return '';
   if($in===''&&!$pv)return '';
   return '<div class="price"'.$pf.'>'.$in.'</div>';
  case 'list':
   $l=[];
   if($t==='pt_package'){$s=sk_pget($id,'sessions');if($s!=='')$l[]=$s.' '.sk_t('t_sessions');$v=sk_pget($id,'validity');if($v!=='')$l[]=sk_t('t_validity').': '.$v;$d=sk_pget($id,'days');if($d!=='')$l[]=sk_t('t_days').': '.$d;foreach((array)sk_meta($id,'trainers') as $y)if($y)$l[]=sk_t('t_trainer').': '.get_the_title($y);}
   elseif($t==='membership')$l=sk_lines(sk_pget($id,'features'));
   else return '';
   if(!$l&&!$pv)return '';
   foreach($l as $y)$o.='<li>'.esc_html($y).'</li>';
   return '<ul'.$pf.'>'.$o.'</ul>';
  case 'btn':
   $key=['trainer'=>'t_trainer_btn','hall'=>'t_hall_btn','branch'=>'t_branch_btn','pt_package'=>'t_pt_btn','membership'=>'t_apply_btn'][$t]??'';if(!$key)return '';
   $lab=esc_html(sk_pget($id,'btn')?:sk_t($key));
   if($t==='pt_package'||$t==='membership'){$d=$t==='pt_package'?sk_booking_url(['type'=>'pt']):sk_signup_url(['plan'=>$id]);return '<a class="sk-btn"'.$pf.' href="'.esc_url(sk_link($id,$d)).'">'.$lab.'</a>';}
   return '<span class="sk-btn"'.$pf.'>'.$lab.'</span>';
 }
 return '';}
function sk_tpart($part,$t){$id=$t->term_id;$pf=sk_pfa($id,$part,'t');$pv=is_customize_preview();
 switch($part){
  case 'title':return '<h3'.$pf.'>'.esc_html(sk_tget($id,'title')).'</h3>';
  case 'text':$d=sk_tget($id,'text');if($d===''&&!$pv)return '';return '<p'.$pf.'>'.esc_html(wp_trim_words($d,14)).'</p>';
  case 'count':$c=trim((string)sk_tget($id,'count'));$c=$c!==''?$c:sk_t('t_cat_count');return '<span class="sk-tag red"'.$pf.'>'.esc_html(str_replace('%d',$t->count,$c)).'</span>';}
 return '';}
function sk_cat_tile($t){$u=sk_tget($t->term_id,'img');?>
<a class="tile"<?php echo sk_cattr($t->term_id,true);?> href="<?php echo esc_url(get_term_link($t));?>" <?php if($u)echo 'style="background-image:url('.esc_url($u).')"';?>><div class="tl"><?php echo sk_tpart('title',$t).sk_tpart('text',$t).sk_tpart('count',$t);?></div></a><?php }
function sk_trainer_card($id){?>
<a class="card"<?php echo sk_cattr($id);?> href="<?php echo esc_url(sk_link($id,get_permalink($id)));?>"><?php echo sk_part('img',$id);?><div class="bd"><?php echo sk_part('title',$id).sk_part('text',$id).sk_part('tags',$id).sk_part('price',$id).sk_part('btn',$id);?></div></a><?php }
function sk_hall_card($id){?>
<a class="card"<?php echo sk_cattr($id);?> href="<?php echo esc_url(sk_link($id,get_permalink($id)));?>"><?php echo sk_part('img',$id);?><div class="bd"><?php echo sk_part('tags',$id).sk_part('title',$id).sk_part('text',$id).sk_part('price',$id).sk_part('btn',$id);?></div></a><?php }
function sk_branch_card($id){?>
<a class="card <?php echo sk_meta($id,'highlight')==='1'?'hl':'';?>"<?php echo sk_cattr($id);?> href="<?php echo esc_url(sk_link($id,get_permalink($id)));?>"><?php echo sk_part('img',$id);?><div class="bd"><?php echo sk_part('tags',$id).sk_part('title',$id).sk_part('text',$id).sk_part('price',$id).sk_part('btn',$id);?></div></a><?php }
function sk_pkg_card($id){?>
<div class="card <?php echo sk_pget($id,'highlight')==='1'?'hl':'';?>"<?php echo sk_cattr($id);?>><?php echo sk_part('img',$id);?><div class="bd"><?php echo sk_part('tags',$id).sk_part('title',$id).sk_part('price',$id).sk_part('list',$id).sk_part('text',$id).sk_part('btn',$id);?></div></div><?php }
function sk_mem_card($id){?>
<div class="card <?php echo sk_pget($id,'highlight')==='1'?'hl':'';?>"<?php echo sk_cattr($id);?>><?php echo sk_part('img',$id);?><div class="bd"><?php echo sk_part('title',$id).sk_part('text',$id).sk_part('price',$id).sk_part('list',$id).sk_part('btn',$id);?></div></div><?php }
function sk_fac_card($id){?>
<div class="card"<?php echo sk_cattr($id);?>><?php echo sk_part('img',$id);?><div class="bd fac"><?php echo sk_part('icon',$id).sk_part('title',$id).sk_part('text',$id);?></div></div><?php }

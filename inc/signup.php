<?php
if(!defined('ABSPATH'))exit;
// ===== Üye olma (kayıt) =====
function sk_signup_url($a=[]){$p=get_page_by_path('uye-ol');$u=$p?get_permalink($p):home_url('/uye-ol/');return $a?add_query_arg($a,$u):$u;}
add_shortcode('sk_uye_ol',function($a){
 $compact=!empty($a['compact']);
 if(is_user_logged_in()){$b='<a class="sk-btn" href="'.esc_url(sk_member_url()).'">'.esc_html(sk_t('u_title')).'</a>';return $compact?'<div class="sk-form"><h3>'.esc_html(sk_t('u_title')).'</h3><p>'.$b.'</p></div>':'<p>'.$b.'</p>';}
 $pl=absint($_GET['plan']??0);ob_start();?>
<?php if(!$compact):?><div class="sk-signup-wrap"><div class="sk-signup-info"><h2><?php echo esc_html(sk_t('s_info_t'));?></h2><ul class="ul"><li><?php echo esc_html(sk_t('s_p1'));?></li><li><?php echo esc_html(sk_t('s_p2'));?></li><li><?php echo esc_html(sk_t('s_p3'));?></li></ul></div><?php endif;?>
<form class="sk-form sk-signup" novalidate><h3><?php echo esc_html(sk_t('s_title'));?></h3>
 <div class="f"><label><?php echo esc_html(sk_t('s_name'));?></label><input name="name" required autocomplete="name"></div>
 <div class="f"><label><?php echo esc_html(sk_t('s_email'));?></label><input type="email" name="email" required autocomplete="email"></div>
 <div class="f"><label><?php echo esc_html(sk_t('s_phone'));?></label><input type="tel" name="phone" required autocomplete="tel"></div>
 <div class="f"><label><?php echo esc_html(sk_t('s_pass'));?></label><input type="password" name="pass" required minlength="8" autocomplete="new-password"></div>
 <div class="f"><label><?php echo esc_html(sk_t('s_pass2'));?></label><input type="password" name="pass2" required minlength="8" autocomplete="new-password"></div>
 <?php $ms=sk_q('membership');if($ms):?><div class="f"><label><?php echo esc_html(sk_t('s_plan'));?></label><select name="plan"><option value="0"><?php echo esc_html(sk_t('s_noplan'));?></option>
 <?php foreach($ms as $m):?><option value="<?php echo (int)$m->ID;?>" <?php selected($pl,$m->ID);?>><?php echo esc_html($m->post_title.' - '.sk_money(sk_meta($m->ID,'price')));?></option><?php endforeach;?></select></div><?php endif;?>
 <div class="f"><label class="chk"><input type="checkbox" name="consent" value="1" required> <?php echo esc_html(sk_t('s_consent'));?></label></div>
 <input class="sk-hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
 <button class="sk-btn" type="submit"><?php echo esc_html(sk_t('s_submit')?:sk_t('s_title')?:'Üye Ol');?></button>
 <div class="sk-msg" role="status" aria-live="polite"></div>
 <p class="sk-muted" style="font-size:.85rem;margin:1em 0 0"><?php echo esc_html(sk_t('s_note'));?></p>
 <p style="margin:.6em 0 0"><a href="<?php echo esc_url(sk_member_url());?>"><?php echo esc_html(sk_t('s_have'));?></a></p>
</form><?php if(!$compact)echo '</div>';return ob_get_clean();});
add_action('wp_ajax_nopriv_sk_signup','sk_ajax_signup');add_action('wp_ajax_sk_signup','sk_ajax_signup');
function sk_ajax_signup(){
 if(!empty($_POST['website']))wp_send_json_success(['msg'=>'ok']);
 $err=function($m){wp_send_json_error(['msg'=>$m]);};
 $ip='sk_su_'.md5($_SERVER['REMOTE_ADDR']??'x');$n=(int)get_transient($ip);if($n>=6)$err('Çok fazla deneme. Lütfen daha sonra tekrar deneyin.');set_transient($ip,$n+1,HOUR_IN_SECONDS);
 $name=sanitize_text_field($_POST['name']??'');$email=sanitize_email($_POST['email']??'');$phone=sanitize_text_field($_POST['phone']??'');$p1=(string)($_POST['pass']??'');$p2=(string)($_POST['pass2']??'');$plan=absint($_POST['plan']??0);
 if($name===''||!is_email($email))$err('Lütfen adınızı ve geçerli bir e-posta adresi girin.');
 if(strlen(preg_replace('/\D/','',$phone))<10)$err('Lütfen geçerli bir telefon numarası girin.');
 if(strlen($p1)<8)$err('Şifre en az 8 karakter olmalı.');if($p1!==$p2)$err('Şifreler eşleşmiyor.');
 if(empty($_POST['consent']))$err('Devam etmek için onay kutusunu işaretleyin.');
 if(email_exists($email))$err('Bu e-posta ile kayıt zaten var. Lütfen giriş yapın.');
 $login=sanitize_user(strstr($email,'@',true),true)?:'uye';$b=$login;$i=1;while(username_exists($login))$login=$b.(++$i);
 $uid=wp_insert_user(['user_login'=>$login,'user_email'=>$email,'user_pass'=>$p1,'display_name'=>$name,'first_name'=>$name,'role'=>'sk_member']);
 if(is_wp_error($uid))$err('Kayıt oluşturulamadı. Lütfen tekrar deneyin.');
 update_user_meta($uid,'sk_phone',$phone);
 $info='Plan seçilmedi';
 if($plan&&get_post_type($plan)==='membership'&&get_post_status($plan)==='publish'){
  $bid=wp_insert_post(['post_type'=>'booking','post_status'=>'publish','post_title'=>sk_types()['membership'].' - '.$name,'meta_input'=>['type'=>'membership','ref_id'=>$plan,'ref_title'=>get_the_title($plan),'date'=>'','start'=>'','end'=>'','name'=>$name,'phone'=>$phone,'email'=>$email,'note'=>'','referee'=>0,'total'=>sk_meta($plan,'price'),'extra'=>get_the_title($plan),'status'=>'pending','slotkey'=>'']]);
  $info='Plan: '.get_the_title($plan).' (onay bekliyor) '.admin_url('post.php?post='.$bid.'&action=edit');}
 wp_mail(sk_opt('booking_email')?:get_option('admin_email'),'Yeni üye kaydı: '.$name,"Ad: $name\nE-posta: $email\nTelefon: $phone\n$info\nÜyeler: ".admin_url('admin.php?page=sk-members'));
 $u=get_userdata($uid);wp_set_current_user($uid);wp_set_auth_cookie($uid,true);do_action('wp_login',$u->user_login,$u);
 wp_send_json_success(['msg'=>sk_opt('s_ok'),'redirect'=>sk_member_url()]);
}
// Üyelik başvurusu onaylanınca: kayıtlı üyenin süresini otomatik başlat / uzat
function sk_activate_from_booking($bid){
 if(get_post_meta($bid,'type',true)!=='membership'||get_post_meta($bid,'status',true)!=='approved'||get_post_meta($bid,'activated',true))return;
 $u=get_user_by('email',get_post_meta($bid,'email',true));if(!$u||!in_array('sk_member',(array)$u->roles,true))return;
 $plan=(int)get_post_meta($bid,'ref_id',true);$days=(int)sk_meta($plan,'days')?:30;$today=current_time('Y-m-d');$cur=get_user_meta($u->ID,'sk_end',true);$start=($cur&&$cur>$today)?$cur:$today;
 update_user_meta($u->ID,'sk_plan',$plan);update_user_meta($u->ID,'sk_start',$start);update_user_meta($u->ID,'sk_end',date('Y-m-d',strtotime($start.' +'.$days.' days')));update_post_meta($bid,'activated',1);
}

// Sayfalar silinmiş / hiç oluşmamışsa kendiliğinden yeniden oluştur (yalnızca sürüm değişince bir kez kontrol eder)
add_action('init',function(){
 if(get_option('sk_pg1')===SK_VER)return;
 foreach([['Üye Ol','uye-ol','[sk_uye_ol]'],['Üye Paneli','uye-paneli','[sk_uye_paneli]']] as $p)if(!get_page_by_path($p[1]))wp_insert_post(['post_type'=>'page','post_title'=>$p[0],'post_name'=>$p[1],'post_status'=>'publish','post_content'=>$p[2]]);
 update_option('sk_pg1',SK_VER);
},20);

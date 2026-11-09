<!doctype html><html <?php language_attributes();?>><head><meta charset="<?php bloginfo('charset');?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head();?></head>
<body <?php body_class();?>><?php wp_body_open();?>
<a class="sk-skip" href="#main"><?php echo esc_html(sk_t('a_skip'));?></a>
<div class="sk-top"><div class="sk-wrap"><span><?php echo sk_x('hours');?></span><span class="sk-topr"><?php if(sk_opt('phone'))echo '<a href="tel:'.esc_attr(preg_replace('/[^+\d]/','',sk_opt('phone'))).'">'.sk_x('phone').'</a>';?><?php if(sk_opt('whatsapp'))echo ' · <a href="https://wa.me/'.esc_attr(preg_replace('/\D/','',sk_opt('whatsapp'))).'" rel="noopener">WhatsApp</a>';?><?php if(is_user_logged_in()):?> · <a href="<?php echo esc_url(sk_member_url());?>"><?php echo esc_html(sk_t('u_title'));?></a><?php else:?> · <a href="<?php echo esc_url(sk_signup_url());?>"><?php echo esc_html(sk_t('s_title'));?></a> · <a href="<?php echo esc_url(sk_member_url());?>"><?php echo esc_html(sk_t('u_login'));?></a><?php endif;?></span></div></div>
<header class="sk-head"><div class="sk-wrap">
 <a class="sk-logo" href="<?php echo esc_url(home_url('/'));?>"><?php if(has_custom_logo())echo wp_get_attachment_image(get_theme_mod('custom_logo'),'full');else echo esc_html(get_bloginfo('name'));?></a>
 <button class="sk-burger" aria-label="Menü" aria-expanded="false" aria-controls="sk-nav">☰</button>
 <nav class="sk-nav" id="sk-nav" aria-label="Ana menü"><?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>'sk_nav_fallback']);?></nav>
 <a class="sk-btn" href="<?php echo esc_url(sk_booking_url());?>"><?php echo sk_x('cta_label');?></a>
</div></header>
<main id="main" tabindex="-1">

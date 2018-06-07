<?php
/*
Template Name: Coming Soon Page
*/
get_header(); ?>

<div class="container">
    <?php wp_nav_menu(array('theme_location'=>'landing-top', 'container_class'=>'landing-top-menu', 'depth'=>-1, 'fallback_cb'=>  create_function('$args', ''))); ?>
</div>

<hr>

<div class="container landing">
    <div class="spaced">
        <h1 class="headline">
            <?php echo wpchimp_esc(get_theme_mod('wpchimpcs_headline', get_bloginfo('name', 'display'))); ?>
        </h1>

        <p class="description">
            <?php echo wpchimp_esc(get_theme_mod('wpchimpcs_description', get_bloginfo('description', 'display'))); ?>
        </p>
    </div>

    <div class="counter"><?php get_template_part('counter'); ?></div>

    <div class="form-container spaced">
        <div class="signup-text"><?php echo wpchimp_esc_html_form(get_theme_mod('wpchimpcs_signup_text')); ?></div>

        <div class="signup-form">
        <?php echo wpchimcs_landing_form(); ?>
        </div>
    </div>
</div>

<div class="container"><div class="landing-sidebars">
<?php if(is_active_sidebar('wpchimpcs-landing-left')) { ?>
    <div class="left">
    <?php dynamic_sidebar('wpchimpcs-landing-left'); ?>
    </div>
<?php } ?>
<?php if(is_active_sidebar('wpchimpcs-landing-right')) { ?>
    <div class="right">
    <?php dynamic_sidebar('wpchimpcs-landing-right'); ?>
    </div>
<?php } ?>
</div></div>

<hr>

<div class="container spaced">
    <?php wp_nav_menu(array('theme_location'=>'landing-footer', 'container_class'=>'landing-footer-menu', 'depth'=>-1, 'fallback_cb'=>  create_function('$args', ''))); ?>
</div>

<?php get_footer();
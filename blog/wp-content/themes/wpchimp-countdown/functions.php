<?php
if(!isset($content_width))
    $content_width = 600;

function wpchimpcs_register_sidebars() {
    register_sidebar(array(
        'id' => 'wpchimpcs-landing-left',
        'name' => __('Landing Page Left', 'wpchimp-countdown'),
        'description' => __('Below the signup form half width, on the left.', 'wpchimp-countdown'),
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'id' => 'wpchimpcs-landing-right',
        'name' => __('Landing Page Right', 'wpchimp-countdown'),
        'description' => __('Below the signup form half width, on the right.', 'wpchimp-countdown'),
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'id' => 'wpchimpcs-blog-right',
        'name' => __('Blog Right', 'wpchimp-countdown'),
        'description' => __('On the right of the blog pages.', 'wpchimp-countdown'),
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'wpchimpcs_register_sidebars');

function wpchimpcs_customization($customizer) {
    require_once 'admin/controls.php';
    require_once 'admin/customizations.php';
}
add_action('customize_register', 'wpchimpcs_customization');

function wpchimpcs_theme_setup() {
    load_theme_textdomain('wpchimp-countdown', get_template_directory() . '/languages' );

    register_nav_menu('landing-top', __('Landing page top menu', 'wpchimp-countdown'));
    register_nav_menu('landing-footer', __('Landing page footer menu', 'wpchimp-countdown'));
    register_nav_menu('blog-top', __('Blog top menu', 'wpchimp-countdown'));
    register_nav_menu('blog-footer', __('Blog footer menu', 'wpchimp-countdown'));
    
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list'));
    add_theme_support('automatic-feed-links');

    add_editor_style();
}
add_action('after_setup_theme', 'wpchimpcs_theme_setup');

function wpchimpcs_enqueue_scripts()
{
    wp_enqueue_style('wpchimpcs-main-style', get_stylesheet_uri());
    wp_enqueue_style('wpchimpcs-open-sans', '//fonts.googleapis.com/css?family=Open+Sans:300,600');
    
    require_once 'style.php';
    wp_add_inline_style('wpchimpcs-main-style', wpchimpcs_get_inline_style());
        
    if(is_singular() && get_option('thread_comments'))
        wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'wpchimpcs_enqueue_scripts');

function wpchimcs_landing_form() {
    $form = '';
    if(function_exists('wpchimp_form'))
         $form = wpchimp_form(get_theme_mod('wpchimpcs_form_name'), false);
    else
        $form = get_theme_mod('wpchimpcs_form_html');

    if(empty($form) && current_user_can('edit_theme_options'))
        $form = __('Please define a form in the theme settings.', 'wpchimp-countdown');

    return $form;
}

function wpchimpcs_time_to_release() {
    $date_str = get_theme_mod('wpchimpcs_release_date');
    if(empty($date_str))
        $date_str = date('Y-m-d', strtotime('now +27days'));

    $old_tz = date_default_timezone_get();
    date_default_timezone_set(get_option('timezone_string', $old_tz));

    $total_seconds = strtotime($date_str) - time();
    if($total_seconds < 0)
        $total_seconds = 0;

    date_default_timezone_set($old_tz);

    $time = array();
    $time['days'] = floor($total_seconds / 60 / 60 / 24 );
    $time['hours'] = floor($total_seconds / 60 / 60 - $time['days'] * 24);
    $time['minutes'] = floor($total_seconds / 60 - $time['days'] * 24 * 60 - $time['hours'] * 60);
    $time['seconds'] = floor($total_seconds - $time['days'] * 24 * 60 * 60 - $time['hours'] * 60 * 60 - $time['minutes'] * 60);

    return $time;
}

function wpchimpcs_title($title, $sep) {
    if(empty($title))
        return get_bloginfo('name');

    return get_bloginfo('name') . $title;
}
add_filter('wp_title', 'wpchimpcs_title', 10, 2);

function wpchimp_esc($content) {
    return wp_kses($content, wp_kses_allowed_html('post'));
}

function wpchimp_esc_html_form($content) {
    return preg_replace("/<script.*?>.*?<\/script>/is", "", $content);
}

require_once 'admin/notice.php';
add_action('admin_notices', 'wpchimpcs_print_admin_notice');

require_once 'admin/default_settings.php';
wpchimpcs_set_defaults();
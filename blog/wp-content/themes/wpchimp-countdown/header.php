<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <meta name="viewport" content="width=device-width">
    
    <title><?php wp_title(); ?></title>
    <meta name="description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php if(!is_front_page() || get_option('show_on_front') != 'page') { ?>
<div class="container">
    <h1 class="blog-title"><a href="<?php echo esc_url(home_url()); ?>">
        <?php echo esc_html(get_bloginfo('title')); ?>
    </a></h1>
    <div class="blog-description"><?php esc_html(get_bloginfo('description')); ?></div>

    <?php wp_nav_menu(array('theme_location'=>'blog-top', 'container_class'=>'blog-top-menu', 'depth'=>-1, 'fallback_cb'=>  create_function('$args', ''))); ?>
</div>
<?php } ?>

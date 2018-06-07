    <div class="container"><div class="footer-info">
        <?php if(!is_front_page() || get_option('show_on_front') != 'page') { ?>
        <div class="spaced">
            <?php wp_nav_menu(array('theme_location'=>'blog-footer', 'container_class'=>'blog-footer-menu', 'depth'=>-1, 'fallback_cb'=>  create_function('$args', ''))); ?>
        </div>
        <?php } ?>

        <small>
            <?php
            // translators: %1$s expands to the current year and %2$s expands to the site's name.
            echo sprintf(__('Copyright &copy; %1$s %2$s', 'wpchimp-countdown'), date('Y'), esc_html(get_bloginfo('name')));
            ?>

            <?php
            /*
             If you want to remove the credit links feel free to do so. All you need
             to do is edit the lines below.
             */
            ?>
            |
            <?php
            // translators: %1$s expands to the WordPress link and %2$s expands to the WPChimp Countdown link.
            echo sprintf(__('Proudly powered by %1$s and %2$s', 'wpchimp-countdown'),
                        '<a href="http://wordpress.org/" class="muted">WordPress</a>',
                        '<a href="' . wp_get_theme()->get('ThemeURI') . '" rel="nofollow" class="muted">WPChimp Countdown</a>'); ?>
        </small>
    </div></div>

    <?php wp_footer(); ?>
</body>
</html>
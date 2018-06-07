<?php
function wpchimpcs_print_error_notice($msg, $wp_error) {
    ?>
    <div class="error" style="width: 100%;">
        <p><?php echo $msg; ?></p>
        <p><?php echo $wp_error->get_error_message(); ?></p>
    </div>
    <?php
}

function wpchimpcs_print_success_notice($msg) {
    ?>
    <div class="updated" style="width: 100%;">
        <p><?php echo $msg; ?></p>
    </div>
    <?php
}

function wpchimpcs_print_admin_notice() {
    $user = wp_get_current_user();
    
    if(!current_user_can('edit_theme_options') ||
       get_user_meta($user->ID, 'wpchimp-countdown-disable-admin-nag', true) ||
       get_option('show_on_front') == 'page')
        return;

    if(isset($_POST['wpchimp-countdown-disable-admin-nag']) &&
       $_POST['wpchimp-countdown-disable-admin-nag']) {
        update_user_meta($user->ID, 'wpchimp-countdown-disable-admin-nag', true);
        return;
    }

    if(isset($_POST['wpchimp-countdown-autoconf']) &&
       $_POST['wpchimp-countdown-autoconf']) {
        $countdown_id = wp_insert_post(array(
            'post_name' => __('countdown', 'wpchimp-countdown'),
            'post_title' => __('Countdown', 'wpchimp-countdown'),
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'template-coming-soon.php'
        ), true);

        if(is_wp_error($countdown_id)) {
            wpchimpcs_print_error_notice(
                    __('Failed to create the Countdown page.', 'wpchimp-countdown'),
                    $countdown_id);
            return;
        }

        $posts_id = wp_insert_post(array(
            'post_name' => __('blog', 'wpchimp-countdown'),
            'post_title' => __('Blog', 'wpchimp-countdown'),
            'post_status' => 'publish',
            'post_type' => 'page'
        ), true);

        if(is_wp_error($posts_id)) {
            wpchimpcs_print_error_notice(
                    __('Failed to create the Blog page.', 'wpchimp-countdown'),
                    $posts_id);
            return;
        }

        update_option('page_on_front', $countdown_id);
        update_option('page_for_posts', $posts_id);
        update_option('show_on_front', 'page');

        wpchimpcs_print_success_notice(__('Static front page configured successfuly.', 'wpchimp-countdown'));
        return;
    }
    ?>
    <div class="update-nag" style="width: 100%;">
        <p>
        <?php _e('WPChimp Countdown needs a static front page with our custom template to show the countdown clock.', 'wpchimp-countdown'); ?>
        </p>
        <p>
        <?php _e('You can either make the changes yourself or have the theme configure your site automatically (it will create two new pages "Countdown" and "Blog").', 'wpchimp-countdown'); ?>
        </p>
        <p>
        <a href="<?php echo wp_get_theme()->get('ThemeURI'); ?>" target="_blank">
            <?php _e('Manual Configuration Instructions', 'wpchimp-countdown'); ?>
        </a>
        </p>

        <form action="" method="post" style="display: inline-block;">
            <input type="hidden" name="wpchimp-countdown-autoconf" value="1">
            <input type="submit" value="<?php _e('Configure Automatically', 'wpchimp-countdown'); ?>" class="button button-small button-primary">
        </form>     

        <form action="" method="post" style="display: inline-block;">
            <input type="hidden" name="wpchimp-countdown-disable-admin-nag" value="1">
            <input type="submit" value="<?php _e('Never Show This Again', 'wpchimp-countdown'); ?>" class="button button-small">
        </form>
    </div>
    <?php
}
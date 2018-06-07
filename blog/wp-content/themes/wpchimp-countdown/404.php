<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h2><?php _e('Page Not Found', 'wpchimp-countdown'); ?></h2>
            <?php _e('We\'re sorry but the page you are looking for does not exist.', 'wpchimp-countdown'); ?>
        </div>

        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer();

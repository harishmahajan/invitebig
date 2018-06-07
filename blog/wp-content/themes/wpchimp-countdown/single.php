<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php while(have_posts()) { the_post(); ?>

            <div <?php post_class('post'); ?>>
                <h2><?php the_title(); ?></h2>
                <small class="post-meta">
                    Posted on <?php the_time(get_option('date_format')); ?> in <?php the_category(', '); ?> by
                    <?php the_author_link(); ?>.

                    <?php edit_post_link(__('Edit.', 'wpchimp-countdown')); ?>
                </small>

                <div class="post-content">
                    <?php the_content(__('Continue reading...', 'wpchimp-countdown')); ?>

                    <div class="clearfix"></div>
                    <?php wp_link_pages(); ?>
                </div>

                <small>
                    <?php the_tags('Tags: '); ?>
                </small>
            </div>

            <?php comments_template(); ?>
            <?php } ?>

            <div class="clearfix"></div>
        </div>

        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer();

<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php while(have_posts()) { the_post(); ?>

            <div <?php post_class('post'); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <small class="post-meta">
                    Posted on <?php the_time(get_option('date_format')); ?> in <?php the_category(', '); ?>
                </small>

                <div class="post-content">
                    <?php the_content(__('Continue reading...', 'wpchimp-countdown')); ?>

                    <div class="clearfix"></div>
                    <?php wp_link_pages(); ?>
                </div>
            </div>

            <?php } ?>

            <div class="pull-left">
            <?php previous_posts_link(); ?>
            </div>

            <div class="pull-right">
            <?php next_posts_link(); ?>
            </div>

            <div class="clearfix"></div>
        </div>
        
        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer();

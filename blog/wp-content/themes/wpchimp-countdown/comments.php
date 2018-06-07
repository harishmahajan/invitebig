<div class="comments">
    <?php if(have_comments()) { ?>
        <small class="comment-count">
        <?php comments_number(__('Be the first to comment!', 'wpchimp-countdown')); ?>
        </small>

        <ul class="comment-list">
        <?php wp_list_comments(); ?>
        </ul>

        <?php paginate_comments_links(); ?>
    <?php } ?>

    <?php comment_form(); ?>
</div>
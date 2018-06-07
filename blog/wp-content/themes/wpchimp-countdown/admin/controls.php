<?php
class wpchimpcs_FormChooser extends WP_Customize_Control {
    function __construct($manager, $id, $args = array()) {
        if(class_exists('WPChimp_FormStorage'))
        {
            $args['type'] = 'select';
            $args['choices'] = array('none' => ' ');

            $forms = WPChimp_FormStorage::get()->get_array();
            foreach($forms as $form)
                $args['choices'][$form->get_name()] = $form->get_name();
        }
        
        parent::__construct($manager, $id, $args);
    }

    function render_content() {
        if(!empty($this->choices))
        {
            parent::render_content();
            return;
        }

        ?>
        <p><?php _e('You have not defined any forms. Please create a WPChimp form to use on your landing page.', 'wpchimp-countdown'); ?></p>
        <?php
    }
}

class wpchimpcs_TextboxControl extends WP_Customize_Control {
    public $placeholder = '';
    
    function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <textarea style="width: 100%; height: 6em;" <?php $this->link(); ?> placeholder="<?php echo $this->placeholder; ?>">
                <?php echo wp_kses($this->value(), wp_kses_allowed_html('post')); ?>
            </textarea>
        </label>
        <?php
    }
}

class wpchimpcs_FormHtml extends wpchimpcs_TextboxControl {
    function render_content() {
        ?>
        <span>
            <?php
            // translators: %s will expand to a MailChimp link.
            _e('Copy-paste your MailChimp form\'s HTML below.', 'wpchimp-countdown');
            ?>
        </span>
        <?php
        parent::render_content();
        ?>
        <span>
            <?php
            // translators: %s will expand to a WPChimp link.
            echo sprintf(__('This theme supports the %s plugin. It will create MailChimp forms for you.', 'wpchimp-countdown'), '<a href="http://wpchimp.com/?wpchimpcs">WPChimp</a>');
            ?>
        </span>
        <?php
    }
}

class wpchimpcs_DateControl extends WP_Customize_Control {
    function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <input type="date" <?php $this->link(); ?> value="<?php echo esc_attr($this->value()); ?>">
        </label>
        <?php
    }
}


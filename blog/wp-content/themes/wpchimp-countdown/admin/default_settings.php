<?php
function wpchimpcs_set_defaults() {
    if(get_theme_mod('wpchimpcs_counter_glow', '') == '')
        set_theme_mod('wpchimpcs_counter_glow', '#85bfee');

    if(get_theme_mod('wpchimpcs_link_color', '') == '')
        set_theme_mod('wpchimpcs_link_color', '#429ce4');

    if(get_theme_mod('wpchimpcs_background_color', '') == '')
        set_theme_mod('wpchimpcs_background_color', '#fefefe');

    if(get_theme_mod('wpchimpcs_foreground_color', '') == '')
        set_theme_mod('wpchimpcs_foreground_color', '#4e4e4e');

    if(get_theme_mod('wpchimpcs_release_date', '') == '')
        set_theme_mod('wpchimpcs_release_date', date('Y-m-d', strtotime('now +27days')));

    if(get_theme_mod('wpchimpcs_form_name', '') == '')
        set_theme_mod('wpchimpcs_form_name', 'none');
}


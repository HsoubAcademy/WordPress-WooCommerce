<?php

add_action('customize_register', 'storefront_child_customizer_hdisplay');
function storefront_child_customizer_hdisplay($wp_customize)
{
    $wp_customize->add_section('storefront_header_display_theme', array(
        'title' => __('Header Display', 'storefront-child'),
        'panel' => 'storefront-child',
    ));

    $wp_customize->add_setting('storefront_header_theme', array(
        'type' => 'theme_mod', // or 'option'
        'capability' => 'edit_theme_options',
        'theme_supports' => '', // Rarely needed.
        'default' => 'theme1',
        'transport' => 'postMessage', // or postMessage
        'sanitize_callback' => '',
        'sanitize_js_callback' => '', // Basically to_json.
    ));

    $wp_customize->add_control('storefront_header_theme', array(
        'type' => 'radio',
        'priority' => 10, // Within the section.
        'section' => 'storefront_header_display_theme', // Required, core or custom.
        'label' => __('Header Theme', 'storefront-child'),
        'description' => __('Change the header display theme', 'storefront-child'),
        'choices' => array(
            'theme1' => __('Theme 1', 'storefront-child'),
            'theme2' => __('Theme 2', 'storefront-child'),
            'theme3' => __('Theme 3', 'storefront-child'),
            'theme4' => __('Theme 4', 'storefront-child'),
        ),
    ));
}

if (get_theme_mod('storefront_header_theme') == 'theme1') {
    return;
} elseif (get_theme_mod('storefront_header_theme') == 'theme2') {
    add_action('get_footer', function () {
        wp_enqueue_style('header-theme2', get_stylesheet_directory_uri() . '/assets/css/header-theme2.css');
    });
} elseif (get_theme_mod('storefront_header_theme') == 'theme3') {
    add_action('get_footer', function () {
        wp_enqueue_style('header-theme3', get_stylesheet_directory_uri() . '/assets/css/header-theme3.css');
    });
} elseif (get_theme_mod('storefront_header_theme') == 'theme4') {
    add_action('get_footer', function () {
        wp_enqueue_style('header-theme4', get_stylesheet_directory_uri() . '/assets/css/header-theme4.css');
    });
}
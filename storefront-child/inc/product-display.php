<?php

add_action('customize_register', 'storefront_child_customizer_pdisplay');
function storefront_child_customizer_pdisplay($wp_customize)
{
    $wp_customize->add_section('storefront_prodcut_display_theme', array(
        'title' => __('Product Display', 'storefront-child'),
        'panel' => 'storefront-child',
    ));

    $wp_customize->add_setting('storefront_product_theme', array(
        'type' => 'theme_mod', // or 'option'
        'capability' => 'edit_theme_options',
        'theme_supports' => '', // Rarely needed.
        'default' => 'theme2',
        'transport' => 'refresh', // or postMessage
        'sanitize_callback' => '',
        'sanitize_js_callback' => '', // Basically to_json.
    ));

    $wp_customize->add_control('storefront_product_theme', array(
        'type' => 'radio',
        'priority' => 10, // Within the section.
        'section' => 'storefront_prodcut_display_theme', // Required, core or custom.
        'label' => __('Product Theme', 'storefront-child'),
        'description' => __('Change the products display in the product archive page', 'storefront-child'),
        'choices' => array(
            'theme1' => __('Top', 'storefront-child'),
            'theme2' => __('Bottom', 'storefront-child'),
            'theme3' => __('Above', 'storefront-child'),
        ),
    ));
}

if (get_theme_mod('storefront_product_theme') == 'theme1') {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price');
    remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title');

    add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_title', 20);
    add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_price', 30);
    add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 40);
}
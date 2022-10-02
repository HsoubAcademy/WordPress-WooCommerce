<?php
function storefront_child_setup() {
    $path = get_stylesheet_directory().'/languages';
    load_child_theme_textdomain( 'storefront-child', $path );
}
add_action( 'after_setup_theme', 'storefront_child_setup' );
/*
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles' );
function storefront_child_enqueue_styles() {
    $parenthandle = 'storefront-style'; // This is 'storefront-style' for the Storefront theme.
    $theme = wp_get_theme();
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
        array(),  // if the parent theme code has a dependency, copy it to here
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'storefront-child-style', get_stylesheet_uri(),
        array( $parenthandle ),
        $theme->get('Version') // this only works if you have Version in the style header
    );
}
*/
/**
* Set a minimum order amount for checkout
*/
add_action( 'woocommerce_checkout_process', 'wc_minimum_order_amount' );
add_action( 'woocommerce_before_cart' , 'wc_minimum_order_amount' );
function wc_minimum_order_amount() {
	// Set this variable to specify a minimum order value
    $minimum = 25;
    if ( WC()->cart->total < $minimum ) {
    	if( is_cart() ) {
    		wc_print_notice(
    		sprintf( __('Your current order total is %s — you must have an order with a minimum of %s to place your order ','storefront-child') ,
    		wc_price( WC()->cart->total ),
    		wc_price( $minimum )
    		), 'error'
    		);
    	} else {
    		wc_add_notice(
    		sprintf( __('Your current order total is %s — you must have an order with a minimum of %s to place your order','storefront-child') ,
    		wc_price( WC()->cart->total ),
    		wc_price( $minimum )
    		), 'error'
    		);
    	}
	}
}

/**
 * Add a new country to countries list
 */
add_filter( 'woocommerce_countries',  'wc_add_my_country' );
function wc_add_my_country( $countries ) {
  $new_countries = array(
	                    'NIRE'  => __( 'Northern Ireland', 'storefront-child' ),
	                    );

	return array_merge( $countries, $new_countries );
}

add_filter( 'woocommerce_continents', 'wc_add_my_country_to_continent' );
function wc_add_my_country_to_continent( $continents ) {
	$continents['EU']['countries'][] = __( 'NIRE', 'storefront-child' );
	return $continents;
}

/**
 * Online Summit Description
 */
function wpcp_show_desc(  ) { 
	global $product;
	if($product->get_type() == 'wcp_online_summit')
	{
		echo '<div class="summit-desc">
		<p><strong>'.esc_html__('Event Starts at: ','storefront-child').'</strong><i>' .esc_html__($product->get_meta( 'wpcp_online_summit_time_from', true )) .
		'</i><strong> ' .esc_html__($product->get_meta( 'wpcp_online_summit_time_zone', true )) . '</strong></p>'.
		'<p><strong>'.esc_html__('Event Ends at: ','storefront-child').'</strong><i>' .esc_html__($product->get_meta( 'wpcp_online_summit_time_to', true )) .
		'</i><strong> ' .esc_html__($product->get_meta( 'wpcp_online_summit_time_zone', true )) . '</strong></p></div>';
	}
};
add_action( 'woocommerce_single_product_summary', 'wpcp_show_desc', 5 ); 

/**
 * remove storefront sidebar
 */
function remove_storefront_sidebar() {
    if ( is_shop() || is_product()) {
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    }
}
add_action( 'get_header', 'remove_storefront_sidebar' );

/**
 * remove storefront footer widgets
 */
function storefront_footer_widgets() {
	return;
}

/**
 * storefront credit change
 */
function storefront_credit() {
	?>
	<div class="site-info">
		<?php echo esc_html__(  'All Rights Reserved &copy; ' . get_bloginfo( 'name' ) , 'storefront-child'); ?>
	</div><!-- .site-info -->
	<?php
}

/**
 * remove storefront breadcrumbs
 */
function storefront_remove_storefront_breadcrumbs() {
	if ( is_shop() ) {
   remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	}
}
add_action( 'storefront_before_header', 'storefront_remove_storefront_breadcrumbs' );

/**
 * remove storefront shop page title
 */
function not_a_shop_page() { 
    return boolval(!is_shop());
}
add_filter( 'woocommerce_show_page_title', 'not_a_shop_page' );

/**
 * remove sale flash
 */
function remove_sale_flash() 
{
	return false;
}
add_filter('woocommerce_sale_flash', 'remove_sale_flash');

/**
 * add the sale badge before the product image 
 */
function sale_badge_before_the_product_image(  ) {
	do_action( 'storefront_child_before_product_image' );
	$html = '<div class="shop-thumbnail-wrap">';
	global $product;

	if ( $product->is_on_sale() ) 
	{ $html .= '<span class="onsale">' . esc_html__( 'Sale!', 'storefront-child' ) . '</span>';}
	echo apply_filters( 'storefront_child_sale_flash_filter', $html );
 }; 
add_action( 'woocommerce_before_shop_loop_item_title', 'sale_badge_before_the_product_image', 9); 

/**
 * close the shop-thumbnail-wrap wrapper div before the title
 */
function close_the_shop_thumbnail_wrapper(  ) {
	echo '<div class="overlay overlayFade"></div>';
	echo '</div><!-- action_woocommerce_shop_loop_item_title -->';
}; 	  
add_action( 'woocommerce_before_shop_loop_item_title', 'close_the_shop_thumbnail_wrapper',11 ); 

function storefront_child_customizer_preview_js() {
	wp_enqueue_script( 'storefront_child_customizer_preview', get_stylesheet_directory_uri() . '/assets/js/customizer.js',  array( 'jquery','customize-preview' ) , true );
}
add_action( 'customize_preview_init', 'storefront_child_customizer_preview_js' );

/**
 * Testing our own do_action hook
 */
//function storefront_child_hook_test() {
//    echo '<h2>Test text</h2>';
//}
//add_action( 'storefront_child_before_product_image', 'storefront_child_hook_test' );

/**
 * Testing our own apply_filters hook
 */
//function storefront_child_filter_hook_test( $html ) {
//    return str_replace('onsale', 'onsale extra-class', $html );
//}
//add_filter( 'storefront_child_sale_flash_filter', 'storefront_child_filter_hook_test' );

require 'inc/wcp-online-summit.php';
require 'inc/share-buttons.php';
require 'inc/product-display.php';
require 'inc/header-display.php';
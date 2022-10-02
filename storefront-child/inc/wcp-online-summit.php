<?php
/**
 * Add a new product type
 */
function online_summit_register_product_type()
{
    if (class_exists('WC_Product')) {
        class WC_Online_Summit_Product extends WC_Product
        {             
            public function __construct($product)
            {
                parent::__construct($product);
            }
            public function get_type()
            {
                return 'wcp_online_summit';
            }
            public function is_virtual() {
                return true;
            }
            public function add_to_cart_text() {
                return __( 'Reserve Your Spot', 'storefront-child' );
            }
        }
    }
}
add_action('init', 'online_summit_register_product_type');

/**
 * Adding the newly created product type class to woocommerce classes
 */
function wcp_online_summit_product_class( $php_classname, $product_type ) {
	if ( $product_type == 'wcp_online_summit' ) {
		$php_classname = 'WC_Online_Summit_Product';
	}
	return $php_classname;
}
add_filter( 'woocommerce_product_class', 'wcp_online_summit_product_class',10,2);

/**
 * Adding the newly created product type to the product type selector
 */
function add_online_summit_product($product_types)
{
    $product_types['wcp_online_summit'] = __('Online Summit', 'storefront-child');
    return $product_types;
}
add_filter('product_type_selector', 'add_online_summit_product');

/**
 * Hide unnecessary tabs for the online summit
 */
function wcp_online_summit_hide_data_tabs($tabs)
{
    $tabs['inventory']['class'][] = 'show_if_wcp_online_summit';
    $tabs['attribute']['class'][] = 'hide_if_wcp_online_summit';
    $tabs['linked_product']['class'][] = 'hide_if_wcp_online_summit';
    $tabs['shipping']['class'][] = 'hide_if_wcp_online_summit';
    $tabs['advanced']['class'][] = 'hide_if_wcp_online_summit';
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'wcp_online_summit_hide_data_tabs');

/**
 * Show necessary tabs for the online summit
 */
function wcp_online_summit_custom_js() {
    if ( 'product' != get_post_type() ){
    return;
    }
    ?>
    <script type='text/javascript'>
    jQuery(document).ready(function ($) {
        $("#product-type").change(function () {
            if ($("#product-type").val() == 'wcp_online_summit') {
                //show General tab
                $('.options_group.pricing').addClass('show_if_wcp_online_summit').show();
                $(".general_tab").show();
                //show Tax fields
                $('select[name="_tax_status"]').val('none');
                $('._tax_status_field').parent().addClass('show_if_wcp_online_summit').show();
            	//for Inventory tab
            	$('#inventory_product_data ._manage_stock_field').addClass('show_if_wcp_online_summit').show();
            	$('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_wcp_online_summit').show();
            	$('#inventory_product_data ._sold_individually_field').addClass('show_if_wcp_online_summit').show();
            }
        });

        $("#product-type").trigger('change');
        
         $('.summit_dates_fields').each(function () {
            $(this).find('input').datepicker({
                defaultDate: '',
                dateFormat: 'yy-mm-dd',
                numberOfMonths: 1,
                showButtonPanel: true,
                onSelect: function () {
                    var datepicker = $(this);
                    option = $(datepicker).next().is('.hasDatepicker') ? 'minDate' : 'maxDate',
                        otherDateField = 'minDate' === option ? $(datepicker).next() : $(datepicker).prev(),
                        date = $(datepicker).datepicker('getDate');

                    $(otherDateField).datepicker('option', option, date);
                    $(datepicker).change();
                }
            });
        });
    });
    </script><?php
}
add_action( 'admin_footer', 'wcp_online_summit_custom_js' );

/**
 * Adding the online summit tab
 */
function wcp_online_summit_product_tabs($tabs)
{
    $tabs['online_summit'] = array(
        'label' => __('Online Summit', 'storefront-child'),
        'target' => 'wcp_online_summit_options',
        'class' => array('show_if_wcp_online_summit'),
    );
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'wcp_online_summit_product_tabs');

/**
 * Adding the online summit tab content fields
 */
function wcp_online_summit_tab_content() {
  global $product_object; ?>
  <div id='wcp_online_summit_options' class='panel woocommerce_options_panel'><?php
    woocommerce_wp_text_input(
        array( 'id' => 'wpcp_online_summit_name',
        'label' => __( 'Online Summit Name', 'storefront-child' ),
        'desc_tip' => 'true', 'description' => __( 'Enter the online summit name or title.', 'storefront-child' ), 'type' => 'text',
        'value' => $product_object->get_meta( 'wpcp_online_summit_name', true )
    ) );
    woocommerce_wp_text_input(
     array( 'id' => 'wpcp_online_summit_time_from',
        'label' => __( 'Starting Time', 'storefront-child' ),
        'desc_tip' => 'true', 'description' => __( 'Enter the exact hour for the start.', 'storefront-child' ), 'type' => 'text',
        'value' => $product_object->get_meta('wpcp_online_summit_time_from', true )
    ) );
    woocommerce_wp_text_input(
        array( 'id' => 'wpcp_online_summit_time_to',
           'label' => __( 'Ending Time', 'storefront-child' ),
           'desc_tip' => 'true', 'description' => __( 'Enter the exact hour for the end.', 'storefront-child' ), 'type' => 'text',
           'value' => $product_object->get_meta( 'wpcp_online_summit_time_to', true )
       ) );
    woocommerce_wp_text_input(
    array( 'id' => 'wpcp_online_summit_time_zone',
        'label' => __( 'Time Zone', 'storefront-child' ),
        'desc_tip' => 'true', 'description' => __( 'Enter the summit time zone.', 'storefront-child' ), 'type' => 'text',
        'value' => $product_object->get_meta( 'wpcp_online_summit_time_zone', true )
    ) );
    
    echo '<div class="summit_dates_fields">
	<p class="form-field summit_date_from_field" style="display:block;">
    <label for="_summit_date_from">' . esc_html__( 'Summit Date Range', 'storefront-child' ) . '</label>
    ' . wc_help_tip( __("Enter in which dates the summit will start and finish", "storefront-child") ) . '
    <input type="text" class="short" name="_summit_date_from" id="_summit_date_from" value="' . esc_attr( $product_object->get_meta( '_summit_date_from', true ) ) . '" placeholder="' . esc_html( _x( 'From&hellip;', 'placeholder', 'storefront-child' ) ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
    </p>
    <p class="form-field summit_date_to_field" style="display:block;">
    <input type="text" class="short" name="_summit_date_to" id="_summitdate_to" value="' . esc_attr( $product_object->get_meta( '_summit_date_to', true ) ) . '" placeholder="' . esc_html( _x( 'To&hellip;', 'placeholder', 'storefront-child' ) ) . '  YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" /> </div>';
    ?>
  </div>
  <?php
}
add_action( 'woocommerce_product_data_panels', 'wcp_online_summit_tab_content' );

/**
 * Store the online summit fields content to the database
 */
function wcp_online_summit_save_product_fields($product_id)
{
    if (current_user_can('manage_options')) {
        $summit_name = isset($_POST['wpcp_online_summit_name']) ? $_POST['wpcp_online_summit_name'] : '';
        update_post_meta($product_id, 'wpcp_online_summit_name', $summit_name);

        $summit_from = isset($_POST['wpcp_online_summit_time_from']) ? $_POST['wpcp_online_summit_time_from'] : '';
        update_post_meta($product_id, 'wpcp_online_summit_time_from', $summit_from);

        $summit_to = isset($_POST['wpcp_online_summit_time_to']) ? $_POST['wpcp_online_summit_time_to'] : '';
        update_post_meta($product_id, 'wpcp_online_summit_time_to', $summit_to);

        $summit_timezone = isset($_POST['wpcp_online_summit_time_zone']) ? $_POST['wpcp_online_summit_time_zone'] : '';
        update_post_meta($product_id, 'wpcp_online_summit_time_zone', $summit_timezone);

        $summit_start_date = isset($_POST['_summit_date_from']) ? $_POST['_summit_date_from'] : '';
        update_post_meta($product_id, '_summit_date_from', $summit_start_date);
        
        $summit_start_date = isset($_POST['_summit_date_to']) ? $_POST['_summit_date_to'] : '';
        update_post_meta($product_id, '_summit_date_to', $summit_start_date);

        //SET THE PRODUCT CATEGORIES
        wp_set_object_terms($product_id, array('Events'), 'product_cat');

        //SET THE PRODUCT TAGS
        wp_set_object_terms($product_id, array('Online Summit', 'virtual'), 'product_tag');
    }
}
add_action('woocommerce_process_product_meta_wcp_online_summit', 'wcp_online_summit_save_product_fields');

/**
 * Add css class name to the html body tag to know that we are on an online summit product page
 */
function wcp_online_summit_type_in_body_class( $classes ){
    if(is_product())
    {
	// get the current product in the loop
	$product = wc_get_product();
        if($product->get_type() == 'wcp_online_summit')
        {
            $classes[] = 'product-type-wcp_online_summit';
        }
    }
	return $classes;
}
add_filter('body_class','wcp_online_summit_type_in_body_class');

/**
 * Add the add to cart button to the prodcut page
 */
add_action( "woocommerce_wcp_online_summit_add_to_cart", function() {
    do_action( 'woocommerce_simple_add_to_cart' );
});
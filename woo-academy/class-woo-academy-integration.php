<?php
if ( ! class_exists( 'Woo_Academy_Integration' ) ) :
class Woo_Academy_Integration extends WC_Integration {
  /**
   * Init and hook in the integration.
   */
  public function __construct() {
    global $woocommerce;
    $this->id                 = 'woo-academy-integration';
    $this->method_title       = __( 'Woo Academy Integration', 'woo-academy' );
    $this->method_description = __( 'Woo Academy Integration to add plugin specific settings to WooCommerce.', 'woo-academy' );
    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();
    // Define user set variables.
    $this->currency_pairs          = $this->get_option('currency_pairs');
    $this->auto_currency_pairs          = $this->get_option('auto_currency_pairs');
    if (isset($this->auto_currency_pairs) && $this->auto_currency_pairs == "yes") {
        //$this->get_currencies_from_api();
        add_action('get_currencies_from_api_hourly', array($this, 'get_currencies_from_api'), 10);
    }
    // Actions.
    add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
    add_shortcode('currency_selector', array($this, 'display_select_currency'));
  }
  /**
   * Initialize integration settings form fields.
   */
  public function init_form_fields() {
    $this->form_fields = array(
      'currency_pairs' => array(
        'title'             => __('Currency Pairs', 'woo-academy'),
        'type'              => 'textarea',
        'description'       => __('Enter Currencies and thier values that corresponds to the main currency exchange rate in the store and separate each pair with a new line following
        this pattern >> Currency:Value(new line)' . "\n" . ' Ex: EUR:1.13' . "\n", 'woo-academy'),
        'desc_tip'          => true,
        'default'           => '',
        'css'               => 'width:100%;',
      ),
      'auto_currency_pairs' => array(
        'title'             => __('Auto Update Currency Pairs', 'woo-academy'),
        'type'              => 'checkbox',
        'description'       => __('Check to enable auto currency pairs updates from external API.', 'woo-academy'),
        'desc_tip'          => true,
        'default'           => '',
      ),
    );
  }

  public function validate_currency_pairs_field($key, $value)
  {
    try {
        if (!empty($value)) {
            $lines = explode("\n", $value);
            $pairs = array();
            foreach ($lines as $line) {
                $pair = explode(":", $line);
                if (empty($pair[1]))
                    throw new Exception;
                $pairs[$pair[0]] = $pair[1];
            }
            update_option('woo_currency_pairs', $pairs);
            return $value;
        }
    } catch (\Throwable $th) {
        WC_Admin_Settings::add_error(esc_html__('Looks like you made a mistake with the Currency Pairs field. Make sure to follow the correct pattern!', 'woo-academy'));
        return $this->currency_pairs;
    }
 }

 public function get_currencies_from_api()
 {
     $pairs = get_option('woo_currency_pairs');
     $currencies = '';
     $last_key = array_key_last($pairs);
     foreach ($pairs as $key => $value) {
         if ($key == $last_key) {
             $currencies .= $key;
         } else {
             $currencies .= $key . ',';
         }
     }
     $apikey = 'rXyqOk62ZVQzKBc1k7IovPY9mnoR8azMrrpJ3t7A';
     $base_currency = get_woocommerce_currency();

     $url = "https://api.currencyapi.com/v3/latest?apikey=$apikey&base_currency=$base_currency&currencies=$currencies";
     $response = wp_remote_get($url);
     $body = wp_remote_retrieve_body($response);
     $json = json_decode($body, true);

     if (isset($json) && isset($json['data'])) {
         $data = $json['data'];
         $currency_field = '';
         $pairs = array();
         foreach ($data as $currency) {
             $currency_field .= $currency['code'] . ':' . $currency['value'] . "\n";
             $pairs[$currency['code']] = $currency['value'];
         }
         $this->update_option('currency_pairs', $currency_field);
         update_option('woo_currency_pairs', $pairs);
     }
 }

 public function display_select_currency()
 {
    $cur_selected = 'not set';
    if (isset($_COOKIE['currency'])) {
        $cur_selected = $_COOKIE['currency'];
    }
    $pairs = get_option('woo_currency_pairs');
    echo '<form action="" method="post" id="currency_selector"><label for="select-currency">' . esc_html__('Currency:', 'woo-academy') . ' </label>
    <select name="currency_selector" id="select-currency">
    <option value="' .  get_woocommerce_currency() . '">' . get_woocommerce_currency() . '</option>';

    foreach ($pairs as $key => $value) {
        $selected =  ($cur_selected == $key) ? 'selected' : '';
        $option = '<option ' . $selected . ' value="' . $key . '">' . $key . '</option>';
        echo $option;
    }
    echo '</select><input type="submit" value="' . esc_html__('Set', 'woo-academy') . '"></form>';
 }
}
endif; 
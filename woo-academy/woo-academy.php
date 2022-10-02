<?php
/*
 Plugin Name: Woo Academy
 Description: WooCommerce add-on plugin example
 Version: 1.0.0
 Author: Hsoub Academy
 Author URI: https://academy.hsoub.com/
 License: GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: woo-academy
 Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// only run if there's no other class with this name
if (!class_exists('Woo_Academy')) {
    class Woo_Academy
    {
        private static $instance = null;

        const PLUGIN_VERSION = '1.0.0';

        // Minimum PHP version required by this plugin.
        const MINIMUM_PHP_VERSION = '7.0.0';

        // Minimum WordPress version required by this plugin.
        const MINIMUM_WP_VERSION = '4.4';

        // Minimum WooCommerce version required by this plugin.
        const MINIMUM_WC_VERSION = '3.5.0';
        
        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        protected function __construct()
        {
            register_activation_hook(__FILE__, array($this, 'activation_check'));
            add_action('admin_init', array($this, 'init_plugin'));
            add_action( 'plugins_loaded', array( $this, 'init_integration' ) );

            add_action('woocommerce_before_main_content', array($this,  'add_currency_selector'), 90);

            add_action('wp_ajax_set_currency_cookie', array($this, 'set_currency_cookie'));
            add_action('wp_ajax_nopriv_set_currency_cookie', array($this, 'set_currency_cookie'));
            add_action('wp_enqueue_scripts', array($this, 'woo_academy_enqueue_assets'));

            add_filter('woocommerce_product_get_price',  array($this, 'change_price'));
            add_filter('woocommerce_product_variation_get_price', array($this, 'change_price'));
            add_filter('woocommerce_product_get_regular_price', array($this, 'change_price'));
            add_filter('woocommerce_product_get_sale_price', array($this, 'change_price'));
            add_filter('woocommerce_currency_symbol', array($this, 'change_existing_currency_symbol'), 10, 2);

            register_deactivation_hook(__FILE__, array($this, 'on_deactivation'));
        }

        function woo_academy_enqueue_assets()
        {
            // Register the script
            wp_register_script('set_currency_cookie_ajax', plugin_dir_url(__FILE__) . '/assets/js/ajax.js', array('jquery'));

            // Localize the script with new data
            $parameters = array('ajaxurl' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('set_currency_cookie'));
            wp_localize_script('set_currency_cookie_ajax', 'set_currency_cookie_ajax', $parameters);

            // Enqueued script with localized data.
            wp_enqueue_script('set_currency_cookie_ajax');
        }

        function change_existing_currency_symbol($currency_symbol, $currency)
        {
            if (!isset($_COOKIE['currency']) || $_COOKIE['currency'] == $currency) {
                return $currency_symbol;
            } else {
                $currency_symbol = get_woocommerce_currency_symbol($_COOKIE['currency']);
                return $currency_symbol;
            }
        }

        function change_price($price)
        {
            if ($price < 1.0 || !isset($_COOKIE['currency']) || $_COOKIE['currency'] == get_woocommerce_currency()) {
                return $price;
            }
            $pairs = get_option('woo_currency_pairs');
            foreach ($pairs as $key => $value) {
                if ($_COOKIE['currency']  == $key) {
                    return floatval($price) * floatval($value);
                }
            }
            return $price;
        }

        function set_currency_cookie()
        {
            if (!wp_verify_nonce($_POST['nonce'], "set_currency_cookie")) {

                exit("Security check error");
            }
            $result = array();
            if (isset($_POST["currency_selector"])) {
                unset($_COOKIE['currency']);
                setcookie("currency", $_POST["currency_selector"], time() + 86400, '/');
                $result['type'] = "success";
                $result = json_encode($result);
                echo $result;
            }
            die();
        }

        function add_currency_selector()
        {
            return do_shortcode('[currency_selector]');
        }

        function activation_check()
        {
            if (!version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=')) {
                $this->deactivate_plugin();
                wp_die(__('Woo Academy could not be activated. The minimum required PHP version is ' . self::MINIMUM_PHP_VERSION, 'woo-academy'));
            }
            if (!wp_next_scheduled('get_currencies_from_api_hourly')) {
                wp_schedule_event(time(), 'hourly', 'get_currencies_from_api_hourly');
            }
        }

        protected function deactivate_plugin()
        {
            deactivate_plugins(plugin_basename(__FILE__));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }

        function on_deactivation()
        {
            wp_clear_scheduled_hook('get_currencies_from_api_hourly');
        }

        public function init_plugin()
        {
            if (!$this->is_compatible()) {
                return;
            }
            // register the new order status.
            $this->register_building_order_status();
            // add to list of WooCommerce order statuses.
            add_filter('wc_order_statuses', array($this, 'add_building_to_order_statuses'));
        }

        public function init_integration() {
            if ( class_exists( 'WC_Integration' ) ) {
                // Include our integration class.
                include_once 'class-woo-academy-integration.php';
                // Register the integration.
                add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
            }
            $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
            load_plugin_textdomain( 'woo-academy', false, $plugin_rel_path );
            // Setting action for plugin
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'Woo_academy_action_links') );
        }

        public function register_building_order_status()
        {
            register_post_status('wc-building', array(
                'label' => _x( 'Building', 'Woo Order status', 'woo-academy' ),
                'public' => false,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop( 'Building <span class="count">(%s)</span>', 'Building <span class="count">(%s)</span>', 'woo-academy' )
            ));
        }

        public function add_integration( $integrations ) {
            $integrations[] = 'Woo_Academy_Integration';
            return $integrations;
        }

        function Woo_academy_action_links( $links ) {

            $links[] = '<a href="'. menu_page_url( 'wc-settings', false ) .'&tab=integration&section=woo-academy-integration">'
            .esc_html__('Settings', 'woo-academy' ).'</a>';
            return $links;
        }

        public function add_building_to_order_statuses($order_statuses)
        {
            $new_order_statuses = array();
            // add new order status after processing
            foreach ($order_statuses as $key => $status) {
                $new_order_statuses[$key] = $status;
                if ('wc-processing' === $key) {
                    $new_order_statuses['wc-building'] = esc_html__('Building', 'woo-academy' );
                }
            }
            return $new_order_statuses;
        }

        public function is_compatible()
        {
            // Check for the required WordPress version
            if (version_compare(get_bloginfo('version'), self::MINIMUM_WP_VERSION, '<')) {
                add_action('admin_notices', [$this, 'admin_notice_minimum_wordpress_version']);
                $this->deactivate_plugin();
                return false;
            }

            // Check if WooCommerce is activated
            if (!defined('WC_VERSION') || version_compare(WC_VERSION, self::MINIMUM_WC_VERSION, '<')) {
                add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
                $this->deactivate_plugin();
                return false;
            } else if (class_exists('woocommerce')) {
                return true;
            } else {
                add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
                $this->deactivate_plugin();
                return false;
            }
            return true;
        }

        public function admin_notice_missing_woocommerce()
        {
            $woocommerce = 'woocommerce/woocommerce.php';
            $pathpluginurl = WP_PLUGIN_DIR . '/' . $woocommerce;
            $isinstalled = file_exists($pathpluginurl);
            if ($isinstalled && !is_plugin_active($woocommerce)) {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $woocommerce . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $woocommerce);
                $message = sprintf(__('%1$sWoo Academy%2$s requires %1$s"WooCommerce"%2$s plugin to be active. Please activate WooCommerce to continue.', 'woo-academy'), '<strong>', '</strong>');
                $button_text = esc_html__('Activate WooCommerce', 'woo-academy');
            } else {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');
                $message = sprintf(__('%1$sWoo Academy%2$s requires %1$s"WooCommerce"%2$s plugin to be installed and activated. Please install WooCommerce to continue.', 'woo-academy'), '<strong>', '</strong>');
                $button_text = esc_html__('Install WooCommerce', 'woo-academy');
            }
            $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
            printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button);
        }

        public function admin_notice_minimum_wordpress_version()
        {
            $message = sprintf(
                esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'woo-academy'),
                '<strong>' . esc_html__('Woo Academy', 'woo-academy') . '</strong>',
                '<strong>' . esc_html__('WordPress', 'woo-academy') . '</strong>',
                self::MINIMUM_WP_VERSION
            );
            printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
        }
    }
}
// fire it up!
Woo_Academy::instance();
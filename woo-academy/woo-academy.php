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
            add_filter( 'gettext', array($this, 'translate_building_oreder_status'), 10, 3 );
        }

        function translate_building_oreder_status( $translated, $untranslated, $domain ) {
        
            if ( 'woo-academy' === $domain && get_bloginfo("language") == 'ar' ) {
                    if ( 'Building' === $untranslated ) {
                        $translated = 'قيد البناء';
                    }
                }
            return $translated;
        }

        public function activation_check()
        {
            if (!version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=')) {
                $this->deactivate_plugin();
                wp_die(__('Woo Academy could not be activated. The minimum required PHP version is ' . self::MINIMUM_PHP_VERSION, 'woo-academy'));
            }
        }

        protected function deactivate_plugin()
        {
            deactivate_plugins(plugin_basename(__FILE__));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }

        public function init_plugin()
        {
            if (!$this->is_compatible()) {
                return;
            }
            // register the new order status
            $this->register_building_order_status();
            // add to list of WooCommerce order statuses
            add_filter('wc_order_statuses', array($this, 'add_building_to_order_statuses'));
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
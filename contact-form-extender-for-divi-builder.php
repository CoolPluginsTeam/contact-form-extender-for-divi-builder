<?php
/*
Plugin Name: Contact Form Extender for Divi
Plugin URI: https://coolplugins.net/product/contact-form-extender-for-divi-builder/
Description: Extend Divi contact form module with file upload field, save form submissions in database, and many advanced form features.
Version:     1.0.3
Author:      Satinder Singh
Author URI:  https://profiles.wordpress.org/satindersingh/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: contact-form-extender-for-divi-builder
*/

namespace CFEFD;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

define( 'CFEFD_PLUGIN_FILE', __FILE__ );
define( 'CFEFD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CFEFD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFEFD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CFEFD_PLUGIN_VERSION', '1.0.3' );
define('CFEFD_FEEDBACK_URL', 'https://feedback.coolplugins.net/');

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

register_activation_hook( __FILE__, array( 'CFEFD\CFEFD_Main', 'cfefd_activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'CFEFD\CFEFD_Main', 'cfefd_deactivate_plugin' ) );


if(!class_exists('CFEFD_Main')) { 
    class CFEFD_Main {

        /**
         * The single instance of the class.
         *
         * @var CFEFD_Main
         */
        private static $instance = null;

        /**
         * Main CFEFD_Main Instance.
         *
         * Ensures only one instance of CFEFD_Main is loaded or can be loaded.
         *
         * @return CFEFD_Main - Main instance.
         */
        public static function get_instance() {
            if ( null == self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * CFEFD_Main Constructor.
         */
        private function __construct() {
            $this->includes();

            if(wp_get_theme()->get('Name') === 'Divi'){
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'cfefd_plugin_dashboard_link' ) );
                add_action( 'activated_plugin', array( $this, 'cfefd_plugin_redirection' ) );
            }
        }

        public function cfefd_plugin_dashboard_link($links){
			$settings_link = '<a href="' . admin_url( 'admin.php?page=contact-form-extender-for-divi-builder' ) . '">Settings</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}
        
        public function cfefd_plugin_redirection( $plugin ) {
			if ( $plugin == CFEFD_PLUGIN_BASENAME ) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
				exit( wp_safe_redirect( admin_url( 'admin.php?page=contact-form-extender-for-divi-builder' ) ) );
			}
		}
        /**
         * Include required files.
         */
        private function includes() {
            require_once CFEFD_PLUGIN_DIR . 'includes/class-cfefd-loader.php';
            \CFEFD_Loader::get_instance();

            if(!class_exists('CFEFD\Admin\CPFM_Feedback_Notice')){
				require_once CFEFD_PLUGIN_DIR . 'admin/feedback/cpfm-common-notice.php';
			}
            
            require_once CFEFD_PLUGIN_DIR . 'admin/feedback/cron/cfefd-class-cron.php';

            if ( is_admin() ) {
				require_once CFEFD_PLUGIN_DIR . 'admin/feedback/admin-feedback-form.php';
			}
        }

        public static function cfefd_activate_plugin() {
            update_option( 'cfefd-v', CFEFD_PLUGIN_VERSION );
			update_option( 'cfefd-type', 'FREE' );
			update_option( 'cfefd-installDate', gmdate( 'Y-m-d h:i:s' ) );

            if (!get_option( 'cfefd_initial_version' ) ) {
                add_option( 'cfefd_initial_version', CFEFD_PLUGIN_VERSION );
            }

            if(!get_option( 'cfefd-install-date' ) ) {
				add_option( 'cfefd-install-date', gmdate('Y-m-d h:i:s') );
        	}

            $settings       = get_option('cfef_usage_share_data');

			
			if (!empty($settings) || $settings === 'on'){
				
				static::cfefd_cron_job_init();
			}
        }

        public static function cfefd_cron_job_init(){
			if (!wp_next_scheduled('cfefd_extra_data_update')) {
				wp_schedule_event(time(), 'every_30_days', 'cfefd_extra_data_update');
			}
		}

        public static function cfefd_deactivate_plugin() {
            if (wp_next_scheduled('cfefd_extra_data_update')) {
            	wp_clear_scheduled_hook('cfefd_extra_data_update');
        	}
        }

    }

    CFEFD_Main::get_instance();
}
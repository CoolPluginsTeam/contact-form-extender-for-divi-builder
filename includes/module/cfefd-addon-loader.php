<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    CFEFD
 * @subpackage CFEFD/frontend
 */
if(!class_exists('CFEFD_Addons_Loader')) { 
    class CFEFD_Addons_Loader {

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param    string    $plugin_name       The name of this plugin.
         * @param    string    $version    The version of this plugin.
         */
        public function __construct($plugin_name, $version) {
            $this->plugin_name = $plugin_name;
            $this->version = $version;

            $this->load_addons();
        }

        /**
         * Load enabled addons for the active Divi version.
         *
         * @since 1.0.0
         */
        public function load_addons() {
            require_once CFEFD_PLUGIN_DIR . 'includes/utils/class-cfefd-utils.php';

            if ( CFEFD_Utils::is_divi_5() ) {
                $this->load_divi_5_addons();
            } else {
                $this->load_divi_4_addons();
            }
        }

        public function load_divi_4_addons() {
            require_once CFEFD_PLUGIN_DIR . 'includes/utils/class-cfefd-utils.php';

            if (CFEFD_Utils::is_field_enabled('file_upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/file-upload/class-cfefd-file-upload.php';
                new CFEFD_File_Upload();
            }
            if (CFEFD_Utils::is_field_enabled('country_code')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/country-code/class-cfefd-country-code-field.php';
                new CFEFD_Country_Code_Field();
            }
        }

        public function load_divi_5_addons() {
            require_once CFEFD_PLUGIN_DIR . 'includes/utils/class-cfefd-utils.php';

            // Load shared File Upload utility class (needed for both range slider and file upload)
            if (!class_exists('CFEFD_File_Upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/file-upload/class-cfefd-file-upload.php';
            }

            if (CFEFD_Utils::is_field_enabled('file_upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-5/file-upload/class-cfefd-file-upload-field.php';
                new CFEFD_File_Upload_D5();
            }
            if (CFEFD_Utils::is_field_enabled('country_code')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-5/country-code/class-cfefd-country-code-field.php';
                new CFEFD_Country_Code_D5();
            }
        }
    }
}

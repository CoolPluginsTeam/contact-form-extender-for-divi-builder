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
         * Check if a field is enabled in the settings.
         *
         * @since 1.0.0
         * @param string $field_key The field key.
         * @return bool True if the field is enabled, false otherwise.
         */
        private function is_field_enabled($field_key) {
            $enabled_elements = get_option('cfefd_enabled_elements', array());
            return in_array(sanitize_key($field_key), array_map('sanitize_key', $enabled_elements));

        }

        public function load_addons() {
            // Check if Divi 5 is enabled
            // if(wp_get_theme()->get('Version') === '4.27.4'){
            //     $this->load_divi_4_addons();
            // }else{
            //     $this->load_divi_5_addons();
            // }

            if (wp_get_theme('Divi')->get('Version') >= 5) {
                $this->load_divi_5_addons();
            } else {
                $this->load_divi_4_addons();
            }
        }

        public function load_divi_4_addons() {
            if ($this->is_field_enabled('range_slider')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/range-slider-field.php';
                new CFEFD_Range_Slider();
            }

            if ($this->is_field_enabled('file_upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/file-upload/class-cfefd-file-upload.php';
                new CFEFD_File_Upload();
            }

            if ($this->is_field_enabled('country_code')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/country-code/class-cfefd-country-code-field.php';
                new CFEFD_Country_Code_Field();
            }
        }

        public function load_divi_5_addons() {
            // Load shared File Upload utility class (needed for both range slider and file upload)
            if (!class_exists('CFEFD_File_Upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/file-upload/class-cfefd-file-upload.php';
            }

            if ($this->is_field_enabled('file_upload')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-5/file-upload/class-cfefd-file-upload-field.php';
                new CFEFD_File_Upload_D5();
            }

            if ($this->is_field_enabled('country_code')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-5/country-code/class-cfefd-country-code-field.php';
                new CFEFD_Country_Code_D5();
            }
        }
    }
}

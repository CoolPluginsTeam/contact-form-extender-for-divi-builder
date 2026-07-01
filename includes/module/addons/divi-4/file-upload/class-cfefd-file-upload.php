<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('CFEFD_File_Upload')) {
    class CFEFD_File_Upload {
        
        const foldername = 'cfefd-uploads';

        public function __construct() {
            $this->load_dependencies();
            $this->init_components();
        }

        private function load_dependencies() {
            require_once plugin_dir_path(__FILE__) . 'class-cfefd-file-upload-settings.php';
            require_once plugin_dir_path(__FILE__) . 'class-cfefd-file-upload-render.php';
            require_once plugin_dir_path(__FILE__) . 'class-cfefd-file-upload-ajax.php';
            require_once plugin_dir_path(__FILE__) . 'class-cfefd-file-upload-submission.php';
        }

        
        private function init_components() {
            new CFEFD_File_Upload_Settings();
            new CFEFD_File_Upload_Render();
            new CFEFD_File_Upload_Ajax();
            new CFEFD_File_Upload_Submission();
        }

        public static function get_wp_upload_dir($folder, $base = null){
            $wp_upload_dir = wp_upload_dir();
            $directory = [
                'basedir' => path_join($wp_upload_dir['basedir'], $folder),
                'baseurl' => path_join($wp_upload_dir['baseurl'], $folder),
            ];

            return $directory[$base] ?? $directory;
        }

        /**
         * @deprecated Use CFEFD_Utils::file_upload_encrypt_decrypt() instead.
         */
        public static function encrypt_decrypt( $string = '', $encrypt_decrypt = 'e' ) {
            return CFEFD_Utils::file_upload_encrypt_decrypt( $string, $encrypt_decrypt );
        }
    }
}

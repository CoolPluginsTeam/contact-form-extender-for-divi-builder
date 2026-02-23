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

        public static function encrypt_decrypt($string = '', $encrypt_decrypt = 'e'){
            $output = null;
            $secret_key = 'wbtZKk}rohV^Uw7V?+pgtNG++R2@hT3La.A)u*8+MK]-l?pM&,lfs{79SvXu/';
            $secret_iv = 'w}W<<gj~+$S.TzRZ,=n*P@B{Ma{MnR(0baJ<zU|V7wCvl)&gC@4%+pth_-=|jRJo';
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ($encrypt_decrypt === 'e') {
                $output = base64_encode(openssl_encrypt($string, "AES-256-CBC", $key, 0, $iv)); // phpcs:ignore
            } elseif ($encrypt_decrypt === 'd') {
                $output = openssl_decrypt(base64_decode($string), "AES-256-CBC", $key, 0, $iv); // phpcs:ignore
            }

            return $output;
        }

        public static function get_wp_allowed_mime_types(){
            $allowed_mime_type = [];
            foreach (get_allowed_mime_types() as $key => $value) {
                if ('css' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['htm|html'] = 'text/html';
                } elseif ('rtf' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['js'] = 'application/javascript';
                } else {
                    $allowed_mime_type[$key] = $value;
                }
            }

            return $allowed_mime_type;
        }
    }
}

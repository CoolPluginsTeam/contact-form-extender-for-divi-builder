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
         * Per-installation secret for upload tokens. Legacy decrypt uses previous hardcoded material when needed.
         *
         * @return string
         */
        private static function get_token_secret() {
            $secret = get_option( 'cfefd_file_upload_token_secret' );
            if ( empty( $secret ) || ! is_string( $secret ) ) {
                $secret = wp_generate_password( 64, true, true );
                add_option( 'cfefd_file_upload_token_secret', $secret, '', 'no' );
            }
            return $secret;
        }

        /**
         * Derive AES key/IV using the same layout as legacy (two strings → hashed key + IV).
         *
         * @param string $secret_key Raw secret for key hash.
         * @param string $secret_iv  Raw string for IV derivation.
         * @return array{0:string,1:string} Key and IV for openssl.
         */
        private static function derive_key_iv( $secret_key, $secret_iv ) {
            $key = hash( 'sha256', $secret_key );
            $iv  = substr( hash( 'sha256', $secret_iv ), 0, 16 );
            return array( $key, $iv );
        }

        /**
         * @param string $string Payload.
         * @param string $mode    'e' encrypt, 'd' decrypt.
         * @param array{0:string,1:string} $material Key and IV from derive_key_iv.
         * @return string|false
         */
        private static function openssl_aes_cbc( $string, $mode, $material ) {
            list( $key, $iv ) = $material;
            if ( 'e' === $mode ) {
                $encrypted = openssl_encrypt( $string, 'AES-256-CBC', $key, 0, $iv ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
                return false !== $encrypted ? base64_encode( $encrypted ) : false;
            }
            if ( 'd' === $mode ) {
                $decoded = base64_decode( $string, true );
                if ( false === $decoded ) {
                    return false;
                }
                return openssl_decrypt( $decoded, 'AES-256-CBC', $key, 0, $iv ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
            }
            return false;
        }

        /**
         * Whether decrypted payload looks like a valid upload token JSON object.
         *
         * @param mixed $plain Result of openssl decrypt.
         * @return bool
         */
        private static function is_valid_token_payload( $plain ) {
            if ( ! is_string( $plain ) || '' === $plain ) {
                return false;
            }
            $data = json_decode( $plain, true );
            return is_array( $data ) && isset( $data['mimetypes'], $data['size'] );
        }

        /**
         * Encrypt or decrypt the file upload token (AES-256-CBC). Encrypt uses per-site secret; decrypt tries site secret then legacy hardcoded key.
         *
         * @param string $string            Plaintext (encrypt) or ciphertext (decrypt).
         * @param string $encrypt_decrypt   'e' or 'd'.
         * @return string|false
         */
        public static function encrypt_decrypt( $string = '', $encrypt_decrypt = 'e' ) {
            if ( 'e' === $encrypt_decrypt ) {
                $root = self::get_token_secret();
                $iv_source = hash( 'sha256', $root . '|dcfe_iv' );
                $material  = self::derive_key_iv( $root, $iv_source );
                return self::openssl_aes_cbc( $string, 'e', $material );
            }
            if ( 'd' === $encrypt_decrypt ) {
                $root = self::get_token_secret();
                $iv_source = hash( 'sha256', $root . '|dcfe_iv' );
                $material  = self::derive_key_iv( $root, $iv_source );
                $plain       = self::openssl_aes_cbc( $string, 'd', $material );
                if ( self::is_valid_token_payload( $plain ) ) {
                    return $plain;
                }
            }
            return false;
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

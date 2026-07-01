<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('CFEFD_Utils')) {
    /**
     * General utility functions for the plugin.
     *
     * @package CFEFD
     * @subpackage CFEFD/includes/utils
     */
    class CFEFD_Utils {

        /**
         * Build show_if / show_if_not so only one custom "use as" mode
         * can be active for a Contact Field at a time.
         *
         * @param string      $mode       Logical mode key, e.g. 'file', 'country'.
         * @param string|null $field_type Field type, e.g. 'input'. Use null to omit a field_type rule.
         *
         * @return array{show_if: array, show_if_not: array}
         */
        public static function get_mode_conditions($mode, $field_type) {
            $map = array(
                'file'        => 'cfefd_use_as_file_upload',
                'country'     => 'cfefd_use_as_country_code',
                'toggle'      => 'cfefd_use_as_toggle',
                'date'        => 'cfefd_use_as_date_picker',
                'signature'   => 'cfefd_use_as_signature_field',
                'image_radio' => 'cfefd_use_as_image_radio',
                'calculator'  => 'cfefd_use_as_calculator',
                'rating'      => 'cfefd_use_as_rating',
                'currency'    => 'cfefd_use_as_currency',
                'wysiwyg'     => 'cfefd_use_as_wysiwyg',
                'range'       => 'cfefd_use_as_range_slider',
            );

            $show_if = array();
            if (null !== $field_type && '' !== $field_type) {
                $show_if['field_type'] = $field_type;
            }

            $show_if_not = array();
            foreach ($map as $key => $option_id) {
                if ($key === $mode) {
                    $show_if[$option_id] = 'on';
                } else {
                    $show_if_not[$option_id] = 'on';
                }
            }

            return array(
                'show_if'     => $show_if,
                'show_if_not' => $show_if_not,
            );
        }


        /**
         * Create DOMDocument from HTML string.
         *
         * @param string|mixed $html HTML content. Non-strings (e.g. arrays from Divi builder/heartbeat) are treated as empty.
         * @return DOMDocument
         */
        public static function create_dom( $html ) {
            if ( is_array( $html ) || is_object( $html ) ) {
                $html = '';
            } elseif ( ! is_string( $html ) ) {
                $html = is_scalar( $html ) ? (string) $html : '';
            }

            $charset = 'utf-8';
            $dom     = new DOMDocument( '1.0', $charset );
            libxml_use_internal_errors( true );

            if ( function_exists( 'mb_encode_numericentity' ) ) {
                $html = mb_encode_numericentity( $html, array( 0x80, 0x10FFFF, 0, 0x1FFFFF ), $charset );
            } elseif ( function_exists( 'mb_convert_encoding' ) ) {
                $html = mb_convert_encoding( $html, 'HTML-ENTITIES', $charset );
            } else {
                $html = htmlentities( $html, ENT_QUOTES | ENT_SUBSTITUTE, $charset );
            }

            $dom->loadHTML( $html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            libxml_clear_errors();

            return $dom;
        }

        /**
         * Check whether a form element is enabled in plugin settings.
         *
         * @since 1.1.4
         * @param string $field_key Settings key for the form element.
         * @return bool
         */
        public static function is_field_enabled( $field_key ) {
            static $enabled_elements = null;

            if ( null === $enabled_elements ) {
                $enabled_elements = array_map(
                    'sanitize_key',
                    (array) get_option( 'cfefd_enabled_elements', array() )
                );
            }

            return in_array( sanitize_key( $field_key ), $enabled_elements, true );
        }

        /**
         * Get Divi theme version from active theme, parent, or direct lookup.
         *
         * @since 1.1.4
         * @return string
         */
        public static function get_divi_version() {
            $theme      = wp_get_theme();
            $divi_theme = ( 'divi' === strtolower( (string) $theme->get( 'Template' ) ) ) ? $theme : $theme->parent();

            if ( ! $divi_theme || 'divi' !== strtolower( (string) $divi_theme->get( 'Template' ) ) ) {
                $divi_theme = wp_get_theme( 'Divi' );
            }

            return (string) $divi_theme->get( 'Version' );
        }

        /**
         * Whether the active site is running Divi 5.0 or newer.
         *
         * @since 1.1.4
         * @return bool
         */
        public static function is_divi_5() {
            return version_compare( self::get_divi_version(), '5.0', '>=' );
        }

        /**
         * WordPress allowed MIME types for file uploads, excluding HTML and JS.
         *
         * @since 1.1.4
         * @return array<string, string>
         */
        public static function get_wp_allowed_mime_types() {
            $allowed_mime_type = get_allowed_mime_types();

            unset( $allowed_mime_type['htm|html'] );
            unset( $allowed_mime_type['js'] );

            return $allowed_mime_type;
        }

        /**
         * Map comma-separated file extensions to allowed MIME types for upload tokens/UI.
         *
         * @since 1.1.4
         * @param string $data Comma-separated extensions (e.g. ".jpg,.png").
         * @return array{keys: string, values: string}
         */
        public static function process_multiple_mimes_checkboxes_value( $data ) {
            $extensions = array_unique(
                array_filter(
                    array_map(
                        static function ( $ext ) {
                            return ltrim( trim( $ext ), '.' );
                        },
                        explode( ',', $data )
                    )
                )
            );

            if ( empty( $extensions ) ) {
                return array(
                    'keys'   => '',
                    'values' => '',
                );
            }

            $allowed_mimes      = self::get_wp_allowed_mime_types();
            $matched_mime_types = array();

            foreach ( $allowed_mimes as $ext_group => $mime_type ) {
                $group_exts = explode( '|', $ext_group );

                if ( array_intersect( $extensions, $group_exts ) ) {
                    $matched_mime_types[] = $mime_type;
                }
            }

            return array(
                'keys'   => implode( ',', array_unique( $matched_mime_types ) ),
                'values' => ' ' . implode( ', ', $extensions ),
            );
        }

        /**
         * Per-installation secret for upload tokens. Legacy decrypt uses previous hardcoded material when needed.
         *
         * @return string
         */
        private static function get_file_upload_token_secret() {
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
        private static function derive_file_upload_key_iv( $secret_key, $secret_iv ) {
            $key = hash( 'sha256', $secret_key );
            $iv  = substr( hash( 'sha256', $secret_iv ), 0, 16 );
            return array( $key, $iv );
        }

        /**
         * @param string $string Payload.
         * @param string $mode    'e' encrypt, 'd' decrypt.
         * @param array{0:string,1:string} $material Key and IV from derive_file_upload_key_iv.
         * @return string|false
         */
        private static function openssl_aes_cbc_file_upload( $string, $mode, $material ) {
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
        private static function is_valid_file_upload_token_payload( $plain ) {
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
        public static function file_upload_encrypt_decrypt( $string = '', $encrypt_decrypt = 'e' ) {
            if ( 'e' === $encrypt_decrypt ) {
                $root = self::get_file_upload_token_secret();
                $iv_source = hash( 'sha256', $root . '|dcfe_iv' );
                $material  = self::derive_file_upload_key_iv( $root, $iv_source );
                return self::openssl_aes_cbc_file_upload( $string, 'e', $material );
            }
            if ( 'd' === $encrypt_decrypt ) {
                $root = self::get_file_upload_token_secret();
                $iv_source = hash( 'sha256', $root . '|dcfe_iv' );
                $material  = self::derive_file_upload_key_iv( $root, $iv_source );
                $plain       = self::openssl_aes_cbc_file_upload( $string, 'd', $material );
                if ( self::is_valid_file_upload_token_payload( $plain ) ) {
                    return $plain;
                }
            }
            return false;
        }
    }
}

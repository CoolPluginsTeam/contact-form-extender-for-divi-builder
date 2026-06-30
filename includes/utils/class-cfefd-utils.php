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
         * Load TinyMCE / editor scripts on the frontend.
         */
        public static function enqueue_wp_editor_for_frontend() {
            add_filter('user_can_rich_edit', '__return_true', 99);
            wp_enqueue_editor();
            remove_filter('user_can_rich_edit', '__return_true', 99);
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
    }
}

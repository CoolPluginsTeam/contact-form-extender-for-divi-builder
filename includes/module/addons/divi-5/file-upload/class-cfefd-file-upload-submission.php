<?php

if (!defined('ABSPATH')) {
    die;
}

class CFEFD_File_Upload_Submission_D5 {

    private static $attachments = [];

    public function __construct() {
        add_action( 'et_pb_contact_form_submit', [ $this, 'capture_files' ], 10, 3 );
        add_filter( 'wp_mail', [ $this, 'attach_files' ], 999 );
    }

    /**
     * Capture uploaded files from Divi 5 submission
     */
    public function capture_files( $fields, $has_error, $form_info ) {
        if ( $has_error ) {
            return;
        }

        $form_id = $form_info['contact_form_id'] ?? '';
        $contact_form_number = isset( $form_info['contact_form_number'] ) ? (string) $form_info['contact_form_number'] : '';
        if ( ! preg_match( '/et_pb_contact_form_(.+)$/', $form_id, $matches ) ) {
            return;
        }
        $unique_id = $matches[1];

        $nonce_key = '_wpnonce-et-pb-contact-form-submitted-' . $unique_id;
        if (
            empty( $_POST[ $nonce_key ] ) ||
            ! wp_verify_nonce(
                sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ),
                'et-pb-contact-form-submit-' . $unique_id
            )
        ) {
            return; // Invalid nonce
        }

        self::$attachments = [];

        $upload_tmp_dir = CFEFD_File_Upload::get_wp_upload_dir(
            path_join( CFEFD_File_Upload::foldername, 'tmp' ),
            'basedir'
        );
        $tmp_root            = realpath( $upload_tmp_dir );
        $tmp_root_normalized = false !== $tmp_root ? trailingslashit( wp_normalize_path( $tmp_root ) ) : '';

        // Only consider our own marker fields that end with '_is_file'
        $marker_keys = array_filter(
            array_keys( $_POST ),
            static function ( $key ) {
                return is_string( $key ) && substr( $key, -8 ) === '_is_file';
            }
        );

        foreach ( $marker_keys as $key ) {

            // Your hidden marker field
            $value = isset( $_POST[ $key ] ) ? sanitize_text_field(wp_unslash( $_POST[ $key ] )) : '';
            if ( $value !== 'yes' ) {
                continue;
            }

            $input_name = str_replace( '_is_file', '', $key );

            // Ensure this field belongs to the current form number.
            if ( strpos( $input_name, "et_pb_contact_{$contact_form_number}_" ) !== 0 ) {
                continue;
            }

            if ( empty( $_POST[ $input_name ] ) ) {
                continue;
            }

            $file_names = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
            if ( empty( $file_names ) ) {
                continue;
            }

            $files = explode( ',', $file_names );
            foreach ( $files as $file ) {
                $file = sanitize_file_name( $file );
                if ( '' === $file || '' === $tmp_root_normalized ) {
                    continue;
                }
                $file_path = path_join( $upload_tmp_dir, $file );
                $file_real = realpath( $file_path );
                if ( false === $file_real ) {
                    continue;
                }
                $file_real_normalized = wp_normalize_path( $file_real );
                if ( 0 !== strpos( $file_real_normalized, $tmp_root_normalized ) ) {
                    continue;
                }
                if ( is_file( $file_real ) ) {
                    self::$attachments[] = $file_real;
                }
            }
        }
    }

    /**
     * Attach files to Divi email
     */
    public function attach_files( $args ) {

        if ( empty( self::$attachments ) ) {
            return $args;
        }

        if ( empty( $args['attachments'] ) ) {
            $args['attachments'] = [];
        }

        if ( ! is_array( $args['attachments'] ) ) {
            $args['attachments'] = explode( "\n", $args['attachments'] );
        }

        $args['attachments'] = array_merge( $args['attachments'], self::$attachments );

        register_shutdown_function( [ $this, 'cleanup' ] );

        return $args;
    }

    /**
     * Cleanup temp files
     */
    public function cleanup() {
        foreach ( self::$attachments as $file ) {
            if ( file_exists( $file ) ) {
                wp_delete_file( $file );
            }
        }

        self::$attachments = [];
    }
}

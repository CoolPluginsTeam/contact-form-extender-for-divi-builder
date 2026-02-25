<?php

namespace CFEFD\Submissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CFEFD_Submissions_Handler
 *
 * Handles capturing and storing Divi Contact Form submissions.
 */
class CFEFD_Submissions_Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook into Divi's contact form submission action.
		add_action( 'et_pb_contact_form_submit', [ $this, 'cfefd_save_submission' ], 10, 3 );
	}

	/**
	 * Captures and saves the form submission data.
	 *
	 * @param array $processed_fields_values Field values processed by Divi.
	 * @param bool  $et_contact_error       Whether there was a validation error.
	 * @param array $contact_form_info      Information about the submitted form.
	 */
	public function cfefd_save_submission( $processed_fields_values, $et_contact_error, $contact_form_info ) {
		// If there's a validation error, don't save the submission
		if ( $et_contact_error ) {
			return;
		}

		// Extract basic info
		$form_id           = isset( $contact_form_info['contact_form_id'] ) ? sanitize_text_field( $contact_form_info['contact_form_id'] ) : 'unknown';
		$form_unique_id    = isset( $contact_form_info['contact_form_unique_id'] ) ? sanitize_text_field( $contact_form_info['contact_form_unique_id'] ) : '';
		$et_pb_contact_num = isset( $contact_form_info['contact_form_number'] ) ? sanitize_text_field( $contact_form_info['contact_form_number'] ) : '';

		// Verify nonce for security
		$nonce_result = isset( $_POST[ "_wpnonce-et-pb-contact-form-submitted-{$et_pb_contact_num}" ] ) && 
						wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ "_wpnonce-et-pb-contact-form-submitted-{$et_pb_contact_num}" ] ) ), 'et-pb-contact-form-submit' );
		
		if ( ! $nonce_result ) {
			return;
		}

		// Extract field definitions from $_POST (visible fields)
		$field_definitions = [];
		if ( '' !== $et_pb_contact_num ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string is processed and elements are sanitized after decoding.
			$visible_fields_json = isset( $_POST[ "et_pb_contact_email_fields_{$et_pb_contact_num}" ] ) ? wp_unslash( $_POST[ "et_pb_contact_email_fields_{$et_pb_contact_num}" ] ) : '';
			if ( ! empty( $visible_fields_json ) ) {
				$visible_fields = json_decode( str_replace( '\\', '', $visible_fields_json ), true );
				if ( is_array( $visible_fields ) ) {
					foreach ( $visible_fields as $field ) {
						if ( isset( $field['field_id'] ) ) {
							$field_definitions[ $field['field_id'] ] = $field;
						}
					}
				}
			}

			// Extract hidden fields from $_POST
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string is processed and elements are sanitized after decoding.
			$hidden_fields_json = isset( $_POST[ "et_pb_contact_email_hidden_fields_{$et_pb_contact_num}" ] ) ? wp_unslash( $_POST[ "et_pb_contact_email_hidden_fields_{$et_pb_contact_num}" ] ) : '';
			if ( ! empty( $hidden_fields_json ) ) {
				$hidden_fields = json_decode( str_replace( '\\', '', $hidden_fields_json ), true );
				if ( is_array( $hidden_fields ) ) {
					foreach ( $hidden_fields as $hidden_field ) {
						$meta_field_id = "et_pb_contact_{$hidden_field}_{$et_pb_contact_num}_cond_meta";
						$cond_meta = isset( $_POST[ $meta_field_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_field_id ] ) ) : '';
						$meta_array = json_decode( base64_decode( $cond_meta ), true );
						$cond_field_title = isset( $meta_array['title'] ) ? sanitize_text_field( $meta_array['title'] ) : '';
						$cond_field_type = isset( $meta_array['type'] ) ? sanitize_text_field( $meta_array['type'] ) : '';
						$field_id = "et_pb_contact_{$hidden_field}_{$et_pb_contact_num}";
						$field_definitions[ $field_id ] = [
							'field_id' => $field_id,
							'original_id' => $hidden_field,
							'field_label' => $cond_field_title,
							'field_type' => $cond_field_type,
						];
					}
				}
			}
		}

		// Process form data from $_POST
		$form_data = [];
		$sender_email = '';
		
		foreach ( $field_definitions as $field_id => $field_info ) {
			$field_label = isset( $field_info['field_label'] ) ? $field_info['field_label'] : $field_id;
			$field_type = isset( $field_info['field_type'] ) ? $field_info['field_type'] : 'input';
			$original_id = isset( $field_info['original_id'] ) ? $field_info['original_id'] : $field_id;
			
			// Get field value from $_POST
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Value is sanitized below based on field type.
			$field_value = isset( $_POST[ $field_id ] ) ? wp_unslash( $_POST[ $field_id ] ) : '';
			
			// Sanitize based on field type
			if ( 'text' === $field_type ) {
				$field_value = sanitize_textarea_field( $field_value );
			} elseif ( is_email( $field_value ) ) {
				$field_value = sanitize_email( $field_value );
				// Capture sender email (prioritize email fields)
				if ( empty( $sender_email ) || 'email' === $field_type ) {
					$sender_email = $field_value;
				}
			} elseif ( isset( $_POST[ "{$field_id}_is_file" ] ) ) {
				$field_type = 'file';
				$field_value = sanitize_text_field( $field_value );
			}  else {
				$field_value = sanitize_text_field( $field_value );
			}
			
			$form_data[ $field_id ] = [
				'id' => $original_id,
				'label' => $field_label,
				'value' => stripslashes( $field_value ),
				'type' => $field_type,
			];
		}

		// Get referer URL from $_POST if available, otherwise use wp_get_referer()
		$referer_url = '';
		if ( ! empty( $_POST['_wp_http_referer'] ) ) {
			$referer_url = sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) );
			// Convert relative URLs to absolute
			if ( strpos( $referer_url, 'http' ) !== 0 ) {
				$referer_url = home_url( $referer_url );
			}
			$referer_url = esc_url_raw( $referer_url );

		} else {
			$referer_url = wp_get_referer() ? esc_url_raw( wp_get_referer() ) : '';
		}

		// Get page title from the referer URL
		$page_title = '';
		if ( ! empty( $referer_url ) ) {
			$post_id = url_to_postid( $referer_url );
			if ( $post_id ) {
				$page_title = get_the_title( $post_id );
			}
		}

		// Insert the submission post
		$submission_post_id = wp_insert_post( [
			'post_type'   => 'cfefd-submissions',
			'post_status' => 'publish',
			'post_title'  => 'Processing...',
			'post_author' => get_current_user_id(),
		] );

		if ( is_wp_error( $submission_post_id ) ) {
			return;
		}

		// Store form data
		update_post_meta( $submission_post_id, '_cfefd_form_data', $form_data );
		update_post_meta( $submission_post_id, '_cfefd_user_email', $sender_email );
		
		// Store metadata
		$meta_info = [
			'page_url' => [
				'value' => $referer_url,
				'title' => __( 'Page URL', 'contact-form-extender-for-divi-builder' ),
			],
			'page_title' => [
				'value' => $page_title,
				'title' => __( 'Page Title', 'contact-form-extender-for-divi-builder' ),
			],
			'ip_address' => [
				'value' => $this->get_ip_address(),
				'title' => __( 'IP Address', 'contact-form-extender-for-divi-builder' ),
			],
			'user_agent' => [
				'value' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'title' => __( 'User Agent', 'contact-form-extender-for-divi-builder' ),
			],
		];

		update_post_meta( $submission_post_id, '_cfefd_form_meta', $meta_info );
		update_post_meta( $submission_post_id, '_cfefd_form_entry_id', $submission_post_id );
		update_post_meta( $submission_post_id, '_cfefd_form_name', $form_id );

		// Update title with entry ID for better identification
		wp_update_post( [
			'ID'         => $submission_post_id,
			'post_title' => sprintf( 
				/* translators: %s: Submission Id */
				__( 'Entry #%d', 'contact-form-extender-for-divi-builder' ), $submission_post_id ),
		] );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	private function get_ip_address() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return $ip;
	}
}

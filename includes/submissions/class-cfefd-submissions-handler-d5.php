<?php

namespace CFEFD\Submissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CFEFD_Submissions_Handler_D5
 *
 * Handles capturing and storing Divi Contact Form submissions.
 */
class CFEFD_Submissions_Handler_D5 {

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

		if ( $et_contact_error || empty( $processed_fields_values ) ) {
			return;
		}

		// Extract form info
		$form_id    = $contact_form_info['contact_form_id'] ?? 'unknown';
		$unique_id  = $contact_form_info['contact_form_unique_id'] ?? '';
		
		// Attempt to extract the unique ID from the form ID as per the default pattern
		// This was the logic that the user confirmed was working before
		if ( preg_match( '/et_pb_contact_form_(.+)$/', $form_id, $matches ) ) {
			$unique_id = $matches[1];
		}

		$nonce_key = '_wpnonce-et-pb-contact-form-submitted-' . $unique_id;

		if (
			empty( $_POST[ $nonce_key ] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ),
				'et-pb-contact-form-submit-' . $unique_id
			)
		) {
			return;
		}

		$form_data    = [];
		$sender_email = '';

		foreach ( $processed_fields_values as $field_key => $field ) {
			$value = $field['value'] ?? '';

			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			$type  = $field['type'] ?? 'input';
			$id    = $field['id'] ?? $field_key;
			$label = $field['label'] ?? $field_key;

			if ( 'email' === $type && is_email( $value ) ) {
				$sender_email = $value;
			}

			// === SAME DATA STRUCTURE AS DIVI 4 ===
			$form_data[ $field_key ] = [
				'id'    => $id,
				'label' => $label,
				'value' => stripslashes( $value ),
				'type'  => $type,
			];
		}

		// Get page metadata using the post_id provided by Divi 5 core handler
		$referer_url = '';
		$page_title  = '';
		$origin_post_id = $contact_form_info['post_id'] ?? 0;

		if ( $origin_post_id ) {
			$referer_url = get_permalink( $origin_post_id );
			$page_title  = get_the_title( $origin_post_id );
		} else {
			// Fallback to referer if post_id is missing
			$referer_url = esc_url_raw( wp_get_referer() );
			// Page title will remain empty if origin_post_id is missing
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
		
		// Store metadata - MATCHING DIVI 4
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
				/* translators: %s: submission ID */
				__( 'Entry #%d', 'contact-form-extender-for-divi-builder' ), $submission_post_id ),
		] );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	private function get_ip_address() {
		// REMOTE_ADDR is set by the server and cannot be spoofed via request headers.
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return '';
	}
}

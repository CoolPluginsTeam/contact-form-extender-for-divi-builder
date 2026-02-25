<?php

namespace CFEFD\Admin\Entries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulk actions on Submissions page.
 */
class CFEFD_Submissions_Bulk_Actions {

	/**
	 * Allowed actions.
	 */
	const ALLOWED_ACTIONS = [
		'trash',
		'restore',
		'delete',
		'empty_trash',
	];

	/**
	 * IDs.
	 */
	private $ids;

	/**
	 * Current action.
	 */
	private $action;

	private $posts_type = 'cfefd-submissions';

	/**
	 * Initialize class.
	 */
	public function init() {
		$this->hooks();
	}

	/**
	 * Hooks.
	 */
	private function hooks() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for read-only hook initialization.
		if( is_admin() && isset($_GET['page']) && $_GET['page'] === 'contact-form-extender-for-divi-builder' && isset($_GET['tab']) && $_GET['tab'] === 'submissions' ){
			add_action('admin_init', [$this, 'after_admin_init']);
		}
	}

	public function after_admin_init(){
		$this->process();
	}

	/**
	 * Process the bulk actions.
	 */
	private function process() {

		if ( ! current_user_can( 'manage_options' )) {
			return;
		}
		
		$this->ids    = isset( $_GET['entry_id'] ) ? array_map( 'absint', (array) $_GET['entry_id'] ) : [];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified later in line 88 before any processing occurs.
		$action=isset($_REQUEST['action']) ? str_replace(' ', '_', strtolower(sanitize_text_field(wp_unslash($_REQUEST['action'])))) : false;
		$this->action = $action ? sanitize_key( $action ) : false;

		if ( $this->action === '-1' ) {
			$this->action = ! empty( $_REQUEST['action2'] ) ? sanitize_key( $_REQUEST['action2'] ) : false;
		}

		if($this->action === 'empty_trash'){
			$this->ids = [0];
		}

		if ( empty( $this->ids ) || empty( $this->action ) ) {
			return;
		}
		
		// Check exact action values.
		if ( ! in_array( $this->action, self::ALLOWED_ACTIONS, true ) ) {
			return;
		}
		
		if ( empty( $_GET['_wpnonce'] ) ) {
			return;
		}
		
		// Check the nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'bulk-submissions' ) ) {
            return;
		}

		// Finally, we can process the action.
		$this->process_action();
	}

	/**
	 * Process action.
	 */
	private function process_action() {

		$method = "process_action_{$this->action}";

		// Check that we have a method for this action.
		if ( ! method_exists( $this, $method ) ) {
			return;
		}

		if ( empty( $this->ids ) || ! is_array( $this->ids ) ) {
			return;
		}

		$result = [];

		foreach ( $this->ids as $id ) {
			$result[ $id ] = $this->$method( $id );
		}

		$count_result = count( array_keys( array_filter( $result ) ) );

		if ( $method === 'process_action_empty_trash' ) {
			$count_result = $result[0] ?? 0;
		}

		$query_args = [];
		$query_args[ rtrim( $this->action, 'e' ) . 'ed' ] = $count_result;
        $query_args['tab'] = 'submissions';

		wp_safe_redirect(
			add_query_arg(
				$query_args,
				remove_query_arg( [ 'action', 'action2', '_wpnonce', 'entry_id', 'paged', '_wp_http_referer' ] )
			)
		);
		exit;
	}

	/**
	 * Trash the entry.
	 */
	private function process_action_trash( $id ) {
		if ( ! current_user_can( 'delete_post', $id ) ) {
			return false; 
		}

		if ( get_post_status( $id ) ) {
			wp_trash_post( $id );
			return true; 
		}

		return false; 
	}

	/**
	 * Restore the entry.
	 */
	private function process_action_restore( $id ) {

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return false; 
		}

		if ( get_post_status( $id ) === 'trash' ) {
			wp_untrash_post( $id );
			return true; 
		}

		return false; 
	}

	/**
	 * Delete the entry.
	 */
	private function process_action_delete( $id ) {
		if ( ! current_user_can( 'delete_post', $id ) ) {
			return false; 
		}

		if ( get_post_status( $id ) ) {
			wp_delete_post( $id, true );
			return true; 
		}

		return false; 
	}

	/**
	 * Empty trash.
	 */
	private function process_action_empty_trash( $id ) {
		$posts = get_posts([
			'post_type' => $this->posts_type,
			'post_status' => 'trash',
			'posts_per_page' => -1,
		]);

		$count = 0;
		foreach($posts as $post){
			if ( current_user_can( 'delete_post', $post->ID ) ) {
                wp_delete_post( $post->ID, true );
                $count++;
			}
		}

		return $count; 
	}

	/**
	 * Define bulk actions available for submissions table.
	 */
	public function get_dropdown_items() {

		$items = [];
        $view = CFEFD_Submissions_Post_Type::get_view();

		if ( $view === 'trash' ) {
			$items = [
				'restore' => esc_html__( 'Restore', 'contact-form-extender-for-divi-builder' ),
				'delete'  => esc_html__( 'Delete Permanently', 'contact-form-extender-for-divi-builder' ),
			];
		} else {
			$items = [
				'trash' => esc_html__( 'Move to Trash', 'contact-form-extender-for-divi-builder' ),
			];
		}

		return $items;
	}
}

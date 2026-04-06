<?php

namespace CFEFD\Admin\Entries;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-admin/includes/screen.php' ); 
    require_once( ABSPATH . 'wp-admin/includes/template.php' );
}

use WP_List_Table;
use CFEFD\Admin\Entries\CFEFD_Submissions_Post_Type;
use CFEFD\Admin\Entries\CFEFD_Submissions_Bulk_Actions;

if(!class_exists('CFEFD_Submissions_List_Table')) { 
class CFEFD_Submissions_List_Table extends WP_List_Table {

    private static $instance = null;

    private $post_type = 'cfefd-submissions';

    private $cfefd_bulk_actions;

    public static function get_instance($post_type) {
        if (null === self::$instance) {
            self::$instance = new self($post_type);
        }
        return self::$instance;
    }
    
    public function __construct($post_type) {
        $this->post_type = esc_html($post_type);

        parent::__construct([
            'singular' => 'submission',
            'plural' => 'submissions',
            'ajax' => false
        ]);

        $this->cfefd_bulk_actions = new CFEFD_Submissions_Bulk_Actions();
    }

    public function get_views() {
        $views = [
            'all' => 'All',
            'trash' => 'Trash',
        ];
        
        return $views;
    }

    public function views() {
        $views = $this->get_views();

        if ( empty( $views ) ) {
            return;
        }

        $current_view = CFEFD_Submissions_Post_Type::get_view();

        // Get counts for all and trash
        $post_counts = wp_count_posts($this->post_type);
        $all_count = $post_counts->publish + $post_counts->draft + $post_counts->pending;
        $trash_count = $post_counts->trash;
        $index = 0;

        echo "<ul class='subsubsub'>\n";    
        foreach ($views as $view => $label) {
            $class = ($view === $current_view) ? 'current' : '';
            $count = ($view === 'all') ? $all_count : ($view === 'trash' ? $trash_count : 0);
            
            if((($index < (count($views))) && $index !== 0) && count($views) > 0 && $count > 0) {
                echo " | ";
            }

            if ( $count > 0 || $view === 'all') {
                $view_url = add_query_arg(
                    [
                        'page'     => 'contact-form-extender-for-divi-builder',
                        'tab'      => 'submissions',
                        'view'     => $view,
                        '_wpnonce' => wp_create_nonce( 'cfefd_submissions_view' ),
                    ],
                    admin_url( 'admin.php' )
                );
                echo "<li class='" . esc_attr( $class ) . "'><a href='" . esc_url( $view_url ) . "'>" . esc_html( $label ) . "</a></li>";
                echo "<span class='count'>(" . esc_html( $count ) . ")</span>";
            }
            

            if($count > 0 || $view === 'all'){
                ++$index;
            }
        }
        echo "</ul>";
    }

    public function get_bulk_actions() {
        return $this->cfefd_bulk_actions->get_dropdown_items();
    }

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'user_email' => 'Email',
            'form_name' => 'Form Name',
            'page_title' => 'Page Title',
            'id' => 'ID',
            'submission_date' => 'Submission Date',
        ];
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="entry_id[]" value="%s" />', esc_attr($item->ID));
    }

    public function column_user_email($item) {
        $email = get_post_meta($item->ID, '_cfefd_user_email', true);
        $edit_url = admin_url('post.php?post='.intval($item->ID).'&action=edit');

        if(!isset($email) || !$email || empty($email)){
            $email = 'N/A';
        }

        return sprintf('<a href="%s">%s</a>', esc_url($edit_url), esc_html($email));
    }

    public function column_form_name($item) {
        $form_name = get_post_meta($item->ID, '_cfefd_form_name', true);
        return !empty($form_name) ? esc_html($form_name) : 'N/A';
    }

    public function column_id($item) {
        $entry_id = get_post_meta($item->ID, '_cfefd_form_entry_id', true);
        return absint( $entry_id );
    }

    public function column_submission_date($item) {
        return esc_html( $item->post_date );
    }

    public function column_page_title($item) {
        $meta_details = get_post_meta($item->ID, '_cfefd_form_meta', true);
        $value= isset($meta_details['page_title']['value']) ? $meta_details['page_title']['value'] : '';
        $page_url= isset($meta_details['page_url']['value']) ? $meta_details['page_url']['value'] : '';

        if(!empty($value)){
            return sprintf('<a href="%s" target="_blank">%s</a>', esc_url($page_url), esc_html($value));
        }

        return esc_html($value);
    }

    protected function handle_row_actions( $item, $column_name, $primary ) {
        if($column_name !== $primary) {
            return '';
        }

        $view = CFEFD_Submissions_Post_Type::get_view();

        $actions           = array();
        if($view === 'all'){
            $edit_url = admin_url('post.php?post='.intval($item->ID).'&action=edit');
            $actions['View']   = sprintf('<a href="%s" class="row-title">View</a>', $edit_url);
            $actions['Trash'] = sprintf('<a href="?page=contact-form-extender-for-divi-builder&tab=submissions&action=trash&entry_id=%s&_wpnonce=%s" class="row-title submitdelete">Trash</a>', $item->ID, wp_create_nonce('bulk-submissions'));
        }
        if($view === 'trash'){
            $actions['Restore'] = sprintf('<a href="?page=contact-form-extender-for-divi-builder&tab=submissions&action=restore&entry_id=%s&_wpnonce=%s" class="row-title">Restore</a>', $item->ID, wp_create_nonce('bulk-submissions'));
            $actions['Delete'] = sprintf('<a href="?page=contact-form-extender-for-divi-builder&tab=submissions&action=delete&entry_id=%s&_wpnonce=%s" class="row-title submitdelete">Delete</a>', $item->ID, wp_create_nonce('bulk-submissions'));
        }

        return $this->row_actions($actions);
    }
    
    protected function row_actions( $actions, $always_visible = false ) {
       
        $action_count = count( $actions );
    
        if ( ! $action_count ) {
            return '';
        }
    
        $mode = get_user_setting( 'posts_list_mode', 'list' );
    
        if ( 'excerpt' === $mode ) {
            $always_visible = true;
        }
    
        $output = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
    
        $i = 0;
    
        foreach ( $actions as $action => $link ) {
            ++$i;
    
            $separator = ( $i < $action_count ) ? ' | ' : '';
    
            $output .= "<span class='".esc_attr(lcfirst($action))."'>{$link}{$separator}</span>";
        }
    
        $output .= '</div>';
    
        $output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
            /* translators: Hidden accessibility text. */
            __( 'Show more details','contact-form-extender-for-divi-builder' ) .
        '</span></button>';
    
        return $output;
    }


    public function prepare_items() {
        
		$columns = $this->get_columns();
		$this->_column_headers = [ $columns ];

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $page     = $this->get_pagenum();
		$order    = isset( $_GET['order'] ) && sanitize_text_field( wp_unslash( $_GET['order'] ) ) === 'asc' ? 'ASC' : 'DESC';
        $search   = isset( $_GET['cfkef-entries-search'] ) ? sanitize_text_field( wp_unslash( $_GET['cfkef-entries-search'] ) ) : '';
		$allowed_orderby = ['ID','post_title','post_date','post_modified','post_status'];
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'ID';
        $orderby = in_array($orderby, $allowed_orderby, true) ? $orderby : 'ID';
        $per_page = $this->get_items_per_page( $this->get_per_page_option_name() , 20 );
        $date_filter = isset( $_GET['date_filter'] ) && isset( $_GET['m'] ) && ! empty( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '';
        $view = CFEFD_Submissions_Post_Type::get_view();
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( $orderby === 'date' ) {
			$orderby = [
				'modified' => $order,
				'date'     => $order,
			];
		};

        $orderby = esc_sql($orderby);
        $order = esc_sql($order);
        $page = esc_sql($page);
        $view = esc_sql($view);
        $search = esc_sql($search);

        $args = [
            'post_type'      => $this->post_type,
            'orderby'        => $orderby,
            'order'          => $order,
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'no_found_rows'  => false,
            'post_status'    => $view === 'trash' ? array('trash') : array('publish', 'draft', 'pending'),
            's'              => $search,
        ];

        global $wpdb;
        $post_status_placeholders = implode( ', ', array_fill( 0, count( $args['post_status'] ), '%s' ) );

        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ($post_status_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Placeholders are dynamically generated for IN clause.
            array_merge( array( $this->post_type ), $args['post_status'] )
        );

        if(!empty($search)){
            $query .= $wpdb->prepare(" AND post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        
        if(!empty($date_filter)){
           if (!empty($date_filter) && preg_match('/^(\d{4})(\d{2})$/', $date_filter, $matches)) {
               $year = $matches[1];
               $month = $matches[2];
      
               $query .= $wpdb->prepare(" AND MONTH(post_date) = %d AND YEAR(post_date) = %d", $month, $year);
           }
        }

        // Build ORDER BY clause from whitelisted values, then use prepare() only for LIMIT/OFFSET.
        $order_by_clause = " ORDER BY {$args['orderby']} {$args['order']} ";
        $query .= $order_by_clause . $wpdb->prepare(
            "LIMIT %d OFFSET %d",
            $args['posts_per_page'],
            ( $args['paged'] - 1 ) * $args['posts_per_page']
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is incrementally prepared above. orderby/order validated against whitelist and escaped. Caching not used for list table queries.
        $this->items = $wpdb->get_results( $query );

        $total_posts=wp_count_posts($this->post_type);
        $post_count=0;

        foreach($args['post_status'] as $status){
            $post_count+=$total_posts->$status;
        }

        $this->set_pagination_args([
            'total_items' => $post_count,
            'per_page'    => $this->get_items_per_page( $this->get_per_page_option_name() , 20 ),
            'total_pages' => (int) ceil( $post_count / $this->get_items_per_page( $this->get_per_page_option_name() , 20 ) ),
        ]);
    }

    protected function extra_tablenav($which) {
        $view = CFEFD_Submissions_Post_Type::get_view();
        if($which === 'top'){
            $this->months_dropdown( $this->post_type );
            echo "<input type='submit' name='date_filter' id='" . esc_attr( $this->post_type ) . "-date-filter' class='button' value='Filter'>";

            if($view === 'trash'){
                echo '<input type="submit" class="button button-secondary" name="action" value="Empty Trash">';
            }
        }
    }

    public function display_tablenav($which) {
        if ( $this->has_items() ) {
            parent::display_tablenav( $which );
            return;
        }
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );

			if ( $which === 'top' ) {
				$this->pagination( $which );
			}
			?>
			<br class="clear" />
		</div>
    <?php
    }

	protected function pagination( $which ) {

		if ( $this->has_items() ) {
			parent::pagination( $which );
			return;
		}

		printf(
			'<div class="tablenav-pages one-page">
				<span class="displaying-num">%s</span>
			</div>',
			esc_html__( '0 items', 'contact-form-extender-for-divi-builder' )
		);
	}

	private function get_per_page_option_name() {
		return 'edit_'.$this->post_type. '_per_page';
	}
}   
}   

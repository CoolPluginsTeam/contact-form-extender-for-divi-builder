<?php

namespace CFEFD\Admin\Entries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CFEFD\Admin\Entries\CFEFD_Submissions_List_Table;
use CFEFD\Admin\Entries\CFEFD_Submissions_Bulk_Actions;

/**
 * Submissions Post Type
 */     
class CFEFD_Submissions_Post_Type {

    private static $instance = null;

    public static $post_type = 'cfefd-submissions';

    /**
     * Get instance
     * 
     * @return CFEFD_Submissions_Post_Type
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }       

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action('add_meta_boxes', [ $this, 'add_submission_meta_boxes' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ]);
        add_action('admin_head', [$this, 'add_screen_option'] );

        $bulk_actions = new CFEFD_Submissions_Bulk_Actions();
        $bulk_actions->init();
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style('cfefd-submissions', CFEFD_PLUGIN_URL . 'admin/assets/css/cfefd-submissions.css', [], CFEFD_PLUGIN_VERSION);
    }

    /**
     * Register post type
     */
    public function register_post_type() {
        
        $labels = array(
            'name'                  => esc_html_x( 'Form Submissions', 'Post Type General Name', 'contact-form-extender-for-divi-builder' ),
            'singular_name'         => esc_html_x( 'Submission', 'Post Type Singular Name', 'contact-form-extender-for-divi-builder' ),
            'menu_name'             => esc_html__( 'Submission', 'contact-form-extender-for-divi-builder' ),
            'name_admin_bar'        => esc_html__( 'Submission', 'contact-form-extender-for-divi-builder' ),
            'archives'              => esc_html__( 'Submission Archives', 'contact-form-extender-for-divi-builder' ),
            'attributes'            => esc_html__( 'Submission Attributes', 'contact-form-extender-for-divi-builder' ),
            'parent_item_colon'     => esc_html__( 'Parent Item:', 'contact-form-extender-for-divi-builder' ),
            'all_items'             => esc_html__( 'Submissions', 'contact-form-extender-for-divi-builder' ),
            'add_new_item'          => esc_html__( 'Add New Item', 'contact-form-extender-for-divi-builder' ),
            'add_new'               => esc_html__( 'Add New', 'contact-form-extender-for-divi-builder' ),
            'new_item'              => esc_html__( 'New Item', 'contact-form-extender-for-divi-builder' ),
            'edit_item'             => esc_html__( 'View Submission', 'contact-form-extender-for-divi-builder' ),
            'update_item'           => esc_html__( 'Update Item', 'contact-form-extender-for-divi-builder' ),
            'view_item'             => esc_html__( 'View Item', 'contact-form-extender-for-divi-builder' ),
            'view_items'            => esc_html__( 'View Items', 'contact-form-extender-for-divi-builder' ),
            'search_items'          => esc_html__( 'Search Item', 'contact-form-extender-for-divi-builder' ),
            'not_found'             => esc_html__( 'Not found', 'contact-form-extender-for-divi-builder' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'contact-form-extender-for-divi-builder' ),
            'featured_image'        => esc_html__( 'Featured Image', 'contact-form-extender-for-divi-builder' ),
            'set_featured_image'    => esc_html__( 'Set featured image', 'contact-form-extender-for-divi-builder' ),
            'remove_featured_image' => esc_html__( 'Remove featured image', 'contact-form-extender-for-divi-builder' ),
            'use_featured_image'    => esc_html__( 'Use as featured image', 'contact-form-extender-for-divi-builder' ),
            'insert_into_item'      => esc_html__( 'Insert into item', 'contact-form-extender-for-divi-builder' ),
            'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'contact-form-extender-for-divi-builder' ),
            'items_list'            => esc_html__( 'Form submissions list', 'contact-form-extender-for-divi-builder' ),
            'items_list_navigation' => esc_html__( 'Form submissions list navigation', 'contact-form-extender-for-divi-builder' ),
            'filter_items_list'     => esc_html__( 'Filter from submission list', 'contact-form-extender-for-divi-builder' ),
        );

        $args = array(
            'label'                 => esc_html__( 'Form Submissions', 'contact-form-extender-for-divi-builder' ),
            'description'           => esc_html__( 'cfefd-submission', 'contact-form-extender-for-divi-builder' ),
            'labels'                => $labels,
            'supports'              => false,
            'capabilities'          => ['create_posts' => 'do_not_allow'],
            'map_meta_cap'          => true,
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true, 
            'show_in_menu'          => false,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'query_var'             => true,
            'exclude_from_search'   => true,
            'show_in_rest'          => true,
        );

        register_post_type( self::$post_type, $args );
        
    }

    public static function get_view() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for read-only view switching.
        return isset($_GET['view']) && in_array(wp_unslash($_GET['view']), ['all', 'trash'], true) ? sanitize_text_field(wp_unslash($_GET['view'])) : 'all';
    }

    public function output_entries_list() {
        ?>
        <div class="cfefd-form-element-wrapper">
            <div class="wrapper-header">
                <div class="cfefd-save-all">
                    <div class="cfefd-title-desc">
                        <h2><?php esc_html_e( 'Submissions', 'contact-form-extender-for-divi-builder' ); ?></h2>
                    </div>
                </div>
            </div>
            <div class="wrapper-body" style="display: block;">
                <div id="cfefd-submissions-list-wrapper">
                    <?php
                    $list_table = CFEFD_Submissions_List_Table::get_instance(self::$post_type);
                    $list_table->prepare_items();
                    $list_table->views();
                    ?>
                    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
                        <input type="hidden" name="page" value="contact-form-extender-for-divi-builder">
                        <input type="hidden" name="tab" value="submissions">
                        <input type="hidden" name="view" value="<?php echo esc_attr(self::get_view()); ?>">
                        <?php
                        // $list_table->search_box( esc_html__( 'Search Submissions', 'contact-form-extender-for-divi-builder' ), 'cfefd-submissions-search' );
                        $list_table->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function add_screen_option() {
        $screen = get_current_screen();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for read-only tab check to add screen options.
        if ( $screen && $screen->id === 'divi_page_contact-form-extender-for-divi-builder' && isset($_GET['tab']) && $_GET['tab'] === 'submissions' ) {

            $args = array(
                'label'   => 'Items per page',
                'default' => 20,
                'option'  => 'edit_'.self::$post_type.'_per_page',
            );
            
            add_screen_option( 'per_page', $args );
        }
    }
    

    /**
     * Add submission meta boxes
     */
    public function add_submission_meta_boxes() {
        remove_meta_box('submitdiv', self::$post_type, 'side');
        remove_meta_box('slugdiv', self::$post_type, 'normal');
        
        add_meta_box( 'cfefd-submissions-meta-box', 'Submission Details', [ $this, 'render_submission_meta_box' ], self::$post_type, 'normal', 'high' );
        add_meta_box( 'cfefd-form-info-meta-box', 'Form Info', [ $this, 'render_form_info_meta_box' ], self::$post_type, 'side', 'high' );
    }

    /**
     * Render submission meta box
     */
    public function render_submission_meta_box() {
        $form_data = get_post_meta(get_the_ID(), '_cfefd_form_data', true);
        if ( empty( $form_data ) ) {
            echo '<p>' . esc_html__( 'No data found for this submission.', 'contact-form-extender-for-divi-builder' ) . '</p>';
            return;
        }
        $this->render_field_html("cfefd-submissions-form-data", $form_data);
    }

    /**
     * Render form info meta box
     */
    public function render_form_info_meta_box() {
        $meta = get_post_meta(get_the_ID(), '_cfefd_form_meta', true);

        $submission_number = get_post_meta(get_the_ID(), '_cfefd_form_entry_id', true);
        $form_name = get_post_meta(get_the_ID(), '_cfefd_form_name', true);

        $data=[
            'Form Name' => array('value' => $form_name),
            'Entry No.' => array('value' => $submission_number),
            'Page Url' => array('value' => isset($meta['page_url']['value']) ? $meta['page_url']['value'] : ''),
        ];

        $this->render_field_html("cfefd-form-info", $data);
    }

    private function render_field_html($type, $data) {
        echo '<div id="' . esc_attr($type) . '" class="cfefd-submissions-field-wrapper">';
        echo '<table class="cfefd-submissions-data-table">';
        echo '<tbody>';
        foreach ($data as $key => $value) {
            $label = $value['label'] ?? $key;
            $display_value = '';

            if (isset($value['value']) && is_array($value['value'])) {
                $display_value = $value['value']['value'] ?? '';
            }
            elseif (isset($value['value'])) {
                $display_value = $value['value'];
            }
            echo '<tr class="cfefd-submissions-data-table-key">';
            echo '<td>' . esc_html($label) . '</td>';
            echo '</tr>';

            echo '<tr class="cfefd-submissions-data-table-value">';
            echo '<td>' . esc_html($display_value) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

}

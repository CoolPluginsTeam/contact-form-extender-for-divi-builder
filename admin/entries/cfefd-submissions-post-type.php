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
        add_action( 'add_meta_boxes', [ $this, 'add_submission_meta_boxes' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'admin_head', [ $this, 'add_screen_option' ] );
        add_action('admin_print_scripts', [$this, 'hide_unrelated_notices']);

        // Render custom plugin-style header within the admin content area.
        add_action( 'admin_notices', [ $this, 'render_submission_header' ] );

        $bulk_actions = new CFEFD_Submissions_Bulk_Actions();
        $bulk_actions->init();
    }

    public function hide_unrelated_notices()
    { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded
        // Only hide notices on this plugin's submissions-related screens.
        if ( ! $this->is_cfefd_submissions_screen() ) {
            return;
        }

        $cfkef_pages = true;

        if ($cfkef_pages) {
            global $wp_filter;

            // Define rules to remove callbacks.
            $rules = [
                'user_admin_notices' => [], // remove all callbacks.
                'admin_notices'      => [],
                'all_admin_notices'  => [],
                'admin_footer'       => [
                    'render_delayed_admin_notices', // remove this particular callback.
                ],
            ];

            $notice_types = array_keys($rules);

            foreach ($notice_types as $notice_type) {
                if (empty($wp_filter[$notice_type]->callbacks) || ! is_array($wp_filter[$notice_type]->callbacks)) {
                    continue;
                }

                $remove_all_filters = empty($rules[$notice_type]);

                foreach ($wp_filter[$notice_type]->callbacks as $priority => $hooks) {
                    foreach ($hooks as $name => $arr) {
                        if (is_object($arr['function']) && is_callable($arr['function'])) {
                            if ($remove_all_filters) {
                                unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                            }
                            continue;
                        }

                        $class = ! empty($arr['function'][0]) && is_object($arr['function'][0]) ? strtolower(get_class($arr['function'][0])) : '';

                        // Remove all callbacks except WPForms notices and this plugin's submission header.
                        if (
                            $remove_all_filters
                            && strpos($class, 'wpforms') === false
                            && strpos($class, 'cfefd_submissions_post_type') === false
                        ) {
                            unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                            continue;
                        }

                        $cb = is_array($arr['function']) ? $arr['function'][1] : $arr['function'];

                        // Remove a specific callback.
                        if (! $remove_all_filters) {
                            if (in_array($cb, $rules[$notice_type], true)) {
                                unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
                            }
                            continue;
                        }
                    }
                }
            }
        }

    }

    /**
     * Enqueue admin scripts
     *
     * @param string $hook_suffix Current admin page hook.
     */
    public function enqueue_admin_scripts( $hook_suffix ) {
        $screen = get_current_screen();

        if ( ! $screen ) {
            return;
        }

        if (
            $screen->id === 'divi_page_contact-form-extender-for-divi-builder' ||
            $screen->post_type === self::$post_type
        ) {
            wp_enqueue_style(
                'cfefd-submissions',
                CFEFD_PLUGIN_URL . 'admin/assets/css/cfefd-submissions.css',
                [],
                CFEFD_PLUGIN_VERSION
            );

            wp_enqueue_style(
                'cfefd-admin-style',
                CFEFD_PLUGIN_URL . 'assets/css/admin-style.css',
                [],
                CFEFD_PLUGIN_VERSION
            );
        }
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
        if ( ! current_user_can( 'edit_posts' ) ) {
            return 'all';
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below when view param is present.
        if ( ! isset( $_GET['view'] ) ) {
            return 'all';
        }
        $view = sanitize_text_field( wp_unslash( $_GET['view'] ) );
        if ( ! in_array( $view, [ 'all', 'trash' ], true ) ) {
            return 'all';
        }
        // Verify nonce for view parameter to prevent tampering.
        if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'cfefd_submissions_view' ) ) {
            return 'all';
        }
        return $view;
    }

    /**
     * Render plugin header on single submission edit screen.
     */
    public function render_submission_header() {
        $screen = get_current_screen();

        if ( ! $screen || $screen->post_type !== self::$post_type || $screen->base !== 'post' ) {
            return;
        }
        ?>
        <div class="cfefd-wrapper cfefd-wrapper-single-submission">
            <div class="cfefd-header">
                <div class="cfefd-header-logo">
                    <span class="cfefd-header-logo-icon">
                        <img src="<?php echo esc_url( CFEFD_PLUGIN_URL . 'admin/assets/icons/icon.svg' ); ?>" alt="<?php esc_attr_e( 'Contact Form Extender for Divi Logo', 'contact-form-extender-for-divi-builder' ); ?>">
                    </span>
                    <h2><?php esc_html_e( 'Contact Form Extender for Divi', 'contact-form-extender-for-divi-builder' ); ?></h2>
                </div>
                <div class="cfefd-header-buttons">
                </div>
            </div>
        </div>

        <p class="cfefd-back-link">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=contact-form-extender-for-divi-builder&tab=submissions' ) ); ?>">
                &larr; <?php esc_html_e( 'Back', 'contact-form-extender-for-divi-builder' ); ?>
            </a>
         </p>
        <?php
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
                        <?php wp_nonce_field( 'cfefd_submissions_list', '_wpnonce', false ); ?>
                        <input type="hidden" name="page" value="contact-form-extender-for-divi-builder">
                        <input type="hidden" name="tab" value="submissions">
                        <input type="hidden" name="view" value="<?php echo esc_attr( self::get_view() ); ?>">
                        <?php
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
     * Check if current screen is one of this plugin's submissions screens.
     *
     * @return bool
     */
    private function is_cfefd_submissions_screen() {
        $screen = get_current_screen();

        if ( ! $screen ) {
            return false;
        }

        // Single submission edit screen.
        if ( $screen->post_type === self::$post_type ) {
            return true;
        }

        // Submissions list inside the plugin settings page.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of current tab.
        if ( $screen->id === 'divi_page_contact-form-extender-for-divi-builder' && isset( $_GET['tab'] ) && $_GET['tab'] === 'submissions' ) {
            return true;
        }

        return false;
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

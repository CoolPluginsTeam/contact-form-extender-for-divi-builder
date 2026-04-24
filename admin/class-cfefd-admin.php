<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://coolplugins.net
 * @since      1.0.0
 *
 * @package    Cool_FormKit
 * @subpackage Cool_FormKit/admin
 */

if (!defined('ABSPATH')) {
    die;
}

require_once CFEFD_PLUGIN_DIR . 'admin/entries/cfefd-submissions-post-type.php';
require_once CFEFD_PLUGIN_DIR . 'admin/entries/cfefd-submissions-list-table.php';
require_once CFEFD_PLUGIN_DIR . 'admin/entries/cfefd-submissions-bulk-actions.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Cool_FormKit
 * @subpackage Cool_FormKit/admin
 */
if(!class_exists('CFEFD_Admin')) { 
    class CFEFD_Admin {

        /**
         * The instance of this class.
         *
         * @since    1.0.0
         * @access   private
         * @var      CFEFD_Admin    $instance    The instance of this class.
         */
        private static $instance = null;

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        private static $allowed_pages = array(
            'contact-form-extender-for-divi-builder',
        );

        /**
         * Constructor to initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param    string    $plugin_name       The name of this plugin.
         * @param    string    $version    The version of this plugin.
         */
        private function __construct($plugin_name, $version) {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
            add_action('admin_menu', array($this, 'add_plugin_admin_menu'),999);
            add_action('admin_init', array($this, 'register_form_elements_settings'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
            add_action('admin_print_scripts', array($this, 'hide_unrelated_notices'));


            // Initialize Submissions
            CFEFD\Admin\Entries\CFEFD_Submissions_Post_Type::get_instance();

            add_action('cpfm_register_notice', function () { 
                if (!class_exists('CFEFD\Admin\CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
                    return;
                }

                $notice = [
                    'title' => __('Divi Form Addons by Cool Plugins', 'contact-form-extender-for-divi-builder'),
                    'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'contact-form-extender-for-divi-builder'),
                    'pages' => ['contact-form-extender-for-divi-builder'],
                    'always_show_on' => ['contact-form-extender-for-divi-builder'], // This enables auto-show
                    'plugin_name'=>'cfefd'
                ];

                CFEFD\Admin\CPFM_Feedback_Notice::cpfm_register_notice('divi_cool_forms', $notice);

                    if (!isset($GLOBALS['cool_plugins_feedback'])) {
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared across Cool Plugins products.
                        $GLOBALS['cool_plugins_feedback'] = [];
                    }
                    
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared across Cool Plugins products.
                    $GLOBALS['cool_plugins_feedback']['divi_cool_forms'][] = $notice;
            
            });
        
            add_action('cpfm_after_opt_in_cfefd', function($category) {
                
                    if ($category === 'divi_cool_forms') {

                        require_once CFEFD_PLUGIN_DIR . 'admin/feedback/cron/cfefd-class-cron.php';

                        // Set the usage share data option to 'on'
                        update_option( 'cfef_usage_share_data', 'on' );

                        // Send initial data for this plugin
                        CFEFD\Admin\cfefd_cronjob::cfefd_send_data();

                        // Ensure cron is scheduled
                        CFEFD\CFEFD_Main::cfefd_cron_job_init();
                    } 
            });
        } 
        /**
         * Get the instance of this class.
         *
         * @since    1.0.0
         * @param    string    $plugin_name       The name of this plugin.
         * @param    string    $version    The version of this plugin.
         * @return   cfefd_Admin    The instance of this class.
         */
        public static function get_instance($plugin_name, $version) {
            if (null == self::$instance) {
                self::$instance = new self($plugin_name, $version);
            }
            return self::$instance;
        }

        /**
         * Add a menu item under Settings.
         *
         * @since    1.0.0
         */
        public function add_plugin_admin_menu() {
            add_submenu_page(
                'et_divi_options',
                __('Contact Form Extender', 'contact-form-extender-for-divi-builder'),
                __('Contact Form Extender', 'contact-form-extender-for-divi-builder'),
                'manage_options',
                'contact-form-extender-for-divi-builder',
                array($this, 'display_plugin_admin_page')
            );
        }
        /**
         * Display the plugin admin page with tabs.
         *
         * @since    1.0.0
         */
        public function display_plugin_admin_page() {
            $choice_option = get_option("cpfm_opt_in_choice_divi_cool_forms");
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for read-only tab switching.
            $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'form-elements';
            ?>
            <div class="cfefd-wrapper">
                <div class="cfefd-header">
                    <div class="cfefd-header-logo">
                        <span class="cfefd-header-logo-icon">
                            <img src="<?php echo esc_url(CFEFD_PLUGIN_URL . 'admin/assets/icons/icon.svg'); ?>" alt="Contact Form Extender for Divi Logo">
                        </span>
                        <h2><?php esc_html_e('Contact Form Extender for Divi', 'contact-form-extender-for-divi-builder'); ?></h2>

                        <a class="button button-primary upgrade-pro-btn" target="_blank" href="https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_header#pricing">
                        <img class="crown-diamond-pro" src="<?php echo esc_url(CFEFD_PLUGIN_URL . 'assets/images/crown-diamond-pro.png'); ?>" alt="Cool FormKit Logo">
                        <?php esc_html_e('Upgrade To Pro', 'contact-form-extender-for-divi-builder'); ?>
                        </a>

                    </div>
                    <div class="cfefd-header-buttons">
                        <p><?php esc_html_e('Need help? Check the docs.', 'contact-form-extender-for-divi-builder'); ?></p>
                        <a href="https://docs.coolplugins.net/plugin/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=setting_page_header" class="button button-secondary" target="_blank"><?php esc_html_e('Check Docs', 'contact-form-extender-for-divi-builder'); ?></a>
                    </div>
                </div>
                <h2 class="nav-tab-wrapper">
                    <a href="?page=contact-form-extender-for-divi-builder&tab=form-elements" class="nav-tab <?php echo $tab == 'form-elements' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Form Elements', 'contact-form-extender-for-divi-builder'); ?></a>

                    <?php if($choice_option === 'yes'  || $choice_option === 'no') { ?>
                        <a href="?page=contact-form-extender-for-divi-builder&tab=settings" class="nav-tab <?php echo $tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Settings', 'contact-form-extender-for-divi-builder'); ?></a>
                    <?php } ?>

                    <a href="?page=contact-form-extender-for-divi-builder&tab=submissions" class="nav-tab <?php echo $tab == 'submissions' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Form Submissions', 'contact-form-extender-for-divi-builder'); ?></a>
                </h2>
                <div class="tab-content">
                    <?php
                    switch ($tab) {
                        case 'form-elements':
                            include_once 'views/form-elements.php';
                            break;
                        case 'settings':
                            if($choice_option === 'yes'  || $choice_option === 'no'){
                                include_once 'views/settings.php';
                            }else{
                                include_once 'views/form-elements.php';
                            }
                            break;
                        case 'submissions':
                            include_once 'views/submissions.php';
                            break;
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Register the settings for form elements.
         *
         * @since    1.0.0
         */
        public function register_form_elements_settings() {
            register_setting('cfefd_form_elements_group', 'cfefd_enabled_elements', array(
                'type' => 'array',
                'description' => 'Enabled Form Elements',
                'sanitize_callback' => array($this, 'sanitize_form_elements'),
            ));
            register_setting( 'cfefd_form_elements_group', 'cfefd_toggle_all', array(
                'sanitize_callback' => 'sanitize_text_field',
            ) );

            register_setting( 'cfefd_form_elements_group', 'cfefd_enable_elementor_pro_form', array(
                'sanitize_callback' => 'sanitize_text_field',
            ) );
            register_setting( 'cfefd_form_elements_group', 'cfefd_enable_hello_plus', array(
                'sanitize_callback' => 'sanitize_text_field',
            ) );
            register_setting( 'cfefd_form_elements_group', 'cfefd_enable_formkit_builder', array(
                'sanitize_callback' => 'sanitize_text_field',
            ) );

            if (!get_option('cfefd_plugin_initialized')) {
                // Get current enabled elements or empty array
                $enabled_elements = get_option('cfefd_enabled_elements', array());

                // Only update if it's empty (first-time install)            
                if (empty($enabled_elements)) {
                    $default_elements = array(
                        'file_upload',
                        'save_submission'
                    );

                    update_option('cfefd_enabled_elements', $default_elements);
                }
                // Set initialization flag to avoid repeating
                update_option('cfefd_plugin_initialized', true);
            }
        }

        /**
         * Sanitize form elements input.
         *
         * @param array $input The input array.
         * @return array The sanitized array.
         */
        public function sanitize_form_elements($input) {
            $valid = array();

            $form_elements = array('file_upload','save_submission','country_code');

            if (is_array($input)) {
                foreach ($input as $element) {
                    if (in_array($element, $form_elements)) {
                        $valid[] = $element;
                    }
                }
            } 
            return $valid;
        }

        /**
         * Enqueue admin styles and scripts.
         *
         * @since    1.0.0
         */
        public function enqueue_admin_styles() {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required for style enqueuing based on page name.
            $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
            if (strpos($page, 'contact-form-extender-for-divi-builder') !== false || strpos($page, 'cfefd-entries') !== false){
                wp_enqueue_style('cfefd-admin-style', CFEFD_PLUGIN_URL . 'assets/css/admin-style.css', array(), $this->version, 'all');


                wp_enqueue_style('dashicons');
                wp_enqueue_script('cfefd-admin-script', CFEFD_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), $this->version, true);
                
                wp_localize_script( 'cfefd-admin-script', 'cfefd_plugin_vars', [
                    'nonce' => wp_create_nonce( 'cfefd_plugin_nonce' ),
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'installNonce' => wp_create_nonce( 'updates' ),
                ] );
            }
        }

        public static function get_allowed_pages()
        {
            $allowed_pages = self::$allowed_pages;

            $allowed_pages = apply_filters('cfefd_dashboard_allowed_pages', $allowed_pages);

            return $allowed_pages;
        }

        public static function current_screen($slug)
        {
            $slug = sanitize_text_field($slug);
            return self::cfefd_current_page($slug);
        }

        private static function cfefd_current_page($slug)
        {
            $current_page = isset($_REQUEST['page']) ? esc_html($_REQUEST['page']) : (isset($_REQUEST['post_type']) ? esc_html($_REQUEST['post_type']) : '');
            $status=false;

            if (in_array($current_page, self::get_allowed_pages()) && $current_page === $slug) {
                $status=true;
            }

            if(function_exists('get_current_screen') && in_array($slug, self::get_allowed_pages())){
                $screen = get_current_screen();

                if($screen && property_exists($screen, 'id') && $screen->id && $screen->id === $slug){
                    $status=true;
                }
            }

            return $status;
        }

        public function hide_unrelated_notices()
        { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded
            $cfkef_pages = false;
            foreach (self::$allowed_pages as $page) {

                if (self::current_screen($page)) {
                    $cfkef_pages = true;
                    break;
                }
            }

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

                            // Remove all callbacks except WPForms notices.
                            if ($remove_all_filters && strpos($class, 'wpforms') === false) {
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

    }
}

<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * admin-facing side of the site and the public-facing side.
 *
 * @link       https://coolplugins.net
 * @since      1.0.0
 *
 * @package    CFEFD
 * @subpackage CFEFD/includes
 */

if (!defined('ABSPATH')) {
    die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CFEFD
 * @subpackage CFEFD/includes
 */
if(!class_exists('CFEFD_Loader')) { 
class CFEFD_Loader {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The loader instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      CFEFD_Loader    $instance    The loader instance.
     */
    private static $instance = null;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    private function __construct() {
        $this->plugin_name = 'Divi Contact Form Extender';
        $this->version = CFEFD_PLUGIN_VERSION;
        
        $this->admin_menu_dashboard();

        $this->load_dependencies();
        add_action('wp_enqueue_scripts', [$this, 'cfefd_enqueue_global_helper_assets']);
    }

    /**
     * Get the instance of this class.
     *
     * @since    1.0.0
     * @return   CFEFD_Loader    The instance of this class.
     */
    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function cfefd_enqueue_global_helper_assets() {
        if(function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()){
            wp_enqueue_script('cfefd_global_helper_assets', CFEFD_PLUGIN_URL . 'assets/js/global-helper.js', array('jquery'), CFEFD_PLUGIN_VERSION, 'all');
            wp_enqueue_style('cfefd_global_helper_assets', CFEFD_PLUGIN_URL . 'assets/css/global-helper.css', array(), CFEFD_PLUGIN_VERSION, 'all');
        }
    }
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - CFEFD_Admin. Defines all hooks for the admin area.
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once CFEFD_PLUGIN_DIR . 'includes/module/cfefd-addon-loader.php';
        new \CFEFD_Addons_Loader($this->get_plugin_name(), $this->get_version());

        if($this->is_field_enabled('save_submission')){
            if ( wp_get_theme('Divi')->get('Version') >= 5 ) {
                require_once CFEFD_PLUGIN_DIR . 'includes/submissions/class-cfefd-submissions-handler-d5.php';
                new \CFEFD\Submissions\CFEFD_Submissions_Handler_D5();
            }else{
                require_once CFEFD_PLUGIN_DIR . 'includes/submissions/class-cfefd-submissions-handler.php';
                new \CFEFD\Submissions\CFEFD_Submissions_Handler();
            }
        }
    }
    
    private function is_field_enabled($field_key) {
        $enabled_elements = get_option('cfefd_enabled_elements', array());
        return in_array(sanitize_key($field_key), array_map('sanitize_key', $enabled_elements));
    }

    private function admin_menu_dashboard() {

        if (!is_plugin_active( 'divi-contact-form-extender/divi-contact-form-extender.php')) {

            require_once CFEFD_PLUGIN_DIR . 'admin/class-cfefd-admin.php';
            CFEFD_Admin::get_instance($this->get_plugin_name(), $this->get_version());
        }

    }
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since    1.0.0
     * @return   string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since    1.0.0
     * @return   string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
}
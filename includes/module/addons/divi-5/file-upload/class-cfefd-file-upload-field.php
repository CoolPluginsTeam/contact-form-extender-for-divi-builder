<?php

if (!defined('ABSPATH')) {
    die;
}

use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use ET\Builder\Framework\Utility\HTMLUtility;

if(!class_exists('CFEFD_File_Upload_D5')) { 
    class CFEFD_File_Upload_D5 {
        
        public function __construct() {
            // Load shared components (AJAX and Submission handlers)
            $this->load_shared_dependencies();
            
            // Register hooks for Divi 5
            add_action('divi_visual_builder_assets_before_enqueue_scripts', array($this, 'enqueue_file_upload_register'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_filter('block_type_metadata_settings', array($this, 'register_file_upload_field_frontend'));
            add_filter('divi_module_wrapper_render', array($this, 'render_file_upload_output_frontend'), 10, 2);
            add_filter( 'divi_module_wrapper_render', array($this, 'filter_wrapper_render_styles'), 10, 2 );
            
            // D4 to D5 Migration
            // add_filter('divi.moduleLibrary.conversion.moduleConversionOutline', array($this, 'add_conversion_mapping'), 10, 2);
            // add_filter('divi.moduleLibrary.conversion.convertModuleAttribute', array($this, 'convert_complex_attributes'), 10, 4);
        }

        private function normalize_attr( $value ) {
            if ( empty( $value ) ) {
                return null;
            }

            if ( is_array( $value ) && isset( $value['desktop'] ) ) {
                return $value;
            }

            return array(
                'desktop' => array(
                    'value' => $value,
                ),
            );
        }

        public function enqueue_google_font($font_family) {
            $font_parts = explode('|', $font_family);
            $font_family_name = $font_parts[0];
            if ($font_family_name) {
                wp_enqueue_style('tmdivi-gfonts-' . $font_family_name, "https://fonts.googleapis.com/css2?family=$font_family_name&display=swap", array(),CFEFD_PLUGIN_VERSION, null);
            }
        }

        public function filter_wrapper_render_styles( $module_wrapper, $args ){
            $module_id             = $args['id'] ?? '';
            $module_name           = $args['name'] ?? '';
            $module_order_index      = $args['orderIndex'] ?? 0;
            $module_store_instance = $args['storeInstance'] ?? 0;
            $module_attrs          = $args['attrs'] ?? [];
            $module_elements       = $args['elements'] ?? null;

            if ( 'divi/contact-form' !== $module_name || ! $module_elements ) {
                return $module_wrapper;
            }

            $cfefd_defaults = [
                'fileuploadTabs' => 'container',

                /* Container defaults */
                'containerBackground' => '#eee',

                'containerPaddingTop' => '20px',
                'containerPaddingRight' => '20px',
                'containerPaddingBottom' => '0px',
                'containerPaddingLeft' => '20px',

                'containerBorderColor' => '',
                'containerBorderWidth' => '',
                'containerBorderStyle' => 'solid',
                'containerBorderTopLeftRadius' => '0px',
                'containerBorderTopRightRadius' => '0px',
                'containerBorderBottomLeftRadius' => '0px',
                'containerBorderBottomRightRadius' => '0px',
                'containerShadow' => 'none',
                'attachedListColor' => '#1b1818ff',
                'attachedListBackgroundColor' => '#ffffff',

                /* Description defaults */
                'acceptedTextColor' => '#999999ff',
                'acceptedTextSize'  => '20px',
                'chosenFileTextColor' => '#999',

                /* Button defaults */
                'buttonBg' => '',
                'buttonColor' => '#2ea3f2',
                'buttonSize' => '20px',
                'buttonBorderColor' => '#2ea3f2',
                'buttonBorderWidth' => '2px',
                'buttonMarginTop' => '0px',
                'buttonMarginRight' => '0px',
                'buttonMarginBottom' => '0px',
                'buttonMarginLeft' => '0px',
                'buttonPaddingTop' => '6px',
                'buttonPaddingRight' => '20px',
                'buttonPaddingBottom' => '6px',
                'buttonPaddingLeft' => '20px',
                'buttonBorderTopLeftRadius' => '3px',
                'buttonBorderTopRightRadius' => '3px',
                'buttonBorderBottomLeftRadius' => '3px',
                'buttonBorderBottomRightRadius' => '3px',
            ];

            $d = $module_attrs['cfefdFileUploadDesignTabs']['innerContent']['desktop']['value'] ?? [];

            if ( empty( $d ) || ! is_array( $d ) ) {
                $d = $cfefd_defaults;
            } else {
                $d = array_replace_recursive( $cfefd_defaults, $d );
            }

            if(!empty($d['acceptedTextFont'])){
                $this->enqueue_google_font($d['acceptedTextFont']);
            }
            if(!empty($d['buttonTextFont'])){
                $this->enqueue_google_font($d['buttonTextFont']);
            }
            
            $order_class = $module_elements->order_class;

            /* ======================
            SELECTORS
            ====================== */

            $container_selector   = "{$order_class} .cfefd_files_container";
            $accept_desc_selector = "{$order_class} .cfefd_accepted_files_desc";
            $chosen_desc_selector = "{$order_class} .cfefd_file_chosen_desc";
            $button_selector      = "{$order_class} .cfefd_file_upload_button";
            $list_selector        = "{$order_class} .cfefd_files_list span";
            $list_link_selector   = "{$order_class} .cfefd_files_list span a";

            /* ======================
            ADVANCED STYLES (Divi 5 way)
            ====================== */

            $advanced_styles = array(
                /* Container */
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'background-color',
                        'attr'     => $this->normalize_attr($d['containerBackground'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'margin',
                        'attr'     => $this->normalize_attr($d['containerMargin'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => "{$order_class} form.et_pb_contact_form .cfefd_files_container",
                        'property' => 'padding-top',
                        'attr'     => $this->normalize_attr($d['containerPaddingTop'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => "{$order_class} form.et_pb_contact_form .cfefd_files_container",
                        'property' => 'padding-right',
                        'attr'     => $this->normalize_attr($d['containerPaddingRight'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => "{$order_class} form.et_pb_contact_form .cfefd_files_container",
                        'property' => 'padding-bottom',
                        'attr'     => $this->normalize_attr($d['containerPaddingBottom'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => "{$order_class} form.et_pb_contact_form .cfefd_files_container",
                        'property' => 'padding-left',
                        'attr'     => $this->normalize_attr($d['containerPaddingLeft'] ?? '') ?? null,
                    ),
                ),
                // container border radius
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-top-left-radius',
                        'attr'     => $this->normalize_attr($d['containerBorderTopLeftRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-top-right-radius',
                        'attr'     => $this->normalize_attr($d['containerBorderTopRightRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-bottom-left-radius',
                        'attr'     => $this->normalize_attr($d['containerBorderBottomLeftRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-bottom-right-radius',
                        'attr'     => $this->normalize_attr($d['containerBorderBottomRightRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-color',
                        'attr'     => $this->normalize_attr($d['containerBorderColor'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-width',
                        'attr'     => $this->normalize_attr($d['containerBorderWidth'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $container_selector,
                        'property' => 'border-style',
                        'attr'     => $this->normalize_attr($d['containerBorderStyle'] ?? '') ?? null,
                    ),
                ),  

                /* File list */
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $list_link_selector,
                        'property' => 'color',
                        'attr'     => $this->normalize_attr($d['attachedListColor'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $list_selector,
                        'property' => 'background-color',
                        'attr'     => $this->normalize_attr($d['attachedListBackgroundColor'] ?? '') ?? null,
                    ),
                ),

                /* Description */
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $accept_desc_selector,
                        'property' => 'color',
                        'attr'     => $this->normalize_attr($d['acceptedTextColor'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $accept_desc_selector,
                        'property' => 'font-size',
                        'attr'     => $this->normalize_attr($d['acceptedTextSize'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $accept_desc_selector,
                        'property' => 'font-family',
                        'attr'     => $this->normalize_attr($d['acceptedTextFont'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $chosen_desc_selector,
                        'property' => 'color',
                        'attr'     => $this->normalize_attr($d['fileChoosenTextColor'] ?? '') ?? null,
                    ),
                ),

                /* Button */
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'background-color',
                        'attr'     => $this->normalize_attr($d['buttonBg'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'color',
                        'attr'     => $this->normalize_attr($d['buttonColor'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'font-size',
                        'attr'     => $this->normalize_attr($d['buttonTextSize'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'font-family',
                        'attr'     => $this->normalize_attr($d['buttonTextFont'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'margin-top',
                        'attr'     => $this->normalize_attr($d['buttonMarginTop'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'margin-right',
                        'attr'     => $this->normalize_attr($d['buttonMarginRight'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'margin-bottom',
                        'attr'     => $this->normalize_attr($d['buttonMarginBottom'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'margin-left',
                        'attr'     => $this->normalize_attr($d['buttonMarginLeft'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'padding-top',
                        'attr'     => $this->normalize_attr($d['buttonPaddingTop'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'padding-right',
                        'attr'     => $this->normalize_attr($d['buttonPaddingRight'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'padding-bottom',
                        'attr'     => $this->normalize_attr($d['buttonPaddingBottom'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'padding-left',
                        'attr'     => $this->normalize_attr($d['buttonPaddingLeft'] ?? '') ?? null,
                    ),
                ),
                // button border radius
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-top-left-radius',
                        'attr'     => $this->normalize_attr($d['buttonBorderTopLeftRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-top-right-radius',
                        'attr'     => $this->normalize_attr($d['buttonBorderTopRightRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-bottom-left-radius',
                        'attr'     => $this->normalize_attr($d['buttonBorderBottomLeftRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-bottom-right-radius',
                        'attr'     => $this->normalize_attr($d['buttonBorderBottomRightRadius'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-color',
                        'attr'     => $this->normalize_attr($d['buttonBorderColor'] ?? '') ?? null,
                    ),
                ),
                array(
                    'componentName' => 'divi/common',
                    'props' => array(
                        'selector' => $button_selector,
                        'property' => 'border-width',
                        'attr'     => $this->normalize_attr($d['buttonBorderWidth'] ?? '') ?? null,
                    ),
                ),
            );


            \ET\Builder\FrontEnd\Module\Style::add(
                [
                    'id'            => $module_id,
                    'name'          => $module_name,
                    'orderIndex'    => $module_order_index,
                    'storeInstance' => $module_store_instance,
                    'styles'        => [
                        // Icon element styles. The `advancedStyles` will be used to add the icon color
                        // and size.
                        $module_elements->style(
                            array(
                                'attrName'   => 'cfefdFileUploadDesignTabs',
                                'styleProps' => array(
                                    'advancedStyles' => $advanced_styles,
                                ),
                            )
                        ),
                    ],
                ]
            );


            return et_core_esc_previously( $module_wrapper );
        }

        private function load_shared_dependencies() {
            // Load shared AJAX handler
            if (!class_exists('CFEFD_File_Upload_Ajax')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-4/file-upload/class-cfefd-file-upload-ajax.php';
                new CFEFD_File_Upload_Ajax();
            }
            
            // Load shared submission handler
            if (!class_exists('CFEFD_File_Upload_Submission_D5')) {
                require_once CFEFD_PLUGIN_DIR . 'includes/module/addons/divi-5/file-upload/class-cfefd-file-upload-submission.php';
                new CFEFD_File_Upload_Submission_D5();
            }
        }

        public function enqueue_scripts() {
            // Enqueue frontend scripts and styles
            wp_enqueue_script('cfefd-file-upload-field-helper', CFEFD_PLUGIN_URL . 'assets/js/file-upload-field-helper.js', array('jquery'), CFEFD_PLUGIN_VERSION, true);
            
            $wp_max_upload_size = wp_max_upload_size();
            $localized_data = [
                'ajaxURL' => esc_js(admin_url('admin-ajax.php')),
                'ajaxNonce' => wp_create_nonce('cfefd-nonce-ajax'),
                'pluginURL' => CFEFD_PLUGIN_URL,
                'wpMaxUploadSize' => $wp_max_upload_size,
                'wpMaxUploadSizeFormatted' => size_format($wp_max_upload_size),
            ];
            wp_localize_script('cfefd-file-upload-field-helper', 'CFEFD_DiviContactFormExtender', $localized_data);

            wp_enqueue_style('cfefd-file-upload-field-helper', CFEFD_PLUGIN_URL . 'assets/css/file-upload-field-helper-d5.css', array(), CFEFD_PLUGIN_VERSION, 'all');
        }

        public function enqueue_file_upload_register() {    
            if (!et_builder_d5_enabled() || !et_core_is_fb_enabled()) {
                return;
            }

            // Register the package build for the module mapping script
            PackageBuildManager::register_package_build(
                [
                    'name'    => 'cfefd-register-file-upload-field',
                    'version' => null,
                    'script'  => [
                        'src'                => CFEFD_PLUGIN_URL . 'assets/js/register-file-upload-field.js',
                        'deps'               => [
                            'lodash',
                            'divi-vendor-wp-hooks',
                            'react'
                        ],
                        'enqueue_top_window' => false,
                        'enqueue_app_window' => true,
                        'args'               => [
                            'in_footer' => false,
                        ],
                    ],
                ]
            );
        }

        public function register_file_upload_field_frontend($settings) {
            if (empty($settings['name'])) {
                return $settings;
            }

            // Register 'file_upload' option for Contact Field type
            if ('divi/contact-field' === $settings['name']) {
                $options = $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] ?? null;

                if (is_array($options)) {
                    $options['file_upload'] = [
                        'label' => 'File Upload',
                    ];
                    $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] = $options;
                }
            }
            return $settings;
        }

        public function render_file_upload_output_frontend($module_wrapper, $args) {
            $module_name  = $args['name'] ?? '';
            $module_attrs = $args['attrs'] ?? [];

            // Only modify Contact Field module
            if ('divi/contact-field' !== $module_name) {
                return et_core_esc_previously($module_wrapper);
            }

            // Check field type
            $field_type = $module_attrs['fieldItem']['advanced']['type']['desktop']['value'] ?? '';
            if ($field_type !== 'file_upload') {
                return et_core_esc_previously($module_wrapper);
            }

            // Prevent duplicate rendering
            if (strpos($module_wrapper, 'cfefd_contact_hidden_files') !== false) {
                return et_core_esc_previously($module_wrapper);
            }

            // Extract field configuration
            $field_id = $module_attrs['fieldItem']['advanced']['id']['desktop']['value'] ?? 'file_upload';
            $max_size = $module_attrs['fieldItem']['advanced']['fileUploadMaxSize']['desktop']['value'] ?? '1024';
            $allowed_types = $module_attrs['fieldItem']['advanced']['fileUploadAllowedTypes']['desktop']['value'] ?? '.jpg,.png';
            $max_files = $module_attrs['fieldItem']['advanced']['fileUploadMaxFiles']['desktop']['value'] ?? '2';
            $use_button_icon = $module_attrs['fieldItem']['advanced']['fileUploadUseButtonIcon']['desktop']['value'] ?? 'on';
            $button_icon = $module_attrs['fieldItem']['advanced']['fileUploadButtonIcon']['desktop']['value'] ?? '';


            // Generate unique field ID
            $parts = explode('-', $args['parentId']);
            $lastNumber = end($parts);
            $orderIndex = $args['orderIndex'];
            $field_id = strtolower($field_id);
            $input_id = "et_pb_contact_{$lastNumber}_{$field_id}_{$orderIndex}";

            // Process file size
            $file_size = preg_replace('/\D/', '', $max_size);
            $file_size_bytes = $file_size * 1024; // KB to Bytes
            $wp_max_upload_size = wp_max_upload_size();
            if ($file_size_bytes > $wp_max_upload_size) {
                $file_size_bytes = $wp_max_upload_size;
            }
            $file_size_formatted = size_format($file_size_bytes);

            // Process MIME types
            $processed_mimes = $this->process_multiple_mimes_checkboxes_value($allowed_types);

            // Fallback if no valid MIMEs found
            if ( empty( $processed_mimes['keys'] ) ) {
                $allowed_types = '.jpg,.png';
                $processed_mimes = $this->process_multiple_mimes_checkboxes_value($allowed_types);
            }

            $files_extensions = $processed_mimes['values'];
            $files_mimes = $processed_mimes['keys'];

            // Create file description
            $file_desc = sprintf(
                '%1$s %2$s. %3$s %4$s',
                __('Accepted file types:', 'contact-form-extender-for-divi-builder'),
                $files_extensions,
                __('Max. file size:', 'contact-form-extender-for-divi-builder'),
                $file_size_formatted
            );

            // Load DOM
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($module_wrapper);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $wrapper_node = $xpath->query(
                "//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_contact_field ')]"
            );

            if (!$wrapper_node || !$wrapper_node->item(0)) {
                return et_core_esc_previously($module_wrapper);
            }

            $target = $wrapper_node->item(0);

            // Build HTML structure
            // 1. Hidden input for storing file names
            $hidden_input_html = HTMLUtility::render([
                'tag' => 'input',
                'attributes' => [
                    'type' => 'text',
                    'class' => 'input cfefd_contact_hidden_files cool_hidden_original',
                    'name' => $input_id,
                    'id' => $input_id,
                    'readonly' => 'readonly',
                    'data-field-id' => $field_id,
                ],
                'selfClosing' => true,
            ]);

            // 2. File input (hidden)
            $file_input_html = HTMLUtility::render([
                'tag' => 'input',
                'attributes' => [
                    'type' => 'file',
                    'class' => 'input cfefd_file_input',
                    'name' => $input_id,
                    'id' => "et_pb_file_input_{$field_id}",
                    'data-field-id' => $field_id,
                    'data-size' => $file_size_bytes,
                    'data-size-formatted' => $file_size_formatted,
                    'data-limit' => $max_files,
                    'multiple' => $max_files > 1 ? 'multiple' : null,
                ],
                'selfClosing' => true,
            ]);

            // 3. Upload button
            $button_class = 'cfefd_file_upload_button et_pb_button';
            if ($use_button_icon === 'on' && !empty($button_icon)) {
                $button_class .= ' et_pb_icon';
            }

            $upload_button_html = HTMLUtility::render([
                'tag' => 'span',
                'attributes' => [
                    'class' => $button_class,
                    'role' => 'button',
                ],
                'children' => __('Choose Files', 'contact-form-extender-for-divi-builder'),
            ]);

            // Add data-icon if used
            if ($use_button_icon === 'on' && !empty($button_icon)) {
                $icon_processed = html_entity_decode(esc_attr(et_pb_process_font_icon($button_icon['unicode'])));
                
                $upload_button_html = str_replace('<span ', '<span data-icon="' . esc_attr($icon_processed) . '" ', $upload_button_html);
            }

            $button_selector = "{$args['elements']->order_class} .cfefd_file_upload_button";

            if ( isset( $button_icon['type'] ) && $button_icon['type'] === 'fa' ) {
                $font_family = 'FontAwesome';
                $font_weight = ( $button_icon['weight'] ?? '400' ) === '900' ? 900 : 400;
            }else{
                $font_family = 'ETmodules';
                $font_weight = ( $button_icon['weight'] ?? '400' ) === '900' ? 900 : 400;
            }

            \ET\Builder\FrontEnd\Module\Style::add([
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $args['elements']->style([
                        'attrName' => 'fieldItem.advanced.fileUploadButtonIcon',
                        'styleProps' => [
                            'advancedStyles' => [
                                [
                                    'componentName' => 'divi/common',
                                    'props' => [
                                        'selector' => "{$button_selector}.et_pb_icon:after",
                                        'attr'     => $this->normalize_attr($font_family) ?? null,
                                        'important'               => true,
                                        'property' => 'font-family',
                                    ],
                                ],
                                [
                                    'componentName' => 'divi/common',
                                    'props' => [
                                        'selector' => "{$button_selector}.et_pb_icon:after",
                                        'attr'     => $this->normalize_attr($font_weight) ?? null,
                                        'important'               => true,
                                        'property' => 'font-weight',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ],
            ]);

            // 4. Chosen file description
            $chosen_desc_html = HTMLUtility::render([
                'tag' => 'span',
                'attributes' => [
                    'class' => 'cfefd_file_chosen_desc',
                ],
                'children' => __('No file chosen', 'contact-form-extender-for-divi-builder'),
            ]);

            // 5. Hidden flag input
            $flag_input_html = HTMLUtility::render([
                'tag' => 'input',
                'attributes' => [
                    'type' => 'hidden',
                    'name' => $input_id . '_is_file',
                    'value' => 'yes',
                ],
                'selfClosing' => true,
            ]);

            // 6. Token input
            $token_data = [
                'size' => $file_size_bytes,
                'extentions' => $files_extensions,
                'mimetypes' => $files_mimes,
                'limit' => $max_files,
            ];
            
            $token_input_html = HTMLUtility::render([
                'tag' => 'input',
                'attributes' => [
                    'type' => 'hidden',
                    'name' => $input_id . '_file_token',
                    'value' => CFEFD_File_Upload::encrypt_decrypt(wp_json_encode($token_data)),
                ],
                'selfClosing' => true,
            ]);

            // 7. File description
            $description_html = HTMLUtility::render([
                'tag' => 'span',
                'attributes' => [
                    'id' => "cfefd_accepted_files_desc_{$field_id}",
                    'class' => 'cfefd_accepted_files_desc',
                    'data-description' => $file_desc,
                ],
                'children' => $file_desc,
            ]);

            // 8. Files list container
            $files_list_html = HTMLUtility::render([
                'tag' => 'span',
                'attributes' => [
                    'id' => "cfefd_files_list_{$field_id}",
                    'class' => 'cfefd_files_list',
                ],
                'children' => '',
            ]);

            // Combine all HTML
            $combined_html = $hidden_input_html . $file_input_html . $upload_button_html . 
                           $chosen_desc_html . $flag_input_html . $token_input_html . 
                           $description_html . $files_list_html;

            // Insert into DOM
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($combined_html);
            $target->appendChild($fragment);

            // Add class to wrapper
            $current_class = $target->getAttribute('class');
            $target->setAttribute('class', $current_class . ' cfefd_files_container');

            // Return updated wrapper
            $module_wrapper = $dom->saveHTML();
            return et_core_esc_previously($module_wrapper);
        }

        public function process_multiple_mimes_checkboxes_value($data) {
            $extensions = array_filter(array_map(function ($ext) {
                return ltrim(trim($ext), '.'); // remove . and trim
            }, explode(',', $data)));

            if (empty($extensions)) {
                return ['keys' => '', 'values' => ''];
            }

            $allowed_mimes = CFEFD_File_Upload_D5::get_wp_allowed_mime_types();

            $matched_mime_types = [];
            $matched_extensions = [];

            foreach ($allowed_mimes as $ext_group => $mime_type) {
                $group_exts = explode('|', $ext_group);

                foreach ($group_exts as $group_ext) {
                    if (in_array($group_ext, $extensions, true)) {
                        $matched_mime_types[] = $mime_type;
                        $matched_extensions = array_merge($matched_extensions, $group_exts);
                        break; // Exit once matched
                    }
                }
            }
            
            // Deduplicate
            $matched_mime_types = array_unique($matched_mime_types);
            $matched_extensions = array_unique($matched_extensions);

            return [
                'keys'   => implode(',', $matched_mime_types),
                'values' => ' ' . implode(', ', $matched_extensions),
            ];
        }

        public static function get_wp_allowed_mime_types(){
            $allowed_mime_type = [];
            foreach (get_allowed_mime_types() as $key => $value) {
                if ('css' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['htm|html'] = 'text/html';
                } elseif ('rtf' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['js'] = 'application/javascript';
                } else {
                    $allowed_mime_type[$key] = $value;
                }
            }

            return $allowed_mime_type;
        }

        /**
         * D4 to D5 Migration - Add conversion mapping
         */
        public function add_conversion_mapping($outline, $module_name) {
            // Contact Field conversion
            if ($module_name === 'et_pb_contact_field') {
                $outline['module'] = $outline['module'] ?? [];
                $outline['module']['cfefd_fileupload_max_size'] = 'fieldItem.advanced.fileUploadMaxSize.*';
                $outline['module']['cfefd_fileupload_allowed_types'] = 'fieldItem.advanced.fileUploadAllowedTypes.*';
                $outline['module']['cfefd_fileupload_max_files'] = 'fieldItem.advanced.fileUploadMaxFiles.*';
                $outline['module']['cfefd_use_file_button_icon'] = 'fieldItem.advanced.fileUploadUseButtonIcon.*';
                $outline['module']['cfefd_file_button_icon'] = 'fieldItem.advanced.fileUploadButtonIcon.*';
            }

            // Contact Form conversion
            if ($module_name === 'et_pb_contact_form') {
                $outline['module'] = $outline['module'] ?? [];

                // Container settings
                $outline['module']['cfefd_files_container_background'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBackground';
                $outline['module']['cfefd_files_container_border_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderColor';
                $outline['module']['cfefd_files_container_border_width'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderWidth';
                $outline['module']['cfefd_files_container_border_style'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderStyle';
                $outline['module']['cfefd_files_container_list_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerListColor';
                $outline['module']['cfefd_files_container_list_background_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerListBg';

                // Description settings
                $outline['module']['cfefd_accepted_file_text_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextColor';
                $outline['module']['cfefd_accepted_file_text_size'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextSize';
                $outline['module']['cfefd_accepted_file_text_font'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextFont';
                $outline['module']['cfefd_chosen_file_text_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.fileChoosenTextColor';

                // Button settings
                $outline['module']['cfefd_file_button_background'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonBg';
                $outline['module']['cfefd_file_button_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonColor';
                $outline['module']['cfefd_file_button_font'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonTextFont';
                $outline['module']['cfefd_file_button_size'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonTextSize';
                $outline['module']['cfefd_file_button_border_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonBorderColor';
            }

            return $outline;
        }

        /**
         * D4 to D5 Migration - Convert complex attributes
         * Handles conversion of pipe-separated values to individual properties
         */
        public function convert_complex_attributes($d5_value, $d4_value, $d4_attr_name, $module_name) {
            // Only process for contact form module
            if ($module_name !== 'et_pb_contact_form') {
                return $d5_value;
            }

            // Helper function to split pipe-separated values
            $split_piped_value = function($value) {
                if (empty($value) || !is_string($value)) {
                    return null;
                }
                $parts = explode('|', $value);
                return count($parts) === 4 ? $parts : null;
            };

            // Container Padding: "20px|20px|0px|20px" → individual properties
            if ($d4_attr_name === 'cfefd_files_container_padding') {
                $parts = $split_piped_value($d4_value);
                if ($parts) {
                    return [
                        'containerPaddingTop' => $parts[0],
                        'containerPaddingRight' => $parts[1],
                        'containerPaddingBottom' => $parts[2],
                        'containerPaddingLeft' => $parts[3]
                    ];
                }
            }

            // Container Border Radius: "3px|3px|3px|3px" → individual properties
            if ($d4_attr_name === 'cfefd_files_container_border') {
                $parts = $split_piped_value($d4_value);
                if ($parts) {
                    return [
                        'containerBorderTopLeftRadius' => $parts[0],
                        'containerBorderTopRightRadius' => $parts[1],
                        'containerBorderBottomRightRadius' => $parts[2],
                        'containerBorderBottomLeftRadius' => $parts[3]
                    ];
                }
            }

            // Button Margin: "0px|0px|0px|0px" → individual properties
            if ($d4_attr_name === 'cfefd_file_button_margin') {
                $parts = $split_piped_value($d4_value);
                if ($parts) {
                    return [
                        'buttonMarginTop' => $parts[0],
                        'buttonMarginRight' => $parts[1],
                        'buttonMarginBottom' => $parts[2],
                        'buttonMarginLeft' => $parts[3]
                    ];
                }
            }

            // Button Padding: "6px|20px|6px|20px" → individual properties
            if ($d4_attr_name === 'cfefd_file_button_padding') {
                $parts = $split_piped_value($d4_value);
                if ($parts) {
                    return [
                        'buttonPaddingTop' => $parts[0],
                        'buttonPaddingRight' => $parts[1],
                        'buttonPaddingBottom' => $parts[2],
                        'buttonPaddingLeft' => $parts[3]
                    ];
                }
            }

            // Button Border Radius: "3px|3px|3px|3px" → individual properties
            if ($d4_attr_name === 'cfefd_file_button_border') {
                $parts = $split_piped_value($d4_value);
                if ($parts) {
                    return [
                        'buttonBorderTopLeftRadius' => $parts[0],
                        'buttonBorderTopRightRadius' => $parts[1],
                        'buttonBorderBottomRightRadius' => $parts[2],
                        'buttonBorderBottomLeftRadius' => $parts[3]
                    ];
                }
            }

            return $d5_value;
        }
        
    }
}


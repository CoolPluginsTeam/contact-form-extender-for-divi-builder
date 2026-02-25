<?php

if (!defined('ABSPATH')) {
    die;
}

use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use ET\Builder\Framework\Utility\HTMLUtility;

if(!class_exists('CFEFD_Range_Slider')) { 
    class CFEFD_Range_Slider {
        public function __construct() {
            add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array($this,'enqueue_range_slider_register') );
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_filter( 'block_type_metadata_settings', array($this,'register_range_field_frontend') );
            add_filter( 'divi_module_wrapper_render', array($this,'render_range_output_frontend'), 10, 2 );
        }

        public function enqueue_scripts(){
            wp_enqueue_script('cfefd-icon-range-slider', CFEFD_PLUGIN_URL . 'assets/lib/js/icon-range-slider.js', array('jquery'),CFEFD_PLUGIN_VERSION , true); 

            wp_enqueue_script('cfefd-register-range-slider-field', CFEFD_PLUGIN_URL . 'assets/js/register-range-slider-field.js', array('jquery'),CFEFD_PLUGIN_VERSION , true); 

            wp_enqueue_style( 'cfefd-icon-range-slider', CFEFD_PLUGIN_URL . 'assets/lib/css/icon-range-slider-css.css', array(), CFEFD_PLUGIN_VERSION );

            wp_enqueue_style( 'cfefd-range-slider-custom', CFEFD_PLUGIN_URL . 'assets/css/range-slider-custom.css', array(), CFEFD_PLUGIN_VERSION );

        }

        public function register_range_field_frontend($settings){
            if ( empty( $settings['name'] ) || 'divi/contact-field' !== $settings['name'] ) {
                return $settings;
            }

            $options = $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] ?? null;

            if ( is_array( $options ) ) {
                // Add your new option
                $options['range'] = [
                    'label' => 'Range Slider',
                ];

                $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] = $options;
            }

            return $settings;
        }

        
        public function enqueue_range_slider_register() {
            if ( ! function_exists('et_builder_d5_enabled') || ! et_builder_d5_enabled() || ! function_exists('et_core_is_fb_enabled') || ! et_core_is_fb_enabled() ) {
                return;
            }

            // Register the package build for the module mapping script. This script is used to map the module elements. It is
            // enqueued in the app window only and requires `lodash` and `divi-vendor-wp-hooks` dependencies. Also, it is not
            // enqueued in the footer to make sure it is loaded exactly after the `lodash` and `divi-vendor-wp-hooks` scripts.
            PackageBuildManager::register_package_build(
                [
                    'name'    => 'cfefd-register-range-slider-field',
                    'version' => null,
                    'script'  => [
                        'src'                => CFEFD_PLUGIN_URL . 'assets/js/register-range-slider-field.js',
                        'deps'               => [
                            'lodash',
                            'divi-vendor-wp-hooks',
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

        public function render_range_output_frontend( $module_wrapper, $args ) {
            $module_name  = $args['name'] ?? '';
            $module_attrs = $args['attrs'] ?? [];

            // Only modify Contact Field module
            if ( 'divi/contact-field' !== $module_name ) {
                return et_core_esc_previously( $module_wrapper );
            }

            // Check field type
            $field_type = $module_attrs['fieldItem']['advanced']['type']['desktop']['value'] ?? '';
            if ( $field_type !== 'range' ) {
                return et_core_esc_previously( $module_wrapper );
            }

            // Extract Range Values (with sane defaults)
            $range_min        = $module_attrs['fieldItem']['advanced']['rangeMin']['desktop']['value']        ?? 0;
            $range_max        = $module_attrs['fieldItem']['advanced']['rangeMax']['desktop']['value']        ?? 100;
            $range_step       = $module_attrs['fieldItem']['advanced']['rangeStep']['desktop']['value']       ?? 1;
            $range_start_from = $module_attrs['fieldItem']['advanced']['rangeStartFrom']['desktop']['value']  ?? 25;
            $range_style      = $module_attrs['fieldItem']['advanced']['rangeStyle']['desktop']['value']      ?? 'round';
            $range_type       = $module_attrs['fieldItem']['advanced']['rangeType']['desktop']['value']       ?? 'single';
            $range_before     = $module_attrs['fieldItem']['advanced']['rangeBeforeText']['desktop']['value'] ?? '';
            $range_after      = $module_attrs['fieldItem']['advanced']['rangeAfterText']['desktop']['value']  ?? '';

            // Generate unique field ID
            $field_id = $module_attrs['fieldItem']['advanced']['id']['desktop']['value'] ?? 'range';

            $parts        = explode('-', $args['parentId']);
            $lastNumber   = end($parts);
            $orderIndex   = $args['orderIndex'];
            $input_id     = "et_pb_contact_{$lastNumber}_{$field_id}_{$orderIndex}";

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

            // Build HTML input with data attributes
            $range_html = HTMLUtility::render([
                'tag'        => 'input',
                'attributes' => [
                    'type'            => 'text',
                    'class'           => 'input form-range-slider',
                    'name'            => $input_id,
                    'id'              => $input_id,

                    // Required flag for JS
                    'data-field_type' => 'range',

                    // IonRangeSlider attributes
                    'data-min'        => $range_min,
                    'data-max'        => $range_max,
                    'data-step'       => $range_step,
                    'data-from'       => $range_start_from,
                    'data-skin'       => $range_style,
                    'data-type'       => $range_type,       // single/double
                    'data-prefix'     => $range_before,
                    'data-postfix'    => $range_after,
                ],
                'selfClosing' => true,
                'children'    => '',
            ]);

            // Insert into DOM
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($range_html);
            $target->appendChild($fragment);

            // Return updated wrapper
            $module_wrapper = $dom->saveHTML();
            return et_core_esc_previously($module_wrapper);
        }

    }
}

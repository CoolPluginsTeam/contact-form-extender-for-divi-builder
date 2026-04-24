<?php

if (!defined('ABSPATH')) {
    die;
}

use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use ET\Builder\Framework\Utility\HTMLUtility;

if(!class_exists('CFEFD_Country_Code_D5')) { 
    class CFEFD_Country_Code_D5 {
        
        public function __construct() {
            add_action('divi_visual_builder_assets_before_enqueue_scripts', array($this, 'enqueue_country_code_register'));
            add_action('wp_enqueue_scripts', array($this, 'register_assets'));
            add_filter('block_type_metadata_settings', array($this, 'register_country_code_field_frontend'));
            add_filter('divi_module_wrapper_render', array($this, 'render_country_code_output_frontend'), 10, 2);
        }

        public function register_assets() {
            wp_register_script( 'cfefd-country-code-library-script', CFEFD_PLUGIN_URL . 'assets/lib/js/intlTelInput.js', array(), CFEFD_PLUGIN_VERSION, true );
            wp_register_script( 'cfefd-country-code-field-helper', CFEFD_PLUGIN_URL . 'assets/js/country-code-field-helper.js', array('jquery', 'cfefd-country-code-library-script'), CFEFD_PLUGIN_VERSION, true );
		    wp_register_style( 'cfefd-country-code-library-style', CFEFD_PLUGIN_URL . 'assets/lib/css/intlTelInput.min.css', array(), CFEFD_PLUGIN_VERSION, 'all' );
            wp_register_style( 'cfefd-country-code-field-helper-style', CFEFD_PLUGIN_URL . 'assets/css/country-code-field-helper.css', array(), CFEFD_PLUGIN_VERSION, 'all' );

            wp_localize_script('cfefd-country-code-field-helper', 'CFEDF_Data', array(
                'pluginUrl' => CFEFD_PLUGIN_URL,
                'errorMap'  => [
                    __("The phone number you entered is not valid. Please check the format and try again.", "contact-form-extender-for-divi-builder"),
                    __("The country code you entered is not recognized. Please ensure it is correct and try again.", "contact-form-extender-for-divi-builder"),
                    __("The phone number you entered is too short. Please enter a complete phone number, including the country code.", "contact-form-extender-for-divi-builder"),
                    __("The phone number you entered is too long. Please ensure it is in the correct format and try again.", "contact-form-extender-for-divi-builder"),
                    __("The phone number you entered is not valid. Please check the format and try again.", "contact-form-extender-for-divi-builder")
                ]
            ));

            if(function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()){
                wp_enqueue_script('cfefd-country-code-library-script');
                wp_enqueue_script('cfefd-country-code-field-helper');
                wp_enqueue_style('cfefd-country-code-library-style');
                wp_enqueue_style('cfefd-country-code-field-helper-style');
            }
        }

        public function enqueue_country_code_register() {    
            if (!function_exists('et_builder_d5_enabled') || !et_builder_d5_enabled() || !function_exists('et_core_is_fb_enabled') || !et_core_is_fb_enabled()) {
                return;
            }

            PackageBuildManager::register_package_build(
                [
                    'name'    => 'cfefd-register-country-code-field',
                    'version' => null,
                    'script'  => [
                        'src'                => CFEFD_PLUGIN_URL . 'assets/js/register-country-code-field.js',
                        'deps'               => [
                            'lodash',
                            'divi-vendor-wp-hooks',
                            'jquery',
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

        public function register_country_code_field_frontend($settings) {
            if (empty($settings['name']) || 'divi/contact-field' !== $settings['name']) {
                return $settings;
            }

            $options = $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] ?? null;

            if (is_array($options)) {
                $options['country_code'] = [
                    'label' => 'Country Code',
                ];
                $settings['attributes']['fieldItem']['settings']['advanced']['type']['item']['component']['props']['options'] = $options;
            }

            $settings['attributes']['fieldItem']['settings']['advanced']['cfefdCountryCodeDefault'] = [
                'type' => 'string',
                'default' => 'in',
            ];

            $settings['attributes']['fieldItem']['settings']['advanced']['cfefdCountryCodeInclude'] = [
                'type' => 'string',
                'default' => '',
            ];

            $settings['attributes']['fieldItem']['settings']['advanced']['cfefdCountryCodeExclude'] = [
                'type' => 'string',
                'default' => '',
            ];

            $settings['attributes']['fieldItem']['settings']['advanced']['cfefdDialCodeVisibility'] = [
                'type' => 'string',
                'default' => 'show',
            ];

            $settings['attributes']['fieldItem']['settings']['advanced']['cfefdStrictMode'] = [
                'type' => 'string',
                'default' => 'off',
            ];

            return $settings;
        }

        public function render_country_code_output_frontend($module_wrapper, $args) {
            $module_name  = $args['name'] ?? '';
            $module_attrs = $args['attrs'] ?? [];

            if ('divi/contact-field' !== $module_name) {
                return $module_wrapper;
            }

            $field_type = $module_attrs['fieldItem']['advanced']['type']['desktop']['value'] ?? '';
            if ($field_type !== 'country_code') {
                return $module_wrapper;
            }

            if (strpos($module_wrapper, 'data-cfefd-country-code') !== false) {
                return $module_wrapper;
            }

            $default_country      = $module_attrs['fieldItem']['advanced']['cfefdCountryCodeDefault']['desktop']['value'] ?? 'in';
            $include_countries    = $module_attrs['fieldItem']['advanced']['cfefdCountryCodeInclude']['desktop']['value'] ?? '';
            $exclude_countries    = $module_attrs['fieldItem']['advanced']['cfefdCountryCodeExclude']['desktop']['value'] ?? '';
            $dial_code_visibility = $module_attrs['fieldItem']['advanced']['cfefdDialCodeVisibility']['desktop']['value'] ?? 'show';
            $strict_mode          = $module_attrs['fieldItem']['advanced']['cfefdStrictMode']['desktop']['value'] ?? 'off';

            // Extract basic field info
            $field_id      = $module_attrs['fieldItem']['advanced']['id']['desktop']['value'] ?? 'country_code';
            $field_title   = $module_attrs['fieldItem']['advanced']['title']['desktop']['value'] ?? '';
            $required_mark = $module_attrs['fieldItem']['advanced']['required']['desktop']['value'] ?? 'off';

            // Generate unique field ID
            $parts = explode('-', $args['parentId']);
            $lastNumber = end($parts);
            $orderIndex = $args['orderIndex'];
            $field_id = strtolower($field_id);
            $input_id = "et_pb_contact_{$lastNumber}_{$field_id}_{$orderIndex}";

            wp_enqueue_script('cfefd-country-code-library-script');
            wp_enqueue_script('cfefd-country-code-field-helper');
            wp_enqueue_style('cfefd-country-code-library-style');
            wp_enqueue_style('cfefd-country-code-field-helper-style');

            $dom = CFEFD_Utils::create_dom($module_wrapper);
            $xpath = new DOMXPath($dom);
            $inputs = $xpath->query('//input[@type="text"]');

            if ($inputs->length > 0) {
                $input = $inputs->item(0);
                $input->setAttribute('data-cfefd-country-code', 'on');
                $input->setAttribute('data-default-country', esc_attr($default_country));
                $input->setAttribute('data-include-countries', esc_attr($include_countries));
                $input->setAttribute('data-exclude-countries', esc_attr($exclude_countries));
                $input->setAttribute('data-dial-code-visibility', esc_attr($dial_code_visibility));
                $input->setAttribute('data-strict-mode', esc_attr($strict_mode));
                $input->setAttribute('data-required_mark', $required_mark === 'on' ? 'required' : 'not_required');
                
                $module_wrapper = $dom->saveHTML();
            } else {
                // If no input found (common for custom fields in D5), render it manually
                $input_html = HTMLUtility::render([
                    'tag' => 'input',
                    'attributes' => [
                        'type' => 'text',
                        'class' => 'input',
                        'name' => $input_id,
                        'id' => $input_id,
                        'placeholder' => $field_title,
                        'data-required_mark' => $required_mark === 'on' ? 'required' : 'not_required',
                        'data-field_id' => $field_id,
                        'data-cfefd-country-code' => 'on',
                        'data-default-country' => esc_attr($default_country),
                        'data-include-countries' => esc_attr($include_countries),
                        'data-exclude-countries' => esc_attr($exclude_countries),
                        'data-dial-code-visibility' => esc_attr($dial_code_visibility),
                        'data-strict-mode' => esc_attr($strict_mode),
                    ],
                    'selfClosing' => true,
                ]);

                $wrapper_node = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_contact_field ')]");
                if ($wrapper_node && $wrapper_node->item(0)) {
                    $target = $wrapper_node->item(0);
                    $fragment = $dom->createDocumentFragment();
                    $fragment->appendXML($input_html);
                    $target->appendChild($fragment);
                    $module_wrapper = $dom->saveHTML();
                }
            }

            return $module_wrapper;
        }

    }
}

<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('CFEFD_Country_Code_Field')) {
    class CFEFD_Country_Code_Field {
        public function __construct() {
            add_filter('et_pb_all_fields_unprocessed_et_pb_contact_field', [$this, 'add_country_code_setting'], 20);
            add_filter('et_module_shortcode_output', [$this, 'render_country_code_output'], 10,3);
            add_action('wp_enqueue_scripts', [$this, 'register_assets']);
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
        }

        public function add_country_code_setting($fields) {
            $fields['cfefd_use_as_country_code'] = [
                'label'           => __('Use As Country Code Field', 'contact-form-extender-for-divi-builder'),
                'type'            => 'yes_no_button',
                'option_category' => 'basic_option',
                'options'         => [
                    'off' => __('No', 'contact-form-extender-for-divi-builder'),
                    'on'  => __('Yes', 'contact-form-extender-for-divi-builder'),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'field_options',
                'description'     => __('Turn this on to use this field as a country code dropdown.', 'contact-form-extender-for-divi-builder'),
                'show_if'         => [
                    'field_type' => 'input',
                ],
            ];

            $fields['cfefd_country_code_default'] = [
                'label'           => __('Default Country', 'contact-form-extender-for-divi-builder'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => 'in',
                'toggle_slug'     => 'field_options',
                'description'     => sprintf(
                    "%s <b>'%s'</b> %s.",
                    esc_html__( 'Set default country code in tel field, like', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'in', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'for India', 'contact-form-extender-for-divi-builder' )
                ),
                'show_if'         => [
                    'cfefd_use_as_country_code' => 'on',
                ],
            ];

            $fields['cfefd_country_code_include'] = [
                'label'           => __('Only country', 'contact-form-extender-for-divi-builder'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'toggle_slug'     => 'field_options',
                'description'     => sprintf(
                    '%s - <b>%s</b>,<b>%s</b>,<b>%s</b>,<b>%s</b>',
                    esc_html__( 'Display only these countries, add comma separated', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'ca', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'in', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'us', 'contact-form-extender-for-divi-builder' ), 
                    esc_html__( 'gb', 'contact-form-extender-for-divi-builder' )
                ),
                'show_if'         => [
                    'cfefd_use_as_country_code' => 'on',
                ],
            ];

            $fields['cfefd_country_code_exclude'] = [
                'label'           => __('Exclude Countries', 'contact-form-extender-for-divi-builder'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'toggle_slug'     => 'field_options',
                'description'     => sprintf(
                    '%s - <b>%s</b>,<b>%s</b><br><br>%s - <a target="__blank" href="' . esc_url( 'https://www.iban.com/country-codes' ) . '">https://www.iban.com/country-codes</a>',
                    esc_html__( 'Exclude some countries, add comma separated', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'af', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'pk', 'contact-form-extender-for-divi-builder' ),
                    esc_html__( 'Check country codes alpha-2 list here', 'contact-form-extender-for-divi-builder' )
                ),
                'show_if'         => [
                    'cfefd_use_as_country_code' => 'on',
                ],
            ];

            $fields['cfefd_dial_code_visibility'] = [
                'label'           => __('Dial Code Visibility', 'contact-form-extender-for-divi-builder'),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => [
                    'show'     => __('Show', 'contact-form-extender-for-divi-builder'),
                    'hide'     => __('Hide', 'contact-form-extender-for-divi-builder'),
                    'separate' => __('Separate', 'contact-form-extender-for-divi-builder'),
                ],
                'default'         => 'show',
                'toggle_slug'     => 'field_options',
                'show_if'         => [
                    'cfefd_use_as_country_code' => 'on',
                ],
            ];

            $fields['cfefd_strict_mode'] = [
                'label'           => __('Strict Mode', 'contact-form-extender-for-divi-builder'),
                'type'            => 'yes_no_button',
                'option_category' => 'basic_option',
                'options'         => [
                    'off' => __('No', 'contact-form-extender-for-divi-builder'),
                    'on'  => __('Yes', 'contact-form-extender-for-divi-builder'),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'field_options',
                'show_if'         => [
                    'cfefd_use_as_country_code' => 'on',
                ],
            ];

            return $fields;
        }

        public function render_country_code_output($output, $render_slug, $module) {
            if (!function_exists('et_core_is_fb_enabled') || et_core_is_fb_enabled()) {
                return $output;
            }

            if ('et_pb_contact_field' !== $render_slug) {
                return $output;
            }
            $props = $module->props;


            $use_country_code = $props['cfefd_use_as_country_code'] ?? 'off';
            if ('on' !== $use_country_code) {
                return $output;
            }

            $default_country      = $props['cfefd_country_code_default'] ?? 'in';
            $include_countries    = $props['cfefd_country_code_include'] ?? '';
            $exclude_countries    = $props['cfefd_country_code_exclude'] ?? '';
            $dial_code_visibility = $props['cfefd_dial_code_visibility'] ?? 'show';
            $strict_mode          = $props['cfefd_strict_mode'] ?? 'off';

            // Identify the input element and add data attributes to it or its wrapper
            // For Divi 4, adding to the module wrapper is common, or we can use DOMDocument to find the input.
            wp_enqueue_script('cfefd-country-code-library-script');
            wp_enqueue_script('cfefd-country-code-field-helper');
            wp_enqueue_style('cfefd-country-code-library-style');
            wp_enqueue_style('cfefd-country-code-field-helper-style');
            
            $dom = $this->create_dom($output);

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
                
                $output = $dom->saveHTML();
            }

            return $output;
        }

        protected function create_dom( $html ) {
            $charset = 'utf-8';
            $dom = new DOMDocument('1.0', $charset);
            libxml_use_internal_errors(true);
            if (function_exists('mb_encode_numericentity')) {
                $html = mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], $charset);
            } elseif (function_exists('mb_convert_encoding')) {
                $html = mb_convert_encoding($html, 'HTML-ENTITIES', $charset);
            } else {
                $html = htmlentities($html, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }
            $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            return $dom;
        }
    }
}

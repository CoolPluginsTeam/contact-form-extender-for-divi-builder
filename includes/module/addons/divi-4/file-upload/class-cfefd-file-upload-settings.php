<?php

if (!defined('ABSPATH')) {
    die;
}

class CFEFD_File_Upload_Settings {

    public function __construct() {
        add_filter('et_pb_all_fields_unprocessed_et_pb_contact_field', [$this, 'cfefd_add_fields'], 20);
        
        // Add design settings to the main Contact Form module
        add_filter('et_builder_get_parent_modules', [$this, 'cfefd_add_toggles'], 10, 2);
        add_filter('et_pb_all_fields_unprocessed_et_pb_contact_form', [$this, 'cfefd_add_design_fields']);
    }

    public function cfefd_add_toggles($modules, $post_type) {
        if (isset($modules['et_pb_contact_form'])) {
            $modules['et_pb_contact_form']->settings_modal_toggles['advanced']['toggles']['cfefd_file_upload_design_toggle'] = [
                'title' => __('File Upload Design', 'contact-form-extender-for-divi-builder'),
                'tabbed_subtoggles' => true,
                'sub_toggles' => [
                    'cfefd_container_toggle' => [ 'name' => __('Container', 'contact-form-extender-for-divi-builder') ],
                    'cfefd_description_toggle' => [ 'name' => __('Descriptions', 'contact-form-extender-for-divi-builder') ],
                    'cfefd_button_toggle' => [ 'name' => __('Button', 'contact-form-extender-for-divi-builder') ],
                ],
                'priority' => 70,
            ];
        }
        return $modules;
    }

    public function cfefd_add_design_fields($fields_unprocessed) {
        // Container
        $fields_unprocessed['cfefd_files_container_background'] = [
            'label' => __('Container Background Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'description' => __('Adjust the background style by customizing the background color.', 'contact-form-extender-for-divi-builder'),
            'custom_color' => true,
            'default' => '#eee',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_padding'] = [
            'label' => __('Container Padding', 'contact-form-extender-for-divi-builder'),
            'type' => 'custom_padding',
            'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'contact-form-extender-for-divi-builder'),
            'default' => '20px|20px|0px|20px',
            'advanced_fields' => true,
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_border'] = [
            'label' => __('Container Border Radius', 'contact-form-extender-for-divi-builder'),
            'type' => 'border-radius',
            'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.', 'contact-form-extender-for-divi-builder'),
            'default' => '',
            'option_category' => 'border',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_border_color'] = [
            'label' => __('Container Border Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color',
            'description' => __('Pick a color to be used for the border.', 'contact-form-extender-for-divi-builder'),
            'custom_color' => true,
            'default' => '',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_border_width'] = [
            'label' => __('Container Border Width', 'contact-form-extender-for-divi-builder'),
            'type' => 'range',
            'description' => __('Increasing the width of the border will increase its size/thickness.', 'contact-form-extender-for-divi-builder'),
            'fixed_unit' => 'px',
            'validate_unit' => true,
            'fixed_range' => true,
            'range_settings' => [
                'min' => '1',
                'max' => '50',
                'step' => '1',
                'min_limit' => -80,
                'max_limit' => 80,
            ],
            'default' => '',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_border_style'] = [
            'label' => __('Container Border Style', 'contact-form-extender-for-divi-builder'),
            'type' => 'select',
            'description' => __('Borders support various different styles, each of which will change the shape of the border element.', 'contact-form-extender-for-divi-builder'),
            'options' => et_builder_get_border_styles(),
            'default' => 'solid',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        // $fields_unprocessed['cfefd_files_container_shadow'] = [
        //     'label' => __('Container Shadow', 'contact-form-extender-for-divi-builder'),
        //     'type' => 'select_box_shadow',
        //     'presets'             => $this->get_shadow_presets('cfefd_files_container'),
        //     'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.', 'contact-form-extender-for-divi-builder'),
        //     'default' => 'none',
        //     'toggle_slug' => 'cfefd_file_upload_design_toggle',
        //     'sub_toggle' => 'cfefd_container_toggle',
        //     'tab_slug' => 'advanced',
        // ];
        
        $shadow_fields = [
            'horizontal' => ['label' => 'Shadow Horizontal Position', 'default' => '0px'],
            'vertical' => ['label' => 'Shadow Vertical Position', 'default' => '2px'],
            'blur' => ['label' => 'Shadow Blur Strength', 'default' => '18px'],
            'spread' => ['label' => 'Shadow Spread Strength', 'default' => '0px'],
            'color' => ['label' => 'Shadow Color', 'type' => 'color-alpha', 'default' => 'rgba(0,0,0,0.3)'],
            'position' => ['label' => 'Shadow Position', 'type' => 'select', 'options' => ['' => 'Outer Shadow', 'inset' => 'Inner Shadow'], 'default' => '']
        ];
        
        // foreach ($shadow_fields as $key => $props) {
        //     $field_key = "cfefd_files_container_shadow_{$key}";
        //     $fields_unprocessed[$field_key] = [
        //         'label' => __($props['label'], 'contact-form-extender-for-divi-builder'),
        //         'type' => isset($props['type']) ? $props['type'] : 'range',
        //         'default' => $props['default'],
        //         'toggle_slug' => 'cfefd_file_upload_design_toggle',
        //         'sub_toggle' => 'cfefd_container_toggle',
        //         'tab_slug' => 'advanced',
        //         'show_if_not' => ['cfefd_files_container_shadow' => 'none'],
        //     ];
        //      if (!isset($props['type']) || $props['type'] === 'range') {
        //          $fields_unprocessed[$field_key]['range_settings'] = ['min' => -80, 'max' => 80, 'step' => 1];
        //          $fields_unprocessed[$field_key]['fixed_unit'] = 'px';
        //      }
        //      if(isset($props['options'])) $fields_unprocessed[$field_key]['options'] = $props['options'];
        // }

        $fields_unprocessed['cfefd_files_container_list_color'] = [
            'label' => __('Attached List Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'description' => __('Adjust the color style by customizing the color color.', 'contact-form-extender-for-divi-builder'),
            'custom_color' => true,
            'default' => '#1b1818ff',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_files_container_list_background_color'] = [
            'label' => __('Attached List Background Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'description' => __('Adjust the color style by customizing the color color.', 'contact-form-extender-for-divi-builder'),
            'custom_color' => true,
            'default' => '#ffffff',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_container_toggle',
            'tab_slug' => 'advanced',
        ];

        // Description
         $fields_unprocessed['cfefd_accepted_file_text_color'] = [
            'label' => __('Accepted File Types Text Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'custom_color' => true,
            'default' => '#999',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_description_toggle',
            'tab_slug' => 'advanced',
        ];
        
        $fields_unprocessed['cfefd_accepted_file_text_size'] = [
            'label' => __('Accepted File Types Text Size', 'contact-form-extender-for-divi-builder'),
            'type' => 'range',
            'default' => '20px',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_description_toggle',
            'tab_slug' => 'advanced',
        ];
        
        $fields_unprocessed['cfefd_accepted_file_text_font'] = [
             'label' => __('Accepted File Types Text', 'contact-form-extender-for-divi-builder'),
             'type' => 'font',
            //  'mobile_options' => true,
             'toggle_slug' => 'cfefd_file_upload_design_toggle',
             'sub_toggle' => 'cfefd_description_toggle',
             'tab_slug' => 'advanced',
        ];

        // Chosen File Text
        $fields_unprocessed['cfefd_chosen_file_text_color'] = [
            'label' => __('File Chosen Text Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'custom_color' => true,
            'default' => '#999',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_description_toggle',
            'tab_slug' => 'advanced',
        ];

        // Upload Button
        $fields_unprocessed['cfefd_file_button_background'] = [
            'label' => __('Button Background Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'custom_color' => true,
            'default' => '',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_file_button_color'] = [
            'label' => __('Button Text Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color-alpha',
            'custom_color' => true,
            'default' => '#2ea3f2',
            // 'mobile_options' => true,
            'hover' => 'tabs',
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];
        
        $fields_unprocessed['cfefd_file_button_font'] = [
             'label' => __('Button Text', 'contact-form-extender-for-divi-builder'),
             'type' => 'font',
            //  'mobile_options' => true,
             'toggle_slug' => 'cfefd_file_upload_design_toggle',
             'sub_toggle' => 'cfefd_button_toggle',
             'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_file_button_size'] = [
            'label' => __('Button Text Size', 'contact-form-extender-for-divi-builder'),
            'type' => 'range',
            'default' => '20px',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];
        
        $fields_unprocessed['cfefd_file_button_margin'] = [
            'label' => __('Button Margin', 'contact-form-extender-for-divi-builder'),
            'type' => 'custom_margin',
            'responsive' => true,
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];

        $fields_unprocessed['cfefd_file_button_padding'] = [
            'label' => __('Button Padding', 'contact-form-extender-for-divi-builder'),
            'type' => 'custom_padding',
            'advanced_fields' => true,
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];
        
        $fields_unprocessed['cfefd_file_button_border'] = [
            'label' => __('Button Border Radius', 'contact-form-extender-for-divi-builder'),
            'type' => 'border-radius',
            'default' => '3px|3px|3px|3px',
            'option_category' => 'border',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];
        
         $fields_unprocessed['cfefd_file_button_border_color'] = [
            'label' => __('Button Border Color', 'contact-form-extender-for-divi-builder'),
            'type' => 'color',
            'custom_color' => true,
            'default' => '#2ea3f2',
            // 'mobile_options' => true,
            'toggle_slug' => 'cfefd_file_upload_design_toggle',
            'sub_toggle' => 'cfefd_button_toggle',
            'tab_slug' => 'advanced',
        ];
        

        return $fields_unprocessed;
    }

    public function cfefd_add_fields($fields_unprocessed){
        $file_mode_conditions = CFEFD_Utils::get_mode_conditions('file', 'input');

        $fields_unprocessed['cfefd_use_as_file_upload'] = array(
            'label'           => __( 'Use As File Upload Field', 'contact-form-extender-for-divi-builder' ),
            'type'            => 'yes_no_button',
            'option_category' => 'basic_option',
            'options'         => array(
                'off' => __( 'No', 'contact-form-extender-for-divi-builder' ),
                'on'  => __( 'Yes', 'contact-form-extender-for-divi-builder' ),
            ),
            'default'         => 'off',
            'toggle_slug'     => 'field_options',
            'description'     => __( 'Turn this on to use this field for file uploads.', 'contact-form-extender-for-divi-builder' ),
            'show_if'         => array(
                'field_type' => 'input',
            ),
            'show_if_not'     => $file_mode_conditions['show_if_not'],
        );

        // 2. Max file size (KB)
        $fields_unprocessed['cfefd_fileupload_max_size'] = array(
            'label'           => __( 'Max File Size (KB)', 'contact-form-extender-for-divi-builder' ),
            'type'            => 'text',
            'option_category' => 'basic_option',
            'toggle_slug'     => 'field_options',
            'show_if'         => $file_mode_conditions['show_if'],
            'show_if_not'     => $file_mode_conditions['show_if_not'],
            'description'     => __( 'Set the maximum allowed upload size (in KB).', 'contact-form-extender-for-divi-builder' ),
        );

        // 3. Allowed file types
        $fields_unprocessed['cfefd_fileupload_allowed_types'] = array(
            'label'           => __( 'Allowed File Types', 'contact-form-extender-for-divi-builder' ),
            'type'            => 'text',
            'option_category' => 'basic_option',
            'toggle_slug'     => 'field_options',
            'show_if'         => $file_mode_conditions['show_if'],
            'show_if_not'     => $file_mode_conditions['show_if_not'],
            'description'     => __(
                'Add multiple values comma (,) separated. Example: jpg, png, pdf, docx.',
                'contact-form-extender-for-divi-builder'
            ),
        );

        // 4. Max number of files
        $fields_unprocessed['cfefd_fileupload_max_files'] = array(
            'label'           => __( 'Max Number of Files', 'contact-form-extender-for-divi-builder' ),
            'type'            => 'text',
            'option_category' => 'basic_option',
            'toggle_slug'     => 'field_options',
            'show_if'         => $file_mode_conditions['show_if'],
            'show_if_not'     => $file_mode_conditions['show_if_not'],
            'description'     => __( 'Set how many files the user can upload.', 'contact-form-extender-for-divi-builder' ),
        );

        $fields_unprocessed['cfefd_use_file_button_icon'] = [
            'label' => __('Show Button Icon', 'contact-form-extender-for-divi-builder'),
            'type' => 'yes_no_button',
            'option_category' => 'configuration',
            'options' => [ 'on' => 'Yes', 'off' => 'No' ],
            'default' => 'on',
            'show_if'         => $file_mode_conditions['show_if'],
            'show_if_not'     => $file_mode_conditions['show_if_not'],
            'toggle_slug' => 'field_options',
        ];
        
        $fields_unprocessed['cfefd_file_button_icon'] = [
            'label' => __('Button Icon', 'contact-form-extender-for-divi-builder'),
            'type' => 'select_icon',
            'default' => '',
            'show_if' => array_merge(
                $file_mode_conditions['show_if'],
                array(
                    'cfefd_use_file_button_icon' => 'on',
                )
            ),
            'show_if_not' => $file_mode_conditions['show_if_not'],
            'toggle_slug' => 'field_options',
        ];

        return $fields_unprocessed;
    }

    public function get_shadow_presets_values($preset = null){
        $presets = [
            'none' => [
                'horizontal' => '',
                'vertical' => '',
                'blur' => '',
                'spread' => '',
                'position' => '',
            ],
            'preset1' => [
                'horizontal' => '0px',
                'vertical' => '2px',
                'blur' => '18px',
                'spread' => '0px',
                'position' => '',
            ],
            'preset2' => [
                'horizontal' => '6px',
                'vertical' => '6px',
                'blur' => '18px',
                'spread' => '0px',
                'position' => '',
            ],
            'preset3' => [
                'horizontal' => '0px',
                'vertical' => '12px',
                'blur' => '18px',
                'spread' => '-6px',
                'position' => '',
            ],
            'preset4' => [
                'horizontal' => '10px',
                'vertical' => '10px',
                'blur' => '0px',
                'spread' => '0px',
                'position' => '',
            ],
            'preset5' => [
                'horizontal' => '0px',
                'vertical' => '6px',
                'blur' => '0px',
                'spread' => '10px',
                'position' => '',
            ],
            'preset6' => [
                'horizontal' => '0px',
                'vertical' => '0px',
                'blur' => '18px',
                'spread' => '0px',
                'position' => 'inset',
            ],
            'preset7' => [
                'horizontal' => '10px',
                'vertical' => '10px',
                'blur' => '0px',
                'spread' => '0px',
                'position' => 'inset',
            ],
        ];
        if (!empty($preset) && isset($presets[$preset])) {
            return $presets[$preset];
        }

        return $presets;
    }
    
    public function get_shadow_presets($prefix){
        $options = [];
        foreach (self::get_shadow_presets_values() as $key => $value) {
            if ('none' === $key) {
                $options[] = [
                    'value' => $key,
                    'icon' => $key,
                    'fields' => [
                        $prefix.'_shadow_horizontal' => '0',
                        $prefix.'_shadow_vertical' => '0',
                        $prefix.'_shadow_blur' => '0',
                        $prefix.'_shadow_spread' => '0',
                        $prefix.'_shadow_position' => '',
                    ]
                ];
            } else {
                $options[] = [
                    'value' => $key,
                    'content' => sprintf('<span class="preset %1$s"></span>', esc_attr($key)),
                    'fields' => [
                        $prefix.'_shadow_horizontal' => $value["horizontal"],
                        $prefix.'_shadow_vertical' => $value["vertical"],
                        $prefix.'_shadow_blur' => $value["blur"],
                        $prefix.'_shadow_spread' => $value["spread"],
                        $prefix.'_shadow_position' => $value["position"],
                    ]
                ];
            }
        }

        return $options;
    }
}

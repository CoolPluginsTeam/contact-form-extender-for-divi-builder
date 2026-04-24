<?php

if (!defined('ABSPATH')) {
    die;
}

class CFEFD_File_Upload_Render {

    public function __construct() {
        add_filter('et_module_shortcode_output', [$this, 'cfefd_filter_shortcode_output'], 10, 3);
        add_filter('et_module_shortcode_output', [$this, 'cfefd_filter_shortcode_output_css'], 10, 3);
        add_action('wp_enqueue_scripts', [$this, 'cfefd_enqueue_scripts']);
        // Styles should also be enqueued
        add_action('wp_enqueue_scripts', [$this, 'cfefd_enqueue_styles']);
    }

    public function cfefd_enqueue_styles() {
        // Create a simple css for button if needed, or rely on Divi styles.
        // For now we assume Divi styles or custom styles in js injection
        wp_enqueue_style('cfefd-file-upload-field-helper', CFEFD_PLUGIN_URL . 'assets/css/file-upload-field-helper.css', array(), CFEFD_PLUGIN_VERSION, 'all');
    }

    public function cfefd_enqueue_scripts(){
        $wp_max_upload_size = wp_max_upload_size();
        $localized_data = [
            'ajaxURL' => esc_js(admin_url('admin-ajax.php')),
            'ajaxNonce' => wp_create_nonce('cfefd-nonce-ajax'),
            'pluginURL' => CFEFD_PLUGIN_URL,
            'wpMaxUploadSize' => $wp_max_upload_size,
            'wpMaxUploadSizeFormatted' => size_format($wp_max_upload_size),
        ];
        
        wp_enqueue_script('cfefd-file-upload-field-helper', CFEFD_PLUGIN_URL . 'assets/js/file-upload-field-helper.js', array('jquery'), CFEFD_PLUGIN_VERSION , true); 
        wp_localize_script('cfefd-file-upload-field-helper', 'CFEFD_DiviContactFormExtender',  $localized_data);
    }

    public function cfefd_filter_shortcode_output_css($output, $render_slug, $module) {
        if ( 'et_pb_contact_form' !== $render_slug ) {
            return $output;
        }

        // Return If Frontend Builder
        if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
            return $output;
        }

        // Return If Backend Builder
        if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
            return $output;
        }

        $props = $module->props;
        $order_class = ET_Builder_Element::get_module_order_class('et_pb_contact_form');

        $this->generate_css($props, $order_class);

        return $output;
    }

    public function cfefd_filter_shortcode_output($output, $render_slug, $module){
        // Only touch Divi contact form fields
        if ( 'et_pb_contact_field' !== $render_slug ) {
            return $output;
        }

        $props = $module->props;
        
        // Ensure $output is string
        if(gettype($output) !== 'string'){
            return $output;
        }
        
        $is_file_upload = isset($props['cfefd_use_as_file_upload']) && $props['cfefd_use_as_file_upload'] === 'on';
        
        // Fallback: Check if output contains data-type="file_upload" if Divi rendered it somehow
        if ( !$is_file_upload && strpos( $output, 'data-type="file_upload"' ) === false ) {
            return $output;
        }

        // Prevent Duplicate Rendering
        if ( strpos( $output, 'cfefd_contact_hidden_files' ) !== false ) {
            return $output;
        }

        $parent_order_class = ET_Builder_Element::get_module_order_class('et_pb_contact_form');
        $parts = explode('_', $parent_order_class);
        $parent_module_order = end($parts);

        $field_id = isset($props['field_id']) ? $props['field_id'] : '';

        $dom = CFEFD_Utils::create_dom($output);
        $input = $dom->getElementsByTagName('input');
        $p_tag = $dom->getElementsByTagName('p');
        
        // If we can't find expected structure, return original
        if ( $p_tag->length === 0 ) {
            return $output;
        }
        
        $new_input_tag = $dom->createElement('input');
        $new_input_tag->setAttribute('type', 'text');
        $p_item = $p_tag->item(0);
        $input_item = null;
        $input_name = '';
        $input_class = '';

        if ($input->length > 0) {
            $input_item = $input->item(0);
            $input_name = $input_item->getAttribute('name');
            $input_class = $input_item->getAttribute('class');
        } else {
            $input_item = $dom->createElement('input');
            $input_item->setAttribute('type', 'text');
            $input_name = 'et_pb_contact_' . $field_id . '_' . $parent_module_order;
            $input_item->setAttribute('name', $input_name);
            $input_class = 'et_pb_contact_field';
            $input_item->setAttribute('class', $input_class);
            $p_item->appendChild($input_item);
        }

        if ($input_item) {
            $input_item->setAttribute('class', $input_class . ' cfefd_contact_hidden_files cool_hidden_original');

            $input_item->setAttribute('readonly', 'readonly');
            $input_item->setAttribute('data-field-id', $field_id);
            
            $p_item = $p_tag->item(0);
            $p_class = $p_item->getAttribute('class');
            // Changed class to cfefd_files_container
            $p_item->setAttribute('class', "$p_class cfefd_files_container");
            
            $file_size = !empty($props['cfefd_fileupload_max_size']) ? preg_replace('/\D/', '', $props['cfefd_fileupload_max_size']) : '1024';
            $file_mimes = !empty($props['cfefd_fileupload_allowed_types']) ? $props['cfefd_fileupload_allowed_types'] : '.jpg,.png';
            
            // Process MIMEs
            $processed_mimes = $this->process_multiple_mimes_checkboxes_value($file_mimes);

            // Fallback if no valid MIMEs found
            if ( empty( $processed_mimes['keys'] ) ) {
                $file_mimes = '.jpg,.png';
                $processed_mimes = $this->process_multiple_mimes_checkboxes_value($file_mimes);
            }

            $files_extentions = $processed_mimes['values'];
            $files_mimes = $processed_mimes['keys'];
            
            $file_size_bytes = $file_size * 1024; // KB to Bytes
             // Override with WP max
            $wp_max_upload_size = wp_max_upload_size();
            if ($file_size_bytes > $wp_max_upload_size) {
                 $file_size_bytes = $wp_max_upload_size;
            }
            $file_size_formatted = size_format($file_size_bytes);
            
            $files_limit = !empty($props['cfefd_fileupload_max_files']) ? $props['cfefd_fileupload_max_files'] : 2;
             
            $file_desc = sprintf('%1$s %2$s. %3$s %4$s', __('Accepted file types:', 'contact-form-extender-for-divi-builder'), $files_extentions, __('Max. file size:', 'contact-form-extender-for-divi-builder'), $file_size_formatted);


            // Create the visible UI components
            
            // 1. Label
            $file_label = $dom->createElement('label', 'File Input');
            $file_label->setAttribute('for', "et_pb_file_input_$field_id");
            $file_label->setAttribute('class', 'et_pb_visually_hidden');
            $p_item->appendChild($file_label);
            
            // 2. File Input (The one user clicks)
            $file_input = $dom->createElement('input');
            $file_input->setAttribute('type', 'file');
            // Changed class to cfefd_file_input
            $file_input->setAttribute('class', $input_class . ' cfefd_file_input');
            $file_input->setAttribute('id', "et_pb_file_input_$field_id");
            $file_input->setAttribute('name', $input_name);
             // Note: This input does NOT have the 'name' attribute of the form field, so it isn't submitted normally.
             // It's just for selecting files for AJAX.
            $file_input->setAttribute('data-limit', $files_limit);
            $file_input->setAttribute('multiple', 'multiple');
            
            $file_input->setAttribute('data-field-id', $field_id);
            $file_input->setAttribute('data-size', $file_size_bytes);
            $file_input->setAttribute('data-size-formatted', $file_size_formatted);
            $p_item->appendChild($file_input);
            
            // 3. Upload Button (Visual)
            $file_upload_button = $dom->createElement('span', __('Choose Files', 'contact-form-extender-for-divi-builder'));
             // Add role button
            $btn_role = $dom->createAttribute('role');
            $btn_role->value = 'button';
            $file_upload_button->appendChild($btn_role);

            // Changed class to cfefd_file_upload_button
            $button_class_attr = 'cfefd_file_upload_button et_pb_button';
            if (isset($props['cfefd_use_file_button_icon']) && $props['cfefd_use_file_button_icon'] === 'on') {
                $button_class_attr .= ' et_pb_icon';
                $file_button_class = '%%order_class%% .cfefd_files_container .cfefd_file_upload_button.et_pb_button';
                $file_button_icon = !empty($props['cfefd_file_button_icon']) ? $props['cfefd_file_button_icon'] : '5';

                $file_button_icon_processed = html_entity_decode(esc_attr(et_pb_process_font_icon($file_button_icon)));

                if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                        ET_Builder_Element::set_style('et_pb_contact_form', [
                            'selector' => "$file_button_class:after, $file_button_class:before",
                            'declaration' => sprintf("font-family:%s !important;font-weight:%s !important;", et_pb_get_icon_font_family($file_button_icon), et_pb_get_icon_font_weight($file_button_icon)),
                    ]);
                }
                ET_Builder_Element::set_style('et_pb_contact_form', [
                    'selector' => "$file_button_class:after, $file_button_class:before",
                    'declaration' => "content:'$file_button_icon_processed' !important;",
                ]);
            }

            $file_upload_button->setAttribute('class', $button_class_attr);
            $p_item->appendChild($file_upload_button);
            
            // 4. Chosen File Text
            $file_chosen_span = $dom->createElement('span', __('No file chosen', 'contact-form-extender-for-divi-builder'));
            // Changed class to cfefd_file_chosen_desc
            $file_chosen_span->setAttribute('class', 'cfefd_file_chosen_desc');
            $p_item->appendChild($file_chosen_span);
            
            // 5. Hidden Flag Input (to help submission handler identify this field)
            $file_hidden_input = $dom->createElement('input');
            $file_hidden_input->setAttribute('type', 'hidden');
            $file_hidden_input->setAttribute('name', $input_name.'_is_file');
            $file_hidden_input->setAttribute('value', 'yes');
            $p_item->appendChild($file_hidden_input);
            
            // 6. Token Input (Security)
            $file_token_input = $dom->createElement('input');
            $file_token_input->setAttribute('type', 'hidden');
            $file_token_input->setAttribute('name', $input_name.'_file_token');
            $token_data = [
                'size' => $file_size_bytes,
                'extentions' => $files_extentions,
                'mimetypes' => $files_mimes,
                'limit' => $files_limit,
            ];
            $file_token_input->setAttribute('value', CFEFD_File_Upload::encrypt_decrypt(wp_json_encode($token_data)));
            $p_item->appendChild($file_token_input);
            
            // 7. Description
            $file_description = $dom->createElement('span');
            // Changed id slightly? No, ID should be unique. Using standard ID format helps JS targeting.
            // Helper uses "et_pb_accepted_files_desc_$field_id". We can use "cfefd_accepted_files_desc_$field_id".
            $file_description->setAttribute('id', "cfefd_accepted_files_desc_$field_id");
            // Changed class to cfefd_accepted_files_desc
            $file_description->setAttribute('class', 'cfefd_accepted_files_desc');
            $file_description->setAttribute('data-description', $file_desc);
             // Using createTextNode is safe
             $file_description->appendChild($dom->createTextNode($file_desc));
            $p_item->appendChild($file_description);
            
            // 8. Files List Container
            $files_list = $dom->createElement('span');
            // Changed ID to cfefd prefix
            $files_list->setAttribute('id', "cfefd_files_list_$field_id");
            // Changed class to cfefd_files_list
            $files_list->setAttribute('class', 'cfefd_files_list');
            $p_item->appendChild($files_list);
            
            $output = $dom->saveHTML();
        }

        return $output;
    }

    protected function generate_css($props, $order_class) {
        $val = function($key) use ($props) {
            return isset($props[$key]) ? $props[$key] : '';
        };

        $container_class = '.' . $order_class . ' .cfefd_files_container';
        $accept_desc_class = '.' . $order_class . ' .cfefd_accepted_files_desc';
        $chosen_desc_class = '.' . $order_class . ' .cfefd_file_chosen_desc';
        $button_class = '.' . $order_class . ' .cfefd_file_upload_button';

        // 1. Container Styles
        if ($bg = $val('cfefd_files_container_background')) { 
            $bg = $this->process_color($bg);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "background-color: $bg !important;",
            ]);
        }
        if ($padding = $val('cfefd_files_container_padding')) {
            $padding = $this->process_margin_padding($padding);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "padding: $padding !important;",
            ]);
        }
        
        if ($br = $val('cfefd_files_container_border')) {
             $br = $this->process_margin_padding($br); // border-radius can also be pipe-delimited
             ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "border-radius: $br !important;",
            ]);
        }
        if ($bw = $val('cfefd_files_container_border_width')) {
            $bw = $this->process_range_value($bw);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "border-width: $bw !important;",
            ]);
        }
        if ($bc = $val('cfefd_files_container_border_color')) {
            $bc = $this->process_color($bc);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "border-color: $bc !important;",
            ]);
        }
        if ($bs = $val('cfefd_files_container_border_style')) {
            ET_Builder_Element::set_style($order_class, [
                'selector' => $container_class,
                'declaration' => "border-style: $bs !important;",
            ]);
        }
        
        // Shadow
        if ($shadow = $val('cfefd_files_container_shadow')) {
            if ($shadow !== 'none') {
                 $h = $this->process_range_value($val('cfefd_files_container_shadow_horizontal'));
                 if ($h === '') $h = '0px';
                 $v = $this->process_range_value($val('cfefd_files_container_shadow_vertical'));
                 if ($v === '') $v = '2px';
                 $b = $this->process_range_value($val('cfefd_files_container_shadow_blur'));
                 if ($b === '') $b = '18px';
                 $s = $this->process_range_value($val('cfefd_files_container_shadow_spread'));
                 if ($s === '') $s = '0px';
                 $c = $this->process_color($val('cfefd_files_container_shadow_color'));
                 if ($c === '') $c = 'rgba(0,0,0,0.3)';
                 $p = $val('cfefd_files_container_shadow_position');
                 
                 $declaration = "box-shadow: $h $v $b $s $c" . ($p === 'inset' ? ' inset' : '') . " !important;";
                 ET_Builder_Element::set_style($order_class, [
                    'selector' => $container_class,
                    'declaration' => $declaration,
                ]);
            }
        }
        
        if ($lc = $val('cfefd_files_container_list_color')) {
            $lc = $this->process_color($lc);
            ET_Builder_Element::set_style($order_class, [
                'selector' => '.' . $order_class . ' .cfefd_files_list span a',
                'declaration' => "color: $lc !important;",
            ]);
        }

        if ($lbc = $val('cfefd_files_container_list_background_color')) {
            $lc = $this->process_color($lbc);
            ET_Builder_Element::set_style($order_class, [
                'selector' => '.' . $order_class . ' .cfefd_files_list span',
                'declaration' => "background-color: $lbc !important;",
            ]);
        }

        // 2. Description Styles
        if ($c = $val('cfefd_accepted_file_text_color')) {
            $c = $this->process_color($c);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $accept_desc_class,
                'declaration' => "color: $c !important;",
            ]);
        }

        if ($s = $val('cfefd_accepted_file_text_size')) {
            $s = $this->process_range_value($s);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $accept_desc_class,
                'declaration' => "font-size: $s !important;",
            ]);
        }
        
        if ($c = $val('cfefd_chosen_file_text_color')) {
            $c = $this->process_color($c);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $chosen_desc_class,
                'declaration' => "color: $c !important;",
            ]);
        }

        if ($font = $val('cfefd_accepted_file_text_font')) {            
            ET_Builder_Element::set_style($order_class, [
                'selector' => $accept_desc_class,
                'declaration' => et_builder_set_element_font($font),
            ]);
        }

        // 3. Button Styles
        if ($c = $val('cfefd_file_button_color')) {
            $c = $this->process_color($c);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "color: $c !important;",
            ]);
        }
        if ($bg = $val('cfefd_file_button_background')) {
            $bg = $this->process_color($bg);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "background-color: $bg !important;",
            ]);
        }
        if ($s = $val('cfefd_file_button_size')) {
             $s = $this->process_range_value($s);
             ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "font-size: $s !important;",
            ]);
        }
        if ($br = $val('cfefd_file_button_border')) {
             $br = $this->process_margin_padding($br);
             ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "border-radius: $br !important;",
            ]);
        }
        if ($bw = $val('cfefd_file_button_border_width')) {
            $bw = $this->process_range_value($bw);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "border-width: $bw !important;",
            ]);
        }
        if ($bc = $val('cfefd_file_button_border_color')) {
            $bc = $this->process_color($bc);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "border-color: $bc !important;",
            ]);
        }
        if ($m = $val('cfefd_file_button_margin')) {
            $m = $this->process_margin_padding($m);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "margin: $m !important;",
            ]);
        }
        if ($p = $val('cfefd_file_button_padding')) {
            $p = $this->process_margin_padding($p);
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => "padding: $p !important;",
            ]);
        }
        if ($font = $val('cfefd_file_button_font')) {
            ET_Builder_Element::set_style($order_class, [
                'selector' => $button_class,
                'declaration' => et_builder_set_element_font($font),
            ]);
        }
    }

    protected function process_margin_padding($value) {
        if (empty($value)) return '';
        if (strpos($value, '|') === false) return $value;

        $values = explode('|', $value);
        $clean_values = [];
        foreach ($values as $val) {
            if (in_array($val, ['true', 'false', 'on', 'off'])) continue;
            $clean_values[] = $val !== '' ? $val : '0px';
        }
        
        return implode(' ', array_slice($clean_values, 0, 4));
    }

    protected function process_range_value($value) {
        if (empty($value)) return '';
        if (strpos($value, '|') === false) return $value;

        $values = explode('|', $value);
        return $values[0];
    }

    protected function process_color($color) {
        if (empty($color)) return '';
        if (strpos($color, 'gcid-') !== false) {
            if (function_exists('et_builder_get_global_color_info')) {
                $info = et_builder_get_global_color_info($color);
                return isset($info['color']) ? $info['color'] : $color;
            }
        }
        return $color;
    }

    public function process_multiple_mimes_checkboxes_value($data) {

        $extensions = array_unique(array_filter(array_map(function ($ext) {
            return ltrim(trim($ext), '.');
        }, explode(',', $data))));

        if (empty($extensions)) {
            return ['keys' => '', 'values' => ''];
        }

        $allowed_mimes = CFEFD_File_Upload::get_wp_allowed_mime_types();

        $matched_mime_types = [];

        foreach ($allowed_mimes as $ext_group => $mime_type) {
            $group_exts = explode('|', $ext_group);

            // If ANY extension matches the group
            if (array_intersect($extensions, $group_exts)) {
                $matched_mime_types[] = $mime_type;
            }
        }

        return [
            'keys'   => implode(',', array_unique($matched_mime_types)),
            'values' => ' ' . implode(', ', $extensions),
        ];
    }
}

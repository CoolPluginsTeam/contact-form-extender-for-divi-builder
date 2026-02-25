<?php

if (!defined('ABSPATH')) {
    die;
}

class CFEFD_File_Upload_Submission {

    private $attachments_to_send = [];

    public function __construct() {
        // Init submission logic when Divi processes the form
        add_filter('et_contact_page_email_to', [$this, 'init_submission_logic']);
    }

    public function init_submission_logic($email) {
        // Global variable set by Divi during form processing
        global $et_pb_contact_form_num;

        // Reset attachments for this new submission attempt
        $this->attachments_to_send = [];

        // 1. Verify this is a valid Divi Contact Form submission
        // Divi uses a nonce named "_wpnonce-et-pb-contact-form-submitted-X"
        $nonce_key = "_wpnonce-et-pb-contact-form-submitted-{$et_pb_contact_form_num}";
        if (!isset($_POST[$nonce_key]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), 'et-pb-contact-form-submit')) {
            // return $email;
        }

        // 2. Process Files
        $upload_tmp_dir = CFEFD_File_Upload::get_wp_upload_dir(path_join(CFEFD_File_Upload::foldername, 'tmp'), 'basedir');
        
        // Iterate over POST to find file tokens for THIS form
        // Divi fields format: et_pb_contact_{field_id}_{form_num}
        // Our hidden field format: {divi_field_name}_is_file
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, '_is_file') !== false && $value === 'yes') {
                $input_name = str_replace('_is_file', '', $key);
                
                // Ensure this field belongs to the current form number
                // Field name format: et_pb_contact_FIELDID_FORMNUM
                // We check if it ends with _{$et_pb_contact_form_num}
                if (substr($input_name, -strlen("_{$et_pb_contact_form_num}")) === "_{$et_pb_contact_form_num}") {
                    
                     if (isset($_POST[$input_name])) {
                        $file_names = sanitize_text_field(wp_unslash($_POST[$input_name]));
                        if (!empty($file_names)) {
                            $files = explode(',', $file_names);
                            foreach ($files as $file) {
                                $file = sanitize_file_name($file);
                                $file_path = path_join($upload_tmp_dir, $file);
                                if (file_exists($file_path)) {
                                    $this->attachments_to_send[] = $file_path;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 3. Register wp_mail hook only if we have files and valid submission
        if (!empty($this->attachments_to_send)) {
            add_filter('wp_mail', [$this, 'cfefd_attach_files'], 1000);
        }

        return $email;
    }

    public function cfefd_attach_files($args) {
        if (!empty($this->attachments_to_send)) {
            if (!isset($args['attachments'])) {
                $args['attachments'] = [];
            } else {
                if (!is_array($args['attachments'])) {
                    $args['attachments'] = explode("\n", str_replace("\r\n", "\n", $args['attachments']));
                }
            }
            
            $args['attachments'] = array_merge($args['attachments'], $this->attachments_to_send);

            // Schedule cleanup after script execution (and email sending) finishes
            register_shutdown_function([$this, 'cleanup_attachments']);
        }

        return $args;
    }

    public function cleanup_attachments() {
        if (!empty($this->attachments_to_send)) {
            foreach ($this->attachments_to_send as $file_path) {
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
            }
        }
    }
}

<?php

if (!defined('ABSPATH')) {
    die;
}

class CFEFD_File_Upload_Ajax {

    private $upload_tmp_dir;
    private $upload_tmp_url;

    public function __construct() {
        $this->upload_tmp_dir = CFEFD_File_Upload::get_wp_upload_dir(path_join(CFEFD_File_Upload::foldername, 'tmp'), 'basedir');
        $this->upload_tmp_url = CFEFD_File_Upload::get_wp_upload_dir(path_join(CFEFD_File_Upload::foldername, 'tmp'), 'baseurl');

        add_action('wp_ajax_cfefd_upload_file', [$this, 'cfefd_upload_file']);
        add_action('wp_ajax_nopriv_cfefd_upload_file', [$this, 'cfefd_upload_file']);
        add_action('wp_ajax_cfefd_remove_file', [$this, 'cfefd_remove_uploaded_file']);
        add_action('wp_ajax_nopriv_cfefd_remove_file', [$this, 'cfefd_remove_uploaded_file']);
    }

    public function cfefd_upload_file(){
        if (!check_ajax_referer('cfefd-nonce-ajax', '_wpnonce', false)) {
            wp_send_json_error(esc_html__('The security check failed. Please try again. Tip: Hard refresh the page (Ctrl+Shift+R on Windows/Linux or Cmd+Shift+R on Mac).', 'contact-form-extender-for-divi-builder'));
        }
        $file_error_types = [
            UPLOAD_ERR_INI_SIZE => __('The file you tried to upload is too large. Please choose a smaller file.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_FORM_SIZE => __('The file size exceeds the maximum allowed. Please choose a smaller file.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_PARTIAL => __('The file upload was incomplete. Please try uploading the file again.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_NO_FILE => __('No file was selected for upload. Please choose a file and try again.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_NO_TMP_DIR => __('Temporary folder is missing. Please contact the system administrator.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_CANT_WRITE => __('Failed to save the file to disk. Please try again or contact support.', 'contact-form-extender-for-divi-builder'),
            UPLOAD_ERR_EXTENSION => __('A PHP extension prevented the file upload. Please try again or contact support.', 'contact-form-extender-for-divi-builder')
        ];
        $error_message = '';
        $json_response = [
            'success' => [],
            'errors' => [],
        ];
        $upload_temp_dir = $this->upload_tmp_dir;
        $upload_temp_url = $this->upload_tmp_url;

        if (!file_exists($upload_temp_dir)) {
            wp_mkdir_p($upload_temp_dir);
        }

        if (!wp_is_writable($upload_temp_dir)) {
            wp_send_json_error("Upload directory is not writable: {$upload_temp_dir}");
        }

        $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : null;
        if (empty($token)) {
            wp_send_json_error(__('The file token is missing. Please contact the system administrator.', 'contact-form-extender-for-divi-builder'));
        }
        $wp_allowed_mime_types = CFEFD_File_Upload::get_wp_allowed_mime_types();
        $token = json_decode(CFEFD_File_Upload::encrypt_decrypt($token, 'd'), ARRAY_A);
        $allowd_filesize = $token['size'] ?? '';
        $allowd_mimes = isset($token['mimetypes']) ? explode(',', $token['mimetypes']) : [];
        $security_reason_text = __('File {filename} has failed to upload. Sorry, this file type is not permitted for security reasons.', 'contact-form-extender-for-divi-builder');
        $allow_filesize_text = __('File {filename} not uploaded. Maximum file size {allowed_filesize}.', 'contact-form-extender-for-divi-builder');
        // Narrow to only the file entries this uploader creates (numeric keys 0..n),
        // instead of iterating the entire $_FILES superglobal. Keys are numeric
        // (int) because we append files with numeric indexes in JS.
        $cfefd_files = array_filter(
            $_FILES,
            static function ( $key ) {
                return is_int( $key ) || ( is_string( $key ) && ctype_digit( $key ) );
            },
            ARRAY_FILTER_USE_KEY
        );

        // Check for errors in uploaded files
        foreach ( $cfefd_files as $file ) {
            $filename = isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : '';
            if ( isset( $file['error'] ) && UPLOAD_ERR_OK !== $file['error'] ) {
                $error_message = $file_error_types[ $file['error'] ] ?? __( 'Something went wrong.', 'contact-form-extender-for-divi-builder' );
            } else {
                $file_tmpname = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
                if ( '' === $file_tmpname || ! is_uploaded_file( $file_tmpname ) ) {
                    $error_message = __( 'Invalid or missing upload. Please try again.', 'contact-form-extender-for-divi-builder' );
                } else {
                    $file_type = isset( $file['type'] ) ? sanitize_text_field( $file['type'] ) : '';
                    $file_size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
                    // Ensure the correct MIME type is detected
                    if ( extension_loaded( 'fileinfo' ) ) {
                        $finfo = finfo_open( FILEINFO_MIME_TYPE );
                        $file_real_mime = finfo_file( $finfo, $file_tmpname );
                        finfo_close( $finfo );
                        $file_real_mime = is_string( $file_real_mime ) ? sanitize_text_field( $file_real_mime ) : '';
                    } else {
                        $file_real_mime = $file_type;
                    }
                    $wp_filetype = wp_check_filetype_and_ext( $file_tmpname, $filename, $wp_allowed_mime_types );
                    // Validate file type and size
                    if ( empty( $wp_filetype['type'] ) || empty( $wp_filetype['ext'] ) ) {
                        $error_message = str_replace( '{filename}', $filename, $security_reason_text );
                    } elseif ( ! in_array( $file_real_mime, $allowd_mimes, true ) || ! in_array( $file_real_mime, $wp_allowed_mime_types, true ) ) {
                        $error_message = str_replace( '{filename}', $filename, $security_reason_text );
                    } elseif ( ( $file_size > $allowd_filesize ) || ( $file_size > wp_max_upload_size() ) ) {
                        $error_message = str_replace( '{filename}', $filename, $allow_filesize_text );
                    }
                }
            }
            if (!empty($error_message)) {
                $json_response['errors'][] = [
                    'name' => $filename,
                    'message' => $error_message,
                ];
            }
        }
        // If errors are present, return the error response
        if (!empty(array_filter($json_response['errors']))) {
            wp_send_json_success($json_response);
        }

        // Upload valid files (validate tmp_name again before move),
        // again limited to the numeric keys our JS uses.
        foreach ( $cfefd_files as $file ) {
            $filename = isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : '';
            $file_tmpname = isset( $file['tmp_name'] ) && is_string( $file['tmp_name'] ) ? $file['tmp_name'] : '';
            if ( '' === $file_tmpname || ! is_uploaded_file( $file_tmpname ) ) {
                $json_response['errors'][] = [
                    'name' => $filename,
                    'message' => __( 'Invalid or missing upload. Please try again.', 'contact-form-extender-for-divi-builder' ),
                ];
                continue;
            }
            $wp_filetype = wp_check_filetype_and_ext( $file_tmpname, $filename, $wp_allowed_mime_types );
            $filename_renamed = strtolower( pathinfo( $filename, PATHINFO_FILENAME ) );
            $filename_renamed = preg_replace( '/[^A-Za-z\d\-]/', ' ', $filename_renamed );
            $file_extension = pathinfo( $filename, PATHINFO_EXTENSION );
            $filename_unique = wp_unique_filename(
                $upload_temp_dir,
                sprintf( '%1$s-%2$s-%3$s.%4$s', mb_substr( $filename_renamed, 0, 30, 'utf-8' ), str_pad( wp_rand( 999, time() ), 5, 0, STR_PAD_BOTH ), time(), $file_extension )
            );
            $file_dir = path_join( $upload_temp_dir, $filename_unique );
            if ( et_()->WPFS()->move( $file_tmpname, $file_dir ) ) {
                $file_url = path_join($upload_temp_url, $filename_unique);
                $json_response['success'][] = [
                    'tmp_name' => $filename,
                    'name' => $filename_unique,
                    'size' => size_format(filesize($file_dir)),
                    'mime' => $wp_filetype['type'],
                    'url' => $file_url,
                ];
            } else {
                $json_response['errors'][] = [
                    'name' => $filename,
                    'message' => __('Failed to move the uploaded file.', 'contact-form-extender-for-divi-builder'),
                ];
            }
        }
        // Return response for successful uploads
        if (!empty(array_filter($json_response['success']))) {
            wp_send_json_success($json_response);
        }
    }

    public function cfefd_remove_uploaded_file(){
        if (!check_ajax_referer('cfefd-nonce-ajax', '_wpnonce', false)) {
            wp_send_json_error(esc_html__('The security check failed. Please try again. Tip: Hard refresh the page (Ctrl+Shift+R on Windows/Linux or Cmd+Shift+R on Mac).', 'contact-form-extender-for-divi-builder'));
        }
        $filename = isset($_POST['file_name']) ? sanitize_text_field(wp_unslash($_POST['file_name'])) : null;
        if (!empty($filename)) {
            $tmp_path = path_join($this->upload_tmp_dir, $filename);
            if (et_()->WPFS()->is_file($tmp_path) && et_()->WPFS()->exists($tmp_path)) {
                wp_delete_file($tmp_path);
                wp_send_json_success(__('The file has been deleted successfully!', 'contact-form-extender-for-divi-builder'));
            } else {
                wp_send_json_error(__('Something went wrong. Please upload file again.', 'contact-form-extender-for-divi-builder'));
            }
        }
    }
}

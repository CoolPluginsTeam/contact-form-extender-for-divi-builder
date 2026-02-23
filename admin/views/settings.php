<?php
// Ensure the file is being accessed through the WordPress admin area
if (!defined('ABSPATH')) {
    die;
}

function cfefd_handle_unchecked_checkbox($new_value) {
        $choice  = get_option('cpfm_opt_in_choice_divi_cool_forms');

        if (!empty($choice)) {

            // If the checkbox is unchecked (value is empty, false, or null)
            if (empty($new_value)) {
                wp_clear_scheduled_hook('cfefd_extra_data_update');
            }

            // If checkbox is checked (value is 'on' or any non-empty value)
            else {
                if (!wp_next_scheduled('cfefd_extra_data_update')) {
                    if (class_exists('CFEFD\Admin\cfefd_cronjob') && method_exists('CFEFD\Admin\cfefd_cronjob', 'cfefd_send_data')) {
                        \CFEFD\Admin\cfefd_cronjob::cfefd_send_data();
                    }
                    wp_schedule_event(time(), 'every_30_days', 'cfefd_extra_data_update');
                }
            }
        }
}

if (isset($_POST['cfefd_settings_nonce'])) {
    check_admin_referer('cfefd_settings_save', 'cfefd_settings_nonce');

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound	
    $cfef_usage_share_data = isset($_POST['cfef_usage_share_data']) ? sanitize_text_field(wp_unslash($_POST['cfef_usage_share_data'])) : '';    

    cfefd_handle_unchecked_checkbox($cfef_usage_share_data);

    update_option('cfef_usage_share_data', $cfef_usage_share_data);
}
?>

<div class="cfefd-settings-box">

    <div>
        <form method="post" action="" class="cool-formkit-form">
            <div class="wrapper-header">
                <div class="cfefd-save-all">
                    <div class="cfefd-title-desc">
                        <h2><?php esc_html_e('Contact Form Extender Settings', 'contact-form-extender-for-divi-builder'); ?></h2>
                    </div>
                    <div class="cfefd-save-controls">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save Changes', 'contact-form-extender-for-divi-builder'); ?></button>
                    </div>
                </div>
            </div>
            <div class="wrapper-body">
                <?php wp_nonce_field('cfefd_settings_save', 'cfefd_settings_nonce'); ?>
                <table class="form-table cool-formkit-table">
                    <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                    <?php $cpfm_opt_in = get_option('cpfm_opt_in_choice_divi_cool_forms','');
                        if (true) {
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                            $check_option =  get_option( 'cfef_usage_share_data','');
                                if($check_option == 'on'){
                                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    $checked = 'checked';
                                }else{
                                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    $checked = '';
                                }        
                                ?>    
                                <tr>
                                    <th scope="row" class="cool-formkit-table-th">
                                        <label for="cfef_usage_share_data" class="usage-share-data-label"><?php esc_html_e('Usage Share Data', 'contact-form-extender-for-divi-builder'); ?></label>
                                    </th>
                                    <td class="cool-formkit-table-td usage-share-data">
                                        <input type="checkbox" id="cfef_usage_share_data" name="cfef_usage_share_data" value="on" <?php echo esc_attr($checked) ?>  class="regular-text cool-formkit-input"  />
                                        <div class="description cool-formkit-description">
                                            <?php esc_html_e('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'contact-form-extender-for-divi-builder'); ?>
                                            <a href="#" class="ccpw-see-terms">[<?php esc_html_e('See terms', 'contact-form-extender-for-divi-builder'); ?>]</a>
                                            <div id="termsBox" style="display: none; padding-left: 20px; margin-top: 10px; font-size: 12px; color: #999;">
                                                <p>
                                                    <?php 
                                                    printf(
                                                        /* translators: site link. */
                                                        esc_html__('Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We\'ll collect: %s', 'contact-form-extender-for-divi-builder'),
                                                            '<a href="https://my.coolplugins.net/terms/usage-tracking" target="_blank">' . esc_html__('Click here', 'contact-form-extender-for-divi-builder') . '</a>'
                                                    );
                                                    ?>
                                                </p>
                                                <ul style="list-style-type: auto;">
                                                    <li><?php esc_html_e('Your website home URL and WordPress admin email.', 'contact-form-extender-for-divi-builder'); ?></li>
                                                    <li><?php esc_html_e('To check plugin compatibility, we will collect the following: list of active plugins and themes, server type, MySQL version, WordPress version, memory limit, site language and database prefix.', 'contact-form-extender-for-divi-builder'); ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                    <?php
                     }?>
                </table>

                <div class="cool-formkit-submit">
                    <?php submit_button(); ?>
                </div>
            </div>
        </form>
    </div>
</div>

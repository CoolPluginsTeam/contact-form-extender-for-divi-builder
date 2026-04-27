<?php
// Ensure the file is being accessed through the WordPress admin area
if (!defined('ABSPATH')) {
    die;
}

if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
// Get the saved options
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound	
$enabled_elements = get_option('cfefd_enabled_elements', array());

// Check if the default plugin option is set to true

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$form_elements = array(
    'save_submission' => array(
        'label' => __('Save Submission', 'contact-form-extender-for-divi-builder'),
        'how_to' => 'https://docs.coolplugins.net/doc/save-divi-contact-form-submissions/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#range-field',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/form-list.svg',
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),
    'file_upload' => array(
        'label' => __('File Upload', 'contact-form-extender-for-divi-builder'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-form-file-upload-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#country-code',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/file-upload.svg',
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),
    'country_code' => array(
        'label' => __('Country Code', 'contact-form-extender-for-divi-builder'),
        'how_to' => 'https://docs.coolplugins.net/doc/country-code-dropdown-divi-form/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#country-code',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/country-code-min.svg',
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'range_slider' => array(
        'label' => __('Range Slider', 'contact-form-extender-for-divi-builder'),
        'how_to' => 'https://docs.coolplugins.net/doc/range-slider-divi-contact-form/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#range-field',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/range-slider-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'date_picker' => array(
        'label' => __('Date', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-date-picker-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#country-code',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/date-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'signature' => array(
        'label' => __('Signature', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-signature-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#signature-field',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/signature.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),
    
    'toggle' => array(
        'label' => __('Toggle', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/toggle-field-divi-form/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#toggle',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/toggle-field.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'image_radio' => array(
        'label' => __('Image Radio', 'contact-form-extender-for-divi-builder'),
        'how_to' => 'docs.coolplugins.net/doc/image-radio-divi-contact-form/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard/#range-field',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/image-radio.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'calculator' => array(
        'label' => __('Calculator', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-calculator-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/calculator-field-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'select2' => array(
        'label' => __('Select2', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-select-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/select2-field-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'rating' => array(
        'label' => __('Rating', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-rating-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/rating-field-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'currency' => array(
        'label' => __('Currency', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-currency-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/currency-field-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'wysiwyg' => array(
        'label' => __('WYSIWYG', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-wysiwyg-field/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/WYSIWYG-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),

    'confirm_dialog' => array(
        'label' => __('Confirm Dialog', 'divi-contact-form-extender'),
        'how_to' => 'https://docs.coolplugins.net/doc/divi-contact-form-confirm-dialog-box/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard',
        'demo' => 'https://coolplugins.net/cool-formkit-for-elementor-forms/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=demo&utm_content=dashboard',
        'icon' => CFEFD_PLUGIN_URL . 'admin/assets/icons/dialog-box-min.svg',
        'pro' => true,
        'pricing_page' => 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_badge#pricing'
    ),
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$popular_elements = array('save_submission','file_upload');
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$updated_elements = array('');
?>

<div id="cfefd-loader" style="display: none;">
  <div class="cfefd-loader-overlay"></div>
  <div class="cfefd-loader-spinner"></div>
</div>
<form method="post" action="options.php">
    <?php settings_fields('cfefd_form_elements_group'); ?>
    <?php do_settings_sections('cfefd_form_elements_group'); ?>

    <div class="cfefd-main-content">
        <div class="cfefd-form-elements-container">

            <div class="cfefd-form-element-wrapper">

                <div class="wrapper-header">
                    <div class="cfefd-save-all">
                        <div class="cfefd-title-desc">
                            <h2><?php esc_html_e('Form Elements', 'contact-form-extender-for-divi-builder'); ?></h2>
                        </div>
                        <div class="cfefd-save-controls">
                            <div class="cfefd-toggle-all-wrapper">
                                <span class="cfefd-toggle-label"><?php esc_html_e('Disable All', 'contact-form-extender-for-divi-builder'); ?></span>
                                <label class="cfefd-toggle-switch">
                                <input type="checkbox" 
                                name="cfefd_toggle_all" 
                                id="cfefd-toggle-all" 
                                value="1" 
                                <?php checked( get_option('cfefd_toggle_all', false) ); ?>>
                                <span class="cfefd-slider round"></span>
                                </label>
                                <span class="cfefd-toggle-label"><?php esc_html_e('Enable All', 'contact-form-extender-for-divi-builder'); ?></span>
                            </div>
                            <button type="submit" class="button button-primary"><?php esc_html_e('Save Changes', 'contact-form-extender-for-divi-builder'); ?></button>
                        </div>
                    </div>
                </div>
                
                <div class="wrapper-body">
                    <div>
                        <p><?php esc_html_e('Enable or disable a form element that you are using in your form module.', 'contact-form-extender-for-divi-builder'); ?></p>
                        <p><?php esc_html_e('After enabling or disabling any element make sure to click the ', 'contact-form-extender-for-divi-builder'); ?><strong><?php esc_html_e('Save Changes', 'contact-form-extender-for-divi-builder'); ?></strong> <?php esc_html_e(' button.', 'contact-form-extender-for-divi-builder'); ?></p>
                    </div>

                    <div class="cfefd-form-element-box">
                        <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
                        <?php foreach ($form_elements as $key => $element): ?>
                        <?php
                            $is_pro_element = !empty($element['pro']);
                            $is_coming_soon = !empty($element['coming_soon']);
                            $pricing_url    = 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_toggle#pricing';
                        ?>
                        <div class="cfefd-form-element-card">
                            <div class="cfefd-form-element-info">
                                <img src="<?php echo esc_url($element['icon'])?>" alt="Color Field">
                                <h4>
                                    <?php echo esc_html($element['label']); ?>
                                        <?php if (!empty($element['pro'])): ?>
                                                    <span class="cfefd-label-pro"><a href="<?php echo esc_url($element['pricing_page']) ?>" target="_blank"><?php esc_html_e('Pro','contact-form-extender-for-divi-builder'); ?></a></span>
                                        <?php endif; ?>
                                        <?php if (!empty($element['coming_soon'])): ?>
                                                    <span class="cfefd-label-coming-soon"><a href="<?php echo esc_url($element['how_to']) ?>" target="_blank"><?php esc_html_e('Coming Soon','contact-form-extender-for-divi-builder'); ?></a></span>
                                        <?php endif; ?>
                                    <?php if (in_array($key, $popular_elements)): ?>
                                        <span class="cfefd-label-popular">Popular</span>
                                    <?php endif; ?>
                                    <?php if (in_array($key, $updated_elements)): ?>
                                        <span class="cfefd-label-updated">Updated</span>
                                    <?php endif; ?>
                                </h4>
                                <div>
                                    <a href="<?php echo esc_url($element['how_to']) ?>" title="Documentation" target="_blank" rel="noreferrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#000" d="M21 11V3h-8v2h4v2h-2v2h-2v2h-2v2H9v2h2v-2h2v-2h2V9h2V7h2v4zM11 5H3v16h16v-8h-2v6H5V7h6z"/></svg>
                                    </a>
                                </div>
                            </div>
                            <label
                                class="cfefd-toggle-switch<?php echo $is_pro_element ? ' cfefd-pro-toggle' : ''; ?>"
                                <?php if ($is_pro_element): ?>
                                    data-pricing-url="<?php echo esc_url($pricing_url); ?>"
                                    data-tooltip="<?php esc_attr_e('Pro', 'contact-form-extender-for-divi-builder'); ?>"
                                <?php endif; ?>
                            >
                                <input type="checkbox" name="cfefd_enabled_elements[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $enabled_elements)); ?> class="cfefd-element-toggle"  <?php disabled($is_pro_element || $is_coming_soon); ?>>
                                <span class="cfefd-slider round"></span>
                                <?php if ($is_pro_element): ?>
                                    <span class="cfefd-tooltip"><?php esc_html_e('Pro', 'contact-form-extender-for-divi-builder'); ?></span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <div class="cfefd-save-bottom">
                <?php submit_button(__('Save Changes', 'contact-form-extender-for-divi-builder')); ?>
            </div>
            
        </div>
        <div class="cfefd-sidebar">            
            <div class="cfefd-sidebar-block">
                <h3><?php esc_html_e('Enjoying Our Plugin?', 'contact-form-extender-for-divi-builder'); ?></h3>
                <p><?php esc_html_e('Please consider leaving us a review. It helps us a lot!', 'contact-form-extender-for-divi-builder'); ?></p>
                <div class="cfefd-sidebar-link-group">
                    <div class="cfefd-review-right">
                        <div class="cfefd-stars">
                        ★★★★★
                        </div>
                        <a href="https://wordpress.org/support/plugin/contact-form-extender-for-divi-builder/reviews/" class="button button-primary" target="_blank"><?php esc_html_e('Leave a Review', 'contact-form-extender-for-divi-builder'); ?></a>
                    </div>
                </div>
            </div>

            <div class="cfefd-sidebar-block">
                <h3><?php esc_html_e('Need Help?', 'contact-form-extender-for-divi-builder'); ?></h3>
                <p><?php esc_html_e('Need assistance with setup or troubleshooting? Visit our support forum for help from the team.', 'contact-form-extender-for-divi-builder'); ?></p>
                <div class="cfefd-sidebar-link-group">
                    <div class="cfefd-review-right">
                        <a href="https://wordpress.org/support/plugin/contact-form-extender-for-divi-builder/" class="button button-primary" target="_blank"><?php esc_html_e('Get Support', 'contact-form-extender-for-divi-builder'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

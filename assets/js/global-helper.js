class CFEFD_Global_Helper {
    constructor() {
        this.init();
    }

    /**
     * Initialize the class
     */
    init() {
        this.bindEvents();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Visual Builder: Ensure toggles for country code and file upload are visible
        jQuery(document).on('mousedown click', '.et-fb-form__toggle[data-name="field_options"]', this.addClassOnToggle.bind(this));
    }

    addClassOnToggle(e) {
        const current = jQuery(e.currentTarget);
        setTimeout(() => {
            let fileUploadToggle = current.find('.et-fb-form__group input[name="cfefd_use_as_file_upload"]');
            fileUploadToggle.closest('.et-fb-option-container').addClass('should_not_hide_warning');

            console.log(' here wer are ')
            let countyCodeToggle = current.find('.et-fb-form__group input[name="cfefd_use_as_country_code"]');
            console.log(countyCodeToggle)
            countyCodeToggle.closest('.et-fb-option-container').addClass('should_not_hide_warning');
        }, 1000);
    }
}

jQuery(document).ready(function ($) {
    new CFEFD_Global_Helper();
});
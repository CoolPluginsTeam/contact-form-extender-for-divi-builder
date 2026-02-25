class CFEFD_CountryCode_Helper {
    constructor() {
        this.itiInstances = {};
        this.init();
        this.bindEvents();
    }

    init() {
        this.initCountryCode();
    }

    bindEvents() {
        const self = this;
        // Form submission validation
        jQuery(document).on('click', '.et_pb_contact_submit', function (e) {
            self.validateFormInputs(e, jQuery(this));
        });
        self.customFlags();
    }

    initCountryCode() {
        const self = this;
        jQuery('input[data-cfefd-country-code="on"]').each(function () {
            var $input = jQuery(this);
            if ($input.hasClass('iti-added')) {
                return;
            }

            // Generate a unique ID if not present for tracking instances
            var inputId = $input.attr('id');
            if (!inputId) {
                inputId = 'cfefd-iti-' + Math.random().toString(36).substr(2, 9);
                $input.attr('id', inputId);
            }

            var defaultCountry = $input.data('default-country') || 'in';
            var includeCountries = $input.data('include-countries') ? $input.data('include-countries').split(',').map(function (s) { return s.trim(); }) : [];
            var excludeCountries = $input.data('exclude-countries') ? $input.data('exclude-countries').split(',').map(function (s) { return s.trim(); }) : [];
            var dialCodeVisibility = $input.data('dial-code-visibility') || 'show';
            var strictMode = $input.data('strict-mode') === 'on';
            var preferredCountries = $input.data('preferred-countries') ? $input.data('preferred-countries').split(',').map(function (s) { return s.trim(); }) : [];
            let utilsPath = CFEDF_Data.pluginUrl + 'assets/lib/js/utils.min.js';

            const defaultCoutiresArr = ['in', 'us', 'gb', 'ru', 'fr', 'de', 'br', 'cn', 'jp', 'it'];

            if (includeCountries.length > 0) {
                defaultCountry = includeCountries[0];
            }

            if (excludeCountries.length > 0) {
                let uniqueValue = defaultCoutiresArr.filter((value) => !excludeCountries.includes(value));
                if (uniqueValue.length > 0) {
                    defaultCountry = uniqueValue[0];
                }
            }

            // Explicit default country setting takes precedence
            if ($input.data('default-country') && '' !== $input.data('default-country')) {
                defaultCountry = $input.data('default-country');
            }

            var itiOptions = {
                initialCountry: defaultCountry,
                preferredCountries: preferredCountries,
                onlyCountries: includeCountries.length > 0 ? includeCountries : [],
                excludeCountries: excludeCountries,
                containerClass: 'cfefd-intl-container',
                strictMode: strictMode,
                separateDialCode: dialCodeVisibility === 'separate',
                utilsScript: utilsPath,
                formatOnDisplay: false,
                formatAsYouType: true,
                autoFormat: false,
                useFullscreenPopup: false,
                customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {
                    if (document.body.classList.contains('et-fb')) {
                        return '';
                    }
                    if (!selectedCountryData || !selectedCountryPlaceholder || !selectedCountryData.dialCode) {
                        return "No country found";
                    }

                    let placeHolder = selectedCountryPlaceholder;
                    if ('in' === selectedCountryData.iso2) {
                        placeHolder = selectedCountryPlaceholder.replace(/^0+/, '');
                    }

                    const placeholderText = dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide' ? `${placeHolder}` : `+${selectedCountryData.dialCode} ${placeHolder}`;
                    return placeholderText;
                },
            };

            var iti = window.intlTelInput($input[0], itiOptions);
            setTimeout(() => {
                if (document.body.classList.contains('et-fb')) {
                    if (jQuery(iti.countryContainer).find('.iti__selected-country').attr('title') === 'Afghanistan: +93' && defaultCountry != 'af') {
                        iti.setCountry(defaultCountry);
                    }
                }
            }, 500)

            $input.addClass('iti-added');
            self.itiInstances[inputId] = {
                iti: iti,
                dialCodeVisibility: dialCodeVisibility,
                strictMode: strictMode
            };

            // Filter the country list manually as well (to ensure items are hidden)
            const countryList = iti.countryList;
            if (countryList && countryList.classList.contains('iti__country-list')) {
                const countryItems = countryList.querySelectorAll('.iti__country');

                countryItems.forEach(function (item) {
                    const countryCode = item.getAttribute('data-country-code');
                    if (excludeCountries.includes(countryCode)) {
                        item.style.display = 'none';
                    }
                });

                const visibleCountries = Array.from(countryItems).filter(item => item.style.display !== 'none');
                const includedVisibleCountries = visibleCountries.filter(item => {
                    const countryCode = item.getAttribute('data-country-code');
                    // return includeCountries.length === 0 || includeCountries.includes(countryCode);
                    return includeCountries.includes(countryCode);
                });

                if (includedVisibleCountries.length > 0) {
                    const selectedItem = includedVisibleCountries.find(item => item.getAttribute('aria-selected') === 'true');
                    if (!selectedItem) {
                        const firstItem = includedVisibleCountries[0];
                        firstItem.setAttribute('aria-selected', 'true');
                        const newCountryCode = firstItem.getAttribute('data-country-code');
                        iti.setCountry(newCountryCode);
                    }
                }
            }

            self.attachCountryLogic($input, iti, dialCodeVisibility, strictMode, self);
            self.setInitialCountry(iti, $input, excludeCountries, defaultCountry);
        });
    }

    setInitialCountry(itiInstance, $input, excludeCountries, defaultCont = '') {
        const defaultCountry = defaultCont || $input.data('default-country') || '';
        const defaultCountries = ['in', 'us', 'gb', 'ru', 'fr', 'de', 'br', 'cn', 'jp', 'it'];

        // All available countries from intl-tel-input instance
        const itiCountriesList = itiInstance.countries.map(data => data.iso2);

        /**
         * Helper to safely set country
         */
        const setCountry = (countryCode) => {
            if (!itiCountriesList.length) {
                return;
            }

            const normalizedCode = countryCode && isNaN(countryCode)
                ? countryCode.toLowerCase()
                : '';

            // Priority order:
            // 1. Valid detected/default country
            // 2. Explicit defaultCountry
            // 3. First allowed country from default list
            // 4. First available country

            if (normalizedCode && itiCountriesList.includes(normalizedCode)) {
                itiInstance.setCountry(normalizedCode);
            } else if (defaultCountry && itiCountriesList.includes(defaultCountry)) {
                itiInstance.setCountry(defaultCountry);
            } else {
                const availableCountries = defaultCountries.filter(code =>
                    itiCountriesList.includes(code) && !excludeCountries.includes(code)
                );

                const fallbackCountry = availableCountries.length
                    ? availableCountries[0]
                    : itiCountriesList[0];

                itiInstance.setCountry(fallbackCountry);
            }
        };

        setCountry(defaultCountry);
    }


    validateFormInputs(e, button) {
        const $form = button.closest('form');
        const self = this;
        let isFormValid = true;

        $form.find('input[data-cfefd-country-code="on"]').each(function () {
            const $input = jQuery(this);
            const inputId = $input.attr('id');
            const instance = self.itiInstances[inputId];
            let intlContainer = $input.closest('.cfefd-intl-container');

            if (instance) {
                const iti = instance.iti;
                const value = $input.val();

                if ('' === value) {
                    if ($input.data('required_mark') === 'required') {
                        isFormValid = false;
                        $input.addClass('et_contact_error');

                        if (intlContainer.siblings('.cfefd-error-msg').length === 0) {
                            intlContainer.after(
                                '<span class="cfefd-error-msg" style="color:red;font-size:12px;">Phone number is required</span>'
                            );
                        } else {
                            intlContainer.siblings('.cfefd-error-msg').text('Phone number is required');
                        }
                    } else {
                        $input.removeClass('et_contact_error');
                        intlContainer.siblings('.cfefd-error-msg').remove();
                    }
                    // Return early from this iteration for empty fields
                    return true;
                }

                self.fixDiviLabel($input);
                // Temporary value for validation if hidden/separate
                const currentCountry = iti.getSelectedCountryData();
                const dialCode = `+${currentCountry.dialCode}`;
                let originalValue = value;

                if (instance.dialCodeVisibility === 'separate' || instance.dialCodeVisibility === 'hide') {
                    if (!value.startsWith('+')) {
                        $input.val(dialCode + value);
                    }
                }

                if (!iti.isValidNumber()) {
                    $input.addClass('iti-error');
                    isFormValid = false;

                    const errorType = iti.getValidationError();
                    const errorMsg = CFEDF_Data.errorMap[errorType] || "Invalid phone number";

                    // Add error message if it doesn't exist (Divi style)
                    if (intlContainer.siblings('.cfefd-error-msg').length === 0) {
                        intlContainer.after('<span class="cfefd-error-msg" style="color:red; font-size:12px;">' + errorMsg + '</span>');
                    } else {
                        intlContainer.siblings('.cfefd-error-msg').text(errorMsg);
                    }
                } else {
                    $input.removeClass('iti-error');
                    intlContainer.siblings('.cfefd-error-msg').remove();
                }

                // Restore original value
                if (isFormValid === false && (instance.dialCodeVisibility === 'separate' || instance.dialCodeVisibility === 'hide')) {
                    $input.val(originalValue);
                }
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
    }

    fixDiviLabel($input) {
        const $field = $input.closest('.et_pb_contact_field');

        // If Divi label already exists near input, do nothing
        if ($input.siblings('.et_pb_contact_form_label').length) {
            return;
        }

        const labelText = $field.find('.et_pb_contact_form_label').first().text();

        if (!labelText) return;

        // Clone label in the place Divi expects
        const $hiddenLabel = jQuery(
            '<label class="et_pb_contact_form_label cfefd-cloned-label" style="display:none;"></label>'
        ).text(labelText);

        $input.before($hiddenLabel);
    }

    customFlags() {
        if (document.body.classList.contains('et-fb')) {
            return '';
        }

        const $form = jQuery('.et_pb_contact_form');
        $form.find('.cfefd-intl-container .iti__country-container .iti__flag:not(.iti__globe)').each(function () {
            const selectedCountry = this;  // 'this' refers to the current element in the loop
            const classList = selectedCountry.className.split(' ');

            if (classList[1]) {
                const selectedCountryFlag = classList[1].split('__')[1];
                const svgFlagPath = CFEDF_Data.pluginUrl + `assets/flags/${selectedCountryFlag}.svg`;

                selectedCountry.style.backgroundImage = `url('${svgFlagPath}')`;
            }
        });
    }

    attachCountryLogic($input, iti, dialCodeVisibility, strictMode, self) {
        let previousCountry = iti.getSelectedCountryData();
        let previousCode = `+${previousCountry.dialCode}`;
        let keyInteraction = false;

        const resetKeyInteraction = () => {
            keyInteraction = false;
        };

        const handleChange = (e) => {
            self.customFlags();
            const currentCountry = iti.getSelectedCountryData();
            const currentCode = `+${currentCountry.dialCode}`;

            if (e.type === 'keydown' || e.type === 'input') {
                keyInteraction = true;
                clearTimeout(resetKeyInteraction);
                setTimeout(resetKeyInteraction, 400);

                if (previousCountry.dialCode !== currentCountry.dialCode) {
                    previousCountry = currentCountry;
                } else if (previousCountry.dialCode === currentCountry.dialCode && previousCountry.iso2 !== currentCountry.iso2) {
                    iti.setCountry(previousCountry.iso2);
                }
            } else if (e.type === "countrychange") {
                if (keyInteraction) {
                    return;
                }
                previousCountry = currentCountry;
            }

            let value = $input.val();

            if (currentCode && '+undefined' === currentCode || ['', '+'].includes(value)) {
                return;
            }

            if (currentCode !== previousCode) {
                value = value.replace(new RegExp(`^\\${previousCode}`), '');
            }

            if (!value.startsWith(currentCode)) {
                value = value.replace(/\+/g, '');
                $input.val(dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide' ? value : currentCode + value);
            } else if (value.length > 12) {
                const plainCode = currentCode.replace('+', '');
                const doublePrefix = `+${plainCode}${plainCode}`;
                if (value.startsWith(doublePrefix)) {
                    $input.val(`+${value.slice(currentCode.length)}`);
                }
            }

            previousCode = currentCode;
        };

        $input.on('keydown input', handleChange);
        $input[0].addEventListener('countrychange', handleChange);

        let intlContainer = $input.closest('.cfefd-intl-container');
        // Strict validation
        if (strictMode) {
            $input.on('blur', () => {
                const value = $input.val();
                if ('' === value) {
                    $input.removeClass('iti-error');
                    intlContainer.siblings('.cfefd-error-msg').remove();
                    return;
                }

                // Temporary value for validation if hidden/separate
                const currentCountry = iti.getSelectedCountryData();
                const dialCode = `+${currentCountry.dialCode}`;
                let originalValue = value;

                if (dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide') {
                    if (!value.startsWith('+')) {
                        $input.val(dialCode + value);
                    }
                }

                if (iti.isValidNumber()) {
                    $input.removeClass('iti-error');
                    intlContainer.siblings('.cfefd-error-msg').remove();
                } else {
                    $input.addClass('iti-error');
                    const errorType = iti.getValidationError();
                    const errorMsg = CFEDF_Data.errorMap[errorType] || "Invalid phone number";

                    if (intlContainer.siblings('.cfefd-error-msg').length === 0) {
                        intlContainer.after('<span class="cfefd-error-msg" style="color:red; font-size:12px;">' + errorMsg + '</span>');
                    } else {
                        intlContainer.siblings('.cfefd-error-msg').text(errorMsg);
                    }
                }

                // Restore original value if we modified it for validation
                if (dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide') {
                    $input.val(originalValue);
                }
            });
        }
    }
}

jQuery(document).ready(function ($) {
    window.contactFormExtenderHelper = new CFEFD_CountryCode_Helper();
});

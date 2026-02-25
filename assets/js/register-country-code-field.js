class CFEFD_CountryCode_JS {
    constructor() {
        if (!window.vendor?.wp?.hooks || !window.React) {
            return;
        }

        this.hooks = window.vendor.wp.hooks;
        this.React = window.React;
        this.timeouts = {};
        this.itiInstances = {};
        this.registerHooks();
    }

    registerHooks() {
        this.hooks.addFilter(
            'divi.moduleLibrary.moduleAttributes.divi.contact-field',
            'cfefd-country-code',
            this.addCountryCodeTypeOption.bind(this)
        );

        this.hooks.addFilter(
            'divi.module.wrapper.render',
            'cfefd-country-code',
            this.renderCountryCode.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleGroups.groups',
            'cfefd-country-code',
            this.addCountryCodeFieldVisibility.bind(this)
        );
    }

    addCountryCodeTypeOption(attributes) {
        const options = attributes?.fieldItem?.settings?.advanced?.type?.item?.component?.props?.options;

        if (!options) {
            return attributes;
        }

        attributes.fieldItem.settings.advanced.type.item.component.props.options = {
            ...options,
            country_code: {
                label: "Country Code",
            },
        };

        // Default Country
        attributes.fieldItemAdvancedCfefdCountryCodeDefault = {
            type: 'string',
            default: 'in',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 50,
                        render: true,
                        attrName: 'fieldItem.advanced.cfefdCountryCodeDefault',
                        label: 'Default Country',
                        description: 'Set default country code (e.g., in, us, gb)',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Include Countries
        attributes.fieldItemAdvancedCfefdCountryCodeInclude = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 51,
                        render: true,
                        attrName: 'fieldItem.advanced.cfefdCountryCodeInclude',
                        label: 'Only Countries',
                        description: 'Comma-separated country codes to include',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Exclude Countries
        attributes.fieldItemAdvancedCfefdCountryCodeExclude = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 52,
                        render: true,
                        attrName: 'fieldItem.advanced.cfefdCountryCodeExclude',
                        label: 'Exclude Countries',
                        description: 'Comma-separated country codes to exclude',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Dial Code Visibility
        attributes.fieldItemAdvancedCfefdDialCodeVisibility = {
            type: 'string',
            default: 'show',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 53,
                        render: true,
                        attrName: 'fieldItem.advanced.cfefdDialCodeVisibility',
                        label: 'Dial Code Visibility',
                        component: {
                            name: 'divi/select',
                            type: 'field',
                            props: {
                                options: {
                                    show: { label: 'Show' },
                                    hide: { label: 'Hide' },
                                    separate: { label: 'Separate' }
                                }
                            }
                        }
                    }
                }
            }
        };

        // Strict Mode
        attributes.fieldItemAdvancedCfefdStrictMode = {
            type: 'string',
            default: 'off',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 54,
                        render: true,
                        attrName: 'fieldItem.advanced.cfefdStrictMode',
                        label: 'Strict Mode',
                        component: {
                            name: 'divi/toggle',
                            type: 'field'
                        }
                    }
                }
            }
        };

        return attributes;
    }

    addCountryCodeFieldVisibility(groups, meta) {
        if (meta?.moduleName !== 'divi/contact-field') return groups;

        const props = groups?.contentFieldOptions?.component?.props?.fields;
        if (!props) return groups;

        const countryCodeFields = [
            'fieldItemAdvancedCfefdCountryCodeDefaultInnercontent',
            'fieldItemAdvancedCfefdCountryCodeIncludeInnercontent',
            'fieldItemAdvancedCfefdCountryCodeExcludeInnercontent',
            'fieldItemAdvancedCfefdDialCodeVisibilityInnercontent',
            'fieldItemAdvancedCfefdStrictModeInnercontent'
        ];

        countryCodeFields.forEach(fieldName => {
            const field = props[fieldName];
            if (field) {
                field.visible = (props) => {
                    const typeValue = props.attrs?.fieldItem?.advanced?.type?.desktop?.value;
                    return typeValue === 'country_code';
                };
            }
        });

        return groups;
    }

    renderCountryCode(moduleWrapper, param) {
        const {
            name: moduleName,
            id,
            attrs,
        } = param;

        if (moduleName !== 'divi/contact-field') {
            return moduleWrapper;
        }

        const fieldType = attrs?.fieldItem?.advanced?.type?.desktop?.value;
        const fieldId = attrs.fieldItem?.advanced?.id?.desktop?.value || `country_code_${id}`;
        const inputId = `et_pb_contact_${fieldId}_${id}`;
        const storageKey = `editor_${id}`; // Use a stable key for tracking

        if (fieldType !== 'country_code') {
            // Clear any pending timeout for this module
            if (this.timeouts[storageKey]) {
                clearTimeout(this.timeouts[storageKey]);
                delete this.timeouts[storageKey];
            }

            // Aggressive Cleanup
            const helper = window.contactFormExtenderHelper;
            if (helper?.itiInstances?.[storageKey]) {
                try {
                    helper.itiInstances[storageKey].iti.destroy();
                    delete helper.itiInstances[storageKey];
                } catch (e) {
                    console.warn('ITI Sync Cleanup failed', e);
                }
            }
            return moduleWrapper;
        }


        const { createElement, cloneElement } = window.vendor.wp.element;


        const fieldTitle = attrs.fieldItem?.advanced?.title?.desktop?.value || '';
        const requiredMark = attrs.fieldItem?.advanced?.requiredMark?.desktop?.value || 'off';
        const defaultCountry = attrs.fieldItem?.advanced?.cfefdCountryCodeDefault?.desktop?.value || 'in';
        const countryCodeInclude = attrs.fieldItem?.advanced?.cfefdCountryCodeInclude?.desktop?.value || '';
        const countryCodeExclude = attrs.fieldItem?.advanced?.cfefdCountryCodeExclude?.desktop?.value || '';
        const dialCodeVisibility = attrs.fieldItem?.advanced?.cfefdDialCodeVisibility?.desktop?.value || 'show';
        const strictMode = attrs.fieldItem?.advanced?.cfefdStrictMode?.desktop?.value || 'off';

        // Create the input element
        const input = createElement('input', {
            key: 'country-code-input',
            type: 'text',
            className: 'input',
            name: `et_pb_contact_${fieldId}_${id}`,
            id: `et_pb_contact_${fieldId}_${id}`,
            placeholder: fieldTitle,
            readOnly: true,
            'data-required_mark': requiredMark === 'on' ? 'required' : 'optional',
            'data-cfefd-country-code': 'on',
            'data-default-country': defaultCountry,
            'data-include-countries': countryCodeInclude,
            'data-exclude-countries': countryCodeExclude,
            'data-dial-code-visibility': dialCodeVisibility,
            'data-strict-mode': strictMode,
        });

        const existingChildren = Array.isArray(moduleWrapper.props.children)
            ? moduleWrapper.props.children
            : (moduleWrapper.props.children ? [moduleWrapper.props.children] : []);

        const newChildren = [...existingChildren, input];

        if (this.timeouts[storageKey]) {
            clearTimeout(this.timeouts[storageKey]);
        }

        this.timeouts[storageKey] = setTimeout(() => {
            delete this.timeouts[storageKey];
            let itiConig = {
                defaultCountry: defaultCountry,
                includeCountries: countryCodeInclude,
                excludeCountries: countryCodeExclude,
                dialCodeVisibility: dialCodeVisibility,
                strictMode: strictMode
            }
            let inputField = jQuery(`#${inputId}`);
            if (!inputField.length || inputField.attr('data-cfefd-country-code') !== 'on') {
                return;
            }

            if (window.contactFormExtenderHelper) {
                let helper = window.contactFormExtenderHelper
                if (helper.itiInstances && helper.itiInstances[storageKey]) {
                    if (helper.itiInstances[inputId]) {
                        let oldInsance = helper.itiInstances[inputId].iti
                        oldInsance.destroy()
                        delete helper.itiInstances[inputId]
                    }
                    let itiInstance = helper.itiInstances[storageKey].iti
                    this.handleLibReInit(itiInstance, inputField, itiConig, storageKey);
                } else {
                    this.handleLibReInit(null, inputField, itiConig, storageKey);
                }
            }

        }, 500)

        return cloneElement(moduleWrapper, {
            ...moduleWrapper.props,
        }, newChildren);
    }

    isCountryAvailable(iti, iso2) {
        if (!iti || !iso2 || typeof iso2 !== 'string') {
            return false;
        }

        iso2 = iso2.toLowerCase();

        return iti.countries.some(country => country.iso2 === iso2);
    }


    handleLibReInit(itiInstance, inputField, itiConig, storageKey) {
        if (!inputField || !inputField.length) {
            return;
        }

        // Destroy old instance
        try {
            if (itiInstance) {
                itiInstance.destroy();
            }
        } catch (e) {
            console.warn('ITI destroy failed', e);
        }

        const inputId = inputField.attr('id');
        const finalKey = storageKey || inputId;

        // Normalize config values
        let defaultCountry = itiConig.defaultCountry || 'in';

        let includeCountries = itiConig.includeCountries
            ? itiConig.includeCountries.split(',').map(s => s.trim())
            : [];

        let excludeCountries = itiConig.excludeCountries
            ? itiConig.excludeCountries.split(',').map(s => s.trim())
            : [];

        let dialCodeVisibility = itiConig.dialCodeVisibility || 'show';
        let strictMode = itiConig.strictMode === 'on';

        let preferredCountries = inputField.data('preferred-countries')
            ? inputField.data('preferred-countries').split(',').map(s => s.trim())
            : [];

        const utilsPath = CFEDF_Data.pluginUrl + 'assets/lib/js/utils.min.js';

        const fallbackCountries = ['in', 'us', 'gb', 'ru', 'fr', 'de', 'br', 'cn', 'jp', 'it'];

        // Resolve default country priority
        if (includeCountries.length > 0) {
            defaultCountry = includeCountries[0];
        }

        if (excludeCountries.length > 0) {
            const available = fallbackCountries.filter(
                c => !excludeCountries.includes(c)
            );
            if (available.length) {
                defaultCountry = available[0];
            }
        }

        if (itiConig.defaultCountry) {
            defaultCountry = itiConig.defaultCountry;
        }

        const itiOptions = {
            initialCountry: defaultCountry,
            preferredCountries: preferredCountries,
            onlyCountries: includeCountries.length ? includeCountries : [],
            excludeCountries: excludeCountries,
            containerClass: 'cfefd-intl-container',
            strictMode: strictMode,
            separateDialCode: dialCodeVisibility === 'separate',
            utilsScript: utilsPath,
            formatOnDisplay: false,
            formatAsYouType: true,
            autoFormat: false,
            useFullscreenPopup: false,
            autoPlaceholder: 'aggressive',
            customPlaceholder: (placeholder, country) => {
                if (!country || !placeholder) {
                    return 'No country found';
                }

                let value = placeholder;
                if (country.iso2 === 'in') {
                    value = placeholder.replace(/^0+/, '');
                }

                return (
                    dialCodeVisibility === 'separate' ||
                    dialCodeVisibility === 'hide'
                )
                    ? value
                    : `+${country.dialCode} ${value}`;
            }
        };

        // Re-init intl-tel-input
        const iti = window.intlTelInput(inputField[0], itiOptions);

        if (dialCodeVisibility !== 'separate') {
            inputField.css('padding-left', '52px')
        }

        const helper = window.contactFormExtenderHelper || (window.contactFormExtenderHelper = {});
        helper.itiInstances = helper.itiInstances || {};

        helper.itiInstances[finalKey] = {
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
        helper.setInitialCountry(iti, inputField, excludeCountries, defaultCountry);
    }
}

new CFEFD_CountryCode_JS();

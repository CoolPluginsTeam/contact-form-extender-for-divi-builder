class CFEFD_RangeSlider_JS {
    constructor() {
        if (!window.location.href.includes('et_fb=1')) {
            jQuery(window).on('load', this.initlizeSlider.bind(this));
        }

        if (!window.vendor?.wp?.hooks || !window.React) {
            return;
        }

        this.hooks = window.vendor.wp.hooks;
        this.React = window.React;
        this.registerHooks();
    }

    /**
     * Register all filters
     */
    registerHooks() {
        this.hooks.addFilter(
            'divi.moduleLibrary.moduleAttributes.divi.contact-field',
            'cfefd',
            this.addRangeTypeOption.bind(this)
        );

        this.hooks.addFilter(
            'divi.module.wrapper.render',
            'cfefd',
            this.renderRangeSlider.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleLibrary.moduleAttributes.divi.contact-field',
            'cfefd',
            this.extendContactFieldAttributes.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleGroups.groups',
            'cfefd',
            this.addRangeFieldVisibility.bind(this)
        );

        jQuery(window).on('load', this.initlizeSlider.bind(this));
    }

    initlizeSlider() {
        jQuery('.form-range-slider').each(function () {
            let slider = jQuery(this);
            slider.ionRangeSlider({
                skin: 'round',
            });
        });
    }


    extendContactFieldAttributes(attributes) {
        attributes.fieldItemAdvancedRangeMin = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeMin',
                        label: 'Min',
                        description: 'minimum allowed value',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeMax = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeMax',
                        label: 'Max',
                        description: 'maximum allowed value',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeStep = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeStep',
                        label: 'Step',
                        description: 'Incremental step value',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeStartFrom = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeStartFrom',
                        label: 'Start From',
                        description: 'starting value of the range slider',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeStyle = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeStyle',
                        label: 'Range Style',
                        description: 'Style of the range slider',
                        component: {
                            name: 'divi/select',
                            type: 'field',
                            props: {
                                options: {
                                    custom: {
                                        label: "Simple"
                                    },
                                    flat: {
                                        label: "Flat"
                                    },
                                    round: {
                                        label: "Round"
                                    },
                                    square: {
                                        label: "Square"
                                    },
                                    modern: {
                                        label: "modern"
                                    },
                                    sharp: {
                                        label: "Sharp"
                                    },
                                    big: {
                                        label: "Big"
                                    },
                                }
                            }
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeType = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeType',
                        label: 'Range Type',
                        description: 'Type of the range slider',
                        component: {
                            name: 'divi/select',
                            type: 'field',
                            props: {
                                options: {
                                    single: {
                                        label: "Single"
                                    },
                                    double: {
                                        label: "Double"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeBeforeText = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeBeforeText',
                        label: 'Before Text',
                        description: 'Text to show before the slider value',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };
        attributes.fieldItemAdvancedRangeAfterText = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.rangeAfterText',
                        label: 'After Text',
                        description: 'Text to show after the slider value',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        return attributes;
    }


    addRangeFieldVisibility(groups, meta) {
        if (meta?.moduleName !== 'divi/contact-field') return groups;

        const props = groups?.contentFieldOptions?.component?.props?.fields;
        if (!props) return groups;

        const field = props.fieldItemAdvancedRangeMinInnercontent;
        if (!field) return groups;

        field.visible = (props) => {
            const typeValue = props.attrs?.fieldItem?.advanced?.type?.desktop?.value;
            return typeValue === 'range';
        };

        return groups;
    }
    /**
     * Add "Range Slider" option to the module settings (Visual Builder)
     */
    addRangeTypeOption(attributes) {

        const options =
            attributes?.fieldItem?.settings?.advanced?.type?.item?.component?.props?.options;

        if (!options) {
            return attributes;
        }

        attributes.fieldItem.settings.advanced.type.item.component.props.options = {
            ...options,
            range: {
                label: "Range Slider",
            },
        };

        return attributes;
    }

    /**
     * Render the custom "range" field inside the Contact Form module
     */
    renderRangeSlider(moduleWrapper, param) {
        const { name: moduleName, attrs } = param;

        if (moduleName !== "divi/contact-field") {
            return moduleWrapper;
        }

        const fieldType =
            attrs?.fieldItem?.advanced?.type?.desktop?.value || "text";

        if (fieldType !== "range") {
            return moduleWrapper;
        }

        this.handleChangeEvents(param);

        const { createElement, cloneElement } = this.React;

        const fieldId =
            attrs.fieldItem?.advanced?.id?.desktop?.value ||
            `range_${param.id}`;


        const rangeMin = attrs.fieldItem?.advanced?.rangeMin?.desktop?.value ?? 5;
        const rangeMax = attrs.fieldItem?.advanced?.rangeMax?.desktop?.value ?? 100;
        const rangeStep = attrs.fieldItem?.advanced?.rangeStep?.desktop?.value ?? 5;
        const rangeStartFrom = attrs.fieldItem?.advanced?.rangeStartFrom?.desktop?.value ?? 25;
        const rangeStyle = attrs.fieldItem?.advanced?.rangeStyle?.desktop?.value ?? 'round';
        const rangeType = attrs.fieldItem?.advanced?.rangeType?.desktop?.value ?? 'single';
        const rangeBeforeText = attrs.fieldItem?.advanced?.rangeBeforeText?.desktop?.value ?? '';
        const rangeAfterText = attrs.fieldItem?.advanced?.rangeAfterText?.desktop?.value ?? '';

        let dynamicArgs = {
            field_id: `et_pb_contact_${fieldId}_${param.id}`,
            status: `update`,
            args: {
                min: rangeMin,
                max: rangeMax,
                step: rangeStep,
                from: rangeStartFrom,
                skin: rangeStyle,
                type: rangeType,
                prefix: rangeBeforeText,
                postfix: rangeAfterText,
            }
        }
        this.initlizeSlider();
        // Build the range input element
        const rangeInput = createElement("input", {
            key: "range-input",
            type: "text",
            className: "input form-range-slider",

            name: `et_pb_contact_${fieldId}_${param.id}`,
            id: `et_pb_contact_${fieldId}_${param.id}`,

            "data-field_type": "range",
            "data-min": rangeMin,
            "data-max": rangeMax,
            "data-step": rangeStep,
            "data-from": rangeStartFrom,
            "data-skin": rangeStyle,
            "data-type": rangeType,
            "data-prefix": rangeBeforeText,
            "data-postfix": rangeAfterText,
        });

        // Ensure children is an array
        const existingChildren = Array.isArray(moduleWrapper.props.children)
            ? moduleWrapper.props.children
            : [moduleWrapper.props.children];

        const newChildren = [...existingChildren, rangeInput];

        // Clone the wrapper with modified children
        return cloneElement(moduleWrapper, moduleWrapper.props, newChildren);
    }

    handleChangeEvents(param) {
        let wrapperId = param.id;
        let wrapper = jQuery('.et_pb_contact_field_' + wrapperId);
        if (wrapper.attr('data-type') === 'range') {
            let input = wrapper.find('input.form-range-slider')
            if (!input.hasClass('irs-hidden-input')) {
                input.addClass('irs-hidden-input');
            }
        }
    }
}

// Initialize class automatically
new CFEFD_RangeSlider_JS();
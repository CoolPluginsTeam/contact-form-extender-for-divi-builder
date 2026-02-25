class CFEFD_FileUpload_JS {
    constructor() {
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
            this.addFileUploadTypeOption.bind(this)
        );

        this.hooks.addFilter(
            'divi.module.wrapper.render',
            'cfefd',
            this.renderFileUpload.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleGroups.groups',
            'cfefd',
            this.addFileUploadFieldVisibility.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleLibrary.moduleSettings.groups.divi.contact-form',
            'cfefd',
            this.addContactFormGroups.bind(this)
        );

        this.hooks.addFilter(
            'divi.moduleLibrary.moduleAttributes.divi.contact-form',
            'cfefd',
            this.extendContactFormAttributes.bind(this)
        );

        this.hooks.addFilter(
            'divi.module.wrapper.render',
            'cfefd',
            this.renderFileUploadAdditionalStyles.bind(this)
        );

        // D4 to D5 Migration - Contact Field
        // this.hooks.addFilter(
        //     'divi.moduleLibrary.conversion.moduleConversionOutline',
        //     'cfefd',
        //     this.addContactFieldConversion.bind(this)
        // );

        // // D4 to D5 Migration - Contact Form
        // this.hooks.addFilter(
        //     'divi.moduleLibrary.conversion.moduleConversionOutline',
        //     'cfefd',
        //     this.addContactFormConversion.bind(this)
        // );

        // // D4 to D5 Migration - Custom attribute conversions for complex fields
        // this.hooks.addFilter(
        //     'divi.moduleLibrary.conversion.convertModuleAttribute',
        //     'cfefd',
        //     this.convertComplexAttributes.bind(this),
        //     10,
        //     4
        // );

    }

    decodeIconUnicode(value) {
        if (!value) return value;

        const textarea = document.createElement('textarea');
        textarea.innerHTML = value;
        return textarea.value;
    }

    normalizeAttr(value) {
        if (!value) {
            return null;
        }
        // Already a Divi attribute object
        if (typeof value === 'object' && value.desktop) {
            return value;
        }
        // Convert primitive to Divi attr structure
        return {
            desktop: {
                value: value,
            },
        };
    }


    renderFileUploadAdditionalStyles(moduleWrapper, param) {
        const {
            name: moduleName,
            attrs,
            elements: moduleElements,
            state: moduleState,
        } = param;

        // Only target Contact Form module
        if (moduleName !== 'divi/contact-form') {
            return moduleWrapper;
        }

        const {
            isEdited,
        } = moduleWrapper.props;

        const containerSelector = `${moduleElements.orderClass} .cfefd_files_container`;
        const acceptDescSelector = `${moduleElements.orderClass} .cfefd_accepted_files_desc`;
        const chosenDescSelector = `${moduleElements.orderClass} .cfefd_file_chosen_desc`;
        const buttonSelector = `${moduleElements.orderClass} .cfefd_file_upload_button`;
        const listSelector = `${moduleElements.orderClass} .cfefd_files_list span`;
        const listLinkSelector = `${moduleElements.orderClass} .cfefd_files_list span a`;


        const d = attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value;

        const acceptedTextColorAttr = this.normalizeAttr(d?.acceptedTextColor);
        const acceptedTextSizeAttr = this.normalizeAttr(d?.acceptedTextSize);
        const acceptedTextFontAttr = this.normalizeAttr(d?.acceptedTextFont);

        const chosenFileTextColorAttr = this.normalizeAttr(d?.fileChoosenTextColor);

        const buttonBgAttr = this.normalizeAttr(d?.buttonBg);
        const buttonColorAttr = this.normalizeAttr(d?.buttonColor);
        const buttonTextSizeAttr = this.normalizeAttr(d?.buttonTextSize);
        const buttonTextFontAttr = this.normalizeAttr(d?.buttonTextFont);
        const buttonMarginTopAttr = this.normalizeAttr(d?.buttonMarginTop);
        const buttonMarginRightAttr = this.normalizeAttr(d?.buttonMarginRight);
        const buttonMarginBottomAttr = this.normalizeAttr(d?.buttonMarginBottom);
        const buttonMarginLeftAttr = this.normalizeAttr(d?.buttonMarginLeft);
        const buttonPaddingTopAttr = this.normalizeAttr(d?.buttonPaddingTop);
        const buttonPaddingRightAttr = this.normalizeAttr(d?.buttonPaddingRight);
        const buttonPaddingBottomAttr = this.normalizeAttr(d?.buttonPaddingBottom);
        const buttonPaddingLeftAttr = this.normalizeAttr(d?.buttonPaddingLeft);
        const buttonBorderTopLeftRadiusAttr = this.normalizeAttr(d?.buttonBorderTopLeftRadius);
        const buttonBorderTopRightRadiusAttr = this.normalizeAttr(d?.buttonBorderTopRightRadius);
        const buttonBorderBottomLeftRadiusAttr = this.normalizeAttr(d?.buttonBorderBottomLeftRadius);
        const buttonBorderBottomRightRadiusAttr = this.normalizeAttr(d?.buttonBorderBottomRightRadius);
        const buttonBorderColorAttr = this.normalizeAttr(d?.buttonBorderColor);
        const buttonBorderWidthAttr = this.normalizeAttr(d?.buttonBorderWidth);

        const containerBgAttr = this.normalizeAttr(d?.containerBackground);
        const containerMarginAttr = this.normalizeAttr(d?.containerMargin);
        const containerPaddingTopAttr = this.normalizeAttr(d?.containerPaddingTop);
        const containerPaddingRightAttr = this.normalizeAttr(d?.containerPaddingRight);
        const containerPaddingBottomAttr = this.normalizeAttr(d?.containerPaddingBottom);
        const containerPaddingLeftAttr = this.normalizeAttr(d?.containerPaddingLeft);

        const containerBorderTopLeftRadiusAttr = this.normalizeAttr(d?.containerBorderTopLeftRadius);
        const containerBorderTopRightRadiusAttr = this.normalizeAttr(d?.containerBorderTopRightRadius);
        const containerBorderBottomLeftRadiusAttr = this.normalizeAttr(d?.containerBorderBottomLeftRadius);
        const containerBorderBottomRightRadiusAttr = this.normalizeAttr(d?.containerBorderBottomRightRadius);
        const containerBorderColorAttr = this.normalizeAttr(d?.containerBorderColor);
        const containerBorderWidthAttr = this.normalizeAttr(d?.containerBorderWidth);
        const containerBorderStyleAttr = this.normalizeAttr(d?.containerBorderStyle);


        const { createElement } = window.vendor.wp.element;
        const { StyleContainer } = window.divi.module;

        const styles = createElement(
            StyleContainer,
            {
                key: 'cfefd-file-upload-styles',
                mode: 'builder',
                state: isEdited ? moduleState : '',
                noStyleTag: false,
            },
            moduleElements.style({
                attrName: 'cfefdFileUploadDesignTabs',
                styleProps: {
                    advancedStyles: [
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'background-color',
                                attr: containerBgAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'margin',
                                attr: containerMarginAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: `${moduleElements.orderClass} form.et_pb_contact_form .cfefd_files_container`,
                                property: 'padding-top',
                                attr: containerPaddingTopAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: `${moduleElements.orderClass} form.et_pb_contact_form .cfefd_files_container`,
                                property: 'padding-right',
                                attr: containerPaddingRightAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: `${moduleElements.orderClass} form.et_pb_contact_form .cfefd_files_container`,
                                property: 'padding-bottom',
                                attr: containerPaddingBottomAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: `${moduleElements.orderClass} form.et_pb_contact_form .cfefd_files_container`,
                                property: 'padding-left',
                                attr: containerPaddingLeftAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-top-left-radius',
                                attr: containerBorderTopLeftRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-top-right-radius',
                                attr: containerBorderTopRightRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-bottom-left-radius',
                                attr: containerBorderBottomLeftRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-bottom-right-radius',
                                attr: containerBorderBottomRightRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-color',
                                attr: containerBorderColorAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-width',
                                attr: containerBorderWidthAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: containerSelector,
                                property: 'border-style',
                                attr: containerBorderStyleAttr,
                            },
                        },

                        /* ======================
                        FILE LIST STYLES
                        ====================== */

                        {
                            componentName: 'divi/common',
                            props: {
                                selector: listLinkSelector,
                                property: 'color',
                                attr: this.normalizeAttr(d?.containerListColor),
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: listSelector,
                                property: 'background-color',
                                attr: this.normalizeAttr(d?.containerListBg),
                            },
                        },

                        /* ======================
                        DESCRIPTION STYLES
                        ====================== */

                        {
                            componentName: 'divi/common',
                            props: {
                                selector: acceptDescSelector,
                                property: 'color',
                                attr: acceptedTextColorAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: acceptDescSelector,
                                property: 'font-size',
                                attr: acceptedTextSizeAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: acceptDescSelector,
                                property: 'font-family',
                                attr: acceptedTextFontAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: chosenDescSelector,
                                property: 'color',
                                attr: chosenFileTextColorAttr,
                            },
                        },

                        /* ======================
                        BUTTON STYLES
                        ====================== */

                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'background-color',
                                attr: buttonBgAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'color',
                                attr: buttonColorAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'font-size',
                                attr: buttonTextSizeAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'font-family',
                                attr: buttonTextFontAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'margin-top',
                                attr: buttonMarginTopAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'margin-right',
                                attr: buttonMarginRightAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'margin-bottom',
                                attr: buttonMarginBottomAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'margin-left',
                                attr: buttonMarginLeftAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'padding-top',
                                attr: buttonPaddingTopAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'padding-right',
                                attr: buttonPaddingRightAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'padding-bottom',
                                attr: buttonPaddingBottomAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'padding-left',
                                attr: buttonPaddingLeftAttr,
                            },
                        },
                        // button border radius
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-top-left-radius',
                                attr: buttonBorderTopLeftRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-top-right-radius',
                                attr: buttonBorderTopRightRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-bottom-left-radius',
                                attr: buttonBorderBottomLeftRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-bottom-right-radius',
                                attr: buttonBorderBottomRightRadiusAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-color',
                                attr: buttonBorderColorAttr,
                            },
                        },
                        {
                            componentName: 'divi/common',
                            props: {
                                selector: buttonSelector,
                                property: 'border-width',
                                attr: buttonBorderWidthAttr,
                            },
                        },
                    ],
                },
            })
        );

        return window.vendor.wp.element.cloneElement(
            moduleWrapper,
            {},
            moduleWrapper.props.children,
            [
                styles,
            ]
        );
    }

    /**
     * Extend Contact Form attributes with file upload design settings
     */
    extendContactFormAttributes(attributes) {

        attributes.cfefdFileUploadDesignTabs = {
            type: 'object',
            default: {
                innerContent: {
                    desktop: {
                        value: {
                            fileuploadTabs: 'container',
                            /* Container defaults */
                            containerBackground: '#eee',

                            containerPaddingTop: '20px',
                            containerPaddingRight: '20px',
                            containerPaddingBottom: '0px',
                            containerPaddingLeft: '20px',

                            containerBorderColor: '',
                            containerBorderWidth: '',
                            containerBorderStyle: 'solid',
                            containerBorderTopLeftRadius: '0px',
                            containerBorderTopRightRadius: '0px',
                            containerBorderBottomLeftRadius: '0px',
                            containerBorderBottomRightRadius: '0px',
                            containerShadow: 'none',
                            attachedListColor: '#ffffff',
                            attachedListBackgroundColor: '#1b1818ff',

                            /* Description defaults */
                            acceptedTextColor: '#999999ff',
                            acceptedTextSize: '20px',
                            chosenFileTextColor: '#999',

                            /* Button defaults */
                            buttonBg: '',
                            buttonColor: '#2ea3f2',
                            buttonSize: '20px',
                            buttonBorderColor: '#2ea3f2',
                            buttonBorderWidth: '2px',
                            buttonPaddingTop: '6px',
                            buttonPaddingRight: '20px',
                            buttonPaddingBottom: '6px',
                            buttonPaddingLeft: '20px',
                            buttonBorderTopLeftRadius: '3px',
                            buttonBorderTopRightRadius: '3px',
                            buttonBorderBottomLeftRadius: '3px',
                            buttonBorderBottomRightRadius: '3px',
                        }
                    }
                }
            },

            settings: {
                innerContent: {
                    groupType: 'group-items',
                    items: {
                        /* =======================
                        TAB CONTROLLER
                        ======================= */
                        fileuploadTabs: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'fileuploadTabs',
                            label: 'File Upload Design',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 10,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/button-options',
                                props: {
                                    tabUi: true,
                                    options: {
                                        container: { label: 'Container' },
                                        descriptions: { label: 'Descriptions' },
                                        button: { label: 'Button' }
                                    }
                                }
                            }
                        },

                        /* =======================
                        CONTAINER FIELDS
                        ======================= */

                        containerBackground: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBackground',
                            label: 'Container Background Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 20,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        // container padding
                        containerPaddingTop: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerPaddingTop',
                            label: 'Container Padding Top',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 21,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        containerPaddingRight: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerPaddingRight',
                            label: 'Container Padding Right',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 22,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        containerPaddingBottom: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerPaddingBottom',
                            label: 'Container Padding Bottom',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 23,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        containerPaddingLeft: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerPaddingLeft',
                            label: 'Container Padding Left',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 24,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        containerBorderColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderColor',
                            label: 'Container Border Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 24,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        containerBorderWidth: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderWidth',
                            label: 'Container Border Width',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 25,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        containerBorderStyle: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderStyle',
                            label: 'Container Border Style',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 26,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/select',
                                props: {
                                    options: {
                                        solid: { label: 'Solid' },
                                        dashed: { label: 'Dashed' },
                                        dotted: { label: 'Dotted' },
                                        double: { label: 'Double' },
                                        groove: { label: 'Groove' },
                                        ridge: { label: 'Ridge' },
                                        inset: { label: 'Inset' },
                                        outset: { label: 'Outset' },
                                        none: { label: 'None' }
                                    }
                                }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        // container border radius
                        containerBorderTopLeftRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderTopLeftRadius',
                            label: 'Container Border Top Left Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 27,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        containerBorderTopRightRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderTopRightRadius',
                            label: 'Container Border Top Right Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 28,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        containerBorderBottomLeftRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderBottomLeftRadius',
                            label: 'Container Border Bottom Left Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 29,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        containerBorderBottomRightRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'containerBorderBottomRightRadius',
                            label: 'Container Border Bottom Right Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 30,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        attachedListColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'attachedListColor',
                            label: 'Attached List Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 40,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },
                        attachedListBackgroundColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'attachedListBackgroundColor',
                            label: 'Attached List Background Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 40,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) => {
                                const activeTab =
                                    attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs
                                    ?? 'container';

                                return activeTab === 'container';
                            }
                        },

                        /* =======================
                        DESCRIPTION FIELDS
                        ======================= */

                        acceptedTextColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'acceptedTextColor',
                            label: 'Accepted File Types Text Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 40,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'descriptions'
                        },

                        acceptedTextSize: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'acceptedTextSize',
                            label: 'Accepted File Types Text Size',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 41,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 1, max: 100, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'descriptions'
                        },
                        acceptedTextFont: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'acceptedTextFont',
                            label: 'Accepted File Types Text Font',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 42,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/select-font',
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'descriptions'
                        },
                        fileChoosenTextColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'fileChoosenTextColor',
                            label: 'File Choosen Text Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 42,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/color-picker',
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'descriptions'
                        },

                        /* =======================
                        BUTTON FIELDS
                        ======================= */

                        buttonBg: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBg',
                            label: 'Button Background Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 60,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },

                        buttonColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonColor',
                            label: 'Button Text Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 61,
                            render: true,
                            component: { type: 'field', name: 'divi/color-picker' },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonTextFont: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonTextFont',
                            label: 'Button Text Font',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 62,
                            render: true,
                            component: { type: 'field', name: 'divi/select-font' },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonTextSize: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonTextSize',
                            label: 'Button Text Size',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 63,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 1, max: 100, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        // button margin
                        buttonMarginTop: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonMarginTop',
                            label: 'Button Margin Top',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 64,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonMarginRight: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonMarginRight',
                            label: 'Button Margin Right',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 65,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonMarginBottom: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonMarginBottom',
                            label: 'Button Margin Bottom',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 66,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonMarginLeft: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonMarginLeft',
                            label: 'Button Margin Left',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 67,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonBorderColor: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBorderColor',
                            label: 'Button Border Color',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 68,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/color-picker',
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },

                        // button padding
                        buttonPaddingTop: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonPaddingTop',
                            label: 'Button Padding Top',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 69,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonPaddingRight: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonPaddingRight',
                            label: 'Button Padding Right',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 70,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonPaddingBottom: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonPaddingBottom',
                            label: 'Button Padding Bottom',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 71,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonPaddingLeft: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonPaddingLeft',
                            label: 'Button Padding Left',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 72,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 50, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        // button border radius
                        buttonBorderTopLeftRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBorderTopLeftRadius',
                            label: 'Button Border Top Left Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 73,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonBorderTopRightRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBorderTopRightRadius',
                            label: 'Button Border Top Right Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 74,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonBorderBottomLeftRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBorderBottomLeftRadius',
                            label: 'Button Border Bottom Left Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 75,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                        buttonBorderBottomRightRadius: {
                            attrName: 'cfefdFileUploadDesignTabs.innerContent',
                            subName: 'buttonBorderBottomRightRadius',
                            label: 'Button Border Bottom Right Radius',
                            groupSlug: 'cfefdFileUploadDesign',
                            priority: 76,
                            render: true,
                            component: {
                                type: 'field',
                                name: 'divi/range',
                                props: { min: 0, max: 30, step: 1, unit: 'px' }
                            },
                            visible: ({ attrs }) =>
                                attrs?.cfefdFileUploadDesignTabs?.innerContent?.desktop?.value?.fileuploadTabs === 'button'
                        },
                    }
                }
            }
        };

        return attributes;
    }

    /**
     * Add Contact Form groups (toggles)
     */
    addContactFormGroups(groups, meta) {
        if (meta?.name !== 'divi/contact-form') return groups;

        // Parent panel label only (visual grouping)
        groups.cfefdFileUploadDesign = {
            panel: 'design',
            priority: 20,
            multiElements: true,
            groupName: 'cfefdFileUploadDesign',
            component: {
                name: 'divi/composite',
                props: {
                    groupLabel: 'File Upload Design',
                },
            },
        };

        return groups;
    }

    /**
     * Add field visibility controls
     */
    addFileUploadFieldVisibility(groups, meta) {
        if (meta?.moduleName !== 'divi/contact-field') return groups;

        const props = groups?.contentFieldOptions?.component?.props?.fields;
        if (!props) return groups;

        // Show file upload fields only when type is 'file_upload'
        const fileUploadFields = [
            'fieldItemAdvancedFileUploadMaxSizeInnercontent',
            'fieldItemAdvancedFileUploadAllowedTypesInnercontent',
            'fieldItemAdvancedFileUploadMaxFilesInnercontent',
            'fieldItemAdvancedFileUploadUseButtonIconInnercontent',
            'fieldItemAdvancedFileUploadButtonIconInnercontent'
        ];

        fileUploadFields.forEach(fieldName => {
            const field = props[fieldName];
            if (field) {
                field.visible = (props) => {
                    const typeValue = props.attrs?.fieldItem?.advanced?.type?.desktop?.value;
                    return typeValue === 'file_upload';
                };
            }
        });

        // Show button icon field only when use button icon is 'on'
        const buttonIconField = props.fieldItemAdvancedFileUploadButtonIconInnercontent;
        if (buttonIconField) {
            buttonIconField.visible = (props) => {
                const typeValue = props.attrs?.fieldItem?.advanced?.type?.desktop?.value;
                const useIcon = props.attrs?.fieldItem?.advanced?.fileUploadUseButtonIcon?.desktop?.value;
                return typeValue === 'file_upload' && useIcon === 'on';
            };
        }

        return groups;
    }

    /**
     * Add "File Upload" option to the field type dropdown
     */
    addFileUploadTypeOption(attributes) {
        const options = attributes?.fieldItem?.settings?.advanced?.type?.item?.component?.props?.options;

        if (!options) {
            return attributes;
        }

        attributes.fieldItem.settings.advanced.type.item.component.props.options = {
            ...options,
            file_upload: {
                label: "File Upload",
            },
        };

        // Max File Size
        attributes.fieldItemAdvancedFileUploadMaxSize = {
            type: 'string',
            default: '1024',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 40,
                        render: true,
                        attrName: 'fieldItem.advanced.fileUploadMaxSize',
                        label: 'Max File Size (KB)',
                        description: 'Maximum allowed file size in kilobytes',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Allowed File Types
        attributes.fieldItemAdvancedFileUploadAllowedTypes = {
            type: 'string',
            default: '.jpg,.png',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 41,
                        render: true,
                        attrName: 'fieldItem.advanced.fileUploadAllowedTypes',
                        label: 'Allowed File Types',
                        description: 'Comma-separated file extensions (e.g., jpg, png, pdf)',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Max Number of Files
        attributes.fieldItemAdvancedFileUploadMaxFiles = {
            type: 'string',
            default: '2',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 42,
                        render: true,
                        attrName: 'fieldItem.advanced.fileUploadMaxFiles',
                        label: 'Max Number of Files',
                        description: 'Maximum number of files that can be uploaded',
                        component: {
                            name: 'divi/text',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Use Button Icon
        attributes.fieldItemAdvancedFileUploadUseButtonIcon = {
            type: 'string',
            default: 'on',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 43,
                        render: true,
                        attrName: 'fieldItem.advanced.fileUploadUseButtonIcon',
                        label: 'Show Button Icon',
                        description: 'Display an icon on the upload button',
                        component: {
                            name: 'divi/toggle',
                            type: 'field'
                        }
                    }
                }
            }
        };

        // Button Icon
        attributes.fieldItemAdvancedFileUploadButtonIcon = {
            type: 'string',
            default: '',
            settings: {
                innerContent: {
                    groupType: 'group-item',
                    item: {
                        groupSlug: 'contentFieldOptions',
                        priority: 44,
                        render: true,
                        attrName: 'fieldItem.advanced.fileUploadButtonIcon',
                        label: 'Button Icon',
                        description: 'Select an icon for the upload button',
                        component: {
                            name: 'divi/icon-picker',
                            type: 'field'
                        }
                    }
                }
            }
        };

        return attributes;
    }

    /**
     * Render the file upload field in Visual Builder
     */
    renderFileUpload(moduleWrapper, param) {
        const { name: moduleName, attrs, elements: moduleElements, state: moduleState } = param;

        if (moduleName !== "divi/contact-field") {
            return moduleWrapper;
        }

        const fieldType = attrs?.fieldItem?.advanced?.type?.desktop?.value || "text";

        if (fieldType !== "file_upload") {
            return moduleWrapper;
        }

        const { createElement, cloneElement } = window.vendor.wp.element;
        const { StyleContainer } = window.divi.module;


        const fieldId = attrs.fieldItem?.advanced?.id?.desktop?.value || `file_upload_${param.id}`;
        const maxSize = attrs.fieldItem?.advanced?.fileUploadMaxSize?.desktop?.value ?? '1024';

        const allowedTypes = attrs.fieldItem?.advanced?.fileUploadAllowedTypes?.desktop?.value ?? '.jpg,.png';
        const maxFiles = attrs.fieldItem?.advanced?.fileUploadMaxFiles?.desktop?.value ?? '2';
        const useButtonIcon = attrs.fieldItem?.advanced?.fileUploadUseButtonIcon?.desktop?.value ?? 'on';
        const buttonIcon = attrs.fieldItem?.advanced?.fileUploadButtonIcon?.desktop?.value ?? '';

        // Calculate file size
        const fileSizeKB = parseInt(maxSize) || 1024;
        const fileSizeBytes = fileSizeKB * 1024;
        const fileSizeFormatted = this.formatFileSize(fileSizeBytes);

        // Create file description
        const fileDesc = `Accepted file types: ${allowedTypes}. Max. file size: ${fileSizeFormatted}`;

        // Build the file upload UI elements
        const elements = [];

        // 1. Hidden input for storing file names
        elements.push(createElement("input", {
            key: "hidden-files",
            type: "text",
            className: "input cfefd_contact_hidden_files cool_hidden_original",
            name: `et_pb_contact_${fieldId}_${param.id}`,
            id: `et_pb_contact_${fieldId}_${param.id}`,
            readOnly: true,
            "data-field-id": fieldId,
        }));

        // 2. File input (hidden)
        elements.push(createElement("input", {
            key: "file-input",
            type: "file",
            className: "input cfefd_file_input",
            id: `et_pb_file_input_${fieldId}`,
            "data-field-id": fieldId,
            "data-size": fileSizeBytes,
            "data-size-formatted": fileSizeFormatted,
            "data-limit": maxFiles,
            multiple: parseInt(maxFiles) > 1,
        }));

        // 3. Upload button
        const buttonClass = useButtonIcon === 'on' && buttonIcon ?
            'cfefd_file_upload_button et_pb_button et_pb_icon' :
            'cfefd_file_upload_button et_pb_button';

        let decodedIcon = '5';
        if (useButtonIcon === 'on' && buttonIcon) {
            decodedIcon = this.decodeIconUnicode(buttonIcon?.unicode);
        }

        elements.push(createElement("span", {
            key: "upload-button",
            className: buttonClass,
            role: "button",
            'data-icon': decodedIcon,
        }, "Choose Files"));

        const buttonSelector =
            `${moduleElements.orderClass} .cfefd_file_upload_button`;


        let fontFamily = 'FontAwesome';
        let fontWeight = buttonIcon.weight;

        if (buttonIcon.type && buttonIcon.type === 'fa') {
            fontFamily = 'FontAwesome';
            fontWeight = buttonIcon.weight;
        } else {
            fontFamily = 'ETmodules';
            fontWeight = buttonIcon.weight;
        }

        const iconStyle = [
            {
                componentName: 'divi/common',
                props: {
                    selector: `${buttonSelector}.et_pb_icon:before, ${buttonSelector}.et_pb_icon:after`,
                    attr: this.normalizeAttr(fontFamily),
                    property: 'font-family',
                    important: true,
                },
            },
            {
                componentName: 'divi/common',
                props: {
                    selector: `${buttonSelector}.et_pb_icon:before, ${buttonSelector}.et_pb_icon:after`,
                    attr: this.normalizeAttr(fontWeight),
                    property: 'font-weight',
                    important: true,
                },
            }
        ];

        const iconStyles = createElement(
            StyleContainer,
            {
                key: 'cfefd-file-upload-icon-styles',
                mode: 'builder',
                state: moduleState,
                noStyleTag: false,
            },
            moduleElements.style({
                attrName: 'fieldItem.advanced.fileUploadButtonIcon',
                styleProps: {
                    advancedStyles: iconStyle,
                },
            })
        );

        elements.push(iconStyles);
        // 4. Chosen file description
        elements.push(createElement("span", {
            key: "chosen-desc",
            className: "cfefd_file_chosen_desc",
        }, "No file chosen"));

        // 5. File description
        elements.push(createElement("span", {
            key: "file-desc",
            id: `cfefd_accepted_files_desc_${fieldId}`,
            className: "cfefd_accepted_files_desc",
            "data-description": fileDesc,
        }, fileDesc));

        // 6. Files list container
        elements.push(createElement("span", {
            key: "files-list",
            id: `cfefd_files_list_${fieldId}`,
            className: "cfefd_files_list",
        }));

        // Ensure children is an array
        const existingChildren = Array.isArray(moduleWrapper.props.children)
            ? moduleWrapper.props.children
            : [moduleWrapper.props.children];

        const newChildren = [...existingChildren, ...elements];

        // Clone the wrapper with modified children and add container class
        const newProps = {
            ...moduleWrapper.props,
            classname: `${moduleWrapper.props.classname || ''} cfefd_files_container`.trim(),
        };

        return cloneElement(moduleWrapper, newProps, newChildren);
    }

    /**
     * Format file size in human-readable format
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * D4 to D5 Migration - Contact Field Conversion
     */
    addContactFieldConversion(conversionOutline, moduleName) {
        if (moduleName !== 'et_pb_contact_field') {
            return conversionOutline;
        }

        // Map D4 field names to D5 attribute paths
        conversionOutline.module = conversionOutline.module || {};

        // Include the toggle field for migration
        conversionOutline.module['cfefd_use_as_file_upload'] = 'fieldItem.advanced.useAsFileUpload.*';
        conversionOutline.module['cfefd_fileupload_max_size'] = 'fieldItem.advanced.fileUploadMaxSize.*';
        conversionOutline.module['cfefd_fileupload_allowed_types'] = 'fieldItem.advanced.fileUploadAllowedTypes.*';
        conversionOutline.module['cfefd_fileupload_max_files'] = 'fieldItem.advanced.fileUploadMaxFiles.*';
        conversionOutline.module['cfefd_use_file_button_icon'] = 'fieldItem.advanced.fileUploadUseButtonIcon.*';
        conversionOutline.module['cfefd_file_button_icon'] = 'fieldItem.advanced.fileUploadButtonIcon.*';

        return conversionOutline;
    }

    /**
     * D4 to D5 Migration - Contact Form Conversion
     */
    addContactFormConversion(conversionOutline, moduleName) {
        if (moduleName !== 'et_pb_contact_form') {
            return conversionOutline;
        }

        // console.log(' second method ')
        // console.log(' conversionOutline ', conversionOutline)
        // console.log('moduleName ', moduleName)

        conversionOutline.module = conversionOutline.module || {};

        // Container settings
        conversionOutline.module['cfefd_files_container_background'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBackground';
        conversionOutline.module['cfefd_files_container_border_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderColor';
        conversionOutline.module['cfefd_files_container_border_width'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderWidth';
        conversionOutline.module['cfefd_files_container_border_style'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerBorderStyle';
        conversionOutline.module['cfefd_files_container_list_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerListColor';
        conversionOutline.module['cfefd_files_container_list_background_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.containerListBg';

        // Description settings
        conversionOutline.module['cfefd_accepted_file_text_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextColor';
        conversionOutline.module['cfefd_accepted_file_text_size'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextSize';
        conversionOutline.module['cfefd_accepted_file_text_font'] = 'cfefdFileUploadDesignTabs.innerContent.*.acceptedTextFont';
        conversionOutline.module['cfefd_chosen_file_text_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.fileChoosenTextColor';

        // Button settings
        conversionOutline.module['cfefd_file_button_background'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonBg';
        conversionOutline.module['cfefd_file_button_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonColor';
        conversionOutline.module['cfefd_file_button_font'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonTextFont';
        conversionOutline.module['cfefd_file_button_size'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonTextSize';
        conversionOutline.module['cfefd_file_button_border_color'] = 'cfefdFileUploadDesignTabs.innerContent.*.buttonBorderColor';

        return conversionOutline;
    }

    /**
     * D4 to D5 Migration - Convert complex attributes
     * Handles conversion of pipe-separated values to individual properties
     */
    convertComplexAttributes(d5Value, d4Value, d4AttrName, moduleName) {
        // Only process for contact form module
        if (moduleName !== 'et_pb_contact_form') {
            return d5Value;
        }

        // Helper function to split pipe-separated values
        const splitPipedValue = (value) => {
            if (!value || typeof value !== 'string') return null;
            const parts = value.split('|');
            return parts.length === 4 ? parts : null;
        };

        // Container Padding: "20px|20px|0px|20px" → individual properties
        if (d4AttrName === 'cfefd_files_container_padding') {
            const parts = splitPipedValue(d4Value);
            if (parts) {
                return {
                    containerPaddingTop: parts[0],
                    containerPaddingRight: parts[1],
                    containerPaddingBottom: parts[2],
                    containerPaddingLeft: parts[3]
                };
            }
        }

        // Container Border Radius: "3px|3px|3px|3px" → individual properties
        if (d4AttrName === 'cfefd_files_container_border') {
            const parts = splitPipedValue(d4Value);
            if (parts) {
                return {
                    containerBorderTopLeftRadius: parts[0],
                    containerBorderTopRightRadius: parts[1],
                    containerBorderBottomRightRadius: parts[2],
                    containerBorderBottomLeftRadius: parts[3]
                };
            }
        }

        // Button Margin: "0px|0px|0px|0px" → individual properties
        if (d4AttrName === 'cfefd_file_button_margin') {
            const parts = splitPipedValue(d4Value);
            if (parts) {
                return {
                    buttonMarginTop: parts[0],
                    buttonMarginRight: parts[1],
                    buttonMarginBottom: parts[2],
                    buttonMarginLeft: parts[3]
                };
            }
        }

        // Button Padding: "6px|20px|6px|20px" → individual properties
        if (d4AttrName === 'cfefd_file_button_padding') {
            const parts = splitPipedValue(d4Value);
            if (parts) {
                return {
                    buttonPaddingTop: parts[0],
                    buttonPaddingRight: parts[1],
                    buttonPaddingBottom: parts[2],
                    buttonPaddingLeft: parts[3]
                };
            }
        }

        // Button Border Radius: "3px|3px|3px|3px" → individual properties
        if (d4AttrName === 'cfefd_file_button_border') {
            const parts = splitPipedValue(d4Value);
            if (parts) {
                return {
                    buttonBorderTopLeftRadius: parts[0],
                    buttonBorderTopRightRadius: parts[1],
                    buttonBorderBottomRightRadius: parts[2],
                    buttonBorderBottomLeftRadius: parts[3]
                };
            }
        }

        return d5Value;
    }
}

// Initialize class automatically
new CFEFD_FileUpload_JS();


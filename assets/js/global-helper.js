class CFEFD_Global_Helper {
	constructor() {
		this.pricingUrl =
			'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=pricing&utm_content=divi_editor#pricing';
		/** Divi 5: append-only Pro teaser rows (must match real field type values where applicable). */
		this.d5ProFieldRows = [
			{ value: 'range', label: 'Range Slider (Pro)' },
			{ value: 'date_picker', label: 'Date Picker (Pro)' },
			{ value: 'signature', label: 'Signature (Pro)' },
			{ value: 'toggle', label: 'Toggle (Pro)' },
			{ value: 'image_radio', label: 'Image Radio (Pro)' },
			{ value: 'calculator', label: 'Calculator (Pro)' },
			{ value: 'select', label: 'Select2 (Pro)' },
			{ value: 'rating', label: 'Rating (Pro)' },
			{ value: 'currency', label: 'Currency (Pro)' },
			{ value: 'wysiwyg', label: 'WYSIWYG (Pro)' },
		];
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

		// Divi 5: field type dropdown — append Pro-only options at end of list
		jQuery(document).on('pointerdown', '#et-vb-fieldItem-advanced-type', this.handleD5FieldTypeOpen.bind(this));

		// Divi 5: Pro rows — redirect to pricing (capture so Divi does not change selection)
		jQuery(document).on(
			'click mousedown',
			'li.select-option-item[data-cfefd-pro-locked="true"]',
			this.handleD5ProOptionActivate.bind(this)
		);
	}

	/**
	 * When Divi 5 field type control opens, append Pro teaser options to the visible list.
	 *
	 * @param {jQuery.Event} e
	 */
	handleD5FieldTypeOpen(e) {
		const current = jQuery(e.currentTarget);
		const delayMs = 200;

		setTimeout(() => {
			const $ul = current.find('li.et-vb-settings-custom-select-wrapper-inner ul').first();
			if (!$ul.length) {
				return;
			}

			this.appendD5ProFieldTypeOptions($ul);
		}, delayMs);
	}

	/**
	 * @param {JQuery} $ul
	 */
	appendD5ProFieldTypeOptions($ul) {
		if ($ul.data('cfefdProAppended')) {
			return;
		}

		this.d5ProFieldRows.forEach((row) => {
			const slug = row.value.replace(/[^a-z0-9_-]/gi, '_');
			const exists = $ul.find(`li.select-option-item[data-cfefd-pro-locked="true"][data-value="${row.value}"]`).length;
			if (exists) {
				return;
			}

			const $li = jQuery(
				'<li/>',
				{
					class: `select-option-item cfefd-pro-field-option select-option-item-${slug}`,
					role: 'option',
					'aria-selected': 'false',
					'aria-disabled': 'true',
					'data-value': row.value,
					'data-cfefd-pro-locked': 'true',
				}
			);
			$li.append(
				jQuery('<span/>', { class: 'select-option-item__name', text: row.label })
			);
			$ul.append($li);
		});

		$ul.data('cfefdProAppended', true);
	}

	/**
	 * @param {jQuery.Event} e
	 */
	handleD5ProOptionActivate(e) {
		const li = e.currentTarget;
		if (!li || !li.getAttribute || !li.getAttribute('data-value')) {
			return;
		}
		e.preventDefault();
		e.stopImmediatePropagation();
		window.open(this.pricingUrl, '_blank', 'noopener,noreferrer');
	}

	addClassOnToggle(e) {
		const current = jQuery(e.currentTarget);
		setTimeout(() => {
			let fileUploadToggle = current.find('.et-fb-form__group input[name="cfefd_use_as_file_upload"]');
			fileUploadToggle.closest('.et-fb-option-container').addClass('should_not_hide_warning');

			let countyCodeToggle = current.find('.et-fb-form__group input[name="cfefd_use_as_country_code"]');
			countyCodeToggle.closest('.et-fb-option-container').addClass('should_not_hide_warning');

			this.injectProUpgradeNotice(current);
		}, 1000);
	}

	injectProUpgradeNotice(current) {
		const fieldSelect = current.find('.et-fb-form__group').first();
		const settingsOptions = fieldSelect.find('.et-fb-settings-options').first();

		if (!settingsOptions.length) {
			return;
		}

		if (fieldSelect.find('.cfefd-d4-pro-notice').length) {
			return;
		}

		const noticeHtml = `
            <div class="cfefd-d4-pro-notice" style="margin: 10px 0 14px; padding: 10px 12px; border-left: 4px solid #326bff; background: #f0f6ff; color: #1d2327; font-size: 12px; line-height: 1.5;">
                Unlock more advanced fields and settings in Pro.
                <a href="${this.pricingUrl}" target="_blank" rel="noopener noreferrer" style="margin-left: 4px; font-weight: 600;">View Pricing</a>
            </div>
        `;

		settingsOptions.after(noticeHtml);
	}
}

jQuery(document).ready(function () {
	new CFEFD_Global_Helper();
});
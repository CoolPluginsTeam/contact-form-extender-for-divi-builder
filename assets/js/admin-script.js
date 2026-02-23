document.addEventListener('DOMContentLoaded', function () {
	adminToggleButtonsHandler();
	buttonShakeEffectHandler();
	handleTermsClick();
});

function handleTermsClick() {
	const termsLinks = document.querySelectorAll('.ccpw-see-terms');
	const termsBox = document.getElementById('termsBox');

	termsLinks.forEach(function (link) {
		link.addEventListener('click', function (e) {
			e.preventDefault();
			if (termsBox) {
				// Toggle display using plain JavaScript
				const isVisible = termsBox.style.display === 'block';
				termsBox.style.display = isVisible ? 'none' : 'block';
				link.innerHTML = !isVisible ? 'Hide Terms' : 'See terms';
			}
		});
	});
}

function adminToggleButtonsHandler() {
	const toggleAll = document.getElementById('cfefd-toggle-all');
	const elementToggles = document.querySelectorAll('.cfefd-element-toggle');

	/**
	 * Update the master toggle state based on individual toggles
	 */
	function updateToggleAllState() {
		if (!toggleAll || elementToggles.length === 0) return;

		const total = elementToggles.length;
		const checkedCount = Array.from(elementToggles).filter(t => t.checked).length;

		if (checkedCount === 0) {
			toggleAll.checked = false;
			toggleAll.indeterminate = false;
		} else if (checkedCount === total) {
			toggleAll.checked = true;
			toggleAll.indeterminate = false;
		} else {
			toggleAll.checked = false;
			toggleAll.indeterminate = true;
		}
	}

	if (toggleAll) {
		// Sync master toggle on page load
		updateToggleAllState();

		toggleAll.addEventListener('change', function () {
			const isChecked = this.checked;

			// Use requestAnimationFrame to avoid blocking the main thread during bulk updates
			requestAnimationFrame(() => {
				elementToggles.forEach(toggle => {
					if (toggle.checked !== isChecked) {
						toggle.checked = isChecked;
						// Trigger change event to ensure linked logic (like shake effect) triggers
						toggle.dispatchEvent(new Event('change', { bubbles: true }));
					}
				});
			});
		});

		// Listen for changes on individual toggles to update master toggle
		elementToggles.forEach(toggle => {
			toggle.addEventListener('change', updateToggleAllState);
		});
	}
}

function buttonShakeEffectHandler() {
	const wrappers = document.querySelectorAll('.cfefd-form-element-wrapper');

	wrappers.forEach(wrapper => {
		const headerButton = wrapper.querySelector('.wrapper-header .button');
		const bodyInputs = wrapper.querySelectorAll('.wrapper-body input[type="checkbox"]');
		const headerToggleCheckbox = wrapper.querySelector('.wrapper-header input[type="checkbox"]');

		if (!headerButton || bodyInputs.length === 0) return;

		const input1 = wrapper.querySelector('input[name="cfefd_enable_elementor_pro_form"]');
		const input2 = wrapper.querySelector('input[name="cfefd_enable_hello_plus"]');
		const input3 = wrapper.querySelector('input[name="cfefd_enable_formkit_builder"]');

		function triggerShake() {
			headerButton.classList.add('shake-effect');
		}

		if (headerToggleCheckbox) {
			headerToggleCheckbox.addEventListener('change', triggerShake);
		}

		bodyInputs.forEach(input => {
			input.addEventListener('change', function () {
				let shouldTrigger = false;

				if (input1 && input2 && input3) {
					shouldTrigger = input1.checked || input2.checked || input3.checked;
				} else {
					bodyInputs.forEach(i => {
						if (i.checked) shouldTrigger = true;
					});
				}

				if (shouldTrigger) {
					triggerShake();
				}
			});
		});
	});
}

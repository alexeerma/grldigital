/**
 * "Change cookie settings" buttons (.grl-consent-open) in page content.
 * Nimini Cookie Consent has no public JS API and only listens for clicks inside #ncc-root,
 * so the button forwards the click to the plugin's own "Preferences" button.
 * Without the plugin the button stays hidden (see legal.css).
 */
(function () {
	'use strict';
	var target = document.querySelector('#ncc-root [data-ncc-action="preferences"]');
	if (!target) {
		return;
	}
	document.querySelectorAll('.grl-consent-open').forEach(function (btn) {
		btn.classList.add('is-ready');
		btn.addEventListener('click', function () {
			target.click();
		});
	});
})();

/**
 * Contact popup: opens #grl-contact (native <dialog>) from [data-grl-contact] triggers and #kontakt links.
 * Without JS (or without <dialog> support) the triggers keep working as plain mailto links.
 */
(function () {
	var dialog = document.getElementById('grl-contact');
	if (!dialog || typeof dialog.showModal !== 'function') {
		return;
	}

	var root = document.documentElement;
	var lastTrigger = null;
	var downOnBackdrop = false;

	function isContactLink(el) {
		if (el.hasAttribute('data-grl-contact')) {
			return true;
		}
		return el.tagName === 'A' && el.hash === '#kontakt' && el.pathname === window.location.pathname && el.host === window.location.host;
	}

	function lockScroll() {
		var gap = window.innerWidth - root.clientWidth;
		if (gap > 0) {
			document.body.style.paddingRight = gap + 'px';
		}
		root.classList.add('grl-contact-open');
	}

	function unlockScroll() {
		root.classList.remove('grl-contact-open');
		document.body.style.paddingRight = '';
	}

	function open(trigger) {
		if (dialog.open) {
			return;
		}
		lastTrigger = trigger || document.activeElement;
		lockScroll();
		dialog.showModal();
	}

	function close() {
		if (dialog.open) {
			dialog.close();
		}
	}

	document.addEventListener('click', function (e) {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
			return;
		}
		var trigger = e.target.closest ? e.target.closest('[data-grl-contact], a[href*="#kontakt"]') : null;
		if (!trigger || !isContactLink(trigger)) {
			return;
		}
		e.preventDefault();
		open(trigger);
	});

	dialog.querySelectorAll('[data-grl-contact-close]').forEach(function (btn) {
		btn.addEventListener('click', close);
	});

	// Backdrop click: the dialog box itself only has transparent padding around the panel,
	// so a press that starts and ends on the dialog element (not inside the panel) is outside the form.
	dialog.addEventListener('pointerdown', function (e) {
		downOnBackdrop = e.target === dialog;
	});
	dialog.addEventListener('click', function (e) {
		if (downOnBackdrop && e.target === dialog) {
			close();
		}
		downOnBackdrop = false;
	});

	// Esc is handled natively (cancel -> close). Clean up on every close path.
	dialog.addEventListener('close', function () {
		unlockScroll();
		var target = lastTrigger;
		lastTrigger = null;
		// The mobile menu collapses when its links are clicked, hiding the trigger; fall back to the menu toggle.
		if (target && !target.offsetParent) {
			target = document.querySelector('.grl-header__toggle');
		}
		if (target && typeof target.focus === 'function') {
			target.focus();
		}
	});

	if (window.location.hash === '#kontakt') {
		open(null);
	}
})();

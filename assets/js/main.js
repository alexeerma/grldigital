(function () {
	document.documentElement.classList.add('grl-js');

	var reveal = document.querySelectorAll('[data-grl-reveal]');
	if (reveal.length) {
		if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) {
					if (en.isIntersecting) {
						en.target.classList.add('is-visible');
						io.unobserve(en.target);
					}
				});
			}, { rootMargin: '0px 0px -10% 0px' });
			reveal.forEach(function (el) { io.observe(el); });
		} else {
			reveal.forEach(function (el) { el.classList.add('is-visible'); });
		}
	}

	var header = document.querySelector('.grl-header');
	var toggle = document.querySelector('.grl-header__toggle');
	if (toggle && header) {
		toggle.addEventListener('click', function () {
			var open = header.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		header.querySelectorAll('.grl-nav a').forEach(function (a) {
			a.addEventListener('click', function () {
				header.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			});
		});
	}

	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 10);
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}
})();

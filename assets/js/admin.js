/* "Minu tööd" edit screen: image pickers, gallery, tag rows. */
jQuery(function ($) {
	var l10n = window.grlProjectAdmin || {};

	function thumbUrl(att, size) {
		var s = att.sizes || {};
		return (s[size] && s[size].url) || (s.medium && s.medium.url) || (s.thumbnail && s.thumbnail.url) || att.url;
	}

	// Legacy media-ID picker (kept for any old markup).
	$(document).on('click', '.grl-media-pick', function (e) {
		e.preventDefault();
		var target = $('#' + $(this).data('target'));
		var frame = wp.media({ multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			target.val(frame.state().get('selection').first().id);
		});
		frame.open();
	});

	// Single image picker.
	$(document).on('click', '.grl-pm-image__pick', function (e) {
		e.preventDefault();
		var box = $(this).closest('[data-grl-image]');
		var input = box.find('.grl-pm-image__id');
		var btn = $(this);
		var frame = wp.media({
			title: l10n.imageTitle,
			button: { text: l10n.imageButton },
			multiple: false,
			library: { type: 'image' }
		});
		frame.on('open', function () {
			var id = parseInt(input.val(), 10);
			if (id) {
				var att = wp.media.attachment(id);
				att.fetch();
				frame.state().get('selection').add(att);
			}
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			input.val(att.id);
			box.find('.grl-pm-image__preview').empty().append(
				$('<img>', { 'class': 'grl-pm-image__img', src: thumbUrl(att, 'medium'), alt: '' })
			);
			box.addClass('has-image');
			btn.text(btn.data('label-change'));
		});
		frame.open();
	});

	$(document).on('click', '.grl-pm-image__remove', function (e) {
		e.preventDefault();
		var box = $(this).closest('[data-grl-image]');
		var btn = box.find('.grl-pm-image__pick');
		box.find('.grl-pm-image__id').val('');
		box.find('.grl-pm-image__preview').empty();
		box.removeClass('has-image');
		btn.text(btn.data('label-empty'));
	});

	// Gallery.
	function galleryItem(att) {
		var li = $('<li>', { 'class': 'grl-pm-gallery__item' });
		li.append($('<img>', { 'class': 'grl-pm-gallery__img', src: thumbUrl(att, 'thumbnail'), alt: '' }));
		li.append($('<input>', { type: 'hidden', name: 'grl_gallery[]', value: att.id }));
		li.append(
			$('<button>', { type: 'button', 'class': 'grl-pm-gallery__remove', 'aria-label': l10n.removeImage })
				.append($('<span>', { 'class': 'dashicons dashicons-no-alt', 'aria-hidden': 'true' }))
		);
		return li;
	}

	$('.grl-pm-gallery__list').sortable({ items: '> li', tolerance: 'pointer', placeholder: 'grl-pm-gallery__placeholder' });

	$(document).on('click', '.grl-pm-gallery__add', function (e) {
		e.preventDefault();
		var list = $(this).closest('[data-grl-gallery]').find('.grl-pm-gallery__list');
		var frame = wp.media({
			title: l10n.galleryTitle,
			button: { text: l10n.galleryButton },
			multiple: 'add',
			library: { type: 'image' }
		});
		frame.on('select', function () {
			var existing = list.find('input').map(function () { return parseInt(this.value, 10); }).get();
			frame.state().get('selection').each(function (model) {
				var att = model.toJSON();
				if (existing.indexOf(att.id) === -1) {
					list.append(galleryItem(att));
				}
			});
		});
		frame.open();
	});

	$(document).on('click', '.grl-pm-gallery__remove', function (e) {
		e.preventDefault();
		$(this).closest('.grl-pm-gallery__item').remove();
	});

	// Tag rows.
	var tagList = $('.grl-pm-tags');
	tagList.sortable({ items: '> li', handle: '.grl-pm-tags__handle', axis: 'y' });

	$(document).on('click', '.grl-pm-tags__add', function (e) {
		e.preventDefault();
		var li = $('<li>', { 'class': 'grl-pm-tags__item' });
		li.append($('<span>', { 'class': 'dashicons dashicons-menu grl-pm-tags__handle', 'aria-hidden': 'true' }));
		li.append($('<input>', { type: 'text', 'class': 'regular-text grl-pm-tags__input', name: 'grl_tags[]', 'aria-label': l10n.tag }));
		li.append(
			$('<button>', { type: 'button', 'class': 'button-link grl-pm-tags__remove', 'aria-label': l10n.removeTag })
				.append($('<span>', { 'class': 'dashicons dashicons-no-alt', 'aria-hidden': 'true' }))
		);
		tagList.append(li);
		li.find('input').trigger('focus');
	});

	$(document).on('click', '.grl-pm-tags__remove', function (e) {
		e.preventDefault();
		var li = $(this).closest('.grl-pm-tags__item');
		if (tagList.children().length > 1) {
			li.remove();
		} else {
			li.find('input').val('');
		}
	});
});

(function() {
	'use strict';

	function CastleJS() {
		return this;
	}

	var Config, Debug;

	/**
	 * Utility functions
	 */
	var Utils = {

		shortSlugify: function (input)
		{
			return input.replace(/[^A-Z0-9]/g, '').toLowerCase();
		},

		slugify: function(input)
		{
			return input.toString()
				.toLowerCase()
				.replace(/\s+/g, '-')
				.replace(/[^\w\-]+/g, '')
				.replace(/\-\-+/g, '-')
				.replace(/^-+/, '')
				.replace(/-+$/, '');
		},

		yiqContrast: function(color, dark, light)
		{
			color = color.replace(/[^0-9A-Fa-f]/, '');
			dark = dark === undefined ? '#2C3E50' : dark;
			light = light === undefined ? '#FFF' : light;

			var r = parseInt(color.substr(0, 2), 16),
				g = parseInt(color.substr(2, 2), 16),
				b = parseInt(color.substr(4, 2), 16);

			var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
			return (yiq >= 128) ? dark : light;
		},

	};

	CastleJS.prototype = {
		constructor: CastleJS,

		init: function(data)
		{
			if (data !== undefined) {
				Debug = data.debug || {};
				Config = data.config || {};
			}

			$('[data-toggle="tooltip"], .btn.btn-tooltip').tooltip({
				container: 'body'
			});

			this.getEditorPageUtils();

			this.initDeletionConfirmation();
			this.initLabelColors();
			this.initTypeFilter();
		},

		getEditorPageUtils: function()
		{
			if ($('.item-editor').length > 0) {
				this.initAttachmentEditor();
				// this.initColorPicker();
				this.initShortSlugFields();
				this.initSlugFields();
				this.initTagFields();
			}
		},

		initAttachmentEditor: function(selector)
		{
			selector = selector || 'select[data-editor="attachments"]';

			$(selector).each(function() {
				var select = $(this),
					form = $('[data-editor-target="#' + select.attr('id') + '"]');

				form.find('[data-attachment]').each(function() {
					var attachment = $(this),
						rel = attachment.data('attachment');

					attachment.find('button[data-role="remove"]').on('click', function() {
						select.children('option[value="' + rel + '"]').prop('selected', false);
						attachment.remove();
					});
				});
			});
		},

		initColorPicker: function(selector)
		{
			selector = selector || 'input[type="color"]';

			var palette = [
				'fce94f', 'edd400', 'c4a000',
				'fcaf3e', 'f57900', 'ce5c00',
				'e9b96e', 'c17d11', '8f5902',
				'8ae234', '73d216', '4e9a06',
				'729fcf', '3465a4', '204a87',
				'ad7fa8', '75507b', '5c3566',
				'ef2929', 'cc0000', 'a40000',
				'd3d7cf', '888a85', '2e3436',
			];

			var s = '';
			while (palette.length > 0) {
				var row = palette.splice(0, 6),
					r = row.length;

				s += '<div class="color-selector">';
				while (r --) {
					var color = row[r];
					s += '<label class="color-selector-color"' +
						'style="background-color: #' + color + ';" value="#' + color +
						'"><input type="radio" name="color" value="#' + color +
						'">&#x00a0;<span class="sr-only">Set color to #' + color + '</input></label>';
				}
				s += '</div>';
			}

			$(selector).after(s);
		},

		initDeletionConfirmation: function(selector)
		{
			selector = selector || '[data-confirm="delete"]';

			$(selector).siblings('button[type="submit"]').hide();

			$(selector).on('click', function(e) {
				var orig = $(this),
					real = orig.siblings('button[type="submit"]');

				orig.hide().blur();
				real.show().hover(function() {
					$(this).prop('disabled', true)
						.delay(625)
						.queue(function(next) {
							$(this).prop('disabled', false);
							next();
						})
						.focus();
				}, function() {
					$(this).stop().hide(0).blur();
					orig.show(0);
				});
			});
		},

		initLabelColors: function(selector)
		{
			selector = selector || '[data-color]';

			$(selector).each(function() {
				var el = $(this),
					bg = el.data('color'),
					fg = Utils.yiqContrast(el.data('color'));

				if (el.data('colorProperties') !== undefined) {
					var props = el.data('colorProperties').split(','),
						rules = {};
					$.each(props, function(i) {
						rules[props[i]] = bg;
					});
					el.css(rules);
				} else {
					el.css({
						backgroundColor: bg,
						color: fg
					});
				}
			});
		},

		initShortSlugFields: function()
		{
			var target = $('input[data-short-slug]');

			target.each(function() {
				var sourceField = $('#' + $(this).data('shortSlug')),
					slugField = $(this);

				if (sourceField.length > 0) {
					sourceField.on('keyup', function(e) {
						slugField.val(Utils.shortSlugify(sourceField.val()));
					});
				}

				slugField.on('blur', function(e) {
					slugField.val(Utils.shortSlugify(slugField.val()));
				});
			});
		},

		initSlugFields: function(selector)
		{
			selector = selector || 'input[data-slug]';

			$(selector).each(function() {
				var sourceField = $('#' + $(this).data('slug')),
					slugField = $(this);

				if (sourceField.length > 0) {
					sourceField.on('keyup', function(e) {
						slugField.val(Utils.slugify(sourceField.val()));
					});
				}

				slugField.on('blur', function(e) {
					slugField.val(Utils.slugify(slugField.val()));
				});
			});
		},

		initTagFields: function(selector)
		{
			selector = selector || '.taggable';

			var colors = {},
				getItemColor = function(field, key) {
					return colors[field][key] === undefined ?
						'' :
						' style="' +
							'background-color: ' + colors[field][key] + ';' +
							'color: ' + Utils.yiqContrast(colors[field][key]) + ';' +
						'"';
				},
				getOptionColor = function(field, key) {
					return colors[field][key] === undefined ?
						'' :
						'<span style="color: ' + colors[field][key] + ';' +
						'">&#x25cf;&#x00a0;</span>';
				};

			$(selector).each(function() {
				var el = $(this),
					field = el.attr('id');

				colors[field] = {};
				el.children('option').each(function() {
					colors[field][$(this).val()] = $(this).data('color');
				});

				el.selectize({
					create: el.data('create'),
					render: {
						item: function(data, escape) {
							return '<div class="item"' +
								getItemColor(field, escape(data.value)) + '>' +
								escape(data.text) + '</span>';
						},
						option: function(data, escape) {
							return '<div class="option">' +
								getOptionColor(field, escape(data.value)) +
								escape(data.text) + '</div>';
						}
					},
				});
			});
		},

		initTypeFilter: function(selector)
		{
			selector = selector || '[data-type-filter]';

			$(selector).each(function() {
				var el = $(this),
					target = $('#' + el.data('typeFilter'));

				target.on('change', function() {
					el.find('[data-type]').each(function() {

						if (target.val() === '' || $(this).data('type') == target.val()) {
							$(this).show();
						} else {
							$(this).hide();
						}

					});
				});

				target.trigger('change');
			});
		},

	};

	if (typeof window == 'object') {
		window.CastleJS = new CastleJS();
	}

})();

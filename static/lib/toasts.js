(() => {
	window.Toast = {
		'make_toast_html': (content, timeout = 1500) => {
			$('.wcl-toast-box').toast_remove();
			let $toast_box = $('<div></div>')
				.addClass('wcl-toast-box')
				.css('position', 'fixed')
				.css('bottom', '150px')
				.css('left', '0')
				.css('margin-left', '50%')
				.css('margin-right', '-20%') // 最大宽度 70%
				.css('transform', 'translateX(-50%)')
				.css('background', '#FFF')
				.css('box-shadow', '0 4px 14px -4px #00000055')
				.css('padding', '8px 14px')
				.css('font-size', '15px')
				.css('font-weight', '700')
				.css('border-radius', '6px')
				.css('opacity', '0')
				.css('display', 'none')
				.css('pointer-events', 'none')
				.css('z-index', '2001')
				.html(content);
			$('body').append($toast_box);
			$toast_box.toast_show();
			setTimeout(() => {
				$toast_box.toast_remove();
			}, timeout);
		},
		'make_toast_text': function(content, timeout = 1500) {
			this.make_toast_html(escapeXml(content), timeout);
		}
	};

	$.fn.toast_show = function() {
		this.show().animate({'opacity': '1', 'bottom': '160px'}, 150, 'swing');
	};
	$.fn.toast_remove = function() {
		this.animate({'opacity': '0', 'bottom': '150px'}, 150, 'swing', () => {
			this.remove();
		});
	};
})();

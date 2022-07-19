/**
 * 自动滚动工具
 */
 (() => {
	var ease_function = (x) => {
		return -((x - 1) ** 2) + 1;
	};
	window.scrollto = function(v,s,d=200) {
		var scroller_interval_T = $(s).attr('data-scroll-t');

		if(scroller_interval_T) {
			removeFrameFunc(scroller_interval_T);
			$(s).removeAttr('data-scroll-t');
			scroller_interval_T = null;
		}

		if(v < 0) v = 0;

		var ele=$(s)[0];
		var old=ele.scrollTop;
		// 检验极值
		ele.scrollTop = 1610612736;
		var mx = ele.scrollTop;
		ele.scrollTop = old;
		if(v < 0) v = 0;
		if(v > mx) v = mx;
		if(old==v) return;

		var E = v - old;
		var last_t = -1;
		var init_t = -1;
		scroller_interval_T = registerFrameFunc(function(timer){
			if(init_t == -1) {
				init_t = last_t = timer;
				return;
			}
			curr_t = timer - init_t;
			prev_t = last_t - init_t;
			last_t = timer;
			if(curr_t > d) {
				curr_t = d;
			}
			var D = E * (ease_function(curr_t / d) - ease_function(prev_t / d));
			ele.scrollTop += D;
			if(curr_t >= d) {
				removeFrameFunc(scroller_interval_T);
				$(s).removeAttr('data-scroll-t');
				scroller_interval_T = null;
			}
		});
		$(s).attr('data-scroll-t', '' + scroller_interval_T);
	}
	window.scrollto_left = function(v,s,d=300) {
		var scroller_interval_L = $(s).attr('data-scroll-l');

		if(scroller_interval_L) {
			removeFrameFunc(scroller_interval_L);
			$(s).removeAttr('data-scroll-l');
			scroller_interval_L = null;
		}

		if(v < 0) v = 0;

		var ele=$(s)[0];
		var old=ele.scrollLeft;
		// 检验极值
		ele.scrollLeft = 1610612736;
		var mx = ele.scrollLeft;
		ele.scrollLeft = old;
		if(v < 0) v = 0;
		if(v > mx) v = mx;
		if(old==v) return;

		var E = v - old;
		var last_t = -1;
		var init_t = -1;
		scroller_interval_L = registerFrameFunc(function(timer){
			if(init_t == -1) {
				init_t = last_t = timer;
				return;
			}
			curr_t = timer - init_t;
			prev_t = last_t - init_t;
			last_t = timer;
			if(curr_t > d) {
				curr_t = d;
			}
			var D = E * (ease_function(curr_t / d) - ease_function(prev_t / d));
			ele.scrollLeft += D;
			if(curr_t >= d) {
				removeFrameFunc(scroller_interval_L);
				$(s).removeAttr('data-scroll-l');
				scroller_interval_L = null;
			}
		});
		$(s).attr('data-scroll-l', '' + scroller_interval_L);
	}
})();

/**
 * 跑马灯显示（手动）
 */
(() => {
	$.fn.scroll_display_setpos = function(pos = 0) {
		var $outer = this;
		var $inner = this.children();
		var t = +new Date();
		$outer.attr('data-wcl-scrollable-t', t);
		if(!$outer.hasClass('wcl-scrollable')) {
			throw new Error('$.fn.scroll_display_setpos - Parent has not class wcl-scrollable');
		}

		var totalwidth = $outer.width();
		var innerwidth = $inner.width();
		var overscroll = +$outer.attr('data-wcl-overscroll');
		if(overscroll) {
			totalwidth -= overscroll;
		}
		var remain = innerwidth - totalwidth;
		var pxpos = 0;

		if(pos > 0 && remain > 0 && totalwidth > 0) {
			let inner_centered = pos * innerwidth;
			let outer_center = totalwidth / 2;
			pxpos = inner_centered - outer_center;

			if(pxpos < 0) {
				pxpos = 0;
			}
			if(pxpos > remain) {
				pxpos = remain;
			}
		}

		var prev_pxpos = $inner.css('margin-left');
		prev_pxpos = -prev_pxpos.substring(0, prev_pxpos.length - 2);
		if(prev_pxpos + 1e-7 < pxpos) {
			$inner.css('transition', 'margin-left .1s');
		} else {
			$inner.css('transition', 'none');
		}

		$inner.css({'margin-left': '-' + pxpos + 'px'});

		return this;
	};
})();

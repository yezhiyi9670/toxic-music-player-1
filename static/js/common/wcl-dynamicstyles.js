(() => {

	window.DynamicStyles = new (function() {

		this.setStyle = function(id, text) {
			let $style = $(`style.wcl-dynstyle[wcl-dynid=${id}]`);
			if($style.length) {
				$style.html(text);
			} else {
				$('head').append(
					$('<style></style>').addClass('wcl-dynstyle')
					.attr('wcl-dynid', id)
					.html(text)
				);
			}
		};

	})();

})();
$(() => {
	
});

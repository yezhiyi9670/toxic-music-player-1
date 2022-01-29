function clean_server_trash() {
	$.ajax({
		url: G.basic_url + 'clean-garbage',
		method: 'GET',
		timeout: 20000,
		success: () => {
			console.log(LNG('ui.debug.trash_cleaned'));
		},
	});
}
$(() => {
	clean_server_trash();
});

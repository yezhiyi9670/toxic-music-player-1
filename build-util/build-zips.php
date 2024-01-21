<?php

chdir('..');

$base = 'txmp';
$version = trim(file_get_contents('lib/VERSION'));
print('Building Version v' . $version . "\n");

$is_preview = -1 != strpos($version, '-pre');

$full_zip = '_build/' . $base . '-v' . $version . '-' . ($is_preview ? 'full' : 'release') . '.zip';
print('Full zip: ' . $full_zip . "\n");

$update_zip = '_build/' . $base . '-v' . $version . '-' . ($is_preview ? 'update' : 'update') . '.zip';
print('Update zip: ' . $update_zip . "\n");

sleep(3);

function add_file($zip, $file) {
	if(!file_exists($file)) {
		print('Nx: ' . $file . "\n");
		return;
	}
	print('Add: ' . $file . ' -> ' . $zip . "\n");
	shell_exec('7z a "' . $zip . '" "' . $file . '"');
}

foreach(['changelog', 'data.init', 'internal_config', 'lib', 'static', '.htaccess', 'index.php', 'LICENSE'] as $file) {
	add_file($full_zip, $file);
}

foreach(['changelog', 'lib', 'static', 'index.php'] as $file) {
	add_file($update_zip, $file);
}

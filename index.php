<?php
require __DIR__ . '/vendor/autoload.php';

$exceptions = ["api/", "onf_admin/", "onf_admin_save/"];

$roots = ["fr", "de", "en", ""];
$directory = preg_split("[/]", $_SERVER["REQUEST_URI"]);
$directory = array_search($directory[1], $roots) !== FALSE ? "" : $directory[1]."/";


$is_exception = array_search($directory, $exceptions) !== FALSE;
$real_folder = ($is_exception) ? "" : $directory;

$config = [
	"settings" => [
		'displayErrorDetails' => true,
		'directory' => $real_folder,
		"url" => ($is_exception) ? "common/sample_settings.json" : $real_folder."settings.json"
	]
];


if(!is_file($config["settings"]["url"]) && !$is_exception) {
	exit;
}

// Instantiate the app
$app = new \Slim\App($config);

// Register routes
require __DIR__ . '/includes/core/routes.php';

// Run app
$app->run();

?>
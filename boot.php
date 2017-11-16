<?php

require __DIR__ . '/../vendor/autoload.php';

$roots = ["fr", "de", "en", ""];
$directory = preg_split("[/]", $_SERVER["REQUEST_URI"]);
$directory = array_search($directory[1], $roots) !== FALSE ? "" : $directory[1]."/";

$config = [
	"settings" => [
		'displayErrorDetails' => true,
		'directory' => $directory,
		"url" => $directory."settings.json"
	]
];

if(!is_file($config["settings"]["url"])) exit;

// Instantiate the app
$app = new \Slim\App($config);

// Register routes
require __DIR__ . '/../includes/core/routes.php';

// Run app
$app->run();

?>
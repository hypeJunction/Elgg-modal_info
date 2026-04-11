<?php

/**
 * PHPUnit test bootstrap for modal_info plugin.
 *
 * Locates the Elgg engine's PHPUnit bootstrap and loads it,
 * then ensures the plugin autoloader is available.
 */

// Elgg engine root – adjust path if your install layout differs
$engine = dirname(__DIR__, 4); // e.g. /path/to/elgg/mod/modal_info -> /path/to/elgg

$elgg_bootstrap = "{$engine}/engine/tests/phpunit/bootstrap.php";
if (!file_exists($elgg_bootstrap)) {
	// Fallback: try the vendor-installed Elgg test bootstrap
	$elgg_bootstrap = "{$engine}/vendor/elgg/elgg/engine/tests/phpunit/bootstrap.php";
}

if (!file_exists($elgg_bootstrap)) {
	throw new \RuntimeException(
		'Could not locate the Elgg PHPUnit bootstrap. '
		. 'Make sure this plugin is installed inside an Elgg installation.'
	);
}

require_once $elgg_bootstrap;

// Load plugin autoloader
$plugin_root = dirname(__DIR__);
if (file_exists("{$plugin_root}/vendor/autoload.php")) {
	require_once "{$plugin_root}/vendor/autoload.php";
}

<?php

/**
 * Plugin Name: WP Parsedown
 * Plugin URI:  https://github.com/asmbs/wp-parsedown
 * Description: Parsedown wrapper for WordPress with a live preview editor
 * Version:     3.0.6
 * Author:      Kyle Tucker / ASMBS
 * Author URI:  https://github.com/asmbs
 *
 * @author Kyle Tucker <kyleatucker@gmail.com>
 */

// A proper composer-installed WordPress instance will handle autoloading on its own, but just in case,
// we'll allow a local autoload if necessary.
$autoloader = __DIR__ .'/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

$GLOBALS['parsedown'] = new ASMBS\WPParsedown\ParsedownPlugin(
    new \ASMBS\WPParsedown\ParsedownModified(),
    __FILE__
);

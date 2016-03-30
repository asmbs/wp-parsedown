<?php

/**
 * Plugin Name: WP Parsedown
 * Plugin URI:  https://github.com/friartuck6000/wp-parsedown
 * Description: Disable the WYSIWYG editor and author content in Markdown, with a live preview
 * Version:     1.0.4
 * Author:      Kyle Tucker
 * Author URI:  https://github.com/friartuck6000
 *
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */

// A proper composer-installed WordPress instance will handle autoloading on its own, but just in case,
// we'll allow a local autoload if necessary.
$autoloader = __DIR__ .'/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

$GLOBALS['parsedown'] = new Ft6k\WpParsedown\ParsedownPlugin(new ParsedownExtra(), __FILE__);

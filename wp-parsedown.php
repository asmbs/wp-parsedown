<?php

/**
 * Plugin Name: WP Parsedown
 * Plugin URI:  https://github.com/friartuck6000/wp-parsedown
 * Description: Disable the WYSIWYG editor and author content in Markdown, with a live preview
 * Version:     1.0.0-dev
 * Author:      Kyle Tucker
 * Author URI:  https://github.com/friartuck6000
 *
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */

// A proper composer-installed WordPress instance will handle autoloading on its own
// require_once __DIR__ .'/vendor/autoload.php';

$GLOBALS['parsedown'] = new Ft6k\WpParsedown\ParsedownPlugin(new ParsedownExtra(), __DIR__);

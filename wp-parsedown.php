<?php
// -- wp-parsedown | v0.1.0 | MIT License | @friartuck6000
// ---------------------------------------------------------------------

// Plugin Name:  WP Parsedown
// Plugin URI:   https://github.com/friartuck6000/wp-parsedown
// Description:  A wrapper for Parsedown that lets you use Markdown in WordPress.
// Version:      0.1.0
// Author:       Kyle Tucker
// Author URI:   https://github.com/friartuck6000

class WP_Parsedown
{
  // Parsedown instance
  public $parser;

  // Private members
  private $path;
  private $uri;

  // Follows singleton pattern
  private static $instance = NULL;

  private function __construct()
  {
    // Set path and URI
    $this->path = plugin_dir_path( __FILE__ );
    $this->uri  = plugin_dir_url(  __FILE__ );

    // Include Parsedown and get an instance of it.
    require_once $this->path .'parsedown/Parsedown.php';
    $this->parser = Parsedown::instance();

    add_action( 'init', [ &$this, 'init' ] );

    // Disable the visual editor globally when this plugin is active.
    add_filter( 'user_can_richedit', '__return_false' );
  }

  // Runs on init; adds our content filter.
  public function init()
  {
    remove_filter( 'the_content', 'wpautop' );
    add_filter( 'the_content', [ &$this, 'parse' ], 1 );
  }

  // The actual parser; runs on the_content filter, but can be run
  // manually too.
  public function parse( $content )
  {
    return $this->parser->parse( $content );
  }

  // Get single object instance.
  public static function instance()
  {
    if ( self::$instance === NULL )
      self::$instance = new self;
    return self::$instance;
  }

};

WP_Parsedown::instance();

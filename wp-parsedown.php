<?php
// -- wp-parsedown | v0.2.0 | MIT License | @friartuck6000
// ---------------------------------------------------------------------

// Plugin Name:  WP Parsedown
// Plugin URI:   https://github.com/friartuck6000/wp-parsedown
// Description:  A wrapper for Parsedown that lets you use Markdown in WordPress.
// Version:      0.2.0
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

    // Init
    add_action( 'init', [ &$this, 'init' ] );

    // Add preview meta box
    add_action( 'add_meta_boxes', [ &$this, 'add_preview_meta_box' ] );

    // Disable the visual editor globally when this plugin is active.
    add_filter( 'user_can_richedit', '__return_false' );
  }

  // Runs on init; adds our content filter.
  public function init()
  {
    remove_filter( 'the_content', 'wpautop' );
    add_filter( 'the_content', [ &$this, 'parse' ], 1 );
  }

  // Runs on add_meta_boxes; adds Markdown Preview meta box
  public function add_preview_meta_box( $post_type, $post )
  {
    // Get post type definition
    $post_type_settings = get_post_type_object( $post_type );

    // If the post type doesn't support a content editor, there's no reason
    // to show the meta box.
    $supports = $post_type_settings->supports;
    if ( !empty( $supports ) && !in_array( 'editor', $supports ) )
      return false;

    // Register the meta box
    add_meta_box(
      'parsedown-preview',
      __( 'Markdown Preview' ),
      [ &$this, 'render_preview_meta_box' ],
      $post_type, 
      'normal',
      'high'
    );

  }

  // Renders the Markdown Preview meta box
  public function render_preview_meta_box( &$post )
  {
    echo '<p>Hey, here\'s the preview box!</p>';
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

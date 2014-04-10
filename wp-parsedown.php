<?php
// -- wp-parsedown | v0.4.0 | MIT License | @friartuck6000
// ---------------------------------------------------------------------

// Plugin Name:  WP Parsedown
// Plugin URI:   https://github.com/friartuck6000/wp-parsedown
// Description:  A wrapper for Parsedown that lets you use Markdown in WordPress.
// Version:      0.4.0
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

    // Add preview meta box and add the AJAX action for updating it
    add_action( 'add_meta_boxes', [ &$this, 'add_preview_meta_box' ], 10, 2 );
    add_action( 'wp_ajax_update_preview', [ &$this, 'ajax_update_preview_meta_box' ] );

    // Enqueue plugin scripts (for editing view only)
    add_action( 'admin_enqueue_scripts', [ &$this, 'maybe_enqueue_scripts' ] );

    // Add Markdown Cheatsheet help tab
    add_action( 'admin_head', [ $this, 'add_help_tab' ] );

    // Disable the visual editor globally when this plugin is active.
    add_filter( 'user_can_richedit', '__return_false' );

    // Alter the code inserted when adding media to a post
    add_filter( 'image_send_to_editor', [ $this, 'image_send_to_editor' ], 100, 8 );
    add_shortcode( 'image', [ $this, 'shortcode_image' ] );

    // Remove all but the fullscreen button from editor quicktags
    add_filter( 'quicktags_settings', [ $this, 'drop_quicktags'] );
  }

  // Runs on init; adds our content filter.
  public function init()
  {
    remove_filter( 'the_content', 'wpautop' );
    add_filter( 'the_content', [ &$this, 'parse' ], 1 );
  }

  // Runs on admin_enqueue_scripts; enqueues JS on editor pages.
  public function maybe_enqueue_scripts( $hook )
  {
    if ( $hook == 'post.php' || $hook == 'post-new.php' )
      wp_enqueue_script( 'preview_js', $this->uri .'assets/js/dist/scripts.min.js', [ 'jquery' ], null, true );
  }

  // Runs on add_meta_boxes; adds Markdown Preview meta box
  public function add_preview_meta_box( $post_type, $post )
  {
    // Get post type definition
    $post_type_settings = get_post_type_object( $post_type );

    // If the post type doesn't support a content editor, there's no reason
    // to show the meta box.
    if ( !post_type_supports( $post_type, 'editor' ) )
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

  // Add help tab
  public function add_help_tab()
  {
    $screen = get_current_screen();
    if ( $screen->base == 'post' )
    {
      $content = sprintf(
        '<h3>%1$s</h3>'
        .'<p>%2$s</p>',
        __( 'What is Markdown?' ),
        sprintf(
          '<a href="%2$s" target="_blank">%1$s</a> %3$s',
          __( 'Markdown' ),
          'http://daringfireball.net/projects/markdown/',
          __( 'is a special formatting syntax that allows you to write content in plain text.' )
        )
      );
      $args = [
        'id'      => 'markdown_help',
        'title'   => _x( 'Markdown', 'help tab' ),
        'content' => $content
      ];
      $screen->add_help_tab( $args );
    }
  }

  public function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt )
  {
    // Set optional items only if necessary
    $options = '';
    if ( !empty( $url ) )
      $options .= sprintf(
        ' url="%s"',
        $url
      );
    if ( !empty( $caption ) )
      $options .= sprintf(
        ' caption="%s"',
        $caption
      );
    if ( !empty( $alt ) )
      $options .= sprintf(
        ' alt="%s"',
        $alt
      );
    if ( !empty( $title ) )
      $options .= sprintf(
        ' title="%s"',
        $title
      );

    // Generate shortcode
    $code = sprintf(
      '[image id="%1$s" align="%2$s" size="%3$s"%4$s /]',
      $id,
      $align,
      $size,
      $options
    );

    return $code;
  }

  public function shortcode_image( $attrs, $content = '' )
  {
    $src = wp_get_attachment_url( $attrs['id'] );

    // Set the HTML for the image itself
    $image_html = sprintf(
      '<img id="image-%1$s" src="%2$s" alt="%3$s" />',
      $attrs['id'],
      $src,
      $attrs['alt']
    );

    // Wrap the image HTML in an anchor if URL was given
    if ( !empty( $attrs['url'] ) )
      $image_html = sprintf( '<a href="%s">', $attrs['url'] ) . $image_html . '</a>';

    // Add a caption element if a caption was set.
    $caption_html = '';
    if ( !empty( $attrs['caption'] ) )
      $caption_html = sprintf(
        '<figcaption>%s</figcaption>',
        $attrs['caption']
      );

    // Set up the whole HTML figure element
    $html = sprintf(
      '<figure id="figure-%1$s" class="image %3$s %4$s">'
        .'%2$s'
        .'%5$s'
      .'</figure>',
      $attrs['id'],
      $image_html,
      $attrs['align'],
      $attrs['size'],
      $caption_html
    );

    // Return it
    return $html;
  }


  // Remove all the quicktags!
  public function drop_quicktags( $quicktags )
  {
    $quicktags['buttons'] = 'fullscreen';

    return $quicktags;
  }

  // Renders the Markdown Preview meta box
  public function render_preview_meta_box( $post )
  {
    echo '<div id="parsedown-preview-content"></div>';
  }

  // AJAX hook for updating the meta box's content
  public function ajax_update_preview_meta_box()
  {
    $content = $_POST['content'];
    echo apply_filters( 'the_content', stripslashes( $content ) );
    die();
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

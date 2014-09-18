<?php
// -- wp-parsedown | v0.5.0 | MIT License | @friartuck6000
// ---------------------------------------------------------------------

// Plugin Name:  WP Parsedown
// Plugin URI:   https://github.com/friartuck6000/wp-parsedown
// Description:  A wrapper for Parsedown that lets you use Markdown in WordPress.
// Version:      0.5.0
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
    require_once $this->path .'parsedown/ParsedownExtra.php';

    $this->parser = new ParsedownExtra();
    $this->parser->setAutolinksEnabled( false );

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
    add_filter( 'the_content', [ $this, 'parse' ], 1 );
  }

  // Runs on admin_enqueue_scripts; enqueues JS on editor pages.
  public function maybe_enqueue_scripts( $hook )
  {
    if ( $hook == 'post.php' || $hook == 'post-new.php' )
    {
      wp_enqueue_script( 'preview_js', $this->uri .'assets/js/dist/scripts.min.js', [ 'jquery' ], null, true );
      wp_enqueue_style( 'markdown_admin_css', $this->uri .'assets/css/admin.min.css', [], null );
    }
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
      // Intro
      $content = sprintf(
        '<h3>%1$s</h3>'
        .'<p>%2$s</p>',
        __( 'Markdown Cheatsheet' ),
        sprintf(
          __( 'Use the quick reference below for tips on authoring content in Markdown. See %s to learn more.' ),
          '<a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">the official Markdown website</a>'
        )
      );

      // Table
      $samples = [
        [
          'title'  => __( 'Paragraphs' ),
          'before' => 'The quick brown fox jumped over the lazy dog.'."\n\n".'Lorem ipsum dolor sit amet...',
          'after'  => '<p>The quick brown fox jumped over the lazy dog.</p><p>Lorem ipsum dolor sit amet...</p>'
        ],
        [
          'title'  => __( 'Bold & Italics' ),
          'before' => 'The _quick brown fox_ jumped over the **lazy dog**.',
          'after'  => 'The <em>quick brown fox</em> jumped over the <strong>lazy dog</strong>.'
        ],
        [
          'title'  => __( 'Lists' ),
          'before' => "-  This is\n-  A _bulleted_\n-  List \n\n1. This is\n2. A _numbered_\n3. List",
          'after'  => '<ul><li>This is</li><li>A <em>bulleted</em></li><li>List</li></ul>'
                        .'<ol><li>This is</li><li>A <em>numbered</em></li><li>List</li></ol>'
        ],
        [
          'title'  => __( 'Headings' ),
          'before' => "## Second-Level Heading\n\n### Third-Level Heading\n\n_First-level headings are "
                        ."reserved for the page title; please don't use them._",
          'after'  => '<h2>Second-Level Heading</h2><h3>Third-Level Heading</h3><p><em>First-level headings '
                        .'are reserved for the page title; please don\'t use them.</em></p>'
        ],
        [
          'title'  => __( 'Hyperlinks' ),
          'before' => '[Google](http://google.com) is the greatest search engine on the planet.',
          'after'  => '<a href="http://google.com">Google</a> is the greatest search engine on the planet.'
        ]
      ];

      $table = '';
      if ( !empty( $samples ) )
      {
        $rows = '';
        foreach ( $samples as $sample )
        {
          $rows .= sprintf(
            '<tr>'
              .'<td class="example-title" colspan="2"><h4>%1$s</h4></td>'
            .'</tr>'
            .'<tr>'
              .'<td class="before"><pre>%2$s</pre></td>'
              .'<td class="after">%3$s</td>'
            .'</tr>',
            $sample['title'],
            $sample['before'],
            $sample['after']
          );
        }
        $table = sprintf(
          '<table id="markdown-cheatsheet">'
            .'%s'
          .'</table>',
          $rows
        );
      }

      // Set arguments and add tab
      $args = [
        'id'      => 'markdown_help',
        'title'   => _x( 'Markdown', 'help tab' ),
        'content' => $content . $table
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
    return $this->parser->text( $content );
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

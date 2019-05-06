<?php

namespace ASMBS\WPParsedown;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ParsedownPlugin
{
    const BREAKS_ENABLED = false;
    const MARKUP_ESCAPED = false;
    const URLS_LINKED    = false;

    const IMG_SHORTCODE = 'image';

    /**
     * @var  \Parsedown
     */
    protected $parser;

    /**
     * @var  string
     */
    protected $rootPath;

    /**
     * @var  string
     */
    protected $rootUrl;

    /**
     * ParsedownPlugin constructor.
     *
     * @param  \Parsedown  $parser
     * @param  string      $rootDir
     */
    public function __construct(\Parsedown $parser, $rootDir = __DIR__)
    {
        // Initialize parser
        $this->parser = $parser;
        $parser->setBreaksEnabled(self::BREAKS_ENABLED)
               ->setMarkupEscaped(self::MARKUP_ESCAPED)
               ->setUrlsLinked(self::URLS_LINKED);

        // Set roots
        $this->rootPath = plugin_dir_path($rootDir);
        $this->rootUrl = plugin_dir_url($rootDir);

        // Register init action
        add_action('init', [$this, 'init']);

        $this->registerActionsAndFilters();
    }

    /**
     * Set action hooks.
     *
     * @return  $this
     */
    protected function registerActionsAndFilters()
    {
        // Disable the WYSIWYG editor globally
        add_filter('user_can_richedit', '__return_false', 100);

        // Enqueue plugin script for admin view
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);

        // Enqueue plugin script for public views
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicScripts']);

        // Register the image shortcode and use it in place of HTML when inserting media
        // into a post
        add_filter('image_send_to_editor', [$this, 'filterImageMarkup'], 100, 8);
        add_shortcode(self::IMG_SHORTCODE, [$this, 'parseImageShortcode']);

        // Add an "Image ID" field to the "Attachment Details" modal and the "Edit Media" page
        add_filter( 'attachment_fields_to_edit', [$this, 'addImageIDField'], null, 2 );

        // Ensure the user is using the [image] shortcode
        add_action( 'admin_notices', [$this, 'validatePostContent'] );

        return $this;
    }

    /**
     * Run on init; modifies the `the_content` filter registry.
     */
    public function init()
    {
        // Remove some core filters
        remove_filter('the_content', 'wpautop');
        remove_filter('the_content', 'convert_smilies');

        // Adjust filter priorities
        remove_filter('the_content', 'wptexturize');
        add_filter('the_content', 'wptexturize', 20);
        remove_filter('the_content', 'do_shortcode', 11);
        add_filter('the_content', 'do_shortcode', 13);

        // Add parsing filters
        add_filter('the_content', [$this, 'addBlockFlags'], 11);
        add_filter('the_content', [$this, 'parseContent'], 12);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the absolute path to a plugin file.
     *
     * @param   string  $path  Plugin-root-relative file path.
     * @return  string
     */
    protected function path($path = null)
    {
        if ($path && $path[0] === '/') {
            $path = substr($path, 1);
        }

        return $this->rootPath . $path;
    }

    /**
     * Get the URL to a plugin file.
     *
     * @param   string  $path  Plugin-root-relative file path.
     * @return  string
     */
    protected function url($path = null)
    {
        if ($path && $path[0] === '/') {
            $path = substr($path, 1);
        }

        return $this->rootUrl . $path;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Enqueue scripts in the editor.
     *
     * @param  string  $hook  Admin page hook.
     */
    public function enqueueAdminScripts($hook)
    {
        if (in_array($hook, ['post.php', 'post-new.php'])) {
            wp_enqueue_script('parsedown_js', $this->url('dist/scripts/admin.bundle.js'), ['jquery'], null, true);
        }
        wp_enqueue_style('parsedown_admin_css', $this->url('dist/styles/admin.css'), [], false);
    }

    public function enqueuePublicScripts($hook)
    {
        wp_enqueue_style( 'parsedown_admin_css', $this->url('dist/styles/main.css'), [], false);
    }

    /**
     * Convert image/figure markup to a shortcode when inserting into a post.
     *
     * @param   string  $html     Intended HTML output.
     * @param   string  $id       Attachment (post) ID of the image.
     * @param   string  $caption  Caption text.
     * @param   string  $title    Title attribute text.
     * @param   string  $align    Alignment class.
     * @param   string  $url      Link target, if the image is to be used as a link.
     * @param   string  $size     Size class.
     * @param   string  $alt      Alt text.
     * @return  string
     */
    public function filterImageMarkup($html, $id, $caption, $title, $align, $url, $size, $alt)
    {
        // Closure for appending attributes
        $appendAttribute = function(&$str, $key, $value) {
            $str .= sprintf(' %s="%s"', $key, $value);
        };

        // Add "optional" attributes
        $extraAttributes = '';
        if ($url) {
            $appendAttribute($extraAttributes, 'href', $url);
        }
        if ($caption) {
            $appendAttribute($extraAttributes. 'caption', $caption);
        }
        if ($alt) {
            $appendAttribute($extraAttributes, 'alt', $alt);
        }
        if ($title) {
            $appendAttribute($extraAttributes, 'title', $title);
        }

        return sprintf(
            '[%1$s id="%2$s" align="%3$s" size="%4$s"%5$s /]',
            self::IMG_SHORTCODE,
            $id,
            $align,
            $size,
            $extraAttributes
        );
    }

    /**
     * Parse image shortcodes.
     *
     * @param   array   $attrs    Attributes.
     * @param   string  $content  Content between opening and closing tags.
     * @return  string
     */
    public function parseImageShortcode(array $attrs, $content = '')
    {
        // Normalize attributes
        $attrs = shortcode_atts([
            'id'      => 0,
            'href'    => false,
            'alt'     => '',
            'caption' => false,
            'align'   => false,
            'width'   => 'auto',
            'size'    => 'large',
            'responsive-opt-out' => false,
            'max-width-opt-out' => false
        ], $attrs);

        /**
         * Allow filtering of image tag classes.
         *
         * @param string[] $imgClasses
         * @param array $attrs
         *
         * @return  string[]
         */
        $imgClasses = [ 'wppd-image-shortcode' ];
        if(!$attrs['max-width-opt-out']){
            $imgClasses[] = 'wppd-image-shortcode-responsive';
        }
        $imgClasses = apply_filters( 'parsedown/image/img_classes', $imgClasses, $attrs );

        // If we should do a modern responsive image tag (i.e. the user didn't opt-out)
        if(!$attrs['responsive-opt-out']){

            $img_tag_src    = wp_get_attachment_url( $attrs['id'] );
            $img_tag_srcset = wp_get_attachment_image_srcset( $attrs['id'] );
            $img_tag_sizes = wp_get_attachment_image_sizes($attrs['id'], 'full');

            $imgHtml = sprintf(
                '<img src="%1$s" srcset="%2$s" sizes="%3$s" alt="%4$s" style="width:%5$s;" class="%6$s">',
                esc_url( $img_tag_src ),
                esc_attr( $img_tag_srcset ),
                esc_attr( $img_tag_sizes ),
                $attrs['alt'],
                $attrs['width'],
                implode( ' ', $imgClasses )
            );

        } else {

            // Get image URL
            if ( $attrs['size'] == 'full' ) {
                $src = wp_get_attachment_url( $attrs['id'] );
            } else {
                $size = in_array( $attrs['size'], get_intermediate_image_sizes() ) ? $attrs['size'] : 'medium';
                $src  = wp_get_attachment_image_src( $attrs['id'], $size );
                if ( $src ) {
                    $src = $src[0];
                }
            }

            // Generate image tag
            $imgHtml = sprintf(
                '<img class="%4$s" src="%2$s" alt="%3$s">',
                $attrs['id'],
                $src,
                $attrs['alt'],
                implode( ' ', $imgClasses )
            );
        }

        if ($attrs['href']) {

            /**
             * Allow filtering of the image link URL.
             *
             * @param   string  $url
             * @param   array   $attrs
             * @return  string
             */
            $attrs['href'] = apply_filters('parsedown/image/link_href', $attrs['href'], $attrs);

            /**
             * Allow filtering of image link classes.
             *
             * @param   string[]  $linkClasses
             * @param   array     $attrs
             * @return  string[]
             */
            $linkClasses = apply_filters('parsedown/image/link_classes', [], $attrs);

            // Wrap image in an anchor
            $imgHtml = sprintf(
                '<a href="%1$s" class="%3$s">%2$s</a>',
                $attrs['href'],
                $imgHtml,
                implode(' ', $linkClasses)
            );
        }

        // Build caption if one is set
        $captionHtml = '';
        if ($attrs['caption']) {
            $captionHtml = sprintf('<figcaption>%s</figcaption>', $attrs['caption']);
        }

        // Set up figure element classes
        $figureClasses = ['img', 'img-'. $attrs['id']];
        if ($attrs['size']) {
            $figureClasses[] = 'img-size-'. $attrs['size'];
        }
        if ($attrs['align'] && in_array($attrs['align'], ['left', 'center', 'right'])) {
            $figureClasses[] = 'img-align-'. $attrs['align'];
        }

        /**
         * Filter figure classes.
         *
         * @param   string[]  $figureClasses
         * @param   array     $attrs
         * @return  string[]
         */
        $figureClasses = apply_filters('parsedown/image/figure_classes', $figureClasses, $attrs);

        $html = sprintf(
            '<figure class="%3$s">%1$s %2$s</figure>',
            $imgHtml,
            $captionHtml,
            implode(' ', $figureClasses)
        );

        /**
         * Filter the final output.
         *
         * @param   string  $html
         * @param   array   $attrs
         * @return  string
         */
        return apply_filters('parsedown/image/output', $html, $attrs);
    }

    /**
     * Adds an "Image ID" field to the "Attachment Details" modal and the "Edit Media" page.
     *
     * @param $form_fields
     * @param $post
     *
     * @return mixed
     */
    public function addImageIDField( $form_fields, $post ) {

        $form_fields['wppd_image_id'] = [
            'label' => __( 'Image ID' ),
            'input'  => 'html',
            'html' => '<span class="wppd-image-id">' . $post->ID . '</span>'
        ];

        return $form_fields;
    }

    // Adds notices if the post is being edited with non-shortcode image insertions.
    public function validatePostContent(){
        global $current_screen, $post;
        if ( $current_screen->parent_base == 'edit' ){
            // If the post content has images inserted with Markdown
            $matches = null;
            if(preg_match('/(?:!\[(.*?)\]\((.*?)\))/', $post->post_content, $matches)){
                echo '<div class="error"><p>Warning - Please use the [image] shortcode (not Markdown formatting) when inserting images.</p></div>';
            }
            // If the post content has images inserted with HTML
            $matches = null;
            if(preg_match('/&lt;img.*?&gt;/', $post->post_content, $matches)){
                echo '<div class="error"><p>Warning - Please use the [image] shortcode (not the HTML img tag) when inserting images.</p></div>';
            }
        }
    }

    /**
     * Run the parser.
     *
     * @param   string  $content  The text (Markdown) content.
     * @return  string
     */
    public function parseContent($content)
    {
        return $this->parser->text($content);
    }

    /**
     * Add `markdown="1"` flags to block-level elements in content. MUST be run _before_
     * parsing to ensure that block-nested Markdown can be parsed.
     *
     * @param   string  $content
     * @return  string
     */
    public function addBlockFlags($content)
    {
        // Set the list of elements to mark
        $blocks = [
            'address',
            'article',
            'aside',
            'blockquote',
            'dd',
            'div',
            'figcaption',
            'footer',
            'form',
            'header',
            'main',
            'nav',
            'noscript',
            'output',
            'section',
        ];

        $regex = sprintf('/< *(%s) *([^>]*)>/i', implode('|', $blocks));

        if (preg_match_all($regex, $content, $m, PREG_OFFSET_CAPTURE)) {
            // Set the flag attribute and get its length
            $flagAttr = ' markdown="1"';
            $flagLength = strlen($flagAttr);

            $attrs = $m[2];
            for ($i = 0; $i < count($attrs); $i++) {
                // Adjust the replacement offset at each iteration to account for the length of the
                // flag attribute added in the last iteration
                $offsetAdjust = $i * $flagLength;

                list($string, $pos) = $attrs[$i];
                // Append the flag
                $content = substr_replace($content, $string . $flagAttr, $pos + $offsetAdjust, strlen($string));
            }
        }

        return $content;
    }
}

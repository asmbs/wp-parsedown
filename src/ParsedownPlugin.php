<?php

namespace Ft6k\WpParsedown;

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

        // TODO: Add preview box hooks

        // Enqueue plugin scripts for editing view
        // TODO: implement script enqueuer
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);

        // Register the image shortcode and use it in place of HTML when inserting media
        // into a post
        // TODO: implement image shortcode and send filter
        add_filter('image_send_to_editor', [$this, 'filterImageMarkup'], 100, 8);
        add_shortcode(self::IMG_SHORTCODE, [$this, 'parseImageShortcode']);

        return $this;
    }

    /**
     * Run on init; modifies the `the_content` filter registry.
     */
    public function init()
    {
        remove_filter('the_content', 'wpautop');
        add_filter('the_content', [$this, 'parseContent'], 1);
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
            wp_enqueue_script('parsedown_js', $this->url('assets/scripts/dist/editor.min.js', ['jquery', 'ace_editor'], null));

            wp_enqueue_script('ace_editor', '//cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js', [], null);
            wp_enqueue_script('ace_markdown', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/mode-markdown.js', ['ace_editor'], null);

            wp_enqueue_style('parsedown_admin_css', $this->url('assets/styles/dist/admin.min.css'), [], false);
        }
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
            $appendAttribute($extraAttributes, 'url', $url);
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
        $src = wp_get_attachment_url($attrs['id']);

        // Generate image tag
        $imageHtml = sprintf(
            '<img id="image-%1$s" src="%3$s" alt="%2$s">',
            $attrs['id'],
            array_key_exists('alt', $attrs) ? $attrs['alt'] : '',
            $src
        );

        // If a URL is set, wrap the image in a link
        if (array_key_exists('url', $attrs)) {
            $imageHtml = sprintf('<a href="%s">%s</a>', $attrs['url'], $imageHtml);
        }

        // Build caption if one is set
        $captionHtml = '';
        if (array_key_exists('caption', $attrs)) {
            $captionHtml = sprintf('<figcaption>%s</figcaption>', $attrs['caption']);
        }

        $html = sprintf(
            '<figure id="figure-%1$s" class="image %2$s %3$s">%4$s %5$s</figure>',
            $attrs['id'],
            $attrs['align'],
            $attrs['size'],
            $imageHtml,
            $captionHtml
        );

        return apply_filters('parsedown/parse_image_shortcode', $html, $attrs);
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
}
<?php

namespace ASMBS\WPParsedown;

/**
 * Class ParsedownPlugin
 * @author Kyle Tucker <kyleatucker@gmail.com>
 * @package ASMBS\WPParsedown
 */
class ParsedownPlugin
{
    const BREAKS_ENABLED = false;
    const MARKUP_ESCAPED = false;
    const URLS_LINKED    = false;

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

    /** @var ImageShortcode */
    protected $imageShortcode;

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

        // Initialize ImageShortcode, if enabled
        if(SettingsPage::get_option('image_shortcode')){
            $this->imageShortcode = new ImageShortcode();
        }

        // Initialize the Settings page
        new SettingsPage();
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

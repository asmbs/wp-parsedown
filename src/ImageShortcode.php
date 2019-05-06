<?php

namespace ASMBS\WPParsedown;

/**
 * Handles the processing of the [image] shortcode, if enabled.
 *
 * Class ImageShortcode
 * @package ASMBS\WPParsedown
 */
class ImageShortcode {

    const IMG_SHORTCODE = 'image';

    /**
     * ImageShortcode constructor.
     */
    public function __construct() {
        $this->registerActionsAndFilters();
    }

    /**
     * Set action hooks.
     */
    protected function registerActionsAndFilters()
    {
        // Register the image shortcode and use it in place of HTML when inserting media
        // into a post
        add_filter('image_send_to_editor', [$this, 'filterImageMarkup'], 100, 8);
        add_shortcode(self::IMG_SHORTCODE, [$this, 'parseImageShortcode']);

        // Add an "Image ID" field to the "Attachment Details" modal and the "Edit Media" page
        if(SettingsPage::get_option('image_show_id')) {
            add_filter( 'attachment_fields_to_edit', [ $this, 'addImageIDField' ], null, 2 );
        }

        // Ensure the user is using the [image] shortcode
        add_action( 'admin_notices', [$this, 'validatePostContent'] );
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

    /**
     * Adds notices if the post is being edited with non-shortcode image insertions.
     */
    public function validatePostContent(){

        global $current_screen, $post;
        if ( $current_screen->parent_base == 'edit' && $post){
            if(SettingsPage::get_option('image_warn_markdown')) {
                // If the post content has images inserted with Markdown
                $matches = null;
                if ( preg_match( '/(?:!\[(.*?)\]\((.*?)\))/', $post->post_content, $matches ) ) {
                    echo '<div class="error"><p>Warning - Please use the [image] shortcode (not Markdown formatting) when inserting images.</p></div>';
                }
            }
            if(SettingsPage::get_option('image_warn_html')) {
                // If the post content has images inserted with HTML
                $matches = null;
                if ( preg_match( '/&lt;img.*?&gt;/', $post->post_content, $matches ) ) {
                    echo '<div class="error"><p>Warning - Please use the [image] shortcode (not the HTML img tag) when inserting images.</p></div>';
                }
            }
        }
    }
}
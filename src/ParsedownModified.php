<?php

namespace ASMBS\WPParsedown;

/**
 * @author Kyle Tucker <kyleatucker@gmail.com>
 */
class ParsedownModified extends \ParsedownExtra
{
    public const HTML_REGEX = '/<\/?(?:\w[\w-]*)(?:[ ]*[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?)*[ ]*(?:\/)?>/';

    function __construct()
    {
        parent::__construct();

        # identify shortcode definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'Shortcode');

        # identify shortcode markers before before links
        array_unshift($this->InlineTypes['['], 'ShortcodeMarker');

        /**
         * Remove `a` from the list of text-level elements.
         * An anchor tag should be able to span multiple lines, per CommonMark spec.
         * @see https://spec.commonmark.org/0.27/#example-128
         * This line won't be needed once Parsedown v1.8 is out of beta.
         */
        unset($this->textLevelElements[array_search('a', $this->textLevelElements)]);
    }

    #
    # Overrides

    function text($text)
    {
        $text = $this->forceHTMLCompliance($text);
        return parent::text($text);
    }

    protected function blockCode($Line, $Block = null)
    {
        return;
    }

    protected function blockCodeContinue($Line, $Block)
    {
        return;
    }

    protected function blockCodeComplete($Block)
    {
        return;
    }

    #
    # Shortcode

    protected function blockShortcode($Line)
    {
        global $shortcode_tags;
        $shortcodeRegExSegment = implode('|', array_keys($shortcode_tags));
        if (preg_match_all('/\[\/?(?:' . $shortcodeRegExSegment .')(?:[^][]++|(?R))*+\](.*\[\/.*\])?/', $Line['text'], $matches))
        {
            $Block = array(
                'markup' => implode('', $matches[0])
            );

            return $Block;
        }
    }

    protected function blockShortcodeContinue($Line, $Block)
    {
        // No-op: Shortcodes should never continue across lines
        return;
    }

    protected function blockShortcodeComplete($Block)
    {
        return $Block;
    }

    #
    # Shortcode Marker

    protected function inlineShortcodeMarker($Excerpt)
    {
        global $shortcode_tags;
        $shortcodeRegExSegment = implode('|', array_keys($shortcode_tags));
        if (preg_match('/\[\/?(?:' . $shortcodeRegExSegment .')(?:[^][]++|(?R))*+\]/', $Excerpt['text'], $matches))
        {
            return array(
                'extent' => strlen($matches[0]),
                'markup' => $matches[0]
            );
        }
    }

    #
    # HTML

    /**
     * Users may paste HTML that is not compliant to the CommonMark spec
     * (i.e. each individual HTML tag may not be on a new line).
     * To reduce the burden on the users, we preprocess the text to force CommonMark-compliance
     * on any HTML in the text.
     *
     * @see https://spec.commonmark.org/0.28/#html-blocks
     *
     * @param $text
     *
     * @return mixed
     */
    protected function forceHTMLCompliance($text)
    {
        // Find all the HTML tags (both opening and closing)
        $matches = null;
        preg_match_all(self::HTML_REGEX, $text, $matches, PREG_OFFSET_CAPTURE);

        // Loop through all of the matched HTML tags
        $addedOffset = 0;
        foreach($matches[0] as $match){
            // Add a line break into the text at the end of the matched tag
            $endPositionOfTag = $match[1] + \strlen($match[0]) + $addedOffset;
            $text = substr_replace($text, PHP_EOL, $endPositionOfTag, 0);
            $addedOffset += \strlen(PHP_EOL);
        }
        return $text;
    }

}

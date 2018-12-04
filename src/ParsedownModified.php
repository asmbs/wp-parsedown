<?php

namespace ASMBS\WPParsedown;

/**
 * @author Kyle Tucker <kyleatucker@gmail.com>
 */
class ParsedownModified extends \ParsedownExtra
{
    function __construct()
    {
        parent::__construct();

        # identify shortcode definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'Shortcode');

        # identify shortcode markers before before links
        array_unshift($this->InlineTypes['['], 'ShortcodeMarker');
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
        if (preg_match_all('/\[\/?(?:' . $shortcodeRegExSegment .')(?:[^][]++|(?R))*+\]/', $Line['text'], $matches))
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
}

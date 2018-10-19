<?php

namespace ASMBS\WPParsedown;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ParsedownModified extends \ParsedownExtra
{
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
}

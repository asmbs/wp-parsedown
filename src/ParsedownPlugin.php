<?php

namespace Ft6k\WpParsedown;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ParsedownPlugin
{
    /**
     * @var  \Parsedown
     */
    protected $parser;

    public function __construct(\Parsedown $parser)
    {
        $this->parser = $parser;
    }
}

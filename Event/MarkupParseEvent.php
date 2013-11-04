<?php

namespace Anh\MarkupBundle\Event;

class MarkupParseEvent extends MarkupEvent
{
    /**
     * Parsed text
     */
    protected $text;

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }
}

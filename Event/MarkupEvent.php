<?php

namespace Anh\Bundle\MarkupBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MarkupEvent extends Event
{
    const CREATE = 'anh_markup.create';
    const PARSE = 'anh_markup.parse';
    const VALIDATE = 'anh_markup.validate';

    protected $type;
    protected $markup;
    protected $options;

    /**
     * Internal parser
     */
    protected $parser;

    public function __construct($type, $markup, $options)
    {
        $this->type = $type;
        $this->markup = $markup;
        $this->options = $options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMarkup()
    {
        return $this->markup;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
    }
}

<?php

namespace Anh\MarkupBundle\Event;

class MarkupCommandEvent extends MarkupEvent
{
    protected $command;

    public function __construct($command, $type, $markup = '', $options = array())
    {
        $this->command = $command;
        parent::__construct($type, $markup, $options);
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}

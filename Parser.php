<?php

namespace Anh\Bundle\MarkupBundle;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Anh\Bundle\MarkupBundle\Event\MarkupEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupCreateEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupParseEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupValidateEvent;

class Parser
{
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function create($type, $markup, $options)
    {
        $event = new MarkupCreateEvent($type, $markup, $options);
        $this->dispatcher->dispatch(MarkupEvent::CREATE, $event);

        return $event->getParser();
    }

    public function parse($type, $markup, $options)
    {
        $parser = $this->create($type, $markup, $options);

        $event = new MarkupParseEvent($type, $markup, $options);
        $event->setParser($parser);

        $this->dispatcher->dispatch(MarkupEvent::PARSE, $event);

        return $event->getText();
    }

    public function validate($type, $markup, $options)
    {
        $parser = $this->create($type, $markup, $options);

        $event = new MarkupValidateEvent($type, $markup, $options);
        $event->setParser($parser);

        $this->dispatcher->dispatch(MarkupEvent::VALIDATE, $event);

        return $event->getErrors();
    }
}

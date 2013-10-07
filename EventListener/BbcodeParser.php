<?php

namespace Anh\Bundle\MarkupBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Anh\Bundle\MarkupBundle\Event\MarkupEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupCreateEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupParseEvent;
use Anh\Bundle\MarkupBundle\Event\MarkupValidateEvent;
use Decoda\Decoda;

class BbcodeParser implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            MarkupEvent::CREATE => 'onCreate',
            MarkupEvent::PARSE => 'onParse',
            MarkupEvent::VALIDATE => 'onValidate'
        );
    }

    public function onCreate(MarkupCreateEvent $event)
    {
        if ($event->getType() != 'bbcode') {
            return;
        }

        $decoda = new Decoda($event->getMarkup(), $event->getOptions());
        $decoda->defaults();

        $event->setParser($decoda);
    }

    public function onParse(MarkupParseEvent $event)
    {
        if ($event->getType() != 'bbcode') {
            return;
        }

        $decoda = $event->getParser();
        $decoda->reset($event->getMarkup());
        $decoda->setConfig($event->getOptions());
        $text = $decoda->parse();

        $event->setText($text);
    }

    public function onValidate(MarkupValidateEvent $event)
    {
        if ($event->getType() != 'bbcode') {
            return;
        }

        $decoda = $event->getParser();
        $decoda->parse();

        $errors = (array) $decoda->getErrors();

        $nesting = array();
        $closing = array();
        $scope = array();

        foreach ($errors as $error) {
            switch ($error['type']) {
                case Decoda::ERROR_NESTING:
                    $nesting[] = $error['tag'];
                    break;

                case Decoda::ERROR_CLOSING:
                    $closing[] = $error['tag'];
                    break;

                case Decoda::ERROR_SCOPE:
                    $scope[] = $error['child'] . ' in ' . $error['parent'];
                    break;
            }
        }

        $errors = array();

        if (!empty($nesting)) {
            $errors[] = sprintf('The following tags have been nested in the wrong order: %s', implode(', ', $nesting));
        }

        if (!empty($closing)) {
            $errors[] = sprintf('The following tags have no closing tag: %s', implode(', ', $closing));
        }

        if (!empty($scope)) {
            $errors[] = sprintf('The following tags can not be placed within a specific tag: %s', implode(', ', $scope));
        }

        $event->setErrors($errors);
    }
}

<?php

namespace Anh\MarkupBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Anh\MarkupBundle\Parser;
use Anh\MarkupBundle\Mapping\Annotation\Parsable;
use Anh\MarkupBundle\Mapping\Annotation\Countable;

class MarkupListener implements EventSubscriber
{
    /**
     * Annotation reader service
     */
    protected $reader;

    /**
     * Parser
     */
    protected $parser;

    public function __construct($reader, Parser $parser)
    {
        $this->reader = $reader;
        $this->parser = $parser;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
        );
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();
        $unitOfWork = $manager->getUnitOfWork();

        $entities = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            $meta = $manager->getClassMetadata(get_class($entity));

            if ($this->processEntity($entity, $meta)) {
                $unitOfWork->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    protected function processEntity($entity, $meta)
    {
        $annotations = $this->getAnnotations($entity);

        if (empty($annotations)) {
            return false;
        }

        foreach ($annotations as $fieldName => $annotation) {
            $markup = $meta
                ->getReflectionProperty($annotation->field)
                ->getValue($entity)
            ;

            $options = (array) $annotation->options + array(
                'entity' => $entity,
            );

            switch (true) {
                case $annotation instanceof Parsable:
                    $value = $this->parser->parse($annotation->type, $markup, $options);
                    break;

                case $annotation instanceof Countable:
                    $value = $this->parser->command('countChars', $annotation->type, $markup, $options);
                    break;

                default:
                    throw new \Exception(
                        sprintf("Unknown annotation '%s'.", get_class($annotation))
                    );
                    break;
            }

            $meta->getReflectionProperty($fieldName)->setValue($entity, $value);
        }

        return true;
    }

    private function getAnnotations($entity)
    {
        $annotations = array();

        $reflection = new \ReflectionObject($entity);

        foreach ($reflection->getProperties() as $property) {
            $annotation = array_filter(
                $this->reader->getPropertyAnnotations($property),
                function ($annotation) {
                    return $annotation instanceof Parsable || $annotation instanceof Countable;
                }
            );

            if (!empty($annotation)) {
                $annotations[$property->getName()] = reset($annotation);
            }
        }

        return $annotations;
    }
}

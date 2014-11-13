<?php

namespace Anh\MarkupBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
            Events::postPersist
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $meta = $args->getEntityManager()
            ->getClassMetadata(get_class($entity))
        ;

        if ($this->process($entity, $meta)) {
            $args->getEntityManager()->flush($entity);
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $meta = $em->getClassMetadata(get_class($entity));
            if ($this->process($entity, $meta)) {
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    protected function process($entity, $meta)
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
                'entity' => $entity
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

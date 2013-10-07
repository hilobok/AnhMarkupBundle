<?php

namespace Anh\Bundle\MarkupBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Anh\Bundle\MarkupBundle\Parser;

class Parsable implements EventSubscriber
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
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        $entityClass = get_class($entity);

        $meta = $em->getClassMetadata($entityClass);

        $annotations = $this->getAnnotations($entity);
        $entityMasterFields = method_exists($entity, 'getMarkupMasterFields') ? $entity->getMarkupMasterFields() : array();

        foreach ($annotations as $fieldName => $annotation) {
            $markup = $meta->getReflectionProperty($annotation->field)->getValue($entity);

            $options = (array) $annotation->options + array(
                'extra' => $entityMasterFields
            );

            $text = $this->parser->parse($annotation->type, $markup, $options);
            $meta->getReflectionProperty($fieldName)->setValue($entity, $text);
        }
   }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $annotations = $this->getAnnotations($entity);

            $entityClass = get_class($entity);

            $meta = $em->getClassMetadata($entityClass);

            $entityMasterFields = method_exists($entity, 'getMarkupMasterFields') ? $entity->getMarkupMasterFields() : array();

            $changedFields = array_keys($uow->getEntityChangeSet($entity));

            foreach ($annotations as $fieldName => $annotation) {
                $masterFields = array_merge(
                    array_keys($entityMasterFields),
                    array($annotation->field)
                );

                if (!array_intersect($masterFields, $changedFields)) {
                    continue;
                }

                $markup = $meta->getReflectionProperty($annotation->field)->getValue($entity);

                $options = (array) $annotation->options + array(
                    'extra' => $entityMasterFields
                );

                $text = $this->parser->parse($annotation->type, $markup, $options);

                $meta->getReflectionProperty($fieldName)->setValue($entity, $text);

                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    private function getAnnotations($entity)
    {
        $annotations = array();

        $reflection = new \ReflectionObject($entity);

        foreach ($reflection->getProperties() as $field) {
            $annotation = $this->reader->getPropertyAnnotation(
                $field,
                'Anh\Bundle\MarkupBundle\Mapping\Annotation\Parsable'
            );

            if ($annotation !== null) {
                $annotations[$field->getName()] = $annotation;
            }
        }

        return $annotations;
    }
}

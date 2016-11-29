<?php

namespace Fizz\ObjectLoggerBundle\Log;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Fizz\ObjectLoggerBundle\Entity\EntityLog;
use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogModel;
use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogUpdateModel;
use Fizz\ObjectLoggerBundle\Log\Processor\EntityLogProcessorInterface;

/**
 * Listens to the most important entity events and dispatches
 * signals to entity log processors.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class EntityLogSubscriber implements EventSubscriber
{

    /**
     * @var EntityLogProcessorInterface[] $processors
     */
    private $processors;


    /**
     * Constructor.
     *
     * @param ArrayCollection $processors
     */
    public function __construct(ArrayCollection $processors)
    {
        $this->processors = $processors;
    }

    /**
     * PrePersist.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $model = new ObjectLogModel($args->getObject(), 'insert');
        foreach($this->processors as $processor) {
            if($processor->supports($model)) {
                $processor->process($model);
            }
        }
    }

    /**
     * PreUpdate.
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $changedFields = array();
        $object = $args->getObject();
        // We are sure the object manager is an EntityManager, because we only support these for now.
        $om = $args->getObjectManager();
        $md = $om->getClassMetadata(get_class($object));
        foreach($md->getFieldNames() as $field) {
            if($args->hasChangedField($field)) {
                $changedFields[$field] = array(
                    'old' => $args->getOldValue($field),
                    'new' => $args->getNewValue($field),
                );
            }
        }

        $model = new ObjectLogUpdateModel($args->getObject(), 'update', $changedFields);
        foreach($this->processors as $processor) {
            if($processor->supports($model)) {
                $processor->process($model);
            }
        }
    }

    /**
     * PreRemove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $model = new ObjectLogModel($args->getObject(), 'remove');
        foreach($this->processors as $processor) {
            if($processor->supports($model)) {
                $processor->process($model);
            }
        }
    }

    /**
     * PostFlush.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $scheduledInsertions = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions();
        if(!count($scheduledInsertions)) {
            return;
        }
        do {
            $checkedEntity = array_shift($scheduledInsertions);
        } while($checkedEntity instanceof EntityLog);
        if(!count($scheduledInsertions)) {
            $args->getEntityManager()->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'preRemove',
            'postFlush',
        );
    }

}
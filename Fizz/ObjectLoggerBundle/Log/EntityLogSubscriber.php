<?php

namespace Fizz\ObjectLoggerBundle\Log;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
     * @var array $entityLogs
     */
    private $entityLogs = array();


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
     * OnFlush.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if(count($this->entityLogs)) {
            $this->entityLogs = array();
        } else {
            $scheduledInsertions = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions();
            foreach ($scheduledInsertions as $oid => $insertion) {
                if ($insertion instanceof EntityLog) {
                    $args->getEntityManager()->remove($insertion);
                    $this->entityLogs[] = $insertion;
                }
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
        if(!count($this->entityLogs)) {
            return;
        }
        // This ugly bit allows us to skip one doctrine transaction and start
        // a separate one just for the logs in the previous one.
        foreach($this->entityLogs as $entityLog) {
            $copy = new EntityLog();
            $copy
                ->setObjectClass($entityLog->getObjectClass())
                ->setObjectIdentifier($entityLog->getObjectIdentifier())
                ->setExtra($entityLog->getExtra())
                ->setIsTranslated($entityLog->getIsTranslated())
                ->setMessage($entityLog->getMessage())
                ->setTranslationDomain($entityLog->getTranslationDomain())
                ->setTranslationParameters($entityLog->getTranslationParameters());
            $args->getEntityManager()->persist($copy);
        }
        $args->getEntityManager()->flush();
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
            'onFlush',
            'postFlush',
        );
    }

}
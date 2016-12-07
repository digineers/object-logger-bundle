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
     * Constructor.
     *
     * @param ArrayCollection $processors
     */
    public function __construct(ArrayCollection $processors)
    {
        $this->processors = $processors;
    }

    /**
     * OnFlush.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach($uow->getScheduledEntityInsertions() as $insertion) {
            $model = new ObjectLogModel($insertion, 'insert');
            foreach($this->processors as $processor) {
                if($processor->supports($model)) {
                    $processor->process($model);
                }
            }
        }

        foreach($uow->getScheduledEntityUpdates() as $update) {
            $changedFields = array();
            $originalData = $uow->getOriginalEntityData($update);
            foreach($uow->getEntityChangeSet($update) as $field => $value) {
                if(is_array($value) && array_key_exists(0, $value) && array_key_exists(1, $value) && '' === $value[0] && null === $value[1] && $value[1] === $originalData[$field]) {
                    continue;
                }
                $changedFields[$field] = array(
                    'old' => $originalData[$field],
                    'new' => $value,
                );
            }
            if(!count($changedFields)) {
                continue;
            }
            $model = new ObjectLogUpdateModel($update, 'update', $changedFields);
            foreach($this->processors as $processor) {
                if($processor->supports($model)) {
                    $processor->process($model);
                }
            }
        }

        foreach($uow->getScheduledEntityDeletions() as $deletion) {
            $model = new ObjectLogModel($deletion, 'remove');
            foreach($this->processors as $processor) {
                if($processor->supports($model)) {
                    $processor->process($model);
                }
            }
        }

        $uow->computeChangeSets();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
        );
    }

}
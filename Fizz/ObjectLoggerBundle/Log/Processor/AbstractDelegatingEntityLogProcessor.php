<?php

namespace Fizz\ObjectLoggerBundle\Log\Processor;


use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogModel;
use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogUpdateModel;

/**
 * Implements the process method from the EntityLogProcessorInterface
 * and delegates specific object log model types to uniquely implemented
 * corresponding methods (insert, update, delete) (optional)
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
abstract class AbstractDelegatingEntityLogProcessor implements EntityLogProcessorInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ObjectLogModel $model)
    {
        if('insert' === $model->getType()) {
            $this->insert($model);
            return;
        }
        if('update' === $model->getType() && $model instanceof ObjectLogUpdateModel) {
            $this->update($model);
            return;
        }
        if('remove' === $model->getType()) {
            $this->remove($model);
            return;
        }
    }

    /**
     * Optional delegated method: insert.
     *
     * @param ObjectLogModel $model
     */
    public function insert(ObjectLogModel $model) { }

    /**
     * Optional delegated method: update.
     *
     * @param ObjectLogUpdateModel $model
     */
    public function update(ObjectLogUpdateModel $model) { }

    /**
     * Optional delegated method: remove.
     *
     * @param ObjectLogModel $model
     */
    public function remove(ObjectLogModel $model) { }

}
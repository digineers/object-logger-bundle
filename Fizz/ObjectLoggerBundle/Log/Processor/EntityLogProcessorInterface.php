<?php

namespace Fizz\ObjectLoggerBundle\Log\Processor;

use Fizz\ObjectLoggerBundle\Log\Model\ObjectLogModel;

/**
 * Implements default methods we expect entity log processors to have.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
interface EntityLogProcessorInterface
{

    /**
     * Process an entity.
     *
     * @param ObjectLogModel $model
     */
    public function process(ObjectLogModel $model);

    /**
     * Check if a model is supported.
     *
     * @param ObjectLogModel $model
     * @return bool
     */
    public function supports(ObjectLogModel $model);

}
<?php

namespace Fizz\ObjectLoggerBundle\DependencyInjection\Compiler;

use Fizz\ObjectLoggerBundle\Log\Processor\EntityLogProcessorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds entity log processors to the collection.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class EntityLogProcessorPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $collectionDefinition = $container->findDefinition('fizz.object_logger.processor_collection');
        $taggedServices = $container->findTaggedServiceIds('fizz.object_logger.processor');

        foreach($taggedServices as $id => $arguments) {
            $collectionDefinition->addMethodCall('add', array(new Reference($id)));
        }
    }

}
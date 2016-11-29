<?php

namespace Fizz\ObjectLoggerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Loads entity logger services into the container.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class FizzObjectLoggerExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('fizz.object_logger.enabled_entities', $config['enabled_entities']);
        $container->setParameter('fizz.object_logger.discriminator_mapping', $config['discriminator_mapping']);
        $container->setParameter('fizz.object_logger.ignored_fields', $config['ignored_fields']);

        if(!$config['enable_default']) {
            $processor = $container->findDefinition('fizz.object_logger.processor.default');
            if($processor->hasTag('fizz.object_logger.processor')) {
                $processor->clearTag('fizz.object_logger.processor');
            }
        }
        if(!$config['save_user']) {
            $listener = $container->findDefinition('fizz.object_logger.processor.event_listener.user_extra');
            if($listener->hasTag('kernel.event_listener')) {
                $listener->clearTag('kernel.event_listener');
            }
        }
    }

}
<?php

namespace Fizz\ObjectLoggerBundle;

use Fizz\ObjectLoggerBundle\DependencyInjection\Compiler\EntityLogProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Entity logging
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class FizzObjectLoggerBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EntityLogProcessorPass());
    }

}
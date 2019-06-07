<?php

declare(strict_types=1);

namespace App\Container\Work;

use App\Twig\Extension\Work\Processor\ProcessorExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(ProcessorExtension::class);

        $services = $container->findTaggedServiceIds('app.twig.work_processor.driver');

        $references = [];
        foreach ($services as $id => $attributes) {
            $references[] = new Reference($id);
        }

        $definition->setArgument(0, $references);
    }
}

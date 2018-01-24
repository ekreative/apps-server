<?php

namespace Ekreative\TestBuild\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EkreativeTestBuildApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (in_array($container->getParameter('kernel.environment'), ['test'])) {
            $definition = new Definition(\Ekreative\TestBuild\ApiBundle\Fixtures\Loader::class, [
                new Reference('doctrine'),
                $container->getParameter('sqlite_path'),
                $container->getParameter('sqlite_backup'),
            ]);
            $container->addDefinitions([
                'ekreative_test_build_api.fixtures' => $definition,
            ]);
        }
    }
}

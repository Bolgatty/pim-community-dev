<?php

namespace Bolgatty\WorkFlowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class WorkFlowExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        // $loader->load('command.yml');
        
        $loader->load('datagrid.yml');
        $loader->load('entities.yml');
        $loader->load('normalizers.yml');
        $loader->load('removers.yml');
        $loader->load('savers.yml');
        $loader->load('merger.yml');
        $loader->load('controllers.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('factories.yml');
        $loader->load('managers.yml');
        $loader->load('twig.yml');
        $loader->load('presenters.yml');
        $loader->load('event_subscribers.yml');

        // $version = $versionClass::VERSION;
        // $versionDirectoryPrefix = '2.x/';
     
       

    }
}

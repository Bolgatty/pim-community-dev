<?php
namespace Bolgatty\WorkFlowBundle;

use Bolgatty\WorkFlowBundle\DependencyInjection\Compiler\RegisterProductDraftPresentersPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class WorkFlowBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
       $container
            ->addCompilerPass(new RegisterProductDraftPresentersPass())
        ;
    }
}

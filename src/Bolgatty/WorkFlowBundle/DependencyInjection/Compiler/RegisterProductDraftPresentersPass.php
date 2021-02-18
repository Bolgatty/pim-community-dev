<?php
namespace Bolgatty\WorkFlowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register product draft value presenters into the product draft twig extension
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class RegisterProductDraftPresentersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('bolgatty.twig.extension.product_draft_changes')) {
            return;
        }

        $definition = $container->getDefinition('bolgatty.twig.extension.product_draft_changes');
        foreach ($container->findTaggedServiceIds('bolgatty_workflow.presenter') as $id => $attribute) {
            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall(
                'addPresenter',
                [
                    new Reference($id),
                    isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                ]
            );
        }
    }
}

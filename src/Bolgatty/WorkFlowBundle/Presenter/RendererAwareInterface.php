<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Bolgatty\WorkFlowBundle\Rendering\RendererInterface;

/**
 * Provides renderer capabilities
 */
interface RendererAwareInterface
{
    /**
     * Set the renderer
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer);
}

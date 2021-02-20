<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Bolgatty\WorkFlowBundle\Rendering\RendererInterface;

/**
 * Provides renderer capabilities default implementation
 */
trait RendererAware
{
    /** @var RendererInterface */
    protected $renderer;

    /**
     * Set the renderer
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
}

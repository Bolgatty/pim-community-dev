<?php
namespace Bolgatty\WorkFlowBundle\Rendering;

/**
 * Diff renderer based on the PHP-Diff library
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class PhpDiffRenderer implements RendererInterface
{
    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var DiffFactory */
    protected $factory;

    /**
     * @param \Diff_Renderer_Html_Array $renderer
     * @param DiffFactory               $factory
     */
    public function __construct(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->renderer = $renderer;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function renderDiff($before, $after)
    {
        return $this->factory->create($before, $after)->render($this->renderer);
    }
}

<?php
namespace Bolgatty\WorkFlowBundle\Rendering;

/**
 * A value diff renderer
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
interface RendererInterface
{
    /**
     * Render differences between two variables
     *
     * @param mixed $before
     * @param mixed $after
     *
     * @return string
     */
    public function renderDiff($before, $after);
}

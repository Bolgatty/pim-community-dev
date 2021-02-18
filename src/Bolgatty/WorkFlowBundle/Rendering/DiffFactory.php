<?php
namespace Bolgatty\WorkFlowBundle\Rendering;

/**
 * A \Diff instance factory
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DiffFactory
{
    /**
     * Instanciate a configured Diff
     *
     * @param string|array $before
     * @param string|array $after
     * @param array        $options
     *
     * @return \Diff
     */
    public function create($before, $after, array $options = [])
    {
        $before = is_array($before) ? $before : [$before];
        $after = is_array($after) ? $after : [$after];
        
        return new \Diff($before, $after, $options);
    }
}

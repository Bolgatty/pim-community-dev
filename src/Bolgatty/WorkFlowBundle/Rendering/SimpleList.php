<?php
namespace Bolgatty\WorkFlowBundle\Rendering;

/**
 * HTML list-based diff renderer
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class SimpleList extends \Diff_Renderer_Html_Array
{
    /**
     * Render a diff in a HTML list (<ul>) element
     *
     * @return string
     */
    public function render()
    {
        $changes = parent::render();

        $result = ['before' => [], 'after' => []];

        foreach ($changes as $blocks) {
            foreach ($blocks as $change) {
                $before = $change['base']['lines'];
                $after = $change['changed']['lines'];

                $result['before'][] = is_array($before) ? implode(', ', $before) : $before;
                $result['after'][] = is_array($after) ? implode(', ', $after) : $after;
            }
        }
        $result['before'] = implode(', ', array_filter($result['before']));
        $result['after'] = implode(', ', array_filter($result['after']));

        return $result;
    }
}

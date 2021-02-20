<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

abstract class AbstractProductValuePresenter implements PresenterInterface, RendererAwareInterface
{
    use RendererAware;

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        return $this->renderer->renderDiff(
            $this->normalizeData($formerData),
            $this->normalizeChange($change)
        );
    }

    /**
     * Normalize data
     *
     * @return array|string
     */
    protected function normalizeData($data)
    {
        return [];
    }

    /**
     * Normalize change
     *
     * @param array $change
     *
     * @return array|string
     */
    protected function normalizeChange(array $change)
    {
        return [];
    }
}

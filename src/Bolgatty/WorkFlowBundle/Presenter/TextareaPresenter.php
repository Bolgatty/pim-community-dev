<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Present textarea data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class TextareaPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::TEXTAREA === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $this->explodeText($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $this->explodeText($change['data']);
    }

    /**
     * Explode text into separated paragraphs
     *
     * @param string $text
     *
     * @return array
     */
    protected function explodeText($text)
    {
        preg_match_all('/<p>(.*?)<\/p>/', $text, $matches);

        return !empty($matches[0]) ? $matches[0] : [$text];
    }
}

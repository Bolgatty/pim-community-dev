<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Present images side by side
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ImagePresenter extends FilePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return $attributeType === AttributeTypes::IMAGE;
    }

    /**
     * Create a file element
     *
     * @param string $fileKey
     * @param string $originalFilename
     *
     * @return string
     */
    protected function createFileElement($fileKey, $originalFilename)
    {
        return sprintf(
            '<img src="%s" title="%s" />',
            $this->generator->generate(
                'pim_enrich_media_show',
                [
                    'filename' => urlencode($fileKey),
                    'filter'   => 'thumbnail',
                ]
            ),
            $originalFilename
        );
    }
}

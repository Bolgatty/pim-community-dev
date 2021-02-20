<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Present two files information side by side
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class FilePresenter implements PresenterInterface
{
    /** @var UrlGeneratorInterface */
    protected $generator;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    public function __construct(
        UrlGeneratorInterface $generator,
        FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->generator = $generator;
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return $attributeType === AttributeTypes::FILE;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $result = ['before' => '', 'after' => ''];

        $originalMedia = $formerData;
        $changedMedia  = isset($change['data']) ? $this->fileInfoRepository->findOneByIdentifier($change['data']) : null;

        if (!$this->hasChanged($changedMedia, $originalMedia)) {
            return $result;
        }

        if (null !== $originalMedia && null !== $originalMedia->getKey() && null !== $originalMedia->getOriginalFilename()) {
            $result['before'] = $this->createFileElement($originalMedia->getKey(), $originalMedia->getOriginalFilename());
        }

        if (null !== $changedMedia && null !== $changedMedia->getKey() && null !== $changedMedia->getOriginalFilename()) {
            $result['after'] = $this->createFileElement($changedMedia->getKey(), $changedMedia->getOriginalFilename());
        }

        return $result;
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
            '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
            $this->generator->generate('pim_enrich_media_show', ['filename' => urlencode($fileKey)]),
            $originalFilename
        );
    }

    /**
     * Check diff between old and new file
     *
     * @param FileInfoInterface|null $changedMedia
     * @param FileInfoInterface|null $originalMedia
     * @return bool
     */
    protected function hasChanged(FileInfoInterface $changedMedia = null, FileInfoInterface $originalMedia = null)
    {
        $originalHash = null !== $originalMedia ? $originalMedia->getHash() : null;
        $changedHash  = null !== $changedMedia  ? $changedMedia->getHash()  : null;

        return $originalHash !== $changedHash;
    }
}

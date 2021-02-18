<?php
namespace Bolgatty\WorkFlowBundle\Presenter\ReferenceData;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Bolgatty\WorkFlowBundle\Presenter\AbstractProductValuePresenter;

/**
 * Abstract Present changes of reference data
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
abstract class AbstractReferenceDataPresenter extends AbstractProductValuePresenter
{
    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /** @var string */
    protected $referenceDataName;

    public function __construct(
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return parent::supports($attributeType) && $this->referenceDataName === $referenceDataName;
    }
}

<?php
namespace Bolgatty\WorkFlowBundle\Twig;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Bolgatty\WorkFlowBundle\Presenter\PresenterInterface;
use Bolgatty\WorkFlowBundle\Presenter\RendererAwareInterface;
use Bolgatty\WorkFlowBundle\Presenter\TranslatorAwareInterface;
use Bolgatty\WorkFlowBundle\Rendering\RendererInterface;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Twig extension to present product draft changes
 *
 * @author Firoj Ahmad <gildas@akeneo.com>
 */
class ProductDraftChangesExtension extends \Twig_Extension
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var [] */
    protected $presenters = [];

    /** @var \Twig_Environment */
    protected $twig;

    /** @var ValueFactory */
    protected $valueFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param                      $renderer
     * @param TranslatorInterface                   $translator
     * @param ValueFactory                          $valueFactory
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        $renderer,
        TranslatorInterface $translator,
        ValueFactory $valueFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->renderer = $renderer;
        $this->translator = $translator;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'present_product_draft_change',
                [$this, 'presentChange'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Present an attribute change
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $change
     * @param string                               $attributeCode
     *
     * @return string
     *@throws \LogicException
     *
     * @throws \InvalidArgumentException
     */
    public function presentChange(EntityWithValuesDraftInterface $productDraft, array $change, string $attributeCode)
    {
        
        $formerValue = $productDraft
            ->getEntityWithValue()
            ->getValue($attributeCode, $change['locale'], $change['scope']);
        $formerData = (null !== $formerValue) ? $formerValue->getData() : null;        
        $data = $this->present($attributeCode, $formerData, $change);
        
        return $data;
    }

    /**
     * Add a presenter
     *
     * @param  $presenter
     * @param int                $priority
     */
    public function addPresenter($presenter, $priority)
    {
        $this->presenters[$priority][] = $presenter;
    }

    /**
     * Get the registered presenters
     *
     * @return []
     */
    public function getPresenters()
    {
        krsort($this->presenters);
        $presenters = [];
        foreach ($this->presenters as $groupedPresenters) {
            $presenters = array_merge($presenters, $groupedPresenters);
        }

        return $presenters;
    }

    protected function present(string $attributeCode, $data, array $change)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        foreach ($this->getPresenters() as $presenter) {
          
            if ($presenter->supports($attribute->getType(), $attribute->getReferenceDataName())) {
                if ($presenter instanceof TranslatorAwareInterface) {
                    $presenter->setTranslator($this->translator);
                }
               
                if ($presenter instanceof RendererAwareInterface) {
                    $presenter->setRenderer($this->renderer);
                }
                
                return $presenter->present($data, array_merge($change, [
                    'attribute' => $attributeCode,
                    'reference_data_name' => $attribute->getReferenceDataName()
                ]));
            }
        }

        throw new \LogicException(
            sprintf(
                'No presenter supports the provided change with key(s) "%s"',
                implode(', ', array_keys($change))
            )
        );
    }
}

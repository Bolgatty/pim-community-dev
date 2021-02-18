<?php
namespace Bolgatty\WorkFlowBundle\Twig;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class AttributeExtension extends \Twig_Extension
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $repository;

    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_attribute_label_from_code',
                [$this, 'getAttributeLabelFromCode'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction('is_attribute_localizable', [$this, 'isAttributeLocalizable']),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getAttributeLabelFromCode($code)
    {
        if (null !== $attribute = $this->repository->findOneByIdentifier($code)) {
            return (string) $attribute;
        }

        return $code;
    }

    /**
     * @param string $code
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isAttributeLocalizable($code)
    {
        $attribute = $this->repository->findOneByIdentifier($code);

        if (null === $attribute) {
            throw new \LogicException(sprintf('Unable to find attribute "%s"', $code));
        }

        return $attribute->isLocalizable();
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\EntityWithQuantifiedAssociationTrait;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Abstract product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProduct implements ProductInterface
{
    use EntityWithQuantifiedAssociationTrait;

    /** @var int|string */
    protected $id;

    /** @var array */
    protected $rawValues;

    /** @var \DateTime */
    protected $created;

    /** @var \DateTime */
    protected $updated;

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var WriteValueCollection
     */
    protected $values;

    /** @var FamilyInterface|null */
    protected $family;

    /** @var Collection */
    protected $categories;

    /** @var bool $enabled */
    protected $enabled = true;

    /** @var Collection */
    protected $groups;

    /** @var Collection */
    protected $associations;

    /**
     * Not persisted.
     *
     * @var QuantifiedAssociationCollection|null
     */
    protected $quantifiedAssociationCollection;

    /** @var Collection */
    protected $completenesses;

    /** @var string|null */
    protected $identifier;

    /** @var Collection */
    protected $uniqueData;

    /** @var ProductModelInterface|null */
    protected $parent;

    /** @var FamilyVariantInterface|null */
    protected $familyVariant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new WriteValueCollection();
        $this->categories = new ArrayCollection();
        $this->completenesses = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->associations = new ArrayCollection();
        $this->uniqueData = new ArrayCollection();
        $this->quantifiedAssociationCollection = QuantifiedAssociationCollection::createFromNormalized([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): ProductInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated(\DateTime $created): TimestampableInterface
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated(\DateTime $updated): TimestampableInterface
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ValueInterface $value): EntityWithValuesInterface
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ValueInterface $value): EntityWithValuesInterface
    {
        $this->values->remove($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes(): array
    {
        return $this->getValues()->getAttributeCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(string $attributeCode, string $localeCode = null, string $scopeCode = null): ValueInterface
    {
        $value = $this->values->getByCodes($attributeCode, $scopeCode, $localeCode);
        if (null !== $value) {
            return $value;
        }

        if (null === $this->getParent()) {
            return null;
        }

        return $this->getParent()->getValue($attributeCode, $localeCode, $scopeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues(): array
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues): EntityWithValuesInterface
    {
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(string $attributeCode): bool
    {
        return in_array($attributeCode, $this->getValues()->getAttributeCodes(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily(): ?FamilyInterface
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family = null): ProductInterface
    {
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyId(): int
    {
        return $this->family !== null ? $this->family->getId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier(?string $identifierValue): ProductInterface
    {
        $this->identifier = $identifierValue;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): \Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection
    {
        if (!$this->isVariant()) {
            return $this->values;
        }

        $values = WriteValueCollection::fromCollection($this->values);

        return $this->getAllValues($this, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(WriteValueCollection $values): EntityWithValuesInterface
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(): ?ValueInterface
    {
        if (null === $this->family) {
            return null;
        }

        $attributeAsImage = $this->family->getAttributeAsImage();

        if (null === $attributeAsImage) {
            return null;
        }

        return $this->getValue($attributeAsImage->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(string $locale = null, string $scope = null): string
    {
        $identifier = (string) $this->getIdentifier();

        if (null === $this->family) {
            return $identifier;
        }

        $attributeAsLabel = $this->family->getAttributeAsLabel();

        if (null === $attributeAsLabel) {
            return $identifier;
        }

        $locale = $attributeAsLabel->isLocalizable() ? $locale : null;
        $scope = $attributeAsLabel->isScopable() ? $scope : null;
        $value = $this->getValue($attributeAsLabel->getCode(), $locale, $scope);

        if (null === $value) {
            return $identifier;
        }

        $data = $value->getData();

        if (empty($data)) {
            return $identifier;
        }

        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories(): ArrayCollection
    {
        if (!$this->isVariant()) {
            return $this->categories;
        }

        $categories = new ArrayCollection($this->categories->toArray());

        return $this->getAllCategories($this, $categories);
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(BaseCategoryInterface $category): self
    {
        if (!$this->categories->contains($category) && !$this->hasAncestryCategory($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(BaseCategoryInterface $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes(): array
    {
        $codes = [];
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }
        sort($codes);

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupCodes(): array
    {
        $codes = [];
        foreach ($this->getGroups() as $group) {
            $codes[] = $group->getCode();
        }
        sort($codes);

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled(bool $enabled): ProductInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily(AttributeInterface $attribute): bool
    {
        return null !== $this->family && $this->family->getAttributes()->contains($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeRemovable(AttributeInterface $attribute): bool
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            return false;
        }
        return !$this->hasAttributeInFamily($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeEditable(AttributeInterface $attribute): bool
    {
        return $this->hasAttributeInFamily($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups(): \Doctrine\Common\Collections\Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group): ProductInterface
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addProduct($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group): ProductInterface
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function addAssociation(AssociationInterface $association): EntityWithAssociationsInterface
    {
        if (!$this->associations->contains($association)) {
            $associationType = $association->getAssociationType();
            if (null !== $associationType && null !== $this->getAssociationForType($associationType)) {
                throw new \LogicException(
                    sprintf(
                        'Can not add an association of type %s because the product already has one',
                        $associationType->getCode()
                    )
                );
            }

            $this->associations->add($association);
            $association->setOwner($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAssociation(AssociationInterface $association): EntityWithAssociationsInterface
    {
        $this->associations->removeElement($association);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociations(): \Doctrine\Common\Collections\Collection
    {
        return $this->associations;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAssociations(): \Doctrine\Common\Collections\Collection
    {
        $associations = new ArrayCollection($this->associations->toArray());

        return $this->getAncestryAssociations($this, $associations);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationForType(AssociationTypeInterface $type): ?AssociationInterface
    {
        return $this->getAssociationForTypeCode($type->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationForTypeCode(string $typeCode): ?AssociationInterface
    {
        foreach ($this->associations as $association) {
            if ($association->getAssociationType()->getCode() === $typeCode) {
                return $association;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociations(Collection $associations): EntityWithAssociationsInterface
    {
        $this->associations = $associations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): string
    {
        return $this->getIdentifier();
    }

    public function getUniqueData(): ArrayCollection
    {
        return $this->uniqueData;
    }

    /**
     * @param ProductUniqueDataInterface $uniqueData
     */
    public function addUniqueData(ProductUniqueDataInterface $uniqueData): ProductInterface
    {
        $this->uniqueData->add($uniqueData);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUniqueData(Collection $data): void
    {
        $this->uniqueData = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?ProductModelInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(ProductModelInterface $parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyVariant(): ?FamilyVariantInterface
    {
        return $this->familyVariant;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function setFamilyVariant(FamilyVariantInterface $familyVariant): void
    {
        $this->familyVariant = $familyVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariationLevel(): int
    {
        return $this->getParent()->getVariationLevel() + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForVariation(): WriteValueCollection
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesForVariation(): Collection
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function isVariant(): bool
    {
        return null !== $this->getParent();
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param WriteValueCollection         $valueCollection
     *
     * @return WriteValueCollection
     */
    private function getAllValues(
        EntityWithFamilyVariantInterface $entity,
        WriteValueCollection $valueCollection
    ): WriteValueCollection {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $valueCollection;
        }

        foreach ($parent->getValuesForVariation() as $value) {
            $valueCollection->add($value);
        }

        return $this->getAllValues($parent, $valueCollection);
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param Collection                       $categoryCollection
     *
     * @return Collection
     */
    private function getAllCategories(
        EntityWithFamilyVariantInterface $entity,
        Collection $categoryCollection
    ): Collection {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $categoryCollection;
        }

        foreach ($parent->getCategories() as $category) {
            if (!$categoryCollection->contains($category)) {
                $categoryCollection->add($category);
            }
        }

        return $this->getAllCategories($parent, $categoryCollection);
    }

    /**
     * Does the ancestry of the entity already has the $category?
     *
     * @param CategoryInterface $category
     *
     * @return bool
     */
    private function hasAncestryCategory(CategoryInterface $category): bool
    {
        $parent = $this->getParent();
        if (null === $parent) {
            return false;
        }

        // no need recursion here as getCategories already look in the whole ancestry
        foreach ($parent->getCategories() as $ancestryCategory) {
            if ($ancestryCategory->getCode() === $category->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param Collection                       $associationsCollection
     *
     * @return Collection
     */
    private function getAncestryAssociations(
        EntityWithFamilyVariantInterface $entity,
        Collection $associationsCollection
    ): Collection {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $associationsCollection;
        }

        foreach ($parent->getAllAssociations() as $association) {
            $associationsCollection = $this->mergeAssociation($association, $associationsCollection);
        }

        return $associationsCollection;
    }

    /**
     * Merges one association in an association collection.
     * It first merge the product existing association
     * And then merges the association into the collection
     *
     * Merging an association means merging all the products, product models and groups
     * into the collection associations or adding it if it doesn't exist
     *
     * @param AssociationInterface $association
     * @param Collection           $associationsCollection
     *
     * @return Collection
     */
    private function mergeAssociation(
        AssociationInterface $association,
        Collection $associationsCollection
    ): Collection {
        $foundInCollection = null;
        foreach ($associationsCollection as $associationInCollection) {
            if ($associationInCollection->getAssociationType()->getCode() ===
                $association->getAssociationType()->getCode()) {
                $foundInCollection = $associationInCollection;
            }
        }

        if (null !== $foundInCollection) {
            foreach ($association->getProducts() as $product) {
                $foundInCollection->addProduct($product);
            }
            foreach ($association->getProductModels() as $productModel) {
                $foundInCollection->addProductModel($productModel);
            }
            foreach ($association->getGroups() as $group) {
                $foundInCollection->addGroup($group);
            }
        }
        $associationsCollection->add($association);

        return $associationsCollection;
    }
}

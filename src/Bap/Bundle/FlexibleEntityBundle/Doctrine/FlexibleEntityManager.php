<?php
namespace Bap\Bundle\FlexibleEntityBundle\Doctrine;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeValue;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttribute;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup;

use Bap\Bundle\FlexibleEntityBundle\Model\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntitySet;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class FlexibleEntityManager
{
    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->manager = $om;
    }

    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getPersistenceManager()
    {
        return $this->manager;
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getEntityShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getSetShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getGroupShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getAttributeShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getAttributeOptionShortname();

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    abstract public function getAttributeValueShortname();

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getEntityClass()
    {
        return $this->manager->getClassMetadata($this->getEntityShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getSetClass()
    {
        return $this->manager->getClassMetadata($this->getSetShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getGroupClass()
    {
        return $this->manager->getClassMetadata($this->getGroupShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeOptionClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeOptionShortname())->getName();
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getAttributeValueClass()
    {
        return $this->manager->getClassMetadata($this->getAttributeValueShortname())->getName();
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        return $this->manager->getRepository($this->getEntityShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getSetRepository()
    {
        return $this->manager->getRepository($this->getSetShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getGroupRepository()
    {
        return $this->manager->getRepository($this->getGroupShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->manager->getRepository($this->getAttributeShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->manager->getRepository($this->getAttributeOptionShortname());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeValueRepository()
    {
        return $this->manager->getRepository($this->getAttributeValueShortname());
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewEntityInstance()
    {
        $class = $this->getEntityClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntitySet
     */
    public function getNewSetInstance()
    {
        $class = $this->getSetClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityGroup
     */
    public function getNewGroupInstance()
    {
        $class = $this->getGroupClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttribute
     */
    public function getNewAttributeInstance()
    {
        $class = $this->getAttributeClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttributeOption
     */
    public function getNewAttributeOptionInstance()
    {
        $class = $this->getAttributeOptionClass();

        return new $class();
    }

    /**
     * Return a new instance
     * @return EntityAttributeValue
     */
    public function getNewAttributeValueInstance()
    {
        $class = $this->getAttributeValueClass();

        return new $class();
    }

    /**
     * Clone an entity type
     *
     * @param EntitySet $entitySet to clone
     * 
     * @return EntitySet
     */
    public function cloneSet($entitySet)
    {
        // create new entity type and clone values
        $cloneSet = $this->getNewSetInstance();
        $cloneSet->setCode($entitySet->getCode());
        $cloneSet->setTitle($entitySet->getTitle());

        // clone groups
        foreach ($entitySet->getGroups() as $groupToClone) {

            // clone group entity
            $cloneGroup = $this->getNewGroupInstance();
            $cloneGroup->setTitle($groupToClone->getTitle());
            $cloneGroup->setCode($groupToClone->getCode());
            $cloneSet->addGroup($cloneGroup);

            // link to same attributes
            foreach ($groupToClone->getAttributes() as $attToLink) {
                $cloneGroup->addAttribute($attToLink);
            }
        }

        return $cloneSet;
    }

    /**
     * Clone an entity
     *
     * @param Entity $entity to clone
     * 
     * @return Entity
     */
    public function cloneEntity($entity)
    {
        // create a new entity
        $class = $this->getEntityClass();
        $clone = new $class();

        // clone entity type
        $cloneSet = $this->cloneSet($entity->getSet());
        $clone->setSet($cloneSet);

        return $clone;
    }
}

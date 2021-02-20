<?php
namespace Bolgatty\WorkFlowBundle\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
interface EntityWithValuesDraftRepositoryInterface extends ObjectRepository
{
    /**
     * Return entities with values based on user
     */
    public function findUserEntityWithValuesDraft(EntityWithValuesInterface $entityWithValues, string $username): ?EntityWithValuesInterface;

    /**
     * Create the datagrid query builder
     */
    public function createDatagridQueryBuilder(): QueryBuilder;

    /**
     * Return entity with values drafts that can be approved by the given user
     */
    public function findApprovableByUser(UserInterface $user, ?int $limit = null): ?array;

    /**
     * Apply the context of the datagrid to the query
     */
    public function applyDatagridContext(QueryBuilder $qb, ?string $entityWithValuesId): EntityWithValuesDraftRepositoryInterface;

    /**
     * Apply filter for datagrid
     */
    public function applyFilter(QueryBuilder $qb, string $field, string $operator, $value): void;

    /**
     * Apply filter for datagrid
     */
    public function applySorter(QueryBuilder $qb, string $field, ?string $direction): void;

    /**
     * Find all by product
     */
    public function findByEntityWithValues(EntityWithValuesInterface $entityWithValues): ?array;

    /**
     * Find all drafts corresponding to the specified ids
     */
    public function findByIds(array $ids): ?array;

    /**
     * Returns the total count of entity with values drafts
     */
    public function countAll(): int;
}
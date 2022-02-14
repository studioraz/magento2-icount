<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

namespace SR\Icount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SR\Icount\Api\Data\IcountDocInterface as EntityDataInterface;
use SR\Icount\Api\Data\IcountDocSearchResultsInterface as EntityDataSearchResultsInterface;

interface IcountDocRepositoryInterface
{
    /**
     * Create or update
     *
     * @param EntityDataInterface $entity
     *
     * @return EntityDataInterface
     */
    public function save(EntityDataInterface $entity): EntityDataInterface;

    /**
     * Get by ID.
     *
     * @param int $entityId
     *
     * @return EntityDataInterface
     *
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): EntityDataInterface;

    /**
     * Retrieve List which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return EntityDataSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): EntityDataSearchResultsInterface;

    /**
     * Delete Entity.
     *
     * @param EntityDataInterface $entity
     *
     * @return bool true on success
     */
    public function delete(EntityDataInterface $entity): bool;

    /**
     * Delete entity by ID.
     *
     * @param int $entityId
     *
     * @return bool true on success
     */
    public function deleteById(int $entityId): bool;



// ========================== SPECIFIC METHODS ====================
}

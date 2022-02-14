<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model\ResourceModel;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SR\Icount\Api\IcountDocRepositoryInterface;
use SR\Icount\Api\Data\IcountDocInterface as EntityDataInterface;
use SR\Icount\Api\Data\IcountDocSearchResultsInterface as EntityDataSearchResultsInterface;
use SR\Icount\Api\Data\IcountDocSearchResultsInterfaceFactory as EntitySearchResultsInterfaceFactory;
use SR\Icount\Model\IcountDoc as EntityModel;
use SR\Icount\Model\IcountDocFactory as EntityModelFactory;
use SR\Icount\Model\ResourceModel\IcountDoc\IcountDocCollectionFactory as EntityResourceCollectionFactory;
use SR\Icount\Model\ResourceModel\IcountDocResource as EntityResource;
use SR\Icount\Registry\IcountDocRegistry as EntityRegistry;

class IcountDocRepository implements IcountDocRepositoryInterface
{
    private EntityRegistry $registry;
    private EntityModelFactory $modelFactory;
    private EntityResource $resource;
    private EntityResourceCollectionFactory $collectionFactory;
    private EntitySearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @param EntityRegistry $registry
     * @param EntityModelFactory $modelFactory
     * @param IcountDocResource $resource
     * @param EntityResourceCollectionFactory $collectionFactory
     * @param EntitySearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        EntityRegistry $registry,
        EntityModelFactory $modelFactory,
        EntityResource $resource,
        EntityResourceCollectionFactory $collectionFactory,
        EntitySearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->registry = $registry;
        $this->modelFactory = $modelFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function save(EntityDataInterface $entity): EntityDataInterface
    {
        $entityModel = $this->modelFactory->create();
        $entityModel->setData($entity->__toArray());

        $this->resource->save($entityModel);
        $this->registry->push($entityModel);

        return $this->getById((int)$entityModel->getId());
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): EntityDataInterface
    {
        return $this->registry->retrieve($entityId)->getDataModel();
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): EntityDataSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->collectionFactory->create();

        // NOTE: Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $searchResults->setTotalCount($collection->getSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $entities = [];
        /** @var EntityModel $entityModel */
        foreach ($collection as $entityModel) {
            $entities[] = $entityModel->getDataModel();
        }

        $searchResults->setItems($entities);
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(EntityDataInterface $entity): bool
    {
        return $this->deleteById((int)$entity->getId());
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        try {
            $entityModel = $this->registry->retrieve($entityId);

            $this->resource->delete($entityModel);
            $this->registry->remove($entityId);
        } catch (\Exception $e) {
        }

        return true;
    }

    /**
     * Add FilterGroup to the collection
     *
     * @param FilterGroup $filterGroup
     * @param AbstractDb $collection
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, AbstractDb $collection): void
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType();
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }



// ========================== SPECIFIC METHODS: ====================
}

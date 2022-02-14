<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Registry;

use Magento\Framework\Exception\NoSuchEntityException;
use SR\Icount\Api\Data\IcountDocInterface as EntityDataInterface;
use SR\Icount\Model\ResourceModel\IcountDocResource as EntityResource;
use SR\Icount\Model\IcountDoc as EntityModel;
use SR\Icount\Model\IcountDocFactory as EntityModelFactory;

class IcountDocRegistry
{
    /**
     * @var EntityModel[]
     */
    private array $registryById = [];
    private EntityModelFactory $modelFactory;
    private EntityResource $resource;

    /**
     * @param EntityModelFactory $modelFactory
     * @param EntityResource $resource
     */
    public function __construct(
        EntityModelFactory $modelFactory,
        EntityResource $resource
    ) {
        $this->modelFactory = $modelFactory;
        $this->resource = $resource;
    }

    /**
     * Retrieves instance from registry given an id
     *
     * @param int $entityId
     *
     * @return EntityModel
     * @throws NoSuchEntityException
     */
    public function retrieve(int $entityId): EntityModel
    {
        if (isset($this->registryById[$entityId])) {
            return $this->registryById[$entityId];
        }

        /** @var EntityModel $entityModel */
        $entityModel = $this->modelFactory->create();
        $this->resource->load($entityModel, $entityId);
        if (!$entityModel->getId()) {
            // NOTE: Entity does not exist
            throw NoSuchEntityException::singleField(EntityDataInterface::ID, $entityId);
        }

        $this->push($entityModel);
        return $entityModel;
    }

    /**
     * Replaces existing instance with a new one.
     *
     * @param EntityModel $entityModel
     *
     * @return $this
     */
    public function push(EntityModel $entityModel): self
    {
        $this->registryById[$entityModel->getId()] = $entityModel;
        return $this;
    }

    /**
     * Removes instance from registry given an id
     *
     * @param int $entityId
     *
     * @return $this
     */
    public function remove(int $entityId): self
    {
        if (isset($this->registryById[$entityId])) {
            //$entityModel = $this->registryById[$entityId];

            unset(
                $this->registryById[$entityId]
            );
        }

        return $this;
    }
}

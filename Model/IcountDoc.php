<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use SR\Icount\Api\Data\IcountDocInterface as EntityDataInterface;
use SR\Icount\Api\Data\IcountDocInterfaceFactory as EntityDataInterfaceFactory;
use SR\Icount\Model\ResourceModel\IcountDoc\IcountDocCollection as EntityResourceCollection;
use SR\Icount\Model\ResourceModel\IcountDocResource as EntityResource;

class IcountDoc extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'studioraz_icount_doc';

    protected $_eventPrefix = 'studioraz_icount_doc';
    protected $_eventObject = 'sricount_doc';

    protected EntityDataInterfaceFactory $dataFactory;
    protected DataObjectHelper $dataObjectHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EntityResource $resource
     * @param EntityResourceCollection $resourceCollection
     * @param EntityDataInterfaceFactory $dataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EntityResource $resource,
        EntityResourceCollection $resourceCollection,
        EntityDataInterfaceFactory $dataFactory,
        DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->dataFactory = $dataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Retrieves Entity Model with its Data
     *
     * @return EntityDataInterface
     */
    public function getDataModel(): EntityDataInterface
    {
        $entityData = $this->getData();

        // NOTE: the Trick to populate Complex data (like an array and Objects)
        $additionalInfo = $entityData[EntityDataInterface::ADDITIONAL_INFORMATION] ?? [];

        unset(
            $entityData[EntityDataInterface::ADDITIONAL_INFORMATION]
        );

        $entityDataObject = $this->dataFactory->create();
        $this->dataObjectHelper->populateWithArray($entityDataObject, $entityData, EntityDataInterface::class);

        // NOTE: the Trick to populate Complex data (like an array and Objects)
        $entityDataObject->setAdditionalInformation($additionalInfo);

        return $entityDataObject;
    }
}

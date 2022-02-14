<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model\ResourceModel\IcountDoc;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SR\Icount\Model\IcountDoc as EntityModel;
use SR\Icount\Model\ResourceModel\IcountDocResource as EntityResource;

class IcountDocCollection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'studioraz_icount_doc_collection';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(EntityModel::class, EntityResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        /** @var AbstractModel $item */
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}

<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SR\Icount\Api\Data\IcountDocInterface as EntityDataModelInterface;
use SR\Icount\Model\IcountDoc as EntityModel;

class IcountDocResource extends AbstractDb
{
    public const ENTITY_DB_TABLE = 'studioraz_icount_doc';

    /**
     * Serializable field: `additional_information`
     *
     * @var array
     */
    protected $_serializableFields = [
        EntityDataModelInterface::ADDITIONAL_INFORMATION => [null, []],
    ];

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::ENTITY_DB_TABLE, EntityDataModelInterface::ID);
        $this->_useIsObjectNew = true;
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var EntityModel $object */
        parent::_beforeSave($object);

        // trick to update 'Updated_At' field
        // see: mysql CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        $object->unsetData(EntityDataModelInterface::UPDATED_AT);

        return $this;
    }
}

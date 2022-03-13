<?php
/**
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SR\Icount\Plugin\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\DB\Select as DbSelect;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use SR\Icount\Model\ResourceModel\IcountDocResource;

class ExtraColumnsPlugin
{

    const EMPTY_VALUE_IF_DOC_NUM_IS_NULL = "-";

    /**
     * After Plugin
     * @param CollectionFactory $subject
     * @param Collection $result
     * @param mixed ...$arguments
     *
     * @return Collection
     * @see \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory::getReport
     *
     */
    public function afterGetReport(CollectionFactory $subject, Collection $result, ...$arguments)
    {
        if (count($arguments) > 0 && $arguments[0] === 'sales_order_grid_data_source') {
            $this->addExtraColumnsToOrdersGrid($result);
        }

        return $result;
    }

    /**
     * Adds extra columns to Sales Order Grid Collection
     *
     * @param Collection $collection
     */
    private function addExtraColumnsToOrdersGrid(Collection $collection)
    {
        $tblAlias = 'sricount';

        /** @var DbSelect $select */
        $select = $collection->getSelect();
        $select->joinLeft(
            [$tblAlias => $collection->getTable(IcountDocResource::ENTITY_DB_TABLE)],
            "{$tblAlias}.doc_linkage_id = main_table.entity_id",
            ['icount_doc_num' => $select->getConnection()->getIfNullSql("{$tblAlias}.doc_num", "'" . self::EMPTY_VALUE_IF_DOC_NUM_IS_NULL . "'")]
        );


        $collection->addFilterToMap('icount_doc_num',
            $select->getConnection()->getIfNullSql("{$tblAlias}.doc_num", "'" . self::EMPTY_VALUE_IF_DOC_NUM_IS_NULL . "'")
        );
    }
}

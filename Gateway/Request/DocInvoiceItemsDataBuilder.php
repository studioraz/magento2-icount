<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Exception\RequestBuilderException;
use SR\Gateway\Model\Request\AbstractDataBuilder;

class DocInvoiceItemsDataBuilder extends AbstractDataBuilder
{
    public const KEY_ITEMS = 'items';

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject): array
    {
        $storeId = $buildSubject[CommandInterface::ARGUMENT_SUBJECT]['store_id'] ?? null;

        $order = $buildSubject[CommandInterface::ARGUMENT_SUBJECT]['order'] ?? null;
        if (!$order instanceof OrderInterface) {
            throw new RequestBuilderException(new Phrase('Order entity is required.'));
        }

        return [
            self::KEY_ITEMS => $this->buildItemsSection($order),
        ];
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function buildItemsSection(OrderInterface $order): array
    {
//        [// SAMPLE of an Item
//            //'item_id' => '1',// Document Item ID (automatic, only in existing document information)
//            //'inventory_item_id' => '0',// Inventory Item ID
//            //'serial' => '',//Serial number
//            //'long_description' => '',// Item long description
//            //'income_type_id' => '0',
//            //'is_refunded' => '0',
//            //'tax_exempt' => '0',//Is item tax exempt
//
//            'sku' => 'sku_1',// Magento item sku {{item.sku}}
//            'description' => 'desc_1',// Magento item name {{item.name}}
//            'unitprice_incvat' => '100',//Magento order item price (including VAT) {{item.price}}
//            'quantity' => '1',// Magento Order item quantity{{item.qty}}
//        ],

        $lines = [];
        foreach ($order->getAllItems() as $item) {
            // NOTE: skip Complex Product's items (configurable, bundle)
            if ($item->getProductType() !== ProductType::TYPE_SIMPLE) {
                continue;
            }

            // NOTE: set default values
            //     sku and name from simple, the rest from parent
            $line = [
                'sku' => $item->getSku(),// Magento item sku {{item.sku}}
                'description' => $item->getName(),// Magento item name {{item.name}}
                'unitprice_incvat' => (float)$item->getPrice(),//Magento order item price (including VAT) {{item.price}}
                'quantity' => (float)$item->getQtyOrdered(),// Magento Order item quantity{{item.qty}}
            ];

            // NOTE: in case the Item is a child of Complex Product (configurable, bundle) re-define specific params
            if (null !== $parentItem = $item->getParentItem()) {
                $line['unitprice_incvat'] = (float)$parentItem->getPrice();
                $line['quantity'] = (float)$parentItem->getQtyOrdered();
            }

            $lines[] = $line;
        }

        // NOTE: start: add extra item SHIPPING
        if ($order->getShippingAmount() > 0) {
            $lines[] = [
                'sku' => 'shipping',
                'description' => $order->getShippingDescription(),
                'unitprice_incvat' => (float)$order->getShippingAmount(),//Magento order item price (including VAT) {{item.price}}
                'quantity' => 1,
            ];
        }
        // end: add extra item SHIPPING

        return $lines;
    }
}

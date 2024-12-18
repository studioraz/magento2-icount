<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Exception\RequestBuilderException;
use SR\Gateway\Model\Request\AbstractDataBuilder;
use SR\Icount\Gateway\Config\Config;
use SR\Icount\Model\Data\IcountDoc\DocType as IcountDocType;

class CreateDocInvoiceDataBuilder extends AbstractDataBuilder
{
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

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress === null) {
            throw new RequestBuilderException(new Phrase('Order Billing Address does not exist.'));
        }

        return [
            'cid' => $this->config->getCompanyId($storeId),
            'doctype' => IcountDocType::TYPE_CODE_INVREC,// Document type [hard-coded constant]
            'doc_title' => $this->buildDocTitleValue($order),// message:: "Invoice for order #OrderIncID"
            'lang' => 'he',// 'he' | 'en'
            'sanity_string' => $order->getIncrementId(),// OrderIncID
            'hwc' => null,// additional comment

            'send_email' => (bool)$this->config->getValue(
                Config::KEY_CONFIG_SEND_COPY_TO_ME,
                Config::GROUP_PATH_DOC_INVOICE,
                $storeId
            ),// see sys-config KEY_CONFIG_SEND_COPY_TO_ME
            'email_to_client' => (bool)$this->config->getValue(
                Config::KEY_CONFIG_SEND_COPY_TO_ADDITIONAL,
                Config::GROUP_PATH_DOC_INVOICE,
                $storeId
            ),// see sys-config KEY_CONFIG_SEND_COPY_TO_ADDITIONAL

            'client_name' => $this->buildClientNameValue($billingAddress),// Bill Add {FN} {LN}
            'client_address' => $this->buildClientAddressValue($billingAddress),// BIll Add {street} {city} {telephone}
            'client_email' => $billingAddress->getEmail(),// Bill Add customer_email

            //'currency' => $order->getOrderCurrencyCode(),// invoice-order-store-currency order.store_currency_code
            'currency' => 'ILS',// invoice-order-store-currency order.store_currency_code
            'discount_incvat' => $this->getDiscount($order),// NOTE: only positive value. float 19.90 {{order.discount_amount}} Order discount amount (including VAT)
            //'' => '',
        ];
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    protected function buildDocTitleValue(OrderInterface $order): string
    {
        return (new Phrase('Invoice for order #%1', [$order->getIncrementId()]))->render();
    }

    /**
     * @param OrderAddressInterface $address
     * @return string
     */
    protected function buildClientNameValue(OrderAddressInterface $address): string
    {
        return $address->getFirstname() . ' ' . $address->getLastname();
    }

    /**
     * @param OrderAddressInterface $address
     * @return string
     */
    protected function buildClientAddressValue(OrderAddressInterface $address): string
    {
        return $address->getStreetLine(1) . ' ' . $address->getCity() . ' ' . $address->getTelephone();
    }

    /**
     * @param $order
     * @return float|int
     */
    protected function getDiscount($order)
    {
        $discount = $order->getDiscountAmount();
        if ($discount) {
            $discount = abs((float)($order->getDiscountInvoiced() ?? 0.0));
        }
        if ($order->getData('mageworx_giftcards_amount')) {
            $discount += abs((float)($order->getData('mageworx_giftcards_amount') ?? 0.0));
        }
        // NOTE: only positive value. float 19.90 {{order.discount_amount}} Order discount amount (including VAT)
        return abs((float)$discount);
    }
}

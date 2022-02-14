<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Exception\RequestBuilderException;
use SR\Gateway\Model\Request\AbstractDataBuilder;

class DocInvoiceCcDataBuilder extends AbstractDataBuilder
{
    public const KEY_CC = 'cc';

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
            self::KEY_CC => $this->buildCcSection($order),
        ];
    }

    public function buildCcSection(OrderInterface $order): array
    {
        $payment = $order->getPayment();
        if ($payment === null) {
            throw new RequestBuilderException(new Phrase('Order Payment does not exist.'));
        }

        $addInfo = $payment->getAdditionalInformation();
        $a = 0;

        return [
            'sum' => (float)$order->getGrandTotal(),// Magento order total amount (Include VAT) {{order.base_grand_total}}

            'token_id' => $addInfo['token_value'] ?? null,// Credit Card token

            'card_type' => $payment->getCcType(),// Magento order Credit-Card type (Visa/MasterCard) {{payment.cc_type}}
            'card_number' => $payment->getCcLast4(),// Magento order Credit-Card number last 4 digits {{payment.cc_last_4}}
            'exp_year' => $payment->getCcExpYear(),// Magento order Card Expiration year format YYYY {{payment.cc_exp_year}}
            'exp_month' => $payment->getCcExpMonth(),// Magento order Card Expiration month format MM {{payment.cc_exp_month}}

            'holder_id' => '123456789',// Card Holder ID FIXME
            'holder_name' => 'Israel Israeli',// Card Holder name FIXME
            'confirmation_code' => $payment->getLastTransId(),// Magento order Credit-Card authorization number {{payment.cc_trans_id}}

            'num_of_payments' => (int)($addInfo['installments_number_of_payments'] ?? 0),//Number of magento order payments {{formatted_payment.qty}}
            'first_payment' => (float)($addInfo['installments_first_payment'] ?? 0),// Magento order First payment amount
        ];
    }
}

<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model\Data\IcountDoc;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

class DocType implements OptionSourceInterface
{
    /**#@+
     * List of supported document types: invoice, receipt, invrec (invoice/receipt), refund, deal, offer, order, trec, delcert.
     * The list may vary by your company type, so to get the complete list of what is available call doc/types method.
     * @see doc/types
     */
    public const TYPE_CODE_DEAL = 'deal';
    public const TYPE_CODE_DELCERT = 'delcert';
    public const TYPE_CODE_INVOICE = 'invoice';
    public const TYPE_CODE_INVREC = 'invrec';
    public const TYPE_CODE_OFFER = 'offer';
    public const TYPE_CODE_ORDER = 'order';
    public const TYPE_CODE_RECEIPT = 'receipt';
    public const TYPE_CODE_REFUND = 'refund';
    public const TYPE_CODE_TREC = 'trec';
    //public const TYPE_CODE_ = '';
    /**#@- */

    /**#@+ */
    public const TYPE_ID_DEAL = 1;
    public const TYPE_ID_DELCERT = 2;
    public const TYPE_ID_INVOICE = 3;
    public const TYPE_ID_INVREC = 4;
    public const TYPE_ID_OFFER = 5;
    public const TYPE_ID_ORDER = 6;
    public const TYPE_ID_RECEIPT = 7;
    public const TYPE_ID_REFUND = 8;
    public const TYPE_ID_TREC = 9;
    //public const TYPE_ID_ = '';
    /**#@- */

    /**
     * Mappings
     * DOC_TYPE code => DOC_TYPE id
     *
     * @var int[]
     */
    private array $mappings = [
        self::TYPE_CODE_DEAL => self::TYPE_ID_DEAL,
        self::TYPE_CODE_DELCERT => self::TYPE_ID_DELCERT,
        self::TYPE_CODE_INVOICE => self::TYPE_ID_INVOICE,
        self::TYPE_CODE_INVREC => self::TYPE_ID_INVREC,
        self::TYPE_CODE_OFFER => self::TYPE_ID_OFFER,
        self::TYPE_CODE_ORDER => self::TYPE_ID_ORDER,
        self::TYPE_CODE_RECEIPT => self::TYPE_ID_RECEIPT,
        self::TYPE_CODE_REFUND => self::TYPE_ID_REFUND,
        self::TYPE_CODE_TREC => self::TYPE_ID_TREC,
    ];

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            self::TYPE_ID_DEAL => new Phrase('Deal'),
            self::TYPE_ID_DELCERT => new Phrase('Del Cert'),
            self::TYPE_ID_INVOICE => new Phrase('Invoice'),
            self::TYPE_ID_INVREC => new Phrase('Invoice / Receipt'),
            self::TYPE_ID_OFFER => new Phrase('Offer'),
            self::TYPE_ID_ORDER => new Phrase('Order'),
            self::TYPE_ID_RECEIPT => new Phrase('Receipt'),
            self::TYPE_ID_REFUND => new Phrase('Refund'),
            self::TYPE_ID_TREC => new Phrase('Trec'),
        ];
    }

    /**
     * @param string $code
     * @return int|null
     */
    public function getIdByCode(string $code): ?int
    {
        $value = null;
        if (array_key_exists($code, $this->mappings)) {
            $value = (int)$this->mappings[$code];
        }
        return $value;
    }
}

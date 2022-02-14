<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \SR\Gateway\Model\Config\Config
{
    public const EXT_ALIAS = 'sricount';

    public const GROUP_PATH_DOC_INVOICE = 'doc_invoice';

    /**#@+
     * XML Config parts
     * ex: '{self::EXT_ALIAS}/{self::GROUP_PATH_...}/{KEY_CONFIG_...}'
     */
    public const KEY_CONFIG_API_CID_PRODUCTION = 'api_cid_production';// company id
    public const KEY_CONFIG_API_CID_SANDBOX = 'api_cid_sandbox';// company id

    public const KEY_CONFIG_SEND_COPY_TO_ME = 'send_copy_to_me';
    public const KEY_CONFIG_SEND_COPY_TO_ADDITIONAL = 'send_copy_to_additional';

    public const KEY_CONFIG_CREATE_INVOICE = 'create_invoice';

    //public const KEY_CONFIG_ = '';
    /**#@- */

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $pathPattern = self::EXT_ALIAS . '/%s/%s'
    ) {
        parent::__construct($scopeConfig, $pathPattern);
    }

    /**
     * Returns API CID (Company Identifier)
     *
     * @param mixed|null $storeId
     * @return string
     */
    public function getCompanyId($storeId = null): string
    {
        return $this->getValue(
            $this->isModeProduction($storeId) ? static::KEY_CONFIG_API_CID_PRODUCTION : static::KEY_CONFIG_API_CID_SANDBOX,
            static::DEFAULT_PATH_GROUP,
            $storeId
        );
    }
}

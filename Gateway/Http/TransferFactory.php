<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Http;

use Magento\Framework\Phrase;
use SR\Gateway\Exception\TransferBuilderException;
use SR\Gateway\Model\Request\AbstractClientBuilder;

class TransferFactory extends \SR\Gateway\Model\Http\TransferFactory
{
    /**
     * @inheritDoc
     *
     * @throws TransferBuilderException
     */
    protected function addTransferUri(array $request): self
    {
        $uri = $request[AbstractClientBuilder::KEY_API_ENDPOINT] ?? null;

        if (empty($uri)) {
            throw new TransferBuilderException(new Phrase('API Endpoint URL is not specified. Please check corresponding System Config parameters.'));
        }

        // Add entity API KEY
        if (isset($request[AbstractClientBuilder::KEY_REQUEST_ACTION])) {
            $entityTypeKey = $request[AbstractClientBuilder::KEY_REQUEST_ACTION];

            $uri = rtrim($uri, '/') . '/' . ltrim($entityTypeKey, '/');
        }

        $queryString = '';

        // TODO: implement logic to build $queryString

        $uri = rtrim($uri, '/') . $queryString;
        $this->transferBuilder->setUri($uri);

        return $this;
    }
}

<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Model\Request\AbstractDataBuilder;

class ApiCredentialsBuilder extends AbstractDataBuilder
{
    /**
     * @inheritDoc
     */
    public function build(array $buildSubject): array
    {
        $storeId = $buildSubject[CommandInterface::ARGUMENT_SUBJECT]['store_id'] ?? null;

        return [
            'user' => $this->config->getApiUsername($storeId),
            'pass' => $this->config->getApiPassword($storeId),
        ];
    }
}

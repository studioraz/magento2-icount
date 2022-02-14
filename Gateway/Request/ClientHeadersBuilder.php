<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request;

class ClientHeadersBuilder extends \SR\Gateway\Model\Request\ClientHeadersBuilder
{
    /**
     * @inheritDoc
     */
    protected function fetchUserDefinedHeaders(array $buildSubject): array
    {
        $userDefained = parent::fetchUserDefinedHeaders($buildSubject);

        $userDefained['Content-Type'] = 'application/json';

        return $userDefained;
    }
}

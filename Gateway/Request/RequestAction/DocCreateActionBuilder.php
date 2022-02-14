<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Request\RequestAction;

class DocCreateActionBuilder extends \SR\Gateway\Model\Request\AbstractClientBuilder
{
    /**
     * @inheritDoc
     */
    public function build(array $buildSubject): array
    {
        return [
            self::KEY_REQUEST_ACTION => 'doc/create',
        ];
    }
}

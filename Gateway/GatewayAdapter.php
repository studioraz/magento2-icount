<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway;

use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Api\Validator\ResultInterface;
use SR\Gateway\Exception\CommandException;

class GatewayAdapter extends \SR\Gateway\Model\GatewayAdapter
{
    /**
     * Sends specific data to External Service, to create new Invoice remotely
     * Returns Response Result
     *
     * @param array $subject [optional]
     *
     * @return ResultInterface|null
     *
     * @throws CommandException
     */
    public function createInvoice(array $subject = []): ?ResultInterface
    {
        return $this->executeCommand('create_invoice', [CommandInterface::ARGUMENT_SUBJECT => $subject]);
    }
}

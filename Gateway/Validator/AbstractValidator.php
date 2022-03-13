<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Validator;

use Magento\Framework\DataObject;
use SR\Gateway\Api\Validator\ResultDataInterface;

abstract class AbstractValidator extends \SR\Gateway\Model\Validator\AbstractValidator
{
    /**
     * @inheritDoc
     */
    protected function isResponseValid(array $rawResponse): bool
    {
        return (bool)($rawResponse['status'] ?? null);
    }

    /**
     * @inheritDoc
     */
    protected function getErrorMessages(array $rawResponse): array
    {
        $messages = [];

        foreach (['reason','error_description','error_details'] as $key) {
            if (isset($rawResponse[$key])) {
                $message = $rawResponse[$key];
                if (is_array($message)) {
                    $message = implode(', ' , $message);
                }
                $messages[] = $key . ': ' . $message;
            }
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    protected function fetchData($rawResponse): ?ResultDataInterface
    {
        $sectionData = $rawResponse;

        $resultData = $this->resultDataFactory->create(['rawData' => $sectionData]);
        $resultData->setEntity(new DataObject($sectionData));

        return $resultData;
    }
}

<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Service\ImportExport\ErrorProcessing;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
    public const MAX_ALLOWED_ERRORS_COUNT = 100;

    protected bool $isInitialized = false;

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * @inheritDoc
     */
    public function initValidationStrategy($validationStrategy, $allowedErrorCount = 0)
    {
        $result = parent::initValidationStrategy(
            $validationStrategy ?: ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS,
            $allowedErrorCount
        );

        $this->isInitialized = true;

        return $result;
    }
}

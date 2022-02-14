<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Cron\Doc;

use SR\Icount\Gateway\Config\Config;
use SR\Icount\Service\Doc\CreateDocInvoiceServiceFactory as ServiceFactory;
use SR\Gateway\Api\Config\ConfigInterface;
use SR\Gateway\Api\LoggerInterface;
use SR\Gateway\Api\ModuleStateInterface;

class CreateInvoiceTask
{
    private ConfigInterface $config;
    private LoggerInterface $logger;
    private ModuleStateInterface $moduleState;
    private ServiceFactory $serviceFactory;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param ModuleStateInterface $moduleState
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        ModuleStateInterface $moduleState,
        ServiceFactory $serviceFactory
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->moduleState = $moduleState;
        $this->serviceFactory = $serviceFactory;
    }

    public function execute(): void
    {
        $this->logger->debug('-------------- SR-ICOUNT :: CREATE DOC INVOICE CRON STARTS --------------');

        // NOTE: check if the SR_Shipping module has been activated.
        if (!$this->isModuleActive()) {
            $this->logger->debug('-------------- SR_Icount module is not active. --------------');
            $this->logger->debug('-------------- SR-ICOUNT :: CREATE DOC INVOICE CRON ENDS --------------');
            return;
        }

        // NOTE: check if the SR_BuzzrShipping module has been activated.
        if (!$this->isTaskEnabled()) {
            $this->logger->debug('-------------- Create Invoices job is not active. --------------');
            $this->logger->debug('-------------- SR-ICOUNT :: CREATE DOC INVOICE CRON ENDS --------------');
            return;
        }

        try {
            $service = $this->serviceFactory->create();
            $service->processMultiple();
            $service->aggregatedErrorsProcessing();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        $this->logger->debug('-------------- SR-ICOUNT :: CREATE DOC INVOICE CRON ENDS --------------');
    }

    /**
     * @return bool
     */
    protected function isModuleActive(): bool
    {
        return $this->moduleState->isActive();
    }

    /**
     * @return bool
     */
    protected function isTaskEnabled(): bool
    {
        $isEnabled = $this->config->getValue(Config::KEY_CONFIG_CREATE_INVOICE, Config::GROUP_PATH_CRON);
        $this->logger->debug('Cron status is ' . (int)$isEnabled);

        return (bool)$isEnabled;
    }
}

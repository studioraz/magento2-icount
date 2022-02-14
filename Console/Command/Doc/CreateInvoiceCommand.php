<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Console\Command\Doc;

use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Phrase;
use SR\Base\Exception\LocalizedException;
use SR\Gateway\Api\ModuleStateInterface;
use SR\Icount\Service\Doc\CreateDocInvoiceService as Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateInvoiceCommand extends Command
{
    private AppState $appState;
    private ModuleStateInterface $moduleState;
    private Service $service;

    /**
     * @param AppState $appState
     * @param ModuleStateInterface $moduleState
     * @param Service $service
     * @param string|null $name
     */
    public function __construct(
        AppState $appState,
        ModuleStateInterface $moduleState,
        Service $service,
        string $name = null
    ) {
        parent::__construct($name);

        $this->appState = $appState;
        $this->moduleState = $moduleState;
        $this->service = $service;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->moduleState->isActive()) {
                throw new LocalizedException(new Phrase('SR_Icount module is not enabled.'));
            }

            $service = $this->service;
            $this->appState->emulateAreaCode(
                Area::AREA_ADMINHTML,
                function() use ($service) {
                    return $service->processMultiple();
                }
            );
        } catch (\Exception $e) {
            $output->writeln(".");
            $output->writeln('Execution failed: ' . $e->getMessage());
        }

        $output->writeln(".");
        $output->writeln("<info>Finished</info>");
    }
}

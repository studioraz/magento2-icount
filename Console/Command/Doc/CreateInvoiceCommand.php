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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateInvoiceCommand extends Command
{

    /**
     * Product create name option
     */
    const INPUT_ORDER_ID = 'order_increment_id';


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
    protected function configure()
    {
        $this->setDescription('Export Magento orders to iCount to create for them Invoice-Receipt. To export a specific order add its ID as a parameter to the command.')
            ->setDefinition($this->getInputList());
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

            $orderId = $input->getArgument(self::INPUT_ORDER_ID);

            $service = $this->service;
            $this->appState->emulateAreaCode(
                Area::AREA_ADMINHTML,
                function() use ($service, $orderId) {
                    $response = null;
                    if ($orderId) {
                        $response = $service->processSingle((int)$orderId);
                    }
                    else {
                        $response = $service->processMultiple();
                    }
                    return $response;
                }
            );
        } catch (\Exception $e) {
            $output->writeln(".");
            $output->writeln('Execution failed: ' . $e->getMessage());
        }

        $output->writeln(".");
        $output->writeln("<info>Finished</info>");
    }

    /**
     * Get list of options and arguments for the command
     * @return array
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::INPUT_ORDER_ID,
                InputArgument::OPTIONAL,
                'Order Id.'
            ),
        ];
    }
}

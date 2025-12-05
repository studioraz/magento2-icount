<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Service\Doc;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory as OrderRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\OrderFactory as OrderResourceFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use SR\Base\Exception\LocalizedException;
use SR\Gateway\Api\Config\ConfigInterface;
use SR\Gateway\Api\LoggerInterface;
use SR\Gateway\Model\GatewayAdapter;
use SR\Gateway\Model\GatewayAdapterFactoryInterface;
use SR\Icount\Api\Data\IcountDocInterface;
use SR\Icount\Gateway\Config\Config;
use SR\Icount\Model\Data\IcountDoc\DocType as IcountDocType;
use SR\Icount\Model\ResourceModel\IcountDocResource;
use SR\Icount\Service\ImportExport\ErrorProcessing\ProcessingErrorAggregator;

class CreateDocInvoiceService
{
    public const MAX_ORDERS_BUNCH = 10;

    private ?GatewayAdapter $gatewayAdapter = null;
    private ?OrderResource $orderResource = null;
    private ?OrderRepositoryInterface $orderRepository = null;

    private ConfigInterface $config;
    private LoggerInterface $logger;
    private ProcessingErrorAggregatorInterface $errorAggregator;
    private GatewayAdapterFactoryInterface $gatewayAdapterFactory;
    private OrderResourceFactory $orderResourceFactory;
    private OrderRepositoryInterfaceFactory $orderRepositoryFactory;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param GatewayAdapterFactoryInterface $gatewayAdapterFactory
     * @param OrderResourceFactory $orderResourceFactory
     * @param OrderRepositoryInterfaceFactory $orderRepositoryFactory
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        ProcessingErrorAggregatorInterface $errorAggregator,
        GatewayAdapterFactoryInterface $gatewayAdapterFactory,
        OrderResourceFactory $orderResourceFactory,
        OrderRepositoryInterfaceFactory $orderRepositoryFactory
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->errorAggregator = $errorAggregator;
        $this->gatewayAdapterFactory = $gatewayAdapterFactory;
        $this->orderResourceFactory = $orderResourceFactory;
        $this->orderRepositoryFactory = $orderRepositoryFactory;
    }

    /**
     * @param int $entityId
     * @return bool
     */
    public function processSingle(int $entityId): bool
    {
        $errorAggregator = $this->getErrorAggregator();

        if (!$errorAggregator->isInitialized()) {
            $errorAggregator->initValidationStrategy(ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR, 1);
            $errorAggregator->clear();
        }

        try {
            $this->performProcess($this->fetchOrder($entityId));
        } catch (NoSuchEntityException $e) {
            $errorAggregator->addError(
            'EntityNotFound',
            ProcessingError::ERROR_LEVEL_WARNING,
                $entityId,
            null,
            $e->getMessage()
            );
        }

        return !$errorAggregator->isErrorLimitExceeded();
    }

    /**
     * @param array|null $idsToProcess
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processMultiple(?array $idsToProcess = null): bool
    {
        $this->logger->debug('>>>> begin :: SR_Icount. Doc-Invoice-Create MULTIPLE.');

        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            ProcessingErrorAggregator::VALIDATION_STRATEGY_STOP_ON_ERROR,
            ProcessingErrorAggregator::MAX_ALLOWED_ERRORS_COUNT
        );
        $errorAggregator->clear();

        if ($idsToProcess === null) {
            $idsToProcess = $this->fetchIdsToProcess();
        }

        // NOTE: start: OrderItems processing (Order by Order)
        $ordersToProcess = count($idsToProcess);
        $ordersProcessed = 0;
        foreach ($idsToProcess as $entityId) {
            $entityId = (int)$entityId;

            try {
                $this->performProcess($this->fetchOrder($entityId));
                ++$ordersProcessed;
            } catch (NoSuchEntityException $e) {
                $errorAggregator->addError(
                    'EntityNotFound',
                    ProcessingError::ERROR_LEVEL_WARNING,
                    $entityId,
                    'entity_id',
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $level = $e instanceof \Magento\Framework\Exception\LocalizedException
                    ? ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    : ProcessingError::ERROR_LEVEL_CRITICAL;

                $errorAggregator->addError(
                    'systemException',
                    $level,
                    $entityId,
                    'entity_id',
                    $e->getMessage()
                );
            }

            if ($errorAggregator->isErrorLimitExceeded()) {
                break;
            }
        }
        // NOTE: end: OrderItems processing

        $this->logger->debug(new Phrase(
            'Summary: %1 of %2 orders have been processed successfully.',
            [$ordersProcessed, $ordersToProcess]
        ));

        $this->logger->debug('>>>> end :: SR_Icount. Doc-Invoice-Create MULTIPLE.');
        return $errorAggregator->isErrorLimitExceeded();
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    protected function performProcess(OrderInterface $order): void
    {
        $this->logger->debug(new Phrase(
            '>> begin :: Order #%1 is processing.',
            [$order->getIncrementId()]
        ));

        try {
            /** @var \SR\Icount\Gateway\GatewayAdapter $gatewayAdapter */
            $gatewayAdapter = $this->getGatewayAdapter();

            $storeId = $order->getStoreId();
            $result = $gatewayAdapter->createInvoice([
                'order' => $order,
                'store_id' => $storeId
            ]);
        } catch (\Exception $e) {
            $this->processExceptionsOnPerform($e, [
                'order' => $order
            ]);
        } finally {
            $this->logger->debug('>> end :: Order is processing.');
        }
    }

    /**
     * Processes Exceptions during Perform action
     *
     * @param \Exception $e
     * @param array $extra list of extra params
     *
     * @return $this
     */
    protected function processExceptionsOnPerform(\Exception $e, array $extra = []): self
    {
        // NOTE: to avoid duplications
        if (!$e instanceof LocalizedException) {
            /** @var OrderInterface $order */
            $order = $extra['order'] ?? null;

            $this->getErrorAggregator()->addError(
                'gatewayException',
                ProcessingError::ERROR_LEVEL_WARNING,
                $order !== null ? $order->getEntityId() : '',
                $order !== null ? $order->getEntityId() : '',
                $e->getMessage()
            );
        }

        $this->logger->debug(['initiator' => static::class, 'message' => 'ERROR :: ' . $e->getMessage()]);

        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function fetchIdsToProcess(): array
    {
        $resource = $this->getOrderResource();
        $select = $resource->getConnection()->select();
        $select->from(
            $resource->getMainTable(),
            ['entity_id']
        );

        // NOTE: start: join icount_doc table
        $tblIcountDocAlias = 'srid';
        $replacements = [
            $tblIcountDocAlias, IcountDocInterface::DOC_TYPE_ID, IcountDocType::TYPE_ID_INVREC,
            $tblIcountDocAlias, IcountDocInterface::DOC_LINKAGE_ID, 'sales_order.entity_id',
        ];
        $select->joinLeft(
            [$tblIcountDocAlias => IcountDocResource::ENTITY_DB_TABLE],
            sprintf("%s.%s = %d AND %s.%s = %s", ...$replacements),
            []
        );
        // NOTE: end: join icount_doc table

        // NOTE: an Invoice should be issued (checking TotalPaid in this case)
        $select->where('state = ?', 'processing')
            ->where('grand_total - total_paid <= 0.0001')// NOTE: check totalPaid amount using given inaccuracy
            ->where('total_invoiced > 0')
            ->where(new \Zend_Db_Expr("{$tblIcountDocAlias}.doc_num = 0 OR {$tblIcountDocAlias}.doc_num IS NULL"));

        // NOTE: Add filter by Order Id FROM/TO if it is applicable
        $orderIdFrom = (int)$this->config->getValue(
            Config::KEY_CONFIG_ORDER_FILTER_ID_MIN,
            Config::GROUP_PATH_DOC_INVOICE
        );
        $orderIdTo = (int)$this->config->getValue(
            Config::KEY_CONFIG_ORDER_FILTER_ID_MAX,
            Config::GROUP_PATH_DOC_INVOICE
        );

        if ($orderIdFrom === $orderIdTo && $orderIdFrom > 0) {
            $select->where('entity_id = ?', $orderIdFrom);
        } else {
            if ($orderIdFrom > 0) {
                $select->where('entity_id >= ?', $orderIdFrom);
            }

            if ($orderIdTo > 0) {
                $select->where('entity_id <= ?', $orderIdTo);
            }

            $select->limit(100);// NOTE: LIMIT of processing Bunch
        }

        //$__sql = (string)$select;//DBG

        return $resource->getConnection()->fetchCol($select);
    }

    /**
     * @param int $id
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    protected function fetchOrder(int $id): OrderInterface
    {
        return $this->getOrderRepository()->get($id);
    }

    protected function getOrderResource(): OrderResource
    {
        if ($this->orderResource === null) {
            $this->orderResource = $this->orderResourceFactory->create();
        }

        return $this->orderResource;
    }

    protected function getOrderRepository(): OrderRepositoryInterface
    {
        if ($this->orderRepository === null) {
            $this->orderRepository = $this->orderRepositoryFactory->create();
        }

        return $this->orderRepository;
    }

    protected function getGatewayAdapter(): GatewayAdapter
    {
        if ($this->gatewayAdapter === null) {
            $this->gatewayAdapter = $this->gatewayAdapterFactory->create(Config::EXT_ALIAS);
        }
        return $this->gatewayAdapter;
    }


    public function getErrorAggregator(): ProcessingErrorAggregatorInterface
    {
        return $this->errorAggregator;
    }

    /**
     * @param ProcessingErrorAggregatorInterface|null $errorAggregator
     * @return void
     */
    public function aggregatedErrorsProcessing(?ProcessingErrorAggregatorInterface $errorAggregator = null): void
    {
        if ($errorAggregator === null) {
            $errorAggregator = $this->getErrorAggregator();
        }

        if ($errorAggregator->getErrorsCount() <= 0) {
            return;
        }

        $this->logger->debug('== Aggregated Errors List: ==');
        $this->logger->debug('## begin :: aggregated errors');

        foreach ($errorAggregator->getAllErrors() as $error) {
            $message = 'Error: ';
            if ($error->getColumnName()) {
                $message .= sprintf('%s = %s. ', $error->getColumnName(), $error->getRowNumber());
            } else {
                $message .= 'general: ';
            }
            $message .= $error->getErrorMessage();

            $this->logger->debug($message);
        }

        $this->logger->debug('## end :: aggregated errors');
    }
}

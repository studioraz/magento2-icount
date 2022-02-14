<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Gateway\Response;

use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use SR\Base\Helper\DateTimeHelper;
use SR\Gateway\Api\CommandInterface;
use SR\Gateway\Api\Config\ConfigInterface;
use SR\Gateway\Api\Response\HandlerInterface;
use SR\Gateway\Exception\ResponseHandlerException;
use SR\Icount\Api\Data\IcountDocInterface;
use SR\Icount\Api\Data\IcountDocInterfaceFactory;
use SR\Icount\Api\IcountDocRepositoryInterfaceFactory;
use SR\Icount\Model\Data\IcountDoc\DocType as IcountDocType;

class CreateDocInvoiceHandler implements HandlerInterface
{
    private ConfigInterface $config;
    private IcountDocInterfaceFactory $icountDataModelFactory;
    private IcountDocRepositoryInterfaceFactory $icountRepositoryFactory;

    public function __construct(
        ConfigInterface $config,
        IcountDocInterfaceFactory $icountDataModelFactory,
        IcountDocRepositoryInterfaceFactory $icountRepositoryFactory
    ) {
        $this->config = $config;
        $this->icountDataModelFactory = $icountDataModelFactory;
        $this->icountRepositoryFactory = $icountRepositoryFactory;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        /** @var OrderInterface $order */
        $order = $handlingSubject[CommandInterface::ARGUMENT_SUBJECT]['order'] ?? null;
        $storeId = $handlingSubject[CommandInterface::ARGUMENT_SUBJECT]['store_id'] ?? null;

        $responseData = $response['object'] ?? [];

        if (empty($responseData['docnum'] ?? null)) {
            return;
        }

        try {
            $dataModel = $this->icountDataModelFactory->create(['data' => [
                IcountDocInterface::DOC_TYPE_ID => IcountDocType::TYPE_ID_INVREC,
                IcountDocInterface::DOC_NUM => (int)$responseData['docnum'],
                IcountDocInterface::DOC_LINKAGE_ID => $order->getEntityId(),
                IcountDocInterface::DOC_EXPORTED_AT => DateTimeHelper::getFormattedValue('now', 'Y:m:d H:i:s'),
                IcountDocInterface::ADDITIONAL_INFORMATION => [
                    'doc_url' => $responseData['doc_url'] ?? null
                ],
            ]]);

            $repository = $this->icountRepositoryFactory->create();
            $repository->save($dataModel);
        } catch (\Exception $e) {
            throw new ResponseHandlerException(new Phrase($e->getMessage()));
        }
    }
}

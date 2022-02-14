<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

declare(strict_types=1);

namespace SR\Icount\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use SR\Icount\Api\Data\IcountDocInterface;

class IcountDocData extends AbstractSimpleObject implements IcountDocInterface
{
    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        $value = $this->_get(self::ID);
        return $value !== null ? (int)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setId(?int $id): self
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getDocExportedAt(): ?string
    {
        return $this->_get(self::DOC_EXPORTED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setDocExportedAt(?string $value): self
    {
        return $this->setData(self::DOC_EXPORTED_AT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDocLinkageId(): ?int
    {
        $value = $this->_get(self::DOC_LINKAGE_ID);
        return $value !== null ? (int)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setDocLinkageId(?int $value): self
    {
        return $this->setData(self::DOC_LINKAGE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDocNum(): ?int
    {
        $value = $this->_get(self::DOC_NUM);
        return $value !== null ? (int)$value : null;
    }

    /**
     * @inheritDoc
     */
    public function setDocNum(?int $value): self
    {
        return $this->setData(self::DOC_NUM, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDocTypeId(): int
    {
        return (int)$this->_get(self::DOC_TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDocTypeId(int $value): self
    {
        return $this->setData(self::DOC_TYPE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalInformation(): ?array
    {
        return $this->_get(self::ADDITIONAL_INFORMATION);
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalInformation(array $dataset): self
    {
        return $this->setData(self::ADDITIONAL_INFORMATION, $dataset);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $value): self
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $value): self
    {
        return $this->setData(self::CREATED_AT, $value);
    }
}

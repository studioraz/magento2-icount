<?php
/*
 * Copyright © 2022 Studio Raz. All rights reserved.
 * See LICENCE file for license details.
 */

namespace SR\Icount\Api\Data;

interface IcountDocInterface
{
    /**#@+ */
    public const ID = 'id';// unique ID of the records

    public const DOC_EXPORTED_AT = 'doc_exported_at';
    public const DOC_LINKAGE_ID = 'doc_linkage_id';
    public const DOC_NUM = 'doc_num';
    public const DOC_TYPE_ID  = 'doc_type_id';

    public const ADDITIONAL_INFORMATION = 'additional_information';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    //public const  = '';
    /**#@- */


    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Setter for Id.
     *
     * @param int|null $id
     *
     * @return $this
     */
    public function setId(?int $id): self;

    /**
     * Get Date/Time of Export operation
     * NOTE: DateTime when Magento doc was Exported
     *
     * @return string|null
     */
    public function getDocExportedAt(): ?string;

    /**
     * Set Date/Time of Export operation
     * NOTE: DateTime when Magento doc was Exported
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function setDocExportedAt(?string $value): self;

    /**
     * Getter for Doc Linkage ID
     * NOTE: ID of document (given by Magento [local])
     *
     * @return int|null
     */
    public function getDocLinkageId(): ?int;

    /**
     * Setter for Doc Linkage ID
     * NOTE: ID of document (given by Magento [local])
     *
     * @param int|null $value
     *
     * @return $this
     */
    public function setDocLinkageId(?int $value): self;

    /**
     * Getter for Doc Num
     * NOTE: ID of document (given by iCount [remote])
     *
     * @return int|null
     */
    public function getDocNum(): ?int;

    /**
     * Setter for Doc Linkage ID
     * NOTE: ID of document (given by iCount [remote])
     *
     * @param int|null $value
     *
     * @return $this
     */
    public function setDocNum(?int $value): self;

    /**
     * Getter for Doc Num
     * NOTE: ID of icount doc type
     *
     * @return int
     */
    public function getDocTypeId(): int;

    /**
     * Setter for Doc Linkage ID
     * NOTE: ID of icount doc type
     *
     * @param int $value
     *
     * @return $this
     */
    public function setDocTypeId(int $value): self;

    /**
     * Note: this is serializable db-field (array <-> json).
     *
     * @return string[]|null
     */
    public function getAdditionalInformation(): ?array;

    /**
     * Note: this is serializable db-field (array <-> json).
     *
     * @param string[] $dataset
     *
     * @return $this
     */
    public function setAdditionalInformation(array $dataset): self;

    /**
     * Getter for UpdatedAt.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Setter for UpdatedAt.
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function setUpdatedAt(?string $value): self;

    /**
     * Getter for CreatedAt.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Setter for CreatedAt.
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function setCreatedAt(?string $value): self;
}

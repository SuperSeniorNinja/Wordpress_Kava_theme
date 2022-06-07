<?php

namespace MangoPay;

/**
 * Dispute document entity
 */
class DisputeDocument extends Libraries\Document
{
    /**
     * The Dispute that this document belongs to
     * @var string
     */
    public $DisputeId;

    /**
     * Type of dispute document
     * @var string
     * @see \MangoPay\DisputeDocumentType
     */
    public $Type;

    /**
     * Status of dispute document
     * @var string
     * @see \MangoPay\DisputeDocumentStatus
     */
    public $Status;
}

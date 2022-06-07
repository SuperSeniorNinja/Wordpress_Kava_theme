<?php

namespace MangoPay;

/**
 * Class to management MangoPay API for transfers
 */
class ApiTransfers extends Libraries\ApiBase
{
    /**
     * Create new transfer
     * @param \MangoPay\Transfer $transfer
     * @return \MangoPay\Transfer Transfer object returned from API
     */
    public function Create($transfer, $idempotencyKey = null)
    {
        return $this->CreateObject('transfers_create', $transfer, '\MangoPay\Transfer', null, null, $idempotencyKey);
    }

    /**
     * Get transfer
     * @param string $transferId Transfer identifier
     * @return \MangoPay\Transfer Transfer object returned from API
     */
    public function Get($transferId)
    {
        return $this->GetObject('transfers_get', '\MangoPay\Transfer', $transferId);
    }

    /**
     * Create refund for transfer object
     * @param string $transferId Transfer identifier
     * @param \MangoPay\Refund $refund Refund object to create
     * @return \MangoPay\Refund Object returned by REST API
     */
    public function CreateRefund($transferId, $refund, $idempotencyKey = null)
    {
        return $this->CreateObject('transfers_createrefunds', $refund, '\MangoPay\Refund', $transferId, null, $idempotencyKey);
    }

    /**
     * Retrieve list of Refunds pertaining to a certain Transfer
     * @param string $transferId Transfer identifier
     * @param \MangoPay\Pagination $pagination Pagination object
     * @param \MangoPay\FilterRefunds $filter Filtering object
     * @param \MangoPay\Sorting $sorting Sorting object
     * @return \MangoPay\Refund[] List of the Transfer's Refunds
     */
    public function GetRefunds($transferId, & $pagination = null, $filter = null, $sorting = null)
    {
        return $this->GetList('refunds_get_for_transfer', $pagination, '\MangoPay\Refund', $transferId, $filter, $sorting);
    }
}

<?php

namespace MangoPay;

/**
 * Filter for dispute list
 */
class FilterDisputes extends FilterBase
{
    /**
     * @var string
     * @see \MangoPay\DisputeType
     */
    public $DisputeType;

    /**
     * @var string
     * @see DisputeStatus
     */
    public $Status;
}

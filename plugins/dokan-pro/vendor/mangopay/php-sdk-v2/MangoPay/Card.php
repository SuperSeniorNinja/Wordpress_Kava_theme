<?php

namespace MangoPay;

/**
 * Card entity
 */
class Card extends Libraries\EntityBase
{
    /**
     * Expiration date
     * @var string
     */
    public $ExpirationDate;

    /**
     * Alias
     * @var string
     */
    public $Alias;

    /**
     * CardProvider
     * @var string
     */
    public $CardProvider;

    /**
     * UserId
     * @var string
     */
    public $UserId;

    /**
     * Card type
     * @var string
     */
    public $CardType;

    /**
     * Product
     * @var string
     */
    public $Product ;

    /**
     * Bank code
     * @var string
     */
    public $BankCode;

    /**
     * Country
     * @var string
     */
    public $Country;

    /**
     * Active
     * @var bool
     */
    public $Active;

    /**
     * Currency
     * @var string
     */
    public $Currency;

    /**
     * Validity. For allowed values, see constants in MangoPay\CardValidity
     * @var string
     */
    public $Validity;

    /**
     * Card's fingerprint hash, unique per 16-digit card number
     * @var string
     */
    public $Fingerprint;
}

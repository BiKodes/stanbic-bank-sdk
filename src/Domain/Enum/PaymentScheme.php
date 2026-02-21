<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Payment Scheme Enum.
 *
 * Represents the various payment schemes and card types supported.
*/
enum PaymentScheme: string
{
    case PULL_VISA = 'PULL_VISA';
    case PULL_MASTERCARD = 'PULL_MASTERCARD';
    case PULL_AMEX = 'PULL_AMEX';
    case PESALINK = 'PESALINK';
    case EFT = 'EFT';
    case SWIFT = 'SWIFT';
    case RTGS = 'RTGS';
    case LOCAL_TRANSFER = 'LOCAL_TRANSFER';
}

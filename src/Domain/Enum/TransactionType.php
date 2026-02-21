<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Transaction Type Enum.
 *
 * Represents the types of transactions/transfer methods supported.
*/
enum TransactionType: string
{
    case PESALINK = 'PESALINK';
    case SWIFT = 'SWIFT';
    case RTGS = 'RTGS';
    case EFT = 'EFT';
    case STANBIC_TRANSFER = 'STANBIC_TRANSFER';
    case INTER_ACCOUNT = 'INTER_ACCOUNT';
    case MOBILE_WALLET = 'MOBILE_WALLET';
}

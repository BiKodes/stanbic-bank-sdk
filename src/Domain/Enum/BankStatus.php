<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Bank Transaction Status Enum.
 *
 * Represents the various statuses a transaction can have at the bank.
*/
enum BankStatus: string
{
    case PROCESSED = 'PROCESSED';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case PENDING = 'PENDING';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case COMPLETED = 'COMPLETED';
    case ON_HOLD = 'ON_HOLD';
}

<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Notification Type Enum.
 *
 * Represents the types of transaction notifications.
*/
enum NotificationType: string
{
    case CREDIT = 'CREDIT';
    case DEBIT = 'DEBIT';
    case REVERSAL = 'REVERSAL';
    case PENDING = 'PENDING';
    case FAILED = 'FAILED';
}

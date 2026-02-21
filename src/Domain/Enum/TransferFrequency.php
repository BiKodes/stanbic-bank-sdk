<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Transfer Frequency Enum.
 *
 * Represents recurring transfer frequency options.
*/
enum TransferFrequency: string
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case QUARTERLY = 'QUARTERLY';
    case YEARLY = 'YEARLY';
    case ONE_TIME = 'ONE_TIME';
}

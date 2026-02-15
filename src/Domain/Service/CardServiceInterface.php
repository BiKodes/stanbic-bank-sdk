<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface CardServiceInterface
{
    /**
     * Retrieve card information by PAN.
     *
     * @param string $pan Primary Account Number
     * @return mixed CardDetailsResponse
    */
    public function getCardDetails(string $pan): mixed;

    /**
     * Get customer card details with authentication.
     *
     * @param mixed $request CustomerCardDetailsRequest
     * @return mixed CustomerCardDetailsResponse
    */
    public function getCustomerCardDetails(mixed $request): mixed;
}

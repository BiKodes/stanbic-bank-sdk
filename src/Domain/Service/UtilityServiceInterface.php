<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface UtilityServiceInterface
{
    /**
     * Get bank sort codes by transaction type.
     *
     * @param string $transactionType PESALINK, SWIFT, RTGS, EFT, etc.
     * @return mixed[] Array of SortCodeResponse
    */
    public function fetchSortCodes(string $transactionType): mixed;

    /**
     * Retrieve SWIFT/BIC codes by country.
     *
     * @param string $countryCode ISO country code
     * @return mixed[] Array of SwiftCodeResponse
    */
    public function getSwiftCode(string $countryCode): mixed;

    /**
     * Get branch details by SWIFT branch code.
     *
     * @param string $branchCode SWIFT branch code
     * @return mixed SwiftBranchResponse
    */
    public function getSwiftBranch(string $branchCode): mixed;
}

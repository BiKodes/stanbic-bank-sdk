<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Service;

interface AccountServiceInterface
{
    /**
     * Retrieve account balance.
     *
     * @return mixed BalanceResponse
    */
    public function getBalance(): mixed;

    /**
     * Get account transactions with pagination.
     *
     * @param string $bookingDateGreaterThan Start date (YYYYMMDD format)
     * @param string $bookingDateLessThan End date (YYYYMMDD format)
     * @param mixed $page Page object with from/size
     * @return mixed PagedResult of transactions
    */
    public function fetchStatements(
        string $bookingDateGreaterThan,
        string $bookingDateLessThan,
        mixed $page
    ): mixed;

    /**
     * Query transaction status by reference ID.
     *
     * @param string $dbsReferenceId DBS transaction reference
     * @return mixed TransactionStatusResponse
    */
    public function getTransactionStatus(string $dbsReferenceId): mixed;
}

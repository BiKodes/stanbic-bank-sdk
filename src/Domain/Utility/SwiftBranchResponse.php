<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Utility;

/**
 * Swift Branch Response.
 *
 * API: GET /swift-codes?branchCode={branchCode}
 * Response contains branch details for a given branch code.
 *
 * @psalm-immutable
*/
final class SwiftBranchResponse
{
    public function __construct(
        public readonly string $branchCode,
        public readonly string $branchName,
        public readonly string $sortCode,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            branchCode: (string) ($data['branchCode'] ?? ''),
            branchName: (string) ($data['branchName'] ?? ''),
            sortCode: (string) ($data['sortCode'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'branchCode' => $this->branchCode,
            'branchName' => $this->branchName,
            'sortCode' => $this->sortCode,
        ];
    }
}

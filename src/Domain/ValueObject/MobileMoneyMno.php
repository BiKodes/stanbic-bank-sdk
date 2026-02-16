<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Mobile money operator details.
 *
 * @psalm-immutable
*/
final class MobileMoneyMno
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}

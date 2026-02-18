<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Originator account details.
 *
 * @psalm-immutable
*/
final class OriginatorAccount
{
    public function __construct(
        public readonly OriginatorIdentification $identification,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|OriginatorIdentification|null $idData */
        $idData = $data['identification'] ?? null;
        if ($idData instanceof OriginatorIdentification) {
            $identification = $idData;
        } elseif (is_array($idData)) {
            /** @var array<string, mixed> $idArray */
            $idArray = $idData;
            $identification = OriginatorIdentification::fromArray($idArray);
        } else {
            $identification = OriginatorIdentification::fromArray([]);
        }

        return new self($identification);
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'identification' => $this->identification->toArray(),
        ];
    }
}

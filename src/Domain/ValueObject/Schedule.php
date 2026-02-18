<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * Payment schedule details.
 *
 * @psalm-immutable
*/
final class Schedule
{
    public function __construct(
        public readonly ?string $transferFrequency = null,
        public readonly ?string $on = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $repeat = null,
        public readonly ?string $every = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            transferFrequency: isset($data['transferFrequency']) || isset($data['transfer_frequency'])
                ? (string) ($data['transferFrequency'] ?? $data['transfer_frequency'])
                : null,
            on: isset($data['on']) ? (string) $data['on'] : null,
            startDate: isset($data['startDate']) || isset($data['start_date'])
                ? (string) ($data['startDate'] ?? $data['start_date'])
                : null,
            endDate: isset($data['endDate']) || isset($data['end_date'])
                ? (string) ($data['endDate'] ?? $data['end_date'])
                : null,
            repeat: isset($data['repeat']) ? (string) $data['repeat'] : null,
            every: isset($data['every']) ? (string) $data['every'] : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->transferFrequency !== null) {
            $data['transferFrequency'] = $this->transferFrequency;
        }

        if ($this->on !== null) {
            $data['on'] = $this->on;
        }

        if ($this->startDate !== null) {
            $data['startDate'] = $this->startDate;
        }

        if ($this->endDate !== null) {
            $data['endDate'] = $this->endDate;
        }

        if ($this->repeat !== null) {
            $data['repeat'] = $this->repeat;
        }

        if ($this->every !== null) {
            $data['every'] = $this->every;
        }

        return $data;
    }
}

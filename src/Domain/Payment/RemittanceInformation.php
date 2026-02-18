<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Payment;

/**
 * Remittance information.
 *
 * @psalm-immutable
*/
final class RemittanceInformation
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?string $content = null,
        public readonly ?string $unstructured = null,
        public readonly ?string $reference = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            type: isset($data['type']) ? (string) $data['type'] : null,
            content: isset($data['content']) ? (string) $data['content'] : null,
            unstructured: isset($data['unstructured']) ? (string) $data['unstructured'] : null,
            reference: isset($data['reference']) ? (string) $data['reference'] : null,
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [];

        if ($this->type !== null) {
            $data['type'] = $this->type;
        }

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->unstructured !== null) {
            $data['unstructured'] = $this->unstructured;
        }

        if ($this->reference !== null) {
            $data['reference'] = $this->reference;
        }

        return $data;
    }
}

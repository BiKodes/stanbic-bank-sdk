<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Card;

/**
 * Interaction value object for card interactions.
 *
 * @psalm-immutable
 */
final class Interaction
{
    public function __construct(
        public readonly ?string $originInteractionID = null,
        public readonly ?string $GetInteractionID = null,
        public readonly ?string $entersektInteractionID = null,
        public readonly ?string $interactionDateTime = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var string|null $entersektId */
        $entersektId = isset($data['entersektInteractionID'])
            ? (string) $data['entersektInteractionID']
            : null;

        return new self(
            originInteractionID: isset($data['originInteractionID'])
                ? (string) $data['originInteractionID']
                : null,
            GetInteractionID: isset($data['GetInteractionID'])
                ? (string) $data['GetInteractionID']
                : null,
            entersektInteractionID: $entersektId,
            interactionDateTime: isset($data['interactionDateTime'])
                ? (string) $data['interactionDateTime']
                : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->originInteractionID !== null) {
            $data['originInteractionID'] = $this->originInteractionID;
        }

        if ($this->GetInteractionID !== null) {
            $data['GetInteractionID'] = $this->GetInteractionID;
        }

        if ($this->entersektInteractionID !== null) {
            $data['entersektInteractionID'] = $this->entersektInteractionID;
        }

        if ($this->interactionDateTime !== null) {
            $data['interactionDateTime'] = $this->interactionDateTime;
        }

        if ($this->type !== null) {
            $data['type'] = $this->type;
        }

        return $data;
    }
}

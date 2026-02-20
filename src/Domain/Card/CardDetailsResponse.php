<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Card;

/**
 * Card Details Response.
 *
 * @psalm-immutable
 */
final class CardDetailsResponse
{
    /**
     * @param array<string, mixed>|null $result
     */
    public function __construct(
        public readonly string $messageID,
        public readonly Interaction $interaction,
        public readonly Card $card,
        public readonly ?array $result = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed>|Interaction|null $interactionData */
        $interactionData = $data['interaction'] ?? null;
        if ($interactionData instanceof Interaction) {
            $interaction = $interactionData;
        } elseif (is_array($interactionData)) {
            /** @var array<string, mixed> $interactionArray */
            $interactionArray = $interactionData;
            $interaction = Interaction::fromArray($interactionArray);
        } else {
            $interaction = Interaction::fromArray([]);
        }

        /** @var array<string, mixed>|Card|null $cardData */
        $cardData = $data['card'] ?? null;
        if ($cardData instanceof Card) {
            $card = $cardData;
        } elseif (is_array($cardData)) {
            /** @var array<string, mixed> $cardArray */
            $cardArray = $cardData;
            $card = Card::fromArray($cardArray);
        } else {
            $card = Card::fromArray([]);
        }

        /** @var array<string, mixed>|null $resultData */
        $resultData = $data['result'] ?? null;
        $result = null;
        if (is_array($resultData)) {
            $result = $resultData;
        }

        return new self(
            messageID: (string) ($data['messageID'] ?? ''),
            interaction: $interaction,
            card: $card,
            result: $result,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'messageID' => $this->messageID,
            'interaction' => $this->interaction->toArray(),
            'card' => $this->card->toArray(),
        ];

        if ($this->result !== null) {
            $data['result'] = $this->result;
        }

        return $data;
    }
}

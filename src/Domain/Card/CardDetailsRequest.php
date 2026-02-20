<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Card;

/**
 * Card Details Request.
 *
 * @psalm-immutable
 */
final class CardDetailsRequest
{
    public function __construct(
        public readonly string $messageID,
        public readonly string $bankingID,
        public readonly Card $card,
        public readonly Interaction $interaction,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
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

        return new self(
            messageID: (string) ($data['messageID'] ?? ''),
            bankingID: (string) ($data['bankingID'] ?? ''),
            card: $card,
            interaction: $interaction,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'messageID' => $this->messageID,
            'bankingID' => $this->bankingID,
            'card' => $this->card->toArray(),
            'interaction' => $this->interaction->toArray(),
        ];
    }
}

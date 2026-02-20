<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Card;

/**
 * Customer Card Details Response.
 *
 * @psalm-immutable
*/
final class CustomerCardDetailsResponse
{
    /**
     * @param array<string, mixed>|null $result
     * @param array<string, mixed>|null $customer
    */
    public function __construct(
        public readonly string $messageID,
        public readonly Interaction $interaction,
        public readonly ?array $result = null,
        public readonly ?array $customer = null,
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

        /** @var array<string, mixed>|null $resultData */
        $resultData = $data['result'] ?? null;
        $result = null;
        if (is_array($resultData)) {
            $result = $resultData;
        }

        /** @var array<string, mixed>|null $customerData */
        $customerData = $data['customer'] ?? null;
        $customer = null;
        if (is_array($customerData)) {
            $customer = $customerData;
        }

        return new self(
            messageID: (string) ($data['messageID'] ?? ''),
            interaction: $interaction,
            result: $result,
            customer: $customer,
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
        ];

        if ($this->result !== null) {
            $data['result'] = $this->result;
        }

        if ($this->customer !== null) {
            $data['customer'] = $this->customer;
        }

        return $data;
    }
}

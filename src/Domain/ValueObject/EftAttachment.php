<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\ValueObject;

/**
 * EFT attachment details.
 *
 * @psalm-immutable
*/
final class EftAttachment
{
    public function __construct(
        public readonly string $attachmentFileId,
        public readonly string $attachmentFileName,
    ) {
    }

    /**
     * @param array<string, mixed> $data
    */
    public static function fromArray(array $data): self
    {
        return new self(
            attachmentFileId: (string) ($data['AttachmentFileId'] ?? $data['attachmentFileId'] ?? ''),
            attachmentFileName: (string) ($data['AttachmentFileName'] ?? $data['attachmentFileName'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        return [
            'AttachmentFileId' => $this->attachmentFileId,
            'AttachmentFileName' => $this->attachmentFileName,
        ];
    }
}

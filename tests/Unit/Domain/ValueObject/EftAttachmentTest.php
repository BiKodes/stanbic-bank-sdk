<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\ValueObject\EftAttachment;

final class EftAttachmentTest extends TestCase
{
    public function testCreateEftAttachment(): void
    {
        $attachment = new EftAttachment(
            attachmentFileId: 'doc.id.12345',
            attachmentFileName: 'Sand-Man.pdf'
        );

        $this->assertSame('doc.id.12345', $attachment->attachmentFileId);
        $this->assertSame('Sand-Man.pdf', $attachment->attachmentFileName);
    }

    public function testFromArray(): void
    {
        $data = [
            'AttachmentFileId' => 'doc.id.67890',
            'AttachmentFileName' => 'Octopus.pdf',
        ];

        $attachment = EftAttachment::fromArray($data);

        $this->assertSame('doc.id.67890', $attachment->attachmentFileId);
        $this->assertSame('Octopus.pdf', $attachment->attachmentFileName);
    }

    public function testToArray(): void
    {
        $attachment = new EftAttachment(
            attachmentFileId: 'doc.id.00001',
            attachmentFileName: 'Venom.pdf'
        );

        $array = $attachment->toArray();

        $this->assertSame('doc.id.00001', $array['AttachmentFileId']);
        $this->assertSame('Venom.pdf', $array['AttachmentFileName']);
    }
}

<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Placeholder;

/**
 * Placeholder test for Phase 0 CI pipeline.
 * Will be replaced with actual tests in Phase 1+.
 */
final class PlaceholderTest extends TestCase
{
    public function testPlaceholder(): void
    {
        $this->assertSame('ok', Placeholder::ping());
    }
}

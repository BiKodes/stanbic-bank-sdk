<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Enum;

/**
 * Serialization Format Enum.
 *
 * Represents the supported serialization formats for API payloads.
*/
enum SerializationFormat: string
{
    case JSON = 'json';
    case XML = 'xml';
}

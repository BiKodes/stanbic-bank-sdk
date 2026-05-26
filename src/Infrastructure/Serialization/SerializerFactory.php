<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Serialization;

/**
 * Factory for creating serializer instances based on format.
 *
 * Provides a convenient interface to get the appropriate serializer
 * implementation for a given content type or format name.
 */
final class SerializerFactory
{
    /**
     * Create a serializer for the given format.
     *
     * @param string $format The format name: 'json', 'xml', or a content type like 'application/json'
     * @return SerializerInterface
     *
     * @throws SerializationException If the format is not supported
     */
    public static function create(string $format): SerializerInterface
    {
        $normalized = strtolower(trim($format));

        return match ($normalized) {
            'json', 'application/json' => new JsonSerializer(),
            'xml', 'application/xml', 'text/xml' => new XmlSerializer(),
            default => throw new SerializationException(
                sprintf('Unsupported serialization format: %s', $format),
            ),
        };
    }

    /**
     * Get a JSON serializer.
     *
     * @return SerializerInterface
     */
    public static function json(): SerializerInterface
    {
        return new JsonSerializer();
    }

    /**
     * Get an XML serializer.
     *
     * @return SerializerInterface
     */
    public static function xml(): SerializerInterface
    {
        return new XmlSerializer();
    }
}

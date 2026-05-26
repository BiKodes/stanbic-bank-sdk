<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Serialization;

/**
 * Strategy interface for serializing and deserializing data.
 *
 * Implementations support multiple formats (JSON, XML, etc.) and handle
 * conversion between PHP values and their serialized representations.
 */
interface SerializerInterface
{
    /**
     * Serialize a PHP value to a string.
     *
     * @param mixed $data The value to serialize
     * @return string The serialized data
     *
     * @throws SerializationException If serialization fails
     */
    public function serialize(mixed $data): string;

    /**
     * Deserialize a string to a PHP object of a given class.
     *
     * @param string $data The serialized data
     * @param string $className Fully qualified class name to deserialize into
     * @return mixed The deserialized object (or mixed value if $className is flexible)
     *
     * @throws SerializationException If deserialization fails or class is invalid
     */
    public function deserialize(string $data, string $className): mixed;
}

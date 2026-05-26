<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Serialization;

use ReflectionClass;

/**
 * JSON serializer with support for PHP 8.1+ named arguments.
 *
 * Deserializes JSON strings into typed objects by inspecting constructor
 * parameters and respecting PHP 8.1 union types and named arguments.
 */
final class JsonSerializer implements SerializerInterface
{
    /**
     * Serialize a PHP value to JSON string.
     *
     * @param mixed $data
     * @return string
     *
     * @throws SerializationException
     */
    public function serialize(mixed $data): string
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            throw new SerializationException(
                sprintf('Failed to serialize JSON: %s', $e->getMessage()),
                previous: $e,
            );
        }

        /** @var string $json */
        return $json;
    }

    /**
     * Deserialize JSON string into a typed object.
     *
     * Decodes the JSON and hydrates the target class with named constructor arguments.
     *
     * @param string $data JSON string
     * @param string $className Fully qualified class name
     * @return mixed The instantiated object
     *
     * @throws SerializationException
     */
    public function deserialize(string $data, string $className): mixed
    {
        try {
            $decoded = json_decode($data, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializationException(
                sprintf('Failed to deserialize JSON: %s', $e->getMessage()),
                previous: $e,
            );
        }

        // Psalm: decoded is array<array-key, mixed>|stdClass|null after json_decode with associative: true
        // it should be array<string, mixed> but Psalm infers broader type due to json_decode signature
        if (!is_array($decoded)) {
            throw new SerializationException(
                sprintf('Expected JSON object, got %s', gettype($decoded)),
            );
        }

        // Ensure we have an associative array (object-like) rather than a list
        if (array_is_list($decoded)) {
            throw new SerializationException('Expected JSON object, got list');
        }

        /** @var array<string,mixed> $assoc */
        $assoc = $decoded;

        return $this->hydrate($assoc, $className);
    }

    /**
     * Hydrate an object using the decoded array and constructor parameter names.
     *
     * @param array<string, mixed> $data
     * @param string $className
     * @return object
     *
     * @throws SerializationException
     */
    private function hydrate(array $data, string $className): object
    {
        try {
            /** @var class-string $className */
            $reflection = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new SerializationException(
                sprintf('Class not found: %s', $className),
                previous: $e,
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        /** @var array<int<0, max>|string, mixed> $args */
        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            $paramValue = null;
            if (array_key_exists($paramName, $data)) {
                /** @var mixed $paramValue */
                $paramValue = $data[$paramName];
            }

            if ($paramValue === null && !$param->isDefaultValueAvailable() && !$param->allowsNull()) {
                throw new SerializationException(
                    sprintf(
                        'Missing required parameter "%s" for constructor of %s',
                        $paramName,
                        $className,
                    ),
                );
            }

            /** @psalm-suppress MixedAssignment */
            $args[] = $paramValue;
        }

        return $reflection->newInstanceArgs($args);
    }

}

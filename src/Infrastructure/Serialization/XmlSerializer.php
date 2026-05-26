<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Serialization;

use DOMDocument;
use DOMException;
use ReflectionClass;

/**
 * XML serializer with support for Transaction Notification API format.
 *
 * Encodes PHP objects/arrays to XML and decodes XML strings back to typed objects,
 * supporting the nested structure of Stanbic's Transaction Notification API.
 */
final class XmlSerializer implements SerializerInterface
{
    /**
     * Root element name for serialized objects.
     */
    private const string ROOT_ELEMENT_NAME = 'root';

    /**
     * Serialize a PHP value to XML string.
     *
     * @param mixed $data
     * @return string XML string
     *
     * @throws SerializationException
     */
    public function serialize(mixed $data): string
    {
        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = true;

            $root = $doc->createElement(self::ROOT_ELEMENT_NAME);
            $doc->appendChild($root);

            $this->valueToXmlElement($doc, $root, $data);

            $xml = $doc->saveXML();

            if ($xml === false) {
                throw new SerializationException('Failed to serialize data to XML');
            }

            return $xml;
        } catch (DOMException $e) {
            throw new SerializationException(
                sprintf('XML serialization error: %s', $e->getMessage()),
                previous: $e,
            );
        }
    }

    /**
     * Deserialize XML string to a typed object.
     *
     * @param string $data XML string
     * @param string $className Fully qualified class name
     * @return mixed The instantiated object
     *
     * @throws SerializationException
     */
    public function deserialize(string $data, string $className): mixed
    {
        $dataNonEmpty = trim($data);
        if ($dataNonEmpty === '') {
            throw new SerializationException('Empty XML document');
        }

        try {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;

            /** @var non-empty-string $dataNonEmpty */
            $loaded = @$doc->loadXML($dataNonEmpty);

            if ($loaded === false) {
                throw new SerializationException('Invalid XML data');
            }

            $root = $doc->documentElement;

            if (!$root instanceof \DOMElement) {
                throw new SerializationException('Empty XML document');
            }

            $decoded = $this->xmlElementToValue($root);

            if (!is_array($decoded)) {
                $decoded = [$root->localName ?? $root->nodeName => $decoded];
            }

            /** @var array<string,mixed> $assoc */
            $assoc = $decoded;

            return $this->hydrate($assoc, $className);
        } catch (DOMException $e) {
            throw new SerializationException(
                sprintf('XML deserialization error: %s', $e->getMessage()),
                previous: $e,
            );
        }
    }

    /**
     * Convert a PHP value to XML elements recursively.
     *
     * @param DOMDocument $doc
     * @param \DOMElement $parentElement
     * @param mixed $value
     * @return void
     *
     * @throws DOMException
     */
    private function valueToXmlElement(DOMDocument $doc, \DOMElement $parentElement, mixed $value): void
    {
        if (is_array($value)) {
            /** @var array<array-key,mixed> $value */
            $this->appendArrayValues($doc, $parentElement, $value);
        } elseif (is_object($value)) {
            /** @var object $value */
            $objectVars = get_object_vars($value);
            /** @var array<string,mixed> $objectVars */
            $this->appendArrayValues($doc, $parentElement, $objectVars);
        } elseif (is_bool($value)) {
            $parentElement->nodeValue = $value ? 'true' : 'false';
        } elseif ($value !== null) {
            $parentElement->nodeValue = (string) $value;
        }
    }

    /**
     * Append array/object properties as child elements.
     *
     * @param array<array-key,mixed> $items
     */
    private function appendArrayValues(DOMDocument $doc, \DOMElement $parentElement, array $items): void
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($items as $key => $itemValue) {
            $elementName = is_string($key) ? $key : 'item';
            $element = $doc->createElement($elementName);
            $parentElement->appendChild($element);
            $this->valueToXmlElement($doc, $element, $itemValue);
        }
    }

    /**
     * Convert an XML element to a PHP value recursively.
     *
     * @param \DOMElement $element
     * @return array<string,mixed>|string|null
     */
    private function xmlElementToValue(\DOMElement $element): array|string|null
    {
        /** @var array<string,mixed> */
        $result = [];
        $hasElementChildren = false;

        /** @var \DOMNode $node */
        foreach ($element->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $hasElementChildren = true;
                $name = $node->localName ?? $node->nodeName;
                /** @var array<string,mixed>|string|null $value */
                $value = $this->xmlElementToValue($node);

                if (array_key_exists($name, $result)) {
                    if (!is_array($result[$name]) || array_is_list($result[$name]) === false) {
                        $result[$name] = [$result[$name]];
                    }

                    $result[$name][] = $value;
                } else {
                    $result[$name] = $value;
                }
            } elseif ($node instanceof \DOMText) {
                // collect text nodes
                $text = (string) $node->nodeValue;
                if ($text !== '' && trim($text) !== '') {
                    $existing = isset($result['__text']) && is_string($result['__text']) ? $result['__text'] : '';
                    $result['__text'] = $existing . $text;
                }
            }
        }

        if (!$hasElementChildren) {
            if (isset($result['__text']) && is_string($result['__text'])) {
                $text = trim($result['__text']);
            } else {
                $text = trim($element->textContent);
            }

            return $text === '' ? null : $text;
        }

        // remove potential temporary __text key
        if (isset($result['__text'])) {
            unset($result['__text']);
        }

        /** @var array<string,mixed> $result */
        return $result;
    }

    /**
     * Hydrate an object from the decoded XML array.
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
                /** @psalm-suppress MixedAssignment */
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

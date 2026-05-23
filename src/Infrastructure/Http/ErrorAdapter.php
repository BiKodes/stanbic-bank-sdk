<?php

declare(strict_types=1);

namespace Stanbic\SDK\Infrastructure\Http;

use Stanbic\SDK\Domain\Exception\ApiErrorMapping;
use Stanbic\SDK\Domain\Exception\StanbicException;
use Stanbic\SDK\Domain\Value\ApiError;

/**
 * Error Adapter.
 *
 * Normalizes Stanbic sandbox error payloads into a single internal representation
 * and maps them to the SDK exception hierarchy.
 */
class ErrorAdapter
{
    public function normalize(string $body, ?string $contentType = null, ?int $statusCode = null): ApiError
    {
        $payload = $this->parseBody($body, $contentType);

        if ($payload === []) {
            return $this->fallbackError($body, $statusCode);
        }

        return $this->normalizePayload($payload, $statusCode);
    }

    public function toException(string $body, ?string $contentType = null, ?int $statusCode = null): StanbicException
    {
        $error = $this->normalize($body, $contentType, $statusCode);

        return ApiErrorMapping::fromError(
            $error->getCode(),
            $error->getMessage(),
            $statusCode,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function normalizePayload(array $payload, ?int $statusCode = null): ApiError
    {
        $payload = $this->unwrapPayload($payload);

        $code = $this->stringValue($this->firstValue($payload, [
            'errorCode',
            'ErrorCode',
            'responseCode',
            'ResponseCode',
            'ResultCode',
            'resultCode',
            'code',
            'status',
            'reasonCode',
            'ReasonCode',
        ]));

        $message = $this->stringValue($this->firstValue($payload, [
            'errorMessage',
            'ErrorMessage',
            'message',
            'Message',
            'responseMessage',
            'ResponseMessage',
            'ResultDesc',
            'resultDesc',
            'reasonText',
            'ReasonText',
            'detail',
            'Detail',
        ]));

        $detail = $this->buildDetail($payload, [
            'detail',
            'Detail',
            'status',
            'Status',
            'data',
            'Data',
            'dbsReferenceId',
            'referenceId',
            'ReferenceId',
            'RefereneceId',
            'DatetimeStamp',
            'datetimeStamp',
            'bankStatus',
            'BankStatus',
            'bankReferenceId',
            'BankReferenceId',
        ]);

        if ($code === '') {
            $code = $statusCode !== null ? (string) $statusCode : 'UNKNOWN';
        }

        if ($message === '') {
            $message = $this->fallbackMessage($statusCode);
        }

        return new ApiError($code, $message, $detail);
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $keys
     * @return mixed
     */
    private function firstValue(array $payload, array $keys): mixed
    {
        $lookup = $this->canonicalMap($payload);

        foreach ($keys as $key) {
            $canonical = $this->canonicalKey($key);

            if (array_key_exists($canonical, $lookup)) {
                return $lookup[$canonical];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $keys
     */
    private function buildDetail(array $payload, array $keys): ?string
    {
        $values = [];
        $lookup = $this->canonicalMap($payload);

        foreach ($keys as $key) {
            $canonical = $this->canonicalKey($key);

            if (!array_key_exists($canonical, $lookup)) {
                continue;
            }

            $value = $this->stringValue($lookup[$canonical]);
            if ($value !== '') {
                $values[] = $value;
            }
        }

        if ($values === []) {
            return null;
        }

        return implode('; ', array_values(array_unique($values)));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function unwrapPayload(array $payload): array
    {
        foreach (['error', 'Error', 'Result', 'result'] as $key) {
            /** @var mixed $value */
            $value = $this->valueForKey($payload, $key);

            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                return $value;
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @psalm-suppress MixedAssignment
     */
    private function canonicalMap(array $payload): array
    {
        /** @var array<string, mixed> $map */
        $map = [];

        /** @var mixed $key */
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            /** @var mixed $value */
            $map[$this->canonicalKey($key)] = $value;
        }

        return $map;
    }

    private function canonicalKey(string $key): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '', $key));
    }

    /**
     * @param array<string, mixed> $payload
     * @return mixed
     */
    private function valueForKey(array $payload, string $key): mixed
    {
        $lookup = $this->canonicalMap($payload);
        $canonical = $this->canonicalKey($key);

        return $lookup[$canonical] ?? null;
    }

    /**
     * @param mixed $value
     */
    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return trim((string) $value);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function parseBody(string $body, ?string $contentType = null): array
    {
        $body = trim($body);

        if ($body === '') {
            return [];
        }

        $isXml = $contentType !== null && str_contains(strtolower($contentType), 'xml');
        $isJson = $contentType !== null && str_contains(strtolower($contentType), 'json');

        if ($isXml || (!$isJson && str_starts_with($body, '<'))) {
            $xml = $this->parseXml($body);
            if ($xml !== []) {
                return $xml;
            }
        }

        $json = $this->parseJson($body);
        if ($json !== []) {
            return $json;
        }

        if (!$isXml) {
            return $this->parseXml($body);
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJson(string $body): array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseXml(string $body): array
    {
        if (!$this->hasSimpleXmlSupport()) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            libxml_clear_errors();
            return [];
        }

        /** @var mixed $json */
        $json = $this->decodeXml($xml);

        if (!is_array($json)) {
            return [];
        }

        /** @var array<string, mixed> $json */
        return $json;
    }

    /**
     * @return mixed
     */
    protected function decodeXml(\SimpleXMLElement $xml): mixed
    {
        return json_decode(json_encode($xml, JSON_THROW_ON_ERROR), true);
    }

    protected function hasSimpleXmlSupport(): bool
    {
        return class_exists(\SimpleXMLElement::class);
    }

    private function fallbackError(string $body, ?int $statusCode = null): ApiError
    {
        $code = $statusCode !== null ? (string) $statusCode : 'UNKNOWN';
        $message = $this->fallbackMessage($statusCode);
        $detail = trim($body) !== '' ? trim($body) : null;

        return new ApiError($code, $message, $detail);
    }

    private function fallbackMessage(?int $statusCode = null): string
    {
        return $statusCode !== null
            ? sprintf('HTTP error %d', $statusCode)
            : 'Unknown error';
    }
}

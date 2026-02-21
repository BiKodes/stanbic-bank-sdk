<?php

declare(strict_types=1);

namespace Stanbic\SDK\Domain\Value;

/**
 * API Error Value Object.
 *
 * Normalized representation of errors from various API endpoints.
 * Handles different error response formats: errorCode/errorMessage, responseCode/responseMessage, etc.
 *
 * @psalm-immutable
*/
final class ApiError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $detail = null,
    ) {
    }

    /**
     * Create ApiError from normalized data.
     *
     * Supports multiple error response formats:
     * - errorCode/errorMessage (Utilities, Notifications)
     * - responseCode/responseMessage (RegisterUrl)
     * - ResultCode/ResultDesc (TransactionNotification)
     *
     * @param array<string, mixed> $data Error response data
    */
    public static function fromArray(array $data): self
    {
        $code = (string) (
            $data['errorCode']
            ?? $data['code']
            ?? $data['responseCode']
            ?? $data['ResultCode']
            ?? $data['resultCode']
            ?? 'UNKNOWN'
        );

        $message = (string) (
            $data['errorMessage']
            ?? $data['message']
            ?? $data['responseMessage']
            ?? $data['ResultDesc']
            ?? $data['resultDesc']
            ?? $data['ErrorMessage']
            ?? 'Unknown error'
        );

        $detail = isset($data['detail']) ? (string) $data['detail'] : null;

        return new self(
            code: trim($code),
            message: trim($message),
            detail: isset($detail) ? trim($detail) : null,
        );
    }

    /**
     * Get error code.
    */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get error message.
    */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get optional error detail.
    */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * Check if error is a success code (2xx-like).
     *
     * @return bool True if code looks like success
    */
    public function isSuccess(): bool
    {
        return in_array($this->code, ['200', '0', 'SUCCESS', 'OK'], true);
    }

    /**
     * Check if error is a client error (4xx-like).
    */
    public function isClientError(): bool
    {
        return str_starts_with($this->code, '4')
            || in_array($this->code, ['INVALID', 'NOT_FOUND', 'BAD_REQUEST'], true);
    }

    /**
     * Check if error is a server error (5xx-like).
    */
    public function isServerError(): bool
    {
        return str_starts_with($this->code, '5')
            || in_array($this->code, ['INTERNAL_ERROR', 'SERVICE_UNAVAILABLE'], true);
    }

    /**
     * Export as array.
     *
     * @return array<string, mixed>
    */
    public function toArray(): array
    {
        $data = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if ($this->detail !== null) {
            $data['detail'] = $this->detail;
        }

        return $data;
    }

    /**
     * String representation.
    */
    public function __toString(): string
    {
        $str = $this->code . ': ' . $this->message;

        if ($this->detail !== null) {
            $str .= ' (' . $this->detail . ')';
        }

        return $str;
    }
}

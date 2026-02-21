<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Domain\Value;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Value\ApiError;

final class ApiErrorTest extends TestCase
{
    public function testCanBeCreatedWithCodeAndMessage(): void
    {
        $error = new ApiError(
            code: '2001',
            message: 'ZERO RECORDS',
        );

        $this->assertSame('2001', $error->code);
        $this->assertSame('ZERO RECORDS', $error->message);
        $this->assertNull($error->detail);
    }

    public function testCanBeCreatedWithDetail(): void
    {
        $error = new ApiError(
            code: '400',
            message: 'Bad Request',
            detail: 'Invalid amount provided',
        );

        $this->assertSame('400', $error->code);
        $this->assertSame('Bad Request', $error->message);
        $this->assertSame('Invalid amount provided', $error->detail);
    }

    public function testFromArrayWithErrorCodePattern(): void
    {
        $data = [
            'errorCode' => '2001',
            'errorMessage' => 'ZERO RECORDS',
        ];

        $error = ApiError::fromArray($data);

        $this->assertSame('2001', $error->getCode());
        $this->assertSame('ZERO RECORDS', $error->getMessage());
    }

    public function testFromArrayWithResponseCodePattern(): void
    {
        $data = [
            'responseCode' => '200',
            'responseMessage' => 'Success',
        ];

        $error = ApiError::fromArray($data);

        $this->assertSame('200', $error->getCode());
        $this->assertSame('Success', $error->getMessage());
    }

    public function testFromArrayWithResultCodePattern(): void
    {
        $data = [
            'ResultCode' => '0',
            'ResultDesc' => 'Transaction successful',
        ];

        $error = ApiError::fromArray($data);

        $this->assertSame('0', $error->getCode());
        $this->assertSame('Transaction successful', $error->getMessage());
    }

    public function testFromArrayWithDetail(): void
    {
        $data = [
            'errorCode' => '500',
            'errorMessage' => 'Internal Server Error',
            'detail' => 'Database connection failed',
        ];

        $error = ApiError::fromArray($data);

        $this->assertSame('500', $error->getCode());
        $this->assertSame('Internal Server Error', $error->getMessage());
        $this->assertSame('Database connection failed', $error->getDetail());
    }

    public function testGetCode(): void
    {
        $error = new ApiError(code: '404', message: 'Not Found');

        $this->assertSame('404', $error->getCode());
    }

    public function testGetMessage(): void
    {
        $error = new ApiError(code: '500', message: 'Server Error');

        $this->assertSame('Server Error', $error->getMessage());
    }

    public function testGetDetail(): void
    {
        $error = new ApiError(
            code: '400',
            message: 'Bad Request',
            detail: 'Missing required field',
        );

        $this->assertSame('Missing required field', $error->getDetail());
    }

    public function testIsSuccess(): void
    {
        $success = new ApiError(code: '200', message: 'OK');
        $success2 = new ApiError(code: 'SUCCESS', message: 'Operation successful');
        $failure = new ApiError(code: '500', message: 'Error');

        $this->assertTrue($success->isSuccess());
        $this->assertTrue($success2->isSuccess());
        $this->assertFalse($failure->isSuccess());
    }

    public function testIsClientError(): void
    {
        $clientError = new ApiError(code: '400', message: 'Bad Request');
        $clientError2 = new ApiError(code: 'NOT_FOUND', message: 'Record not found');
        $serverError = new ApiError(code: '500', message: 'Server Error');

        $this->assertTrue($clientError->isClientError());
        $this->assertTrue($clientError2->isClientError());
        $this->assertFalse($serverError->isClientError());
    }

    public function testIsServerError(): void
    {
        $serverError = new ApiError(code: '500', message: 'Server Error');
        $serverError2 = new ApiError(code: 'SERVICE_UNAVAILABLE', message: 'Service down');
        $clientError = new ApiError(code: '400', message: 'Client Error');

        $this->assertTrue($serverError->isServerError());
        $this->assertTrue($serverError2->isServerError());
        $this->assertFalse($clientError->isServerError());
    }

    public function testToArray(): void
    {
        $error = new ApiError(
            code: '404',
            message: 'Not Found',
            detail: 'Resource does not exist',
        );

        $array = $error->toArray();

        $this->assertSame('404', $array['code']);
        $this->assertSame('Not Found', $array['message']);
        $this->assertSame('Resource does not exist', $array['detail']);
    }

    public function testToArrayWithoutDetail(): void
    {
        $error = new ApiError(code: '200', message: 'OK');

        $array = $error->toArray();

        $this->assertArrayHasKey('code', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayNotHasKey('detail', $array);
    }

    public function testToString(): void
    {
        $error = new ApiError(code: '2001', message: 'ZERO RECORDS');

        $this->assertSame('2001: ZERO RECORDS', (string) $error);
    }

    public function testToStringWithDetail(): void
    {
        $error = new ApiError(
            code: '400',
            message: 'Bad Request',
            detail: 'Invalid input',
        );

        $this->assertSame('400: Bad Request (Invalid input)', (string) $error);
    }
}

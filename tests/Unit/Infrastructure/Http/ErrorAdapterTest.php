<?php

declare(strict_types=1);

namespace Stanbic\SDK\Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use Stanbic\SDK\Domain\Exception\DuplicateTransactionException;
use Stanbic\SDK\Domain\Exception\InsufficientFundsException;
use Stanbic\SDK\Domain\Exception\InvalidRequestException;
use Stanbic\SDK\Domain\Exception\StanbicException;
use Stanbic\SDK\Domain\Exception\UnauthorizedException;
use Stanbic\SDK\Infrastructure\Http\ErrorAdapter;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ErrorAdapterTest extends TestCase
{
    private ErrorAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new ErrorAdapter();
    }

    public function testNormalizesBalanceErrorMessageJson(): void
    {
        $error = $this->adapter->normalize(
            '{"errorCode":"2001","errorMessage":"ZERO RECORDS"}',
            'application/json',
            404,
        );

        self::assertSame('2001', $error->getCode());
        self::assertSame('ZERO RECORDS', $error->getMessage());
    }

    public function testNormalizesTransactionStatusErrorMessageJson(): void
    {
        $error = $this->adapter->normalize(
            '{"dbsReferenceId":"989892717711","responseCode":"2001","responseMessage":"Invalid mobile number"}',
            'application/json',
            400,
        );

        self::assertSame('2001', $error->getCode());
        self::assertSame('Invalid mobile number', $error->getMessage());
        self::assertSame('989892717711', $error->getDetail());
    }

    public function testNormalizesErrorResponseJson(): void
    {
        $error = $this->adapter->normalize(
            '{"status":"403","message":"Possible Error No Data found matching Criteria","data":"null"}',
            'application/json',
            403,
        );

        self::assertSame('403', $error->getCode());
        self::assertSame('Possible Error No Data found matching Criteria', $error->getMessage());
        self::assertSame('403; null', $error->getDetail());
    }

    public function testNormalizesRegisterUrlErrorMessageJson(): void
    {
        $error = $this->adapter->normalize(
            '{"ErrorCode":"01","Status":"REJECTED",' .
            '"ErrorMessage":"Registration Failed","RefereneceId":"XVD12233333111"}',
            'application/json',
            400,
        );

        self::assertSame('01', $error->getCode());
        self::assertSame('Registration Failed', $error->getMessage());
        self::assertSame('REJECTED; XVD12233333111', $error->getDetail());
    }

    public function testNormalizesCardErrorResponseJson(): void
    {
        $error = $this->adapter->normalize(
            '{"status":"403","message":"Possible Error No Data found matching Criteria","data":"null"}',
            'application/json',
            403,
        );

        self::assertSame('403', $error->getCode());
        self::assertSame('Possible Error No Data found matching Criteria', $error->getMessage());
    }

    public function testNormalizesTransactionNotificationXml(): void
    {
        $error = $this->adapter->normalize(
            '<Result><ResultCode>01</ResultCode><ResultDesc>Rejected</ResultDesc></Result>',
            'application/xml',
            400,
        );

        self::assertSame('01', $error->getCode());
        self::assertSame('Rejected', $error->getMessage());
    }

    public function testFallsBackWhenCodeAndMessageAreMissing(): void
    {
        $error = $this->adapter->normalize(
            '{"note":"Only detail"}',
            'application/json',
            502,
        );

        self::assertSame('502', $error->getCode());
        self::assertSame('HTTP error 502', $error->getMessage());
        self::assertNull($error->getDetail());
    }

    public function testUnwrapsNestedErrorEnvelope(): void
    {
        $error = $this->adapter->normalize(
            '{"error":{"errorCode":"E-NESTED","errorMessage":"Nested failure"}}',
            'application/json',
            422,
        );

        self::assertSame('E-NESTED', $error->getCode());
        self::assertSame('Nested failure', $error->getMessage());
    }

    public function testHandlesNumericPayloadKeys(): void
    {
        $error = $this->adapter->normalize(
            '["unexpected","array","payload"]',
            'application/json',
            500,
        );

        self::assertSame('500', $error->getCode());
        self::assertSame('HTTP error 500', $error->getMessage());
    }

    public function testBuildsDetailFromMixedValueTypes(): void
    {
        $error = $this->adapter->normalize(
            '{"errorCode":"MIXED","errorMessage":"Mixed values",' .
            '"detail":null,"status":[],"data":123,"bankStatus":true}',
            'application/json',
            400,
        );

        self::assertSame('MIXED', $error->getCode());
        self::assertSame('Mixed values', $error->getMessage());
        self::assertSame('123; 1', $error->getDetail());
    }

    public function testFallsBackForEmptyBody(): void
    {
        $error = $this->adapter->normalize('   ', 'application/json', 500);

        self::assertSame('500', $error->getCode());
        self::assertSame('HTTP error 500', $error->getMessage());
        self::assertNull($error->getDetail());
    }

    public function testFallsBackForInvalidXmlBody(): void
    {
        $error = $this->adapter->normalize('<Result><Broken>', 'application/xml', 400);

        self::assertSame('400', $error->getCode());
        self::assertSame('HTTP error 400', $error->getMessage());
        self::assertSame('<Result><Broken>', $error->getDetail());
    }

    public function testFallsBackWhenXmlSupportIsUnavailable(): void
    {
        $adapter = new class extends ErrorAdapter {
            protected function hasSimpleXmlSupport(): bool
            {
                return false;
            }
        };

        $error = $adapter->normalize('<Result><ResultCode>01</ResultCode></Result>', 'application/xml', 400);

        self::assertSame('400', $error->getCode());
        self::assertSame('HTTP error 400', $error->getMessage());
    }

    public function testHandlesScalarXmlRoot(): void
    {
        $error = $this->adapter->normalize('<root>text</root>', 'application/xml', 400);

        self::assertSame('400', $error->getCode());
        self::assertSame('HTTP error 400', $error->getMessage());
    }

    public function testFallsBackWhenXmlDecodingReturnsNonArray(): void
    {
        $adapter = new class extends ErrorAdapter {
            protected function decodeXml(\SimpleXMLElement $xml): mixed
            {
                return 'not-an-array';
            }
        };

        $error = $adapter->normalize('<root>text</root>', 'application/xml', 400);

        self::assertSame('400', $error->getCode());
        self::assertSame('HTTP error 400', $error->getMessage());
        self::assertSame('<root>text</root>', $error->getDetail());
    }

    public function testMapsErrorCodeToTypedException(): void
    {
        $exception = $this->adapter->toException(
            '{"dbsReferenceId":"98989271771176942","reasonCode":"DUPLICATE_TRANSACTION",' .
            '"reasonText":"Duplicate Transaction"}',
            'application/json',
            409,
        );

        self::assertInstanceOf(DuplicateTransactionException::class, $exception);
        self::assertSame('Duplicate Transaction', $exception->getMessage());
        self::assertSame(409, $exception->getStatusCode());
    }

    public function testMapsInsufficientFundsErrorCode(): void
    {
        $exception = $this->adapter->toException(
            '{"reasonCode":"INSUFFICIENT_FUNDS","reasonText":"Insufficient funds"}',
            'application/json',
            400,
        );

        self::assertInstanceOf(InsufficientFundsException::class, $exception);
    }

    public function testFallsBackToStatusMappingWhenCodeUnmapped(): void
    {
        $exception = $this->adapter->toException(
            '{"errorCode":"UNKNOWN","errorMessage":"Access denied"}',
            'application/json',
            401,
        );

        self::assertInstanceOf(UnauthorizedException::class, $exception);
        self::assertSame('Access denied', $exception->getMessage());
    }

    public function testFallsBackForInvalidPayload(): void
    {
        $error = $this->adapter->normalize('not-json-or-xml', 'text/plain', 500);

        self::assertSame('500', $error->getCode());
        self::assertSame('HTTP error 500', $error->getMessage());
        self::assertSame('not-json-or-xml', $error->getDetail());
    }

    public function testFallsBackToBaseExceptionForUnknownStatus(): void
    {
        $exception = $this->adapter->toException('not-json-or-xml', 'text/plain', 418);

        self::assertInstanceOf(StanbicException::class, $exception);
        self::assertNotInstanceOf(InvalidRequestException::class, $exception);
    }
}

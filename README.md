# Stanbic Bank Kenya PHP SDK

[![CI Status](https://github.com/BiKodes/stanbic-bank-sdk/workflows/CI/badge.svg)](https://github.com/BiKodes/stanbic-bank-sdk/actions)
[![Coverage Status](https://coveralls.io/repos/github/BiKodes/stanbic-bank-sdk/badge.svg?branch=master)](https://coveralls.io/github/BiKodes/stanbic-bank-sdk?branch=master)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg?logo=php)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Psalm Level](https://shepherd.dev/github/BiKodes/stanbic-bank-sdk/level.svg)](https://shepherd.dev/github/BiKodes/stanbic-bank-sdk)
[![PSR-12](https://img.shields.io/badge/code%20style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)

A modern, type-safe PHP SDK for integrating with Stanbic Bank Kenya APIs. Built with PSR standards, OAuth2 authentication, resilient HTTP handling, and clean architecture patterns.

## Disclaimer

**This is an unofficial, community-maintained SDK for interacting with the Stanbic Bank Kenya Sandbox APIs.**

It is **not affiliated with, endorsed by, or supported by** Stanbic Bank Kenya or Standard Bank Group. Use at your own risk. For official support and production access, please contact Stanbic Bank Kenya directly.

## Features

- **OAuth2 Client Credentials**
Secure `client_credentials` flow with automatic token management and caching
- **20+ APIs Supported**
Accounts, Payments/Transfers, Cards (ACS), Notifications, Utilities
- **Resilient HTTP**
Exponential backoff retries, configurable timeouts, request ID tracking
- **Type-Safe DTOs**
Immutable domain objects with full type hints for all request/response payloads
- **Typed Exceptions**
Granular exception hierarchy mapping API error codes to catchable types
- **Pluggable Serialization**
JSON (default) and XML support for multi-format APIs
- **PSR-Compliant**
PSR-18 (HTTP Client), PSR-7 (HTTP Messages), PSR-3 (Logging), PSR-4 (Autoloading)
- **Design Patterns**
Factory, Strategy, Decorator, Template Method, Adapter, Facade for extensibility
- **Comprehensive Testing**
95%+ coverage with mock fixtures for all endpoints
- **Production Ready**
CI/CD pipeline, static analysis (Psalm, PHPCS), strict type checking

## Requirements

- **PHP** ≥ 8.1
- **Composer** 2.0+
- **PSR-18 HTTP Client** (e.g., Guzzle, Curl, or HTTPlug implementation)
- **PSR-3 Logger** (e.g., Monolog, for optional logging)

## Installation

Install via Composer

```bash
composer require stanbic/kenya-sdk
```

Or clone and install locally:

```bash
git clone https://github.com/BiKodes/stanbic-bank-sdk.git
cd stanbic-bank-sdk
composer install
```

## Quick Start

### 1. Basic Setup

```php
<?php

require 'vendor/autoload.php';

use Stanbic\SDK\Application\StanbicClient;
use Stanbic\SDK\Infrastructure\Http\HttpConfig;

$config = HttpConfig::create(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    baseUrl: 'Stanbic Sandbox BaseURL',
    timeout: 30,
    retryAttempts: 3,
    retryBackoffMs: 100
);

$client = StanbicClient::create($config);
```

### 2. Get Account Balance

```php
try {
    $balance = $client->accounts()->getBalance();
    echo "Available Balance: " . $balance->availableBalance . "\n";
} catch (StanbicException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### 3. Fetch Account Statements with Pagination

```php
use Stanbic\SDK\Domain\Pagination\Page;

try {
    $page = new Page(from: 0, size: 20);
    $statements = $client->accounts()->fetchStatements(
        bookingDateGreaterThan: '20240101',
        bookingDateLessThan: '20240131',
        page: $page
    );

    foreach ($statements as $transaction) {
        echo $transaction->reference . ": " . $transaction->transactionAmountCurrency->amount . "\n";
    }
} catch (InvalidRequestException $e) {
    echo "Invalid date range: " . $e->getMessage() . "\n";
}
```

### 4. Initiate a Pesalink Transfer

```php
use Stanbic\SDK\Domain\Payments\PesalinkPaymentRequest;
use Stanbic\SDK\Domain\Payments\TransferTransactionInformation;
use Stanbic\SDK\Domain\Payments\CounterpartyAccount;

try {
    $request = new PesalinkPaymentRequest(
        originatorMobileNumber: '254737696956',
        dbsReferenceId: 'unique-reference-' . time(),
        txnNarrative: 'Payment for services',
        requestedExecutionDate: date('Y-m-d'),
        transferTransactionInformation: new TransferTransactionInformation(
            instructedAmount: new Amount('500', 'KES'),
            counterpartyAccount: new CounterpartyAccount(
                recipientBankAcctNo: '01008747142',
                recipientBankCode: '07000'
            ),
            counterpartyName: 'John Doe'
        )
    );

    $response = $client->payments()->initiatePesalinkPayment($request);
    echo "Transaction Reference: " . $response->bankReferenceId . "\n";
} catch (DuplicateTransactionException $e) {
    echo "Duplicate transaction detected: " . $e->getMessage() . "\n";
} catch (InsufficientFundsException $e) {
    echo "Insufficient funds: " . $e->getMessage() . "\n";
}
```

### 5. Handle Typed Exceptions

```php
use Stanbic\SDK\Domain\Exception\{
    StanbicException,
    UnauthorizedException,
    InvalidRequestException,
    ServerErrorException
};

try {
    $balance = $client->accounts()->getBalance();
} catch (UnauthorizedException $e) {
    echo "OAuth2 authentication failed. Check credentials.\n";
} catch (InvalidRequestException $e) {
    echo "Bad request: " . $e->getMessage() . "\n";
} catch (ServerErrorException $e) {
    echo "Bank server error: " . $e->getMessage() . "\n";
} catch (StanbicException $e) {
    echo "Stanbic error: " . $e->getMessage() . "\n";
}
```

### 6. Register for Real-time Notifications

```php
try {
    $response = $client->notifications()->registerPaymentResultUrl(
        referenceId: 'callback-ref-' . time(),
        callBackUrl: 'https://stanbic-php-sdk-bikodes.com/webhook/stanbic',
        notificationType: 'CREDIT',
        channel: 'API'
    );

    echo "Callback registered: " . $response->referenceId . "\n";
} catch (StanbicException $e) {
    echo "Failed to register callback: " . $e->getMessage() . "\n";
}
```

## Available APIs

### Accounts
- `getBalance()` – Retrieve account balance
- `fetchStatements(Page $page)` – Get account transactions with pagination
- `getTransactionStatus(string $dbsReferenceId)` – Query transaction status

### Payments & Transfers
- `initiatePesalinkPayment()` – Inter-bank transfer via Pesalink
- `initiateStanbicPayment()` – Transfer within Stanbic accounts
- `initiateInterAccountTransfer()` – Own account transfers
- `initiateEftTransfer()` – Electronic Funds Transfer
- `initiateSwiftTransfer()` – International SWIFT payments
- `initiateRtgsTransfer()` – Real-time Gross Settlement
- `sendToMobileWallet()` – Disburse to M-PESA, Airtel Money, T-Kash

### Cards (ACS)
- `getCardDetails(string $pan)` – Retrieve card information
- `getCustomerCardDetails()` – Get customer card details with authentication

### Notifications
- `registerPaymentResultUrl()` – Register webhook for payment alerts
- `registerTransactionNotification()` – Enable transaction notifications
- `sendSmsEmailNotification()` – Send SMS/email alerts

### Utilities
- `fetchSortCodes(string $transactionType)` – Get bank sort codes (PESALINK, SWIFT, RTGS)
- `getSwiftCode(string $countryCode)` – Retrieve SWIFT/BIC codes by country
- `getSwiftBranch(string $branchCode)` – Get branch details by SWIFT branch code

## Architecture

### Layered Design

```
┌─────────────────────────────────────────┐
│      Application Layer (Facade)         │
│          StanbicClient                  │
├─────────────────────────────────────────┤
│     Application Services Layer          │
│  (AccountService, PaymentService, ...)  │
├─────────────────────────────────────────┤
│     Infrastructure Layer                │
│  (HTTP, Auth, Serialization, Logging)   │
├─────────────────────────────────────────┤
│         Domain Layer (Entities)         │
│   (DTOs, Value Objects, Exceptions)     │
└─────────────────────────────────────────┘
```

### Domain-Driven Design (DDD)

The SDK follows Domain-Driven Design principles by centering the model on domain
concepts (DTOs, value objects, and exceptions) and keeping infrastructure
concerns (HTTP, auth, serialization) separated from the core domain.

### Value Objects & Helpers

Shared, cross-domain primitives live in [src/Domain/ValueObject](src/Domain/ValueObject). Domain-specific
helpers stay inside their feature folder (e.g., Payment DTOs in Payment), while reusable
value objects should be placed in this global location to keep things DRY.

### Design Patterns Used

| Pattern | Usage | Benefit |
|---------|-------|---------|
| **Facade** | `StanbicClient` | Single entry point for all services |
| **Factory** | `HttpClientFactory`, `SerializerFactory` | Decoupled object creation |
| **Strategy** | `TokenProvider`, `Serializer`, `RetryStrategy` | Pluggable algorithms (e.g., JSON/XML) |
| **Decorator** | `AuthMiddleware`, `RetryMiddleware`, `TimeoutMiddleware` | Composable request transformations |
| **Template Method** | `BaseService` | Shared request/response handling in services |
| **Adapter** | `ErrorAdapter` | Normalize diverse error formats |
| **Data Transfer Object (DTO)** | All `*Request`, `*Response` classes | Type-safe immutable payloads |
| **Value Object** | `Amount`, `Currency`, `Account`, `Page` | Encapsulate domain concepts |

## Configuration

### HTTP Config

```php
use Stanbic\SDK\Infrastructure\Http\HttpConfig;

$config = new HttpConfig(
    baseUrl: 'Stanbic Sandbox BaseURL',
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    tokenUrl: 'Stanbic Sandbox Token URL',
    timeout: 30,                    
    retryAttempts: 3,
    retryBackoffMs: 100,            
    serializationFormat: 'JSON',    
    logger: $psr3Logger           
);

$client = StanbicClient::create($config);
```

### With Custom HTTP Client

```php
use Http\Client\Curl\Client;
use Psr\Http\Client\ClientInterface;

$httpClient = new Client();
$config = HttpConfig::create(...);

$client = new StanbicClient(
    httpClient: $httpClient,
    tokenProvider: new OAuth2TokenProvider($config),
    config: $config
);
```

## Error Handling

### Exception Hierarchy

```
StanbicException (base)
├── UnauthorizedException (401)
├── InvalidRequestException (400)
├── ForbiddenException (403)
├── NotFoundException (404)
├── ConflictException (409, duplicate)
├── ServerErrorException (500+)
├── TimeoutException (request timeout)
├── InsufficientFundsException (domain-specific)
├── DuplicateTransactionException (domain-specific)
├── InvalidAccountException (domain-specific)
└── NetworkException (connection error)
```

### Catching Specific Errors

```php
try {
    $response = $client->payments()->initiatePesalinkPayment($request);
} catch (DuplicateTransactionException $e) {
    log()->warning("Duplicate detected, may retry: " . $e->getMessage());
} catch (UnauthorizedException $e) {
    log()->error("Auth failed: " . $e->getMessage());
} catch (ServerErrorException $e) {
    log()->error("Bank server error: " . $e->getMessage());
} catch (StanbicException $e) {
    log()->error("Stanbic error: " . $e->getMessage());
}
```

## Implementation Phases

This SDK is being built in structured phases to ensure quality and maintainability.

### Phase 0 (Project Foundation & Setup)
Scaffold project structure, tooling, and baseline configuration.
- Initialize `composer.json` with PSR-4 namespace, dev dependencies
- Create directory structure: `src/`, `tests/`, `examples/`, `docs/`
- Set up GitHub Actions CI/CD pipeline
- Create `docs/api-endpoints.md` with endpoint catalog
- Baseline `README.md` and project documentation

### Phase 1 (Domain Layer & DTOs)
Define all domain objects, value objects, enums, and exceptions.
- Exception hierarchy: `StanbicException` and 10+ typed subclasses
- Domain Service Interfaces: `AccountServiceInterface`, `PaymentServiceInterface`, etc.
- DTOs for all 20 APIs (Accounts, Payments, Cards, Notifications, Utilities)
- Value Objects: `Amount`, `Currency`, `Account`, `Card`, `Interaction`, `Page`
- Enums: `BankStatus`, `PaymentScheme`, `NotificationType`, `SerializationFormat`
- Pagination helpers: `Page` DTO and `StatementIterator`

### Phase 2 (Infrastructure – HTTP & Auth Layer)
Build resilient PSR-18/PSR-7 HTTP client with OAuth2 and retry logic.
- `HttpClientFactory`: PSR-18 client builder
- `OAuth2TokenProvider`: `client_credentials` grant with token caching
- Middleware stack: `AuthMiddleware`, `RetryMiddleware`, `TimeoutMiddleware`, `RequestIdMiddleware`
- `ErrorAdapter`: normalize error response formats to `ApiError` DTO
- Request/response logging via PSR-3

### Phase 3 (Serialization Layer)
Support JSON (default) and XML serialization for multi-format APIs.
- `SerializerInterface` (Strategy): `serialize()`, `deserialize()`
- `JsonSerializer`: JSON encoder/decoder
- `XmlSerializer`: SimpleXML/DOMDocument for Transaction Notification
- `SerializerFactory`: format picker by enum
- Automatic format routing in `ErrorAdapter`

### Phase 4 (Application Layer & Services)
Implement domain services with consistent request/response handling.
- `BaseService`: Template Method for shared request/response logic
- 6 service modules: `AccountService`, `PaymentService`, `TransferService`, `CardService`, `NotificationService`, `UtilityService`
- 20+ service methods covering all APIs
- `StanbicClient` Facade: static factory and service delegation

### Phase 5 (Testing & Mocking)
Comprehensive unit and integration tests with mock HTTP responses.
- Unit tests for Domain, Infrastructure (HTTP, Serialization), Application layers
- Mock fixtures for all 20 APIs
- `MockHttpClientBuilder` test helper
- 70%+ code coverage target
- Error path and exception mapping tests

### Phase 6 (Documentation & Examples)
User-facing docs and runnable examples.
- Complete `README.md` with quick-start and feature table
- `docs/api-endpoints.md`: all 20 endpoints with error mappings
- 7+ runnable examples in `examples/`: auth, accounts, payments, cards, notifications, utilities, error handling
- `docs/design-patterns.md`: rationale for each pattern

### Phase 7 (CI/CD & Polish)
Production-ready tooling, type safety, and release prep.
- GitHub Actions: lint, test, coverage, static analysis
- Psalm (level 9), PHPCS (PSR-12), optional PHPStan
- `CHANGELOG.md`, `LICENSE`, Packagist registration
- Final checklist: tests passing, 0 lint violations, 70%+ coverage

### Phase 8 (Optional Enhancements)
Extended features for v0.2+.
- Async/non-blocking HTTP via `amp` or `ReactPHP`
- Request signing (HMAC-SHA256)
- Batch API calls
- Caching layer
- Webhook validation helpers
- Rate limiting and circuit breaker

## Development Todo List

### Phase 0 (Foundation)

**Project Setup**
- [x] Initialize `composer.json` with PSR-4 `Stanbic\SDK\*` namespace
- [x] Add dev dependencies: PHPUnit, Psalm, PHPCS, symfony/var-dumper, PSR-18 HTTP client stubs
- [x] Create directory structure: `src/Domain`, `src/Application`, `src/Infrastructure`, `tests`, `examples`, `docs`
- [x] Add `.gitignore` (vendor, build, IDE)
- [x] Create `.editorconfig` for consistent formatting

**CI/CD**
- [x] Create `.github/workflows/test.yml` (lint, test, coverage)
- [x] Set up Psalm configuration (`psalm.xml`)
- [x] Configure PHPCS (PSR-12 standard)
- [x] Create `phpstan.neon.dist` (optional, level 9)

**Documentation**
- [x] Create `docs/api-endpoints.md` with 20 API catalog
- [x] Create `docs/design-patterns.md` with pattern rationale
- [x] Create `CHANGELOG.md` (v0.1.0 skeleton)
- [ ] Update `README.md` with all sections from this template

### Phase 1 (Domain Layer)

**Exceptions**
- [x] Create `src/Domain/Exception/StanbicException.php` (base)
- [x] Implement 10+ typed exception subclasses:
  - [x] `UnauthorizedException` (401)
  - [x] `InvalidRequestException` (400)
  - [x] `ForbiddenException` (403)
  - [x] `NotFoundException` (404)
  - [x] `ConflictException` (409)
  - [x] `ServerErrorException` (5xx)
  - [x] `TimeoutException`
  - [x] `InsufficientFundsException`
  - [x] `DuplicateTransactionException`
  - [x] `InvalidAccountException`
  - [x] `NetworkException`
- [x] Create `src/Domain/Exception/ApiErrorMapping.php` (error code → exception mapper)

**Service Interfaces**
- [x] Define `src/Domain/Service/AccountServiceInterface.php`
- [x] Define `src/Domain/Service/PaymentServiceInterface.php`
- [x] Define `src/Domain/Service/TransferServiceInterface.php`
- [x] Define `src/Domain/Service/CardServiceInterface.php`
- [x] Define `src/Domain/Service/NotificationServiceInterface.php`
- [x] Define `src/Domain/Service/UtilityServiceInterface.php`

**DTOs – Accounts**
- [x] Create `src/Domain/Account/BalanceResponse.php`
- [x] Create `src/Domain/Account/Transaction.php` (with date, amount, counterparty)
- [x] Create `src/Domain/Account/TransactionStatusResponse.php` (with bankStatus, bankReferenceId, transferFee)
- [x] Create `src/Domain/Account/StatementRequest.php` (bookingDateGreaterThan, bookingDateLessThan, page)
- [x] Create `src/Domain/Account/StatementResponse.php` (totalElements, transaction-items)

**DTOs – Payments/Transfers**
- [x] Create `src/Domain/Payment/PaymentRequest.php` (base abstract)
- [x] Create `src/Domain/Payment/PesalinkPaymentRequest.php`
- [x] Create `src/Domain/Payment/StanbicPaymentRequest.php`
- [x] Create `src/Domain/Payment/InterAccountTransferRequest.php`
- [x] Create `src/Domain/Payment/EftTransferRequest.php`
- [x] Create `src/Domain/Payment/SwiftTransferRequest.php`
- [x] Create `src/Domain/Payment/RtgsTransferRequest.php`
- [x] Create `src/Domain/Payment/MobileMoneyRequest.php`
- [x] Create `src/Domain/Payment/PaymentResponse.php` (common response)
- [x] Create `src/Domain/Payment/TransferTransactionInformation.php`
- [x] Create `src/Domain/Payment/CounterpartyAccount.php`
- [x] Create `src/Domain/Payment/Counterparty.php`
- [x] Create `src/Domain/Payment/RemittanceInformation.php`

**DTOs – Cards**
- [x] Create `src/Domain/Card/CardDetailsRequest.php`
- [x] Create `src/Domain/Card/CardDetailsResponse.php`
- [x] Create `src/Domain/Card/CustomerCardDetailsRequest.php`
- [x] Create `src/Domain/Card/CustomerCardDetailsResponse.php`
- [x] Create `src/Domain/Card/Card.php` (value object)
- [x] Create `src/Domain/Card/Interaction.php` (value object)

**DTOs – Notifications**
- [x] Create `src/Domain/Notification/RegisterUrlRequest.php`
- [x] Create `src/Domain/Notification/RegisterUrlResponse.php`
- [x] Create `src/Domain/Notification/TransactionNotificationRequest.php` (XML support)
- [x] Create `src/Domain/Notification/TransactionNotificationResponse.php`
- [x] Create `src/Domain/Notification/SmsEmailNotificationRequest.php`
- [x] Create `src/Domain/Notification/SmsEmailNotificationResponse.php`

**DTOs – Utilities**
- [x] Create `src/Domain/Utility/SortCodeRequest.php`
- [x] Create `src/Domain/Utility/SortCodeResponse.php`
- [x] Create `src/Domain/Utility/SwiftCodeRequest.php`
- [x] Create `src/Domain/Utility/SwiftCodeResponse.php`
- [x] Create `src/Domain/Utility/SwiftBranchResponse.php`

**Value Objects**
- [x] Create `src/Domain/Value/Amount.php` (amount, currency)
- [x] Create `src/Domain/Value/Currency.php` (enum or string)
- [x] Create `src/Domain/Value/Account.php` (account number, bank code)
- [x] Create `src/Domain/Value/PostalAddress.php`
- [x] Create `src/Domain/Value/ApiError.php` (normalized error)

**Enums**
- [x] Create `src/Domain/Enum/BankStatus.php` (PROCESSED, ACCEPTED, REJECTED, etc.)
- [x] Create `src/Domain/Enum/PaymentScheme.php` (PULL_VISA, etc.)
- [x] Create `src/Domain/Enum/NotificationType.php` (CREDIT, DEBIT)
- [x] Create `src/Domain/Enum/TransactionType.php` (PESALINK, SWIFT, RTGS, EFT)
- [x] Create `src/Domain/Enum/SerializationFormat.php` (JSON, XML)
- [x] Create `src/Domain/Enum/TransferFrequency.php` (DAILY, WEEKLY, MONTHLY)

**Pagination**
- [x] Create `src/Domain/Pagination/Page.php` (from, size)
- [x] Create `src/Domain/Pagination/PagedResult.php` (totalElements, items)
- [x] Create `src/Domain/Pagination/StatementIterator.php` (iterate over transactions)

**Tests – Domain**
- [ ] Create `tests/Unit/Domain/Exception/` tests for all exception types
- [ ] Create `tests/Unit/Domain/` DTO validation tests
- [ ] Create `tests/Unit/Domain/Pagination/` Page and Iterator tests

### Phase 2 (Infrastructure – HTTP & Auth)

**HTTP Config & Factory**
- [ ] Create `src/Infrastructure/Http/HttpConfig.php` (immutable configuration)
- [ ] Create `src/Infrastructure/Http/HttpClientFactory.php` (PSR-18 builder)

**OAuth2 Token Provider**
- [ ] Create `src/Infrastructure/Http/OAuth2TokenProvider.php` (Strategy pattern)
  - [ ] Implement `client_credentials` grant
  - [ ] Add token caching (TTL-based)
  - [ ] Handle token refresh
  - [ ] Validate token expiration

**Middleware Stack**
- [ ] Create `src/Infrastructure/Http/Middleware/AuthMiddleware.php` (inject Bearer token)
- [ ] Create `src/Infrastructure/Http/Middleware/RetryMiddleware.php` (exponential backoff)
  - [ ] Implement retry logic (3 attempts, 100ms base)
  - [ ] Add jitter to prevent thundering herd
  - [ ] Skip retries for 4xx (except 408, 429)
- [ ] Create `src/Infrastructure/Http/Middleware/TimeoutMiddleware.php` (enforce 30s timeout)
- [ ] Create `src/Infrastructure/Http/Middleware/RequestIdMiddleware.php` (inject X-Request-ID)
- [ ] Create `src/Infrastructure/Http/Middleware/LoggingMiddleware.php` (PSR-3 logging)

**Error Adapter**
- [ ] Create `src/Infrastructure/Http/ErrorAdapter.php` (Adapter pattern)
  - [ ] Normalize `errorMessage` format (balance, statements, status)
  - [ ] Normalize `errorResponse` format (inter-account, EFT)
  - [ ] Normalize `ErrorMessage` format (register URL, notifications)
  - [ ] Normalize card error responses
  - [ ] Map error codes to exception types via `ApiErrorMapping`
  - [ ] Support JSON and XML error parsing

**HTTP Client**
- [ ] Create `src/Infrastructure/Http/HttpClient.php` (request builder)
- [ ] Integrate all middleware in correct order (logging → timeout → retry → auth)
- [ ] Add request/response logging
- [ ] Add PSR-3 logger injection

**Tests – Infrastructure**
- [ ] Create `tests/Unit/Infrastructure/Http/OAuth2TokenProviderTest.php`
- [ ] Create `tests/Unit/Infrastructure/Http/AuthMiddlewareTest.php`
- [ ] Create `tests/Unit/Infrastructure/Http/RetryMiddlewareTest.php`
- [ ] Create `tests/Unit/Infrastructure/Http/ErrorAdapterTest.php` (all 20 error variants)
- [ ] Create `tests/Unit/Infrastructure/Http/HttpClientTest.php` (middleware stack)

### Phase 3 (Serialization Layer)

**Serializer Interface & Implementations**
- [ ] Create `src/Infrastructure/Serialization/SerializerInterface.php` (Strategy)
- [ ] Create `src/Infrastructure/Serialization/JsonSerializer.php`
  - [ ] Implement `serialize(mixed $data): string`
  - [ ] Implement `deserialize(string $data, string $className): mixed`
  - [ ] Handle PHP 8.1+ named arguments
- [ ] Create `src/Infrastructure/Serialization/XmlSerializer.php`
  - [ ] Implement XML encode/decode
  - [ ] Support Transaction Notification API format
- [ ] Create `src/Infrastructure/Serialization/SerializerFactory.php` (factory method)

**Integration with HTTP Layer**
- [ ] Update `ErrorAdapter` to use pluggable serializer (JSON/XML)
- [ ] Update `HttpClient` to serialize requests via serializer
- [ ] Update response deserialization to use serializer

**Tests – Serialization**
- [ ] Create `tests/Unit/Infrastructure/Serialization/JsonSerializerTest.php`
- [ ] Create `tests/Unit/Infrastructure/Serialization/XmlSerializerTest.php`
- [ ] Create `tests/Unit/Infrastructure/Serialization/SerializerFactoryTest.php`

### Phase 4: Application Layer & Services (Days 11–14)

**Base Service**
- [ ] Create `src/Application/Service/BaseService.php` (Template Method)
  - [ ] Implement `execute()` template method
  - [ ] Shared request building, serialization, HTTP call, error handling, deserialization
  - [ ] Dependency injection for HttpClient, Serializer

**Account Service**
- [ ] Create `src/Application/Service/AccountService.php`
  - [ ] `getBalance(): BalanceResponse`
  - [ ] `fetchStatements(string $from, string $to, Page $page): PagedResult`
  - [ ] `getTransactionStatus(string $dbsReferenceId): TransactionStatusResponse`

**Payment Service**
- [ ] Create `src/Application/Service/PaymentService.php`
  - [ ] `initiatePesalinkPayment(PesalinkPaymentRequest): PaymentResponse`
  - [ ] `initiateStanbicPayment(StanbicPaymentRequest): PaymentResponse`
  - [ ] `sendToMobileWallet(MobileMoneyRequest): PaymentResponse`

**Transfer Service**
- [ ] Create `src/Application/Service/TransferService.php`
  - [ ] `initiateInterAccountTransfer(InterAccountTransferRequest): PaymentResponse`
  - [ ] `initiateEftTransfer(EftTransferRequest): PaymentResponse`
  - [ ] `initiateSwiftTransfer(SwiftTransferRequest): PaymentResponse`
  - [ ] `initiateRtgsTransfer(RtgsTransferRequest): PaymentResponse`

**Card Service**
- [ ] Create `src/Application/Service/CardService.php`
  - [ ] `getCardDetails(CardDetailsRequest): CardDetailsResponse`
  - [ ] `getCustomerCardDetails(CustomerCardDetailsRequest): CustomerCardDetailsResponse`

**Notification Service**
- [ ] Create `src/Application/Service/NotificationService.php`
  - [ ] `registerPaymentResultUrl(RegisterUrlRequest): RegisterUrlResponse`
  - [ ] `registerTransactionNotification(TransactionNotificationRequest): TransactionNotificationResponse`
  - [ ] `sendSmsEmailNotification(SmsEmailNotificationRequest): SmsEmailNotificationResponse`

**Utility Service**
- [ ] Create `src/Application/Service/UtilityService.php`
  - [ ] `fetchSortCodes(string $transactionType): SortCodeResponse[]`
  - [ ] `getSwiftCode(string $countryCode): SwiftCodeResponse[]`
  - [ ] `getSwiftBranch(string $branchCode): SwiftBranchResponse[]`

**Facade**
- [ ] Create `src/Application/StanbicClient.php` (Facade)
  - [ ] Constructor injection of services, HttpClient, HttpConfig
  - [ ] Public method properties/getters: `accounts()`, `payments()`, `transfers()`, `cards()`, `notifications()`, `utilities()`
  - [ ] Static factory: `StanbicClient::create(clientId, clientSecret, baseUrl): self`
  - [ ] Optional: shorthand delegators for common operations

**Pagination Helper**
- [ ] Create `src/Application/Pagination/StatementIterator.php`
  - [ ] Implement `Iterator`, `Countable` interfaces
  - [ ] Lazy-load pages on iteration
  - [ ] Reuse `fetchStatements()` with page increments

**Tests – Application**
- [ ] Create `tests/Unit/Application/Service/AccountServiceTest.php` (with mock HTTP)
- [ ] Create `tests/Unit/Application/Service/PaymentServiceTest.php`
- [ ] Create `tests/Unit/Application/Service/TransferServiceTest.php`
- [ ] Create `tests/Unit/Application/Service/CardServiceTest.php`
- [ ] Create `tests/Unit/Application/Service/NotificationServiceTest.php`
- [ ] Create `tests/Unit/Application/Service/UtilityServiceTest.php`
- [ ] Create `tests/Unit/Application/StanbicClientTest.php` (Facade wiring)

### Phase 5 (Testing & Mocking)

**Test Fixtures**
- [ ] Create `tests/Fixtures/` directory for all mock responses
- [ ] Create `tests/Fixtures/Accounts/` (balance, statements, status)
- [ ] Create `tests/Fixtures/Payments/` (all payment types)
- [ ] Create `tests/Fixtures/Cards/` (card details, customer details)
- [ ] Create `tests/Fixtures/Notifications/` (register, SMS/email, notification)
- [ ] Create `tests/Fixtures/Utilities/` (sort codes, Swift codes)

**Mock HTTP Client Builder**
- [ ] Create `tests/MockHttpClientBuilder.php` (test helper)
  - [ ] Fluent API: `->withResponse(statusCode, body)`
  - [ ] Support multiple sequential responses
  - [ ] Capture and assert requests

**Integration Tests**
- [ ] Create `tests/Integration/` directory
- [ ] Create `tests/Integration/AuthenticationTest.php` (OAuth2 flow, token caching)
- [ ] Create `tests/Integration/EndToEndTest.php` (full request/response cycles with mocks)

**Coverage & Reports**
- [ ] Ensure 95%+ code coverage
- [ ] Generate coverage report: `vendor/bin/phpunit --coverage-html=build/coverage`
- [ ] Verify critical paths (auth, error handling, serialization)

### Phase 6 (Documentation & Examples)

**Update README**
- [ ] Complete `README.md`
- [ ] Add table of contents
- [ ] Add feature table
- [ ] Add architecture diagram
- [ ] Add design patterns table

**Create Examples**
- [ ] Create `examples/01-auth.php` (OAuth2 setup and caching)
- [ ] Create `examples/02-accounts.php` (balance, statements with pagination, status)
- [ ] Create `examples/03-payments.php` (Pesalink, Stanbic, mobile money)
- [ ] Create `examples/04-transfers.php` (EFT, SWIFT, RTGS, inter-account)
- [ ] Create `examples/05-cards.php` (card details, customer details)
- [ ] Create `examples/06-notifications.php` (register URL, notifications, SMS/email)
- [ ] Create `examples/07-utilities.php` (sort codes, Swift codes)
- [ ] Create `examples/08-error-handling.php` (typed exception catching)

**Finalize API Documentation**
- [ ] Complete `docs/api-endpoints.md` (all 20 endpoints, error mappings, examples)
- [ ] Create `docs/design-patterns.md` (detailed rationale for each pattern)
- [ ] Create `docs/pagination.md` (Page DTO, StatementIterator usage)
- [ ] Create `docs/serialization.md` (JSON/XML support, custom serializers)
- [ ] Create `docs/error-handling.md` (exception hierarchy, mapping table)

### Phase 7 (CI/CD & Polish)

**GitHub Actions**
- [ ] Complete `.github/workflows/test.yml`:
  - [ ] Run PHPUnit on PHP 8.1, 8.2, 8.3
  - [ ] Generate coverage report
  - [ ] Upload to Codecov
- [ ] Create `.github/workflows/lint.yml`:
  - [ ] PHPCS (PSR-12)
  - [ ] Psalm (level 9)
- [ ] Create `.github/workflows/release.yml` (tag and deploy to Packagist)

**Static Analysis**
- [ ] Run Psalm: `vendor/bin/psalm --output-format=github`
- [ ] Fix all Psalm errors (aim for 0 errors, strict mode)
- [ ] Run PHPCS: `vendor/bin/phpcs src/ tests/`
- [ ] Fix all style violations
- [ ] Optional: PHPStan level 9

**Release Preparation**
- [ ] Finalize `CHANGELOG.md`
- [ ] Verify `LICENSE`
- [ ] Update `composer.json`: description, keywords, homepage, authors, license, repository
- [ ] Tag release: `git tag v0.1.0`
- [ ] Push to GitHub: `git push origin v0.1.0`
- [ ] Register on Packagist (if public): https://packagist.org/packages/submit

**Final Checklist**
- [ ] All tests passing locally
- [ ] CI pipeline green (all PHP versions)
- [ ] 70%+ code coverage
- [ ] 0 Psalm errors
- [ ] 0 PHPCS violations
- [ ] All examples runnable
- [ ] README complete with all sections
- [ ] API docs complete
- [ ] CHANGELOG updated
- [ ] Packagist registered (if applicable)

### Phase 8 (Optional Enhancements)

- [ ] Async HTTP support via `amphp` or `react`
- [ ] Request signing (HMAC-SHA256 for additional security)
- [ ] Batch API calls (multi-transfer, multi-query in single request)
- [ ] Response caching layer (memoization with TTL)
- [ ] Webhook validation helpers (verify inbound notifications)
- [ ] Rate limiting helpers and circuit breaker
- [ ] OpenAPI spec generation from DTOs
- [ ] SDK performance benchmarks and optimization
- [ ] Integration test suite with sandbox credentials

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit changes: `git commit -m 'Add feature'`
4. Push to branch: `git push origin feature/my-feature`
5. Submit a pull request with detailed description

### Code Standards

- PSR-12 coding style (enforced via PHPCS)
- Psalm level 9 type checking (strict mode)
- 70%+ test coverage (PHPUnit)
- Type hints on all parameters and return values
- Immutable DTOs (readonly properties in PHP 8.1+)

## Support

For issues, questions, or feature requests, please:

- Open a GitHub issue: [Issues](https://github.com/BiKodes/stanbic-bank-sdk/issues)
- Check existing documentation: [docs/](docs/)
- Review examples: [examples/](examples/)
- Contact API support: kilele@stanbic.com

## License

This SDK is licensed under the **MIT License**. See [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and release notes.

## Acknowledgments

- Stanbic Bank Kenya for sandbox access and API documentation
- PSR standards (PSR-4, PSR-7, PSR-18, PSR-3) for interoperability
- Gang of Four design patterns for architectural guidance
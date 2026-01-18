# API Endpoints Catalog (Sandbox)

> Unofficial, community-maintained SDK. Use sandbox credentials and verify all payloads against Stanbic Bank Kenya documentation.

## Base URLs
- Sandbox Base: `https://sandbox.connect.stanbicbank.co.ke/api/sandbox`
- Token URL: `https://sandbox.connect.stanbicbank.co.ke/api/sandbox/auth/oauth2/token`

## Auth
- OAuth2 `client_credentials` (scope: `payments`)
- Headers: `Authorization: Bearer <token>`, `Content-Type: application/json` (unless otherwise noted)

## Accounts
- **GET /balance** — Account Balance
- **POST /fetchTransactions** — Account Statements (paginated)
- **GET /transaction-status-sandbox?dbsReferenceId=...** — Transaction Status

## Payments & Transfers
- **POST /pesalink-payments** — Inter-bank via Pesalink
- **POST /stanbic-payments** — Payments within Stanbic
- **POST /inter-account-transfer** — Own-account transfer
- **POST /eft-payments** — EFT transfer
- **POST /rtgs-payments** — RTGS transfer
- **POST /swift-payments** — SWIFT transfer
- **POST /mobile-payments** — Mobile money B2C
- **POST /mpesa-checkout** — STK Push (MPESA)

## Cards (ACS)
- **POST /acs/card-details** — Get Card Details
- **POST /acs/get-customer-of-card-details** — Get Customer of Card Details
- **POST /acs/sms-email-notification** — SMS/Email Notification

## Notifications
- **POST /registerurl** — Register webhook for real-time credit alerts
- **POST /transaction-notification-api** — Transaction notification (XML support)

## Utilities
- **POST /fetch-sortcodes** — Fetch Sort Codes
- **GET /10.235.76.151:7844/swift-codes/{countryCode}** — Swift Codes by country
- **GET /10.235.76.151:7844/swift-codes?branchCode=...** — Swift Branch details

## Notes
- Some endpoints support or require XML (e.g., transaction notifications); ensure serializer selection per endpoint.
- Error formats vary (`errorMessage`, `errorResponse`, `ErrorMessage`); normalize via Error Adapter in SDK.
- Always test against sandbox before any production move.

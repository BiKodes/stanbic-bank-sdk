# Design Patterns

This SDK uses a small set of patterns to keep the codebase modular and testable.

## Facade
- StanbicClient provides a single entry point for service modules.

## Factory
- HttpClientFactory and SerializerFactory centralize object creation.

## Strategy
- Token providers and serializers can be swapped without changing callers.

## Decorator
- Middleware wraps requests with auth, retry, and logging behavior.

## Template Method
- BaseService defines the request flow, with services providing specifics.

## Adapter
- ErrorAdapter normalizes differing error formats into a single model.

## DTO / Value Object
- DTOs transport API payloads; value objects encapsulate domain concepts.

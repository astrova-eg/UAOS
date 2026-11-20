# Data Flow Mechanics

**Document ID:** ARCH-DOC-005  
**Version:** 2.0.0  
**Classification:** Architecture Specification  
**Status:** [Active Architecture]  

---

## 1. Inbound Request Data Flow
1. Client issues HTTPS request with Bearer token.
2. Nginx/Laragon routes request to Laravel routing engine.
3. Authenticate & TenantScope middleware validate identity and bind active tenant context.
4. FormRequest validates payload against strict schema and authorizes user ability.
5. Invokable Controller instantiates DTO and calls Action class.
6. Action executes inside database transaction, persists via Eloquent, and dispatches domain event.
7. Controller wraps result in JSON:API or RFC 7807 problem details response envelope.
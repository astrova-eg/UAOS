# Shared Engineering Skill: RESTful and JSON:API Engineering Standards

**Skill ID:** SKILL-API-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **RESTful and JSON:API Engineering Standards** skill establishes the authoritative enterprise engineering standard for designing, constructing, securing, and maintaining production-grade APIs within Universal Core.

Its primary purpose is to transform API engineering from ad-hoc endpoint construction into a disciplined, high-assurance software engineering practice. It provides comprehensive technical guidance across the entire API lifecycle: resource modeling, protocol semantics, contract definition, defense-in-depth security, concurrency control, distributed reliability, performance scalability, and observability.

This skill equips autonomous agents and software engineers to:
- Deeply understand HTTP/1.1 and HTTP/2 protocol mechanics, network boundaries, and state transitions.
- Apply rigorous trade-off analysis between architectural patterns (e.g., REST vs. RPC, offset vs. cursor pagination, synchronous vs. asynchronous execution).
- Enforce strict API contracts using OpenAPI 3.1.0 specifications and machine-readable error schemas.
- Implement robust enterprise patterns in **Laravel 11+**, including Invokable Controllers, Form Request authorization and validation, Domain Actions, JsonResource envelopes, and global exception handlers.
- Eliminate systemic security vulnerabilities such as Broken Object-Level Authorization (BOLA/IDOR), Broken Function-Level Authorization (BFLA), token hijacking, SSRF, and webhook replay attacks.
- Guarantee reliability across unreliable networks using atomic idempotency keys, exponential backoff with jitter, transactional outboxes, and circuit breakers.
- Systematically diagnose, troubleshoot, and prevent production failures using distributed tracing, metric baselines, and structured root-cause analysis.

---

## 2. Scope and Applicability

This standard applies universally to all HTTP APIs, internal service boundaries, external partner integrations, webhooks, and client-facing endpoints built across Universal Core.

- **Framework & Runtime Scope:** Laravel 11.x, PHP 8.3+, running under FastCGI / PHP-FPM, RoadRunner, FrankenPHP, or Laravel Octane.
- **Protocol & Serialization Scope:** HTTP/1.1, HTTP/2, RESTful architectural style, JSON:API (RFC 7159), Problem Details for HTTP APIs (RFC 7807), and OpenAPI 3.1.0.
- **Boundary Scope:**
  - Ingress request validation and sanitization.
  - Authentication, token lifecycle, and cryptographic verification.
  - Multi-tenant and project-level authorization scoping.
  - Domain action orchestration and atomic database transactions.
  - Response transformation, serialization, and caching headers.
  - Outgoing HTTP client integrations and bidirectional webhooks.
- **Exclusions:** Native binary streaming protocols (e.g., raw gRPC protobuf over HTTP/2, AMQP broker wire protocols), which are governed by specialized transport skills, though the architectural principles defined herein continue to apply at the contract boundary.

---

## 3. Required Knowledge and Competencies

Engineers and agents implementing this skill must demonstrate verified competency across eight technical domains:

1. **HTTP Protocol & Semantics:** Precise command of RFC 9110 (HTTP Semantics), RFC 9111 (HTTP Caching), RFC 9112 (HTTP/1.1), safe vs. idempotent methods, status code semantics, and header negotiation.
2. **Resource Modeling:** Domain-Driven resource boundaries, hierarchical URI taxonomy, pluralized entity modeling, and hypermedia/link navigation semantics.
3. **Contract Engineering:** Schema definition via OpenAPI 3.1.0, JSON Schema validation, semantic versioning, and backward-compatible schema evolution.
4. **API Security:** OWASP API Security Top 10 (2023), Laravel Sanctum bearer token governance, OAuth 2.0 / OIDC flows, cryptographic signature verification (HMAC-SHA256), and rate-limiting algorithms.
5. **Distributed Reliability:** Distributed consensus on idempotency, atomic caching locks, exponential backoff with jitter algorithms, transactional outbox pattern, and dead-letter queue pipelines.
6. **High-Throughput Performance:** Keyset/cursor-based pagination mathematics, relational query optimization, N+1 query elimination, HTTP conditionality (`ETag`, `If-None-Match`), and payload streaming.
7. **Operational Telemetry:** W3C Trace Context (`traceparent`, `tracestate`), correlation ID propagation, Prometheus-compatible metric instrumentation, and structured RFC 5424 JSON logging.
8. **Laravel 11 Architecture:** Pipeline middleware execution, Form Request lifecycle, Policy gates, JsonResource transformations, and custom exception rendering in `bootstrap/app.php`.

---

## 4. Core Engineering Principles

### 4.1 Ten-Step Professional Engineering Decision Framework

Every API design, enhancement, or refactoring must execute the following ten-step engineering decision framework. Agents must never implement endpoints without completing and documenting this systematic process:

```
┌────────────────────────────────────────────────────────────────────────┐
│ 1. Understand Requirement  ──►  2. Identify Constraints & Assumptions  │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 3. Analyze System Context  ──►  4. Identify Risks & Failure Modes     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 5. Evaluate Alternatives   ──►  6. Select Approach & Document Trade-Off│
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 7. Define Verification Criteria ──► 8. Implement to Boundaries         │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 9. Execute Automated Tests  ──► 10. Record Verifiable Quality Evidence │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Understand Requirement:** Identify the exact business capability, consumer identity, data exchange volume, and SLA requirements.
2. **Identify Constraints & Assumptions:** Document network latency thresholds, payload size boundaries, consumer client capabilities, and consistency requirements (eventual vs. strong).
3. **Analyze System Context:** Map upstream caller topologies, downstream database schema constraints, third-party dependency latencies, and tenant boundaries.
4. **Identify Risks & Failure Modes:** Perform threat modeling (STRIDE), evaluate concurrency collisions, partial failures, duplicate requests, and resource exhaustion vectors.
5. **Evaluate Alternatives:** Compare at least two distinct architectural patterns (e.g., synchronous execution vs. 202 Accepted polling, cursor vs. offset pagination, header vs. URI versioning).
6. **Select Approach & Document Trade-Off:** Choose the optimal pattern based on concrete technical criteria. Explicitly justify why rejected alternatives were unsuitable.
7. **Define Verification Criteria:** Establish measurable pass/fail gating: HTTP status codes, schema contract compliance, query count limits, and latency budgets.
8. **Implement to Boundaries:** Construct the deliverable adhering strictly to the architectural layers (Route -> Middleware -> FormRequest -> Action -> Resource), respecting file size limits ($\le 300$ lines).
9. **Execute Automated Tests:** Run unit, feature, contract, and adversarial tests verifying positive, negative, and edge-case execution paths.
10. **Record Verifiable Quality Evidence:** Capture test outputs, schema validation logs, and query logs proving that the endpoint satisfies all constraints without regression.

### 4.2 Architectural Trade-Off Analysis

#### 4.2.1 Pagination Strategy: Offset vs. Cursor/Keyset
- **Offset Pagination (`OFFSET n LIMIT m`):**
  - *Advantages:* Easy implementation; supports arbitrary page jumping (`page=15`).
  - *Disadvantages:* Severe performance degradation at scale ($O(N)$ scanning); prone to "page drift" when records are inserted or deleted during traversal.
  - *Decision Rule:* **REJECT** for high-volume datasets ($> 10,000$ records) or real-time event feeds. **PERMITTED ONLY** for small, static administrative catalogs.
- **Cursor/Keyset Pagination (`WHERE id > :cursor ORDER BY id ASC LIMIT m`):**
  - *Advantages:* Constant-time performance ($O(1)$) utilizing indexed B-Trees; deterministic results immune to page drift.
  - *Disadvantages:* Disallows random page jumping; requires sequential navigation (`next_cursor`).
  - *Decision Rule:* **MANDATORY** for all production API entity feeds, event streams, audit logs, and datasets exceeding 10,000 records.

#### 4.2.2 Processing Paradigm: Synchronous vs. Asynchronous (202 Accepted)
- **Synchronous (200 OK / 201 Created):**
  - *Criteria:* Operation finishes deterministically within $< 200\text{ ms}$; involves zero external third-party network calls.
- **Asynchronous (202 Accepted with Status Resource):**
  - *Criteria:* Operation involves complex I/O, heavy transformations, file generation, or third-party web services with variable latency.
  - *Mandatory Pattern:* Return HTTP `202 Accepted` with a `Location: /api/v1/jobs/{job_id}` header pointing to a polling endpoint, or emit a client-registered webhook upon completion.

---

## 5. Mandatory Standards

### 5.1 Universal Engineering Governance
1. **File Size Enforcement:** No PHP source file (Controllers, Actions, Form Requests, Resources, Policies) may exceed 300 physical lines. Classes must be modularized when approaching this boundary.
2. **Strict Typing:** All PHP files must declare `declare(strict_types=1);`. Every method must specify parameter types and return types.
3. **Zero Implementation Debt:** Mock stubs, placeholder responses, swallowed exceptions, and bypassed security checks (`authorize() => true` without inspection) are strictly prohibited.

### 5.2 HTTP Semantics and Protocol Correctness

| HTTP Method | Semantics | Idempotent | Safe | Permitted Status Codes |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | Retrieve resource representation | Yes | Yes | `200 OK`, `304 Not Modified`, `404 Not Found` |
| `HEAD` | Retrieve headers identical to GET | Yes | Yes | `200 OK`, `304 Not Modified`, `404 Not Found` |
| `POST` | Create sub-resource or trigger action | No | No | `201 Created` (with `Location`), `202 Accepted`, `200 OK` |
| `PUT` | Complete resource replacement | Yes | No | `200 OK`, `204 No Content`, `201 Created` |
| `PATCH` | Partial resource mutation | No* | No | `200 OK`, `204 No Content` |
| `DELETE` | Remove resource | Yes | No | `204 No Content`, `200 OK`, `202 Accepted` |

*Note: While RFC 9110 specifies PATCH as non-idempotent by default, PATCH endpoints modifying specific fields must be implemented to be functionally idempotent whenever possible.

#### 5.2.1 Status Code Selection Rules
- **201 Created:** Must include a `Location` header containing the canonical URI of the newly created resource.
- **204 No Content:** Must not include a message body.
- **400 Bad Request:** Syntactically invalid payloads (e.g., malformed JSON).
- **401 Unauthorized:** Missing, expired, or invalid authentication credentials (`WWW-Authenticate` header required).
- **403 Forbidden:** Authenticated identity lacks permission to access or mutate the resource.
- **404 Not Found:** Resource does not exist, or identity lacks permission to know it exists (to prevent enumeration).
- **409 Conflict:** State conflict (e.g., duplicate unique key, optimistic locking collision).
- **412 Precondition Failed:** Conditional header mismatch (`If-Match` does not match current `ETag`).
- **422 Unprocessable Entity:** Syntactically valid JSON that fails semantic or business validation rules.
- **429 Too Many Requests:** Rate limit exceeded. Must include `Retry-After` header.

### 5.3 API Versioning and URL Taxonomy
1. **URL Path Versioning:** Universal Core mandates URI path versioning for major revisions: `/api/v1/resources`.
2. **Resource Naming Conventions:**
   - URIs must use kebab-case, pluralized nouns: `/api/v1/billing-accounts/{account_id}/invoices`.
   - Verbs in URIs are strictly forbidden for CRUD operations. For non-CRUD business actions, use controller actions modeled as sub-resources: `POST /api/v1/invoices/{invoice_id}/settlement`.
3. **Deprecation Protocol (RFC 8594):**
   - When an endpoint is deprecated, it must return the `Deprecation: @<timestamp>` header and `Sunset: <date>` header.
   - Example:
     ```http
     Deprecation: @1735689600
     Sunset: Wed, 01 Jul 2026 00:00:00 GMT
     Link: <https://api.core.dev/docs/deprecations/v1>; rel="deprecation"
     ```

### 5.4 API Contracts and Response Envelopes

#### 5.4.1 Standardized Success Envelope
All JSON responses must return a structured envelope separating primary data from metadata:

```json
{
  "data": {
    "type": "invoices",
    "id": "01hy38a7b9c1d2e3f4g5h6j7k8",
    "attributes": {
      "reference_code": "INV-2026-0091",
      "amount": "1450.00",
      "currency": "EUR",
      "status": "pending",
      "issued_at": "2026-11-14T12:00:00Z"
    }
  },
  "meta": {
    "server_time": "2026-11-14T12:00:05Z",
    "trace_id": "01hy38a7b9-trace-abc123"
  }
}
```

#### 5.4.2 Standardized Error Envelope (RFC 7807 Compliant)
Errors must never return unformatted strings or generic 500 pages. All errors must conform to RFC 7807 / Universal Core standard:

```json
{
  "error": {
    "type": "https://api.core.dev/errors/validation-failed",
    "title": "Validation Failed",
    "status": 422,
    "code": "VALIDATION_FAILED",
    "detail": "The payload contained semantic validation errors.",
    "trace_id": "01hy38a7b9-trace-abc123",
    "invalid_params": [
      {
        "name": "currency",
        "reason": "The currency must be a valid ISO 4217 code."
      }
    ]
  }
}
```

### 5.5 Distributed Reliability Standards

#### 5.5.1 Idempotency Key Architecture
- All state-altering endpoints (`POST`, `PUT`, `PATCH`, `DELETE`) accepting an `Idempotency-Key` header must guarantee exactly-once execution semantics.
- Idempotency flow:
  1. Acquire an atomic lock on `idempotency:{tenant_id}:{key}` with a 60-second lease.
  2. If the key already has a cached response, release lock and return cached response immediately with `X-Cache-Lookup: HIT`.
  3. If another request holds the lock, return `409 Conflict` with `Retry-After: 2`.
  4. Execute the domain action inside a database transaction.
  5. Cache the resulting HTTP response (status, headers, body) for 24 hours under the key.
  6. Release the lock and return the response.

#### 5.5.2 Resilient Outbound Retries with Exponential Backoff and Jitter
When calling external APIs or webhooks, retries must apply full jitter to prevent thundering herds:

$$\text{sleep} = \text{random}(0, \min(T_{\max}, T_{\text{base}} \times 2^{\text{attempt}}))$$

```php
$maxAttempts = 5;
$baseDelayMs = 100;
$maxDelayMs = 3000;

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
        return $httpClient->post($url, $payload);
    } catch (ConnectionException $e) {
        if ($attempt === $maxAttempts) {
            throw $e;
        }
        $exponential = min($maxDelayMs, $baseDelayMs * (2 ** ($attempt - 1)));
        $jitteredDelay = random_int(0, $exponential);
        usleep($jitteredDelay * 1000);
    }
}
```

---

## 6. Implementation Procedures and Practices

The following production-ready Laravel 11 implementation illustrates the complete architectural boundary flow:

### 6.1 Invokable Controller (Orchestrator)
The controller remains strictly under 30 physical lines, orchestrating validation, domain action execution, and response transformation:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Invoices;

use App\Domain\Billing\Actions\SettleInvoiceAction;
use App\Domain\Billing\DTOs\SettleInvoiceDTO;
use App\Http\Requests\Api\V1\Invoices\SettleInvoiceRequest;
use App\Http\Resources\Api\V1\Invoices\InvoiceResource;
use Illuminate\Http\JsonResponse;

final class SettleInvoiceController
{
    public function __invoke(
        SettleInvoiceRequest $request,
        SettleInvoiceAction $action,
        string $invoiceId
    ): JsonResponse {
        $dto = SettleInvoiceDTO::fromRequest($request, $invoiceId);

        $invoice = $action->execute($dto);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(200);
    }
}
```

### 6.2 Form Request (Validation & Authorization Gate)
Validates the payload, inspects policies against Insecure Direct Object References (BOLA/IDOR), and ensures typed access:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Invoices;

use App\Domain\Billing\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SettleInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = Invoice::query()
            ->where('id', $this->route('invoiceId'))
            ->where('tenant_id', $this->user()?->tenant_id)
            ->first();

        return $invoice !== null && ($this->user()?->can('settle', $invoice) ?? false);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'string', 'ulid'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', Rule::in(['USD', 'EUR', 'GBP'])],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

### 6.3 Domain Action with Idempotency & Concurrency Locking
Encapsulates business invariants inside an atomic transaction with pessimistic locking:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\DTOs\SettleInvoiceDTO;
use App\Domain\Billing\Exceptions\InvoiceConflictException;
use App\Domain\Billing\Models\Invoice;
use Illuminate\Support\Facades\DB;

final class SettleInvoiceAction
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway
    ) {}

    public function execute(SettleInvoiceDTO $dto): Invoice
    {
        return DB::transaction(function () use ($dto): Invoice {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()
                ->where('id', $dto->invoiceId)
                ->where('tenant_id', $dto->tenantId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->isSettled()) {
                throw new InvoiceConflictException("Invoice {$invoice->id} has already been settled.");
            }

            $charge = $this->gateway->charge(
                amount: $dto->amount,
                currency: $dto->currency,
                methodId: $dto->paymentMethodId
            );

            $invoice->update([
                'status' => 'settled',
                'settled_at' => now(),
                'transaction_reference' => $charge->transactionId,
            ]);

            return $invoice;
        }, attempts: 3);
    }
}
```

### 6.4 API Resource (Standardized Data Projection)
Guarantees attribute projection and prevents internal model leaks:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Invoices;

use App\Domain\Billing\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
final class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'invoices',
            'id' => $this->id,
            'attributes' => [
                'reference_code' => $this->reference_code,
                'amount' => number_format((float) $this->total_amount, 2, '.', ''),
                'currency' => $this->currency,
                'status' => $this->status,
                'settled_at' => $this->settled_at?->toIso8601String(),
                'created_at' => $this->created_at->toIso8601String(),
            ],
            'links' => [
                'self' => route('api.v1.invoices.show', ['invoiceId' => $this->id]),
            ],
        ];
    }
}
```

---

## 7. Prohibited Practices and Their Rationale

| Prohibited Practice | Architectural / Operational Failure | Mandated Replacement |
| :--- | :--- | :--- |
| **Direct Model Returns in Controllers** | Leaks database schema, hidden fields, password hashes, and unformatted attributes to consumers. | Always transform domain models via explicit `JsonResource` classes. |
| **Unanchored Offset Pagination** | Causes $O(N)$ full table scans and severe database CPU exhaustion on deep page queries (`OFFSET 100000`). | Use Keyset/Cursor-based pagination (`cursorPaginate()`). |
| **`authorize() => true` in Form Requests** | Creates critical Broken Access Control (BOLA/IDOR) vulnerabilities allowing cross-tenant data tampering. | Inspect authenticated user permissions and route model ownership. |
| **Raw SQL in Queries Without Parameterization** | Introduces SQL injection (SQLi) vulnerabilities. | Use parameterized bindings or Eloquent Query Builder exclusively. |
| **Missing HTTP Status Code Adherence** | Returning `200 OK` with `{"error": "..."}` breaks HTTP caches, API gateways, and client error handling. | Return appropriate 4xx/5xx HTTP status codes with standard error bodies. |
| **Synchronous Long-Running Operations** | Request timeouts, thread pool starvation in PHP-FPM, and 504 Gateway Timeouts. | Dispatch asynchronous jobs and return `202 Accepted` with a status URL. |
| **Bypassing Idempotency on State-Changing Retries** | Causes duplicate credit card charges, double billing, or duplicate inventory decrements. | Require and enforce atomic `Idempotency-Key` headers on mutating requests. |
| **Verbs in RESTful Resource URIs** | Violates REST principles (`/api/v1/settle-invoice`), leading to unmaintainable, chaotic URL spaces. | Use HTTP verbs on resource URIs or model actions as sub-resources. |

---

## 8. Security and Reliability Requirements

### 8.1 API Security Architecture (OWASP API Top 10 Aligned)

#### 8.1.1 Broken Object-Level Authorization (BOLA / IDOR Prevention)
- Never rely on client-supplied model IDs without verifying tenant ownership.
- Every query must scope records to the authenticated tenant:
  ```php
  $record = Entity::where('id', $id)->where('tenant_id', $user->tenant_id)->firstOrFail();
  ```
- Use ULIDs or UUIDv7 for all public resource identifiers to eliminate sequential ID enumeration attacks.

#### 8.1.2 Token Lifecycle and Scope Governance
- Laravel Sanctum tokens must enforce explicit token abilities (e.g., `invoices:create`, `invoices:read`).
- Plaintext API tokens must never be logged or stored in the database. Store only SHA-256 hashes.
- Revocation must be instantaneous: token lookup must verify `revoked_at IS NULL` on each request.

#### 8.1.3 Webhook Ingress: Signature Verification and Replay Protection
All incoming webhooks must verify HMAC signatures and enforce replay protection:

```php
final class VerifyWebhookSignature
{
    public function verify(string $payload, string $signature, string $timestamp, string $secret): bool
    {
        // Enforce 5-minute replay tolerance window
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
        return hash_equals($expected, $signature);
    }
}
```

### 8.2 Distributed Webhook Delivery & Transactional Outbox Pattern
When emitting webhooks to external consumers:
1. Do not emit HTTP requests directly inside an active database transaction.
2. Persist the webhook payload to an `outbox_events` table within the primary transaction.
3. A background daemon polls or consumes events, dispatching queued delivery jobs.
4. Jobs deliver webhooks with signed headers (`X-Signature-SHA256`, `X-Timestamp`), tracking delivery attempts and HTTP response codes.

---

## 9. Testing and Verification

To certify that an API endpoint is complete and production-ready, engineers must provide verifiable quality evidence covering four testing tiers:

```
┌────────────────────────────────────────────────────────┐
│ 1. Contract & Schema Validation (OpenAPI 3.1.0)        │
├────────────────────────────────────────────────────────┤
│ 2. Feature & State Transition Testing (HTTP Tests)     │
├────────────────────────────────────────────────────────┤
│ 3. Concurrency & Idempotency Testing                   │
├────────────────────────────────────────────────────────┤
│ 4. Query Budget & Performance Assertion                │
└────────────────────────────────────────────────────────┘
```

### 9.1 Verification Suite Implementation

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Invoices;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Users\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SettleInvoiceApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_settle_invoice_with_valid_contract(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['tenant_id' => $user->tenant_id, 'status' => 'pending']);

        $mockGateway = $this->createMock(PaymentGatewayInterface::class);
        $mockGateway->expects($this->once())
            ->method('charge')
            ->willReturn((object) ['transactionId' => 'TXN-998811']);
        $this->app->instance(PaymentGatewayInterface::class, $mockGateway);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/invoices/{$invoice->id}/settlement", [
                'payment_method_id' => '01hy38a7b9c1d2e3f4g5h6j7k8',
                'amount' => 1450.00,
                'currency' => 'EUR',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => ['reference_code', 'amount', 'currency', 'status', 'settled_at'],
                    'links' => ['self'],
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'settled',
            'transaction_reference' => 'TXN-998811',
        ]);
    }

    public function test_unauthorized_cross_tenant_access_is_forbidden(): void
    {
        $user = User::factory()->create(['tenant_id' => 'tenant-alpha']);
        $foreignInvoice = Invoice::factory()->create(['tenant_id' => 'tenant-beta', 'status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/invoices/{$foreignInvoice->id}/settlement", [
                'payment_method_id' => '01hy38a7b9c1d2e3f4g5h6j7k8',
                'amount' => 100.00,
                'currency' => 'EUR',
            ]);

        $response->assertStatus(403);
    }

    public function test_endpoint_satisfies_strict_query_budget(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['tenant_id' => $user->tenant_id, 'status' => 'pending']);

        DB::enableQueryLog();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/invoices/{$invoice->id}");

        $queries = DB::getQueryLog();
        // Gating criterion: Single resource read must not exceed 2 database queries
        $this->assertLessThanOrEqual(2, count($queries), 'Query budget exceeded for resource read.');
    }
}
```

---

## 10. Failure Scenarios and Prevention

### 10.1 Production Failure Analysis Matrix

| Failure Mode | Root Cause | Impact | Detection Mechanism | Engineering Prevention |
| :--- | :--- | :--- | :--- | :--- |
| **N+1 Query Explosion** | Accessing lazy-loaded relations in JsonResource collection. | Massive database latency spike, connection pool exhaustion. | Query log monitoring; `Model::preventLazyLoading(!app()->isProduction())`. | Eager load relations with explicit column selections (`with(['items'])`). |
| **Webhook Replay Stampede** | Malicious or network-retried webhooks processed repeatedly. | Duplicate inventory allocation, repeated financial debits. | Duplicate correlation IDs in access logs; duplicate unique constraint errors. | Enforce HMAC signature with 5-minute timestamp window and unique message IDs. |
| **Distributed Deadlock** | Concurrent transactions locking resources in divergent orders. | 500 Internal Server Errors, aborted transactions, worker crashes. | MySQL/PostgreSQL deadlock error codes (`1213`, `40P01`). | Sort entities deterministically before locking; wrap in `DB::transaction(..., attempts: 3)`. |
| **SSRF via Webhook Ingress** | Consumer-provided webhook URLs pointing to internal metadata APIs (`169.254.169.254`). | Exfiltration of cloud IAM credentials and private internal infrastructure data. | Security audit logs showing private CIDR connections. | Validate URL against explicit allowlist; resolve DNS and block private/loopback IP ranges. |
| **Memory Exhaustion on Large Exports** | Loading 50,000 Eloquent models into memory for JSON serialization. | PHP fatal error `Allowed memory size exhausted`, 502 Bad Gateway. | APM memory consumption alerts; fatal error logs. | Stream JSON directly using `cursor()` and chunked streaming responses. |

### 10.2 Deep Dive: Concurrency Collision on Account Balances
- **Scenario:** Two concurrent API requests arrive simultaneously to deduct \$50 from a balance of \$60.
- **Root Cause:** Both threads execute `SELECT balance FROM accounts WHERE id = 1` before either writes, seeing \$60. Both proceed to deduct \$50, leaving the balance at -\$40.
- **Prevention:** Apply pessimistic row-level locking (`SELECT ... FOR UPDATE`) inside an atomic transaction:
  ```php
  DB::transaction(function () use ($accountId, $deductionAmount) {
      $account = Account::query()->where('id', $accountId)->lockForUpdate()->firstOrFail();
      if ($account->balance < $deductionAmount) {
          throw new InsufficientFundsException();
      }
      $account->decrement('balance', $deductionAmount);
  }, attempts: 3);
  ```

---

## 11. Acceptance Criteria

Every API deliverable must satisfy 100% of the following verification checklist before being accepted:

- [ ] **Strict Typing:** File begins with `declare(strict_types=1);` and all parameters/returns are explicitly typed.
- [ ] **Line Count Limit:** File does not exceed 300 physical lines.
- [ ] **Thin Controller:** Controller is single-action (`__invoke`) or strictly delegates orchestration to Actions.
- [ ] **Policy Authorization:** Form Request validates and enforces policy permissions (`authorize()`); no bypassed gates.
- [ ] **Strict Input Validation:** All payload parameters are validated against strict rules and allowed values.
- [ ] **Contract Compliance:** Responses conform strictly to the standardized `data` / `meta` / `error` envelopes.
- [ ] **No Model Leakage:** Endpoints return dedicated `JsonResource` classes; raw Eloquent models are never returned.
- [ ] **Atomicity & Locking:** State-modifying multi-record operations are wrapped in `DB::transaction()` with deadlock retries.
- [ ] **Idempotency Safeguards:** Financial or mutating endpoints implement atomic idempotency key verification.
- [ ] **Query Efficiency:** Lazy loading is disabled; endpoints do not trigger N+1 queries and satisfy query budgets.
- [ ] **HTTP Protocol Correctness:** Appropriate status codes are returned (201 with Location, 204, 400, 401, 403, 404, 409, 422).
- [ ] **Automated Tests:** Feature tests verify happy path, validation rejections, unauthorized tenant access, and query count bounds.

---

## 12. References and Related Skills

### 12.1 Authoritative Specifications and Standards
- **RFC 9110:** HTTP Semantics (IETF)
- **RFC 9111:** HTTP Caching (IETF)
- **RFC 7807 / RFC 9457:** Problem Details for HTTP APIs (IETF)
- **RFC 8594:** The Sunset HTTP Header Field (IETF)
- **OpenAPI Specification:** Version 3.1.0 (OpenAPI Initiative)
- **OWASP API Security Top 10 (2023):** Open Web Application Security Project

### 12.2 Related Universal Core Skills
- `SKILL-LARAVEL-001`: Laravel Engineering Standards
- `SKILL-SEC-001`: Secure Coding and Defensive Engineering Standards
- `SKILL-DB-001`: Database Engineering and Schema Governance Standards
- `SKILL-TEST-001`: Automated Testing and Verification Standards
- `SKILL-QUEUE-001`: Queue and Asynchronous Architecture Standards

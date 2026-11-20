# Shared Engineering Skill: Exception Handling, Telemetry and Observability Standards

**Skill ID:** SKILL-ERR-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Exception Handling, Telemetry and Observability Standards** skill establishes the authoritative enterprise standard for error architecture, structured logging, distributed tracing, metric instrumentation, alerting, and operational observability across Universal Core.

Its primary purpose is to provide real-time, high-fidelity visibility into distributed system health, performance, and failure modes while guaranteeing strict tenant isolation, zero PII or credential leakage, and sub-minute incident diagnosis. It defines concrete implementation rules for correlating operations across HTTP boundaries, Redis queues, and database transactions; formulating actionable Service Level Objectives (SLOs); enforcing RED and USE monitoring methodologies; and preventing alert fatigue.

---

## 2. Scope and Applicability

This standard governs all runtime error handling, diagnostic instrumentation, and telemetry pipelines across Universal Core:
- **Application Exception Architecture:** Four-tier exception classification (Domain, Application, Infrastructure, Integration), HTTP error response formatting, and centralized translation in Laravel 11 (`bootstrap/app.php`).
- **Exception Failure Semantics:** Preserving root cause chains (`$previous`), diagnostic context capture, atomic transaction rollback guarantees, retryable vs. non-retryable exception handling, and suppression of duplicate reporting.
- **Context Propagation & Distributed Tracing:** W3C TraceContext (`traceparent`), distributed correlation IDs (UUIDv7), and unified context propagation across HTTP, Redis queues, and outbound integrations.
- **Structured JSON Logging:** Monolog 3.x processors, structured logging schemas, and automated sensitive data redaction across context, extra, messages, and exception traces.
- **Component Instrumentation:** HTTP middleware, MySQL query listeners (`DB::listen`), Redis command telemetry, queue worker event listeners, and scheduled command diagnostics.
- **System Metrics & Monitoring:** RED (Rate, Errors, Duration) for service endpoints and USE (Utilization, Saturation, Errors) for infrastructure resources; cardinality governance.
- **Health Probes & Incident Management:** Liveness (`/health/live`) and readiness (`/health/ready`) probes, alerting severity classification (P1–P4), and blameless post-incident reviews.

---

## 3. Required Knowledge and Competencies

Engineers and automated agents applying this skill must demonstrate verified expertise in:
1. **Exception Hierarchy and Error Contracts:** Four-tier exception classification, domain invariants vs. systemic faults vs. external integration failures, error code contracts, and RFC 7807 / JSON:API error response mappings ($400, 401, 403, 404, 409, 422, 500, 502, 503, 504$).
2. **Transaction Consistency & Rollback Boundaries:** Database transaction lifecycles under failure conditions, ensuring atomic rollback before rethrowing or translating exceptions to eliminate partial state corruption.
3. **Queue Failure Semantics:** Differentiating transient retryable exceptions (network blips, rate limits) with stepped backoff and jitter from permanent non-retryable failures (validation, invalid credentials) terminating immediately via `$this->fail($e)`.
4. **The Three Pillars of Observability:** Deep understanding of the distinct roles, data structures, cost models, and limitations of Logs, Metrics, and Distributed Traces:
   - **Logs:** Discrete, high-context, high-cardinality event records. Crucial for forensic root-cause analysis; expensive to index at scale.
   - **Metrics:** Aggregated numeric time-series data (counters, gauges, histograms). Extremely fast, low-cost alerting; completely lack individual execution context.
   - **Traces:** Causal DAGs (Directed Acyclic Graphs) tracking execution across distributed service boundaries. Crucial for latency breakdown and bottleneck identification.
5. **Distributed Tracing & Context Standards:** W3C TraceContext specifications (`traceparent: version-trace_id-parent_id-flags`), OpenTelemetry semantic conventions, and cross-process header propagation.
6. **Laravel 11 Error Pipeline:** Centralized exception handling via `bootstrap/app.php` using the `withExceptions()` closure, custom renderers, reporting callbacks (`reportable`), and reporting suppression (`dontReport`).
7. **Data Privacy & Governance:** GDPR Article 32 compliance, PCI-DSS requirements for log sanitization, and automated redaction of authentication tokens, API keys, and PII.
8. **Metric Cardinality Management:** The mechanics of time-series databases (TSDBs) and why unbounded cardinality (e.g., tagging metrics with UUIDs, user IDs, or search queries) causes exponential memory exhaustion.

---

## 4. Core Engineering Principles

1. **Explicit Exception Classification & Cause Preservation:** Every exceptional condition must be mapped to a typed, domain-meaningful exception inheriting from `AppException`. Never swallow exceptions in empty catch blocks, and never lose the original cause when wrapping or translating exceptions—always chain `$e->getPrevious()` and attach structured diagnostic context.
2. **Atomic Rollback and Fail-Closed State:** When an operation fails within an active database transaction, the transaction must roll back cleanly before any reporting, translation, or rethrowing occurs. Execution must never leave partially written records or corrupt tenant datasets.
3. **Differentiated Retry Semantics:** Only transient, recoverable integration or infrastructure failures (e.g. rate limits with `retry_after`, gateway timeouts) may be retried. Non-retryable domain invariant violations or malformed payloads must terminate immediately without retry storms.
4. **Single-Source Error Reporting:** Eliminate duplicate error logs. Never catch an exception, log it locally with `Log::error(...)`, and then re-throw it to the framework handler. Handle and recover, OR enrich and re-throw, OR allow the centralized exception reporter to log it once with complete context.
5. **Structured & Machine-Readable by Default:** Plain-text, unstructured log strings are strictly prohibited in production. All log entries must be emitted as single-line, structured JSON adhering to the Universal Core Telemetry Schema.
6. **End-to-End Context Correlation:** Every inbound HTTP request, console command, or background queue job must possess an immutable `correlation_id` (UUIDv7) and W3C `trace_id`. This identifier must propagate through all database operations, Redis queue jobs, and outbound HTTP client requests.
7. **Fail Safely and Conceal Internals in Production:** Client-facing HTTP responses must never expose stack traces, database schema details, raw SQL queries, or internal file paths. Production errors must return standardized, sanitized JSON:API error objects containing the `correlation_id` for client-side support reference.
8. **Zero Sensitive Data in Telemetry:** Passwords, API tokens, bearer credentials, credit card numbers (PAN), and sensitive PII must be scrubbed at the logger processor level before reaching disk, network, or external monitoring sinks.
9. **Bounded Metric Cardinality:** Metric label dimensions must be strictly bounded to finite, low-cardinality sets (e.g., `http_status_group`, `queue_name`, `tenant_tier`). High-cardinality values belong exclusively in logs and trace span attributes.
10. **Actionable Alerting on SLO Degradation:** Alerts must page on customer-impacting symptoms and error budget burn rates—never on isolated, transient anomalies. Zero tolerance for alert fatigue.

---

## 5. Mandatory Standards

### 5.1 Telemetry Data Models and The Three Pillars Matrix

Telemetry data within Universal Core must be routed and governed according to the following evaluated characteristics:

| Telemetry Pillar | Primary Purpose | Underlying Data Model | Cardinality Tolerance | Storage & Cost Model | Retention Standard |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Metrics** | Real-time health monitoring, anomaly detection, automated scaling, and alerting. | Numeric time-series (Counter, Gauge, Histogram) indexed by low-cardinality tags. | **Strictly Finite & Low** ($< 50$ distinct values per label). | Extremely compact; low compute and storage cost. | Raw: 14 days; Aggregated: 90 days. |
| **Logs** | Detailed event context, forensic audit trails, and post-mortem root-cause analysis. | Semi-structured JSON key-value documents with message string and context payload. | **High** (Accepts user IDs, transaction IDs, error messages). | Voluminous; high indexing and search compute cost. | Debug: 7 days; Info: 30 days; Audit: 365+ days. |
| **Traces** | End-to-end distributed transaction latency profiling and dependency mapping. | Directed graphs of spans containing timestamped intervals and span attributes. | **Medium to High** (Spans hold high-cardinality attributes without index degradation). | Moderate to high; governed via head/tail sampling strategies. | 14 days (100% errors, 5% sampled successes). |

---

### 5.2 Structured Logging Schema

Every log entry generated across Universal Core must emit valid JSON matching this mandatory schema:

```json
{
  "timestamp": "2026-11-14T18:25:00.123456Z",
  "level": "ERROR",
  "message": "Payment settlement gateway rejected transaction.",
  "service": "billing-service",
  "environment": "production",
  "correlation_id": "01932e54-9a8c-7b31-8e01-8b2b71234567",
  "trace_id": "4bf92f3577b34da6a3ce929d0e0e4736",
  "span_id": "00f067aa0ba902b7",
  "tenant_id": "01932e50-201a-7b31-8e01-8b2b79876543",
  "user_id": "01932e52-1111-7b31-8e01-8b2b70000000",
  "duration_ms": 142.5,
  "context": {
    "transaction_id": 48291,
    "gateway": "stripe",
    "response_code": "insufficient_funds"
  },
  "exception": {
    "class": "App\\Domain\\Billing\\Exceptions\\PaymentGatewayRejectedException",
    "message": "Card has insufficient funds for capture.",
    "code": 402,
    "file": "/app/src/Domain/Billing/Services/StripeGateway.php",
    "line": 84,
    "stack_trace": "[REDACTED_OR_STRUCTURED_FRAMES]"
  }
}
```

---

### 5.3 Correlation ID & Distributed Trace Propagation

1. **HTTP Ingestion Pipeline:**
   - The inbound HTTP middleware (`CorrelationAndTraceMiddleware`) must inspect the incoming headers:
     - `X-Correlation-ID`: If present and a valid UUID, adopt it; otherwise, generate a fresh `UUIDv7`.
     - `traceparent`: If present, parse W3C TraceContext; otherwise, generate a fresh 16-byte `trace_id` and 8-byte `span_id`.
   - The middleware immediately binds these identifiers into `Log::withContext()` and appends `X-Correlation-ID` to the outgoing HTTP response headers.
2. **Queue Dispatch Propagation:**
   - When a job is dispatched, the current `correlation_id` and `traceparent` must be injected into the queued job's metadata.
   - When a queue worker picks up the job, the queue middleware extracts these values and binds them to `Log::withContext()` before executing `handle()`.
3. **Outbound HTTP Propagation:**
   - All outbound HTTP requests made via Laravel's `Http` client or Guzzle must automatically attach the `X-Correlation-ID` and `traceparent` headers to preserve distributed causal tracing across external microservices and third-party integrations.

---

### 5.4 Exception Architecture, Classification, and Failure Semantics

1. **Four-Tier Exception Classification Hierarchy:**
   All application exceptions must inherit from the abstract root `AppException` and adhere to strict semantic tiering:
   - **`AppException` (Root Base Exception):** Encapsulates machine-readable `errorCode`, HTTP `statusCode`, client-facing `title`, structured diagnostic `context` array, and previous exception chaining (`?Throwable $previous`).
   - **`DomainException` (Client-Correctable / 4xx):** Represents domain business rule and invariant violations (e.g., `InsufficientBalanceException`, `DuplicateResourceException`, `OrderAlreadySettledException`). Maps directly to HTTP $400, 404, 409, 422$. Excluded from error budgets and on-call alerting; suppressed in centralized error trackers via `$exceptions->dontReport([DomainException::class])`.
   - **`ApplicationException` (Application Workflow & Auth / 4xx):** Represents client workflow, authentication, authorization, or rate-limiting failures (e.g., `UnauthorizedException` $\to 401$, `ForbiddenException` $\to 403$, `RateLimitExceededException` $\to 429$).
   - **`InfrastructureException` (Systemic Internal Faults / 5xx):** Represents internal technical infrastructure failures (e.g., `DatabaseConnectionLostException`, `RedisTimeoutException`, `DiskFullException`). Maps to HTTP $500$ or $503$. Must log at `ERROR` or `CRITICAL` with full trace and increment error metrics.
   - **`IntegrationException` (Third-Party External Services):** Represents external HTTP/API gateway failures (e.g., `PaymentGatewayException`, `TwilioSmsException`). Must declare explicit retryability via `isRetryable(): bool`:
     - *Retryable/Transient:* HTTP 408 (Request Timeout), HTTP 429 (Rate Limit with `retry_after`), HTTP 502 (Bad Gateway), HTTP 503 (Service Unavailable), HTTP 504 (Gateway Timeout), connection resets, and TLS handshake timeouts.
     - *Non-Retryable/Permanent:* HTTP 400 (Malformed Payload), HTTP 401/403 (Invalid API Credentials), HTTP 404 (Resource Not Found on Provider), HTTP 422 (Provider Business Validation Rejection).

2. **Preserving Original Exception Causes & Diagnostic Context:**
   - When catching lower-level or third-party exceptions and re-throwing them as higher-level domain or integration exceptions, developers must ALWAYS chain the original exception: `new InfrastructureException($message, previous: $e, context: [...])`.
   - Never suppress or sever `$e->getPrevious()`.
   - The diagnostic `context` array must capture relevant identifiers (`order_id`, `tenant_id`, `gateway_reference`, `attempt_number`) while strictly omitting raw credentials, authentication tokens, and full payment details.

3. **Database Transaction Consistency and Atomic Rollback:**
   - Any failure within a database operation must ensure complete transaction rollback before translation, logging, or rethrowing.
   - Preferred pattern: `DB::transaction(function () { ... })`, which automatically triggers `DB::rollBack()` upon any thrown `Throwable`.
   - When using manual transaction demarcations (`DB::beginTransaction()`), the `catch` block must explicitly invoke `DB::rollBack()` before rethrowing or dispatching error notifications to guarantee that no uncommitted or corrupted state lingers.

4. **Queue Worker Failure Semantics:**
   - In background queue jobs, exception handling must inspect exception retryability:
     - If `$e instanceof IntegrationException && $e->isRetryable()`: calculate stepped backoff with jitter and call `$this->release($delay)` (or allow worker retry loop up to `$tries`).
     - If non-retryable (e.g., `DomainException`, invalid payload, permanent auth failure): call `$this->fail($e)` immediately. Never consume worker cycles on unrecoverable failures.
   - Because Laravel queue middleware only wraps `handle()`, any status update executed in `$job->failed(Throwable $e)` must restore tenant context (`TenantContext::executeAs()`) so global scopes do not block failure state recording.

5. **Eliminating Swallowed Exceptions and Duplicate Reporting:**
   - **Swallowed Exceptions Banned:** Empty catch blocks (`catch (Throwable $e) {}`) are strictly prohibited. Every caught exception must either be handled with legitimate compensating business logic, logged at appropriate severity with diagnostic context, or re-thrown.
   - **Preventing Duplicate Logs:** Never log an exception in a local catch block (`Log::error($e->getMessage())`) and then re-throw `throw $e`. This produces duplicate log entries when Laravel's global exception handler intercepts the rethrown exception. Either handle and log, OR enrich and rethrow, OR let the framework reporter process it once.

6. **Production Shielding and Standardized JSON:API Error Formatting:**
   - In production environments (`APP_DEBUG=false`), unhandled 5xx exceptions must NEVER expose database query strings, file lines, or raw exception messages to the client.
   - The client must receive a standardized JSON response:
     ```json
     {
       "errors": [
         {
           "status": "500",
           "code": "INTERNAL_SERVER_ERROR",
           "title": "Internal Server Error",
           "detail": "An unexpected error occurred while processing your request. Please quote the correlation ID when contacting support.",
           "meta": {
             "correlation_id": "01932e54-9a8c-7b31-8e01-8b2b71234567"
           }
         }
       ]
     }
     ```

---

### 5.5 Component Instrumentation Standards

1. **Database Query Telemetry (`DB::listen`):**
   - Register a query listener in `AppServiceProvider`:
     - Log all queries exceeding $100\text{ ms}$ at `WARNING` level with message `"Slow database query detected"`.
     - Mask literal parameter bindings to prevent SQL parameter data leakage.
     - Detect and log MySQL deadlocks (Error code `1213`) at `ERROR` level with transaction duration.
2. **Redis Command & Connection Monitoring:**
   - Track Redis command latency. Log commands taking $> 20\text{ ms}$ at `WARNING` level.
   - Monitor connection pool drops and catch `RedisException` to alert immediately.
3. **Queue Worker Instrumentation:**
   - Listen to Laravel queue events (`JobQueued`, `JobProcessing`, `JobProcessed`, `JobFailed`):
     - Calculate **Queue Wait Time**: $\text{wait\_time} = \text{pickup\_time} - \text{dispatched\_time}$.
     - Calculate **Job Execution Duration**: $\text{duration} = \text{finish\_time} - \text{pickup\_time}$.
     - Increment Prometheus/StatsD counters for `jobs_processed_total` and `jobs_failed_total`.

---

### 5.6 Metrics Methodologies: RED, USE, and SLO Governance

1. **The RED Method (For Request-Driven Services & APIs):**
   - **Rate:** Requests per second ($\text{req/s}$) partitioned by endpoint and tenant tier.
   - **Errors:** Failed requests per second ($5\text{xx}$ responses).
   - **Duration:** Latency distribution measured in percentiles: $p50$, $p90$, $p95$, $p99$. Arithmetic averages are strictly banned for latency reporting as they conceal tail latency spikes.
2. **The USE Method (For Infrastructure Resources: CPU, Memory, MySQL, Redis):**
   - **Utilization:** Percentage of time the resource is busy (e.g., MySQL thread pool % active, CPU % utilized).
   - **Saturation:** The degree to which extra work is queued (e.g., Redis queue depth, MySQL lock wait queue length, OS load average).
   - **Errors:** Error events (e.g., memory allocation errors, MySQL connection aborts, packet drops).
3. **Service Level Objectives (SLOs) & Error Budgets:**
   - Every tier-1 API service must define an SLO:
     *Example:* "$99.9\%$ of valid requests to `/api/v1/checkout` return $2\text{xx}$ in $< 250\text{ ms}$ over a rolling 30-day window."
   - Automated alerts fire when the **Error Budget Burn Rate** exceeds critical thresholds ($14.4\times$ burn over 1 hour = P1 Page; $6\times$ burn over 6 hours = P2 Ticket).

---

### 5.7 Security, Privacy, and Redaction Governance

1. **Mandatory Log Redaction Processor:**
   - Telemetry pipelines must run a Monolog record processor (`SensitiveDataScrubberProcessor`) that executes before log records reach every configured output handler (file, socket, Sentry, Datadog).
   - The processor must recursively inspect and mask sensitive keys across `$record->context`, `$record->extra`, `$record->message`, and `$record->context['exception']` instances.
   - The following keys must be scrubbed unconditionally:
     `password`, `password_confirmation`, `token`, `secret`, `api_key`, `authorization`, `cookie`, `credit_card`, `card_number`, `cvv`, `pan`, `ssn`.
   - Scrubbed values must be replaced with the exact literal string `"[REDACTED]"`.
   - Pattern-based redaction must scrub raw JWTs (`eyJ...`), Bearer authorization strings, and 13–19 digit credit card PANs from all free-text messages and exception strings.
   - The processor must be registered on all active logging channels in `config/logging.php` via `'tap' => [App\Core\Observability\Logging\SensitiveDataScrubberProcessor::class]`.
2. **Log Access Control & Tamper Resistance:**
   - Production log files must be configured with append-only permissions (`0640` owned by `www-data:adm`).
   - Centralized log shipping must enforce TLS 1.3 in-transit and AES-256 at-rest encryption.

---

## 6. Implementation Procedures and Practices

### 6.1 Correlation and Distributed Tracing Middleware

```php
<?php

declare(strict_types=1);

namespace App\Core\Observability\Middleware;

use App\Core\Tenancy\Context\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class CorrelationAndTraceMiddleware
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // 1. Resolve or generate Correlation ID (UUIDv7)
        $correlationId = $request->header('X-Correlation-ID');
        if (empty($correlationId) || ! Str::isUuid($correlationId)) {
            $correlationId = (string) Str::uuid7();
        }

        // 2. Resolve or generate W3C TraceContext
        $incomingTraceparent = $request->header('traceparent');
        if (
            empty($incomingTraceparent) ||
            ! preg_match('/^00-([a-f0-9]{32})-([a-f0-9]{16})-([a-f0-9]{2})$/', $incomingTraceparent, $matches) ||
            $matches[1] === '00000000000000000000000000000000' ||
            $matches[2] === '0000000000000000'
        ) {
            $traceId = bin2hex(random_bytes(16));
            $spanId = bin2hex(random_bytes(8));
            $traceFlags = '01';
            $traceparent = sprintf('00-%s-%s-%s', $traceId, $spanId, $traceFlags);
        } else {
            $traceId = $matches[1];
            $spanId = bin2hex(random_bytes(8)); // Generate child span for local execution frame
            $traceFlags = $matches[3];
            $traceparent = sprintf('00-%s-%s-%s', $traceId, $spanId, $traceFlags);
        }

        // 3. Bind structured context to Laravel's logging facade
        Log::withContext([
            'correlation_id' => $correlationId,
            'trace_id' => $traceId,
            'span_id' => $spanId,
            'tenant_id' => $this->tenantContext->check() ? $this->tenantContext->id() : null,
            'user_id' => $request->user()?->getAuthIdentifier(),
            'http_method' => $request->method(),
            'http_path' => $request->path(),
            'client_ip' => hash('sha256', $request->ip() . config('app.key')), // Anonymized IP
        ]);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // 4. Attach tracing headers to response
        $response->headers->set('X-Correlation-ID', $correlationId);
        $response->headers->set('traceparent', $traceparent);
        $response->headers->set('X-Response-Time-Ms', (string) $durationMs);

        return $response;
    }
}
```

---

### 6.2 Sensitive Data Redaction Monolog Processor

```php
<?php

declare(strict_types=1);

namespace App\Core\Observability\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Throwable;

final class SensitiveDataScrubberProcessor implements ProcessorInterface
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'secret',
        'api_key',
        'authorization',
        'card_number',
        'cvv',
        'pan',
        'ssn',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        $scrubbedContext = $this->scrubArray($record->context);
        $scrubbedExtra = $this->scrubArray($record->extra);
        $scrubbedMessage = $this->scrubString($record->message);

        return $record->with(
            message: $scrubbedMessage,
            context: $scrubbedContext,
            extra: $scrubbedExtra
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function scrubArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->scrubArray($value);
                continue;
            }

            if ($value instanceof Throwable) {
                $data[$key] = $this->scrubThrowable($value);
                continue;
            }

            if (is_string($key) && $this->isSensitiveKey($key)) {
                $data[$key] = '[REDACTED]';
                continue;
            }

            if (is_string($value)) {
                $data[$key] = $this->scrubString($value);
            }
        }

        return $data;
    }

    private function scrubString(string $value): string
    {
        if (str_starts_with($value, 'Bearer ') || str_starts_with($value, 'eyJ')) {
            return '[REDACTED_PATTERN]';
        }

        if (preg_match('/\b(?:\d[ -]*?){13,19}\b/', $value)) {
            return (string) preg_replace('/\b(?:\d[ -]*?){13,19}\b/', '[REDACTED_PAN]', $value);
        }

        return $value;
    }

    /**
     * @return array{class: string, message: string, code: int|string, file: string, line: int, previous?: mixed}
     */
    private function scrubThrowable(Throwable $e): array
    {
        $sanitized = [
            'class' => get_class($e),
            'message' => $this->scrubString($e->getMessage()),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e->getPrevious() !== null) {
            $sanitized['previous'] = $this->scrubThrowable($e->getPrevious());
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', '_'], '', $key));

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($normalized, str_replace(['-', '_'], '', $sensitive))) {
                return true;
            }
        }

        return false;
    }
}
```

To guarantee execution prior to every handler, register the processor in `config/logging.php`:
```php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
        'tap' => [App\Core\Observability\Logging\SensitiveDataScrubberProcessor::class],
    ],
],
```

---

### 6.3 Centralized Exception Handling in Laravel 11 (`bootstrap/app.php`)

```php
<?php

use App\Core\Observability\Exceptions\AppException;
use App\Core\Observability\Exceptions\DomainException;
use App\Core\Observability\Exceptions\InfrastructureException;
use App\Core\Observability\Exceptions\IntegrationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Core\Observability\Middleware\CorrelationAndTraceMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. Configure reporting suppression: domain exceptions must not pollute error logs or error trackers
        $exceptions->dontReport([
            DomainException::class,
        ]);

        // 2. Centralized telemetry reporting: enrich unhandled exceptions without duplicate logging
        $exceptions->report(function (Throwable $e) {
            $context = Log::sharedContext();
            if ($e instanceof IntegrationException) {
                Log::error('External integration service failure encountered.', [
                    'correlation_id' => $context['correlation_id'] ?? null,
                    'is_retryable' => $e->isRetryable(),
                    'error_code' => $e->getErrorCode(),
                    'diagnostic_context' => $e->getContext(),
                    'exception_message' => $e->getMessage(),
                ]);
            }
        });

        // 3. Centralized JSON:API rendering for all API routes
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $correlationId = Log::sharedContext()['correlation_id'] ?? (string) Str::uuid7();

            // Handle domain and application exceptions (Client-correctable - 4xx)
            if ($e instanceof DomainException || $e instanceof AppException) {
                return response()->json([
                    'errors' => [
                        [
                            'status' => (string) $e->getStatusCode(),
                            'code' => $e->getErrorCode(),
                            'title' => $e->getTitle(),
                            'detail' => $e->getMessage(),
                            'meta' => [
                                'correlation_id' => $correlationId,
                                'context' => config('app.debug') ? $e->getContext() : null,
                            ],
                        ],
                    ],
                ], $e->getStatusCode());
            }

            // Handle standard HTTP framework exceptions (404, 403, 405, 429)
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'errors' => [
                        [
                            'status' => (string) $e->getStatusCode(),
                            'code' => 'HTTP_ERROR',
                            'title' => Response::$statusTexts[$e->getStatusCode()] ?? 'HTTP Error',
                            'detail' => $e->getMessage() ?: (Response::$statusTexts[$e->getStatusCode()] ?? 'HTTP Error'),
                            'meta' => ['correlation_id' => $correlationId],
                        ],
                    ],
                ], $e->getStatusCode());
            }

            // Handle unhandled systemic & infrastructure exceptions (500)
            // Conceal internals in production; never expose raw message or trace
            return response()->json([
                'errors' => [
                    [
                        'status' => '500',
                        'code' => 'INTERNAL_SERVER_ERROR',
                        'title' => 'Internal Server Error',
                        'detail' => config('app.debug')
                            ? $e->getMessage()
                            : 'An unexpected error occurred while processing your request. Please quote the correlation ID when contacting support.',
                        'meta' => [
                            'correlation_id' => $correlationId,
                            'trace' => config('app.debug') ? explode("\n", $e->getTraceAsString()) : null,
                        ],
                    ],
                ],
            ], 500);
        });
    })->create();
```

---

### 6.4 Base Exception Hierarchy Implementation

```php
<?php

declare(strict_types=1);

namespace App\Core\Observability\Exceptions;

use Exception;
use Throwable;

abstract class AppException extends Exception
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $statusCode = 500,
        private readonly string $title = 'Application Error',
        private readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}

class DomainException extends AppException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        string $errorCode = 'DOMAIN_INVARIANT_VIOLATION',
        int $statusCode = 422,
        string $title = 'Domain Error',
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode, $statusCode, $title, $context, $previous);
    }
}

class ApplicationException extends AppException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        string $errorCode = 'APPLICATION_ERROR',
        int $statusCode = 400,
        string $title = 'Client Request Error',
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode, $statusCode, $title, $context, $previous);
    }
}

class InfrastructureException extends AppException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        string $errorCode = 'INFRASTRUCTURE_FAILURE',
        int $statusCode = 500,
        string $title = 'System Infrastructure Error',
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode, $statusCode, $title, $context, $previous);
    }
}

class IntegrationException extends AppException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        private readonly bool $isRetryable = false,
        string $errorCode = 'INTEGRATION_GATEWAY_FAILURE',
        int $statusCode = 502,
        string $title = 'External Service Error',
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode, $statusCode, $title, $context, $previous);
    }

    public function isRetryable(): bool
    {
        return $this->isRetryable;
    }
}
```

---

### 6.5 Comprehensive Health, Readiness, and Liveness Probes

```php
<?php

declare(strict_types=1);

namespace App\Core\Observability\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class HealthCheckController
{
    /**
     * Liveness probe: Confirms web server process is responsive.
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Readiness probe: Confirms downstream infrastructure (MySQL, Redis) is healthy.
     */
    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $isReady = ! in_array(false, array_column($checks, 'healthy'), true);

        return response()->json([
            'status' => $isReady ? 'ready' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $isReady ? 200 : 503);
    }

    /**
     * @return array{healthy: bool, latency_ms: float, error?: string}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            return [
                'healthy' => true,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            Log::error('Readiness probe failed database connectivity check.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'healthy' => false,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => config('app.debug')
                    ? 'Database connection failed: ' . $e->getMessage()
                    : 'Database connection unavailable',
            ];
        }
    }

    /**
     * @return array{healthy: bool, latency_ms: float, error?: string}
     */
    private function checkRedis(): array
    {
        $start = microtime(true);
        try {
            $pong = Redis::ping();
            $healthy = ($pong === true || $pong === 'PONG' || $pong === '+PONG' || (string) $pong === 'PONG');

            return [
                'healthy' => $healthy,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            Log::error('Readiness probe failed Redis ping check.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'healthy' => false,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => config('app.debug')
                    ? 'Redis ping failed: ' . $e->getMessage()
                    : 'Cache datastore unavailable',
            ];
        }
    }
}
```

---

## 7. Prohibited Practices and Anti-Patterns

1. **Swallowing Exceptions in Empty Catch Blocks:**
   - *Violation:* Writing `catch (Throwable $e) {}` with zero reporting or telemetry.
   - *Consequence:* Silent failures hide corrupt state, prevent retries, and make debugging in production impossible.
2. **Double-Logging Exceptions (Catch and Rethrow):**
   - *Violation:* Catching an exception, logging it via `Log::error($e->getMessage())`, and then calling `throw $e;`.
   - *Consequence:* Causes duplicate log records, artificially inflates error counts, and triggers duplicate alert notifications.
3. **Retrying Non-Retryable Integration or Domain Failures:**
   - *Violation:* Retrying permanent errors (e.g., 400 Bad Request, 401 Unauthorized, 422 Business Validation) in queue workers.
   - *Consequence:* Wastes worker throughput, thrashes database connections, and creates thundering-herd load on external APIs.
4. **Executing External HTTP Requests Inside Open Database Transactions:**
   - *Violation:* Calling third-party payment gateways, webhooks, or email APIs while holding active InnoDB row locks (`lockForUpdate()`).
   - *Consequence:* Network latency holds database locks, starves the MySQL connection pool, and triggers catastrophic deadlocks.
5. **Exposing Raw Stack Traces or Exception Messages to HTTP Clients in Production:**
   - *Violation:* Leaving `APP_DEBUG=true` in production or returning `$e->getMessage()` on 500 errors or health probes.
   - *Consequence:* Discloses database credentials, table schemas, internal file paths, and environment secrets to attackers.
6. **High-Cardinality Tags in Metric Dimensions:**
   - *Violation:* Adding `$userId`, `$orderUuid`, or `$queryParameter` as labels in Prometheus/StatsD metrics.
   - *Consequence:* Causes combinatorial time-series explosion in TSDBs, leading to catastrophic out-of-memory crashes and monitoring outages.
7. **Logging Plaintext Secrets or PII:**
   - *Violation:* Calling `Log::info('Login attempt', $request->all())` containing passwords, credit card numbers, or authorization tokens.
   - *Consequence:* Serious GDPR / PCI-DSS compliance violations and catastrophic credential exposure in log aggregation platforms.
8. **Alerting on Transient Anomalies Without Aggregation:**
   - *Violation:* Configuring a PagerDuty page for every single 500 error or network retry.
   - *Consequence:* Severe alert fatigue leading to engineers ignoring or muting alerts during genuine high-severity outages.

---

## 8. Incident Management, Alerting, and Recovery

### 8.1 Incident Severity Classification Matrix

| Severity Level | Definition | Impact Scope | Response SLA | Escalation / Notification Channel |
| :--- | :--- | :--- | :--- | :--- |
| **P1 - Critical** | Complete service outage, data corruption risk, or core user journey completely blocked across tenants. | $> 10\%$ of all tenant traffic failing or core payment processing down. | Immediate page; $< 15\text{ min}$ response. | PagerDuty voice call, dedicated war room, executive notification. |
| **P2 - Major** | Degraded system performance, critical tenant down, or non-critical subsystem failure (e.g., reports queue stalled). | Significant subset of tenants impacted; workaround available. | $< 1\text{ hour}$ response. | On-call Slack page, engineering team lead notified. |
| **P3 - Minor** | Localized defect, non-blocking bug, or intermittent transient failure on non-critical endpoints. | Isolated users or low-priority background workers. | $< 1\text{ business day}$. | Standard Jira ticket, team backlog review. |
| **P4 - Info** | Telemetry anomalies, low-priority warnings, or minor cosmetic inconsistencies. | Zero customer business impact. | Scheduled sprint cycle. | Team dashboard, non-paging log monitor. |

### 8.2 Root-Cause Forensics Workflow
When an incident is flagged via alert:
1. **Identify the Core Correlation ID:** Extract `correlation_id` from the alert payload or customer support ticket.
2. **Execute Unified Distributed Query:** Query centralized logging sink with `correlation_id = '...'` to retrieve the entire execution timeline across HTTP ingress, database queries, Redis queue dispatches, and worker execution.
3. **Inspect Causal Spans:** Use the `trace_id` to inspect span waterfalls and identify where execution stalled or threw an unhandled exception.
4. **Formulate Containment:** If downstream failure (e.g., third-party API outage), engage circuit breaker; if poison queue message, quarantine to dead-letter queue.
5. **Verify Recovery:** Validate that readiness probes (`/health/ready`) return `200 OK` and error budget burn rate drops to zero.
6. **Blameless Post-Mortem:** Complete post-incident report within 48 hours detailing root cause, timeline, contributing factors, and permanent preventive actions.

---

## 9. Testing and Verification

### 9.1 Sensitive Data Redaction Verification
```php
public function test_sensitive_data_processor_scrubs_passwords_tokens_and_exceptions(): void
{
    $processor = new SensitiveDataScrubberProcessor();
    $record = new LogRecord(
        datetime: new \DateTimeImmutable(),
        channel: 'testing',
        level: \Monolog\Level::Info,
        message: 'Authorization header: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
        context: [
            'username' => 'testuser@core.test',
            'password' => 'SuperSecretP@ssw0rd',
            'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
            'nested' => [
                'card_number' => '4111 2222 3333 4444',
                'safe_field' => 'visible_value',
            ],
            'exception' => new \RuntimeException('Connection failed with token: Bearer secret_token_value'),
        ]
    );

    $scrubbed = $processor($record);

    $this->assertSame('[REDACTED_PATTERN]', $scrubbed->message);
    $this->assertSame('[REDACTED]', $scrubbed->context['password']);
    $this->assertSame('[REDACTED_PATTERN]', $scrubbed->context['token']);
    $this->assertSame('[REDACTED_PAN]', $scrubbed->context['nested']['card_number']);
    $this->assertSame('visible_value', $scrubbed->context['nested']['safe_field']);
    $this->assertIsArray($scrubbed->context['exception']);
    $this->assertSame('[REDACTED_PATTERN]', $scrubbed->context['exception']['message']);
}
```

### 9.2 Domain and Integration Exception Failure Path Tests
```php
public function test_domain_exception_carries_http_status_and_error_code(): void
{
    $exception = new DomainException(
        message: 'Insufficient balance to complete capture.',
        errorCode: 'INSUFFICIENT_FUNDS',
        statusCode: 422,
        title: 'Unprocessable Transaction',
        context: ['account_id' => 'acc_123']
    );

    $this->assertSame(422, $exception->getStatusCode());
    $this->assertSame('INSUFFICIENT_FUNDS', $exception->getErrorCode());
    $this->assertSame('Unprocessable Transaction', $exception->getTitle());
    $this->assertSame(['account_id' => 'acc_123'], $exception->getContext());
}

public function test_integration_exception_distinguishes_retryable_from_permanent_failures(): void
{
    $retryable = new IntegrationException(
        message: 'Stripe gateway timed out.',
        isRetryable: true,
        errorCode: 'GATEWAY_TIMEOUT',
        statusCode: 504
    );

    $permanent = new IntegrationException(
        message: 'Invalid API secret key.',
        isRetryable: false,
        errorCode: 'INVALID_CREDENTIALS',
        statusCode: 401
    );

    $this->assertTrue($retryable->isRetryable());
    $this->assertFalse($permanent->isRetryable());
}
```

### 9.3 Correlation ID and Production Error Response Shielding
```php
public function test_production_error_response_contains_correlation_id_and_hides_stack_trace(): void
{
    config(['app.debug' => false]);

    $response = $this->getJson('/api/v1/trigger-test-exception');

    $response->assertStatus(500)
        ->assertHeader('X-Correlation-ID')
        ->assertJsonStructure([
            'errors' => [
                [
                    'status',
                    'code',
                    'title',
                    'detail',
                    'meta' => ['correlation_id'],
                ],
            ],
        ]);

    $this->assertStringNotContainsString('trigger-test-exception', (string) $response->getContent());
    $this->assertStringNotContainsString('Stack trace:', (string) $response->getContent());
}
```

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Detection Signal | Engineering Prevention Rule |
| :--- | :--- | :--- | :--- |
| **Log Disk Exhaustion** | Unbounded verbose debug logging in production under high load. | Host disk usage alerts ($> 90\%$), server freezes. | Enforce structured JSON logging with log rotation, rate limiting, and 14-day retention. |
| **TSDB Memory Crash (OOM)** | Storing user IDs, order UUIDs, or search terms in metric label tags. | Prometheus / Datadog agent crashes with OOM. | Lint metric label definitions; strictly limit metric tags to bounded enumerations. |
| **Silently Corrupted Data** | Empty catch block swallows database exception during transaction. | Inconsistent financial balances discovered days later. | Prohibit empty catch blocks; always re-throw or log at `ERROR` level with full context. |
| **Duplicate Alert Spikes** | Catching an exception, logging it, and re-throwing to framework handler. | Double log entries for single failure; false P2 alert trigger. | Enforce single-source logging; log and handle OR let framework reporter log. |
| **Worker Capacity Starvation** | Retrying permanent 4xx domain or validation failures in queue jobs. | Queue backlog accumulates; dead-letter queue fills with retry exhaustion. | Terminate non-retryable failures immediately via `$this->fail($e)`. |
| **Database Pool Exhaustion** | Calling slow external HTTP API inside open relational transaction. | MySQL thread pool maxes out; lock wait timeout errors. | Perform external HTTP requests outside database transactions. |
| **Data Leak in Error Tracker** | Exception handler passes unscrubbed request payload to Sentry / Bugsnag. | Customer credit card numbers or passwords visible in dashboard. | Enforce `SensitiveDataScrubberProcessor` before dispatching to external error trackers. |
| **Internal SQL Leak in Health Probe** | Returning `$e->getMessage()` in `/health/ready` probe on database failure. | Sensitive table names or database credentials disclosed to probe scanners. | Return sanitized error string in production; log full exception internally. |

---

## 11. Acceptance Criteria

Before approving any observability deliverable, verify:
- [ ] All custom exceptions inherit from `AppException` and preserve `$previous` causes.
- [ ] Database transactions roll back cleanly on exception before handling or rethrowing.
- [ ] Background queue jobs differentiate retryable vs. non-retryable failures and fail immediately on permanent errors.
- [ ] All application logs emit structured JSON adhering to the Universal Core Telemetry Schema.
- [ ] Correlation ID (`X-Correlation-ID`) and W3C `traceparent` propagate across HTTP, queues, and outbound calls.
- [ ] Client error responses in production (`APP_DEBUG=false`) conceal internal details and provide `correlation_id`.
- [ ] The `SensitiveDataScrubberProcessor` is active and verified by tests; zero plain-text secrets in logs.
- [ ] Query listener is configured to detect and warn on database queries exceeding $100\text{ ms}$.
- [ ] Metric label dimensions contain zero high-cardinality values (no user IDs, UUIDs, or raw query parameters).
- [ ] Both `/health/live` and `/health/ready` endpoints are implemented and conceal internal exception details in production.
- [ ] Alerts are configured based on SLO error budget burn rates and severity levels (P1–P4).
- [ ] Multi-tenant operations tag all telemetry with `tenant_id` without exposing cross-tenant data.

---

## 12. Cross-Skill Integration, References, and Related Skills

### 12.1 Cross-Skill Integration Architecture
This skill operates as a unified triad with **SKILL-TENANT-001** and **SKILL-QUEUE-001**:
- **With SKILL-TENANT-001 (Multi-Tenancy):** Observability ingests `tenant_id` into `Log::withContext()`. Every log line, metric, and trace span carries tenant identity for multi-tenant telemetry filtering, while scrubbing ensures tenant privacy is rigorously maintained.
- **With SKILL-QUEUE-001 (Queue Architecture):** Observability instruments queued job lifecycles (`JobQueued`, `JobProcessing`, `JobProcessed`, `JobFailed`). Correlation IDs are serialized into job payloads and restored on worker pickup, enabling end-to-end distributed tracing across asynchronous boundaries.

### 12.2 Seven Unified End-to-End Scenarios Across the Triad
The three skills (`SKILL-TENANT-001`, `SKILL-QUEUE-001`, `SKILL-ERR-001`) collaborate across seven mission-critical enterprise scenarios:
1. **Tenant Submits Background Job:**
   - *Multi-Tenancy:* Authenticated HTTP request resolves tenant identity into `TenantContext`.
   - *Queue:* Job is dispatched with scalar `$tenantId`, transactional synchronization (`$afterCommit = true`, `'after_commit' => true` connection config, or `dispatch()->afterCommit()`), and lightweight arguments.
   - *Observability:* Inbound `CorrelationAndTraceMiddleware` injects `correlation_id` and W3C `traceparent` into the job payload metadata.
2. **Job Executes Under Correct Tenant Context:**
   - *Queue:* Worker picks up job from named queue (`high`).
   - *Multi-Tenancy:* `SetTenantContextMiddleware` initializes `TenantContext` using `$job->tenantId` and verifies tenant is active.
   - *Observability:* Worker extracts `correlation_id` and binds it to `Log::withContext(['tenant_id' => $tenantId, 'correlation_id' => $correlationId])`.
3. **Job Fails and Retries Safely:**
   - *Queue:* Downstream network timeout triggers stepped `backoff()` with randomized jitter; `$tries` count increments.
   - *Multi-Tenancy:* Context is safely reset in middleware `finally` block to prevent lingering state.
   - *Observability:* Failure is logged with structured exception context, tenant ID, attempt number, and incremented error metrics.
4. **Duplicate Execution is Safely Handled (Business-Operation Idempotency):**
   - *Queue:* Redis at-least-once re-delivery or network retry pickup occurs; two-phase idempotency ledger or atomic lock (`Cache::lock`) detects existing finalized record.
   - *Multi-Tenancy:* Query verifies tenant ownership of existing record before returning cached result.
   - *Observability:* System logs an informational message (`"Idempotency guard: job already finalized"`) without incrementing error budgets.
5. **Redis Becomes Temporarily Unavailable:**
   - *Queue:* Worker connection throws `RedisException`; supervised worker catches connection drop, pauses with exponential backoff, and enters reconnect loop.
   - *Observability:* Readiness probe (`/health/ready`) immediately returns `503 Service Unavailable`, triggering P1 alert for on-call rotation.
   - *Multi-Tenancy:* Incoming tenant requests fail fast or degrade safely without cross-tenant queue leakage.
6. **Incident Detection and Tracing:**
   - *Observability:* Engineers query centralized logs using `correlation_id`, retrieving the entire distributed timeline across HTTP ingress, Redis queue wait time, database execution, and error traces.
   - *Multi-Tenancy:* Telemetry isolates tenant views so Tenant A never views telemetry belonging to Tenant B.
   - *Queue:* Horizon / queue depth metrics indicate backlog recovery rate.
7. **Recovery Verification Without Cross-Tenant Exposure:**
   - *Observability:* Health probe (`/health/ready`) returns `200 OK`; SLO burn rate returns to zero.
   - *Queue:* Dead-letter jobs in `failed_jobs` table are safely retried per tenant (`php artisan queue:retry`).
   - *Multi-Tenancy:* Each retried job strictly restores its respective tenant context, verifying zero cross-tenant contamination.

### 12.3 Authoritative Technical References
- **Universal Core Skills:**
  - `SKILL-TENANT-001` (Multi-Tenant Architecture and Isolation Standards)
  - `SKILL-QUEUE-001` (Queue and Asynchronous Architecture Standards)
  - `SKILL-API-001` (RESTful and JSON:API Engineering Standards)
  - `SKILL-PERF-001` (Performance Profiling and Optimization Standards)
- **External Standards:**
  - W3C TraceContext Recommendation: Distributed Tracing Header Specifications
  - Google Site Reliability Engineering (SRE) Handbook: Monitoring Distributed Systems & Service Level Objectives
  - The OpenTelemetry Specification: Logging, Tracing, and Metrics Data Models

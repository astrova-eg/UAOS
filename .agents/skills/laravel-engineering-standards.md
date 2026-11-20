# Shared Engineering Skill: Laravel Engineering Standards

**Skill ID:** SKILL-LARAVEL-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Laravel Engineering Standards** skill establishes the authoritative enterprise standard for developing scalable, secure, and maintainable Laravel applications within Universal Core.

Its primary purpose is to ensure full compatibility with modern **Laravel 11+** architectures, enforce strict separation of responsibilities, mandate safe database and concurrency controls, guarantee idempotent asynchronous queue operations, and standardize automated testing. It prevents common architectural pitfalls—such as fat controllers, N+1 query cascades, bypassed authorizations, and race conditions—ensuring that every Laravel component meets high-assurance enterprise criteria.

---

## 2. Scope and Applicability

This standard applies to all Laravel applications, modules, packages, and CLI commands developed under Universal Core.

- **Framework Compatibility:** Laravel 11.x and future forward-compatible releases.
- **Architectural Scope:** Application bootstrap configuration (`bootstrap/app.php`), Routing, Middlewares, Controllers, Form Requests, DTOs, Actions, Domain Services, Eloquent Models, Relationships, Query Scopes, Database Migrations, Events, Listeners, Queued Jobs, Notifications, Policies, and API Resources.
- **Framework Integrity:** Strictly prohibited from altering Laravel framework core files (`vendor/laravel/*`) or modifying vendor distributions directly. All customizations must occur via standard framework extension points (Service Providers, Middleware, Macros, Event Listeners).

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified expertise in:
1. **Laravel 11 Application Architecture:** Streamlined application configuration in `bootstrap/app.php`, lean service providers, and minimalist directory conventions.
2. **Layered Separation of Concerns:** Single-Action Controllers, Action Pattern, Form Request validation, Domain Transfer Objects (DTOs), and Policy-driven authorization.
3. **Advanced Eloquent ORM & Query Tuning:** Strict model hydration, preventing lazy loading in non-production, eager loading techniques (`with`, `loadMissing`), chunking, and subquery optimization.
4. **Concurrency and Transaction Governance:** Atomic transactions (`DB::transaction`), pessimistic row-locking (`lockForUpdate`), optimistic locking, and deadlock retry strategies.
5. **Resilient Asynchronous Architectures:** Queue workers, `ShouldQueue`, `ShouldBeUnique`, job retries, backoff algorithms, dead-letter queues, and idempotency guarantees.
6. **Enterprise Test Automation:** HTTP test assertions, database seeding, transaction rollback isolation (`LazilyRefreshDatabase`), and external HTTP client faking (`Http::fake()`).

---

## 4. Core Engineering Principles

1. **Thin Presentation, Rich Domain Orchestration:** Controllers must strictly orchestrate HTTP request/response lifecycles; they must never contain business logic, complex database queries, or authorization algorithms.
2. **Explicit Contracts and Dependency Injection:** Leverage the Laravel Service Container via explicit type-hinted constructor injection. Avoid calling static facades inside core domain logic to ensure testability and loose coupling.
3. **Defensive Authorization by Default:** Every endpoint modifying or exposing state must be safeguarded by explicit Policy or Gate checks before execution begins.
4. **Guaranteed Data Consistency:** All multi-step relational mutations must execute within explicit database transactions with deadlock retry resilience.
5. **Zero Implementation Debt:** All production code must be fully implemented, verified with automated tests, and adhere to universal file size limits.

---

## 5. Mandatory Standards

### 5.1 Universal Code Quality Standards

#### 5.1.1 File Size and Single Responsibility Limit
- **Maximum 300 lines per code file:** No PHP source file (Controllers, Actions, Models, FormRequests, Jobs, Migrations) may exceed 300 physical lines by default.
- **Exception Governance:** Any exception above 300 lines requires:
  - Specific technical justification.
  - Documented explanation of why decomposition is unsuitable.
  - Explicit prior human user approval.
  - Recorded exception scope and affected file.
  - No agent, automated process, validator, or department manager may grant itself an exception.
  - Approval for one file does not establish a general exception for other files.
  - Unauthorized oversized files are treated as non-compliant.
- **Decomposition Guidelines:** When a controller or service approaches 300 lines, decompose it into Single-Action Invokable Controllers (`__invoke`), dedicated Action classes, or specialized Service components.
- **Preservation of Quality:** Never compress code or remove necessary comments to satisfy the line limit.

#### 5.1.2 Code Readability and Semantic Expressiveness
- **Clear Intent:** Code must be understandable without guesswork. Use descriptive method names that reflect domain language (e.g., `releasePendingHold()` instead of `doRelease()`).
- **Elimination of Magic Values:** Magic numbers, unanchored strings, and arbitrary status flags are strictly forbidden. Use PHP 8.3 Backed Enums or Model constants.
- **Consistent Code Formatting:** Conform strictly to Laravel Pint / PSR-12 coding conventions.

#### 5.1.3 Implementation Integrity
- **No Mock or Placeholder Logic in Production:** Stubbed returns (`return true;`, `return response()->json([])`) masquerading as completed logic are strictly prohibited.
- **No Hardcoded Demonstration Data:** Real database models and dynamic domain services must be queried.
- **No Swallowed Exceptions:** Unhandled exceptions must not be caught and discarded silently. Every caught exception must be handled or logged with context.

### 5.2 Application Layer Architecture

```
HTTP Request ──► Route ──► Middleware (Auth/Throttle)
                             │
                             ▼
                    FormRequest (Validation & Authorization)
                             │
                             ▼
                    Controller (Thin Orchestrator: __invoke)
                             │
                             ▼
                    Action / Domain Service (Business Invariants)
                             │
                ┌────────────┴────────────┐
                ▼                         ▼
         Eloquent Models             Events / Jobs
     (Atomic DB Transaction)     (Idempotent Async Worker)
```

1. **Controllers:** Keep controllers thin. Prefer single-action controllers (`__invoke`) for complex endpoints. A controller method must not exceed 25 lines of execution code.
2. **Validation:** All incoming request payloads must be validated via dedicated `Illuminate\Foundation\Http\FormRequest` classes. Controllers must never invoke `$request->validate()` inline.
3. **Authorization:** Every FormRequest must enforce authorization in its `authorize()` method using policies:
   ```php
   public function authorize(): bool
   {
       return $this->user()?->can('update', $this->route('invoice')) ?? false;
   }
   ```
4. **API Responses:** Return dedicated `JsonResource` classes for API endpoints. Never return raw Eloquent models directly to clients to prevent internal attribute leakage.

### 5.3 Eloquent and Query Integrity
1. **Prevent Lazy Loading:** Disable lazy loading in local and testing environments to catch N+1 query cascades before deployment:
   ```php
   // In AppServiceProvider or bootstrap
   Model::preventLazyLoading(! app()->isProduction());
   Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
   ```
2. **Mass Assignment Protection:** Models must explicitly declare `$fillable` attributes. Setting `$guarded = []` or `$guarded = ['id']` is strictly prohibited.
3. **Explicit Columns in Queries:** When querying large datasets, explicitly select required columns (`select(['id', 'tenant_id', 'status'])`) rather than `select *`.

### 5.4 Database Transactions and Concurrency
1. **Atomic Transactions:** Any workflow mutating more than one database record or table must be wrapped in `DB::transaction()`:
   ```php
   DB::transaction(function () use ($action) {
       // Atomic execution
   }, attempts: 3);
   ```
2. **Pessimistic Locking:** Any modification to financial accounts, wallet balances, or inventory stocks must acquire a row-level pessimistic lock (`lockForUpdate()`) inside an active transaction.

### 5.5 Queues, Retries, and Idempotency
1. **Queue Configuration:** All asynchronous jobs implementing `ShouldQueue` must explicitly define:
   - `public int $tries = 3;`
   - `public int $timeout = 60;`
   - `public array $backoff = [5, 30, 120];`
2. **Job Idempotency:** Jobs must be safe to execute multiple times without unintended side effects. Use `ShouldBeUnique` where concurrent duplicate execution could corrupt state:
   ```php
   class ProcessBillingCycleJob implements ShouldQueue, ShouldBeUnique
   {
       public int $uniqueFor = 300;
       public function uniqueId(): string
       {
           return (string) $this->tenantId;
       }
   }
   ```
3. **Failure Handling:** Implement the `failed(\Throwable $exception): void` method on queued jobs to handle graceful degradation, alerting, or compensation.

### 5.6 Secure File Uploads and Storage
1. **Storage Abstraction:** Never write directly to `public/` or `storage/` paths using native PHP functions (`move_uploaded_file`). Use Laravel's `Storage` facade with configured disks.
2. **Upload Validation:** Validate uploaded files strictly:
   ```php
   'document' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'],
   ```
3. **Private Disks for Sensitive Data:** Files containing PII or proprietary tenant data must be stored on non-public disks and served via temporary signed URLs or authorized streaming responses (`Storage::download()`).

---

## 6. Implementation Procedures and Practices

### 6.1 Invokable Controller with FormRequest and Action Pattern
The following implementation illustrates the correct enterprise separation of responsibilities:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Domain\Billing\Actions\SettleInvoiceAction;
use App\Domain\Billing\DTOs\SettleInvoiceDTO;
use App\Http\Requests\Billing\SettleInvoiceRequest;
use App\Http\Resources\Billing\InvoiceResource;
use Illuminate\Http\JsonResponse;

final class SettleInvoiceController
{
    public function __invoke(
        SettleInvoiceRequest $request,
        SettleInvoiceAction $action
    ): JsonResponse {
        $dto = SettleInvoiceDTO::fromRequest($request);

        $invoice = $action->execute($dto);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(200);
    }
}
```

### 6.2 Resilient Action with Transaction and Concurrency Lock

```php
<?php

declare(strict_types=1);

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\DTOs\SettleInvoiceDTO;
use App\Domain\Billing\Events\InvoiceSettledEvent;
use App\Domain\Billing\Exceptions\InvoiceAlreadySettledException;
use App\Domain\Billing\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Psr\EventDispatcher\EventDispatcherInterface;

final class SettleInvoiceAction
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly EventDispatcherInterface $dispatcher
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

            if ($invoice->isPaid()) {
                throw new InvoiceAlreadySettledException("Invoice {$invoice->id} is already settled.");
            }

            $paymentResult = $this->gateway->charge(
                amount: $invoice->total_amount,
                currency: $invoice->currency,
                paymentMethodId: $dto->paymentMethodId
            );

            $invoice->update([
                'status' => 'paid',
                'transaction_reference' => $paymentResult->transactionId,
                'paid_at' => now(),
            ]);

            $this->dispatcher->dispatch(new InvoiceSettledEvent($invoice->id));

            return $invoice;
        }, attempts: 3);
    }
}
```

---

## 7. Prohibited Practices

1. **PROHIBITED:** Writing business logic, calculations, or direct third-party API calls inside Controllers.
2. **PROHIBITED:** Setting `$guarded = []` on any Eloquent model.
3. **PROHIBITED:** Triggering unmitigated N+1 queries by accessing lazy-loaded relations inside loops.
4. **PROHIBITED:** Mutating balances or financial state outside an atomic `DB::transaction()` with pessimistic locking.
5. **PROHIBITED:** Modifying any file inside the `vendor/` directory or framework core.
6. **PROHIBITED:** Swallowing exceptions without logging or rethrowing.
7. **PROHIBITED:** Bypassing FormRequest authorization (`authorize() => true` without permission validation).
8. **PROHIBITED:** Exceeding the 300-line code file limit without prior explicit human user approval and documented technical justification.
9. **PROHIBITED:** Returning raw Eloquent models directly from API routes without a `JsonResource`.
10. **PROHIBITED:** Using untyped Queued Jobs without explicit `$tries`, `$timeout`, and `$backoff` definitions.

---

## 8. Security and Reliability Requirements

1. **Multi-Tenant Scoping:** All Eloquent queries within multi-tenant contexts must strictly enforce tenant constraints (via global scopes or explicit `where('tenant_id', ...)` clauses).
2. **Safe Model Binding:** Route model bindings must always scope children to parents to prevent Insecure Direct Object References (IDOR):
   ```php
   Route::get('/tenants/{tenant}/invoices/{invoice:ulid}', ...)->scopeBindings();
   ```
3. **Signed URLs for Export/Download:** File downloads or asynchronous exports must require signed, temporary URLs (`URL::temporarySignedRoute()`).
4. **Cache Stampede Prevention:** Never update shared cache keys without atomic locks:
   ```php
   $lock = Cache::lock('processing-daily-stats', 10);
   if ($lock->get()) {
       try {
           // Compute and cache
       } finally {
           $lock->release();
       }
   }
   ```

---

## 9. Testing and Verification

### 9.1 Automated Test Pyramid
- **Unit Tests:** Fast, isolated tests for Domain Actions, DTOs, and Value Objects without hitting the database.
- **Feature Tests:** HTTP integration tests verifying routing, FormRequest validation rules, authorization policies, and status codes.
- **Database Isolation:** Use `Illuminate\Foundation\Testing\LazilyRefreshDatabase` to ensure transactions are rolled back cleanly after each test without penalizing non-database tests.

### 9.2 Verification Commands
```bash
# Static analysis check
vendor/bin/phpstan analyse --level=8

# Code style compliance
vendor/bin/pint --test

# Test suite execution with zero failures
php artisan test --parallel
```

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **N+1 Database Query Cascade** | Looping over Eloquent collection and accessing relation properties. | Enforce `Model::preventLazyLoading(!app()->isProduction())` in local/testing. |
| **Race Condition on Balances** | Concurrent requests reading old balance before write completes. | Wrap in `DB::transaction()` and apply `lockForUpdate()`. |
| **Silent Job Failure in Queue** | Unhandled exception with infinite retry loop. | Set explicit `$tries = 3`, `$backoff`, and implement `failed()` method. |
| **IDOR Data Leakage** | Guessable model ID accessed without tenant verification. | Use ULIDs/UUIDs and enforce `scopeBindings()` and Policy checks. |
| **Mass Assignment Security Hole** | Setting `$guarded = []` and passing `$request->all()` into `Model::create()`. | Strictly declare explicit `$fillable` properties on all models. |

---

## 11. Acceptance Criteria

Before completing any Laravel engineering deliverable, verify:
- [ ] File has `declare(strict_types=1);` and does not exceed 300 lines.
- [ ] No business logic resides in Controllers; thin orchestrators or single-action classes are used.
- [ ] All request payloads are validated and authorized through dedicated `FormRequest` classes.
- [ ] APIs return `JsonResource` envelopes; no raw models are exposed.
- [ ] All multi-step relational mutations are encapsulated in `DB::transaction()` with retry logic.
- [ ] Financial, balance, or inventory modifications use `lockForUpdate()`.
- [ ] Models explicitly define `$fillable`; `$guarded = []` is absent.
- [ ] Queued jobs specify `$tries`, `$timeout`, `$backoff`, and idempotency mechanisms.
- [ ] Automated tests cover normal execution, validation rejections, and unauthorized access attempts.
- [ ] Zero modifications made to Laravel core or vendor files.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-QUEUE-001` (Queue and Asynchronous Architecture Standards)
  - `SKILL-TEST-001` (Automated Testing and Verification Standards)
- **External Specifications:**
  - Laravel 11.x Official Documentation
  - OWASP Top 10 Application Security Risks

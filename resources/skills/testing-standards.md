# Shared Engineering Skill: Automated Testing and Verification Standards

**Skill ID:** SKILL-TEST-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Automated Testing and Verification Standards** skill establishes the authoritative enterprise engineering standard for test design, automated quality assurance, regression defense, and mathematical verification across Universal Core.

Its primary purpose is to transform automated testing from an afterthought of superficial assertions into an unshakeable, deterministic verification system. Testing is the primary vehicle for proving system correctness, architectural compliance, security boundaries, and concurrency safety.

This skill equips autonomous agents and software engineers to:
- Deeply understand testing theory, boundary value mechanics, finite-state transitions, and mutation analysis.
- Strategically structure test portfolios using the Testing Pyramid (Unit, Feature/Integration, Contract, Concurrency, and End-to-End).
- Design high-fidelity tests using PHPUnit 11 and Pest v2/v3 in **Laravel 11+**.
- Isolate system boundaries with precision, distinguishing framework fakes, real doubles, and external boundary mocks while prohibiting the mocking of internal domain models.
- Guarantee hermetic, deterministic test execution with zero flaky tests, frozen clocks, and database transaction rollbacks (`LazilyRefreshDatabase`).
- Rigorously test advanced failure modes: race conditions, distributed deadlocks, multi-tenant data leaks, partial transaction aborts, and external service outages.
- Measure and enforce high mutation scores (Infection MSI $\ge 80\%$) to eliminate false confidence derived from hollow code coverage.
- Conduct systematic defect reproduction and root-cause analysis by writing failing regression suites prior to applying code fixes.

---

## 2. Scope and Applicability

This standard applies universally to all automated test suites, testing harnesses, CI/CD verification pipelines, and quality gating procedures across Universal Core.

- **Runtimes & Frameworks:** PHP 8.3+, Laravel 11.x, PHPUnit 11+, Pest v2/v3, and Infection PHP.
- **Verification Levels:**
  - **Unit Testing:** Pure domain logic, Value Objects, Actions, DTOs, and algorithmic calculations executed strictly in memory without database or network I/O.
  - **Feature & Integration Testing:** HTTP request/response lifecycles, Form Request validations, Authorization Policies, Middleware pipelines, Eloquent persistence, and Queued Job dispatching.
  - **Contract Testing:** OpenAPI 3.1.0 schema compliance, JSON:API response envelope verification, and consumer-driven contracts.
  - **Security & Multi-Tenant Testing:** IDOR/BOLA vulnerability testing, tenant boundary isolation, cross-project data leakage prevention, and SQLi/XSS adversarial tests.
  - **Concurrency & Transaction Testing:** Row-level locking (`lockForUpdate`), atomic cache locks, and deadlock recovery verification.
  - **Resilience Testing:** External API failure injection (`Http::fakeSequence`), network timeouts, and partial database rollback verification.
- **Quality Gates:** PHPStan/Larastan Level 8 / Max, Laravel Pint (PSR-12), Composer security audits, and Mutation Testing (MSI).

---

## 3. Required Knowledge and Competencies

Engineers and agents implementing this skill must demonstrate verified expertise across eight core domains:

1. **Test Design Intelligence:** Equivalence partitioning, boundary-value analysis (BVA), decision tables, and finite-state machine (FSM) transition modeling.
2. **Mutation Testing Theory:** Abstract Syntax Tree (AST) mutation, mutant survival mechanics, killed vs. escaped mutants, and Mutation Score Indicator (MSI) calculations via Infection PHP.
3. **Laravel Test Harness:** Test lifecycle, `TestCase`, `LazilyRefreshDatabase` vs. `RefreshDatabase`, model factories, states, sequences, and in-memory SQLite vs. real PostgreSQL/MySQL engines.
4. **Mocking & Boundary Architecture:** Precise differentiation of Stubs, Mocks, Spies, and Framework Fakes (`Http::fake`, `Queue::fake`, `Event::fake`, `Storage::fake`). Prohibitions against mocking database query builders.
5. **Deterministic Engineering:** Frozen temporal environments (`Carbon::setTestNow()`), seed-controlled pseudo-randomness, and stateless execution immune to test ordering.
6. **Concurrency & Race Condition Verification:** Parallel execution simulations, pessimistic lock validation, and atomic transaction boundary verification.
7. **Adversarial & Negative Testing:** Fuzzing input structures, malformed payloads, injection probes, and privilege escalation simulations.
8. **Root-Cause Analysis:** The 5-Whys methodology, automated regression suite design, and `git bisect` automated test execution.

---

## 4. Core Engineering Principles

### 4.1 Ten-Step Professional Engineering Decision Framework

When designing, refactoring, or evaluating tests, agents must follow this ten-step decision framework:

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
│ 5. Evaluate Test Level     ──►  6. Select Design Technique (BVA/FSM)  │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 7. Define Boundary Mocks   ──►  8. Implement Hermetic Test Cases       │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ 9. Verify Determinism & MSI ──► 10. Record Quality & Mutation Evidence │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Understand Requirement:** Identify the exact specification, business invariant, or defect report to be verified.
2. **Identify Constraints & Assumptions:** Identify database engines, third-party integrations, latency budgets, and asynchronous dependencies.
3. **Analyze System Context:** Determine architectural boundaries (Controller, Action, Domain Model, Gateway, Queue).
4. **Identify Risks & Failure Modes:** Map potential edge cases: boundary values ($0$, $-1$, $\text{INT\_MAX}$), null states, race conditions, unauthorized tenants.
5. **Evaluate Test Level:** Choose the lowest effective level in the Testing Pyramid (Unit vs. Feature vs. Integration vs. Contract).
6. **Select Design Technique:** Apply Equivalence Partitioning, Boundary Value Analysis, State-Transition testing, or Decision Tables.
7. **Define Boundary Mocks:** Keep doubles restricted to external I/O boundaries (payment gateway, SMS service). Never mock internal database entities.
8. **Implement Hermetic Test Cases:** Write clean, isolated tests adhering to AAA (Arrange-Act-Assert) and file size limits ($\le 300$ lines).
9. **Verify Determinism & MSI:** Run tests with `--order-by=random` across multiple workers. Run Infection to confirm mutant eradication.
10. **Record Quality & Mutation Evidence:** Document execution logs, query logs, assertion counts, and mutation score indicators.

### 4.2 Test Portfolio and Pyramid Trade-Offs

```
       /\
      /  \      End-to-End Tests (Critical Journeys Only: 5%)
     /----\
    /      \    Contract & Concurrency Tests (15%)
   /--------\
  /          \  Feature / HTTP Integration Tests (50%)
 /------------\
/              \ Unit Tests (Domain Invariants & DTOs: 30%)
----------------
```

#### 4.2.1 Unit Tests vs. Feature Tests
- **Unit Tests:**
  - *When to use:* Pure business logic, Value Objects, math algorithms, parsing, state transitions without database access.
  - *Execution:* Sub-millisecond execution time, zero I/O.
- **Feature Tests:**
  - *When to use:* HTTP routes, Form Requests, Policies, Middleware, Controller orchestration, database transactions, and event/job dispatching.
  - *Execution:* Fast database transaction rollbacks via `LazilyRefreshDatabase`.

#### 4.2.2 Code Coverage vs. Mutation Score Indicator (MSI)
- **The Code Coverage Trap:** A test suite can achieve 100% line coverage with zero assertions by merely executing lines of code without validating outcomes. Line coverage measures execution, not verification.
- **Mutation Testing (Infection):** Introduces deliberate faults (mutants) into the source code (e.g., swapping `>` for `>=`, changing `true` to `false`, removing function calls). If the test suite passes despite the fault, the mutant *survived* (test failure).
- **Mandatory Quality Metric:** High-assurance modules in Universal Core must achieve a **Mutation Score Indicator (MSI) $\ge 80\%$**.

---

## 5. Mandatory Standards

### 5.1 Universal Engineering Governance
1. **File Size Enforcement:** Test files, factories, and test helpers must not exceed 300 physical lines. Decompose large test classes into focused, single-concern suites (e.g., `SettleInvoiceValidationTest`, `SettleInvoiceAuthorizationTest`, `SettleInvoiceExecutionTest`).
2. **Strict Typing:** All test files must declare `declare(strict_types=1);` and explicitly type helper return values and closures.
3. **Zero Implementation Debt in Tests:**
   - Empty test methods (`test_example() { $this->assertTrue(true); }`) are strictly prohibited.
   - Commented-out assertions or skipped tests without formal tracking are forbidden.
   - Tests must assert exact state changes, not merely HTTP status codes.

### 5.2 Deterministic Testing Mandates
1. **Temporal Freezing:** Any test relying on timestamps, dates, or expirations must freeze time via `Carbon::setTestNow()`:
   ```php
   $now = Carbon::create(2026, 11, 14, 12, 0, 0, 'UTC');
   Carbon::setTestNow($now);
   // Execute operations
   Carbon::setTestNow(); // Clean teardown
   ```
2. **Database Hermeticity:** Tests modifying database state must use `Illuminate\Foundation\Testing\LazilyRefreshDatabase`. This ensures transactions roll back after each test without penalizing non-database tests.
3. **Execution Order Independence:** The test suite must pass consistently when executed in random order:
   ```bash
   php artisan test --order-by=random
   ```
4. **Zero Shared Static State:** Tests must never write to shared global variables, static model properties, or unreset application singletons.

### 5.3 Mocking Boundaries and Double Architecture

| Target Component | Permitted Double Type | Prohibited Double Type | Rationale |
| :--- | :--- | :--- | :--- |
| **External Third-Party APIs** | `Http::fake()` or Interface Mock | Real HTTP connections | External networks are slow, non-deterministic, and incur financial costs. |
| **Queues and Jobs** | `Queue::fake()` / `Bus::fake()` | Mockery mock of Queue | Framework fakes provide native assertions and lifecycle validation. |
| **Events and Notifications** | `Event::fake()` / `Notification::fake()` | Mockery mock of Dispatcher | Native fakes assert dispatched payloads without breaking container wiring. |
| **Filesystem / Storage** | `Storage::fake('s3')` | Native filesystem writes | Prevents disk pollution and guarantees clean memory execution. |
| **Eloquent Models & Queries** | **Real Database Execution** | Mockery mock of Model/QueryBuilder | Mocking Query Builder creates brittle tests that verify ORM implementation details instead of SQL correctness. |

---

## 6. Implementation Procedures and Practices

### 6.1 High-Assurance Feature Test (Authentication, Validation, Authorization & DB State)

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Enums\InvoiceStatus;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Users\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class SettleInvoiceFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 14, 12, 0, 0, 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_authorized_user_can_settle_pending_invoice(): void
    {
        $user = User::factory()->create(['tenant_id' => 'tenant-100']);
        $invoice = Invoice::factory()->create([
            'tenant_id' => $user->tenant_id,
            'total_amount' => 50000, // 500.00 EUR in cents
            'currency' => 'EUR',
            'status' => InvoiceStatus::Pending,
        ]);

        $gatewayMock = $this->createMock(PaymentGatewayInterface::class);
        $gatewayMock->expects($this->once())
            ->method('charge')
            ->with(50000, 'EUR', 'pm_card_valid')
            ->willReturn((object) ['transactionId' => 'ch_test_998811']);
        $this->app->instance(PaymentGatewayInterface::class, $gatewayMock);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/invoices/{$invoice->id}/settlement", [
                'payment_method_id' => 'pm_card_valid',
                'amount' => 50000,
                'currency' => 'EUR',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'settled')
            ->assertJsonPath('data.attributes.settled_at', '2026-11-14T12:00:00Z');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Settled->value,
            'transaction_reference' => 'ch_test_998811',
            'settled_at' => '2026-11-14 12:00:00',
        ]);
    }

    public function test_settlement_rejected_when_amount_mismatches_invoice(): void
    {
        $user = User::factory()->create(['tenant_id' => 'tenant-100']);
        $invoice = Invoice::factory()->create([
            'tenant_id' => $user->tenant_id,
            'total_amount' => 50000,
            'currency' => 'EUR',
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/invoices/{$invoice->id}/settlement", [
                'payment_method_id' => 'pm_card_valid',
                'amount' => 25000, // Mismatched amount
                'currency' => 'EUR',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
```

### 6.2 Advanced Multi-Tenant Isolation Test

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Users\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class MultiTenantIsolationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_tenant_user_cannot_access_or_mutate_foreign_tenant_invoice(): void
    {
        $tenantAlice = User::factory()->create(['tenant_id' => 'tenant-alice']);
        $tenantBob = User::factory()->create(['tenant_id' => 'tenant-bob']);

        $bobInvoice = Invoice::factory()->create([
            'tenant_id' => $tenantBob->tenant_id,
            'total_amount' => 10000,
        ]);

        // Attempt read
        $readResponse = $this->actingAs($tenantAlice, 'sanctum')
            ->getJson("/api/v1/invoices/{$bobInvoice->id}");

        $this->assertTrue(
            in_array($readResponse->status(), [403, 404], true),
            'Cross-tenant read must return 403 Forbidden or 404 Not Found'
        );

        // Attempt mutation
        $mutateResponse = $this->actingAs($tenantAlice, 'sanctum')
            ->postJson("/api/v1/invoices/{$bobInvoice->id}/settlement", [
                'payment_method_id' => 'pm_attacker',
                'amount' => 10000,
                'currency' => 'EUR',
            ]);

        $this->assertTrue(
            in_array($mutateResponse->status(), [403, 404], true),
            'Cross-tenant mutation must return 403 Forbidden or 404 Not Found'
        );

        // Confirm database was not mutated
        $this->assertDatabaseMissing('invoices', [
            'id' => $bobInvoice->id,
            'transaction_reference' => 'pm_attacker',
        ]);
    }
}
```

### 6.3 Concurrency and Race-Condition Verification Test

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Concurrency;

use App\Domain\Billing\Actions\SettleInvoiceAction;
use App\Domain\Billing\DTOs\SettleInvoiceDTO;
use App\Domain\Billing\Exceptions\InvoiceConflictException;
use App\Domain\Billing\Models\Invoice;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class InvoiceConcurrencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_concurrent_settlement_attempts_trigger_conflict_safely(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'pending']);
        $action = app(SettleInvoiceAction::class);

        $dto = new SettleInvoiceDTO(
            invoiceId: $invoice->id,
            tenantId: $invoice->tenant_id,
            amount: $invoice->total_amount,
            currency: $invoice->currency,
            paymentMethodId: 'pm_card_concurrency'
        );

        // First execution succeeds
        $firstResult = $action->execute($dto);
        $this->assertSame('settled', $firstResult->status);

        // Immediate duplicate execution must throw InvoiceConflictException and rollback
        $this->expectException(InvoiceConflictException::class);
        $action->execute($dto);
    }
}
```

---

## 7. Prohibited Practices and Their Rationale

| Prohibited Practice | Failure Mode / Flaw | Mandated Practice |
| :--- | :--- | :--- |
| **Mocking Eloquent Models / Query Builders** | Bypasses database constraints, data types, and index execution; tests pass while production queries fail. | Use real test database transactions with `LazilyRefreshDatabase`. |
| **Testing Only HTTP Status Codes (`assertOk()`)** | Allows silent schema drift, missing attributes, and unverified data mutations. | Assert response body schema, JSON paths, and database state mutations. |
| **Unfrozen Clocks in Time-Sensitive Logic** | Causes flaky tests that intermittently fail across midnight, leap years, or DST changes. | Use `Carbon::setTestNow()` with explicit teardown in every temporal test. |
| **Shared Database State Between Tests** | Causes non-deterministic test failures when execution order changes. | Wrap all tests in transaction rollbacks using `LazilyRefreshDatabase`. |
| **Real Network I/O in Automated Test Suites** | Causes slow CI execution, flaky tests on network drops, and external credential leaks. | Fake all external HTTP services using `Http::fake()` or mock interfaces. |
| **Relying Solely on Line Coverage** | Creates false sense of security; tests may execute lines without verifying correct outcomes. | Enforce Mutation Testing with Infection (MSI $\ge 80\%$). |
| **Empty or Incomplete Test Methods** | Tests masquerading as completed verification that actually test nothing. | Fully implement Arrange-Act-Assert blocks or remove invalid tests. |

---

## 8. Security and Reliability Requirements

### 8.1 Automated Security Verification Suite
Every security boundary must have explicit automated regression test coverage:
1. **Broken Object-Level Authorization (BOLA/IDOR):** Automated test cases verifying that Tenant A cannot access Tenant B resources.
2. **Broken Function-Level Authorization (BFLA):** Automated test cases verifying that non-admin identities receive 403 Forbidden on administrative endpoints.
3. **Mass Assignment Prevention:** Feature test asserting that passing unauthorized fields (e.g., `is_admin`, `balance`) via request payloads does not alter model attributes.
4. **Rate Limiting Enforcement:** Adversarial test executing $N+1$ rapid requests to assert HTTP 429 Too Many Requests response with `Retry-After` header.

### 8.2 Query Budget Testing
To prevent N+1 query cascades from reaching production, critical endpoints must enforce query budgets:

```php
public function test_listing_endpoint_satisfies_constant_query_budget(): void
{
    User::factory()->count(20)->create();

    DB::enableQueryLog();

    $this->actingAs(User::first(), 'sanctum')
        ->getJson('/api/v1/users');

    $queries = DB::getQueryLog();
    // Budget: Authentication lookup (1) + Paginated items query (1) = Max 2 queries
    $this->assertLessThanOrEqual(2, count($queries), 'Query budget exceeded. N+1 cascade detected.');
}
```

---

## 9. Testing and Verification (CI/CD Pipeline Quality Gates)

Verification is complete only when the system passes all five progressive automated CI quality gates:

```
┌─────────────────────────────────────────────────────────────────┐
│ GATE 1: Code Formatting & Style Compliance (Laravel Pint / PSR) │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Pass
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ GATE 2: Static Analysis at Level 8 (PHPStan / Larastan)         │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Pass
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ GATE 3: Security & Supply Chain Audit (composer audit)          │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Pass
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ GATE 4: Full Automated Test Suite with Random Ordering          │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Pass
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ GATE 5: Mutation Testing (Infection MSI Threshold >= 80%)       │
└─────────────────────────────────────────────────────────────────┘
```

### 9.1 Verification Command Matrix

```bash
# Gate 1: Code style
vendor/bin/pint --test

# Gate 2: Static analysis
vendor/bin/phpstan analyse --level=8

# Gate 3: Security vulnerability audit
composer audit

# Gate 4: Deterministic automated tests in parallel
php artisan test --parallel --order-by=random

# Gate 5: Mutation testing score indicator
vendor/bin/infection --min-msi=80 --min-covered-msi=85 --threads=4
```

---

## 10. Failure Scenarios and Prevention

### 10.1 Test Failure Analysis Matrix

| Failure Mode | Root Cause | Impact | Detection Mechanism | Engineering Prevention |
| :--- | :--- | :--- | :--- | :--- |
| **Flaky Test on Time Transitions** | Test calculates `now()->addDays(30)` across midnight or month boundaries. | Intermittent CI failure; engineers ignore red builds. | Test fails on specific calendar days or midnight UTC runs. | Always freeze time using `Carbon::setTestNow()`. |
| **Escaped Mutants in Domain Logic** | Asserting only status code without validating model attributes. | Critical business logic bugs slip into production unnoticed. | Infection mutation test reports surviving mutants. | Add granular assertions checking exact database values and DTO properties. |
| **Test State Leakage Across Workers** | Non-transactional writes or persistent test files left on disk. | Random test failures in parallel CI runs (`--parallel`). | Test passes individually but fails when run in full suite. | Use `LazilyRefreshDatabase` and `Storage::fake()`. |
| **Mocking Internal Architecture** | Mocking Eloquent query builder methods (`where`, `orderBy`). | False positive: tests pass, but production query has SQL syntax errors. | Production crashes despite 100% test pass rate. | Never mock Eloquent or DB. Run real queries against test database. |
| **Hidden N+1 Performance Regression** | Developer adds relationship access in resource without eager loading. | API performance degrades under high load. | Production latency alerts. | Implement automated Query Budget assertions in Feature tests. |

### 10.2 Defect Reproduction Protocol (Five Steps)
When a production bug or regression is reported:
1. **Capture Diagnostic Trace:** Extract request payload, headers, user context, and stack trace from error monitoring.
2. **Write Failing Regression Test First:** Construct an automated Feature test that reproduces the defect in isolation. Verify that the test fails with the exact reported error.
3. **Isolate Root Cause:** Inspect the failure trace, query logs, and boundary states.
4. **Apply Architectural Fix:** Modify the production code according to approved standards without modifying the regression test.
5. **Verify Fix & Mutation Eradication:** Run the test to confirm it passes, then run Infection to confirm no mutants survive around the fix.

---

## 11. Acceptance Criteria

Before certifying any testing deliverable, verify compliance against this checklist:

- [ ] **Strict Typing:** All test files declare `declare(strict_types=1);` and are fully typed.
- [ ] **File Size Boundary:** Every test class is under 300 physical lines.
- [ ] **Meaningful Assertions:** Tests assert HTTP status, JSON structure, and database row changes.
- [ ] **Database Isolation:** Database tests utilize `LazilyRefreshDatabase`; zero persistent state leaks.
- [ ] **Deterministic Clocks:** Time-dependent tests freeze time using `Carbon::setTestNow()`.
- [ ] **Mocking Boundaries Respected:** External I/O is faked (`Http::fake`, `Storage::fake`); Eloquent queries are executed for real.
- [ ] **Multi-Tenant Boundaries Verified:** Tests prove cross-tenant data access is blocked (403/404).
- [ ] **Concurrency & Locking Tested:** Pessimistic locking or atomic actions are verified for conflict handling.
- [ ] **Query Budgets Enforced:** Critical read endpoints assert maximum query count thresholds.
- [ ] **Random Order Pass:** Test suite passes completely under `php artisan test --order-by=random`.
- [ ] **Quality Gates Passed:** Pint, PHPStan Level 8, and security audit pass with zero errors.
- [ ] **Mutation Score Compliant:** Infection MSI meets or exceeds the 80% threshold for domain packages.

---

## 12. References and Related Skills

### 12.1 Authoritative Specifications and Literature
- **PHPUnit 11 Documentation:** Official PHPUnit Testing Framework Manual
- **Pest PHP Documentation:** Elegant PHP Testing Framework
- **Infection PHP:** Mutation Testing Framework for PHP
- **OWASP ASVS v4.0.3:** Automated Security Verification Standard
- **The Practical Test Pyramid:** Martin Fowler architectural guidance

### 12.2 Related Universal Core Skills
- `SKILL-API-001`: RESTful and JSON:API Engineering Standards
- `SKILL-LARAVEL-001`: Laravel Engineering Standards
- `SKILL-SEC-001`: Secure Coding and Defensive Engineering Standards
- `SKILL-DB-001`: Database Engineering and Schema Governance Standards
- `SKILL-PHP-001`: PHP Modern Engineering Standards

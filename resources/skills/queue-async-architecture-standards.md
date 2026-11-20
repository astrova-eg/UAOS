# Shared Engineering Skill: Queue and Asynchronous Architecture Standards

**Skill ID:** SKILL-QUEUE-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Queue and Asynchronous Architecture Standards** skill establishes the authoritative enterprise standard for designing, implementing, securing, operating, and monitoring asynchronous queue processing architectures across Universal Core.

In the Universal Core infrastructure environment, **Redis is a mandatory, continuously available infrastructure service**. The primary purpose of this standard is to guarantee predictable job execution, high throughput, zero message loss, strict idempotency, safe worker process lifecycles, and unbreakable tenant isolation. This skill provides explicit engineering rules to prevent ghost-record race conditions, worker memory bloat, thundering-herd retries, unmonitored poison messages, and cross-tenant execution contamination.

---

## 2. Scope and Applicability

This standard governs all asynchronous computation and background processing workflows across Universal Core:
- **Redis Queue Engine:** Redis connection topologies, key schemas, atomic Lua reservation scripts, and sorted set (`ZSET`) delayed scheduling.
- **Worker Process Lifecycles:** Laravel Horizon supervisors, Linux Supervisor, Windows background services, process recycling, memory thresholds, and graceful shutdown handling.
- **Job Implementation Patterns:** Laravel queue contracts (`ShouldQueue`, `ShouldBeUnique`, `ShouldBeUniqueUntilProcessing`), payload serialization, retry policies, and backoff governance.
- **Data & Transaction Boundaries:** Database transaction synchronization (`afterCommit`), Transactional Outbox pattern, and distributed state coordination.
- **Reliability & Delivery Semantics:** At-least-once delivery, effectively-once processing, idempotency ledgers, atomic distributed locks, and dead-letter queue (DLQ) workflows.
- **Tenant & Telemetry Integration:** Tenant context propagation, distributed tracing (`traceparent`), correlation IDs, and operational metrics.

---

## 3. Required Knowledge and Competencies

Engineers and automated agents applying this skill must demonstrate verified expertise in:
1. **Redis Internals and Data Structures:** Redis primitive commands (`LPUSH`, `RPOP`, `BRPOP`, `ZADD`, `ZRANGEBYSCORE`), atomic script execution via Lua, connection multiplexing, and memory eviction governance (`noeviction` for queue datastores).
2. **Distributed Computing Delivery Semantics:** The mathematical and physical realities of distributed task execution: why "exactly-once" delivery is a theoretical impossibility in unreliable networks without an idempotency ledger, and how to implement "effectively-once" state machines.
3. **PHP Long-Running CLI Worker Mechanics:** PHP memory management, garbage collection cycles (`gc_collect_cycles()`), memory leaks from static properties/singletons, and database query logging disablement (`DB::disableQueryLog()`).
4. **Relational Concurrency & Deadlock Immunity:** InnoDB pessimistic row locking (`lockForUpdate()`), transaction commit timing, and race conditions between worker pickup and database transaction commits.
5. **Worker Supervision & Process Orchestration:** Process signals (`SIGTERM`, `SIGINT`), Horizon configuration, and queue balancing strategies (simple, auto, false).

---

## 4. Core Engineering Principles

1. **Strict Idempotency by Default:** Every queued job must be designed under the assumption of *at-least-once* delivery. Re-executing a job with identical parameters—whether due to network retry, worker crash, or manual replay—must produce the exact same system state with zero duplicate side-effects (e.g., zero duplicate credit card charges or double-minted invoices).
2. **Transactional Synchronization for Queue Dispatches:** When a job is dispatched within a database transaction boundary, dispatch execution must synchronize with transaction commit to eliminate ghost-record race conditions (where workers query uncommitted data). In Laravel 11, enforce this synchronization via connection-level configuration (`'after_commit' => true` in `config/queue.php`), class property (`public bool $afterCommit = true`), or explicit dispatch modifier (`dispatch($job)->afterCommit()`). Dispatches originating outside database transactions do not require `$afterCommit`.
3. **Lightweight Scalar Payloads:** Pass scalar identifiers (`int`, `string`, `ULID`) or minimal immutable DTOs into job constructors. Never serialize entire Eloquent model hierarchies, open file resources, database connections, or sensitive credentials into job payloads.
4. **Bounded Retry and Timeout Governance:** Every job class must explicitly declare `$tries`, `$timeout`, and `$backoff`. Unbounded retries and missing timeouts are strictly prohibited.
5. **Strict Queue Timing Invariant:** The queue connection's `retry_after` setting MUST be strictly greater than the job's `$timeout` (e.g., `$timeout = 60`, `retry_after = 90`). If `retry_after <= $timeout`, Redis will release the job to a second worker while the first worker is still actively executing it, guaranteeing duplicate concurrent execution.

---

## 5. Mandatory Standards

### 5.1 Redis Queue Topology, Prioritization, and Isolation

1. **Dedicated Queue Hierarchy:**
   Jobs must be categorized by latency sensitivity and resource intensity into dedicated named queues:
   - `critical`: High-priority, real-time user-facing tasks (e.g., password reset emails, multi-factor auth codes, instantaneous webhook dispatches). Target latency: $< 500\text{ ms}$.
   - `high`: Core business operations (e.g., payment authorizations, order settlement). Target latency: $< 2\text{ s}$.
   - `default`: Standard asynchronous processing (e.g., domain events, notifications). Target latency: $< 10\text{ s}$.
   - `low`: Non-urgent background syncing, maintenance, and search indexing. Target latency: $< 60\text{ s}$.
   - `exports`: Heavy, CPU/IO-intensive batch jobs (e.g., CSV imports/exports, PDF generation). Isolated workers with dedicated concurrency limits.
2. **Worker Priority Configuration:**
   Workers must process queues in strict priority order to eliminate priority inversion:
   ```bash
   php artisan queue:work redis --queue=critical,high,default,low
   ```
3. **Redis Database & Connection Segregation:**
   - The queue datastore must use a dedicated Redis database index or isolated Redis instance separate from cache and session storage.
   - Cache eviction policies (`volatile-lru` or `allkeys-lru`) must NEVER apply to queue Redis instances. Queue instances must enforce `maxmemory-policy: noeviction` so jobs are never evicted under memory pressure.

---

### 5.2 Delivery Semantics and Idempotency Architecture

#### Delivery Guarantees vs. Business-Operation Idempotency
In distributed systems subject to latency, network partitions, or worker process crashes (the Two Generals' Problem), message transport queues provide **at-least-once** delivery guarantees. A worker may complete task processing successfully, yet crash or lose network connectivity before acknowledging completion to Redis, causing Redis to re-deliver the job upon `retry_after` expiration.

No message broker or ledger mechanism alone can physically guarantee "exactly-once delivery" across unreliable networks. Instead, Universal Core enforces **business-operation idempotency** using a **Two-Phase Idempotency Ledger and Atomic State Transitions**: duplicate message deliveries are intercepted and reconciled so that external side-effects and database mutations occur at most once.

```
[Job Pickup (At-Least-Once)] -> [Phase 1: Status Inspection & Distributed Lock] -> [Phase 2: External Side-Effect & Atomic DB Commit]
```

1. **Phase 1: Status Inspection and Distributed Locking:**
   - The job queries an `idempotency_records` table or acquires an atomic distributed lock (`Cache::lock`) using a deterministic key (e.g., `idempotency:charge:{order_id}`).
   - If the business entity or idempotency key is already in status `completed` (or settled), the job logs an informational message and exits cleanly without re-executing side effects.
   - If a concurrent worker is actively processing the same key and the lock has not expired, the job calls `$this->release(15)` and yields execution.
   - Otherwise, the job records the reservation status as `processing` with a defined TTL.
2. **Phase 2: Execution and Finalization:**
   - Execute any non-transactional external side-effects (e.g., payment gateway call with provider-side idempotency key).
   - In a fast, dedicated database transaction, update the business entity and transition the idempotency record to `completed` atomically.
3. **Concurrency Locking (`ShouldBeUnique`):**
   - For jobs that should not be queued concurrently with identical arguments (e.g., report generation for the same tenant and date), implement `ShouldBeUnique` or `ShouldBeUniqueUntilProcessing`:
     ```php
     final class GenerateMonthlyReportJob implements ShouldQueue, ShouldBeUnique
     {
         public int $uniqueFor = 600; // 10 minutes

         public function uniqueId(): string
         {
             return sprintf('report:%s:%s', $this->tenantId, $this->billingMonth);
         }
     }
     ```

---

### 5.3 Transaction Boundaries and After-Commit Dispatching

1. **The Ghost-Record Race Condition:**
   - If a job is dispatched inside a transaction before `DB::commit()`, a fast Redis worker may pick up the job and query the database *before* the issuing thread commits. The worker encounters a `ModelNotFoundException` or reads stale state.
2. **Transactional Synchronization Options:**
   To synchronize job dispatching with transaction commits, use one of the following documented mechanisms:
   - **Connection-Level Configuration (Recommended for Global Consistency):** Set `'after_commit' => true` in `config/queue.php` for the Redis connection, automatically delaying dispatch until transaction commit across all jobs on that connection.
   - **Job Class Property:** Declare `public bool $afterCommit = true;` on individual job classes that are frequently dispatched within transaction blocks.
   - **Dispatch-Time Fluent Modifier:** Call `dispatch($job)->afterCommit()` explicitly at the dispatch site.
   Jobs dispatched outside of database transactions execute immediately without waiting.
3. **The Transactional Outbox Pattern:**
   For ultra-critical financial or audit events where Redis unavailability must not abort the database transaction:
   - Write the event payload to an `outbox_messages` table within the primary business database transaction.
   - A dedicated outbox publisher process reads unpublished records (`WHERE published_at IS NULL`) with `lockForUpdate()`, pushes them to Redis, and marks them `published_at = now()`.

---

### 5.4 Retry Policies, Exponential Backoff, and Jitter

1. **Stepped and Exponential Backoff:**
   Hardcoded immediate retries create a "thundering herd" that overloads recovering external APIs or databases. Jobs must implement exponential or stepped backoff.
2. **Random Jitter:**
   To prevent synchronized retry waves across hundreds of concurrent failed jobs, add randomized jitter to backoff intervals:
   ```php
   /**
    * Calculate the number of seconds to wait before retrying the job.
    *
    * @return array<int, int>
    */
    public function backoff(): array
    {
        return [
            5 + random_int(1, 3),    // Attempt 1: ~5-8s
            30 + random_int(2, 8),   // Attempt 2: ~32-38s
            180 + random_int(10, 30) // Attempt 3: ~190-210s
        ];
    }
   ```
3. **Explicit Maximum Attempts:**
   Every job must define `$tries = 3` (or up to 5 for transient network I/O). A job must never define `$tries = 0` (infinite retries).

---

### 5.5 Worker Lifecycle, Memory Hygiene, and Supervision

1. **CLI Worker Memory Management:**
   - Because workers run as persistent long-running PHP processes, memory leaks will crash workers or degrade host performance.
   - Supervised worker commands must strictly enforce limits:
     ```bash
     php artisan queue:work redis \
         --queue=critical,high,default,low \
         --memory=128 \
         --max-jobs=1000 \
         --max-time=3600 \
         --timeout=60 \
         --sleep=3 \
         --tries=3
     ```
   - In the application's service providers, disable query logging on queue workers during boot:
     ```php
     if (app()->runningInConsole()) {
         DB::disableQueryLog();
     }
     ```
2. **Graceful Worker Shutdown:**
   - Deployments must issue `php artisan queue:restart`.
   - Workers intercept the restart signal, finish the active in-flight job, and exit gracefully with code `0`. The supervisor immediately spawns a fresh worker process running updated application code.
3. **Redis Outages & Reconnection Resilience:**
   - Workers must be supervised so that if Redis becomes temporarily unreachable, connection exceptions trigger worker restarts with backoff delays rather than crashing permanently.

---

### 5.6 Tenant Isolation & Payload Security

1. **Context Serialization:**
   - Every tenant-scoped job must define a public `readonly string $tenantId` constructor argument.
   - Models must be passed as IDs (`int $orderId`, `string $userId`), never as pre-loaded Eloquent models containing dirty or cached attributes.
2. **Tenant Middleware Enforcement:**
   - Jobs must be routed through `SetTenantContextMiddleware` (defined in `SKILL-TENANT-001`), which initializes `TenantContext` before `handle()` and resets it in `finally`.
3. **Payload Sanitization (Zero Credentials in Redis):**
   - Redis stores job payloads as base64-encoded JSON in plain text.
   - Job payloads must NEVER include user passwords, API secret keys, full credit card numbers (PAN), or raw bearer tokens.

---

## 6. Implementation Procedures and Practices

### 6.1 Resilient, Idempotent Job Implementation

```php
<?php

declare(strict_types=1);

namespace App\Domain\Billing\Jobs;

use App\Core\Tenancy\Context\TenantContext;
use App\Core\Tenancy\Queue\SetTenantContextMiddleware;
use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Models\PaymentTransaction;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessPaymentSettlementJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $uniqueFor = 180;
    public bool $afterCommit = true;

    public function __construct(
        public readonly string $tenantId,
        public readonly int $transactionId,
        public readonly string $idempotencyKey
    ) {
        $this->onQueue('high');
    }

    public function uniqueId(): string
    {
        return sprintf('settle_%s_%d', $this->tenantId, $this->transactionId);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            app(SetTenantContextMiddleware::class),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [
            5 + random_int(1, 3),
            30 + random_int(2, 6),
            120 + random_int(10, 20),
        ];
    }

    public function handle(PaymentGatewayInterface $gateway): void
    {
        // 1. Acquire short-lived atomic distributed execution lock
        $lockKey = sprintf('lock:payment:%s:%d', $this->tenantId, $this->transactionId);
        $lock = Cache::lock($lockKey, 30);

        if (! $lock->get()) {
            $this->release(10);
            return;
        }

        try {
            // 2. Strict Idempotency Check: Fast query outside long transaction
            $transaction = PaymentTransaction::query()
                ->where('id', $this->transactionId)
                ->firstOrFail();

            if ($transaction->status !== PaymentTransaction::STATUS_PENDING) {
                Log::info('Payment transaction already finalized. Skipping processing.', [
                    'tenant_id' => $this->tenantId,
                    'transaction_id' => $this->transactionId,
                    'current_status' => $transaction->status,
                ]);
                return;
            }

            // 3. Execute external side-effect OUTSIDE relational database transaction
            // Note: Never hold an open MySQL transaction or row lock during third-party HTTP calls
            $result = $gateway->settle(
                transactionReference: $transaction->gateway_reference,
                amountCents: $transaction->amount_cents,
                idempotencyKey: $this->idempotencyKey
            );

            // 4. Atomic state transition in a fast, dedicated database transaction
            DB::transaction(function () use ($result) {
                $transaction = PaymentTransaction::query()
                    ->where('id', $this->transactionId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($transaction->status !== PaymentTransaction::STATUS_PENDING) {
                    return;
                }

                $transaction->update([
                    'status' => $result->isSuccess() ? PaymentTransaction::STATUS_SETTLED : PaymentTransaction::STATUS_FAILED,
                    'settled_at' => now(),
                    'gateway_response_code' => $result->responseCode,
                ]);
            });
        } finally {
            $lock->release();
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::critical('Payment settlement job exhausted retries and permanently failed.', [
            'tenant_id' => $this->tenantId,
            'transaction_id' => $this->transactionId,
            'idempotency_key' => $this->idempotencyKey,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Job middleware does NOT wrap failed(). Explicitly restore TenantContext
        // to prevent TenantScope from appending 'WHERE 1 = 0' on unpartitioned queries.
        $tenant = Tenant::query()->find($this->tenantId);
        if ($tenant !== null) {
            app(TenantContext::class)->executeAs($tenant, function () use ($exception): void {
                PaymentTransaction::query()
                    ->where('id', $this->transactionId)
                    ->where('status', PaymentTransaction::STATUS_PENDING)
                    ->update([
                        'status' => PaymentTransaction::STATUS_FAILED,
                        'failure_reason' => 'Queue job failed: ' . $exception->getMessage(),
                    ]);
            });
        }
    }
}
```

---

### 6.2 High-Throughput Batch Processing with Failure Isolation

When processing large datasets (e.g., monthly billing statements across 50,000 users), never process the entire dataset in a single monolithic job. Chunk into small atomic jobs ($\le 500$ records per job) managed via `Bus::batch`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Jobs\GenerateSingleInvoiceJob;
use App\Domain\User\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DispatchTenantBillingBatchAction
{
    public function execute(string $tenantId, string $billingCycle): Batch
    {
        $jobs = [];

        User::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select(['id', 'tenant_id'])
            ->chunkById(500, function ($users) use (&$jobs, $billingCycle) {
                foreach ($users as $user) {
                    $jobs[] = new GenerateSingleInvoiceJob($user->tenant_id, $user->id, $billingCycle);
                }
            });

        return Bus::batch($jobs)
            ->name(sprintf('billing:%s:%s', $tenantId, $billingCycle))
            ->allowFailures()
            ->onQueue('exports')
            ->then(function (Batch $batch) use ($tenantId, $billingCycle): void {
                Log::info('Tenant billing batch completed successfully.', [
                    'tenant_id' => $tenantId,
                    'batch_id' => $batch->id,
                    'total_jobs' => $batch->totalJobs,
                ]);
            })
            ->catch(function (Batch $batch, Throwable $e) use ($tenantId): void {
                Log::error('Tenant billing batch encountered job failures.', [
                    'tenant_id' => $tenantId,
                    'batch_id' => $batch->id,
                    'failed_jobs' => $batch->failedJobs,
                    'error' => $e->getMessage(),
                ]);
            })
            ->dispatch();
    }
}
```

---

## 7. Prohibited Practices and Anti-Patterns

1. **Claiming "Exactly-Once" Processing Without Idempotency:**
   - *Violation:* Assuming a message queue guarantees single execution without implementing idempotency checks.
   - *Consequence:* Network timeouts and worker failures cause duplicate payments, emails, or state corruptions.
2. **Dispatching Jobs Inside Uncommitted Transactions without `afterCommit`:**
   - *Violation:* Calling `dispatch(new ProcessOrderJob($order->id))` inside `DB::transaction()` without `$afterCommit = true`.
   - *Consequence:* The worker queries MySQL before the transaction commits, resulting in `ModelNotFoundException` or race conditions.
3. **Queue `retry_after` Less Than or Equal to Job `$timeout`:**
   - *Violation:* Configuring `config/queue.php` with `'retry_after' => 60` while a job has `public int $timeout = 60` or `120`.
   - *Consequence:* Redis assumes the worker died at second 60 and delivers the job to a second worker while the first is still running, guaranteeing duplicate concurrent execution.
4. **Serializing Heavy Eloquent Models in Job Payloads:**
   - *Violation:* Writing `__construct(public Order $order)`.
   - *Consequence:* Outdated model state is serialized into Redis. When the job runs hours later, it overwrites fresher database state with stale attributes.
5. **Unbounded Retries and Missing Backoff:**
   - *Violation:* Omitting `$tries` or setting `$tries = 0` with zero backoff.
   - *Consequence:* A single bug or downstream outage triggers an infinite loop consuming 100% of CPU and flooding logs with millions of failures.
6. **Leaking Sensitive Credentials in Job Payloads:**
   - *Violation:* Passing raw API tokens, user passwords, or credit card numbers into job constructors.
   - *Consequence:* Plaintext credentials are stored in Redis lists, exposed in failed job tables, and leaked into telemetry log streams.

---

## 8. Security, Resilience, and High-Throughput Scenarios

### 8.1 Dead-Letter Queue (DLQ) and Poison Message Quarantine
1. **The Poison Message Threat:**
   A poison message is a job whose payload contains corrupt data or causes a fatal runtime crash (e.g., uncatchable syntax error or segfault). Left unmanaged, it repeatedly consumes worker time until exhausting retries.
2. **Quarantine Governance:**
   - Jobs exceeding `$tries` are automatically logged into the `failed_jobs` table via the `failed()` hook.
   - Automated alerts (P2) notify the engineering on-call rotation of failed job accumulation.
   - Reprocessing failed jobs must always use `php artisan queue:retry {id}` in controlled, audited staging or maintenance windows—never bulk retry without addressing root causes.

### 8.2 External Service Rate Limiting with `Redis::throttle`
When interacting with rate-limited third-party APIs (e.g., Stripe, Twilio), jobs must throttle dispatch rates atomically using Redis:
```php
Redis::throttle('stripe-gateway')
    ->allow(50)
    ->every(1)
    ->then(function () use ($gateway) {
        $gateway->execute();
    }, function () {
        // Could not obtain lock within interval; release job with backoff
        $this->release(5);
    });
```

---

## 9. Testing and Verification

### 9.1 Idempotency Double-Execution Verification
Every queued job must undergo automated testing proving that executing `handle()` multiple times produces zero duplicate side-effects:
```php
public function test_payment_settlement_job_is_strictly_idempotent(): void
{
    $tenant = Tenant::factory()->create(['status' => 'active']);
    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => PaymentTransaction::STATUS_PENDING,
        'amount_cents' => 10000,
    ]);

    $gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
    $gatewayMock->shouldReceive('settle')
        ->once() // Must be invoked exactly once across two executions
        ->andReturn(new PaymentGatewayResponse(isSuccess: true, responseCode: 'OK'));

    $job = new ProcessPaymentSettlementJob($tenant->id, $transaction->id, 'idem_key_123');

    // First execution: should perform settlement
    $job->handle($gatewayMock);
    $transaction->refresh();
    $this->assertSame(PaymentTransaction::STATUS_SETTLED, $transaction->status);

    // Second execution (duplicate delivery simulation): should skip gateway settlement
    $job->handle($gatewayMock);
    $this->assertSame(PaymentTransaction::STATUS_SETTLED, $transaction->status);
}
```

### 9.2 After-Commit Transactional Dispatch Verification
```php
public function test_job_is_dispatched_only_after_database_transaction_commits(): void
{
    Queue::fake();

    DB::transaction(function () {
        OrderSettlementAction::execute();
        Queue::assertNothingPushed(); // Must not be pushed inside uncommitted transaction
    });

    Queue::assertPushed(ProcessPaymentSettlementJob::class, 1);
}
```

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Detection Signal | Engineering Prevention Rule |
| :--- | :--- | :--- | :--- |
| **Duplicate Concurrent Execution** | `retry_after <= $timeout` in queue config. | Simultaneous logs from two workers processing identical job ID. | Enforce `retry_after >= $timeout + 30` in queue configurations. |
| **Worker Reads Stale State** | Job dispatched inside uncommitted transaction without `afterCommit`. | `ModelNotFoundException` on worker pickup. | Synchronize dispatch with transaction commit via connection config, job property (`$afterCommit`), or `dispatch()->afterCommit()`. |
| **Thundering-Herd API Cascade** | Hundreds of jobs failing and retrying simultaneously without jitter. | 429 Too Many Requests on external API, exponential CPU spikes. | Implement stepped backoff with randomized jitter on all retried jobs. |
| **Worker Process OOM Crash** | Memory leak in long-running CLI process (e.g., `DB::enableQueryLog()`). | Worker process terminated with exit code 137. | Run workers with `--memory=128`, `--max-jobs=1000`, and disable query logs. |
| **Stale Serialized Model Bug** | Passing Eloquent model instance directly into job constructor. | Job updates database using hours-old entity properties. | Pass scalar IDs only; re-fetch models with `lockForUpdate()` in `handle()`. |
| **Plaintext Credential Exposure** | Passing API key or password into job constructor. | Credentials visible in Redis `redis-cli` or `failed_jobs` table. | Never serialize secrets; resolve credentials from encrypted vault inside `handle()`. |

---

## 11. Acceptance Criteria

Before approving any asynchronous queue engineering deliverable, verify:
- [ ] Redis is configured as the active queue driver with `noeviction` memory policy.
- [ ] Every queued job implements `ShouldQueue` and assigns an explicit `$onQueue('...')`.
- [ ] Every queued job defines explicit `$tries`, `$timeout`, and `backoff()` with randomized jitter.
- [ ] Redis connection `retry_after` is strictly greater than job `$timeout` ($+30\text{ s}$ minimum).
- [ ] Dispatches within transactions synchronize with commit (connection-level `'after_commit'`, job property, or `dispatch()->afterCommit()`).
- [ ] Constructors accept only scalar identifiers or lightweight DTOs; zero serialized Eloquent models.
- [ ] Job payload contains zero plain-text secrets, API keys, passwords, or raw PANs.
- [ ] Multi-tenant jobs carry scalar `tenantId` and apply `SetTenantContextMiddleware`.
- [ ] Double-execution automated tests prove strict idempotency without duplicate side-effects.
- [ ] Worker processes are supervised with `--memory`, `--max-jobs`, and `--max-time` recycling bounds.

---

## 12. Cross-Skill Integration, References, and Related Skills

### 12.1 Cross-Skill Integration Architecture
This skill operates as a unified triad with **SKILL-TENANT-001** and **SKILL-ERR-001**:
- **With SKILL-TENANT-001 (Multi-Tenancy):** The queue system serializes `tenant_id` into job payloads and enforces `SetTenantContextMiddleware` on the worker, guaranteeing that asynchronous execution operates under strict tenant data isolation.
- **With SKILL-ERR-001 (Observability):** Every dispatched job inherits the distributed `correlation_id` and W3C `traceparent`. Queue workers inject these into `Log::withContext()` before executing `handle()`, ensuring end-to-end trace correlation from HTTP request to background worker.

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
  - `SKILL-ERR-001` (Exception Handling, Telemetry and Observability Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-PERF-001` (Performance Profiling and Optimization Standards)
- **External Standards:**
  - Redis Reference Manual: Data Structures, Lua Scripting, and Replication
  - Enterprise Integration Patterns: Idempotent Receiver & Transactional Outbox
  - Laravel 11.x Documentation: Queues, Horizon, and Job Batching

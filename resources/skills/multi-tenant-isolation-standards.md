# Shared Engineering Skill: Multi-Tenant Architecture and Isolation Standards

**Skill ID:** SKILL-TENANT-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Multi-Tenant Architecture and Isolation Standards** skill establishes the authoritative enterprise standard for designing, isolating, securing, operating, and verifying multi-tenant SaaS systems across Universal Core.

Its primary purpose is to guarantee absolute, unbreakable data isolation between tenants across all execution surfaces: database queries, Eloquent models, cache keys, Redis datastores, filesystem storage, background queue workers, event listeners, search indexes, external exports, and API endpoints. It provides concrete engineering standards to eliminate cross-tenant data leakage (Insecure Direct Object Reference / IDOR), tenant context spoofing, noisy-neighbor degradation, and unauthorized administrative cross-tenant contamination.

---

## 2. Scope and Applicability

This standard applies to all software components within Universal Core handling tenant-owned state, identity, or processing:
- **Relational Databases & Migrations:** Shared database/shared schema partitioning, schema-per-tenant, database-per-tenant connection routing, and compound uniqueness governance in MySQL 8.4 LTS (and MySQL 8.0+).
- **Application Context & Scoping:** Tenant resolution pipelines, request lifecycle bindings, Eloquent global query scopes (`TenantScope`), repository enforcement, and Route Model Binding.
- **Volatile & Ephemeral Storage:** Redis keyspacing, cache tag isolation, session management, and rate limiting.
- **Persistent Unstructured Storage:** Filesystem partitioning, S3/local directory hierarchies, and signed export asset streaming.
- **Asynchronous Execution:** Queued jobs, event broadcasts, notification dispatch, and scheduled commands (`schedule:run`).
- **Administration & Operations:** Super-admin cross-tenant operations, audit logging, tenant provisioning, schema migration, deprovisioning, and disaster recovery.

---

## 3. Required Knowledge and Competencies

Engineers and automated agents applying this skill must demonstrate verified expertise in:
1. **Multi-Tenancy Architectural Trade-offs:** Deep theoretical and practical comprehension of shared-schema, schema-per-tenant, and database-per-tenant paradigms, including their operational, cost, and security profiles.
2. **Database Isolation & Indexing:** InnoDB clustered indexing, compound unique constraints including `tenant_id`, and row-level scoping.
3. **Laravel Request & Execution Lifecycles:** Container service binding, request-scoped singletons, middleware pipelines, Eloquent query builder macro mechanics, and global scopes.
4. **Long-Running Process Memory Hygiene:** Process state cleanup, container resetting in worker processes (Horizon, Octane, queue workers) to eliminate cross-tenant context bleed.
5. **Defensive Authorization & Security:** RBAC/ABAC policy enforcement, tenant context cryptographic verification, prevention of mass-assignment privilege escalation, and IDOR mitigation.

---

## 4. Core Engineering Principles

1. **Isolation by Default (Defense-in-Depth):** Data isolation must never rely on a single defensive layer. Isolation is enforced concurrently by:
   - Database schema constraints (compound unique indexes incorporating `tenant_id`).
   - Automated application scoping (Eloquent global `TenantScope`).
   - Explicit domain authorization policies asserting tenant ownership.
2. **Immutable Tenant Context within Execution Frames:** Once a tenant context is resolved for an HTTP request, queue job, or background task, it must remain immutable throughout that discrete unit of execution, except during explicitly authorized, audited administrative switches.
3. **Zero Trust on Client Tenant Claims:** On authenticated routes, the tenant context must NEVER be derived from mutable client-supplied request bodies, route query parameters, or unverified headers. It must strictly derive from the authenticated user's cryptographically verified session or bearer token claims.
4. **Fail-Closed on Missing Context:** If an operation requires a tenant context and none is bound (or the resolved tenant is inactive, suspended, or archived), execution must immediately abort with a `403 Forbidden` or `404 Not Found` response. Execution must never fall back to an unpartitioned or global dataset.
5. **Compound Relational Uniqueness:** Every unique index on a tenant-partitioned table must include `tenant_id` as a leading or composite column (e.g., `UNIQUE(tenant_id, slug)`, `UNIQUE(tenant_id, email)`).

---

## 5. Mandatory Standards

### 5.1 Architectural Partitioning Models & Decision Matrix

When architecting a service on Universal Core, the multi-tenancy model must be selected according to the following evaluated trade-off matrix:

| Architectural Model | Data Isolation Mechanism | Cost & Resource Density | Migration & Schema Complexity | Backup / Restore Granularity | Primary Use Case in Universal Core |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Shared Database / Shared Schema** *(Default)* | Discriminator column (`tenant_id`) enforced via indexes & Eloquent `TenantScope`. | Extremely high resource density; lowest operational infrastructure cost. | Simple, single schema migration runner across all tenants. | Full database restore; single-tenant point-in-time recovery requires logical ETL export. | Standard SaaS tenants, SMB tiers, high-density multi-tenant workloads. |
| **Schema-per-Tenant** | Dedicated MySQL schema/database per tenant on a shared database server. | Medium resource density; bounded overhead per schema. | High complexity; migrations must iterate through all tenant schemas sequentially or concurrently. | Native single-schema dump and restore (`mysqldump` / point-in-time restore per tenant). | Mid-market to enterprise tenants requiring independent schema lifecycle or compliance boundaries. |
| **Database-per-Tenant** | Dedicated MySQL instance/cluster with dynamic database connection switching. | Low resource density; highest infrastructure cost. | Highest complexity; fleet-wide connection routing, migration orchestration, and monitoring. | Complete physical isolation; independent database snapshots, replication, and encryption keys. | High-tier Enterprise / regulated tenants (HIPAA, PCI-DSS Level 1, sovereign data residency). |

*Universal Core Rule:* Unless an explicit Architecture Decision Record (ADR) mandates Schema-per-Tenant or Database-per-Tenant, services must implement the **Shared Database / Shared Schema** model with absolute application-layer and relational constraint enforcement.

---

### 5.2 Tenant Resolution and Context Lifecycle

1. **Resolution Pipeline Hierarchy:**
   - **Public / Domain Routes:** Subdomain (`{tenant}.core.test`) or verified custom domain resolved via strict database lookup against active tenant records.
   - **Authenticated API / Web Routes:** Resolved strictly from the authenticated subject (`$request->user()->tenant_id`). Mutable request headers (e.g., `X-Tenant-ID`) are strictly rejected on authenticated endpoints.
   - **Signed Webhook Routes:** Resolved via cryptographic signature verification against a per-tenant secret key.
2. **Context State Governance (`TenantContext`):**
   - The resolved tenant must be bound into Laravel's IoC container as a request-scoped singleton service (`TenantContext`).
   - The `TenantContext` service must encapsulate:
     - The active `Tenant` model instance.
     - Fast scalar accessors (`id()`, `uuid()`, `slug()`).
     - State verification (`isActive()`).
     - Controlled execution switching (`executeAs(Tenant $tenant, callable $callback): mixed`).
     - Teardown and reset (`reset(): void`).
3. **Lifecycle Cleanup in Long-Running Processes:**
   - In queue workers, Horizon, and Octane runtimes, `TenantContext::reset()` must be called in a `finally` block or worker lifecycle hook (`JobProcessed`, `JobFailed`) to eliminate cross-request or cross-job memory retention.

---

### 5.3 Database-Level & Application-Level Query Isolation

1. **Mandatory Column & Index Standard:**
   - Every tenant-owned table must contain a `tenant_id` foreign key column (matching the `tenants.id` type: `ULID`, `UUIDv7`, or `BIGINT UNSIGNED`).
   - Every query index used for lookup must lead with or contain `tenant_id`:
     ```sql
     INDEX idx_orders_tenant_status (tenant_id, status, created_at)
     ```
2. **Automated Eloquent Enforcement via `TenantScope`:**
   - All tenant-partitioned models must implement `TenantScopedInterface` and use the `BelongsToTenant` trait.
   - The trait must register a global query scope (`TenantScope`) that automatically injects `WHERE tenant_id = ?` on all `select`, `update`, and `delete` operations.
   - The trait's `creating` model event must verify that if `tenant_id` is supplied, it strictly matches `TenantContext::id()`, and automatically populate `$model->tenant_id = TenantContext::id()` when omitted.
   - The trait's `updating` model event must prohibit reassigning `tenant_id` on existing records (`$model->isDirty('tenant_id')`), guaranteeing tenant ownership immutability.
3. **Prohibition of Unscoped Mass Assignment:**
   - The `tenant_id` attribute must **NEVER** appear in the `$fillable` array of any Eloquent model. It must be guarded against mass assignment at all times.
4. **Governed Global Scope Removal:**
   - Calling `withoutGlobalScope(TenantScope::class)` or `withoutGlobalScopes()` on a tenant-owned model is strictly prohibited in standard application logic.
   - Any legitimate administrative or cross-tenant reporting requirement must be executed within an explicitly audited `CrossTenantContext` block.
5. **Raw Query Governance:**
   - Direct calls to `DB::table()`, `DB::select()`, or raw SQL expressions on tenant-partitioned tables bypass Eloquent global scopes.
   - When raw database queries are unavoidable for performance reasons, the query must explicitly include `->where('tenant_id', TenantContext::id())`. Code reviews must automatically reject raw queries lacking explicit tenant filtering.

---

### 5.4 Route Model Binding and Parameter Tampering Defense

1. **Scoped Route Model Binding:**
   - Nested route model bindings must use Laravel's scoped bindings to verify child ownership:
     ```php
     Route::get('/tenants/{tenant}/invoices/{invoice}', [InvoiceController::class, 'show'])
         ->scopeBindings();
     ```
2. **Implicit Binding Tenant Verification:**
   - When resolving single-parameter models (e.g., `Route::get('/invoices/{invoice}')`), the model must be resolved through the `TenantScope`. If the requested entity belongs to Tenant B while Tenant A is active, the query will return zero rows and trigger an immediate `404 Not Found` (never revealing the existence of the entity to an attacker).
3. **Authorization Policy Enforcement:**
   - Policies must explicitly assert tenant ownership as a secondary invariant:
     ```php
     public function view(User $user, Invoice $invoice): bool
     {
         return $user->tenant_id === $invoice->tenant_id
             && $user->can('invoices.view');
     }
     ```

---

### 5.5 Cache, Redis, and Storage Partitioning

1. **Cache Key Isolation:**
   - All cache operations must prefix keys with the active tenant identifier:
     ```php
     $key = sprintf('tenant:%s:reports:%s', TenantContext::id(), $reportKey);
     ```
   - Where the cache driver supports tagging (e.g., Redis), operations should use tagged caches:
     ```php
     Cache::tags(['tenant:' . TenantContext::id(), 'invoices'])->put($key, $value, $ttl);
     ```
   - Flush operations must strictly target tenant tags (`Cache::tags(['tenant:' . $tenantId])->flush()`). A global `Cache::flush()` is strictly prohibited in production multi-tenant environments.
2. **Redis Datastore & Pub/Sub Isolation:**
   - Redis keys generated outside the cache facade (e.g., rate limiters, temporary hashes) must follow the naming pattern `core:{environment}:tenant:{tenant_id}:{subsystem}:{key}`.
   - Redis Pub/Sub channels and WebSocket broadcast channels must be tenant-prefixed:
     `private-tenant.{tenant_id}.orders`.
3. **Filesystem Storage Isolation:**
   - Tenant uploaded files, exports, and temporary artifacts must be stored within dedicated directory hierarchies:
     `storage/app/tenants/{tenant_id}/{module}/{filename}`.
   - Direct public access to tenant storage directories is prohibited. All downloads must flow through authenticated controller actions or cryptographically signed, short-lived temporary URLs verifying tenant context upon access.
4. **Search Index Partitioning (Scout / Meilisearch):**
   - Search indexes must either be dedicated per tenant (`tenants_{tenant_id}_products`) or enforce a mandatory filter attribute `tenant_id = {tenant_id}` configured as a filterable attribute in index settings.

---

### 5.6 Asynchronous Processing, Events, and Scheduled Tasks

1. **Queue Context Propagation:**
   - Every queued job dispatched in a tenant context must implement `TenantAwareJobInterface` and include the scalar `tenant_id` in its payload.
   - Queued jobs must run through a dedicated `TenantAwareQueueMiddleware` that resolves and initializes `TenantContext` on the worker prior to job execution, and safely resets it upon completion.
2. **Event & Notification Boundaries:**
   - Domain events must encapsulate the `tenant_id`.
   - Event listeners executing asynchronously must inherit the tenant context from the dispatched event.
3. **Scheduled Tasks (`schedule:run`):**
   - Scheduled commands executing per-tenant business logic (e.g., billing renewal, automated invoicing) must iterate through active tenants explicitly, wrapping each tenant's batch in `TenantContext::executeAs()`:
     ```php
     Tenant::active()->chunkById(100, function (Collection $tenants) {
         foreach ($tenants as $tenant) {
             TenantContext::executeAs($tenant, function () use ($tenant) {
                 $this->billingService->processDailyRenewals();
             });
         }
     });
     ```

---

### 5.7 Administrative Access and Audited Cross-Tenant Operations

1. **Administrative Impersonation & Cross-Tenant Access:**
   - Cross-tenant data access is strictly forbidden for regular users.
   - Platform administrators may only access tenant state via explicitly authorized operations requiring:
     - A verified administrative role with `manage-tenants` permission.
     - An explicit business reason recorded in an immutable audit log.
     - A bounded, time-limited impersonation session.
2. **Cross-Tenant Audit Trail:**
   - Every cross-tenant query, impersonated request, or administrative modification must emit a high-severity security audit event containing:
     - `operator_id` (admin user ID).
     - `target_tenant_id`.
     - `action` (e.g., `tenant.impersonate`, `tenant.data_export`).
     - `justification` (ticket/incident reference).
     - `ip_address` and `user_agent`.

---

## 6. Implementation Procedures and Practices

### 6.1 Tenant Context Service Contract & Implementation

```php
<?php

declare(strict_types=1);

namespace App\Core\Tenancy\Context;

use App\Core\Tenancy\Exceptions\TenantContextException;
use App\Domain\Tenant\Models\Tenant;
use Throwable;

final class TenantContext
{
    private ?Tenant $activeTenant = null;

    public function initialize(Tenant $tenant): void
    {
        if ($this->activeTenant !== null && $this->activeTenant->id !== $tenant->id) {
            throw new TenantContextException(
                sprintf('TenantContext already initialized with ID [%s]; cannot overwrite with [%s] without explicit reset.',
                    $this->activeTenant->id,
                    $tenant->id
                )
            );
        }

        if (! $tenant->isActive()) {
            throw new TenantContextException(
                sprintf('Cannot initialize TenantContext with inactive tenant [%s]. Status: [%s].',
                    $tenant->id,
                    $tenant->status
                )
            );
        }

        $this->activeTenant = $tenant;
    }

    public function get(): Tenant
    {
        if ($this->activeTenant === null) {
            throw new TenantContextException('Operation requires an active TenantContext, but none is set.');
        }

        return $this->activeTenant;
    }

    public function id(): string
    {
        return $this->get()->id;
    }

    public function check(): bool
    {
        return $this->activeTenant !== null;
    }

    public function reset(): void
    {
        $this->activeTenant = null;
    }

    /**
     * Safely execute a callback under a specific tenant context and restore the previous state.
     *
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     */
    public function executeAs(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->activeTenant;
        $this->activeTenant = null;
        $this->initialize($tenant);

        try {
            return $callback();
        } finally {
            $this->activeTenant = $previousTenant;
        }
    }
}
```

---

### 6.2 Eloquent Global Scope and `BelongsToTenant` Trait

```php
<?php

declare(strict_types=1);

namespace App\Core\Tenancy\Scopes;

use App\Core\Tenancy\Context\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function __construct(
        private readonly TenantContext $context
    ) {}

    /**
     * Apply the tenant constraint to a given Eloquent query builder.
     *
     * @param Builder<Model> $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! $this->context->check()) {
            // In maintenance/CLI operations without context, block access by default to prevent leakage
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), '=', $this->context->id());
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Core\Tenancy\Traits;

use App\Core\Tenancy\Context\TenantContext;
use App\Core\Tenancy\Scopes\TenantScope;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        $context = app(TenantContext::class);
        static::addGlobalScope(new TenantScope($context));

        static::creating(function (Model $model) use ($context): void {
            if (! $context->check()) {
                throw new LogicException(
                    sprintf('Cannot create tenant-scoped model [%s] without an active TenantContext.', static::class)
                );
            }

            $currentTenantId = $model->getAttribute('tenant_id');
            if ($currentTenantId !== null && (string) $currentTenantId !== (string) $context->id()) {
                throw new LogicException(
                    sprintf('Tenant ID mismatch on [%s]: provided [%s] does not match active context [%s].',
                        static::class,
                        $currentTenantId,
                        $context->id()
                    )
                );
            }

            $model->setAttribute('tenant_id', $context->id());
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('tenant_id')) {
                throw new LogicException(
                    sprintf('Cannot reassign tenant_id on existing model [%s].', static::class)
                );
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
```

---

### 6.3 HTTP Tenant Identification Middleware

```php
<?php

declare(strict_types=1);

namespace App\Core\Tenancy\Middleware;

use App\Core\Tenancy\Context\TenantContext;
use App\Domain\Tenant\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EnforceTenantContextMiddleware
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Resolve tenant identity strictly from authenticated user state
        if ($user !== null && isset($user->tenant_id)) {
            $tenant = Tenant::query()->find($user->tenant_id);

            if ($tenant === null) {
                throw new NotFoundHttpException('Assigned tenant entity does not exist.');
            }

            if (! $tenant->isActive()) {
                throw new AccessDeniedHttpException('Assigned tenant account is suspended or inactive.');
            }

            $this->tenantContext->initialize($tenant);
        } else {
            // Unauthenticated routes requiring tenant resolution must use verified subdomains
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0] ?? null;

            if ($subdomain === null || $subdomain === 'www' || $subdomain === 'api') {
                throw new NotFoundHttpException('Tenant identifier could not be resolved from route.');
            }

            $tenant = Tenant::query()->where('slug', $subdomain)->first();

            if ($tenant === null || ! $tenant->isActive()) {
                throw new NotFoundHttpException('Specified tenant domain not found or inactive.');
            }

            $this->tenantContext->initialize($tenant);
        }

        try {
            return $next($request);
        } finally {
            // Ensure no lingering context across request cycles in long-running processes (Octane)
            $this->tenantContext->reset();
        }
    }
}
```

---

### 6.4 Queue Worker Tenant Context Middleware

```php
<?php

declare(strict_types=1);

namespace App\Core\Tenancy\Queue;

use App\Core\Tenancy\Context\TenantContext;
use App\Domain\Tenant\Models\Tenant;
use Closure;
use RuntimeException;

final class SetTenantContextMiddleware
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @param object $job
     * @param Closure(object): void $next
     */
    public function handle(object $job, Closure $next): void
    {
        if (! property_exists($job, 'tenantId') || empty($job->tenantId)) {
            throw new RuntimeException(
                sprintf('Queued job [%s] must define a non-empty public string $tenantId property.', get_class($job))
            );
        }

        $tenant = Tenant::query()->find($job->tenantId);

        if ($tenant === null || ! $tenant->isActive()) {
            throw new RuntimeException(
                sprintf('Cannot process job [%s] for missing or inactive tenant ID [%s].',
                    get_class($job),
                    $job->tenantId
                )
            );
        }

        $this->tenantContext->initialize($tenant);

        try {
            $next($job);
        } finally {
            $this->tenantContext->reset();
        }
    }
}
```

---

## 7. Prohibited Practices and Anti-Patterns

1. **Exposing `tenant_id` in Mass-Assignment `$fillable` Arrays:**
   - *Violation:* Placing `'tenant_id'` in `$fillable` allows malicious actors to supply `tenant_id` in HTTP PUT/POST payloads, reassigning entity ownership across tenants.
   - *Rule:* `tenant_id` must NEVER be fillable. It is set automatically by the `BelongsToTenant` trait.
2. **Deriving Tenant Context from Unsigned Client Headers or Query Strings:**
   - *Violation:* Trusting an incoming `X-Tenant-ID: 123` header or `?tenant_id=123` parameter on authenticated requests allows trivial tenant impersonation.
   - *Rule:* Authenticated routes must strictly resolve tenant context from the authenticated user record or cryptographically signed session.
3. **Using `withoutGlobalScopes()` in Feature Code:**
   - *Violation:* Stripping global scopes to perform queries bypasses tenant isolation and exposes all records in the database.
   - *Rule:* `withoutGlobalScopes()` is strictly banned outside authorized, tested internal maintenance utilities.
4. **Shared Cache Key Namespaces without Tenant Prefixing:**
   - *Violation:* Writing `Cache::put('user_settings_' . $userId, $settings)` allows Tenant A to overwrite or read settings of Tenant B if user IDs collide.
   - *Rule:* All cache keys must be explicitly formatted as `tenant:{tenant_id}:{key}` or utilize tenant cache tags.
5. **Static Context Leakage in Worker / Octane Lifecycles:**
   - *Violation:* Retaining tenant context in static class variables without cleaning it up in a `finally` block causes subsequent jobs/requests to execute under the preceding tenant's identity.
   - *Rule:* Context teardown (`reset()`) is mandatory in middleware `finally` blocks.

---

## 8. Security, Isolation, and Advanced Operational Scenarios

### 8.1 Tenant Provisioning Workflow
Tenant provisioning must execute within a resilient, atomic lifecycle:
1. **Validation & Reservation:** Verify tenant domain/slug availability using unique constraints.
2. **Relational Record Creation:** Create `Tenant` record in `status = 'provisioning'` within a database transaction.
3. **Storage Allocation:** Provision isolated filesystem directories (`storage/app/tenants/{tenant_id}/`).
4. **Seed Baseline State:** Seed mandatory tenant roles, permissions, and default system configurations.
5. **Activation & Event Broadcast:** Transition status to `'active'`, commit the transaction, and dispatch `TenantProvisionedEvent`.
6. **Rollback on Failure:** If any provisioning step throws an exception, all database records must be rolled back and any created filesystem directories recursively cleaned.

### 8.2 Tenant Deprovisioning & Cryptographic Shredding
To comply with GDPR and enterprise data governance:
1. **Suspension Phase:** Transition status to `'suspended'`. Deny all login, API, and background job processing. Data is retained read-only for a configurable retention window (e.g., 30 days).
2. **Deprovisioning Phase:** Transition status to `'deprovisioned'`.
3. **Data Purge / Shredding:**
   - Cascade delete all database records referencing `tenant_id` within transactional chunks.
   - Delete all tenant cache keys via `Cache::tags(['tenant:' . $id])->flush()`.
   - Purge tenant filesystem directory recursively.
   - Retain an immutable audit ledger entry documenting tenant deletion date, authorizing admin, and completion confirmation.

### 8.3 Noisy-Neighbor Prevention and Rate Limiting
1. **Tenant-Level Rate Limiting:**
   Configure Redis-backed rate limiters partitioned by tenant ID to prevent a single tenant from starving system capacity:
   ```php
   RateLimiter::for('api', function (Request $request) {
       $tenantId = app(TenantContext::class)->check()
           ? app(TenantContext::class)->id()
           : $request->ip();

       return Limit::perMinute(600)->by($tenantId);
   });
   ```
2. **Dedicated Queue Allocation for Heavy Tenants:**
   High-volume tenants must have their background workloads routed to segregated queues (`exports-high-volume`, `webhooks-dedicated`) so standard tenant queues remain responsive.

---

## 9. Testing and Verification

Every multi-tenant service must implement comprehensive automated verification proving isolation integrity:

### 9.1 Cross-Tenant Adversarial Tests
Write automated tests demonstrating that Tenant A cannot access, modify, or delete Tenant B's data under any condition:
```php
public function test_tenant_a_cannot_access_tenant_b_order(): void
{
    $tenantA = Tenant::factory()->create(['status' => 'active']);
    $tenantB = Tenant::factory()->create(['status' => 'active']);

    $userA = User::factory()->forTenant($tenantA)->create();
    $orderB = Order::factory()->forTenant($tenantB)->create();

    $this->actingAs($userA)
        ->getJson('/api/v1/orders/' . $orderB->id)
        ->assertStatus(404); // Must return 404, not 403, to avoid leaking existence
}

public function test_tenant_id_cannot_be_overwritten_via_payload(): void
{
    $tenantA = Tenant::factory()->create(['status' => 'active']);
    $tenantB = Tenant::factory()->create(['status' => 'active']);
    $userA = User::factory()->forTenant($tenantA)->create();

    $this->actingAs($userA)
        ->postJson('/api/v1/orders', [
            'tenant_id' => $tenantB->id, // Malicious injection attempt
            'amount_cents' => 5000,
        ])
        ->assertStatus(201);

    $createdOrder = Order::latest()->firstOrFail();
    $this->assertSame($tenantA->id, $createdOrder->tenant_id, 'Tenant ID must match session, not payload.');
}
```

### 9.2 Context Isolation & Reset Verification
```php
public function test_tenant_context_resets_cleanly(): void
{
    $context = new TenantContext();
    $tenant = Tenant::factory()->create(['status' => 'active']);

    $context->initialize($tenant);
    $this->assertTrue($context->check());

    $context->reset();
    $this->assertFalse($context->check());
    $this->expectException(TenantContextException::class);
    $context->get();
}
```

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Detection Signal | Engineering Prevention Rule |
| :--- | :--- | :--- | :--- |
| **Cross-Tenant IDOR Leak** | Controller queries entity by ID without checking tenant ownership. | Security audit finding, 200 OK on foreign entity ID. | Enforce `BelongsToTenant` global scope and policy validation on every model. |
| **Mass-Assignment Tenant Hijacking** | `tenant_id` present in `$fillable` array. | User updates entity and sets `tenant_id` to another account. | Exclude `tenant_id` from `$fillable`; set automatically in `creating` hook. |
| **Cross-Job Memory Contamination** | Queue worker does not reset `TenantContext` between jobs. | Job for Tenant B executes under Tenant A's context. | Implement `TenantAwareQueueMiddleware` with mandatory `finally { reset(); }`. |
| **Cache Collision Between Tenants** | Cache key does not incorporate `tenant_id`. | Tenant B observes cached data generated by Tenant A. | Prefix all cache keys with `tenant:{id}:` or enforce tagged caching. |
| **Bypassed Scope in Raw SQL** | Developer uses `DB::table('orders')` omitting `where('tenant_id')`. | Query returns records belonging to multiple tenants. | Static analysis lint rule prohibiting raw queries without tenant constraint. |
| **Orphaned File Exposure** | Files stored in a shared flat directory with guessable names. | Unauthenticated user accesses another tenant's invoice PDF. | Store files in `tenants/{tenant_id}/` and serve via signed temporary URLs. |

---

## 11. Acceptance Criteria

Before approving any deliverable involving multi-tenant architecture, verify:
- [ ] Every tenant-partitioned database table includes an indexed `tenant_id` column.
- [ ] All compound unique constraints incorporate `tenant_id` as a composite column.
- [ ] All tenant models utilize `BelongsToTenant` trait and apply `TenantScope`.
- [ ] The `tenant_id` column is strictly omitted from Eloquent `$fillable` arrays.
- [ ] No feature code utilizes `withoutGlobalScope(TenantScope::class)` or `withoutGlobalScopes()`.
- [ ] Raw queries (`DB::table`) on tenant tables explicitly include `where('tenant_id', ...)`.
- [ ] Authenticated endpoints resolve tenant identity strictly from user session/token, never from request payload or unverified headers.
- [ ] Cache keys, Redis data, and filesystem paths strictly enforce tenant namespaces.
- [ ] Queued jobs carry scalar `tenant_id` and execute under `SetTenantContextMiddleware`.
- [ ] Automated tests include negative cross-tenant access assertions (404/403).

---

## 12. Cross-Skill Integration, References, and Related Skills

### 12.1 Cross-Skill Integration Architecture
This skill operates as a unified triad with **SKILL-QUEUE-001** and **SKILL-ERR-001**:
- **With SKILL-QUEUE-001 (Queue Architecture):** Multi-tenancy provides the trusted `tenant_id` context that is serialized into queue payloads. The queue system safely propagates this context across worker processes, guaranteeing that async tasks execute under strict tenant isolation.
- **With SKILL-ERR-001 (Observability):** Multi-tenancy binds `tenant_id` into `Log::withContext()`. The observability system attaches tenant metadata to logs, traces, and metrics while enforcing strict data redaction so tenant-sensitive information is never exposed across operational dashboards.

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
  - `SKILL-QUEUE-001` (Queue and Asynchronous Architecture Standards)
  - `SKILL-ERR-001` (Exception Handling, Telemetry and Observability Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
- **External Standards:**
  - MySQL 8.4 Reference Manual: Partitioning & Multi-Tenant Relational Schema Design
  - OWASP Top 10: Broken Object Level Authorization (BOLA / IDOR) Mitigation
  - Laravel 11.x Documentation: Eloquent Scopes, Route Model Binding, and Authentication

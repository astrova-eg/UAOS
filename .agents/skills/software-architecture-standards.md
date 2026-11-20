# Shared Engineering Skill: Software Architecture, DDD and Modular Standards

**Skill ID:** SKILL-ARCH-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Software Architecture, DDD and Modular Standards** skill establishes the authoritative enterprise discipline for designing, structuring, evaluating, and governing software architectures across Universal Core and modular Laravel applications.

Its primary purpose is to preserve structural boundaries, enforce strict dependency direction, isolate business domain logic from volatile framework and infrastructure mechanisms, prevent architectural drift, and enable high system evolvability and maintainability over multi-year enterprise lifecycles.

This skill equips software architects, system designers, and engineering agents with the technical depth required to make principled architectural decisions, evaluate trade-offs, model complex domains using Domain-Driven Design (DDD), govern modular monoliths and distributed services, and verify architecture integrity via automated fitness functions.

---

## 2. Scope and Applicability

This standard governs all structural and architectural aspects of applications, including:
- **System Topologies:** Monoliths, Modular Monoliths, Hexagonal/Ports & Adapters, Clean Architecture, and Distributed Event-Driven micro-topologies.
- **Domain Modeling:** Bounded Contexts, Aggregates, Entities, Value Objects, Domain Events, Domain Services, Repositories, and Anti-Corruption Layers (ACL).
- **Communication & Integration:** Synchronous API contracts, Asynchronous Messaging, Eventual Consistency, Transactional Outbox, and Saga orchestration/choreography.
- **Framework Integration:** Boundary demarcation between Laravel framework plumbing (HTTP controllers, routing, Eloquent ORM, console commands) and core enterprise domain logic.
- **Architectural Governance:** Architecture Decision Records (ADRs), fitness functions, dependency rules, modular boundaries, and technical debt remediation.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified competencies across the six architectural knowledge layers:

1. **Foundations of Architectural Topologies:**
   - Deep mechanics of Layered, Hexagonal (Ports & Adapters), Onion, Clean, and Modular Monolith architectures.
   - Trade-offs between monolithic cohesion and microservice operational overhead (network fallacies, distributed transactions, partial failures).
2. **Domain-Driven Design (Strategic & Tactical):**
   - Strategic: Ubiquitous Language, Bounded Context mapping, Subdomains (Core, Supporting, Generic), Context Maps (Shared Kernel, Customer-Supplier, Conformist, ACL).
   - Tactical: Aggregate design and transactional boundaries, entities vs value objects, domain invariants, domain events, domain service orchestration.
3. **CQRS & Event Sourcing Mechanics:**
   - Command-Query Responsibility Segregation (CQRS) read/write model separation, projection lifecycles, and synchronization latency.
   - Event Sourcing storage, immutable event streams, snapshotting, schema versioning, projection replay, and operational complexity limits.
4. **Distributed Systems & Concurrency Mechanics:**
   - Fallacies of Distributed Computing, CAP theorem, PACELC theorem, strong vs eventual consistency models.
   - Dual-write problems and distributed consensus; Transactional Outbox pattern, Idempotent Consumers, Saga execution coordinators.
5. **Laravel Internal Architecture & Extensibility:**
   - Application service container lifecycle, deferred service providers, contextual binding, pipeline pattern, and framework extension points without vendor modification.
   - Decoupled Action pattern vs fat controller/god model patterns.
6. **Architecture Fitness & Governance:**
   - Static dependency analysis tools (Pest Arch, Deptrac, PHPStan architecture extensions), structural metric evaluation (afferent/efferent coupling, instability, abstractness, distance from main sequence).

---

## 4. Core Engineering Principles

### 4.1 The Stable Dependencies & Abstractions Principles
1. **The Dependency Inversion Principle (DIP):** High-level domain modules must not depend on low-level infrastructure modules; both must depend on abstractions. Abstractions must not depend on details; details must depend on abstractions.
2. **Stable Abstractions Principle (SAP):** The stability of a module is directly proportional to its abstractness. Core domain policies must reside in stable, highly abstract interfaces; volatile infrastructure implementations must be concrete and unstable.
3. **Acyclic Dependencies Principle (ADP):** The dependency structure between modules, packages, and bounded contexts must be a Directed Acyclic Graph (DAG). Circular dependencies between domains or packages are strictly prohibited.

### 4.2 Single Responsibility & Cohesion at Scale
- **High Cohesion, Low Coupling:** Components within a module or aggregate must change together for the same business reasons. Coupling between separate modules must be minimal and strictly mediated through public API contracts or value objects.
- **Explicit Boundaries Over Shared State:** Direct database-level joins across separate bounded contexts or modules are prohibited. Modules must interact solely through published contracts, DTOs, or domain events.

### 4.3 Pragmatic Architecture & Overengineering Prevention
- **The Rule of Three & Cost of Indirection:** Do not introduce layers of abstraction, intermediate adapters, or distributed boundaries prematurely. A pattern must solve an existing, measurable complexity problem, not a speculative future requirement.
- **YAGNI (You Aren't Gonna Need It):** Start with a well-structured Modular Monolith before decomposing into distributed microservices. Microservices are a tool for organizational and scaling scale-out, not a default starting point.

### 4.4 The 10-Step Architectural Decision Framework
Every structural change, boundary modification, or architectural pattern adoption must follow this repeatable ten-step procedure:

```
Step 1: Understand the Business Driver & Problem Statement
Step 2: Identify System Context, Scale, and Operational Constraints
Step 3: Analyze Module Boundaries, Data Ownership, and Dependencies
Step 4: Identify Functional and Non-Functional Quality Requirements
Step 5: Formulate at Least Two Viable Alternative Architectures
Step 6: Evaluate Trade-offs, Complexity Cost, and Failure Modes
Step 7: Select Approach and Author Architecture Decision Record (ADR)
Step 8: Define Implementation Boundaries, Ports, and Contracts
Step 9: Define Automated Verification Criteria (Fitness Functions & Tests)
Step 10: Implement, Verify Against Quality Gates, and Monitor Drift
```

---

## 5. Mandatory Standards

### 5.1 Universal Code Governance (Universal Core Rule)
- **300-Line Physical Limit:** No architectural class, domain aggregate, service, or interface file may exceed 300 physical lines. Files exceeding this limit violate Single Responsibility and must be decomposed into cohesive collaborating units.
- **Strict Typing:** Every PHP file must declare `declare(strict_types=1);` with full parameter and return type declarations.
- **Final by Default:** All domain classes, value objects, actions, and services must be declared `final` unless explicitly designed for extension.

### 5.2 System Decomposition & Module Boundaries
1. **Directory Structure:** Enterprise Laravel applications must be structured by Domain / Bounded Context, not by technical role:
   ```
   app/
   ├── Domain/                 # Pure Enterprise Business Logic (No Laravel Dependencies)
   │   └── Billing/
   │       ├── Contracts/      # Ports (Interfaces defined by Domain)
   │       ├── Events/         # Domain Events (Immutable)
   │       ├── Exceptions/     # Domain-Specific Exceptions
   │       ├── Models/         # Aggregates, Entities, Value Objects
   │       └── Services/       # Pure Domain Services
   ├── Application/            # Use Case Orchestration & Workflow
   │   └── Billing/
   │       ├── Actions/        # Single-purpose Command/Use-case Handlers
   │       ├── DTOs/           # Input/Output Data Transfer Objects
   │       └── Queries/        # Read-model Query Handlers (CQRS)
   └── Infrastructure/         # Technical Adapters & External Implementations
       └── Billing/
           ├── Adapters/       # Stripe/Paddle implementations of Domain Ports
           ├── Persistence/    # Eloquent Models, Repositories, Migrations
           └── Providers/      # BillingDomainServiceProvider
   ```
2. **Dependency Direction Enforcement:**
   - `Domain` layer depends on NOTHING external (no Laravel facade, no Eloquent base class, no HTTP classes).
   - `Application` layer depends only on `Domain`.
   - `Infrastructure` layer depends on `Domain` and `Application` (implements Ports).
   - `UI / HTTP / Console` layer depends on `Application` and `Domain` DTOs.

### 5.3 Domain-Driven Design (DDD) Standards
1. **Aggregate Root Boundaries:**
   - Aggregates represent the consistency boundary for transactions. Internal entities of an aggregate can only be mutated through the Aggregate Root.
   - Outside objects may only hold references to the Aggregate Root identity, never to internal entities.
   - Transactions must not span multiple Aggregates. Cross-aggregate consistency must be achieved via Domain Events and eventual consistency.
2. **Entities vs Value Objects:**
   - Model concepts as **Value Objects** whenever identity is not required. Value Objects must be immutable, self-validating in constructor, and compared by value equality.
   - Entities must encapsulate business invariants. Public property setters (`setStatus()`, `setTotal()`) are strictly prohibited. State transitions must occur via explicit business intent methods (e.g., `cancel(CancellationReason $reason)`).
3. **Domain Events:**
   - Domain Events must be named in the past tense (`InvoiceSettled`, `OrderPlaced`).
   - Domain Events must be immutable and record the timestamp and aggregate identifier.

### 5.4 Repository Usage & When NOT to Introduce Repositories
1. **When Repositories are Prohibited:**
   - Simple CRUD operations and standard Eloquent queries in simple modules do NOT require a generic repository layer. Adding `InvoiceRepositoryInterface` that merely proxies `$this->model->find($id)` is an anti-pattern (Repository Obsession) that adds indirection without architectural value.
2. **When Repositories are Mandatory:**
   - Complex Aggregates with non-trivial lifecycle reconstitutions.
   - Core domain logic requiring complete decoupling from database persistence engines for hermetic unit testing.
   - Read models optimized through CQRS where raw SQL / DB queries outperform ORM hydration.

### 5.5 Distributed Architecture & Event Delivery
1. **Network Failure & Idempotency:**
   - Network calls fail. Every asynchronous consumer, queue job, and webhook handler must be strictly idempotent. Deduplication keys must be enforced at the persistence layer.
2. **Transactional Outbox Pattern:**
   - Direct publication of events to an external message broker (RabbitMQ, Kafka, AWS SQS) inside a database transaction is prohibited.
   - Applications must write the event to an `outbox_messages` table within the same local database transaction as the business state change. A background relay worker reads and publishes outbox messages.
3. **Saga Pattern vs Distributed Transactions:**
   - Two-Phase Commit (2PC) over distributed microservices is prohibited due to locking and availability penalties.
   - Cross-service workflows must use Sagas (orchestrated via an application workflow coordinator or choreographically via domain events) with explicit compensating actions for failures.

### 5.6 Architecture Decision Records (ADR)
Any structural change, module introduction, or significant pattern adoption requires an ADR in `docs/adr/` using the following mandatory format:
- **Number & Title:** `ADR-00X: Title`
- **Status:** Proposed | Accepted | Deprecated | Superseded
- **Context:** Problem statement, business drivers, constraints, alternatives considered.
- **Decision:** Chosen architecture with precise boundary definitions.
- **Consequences:** Positive benefits, negative trade-offs, and residual risks.

---

## 6. Implementation Procedures and Practices

### 6.1 Domain Aggregate Root with Invariant Protection and Domain Events
```php
<?php

declare(strict_types=1);

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Events\InvoiceSettled;
use App\Domain\Billing\Exceptions\InvoiceAlreadySettledException;
use App\Domain\Billing\Exceptions\InvalidInvoiceStateException;

final class Invoice
{
    /** @var list<object> */
    private array $recordedEvents = [];

    public function __construct(
        private readonly InvoiceId $id,
        private readonly TenantId $tenantId,
        private Money $amount,
        private InvoiceStatus $status,
        private ?\DateTimeImmutable $settledAt = null
    ) {}

    public function settle(TransactionReference $transactionRef, \DateTimeImmutable $occurredAt): void
    {
        if ($this->status === InvoiceStatus::Paid) {
            throw new InvoiceAlreadySettledException("Invoice {$this->id->value} is already settled.");
        }

        if ($this->status === InvoiceStatus::Void) {
            throw new InvalidInvoiceStateException("Cannot settle voided invoice {$this->id->value}.");
        }

        $this->status = InvoiceStatus::Paid;
        $this->settledAt = $occurredAt;

        $this->recordEvent(new InvoiceSettled(
            invoiceId: $this->id->value,
            tenantId: $this->tenantId->value,
            amountCents: $this->amount->amountCents,
            currency: $this->amount->currency,
            transactionReference: $transactionRef->value,
            occurredAt: $occurredAt
        ));
    }

    /** @return list<object> */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function id(): InvoiceId { return $this->id; }
    public function status(): InvoiceStatus { return $this->status; }
}
```

### 6.2 Application Action with Unit of Work and Outbox Relaying
```php
<?php

declare(strict_types=1);

namespace App\Application\Billing\Actions;

use App\Domain\Billing\Contracts\InvoiceRepositoryInterface;
use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Models\InvoiceId;
use App\Domain\Billing\Models\TransactionReference;
use App\Infrastructure\Outbox\Contracts\OutboxWriterInterface;
use Illuminate\Support\Facades\DB;

final readonly class SettleInvoiceAction
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices,
        private PaymentGatewayInterface $gateway,
        private OutboxWriterInterface $outbox
    ) {}

    public function execute(string $invoiceId): void
    {
        $id = new InvoiceId($invoiceId);
        $invoice = $this->invoices->findOrFail($id);

        // 1. External payment execution outside DB transaction
        $paymentResult = $this->gateway->charge($invoice->id(), $invoice->amount());

        // 2. Atomic persistence of state change + outbox events
        DB::transaction(function () use ($invoice, $paymentResult) {
            $invoice->settle(
                new TransactionReference($paymentResult->reference),
                new \DateTimeImmutable()
            );

            $this->invoices->save($invoice);

            foreach ($invoice->releaseEvents() as $event) {
                $this->outbox->write($event);
            }
        }, attempts: 3);
    }
}
```

### 6.3 Automated Architectural Fitness Testing (Pest Architecture)
```php
<?php

declare(strict_types=1);

// tests/Architecture/DomainBoundariesTest.php

test('domain layer must have zero dependencies on framework or infrastructure')
    ->expect('App\Domain')
    ->not->toUse([
        'Illuminate',
        'App\Infrastructure',
        'App\Application',
        'App\Http',
    ])
    ->ignoring([
        'Illuminate\Support\Collection', // If explicitly approved in ADR
    ]);

test('application layer depends only on domain')
    ->expect('App\Application')
    ->not->toUse([
        'App\Infrastructure',
        'App\Http',
    ]);

test('controllers must never directly access domain models or repositories')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'App\Domain\*\Models\*',
        'App\Domain\*\Contracts\*RepositoryInterface',
    ]);

test('all domain models and value objects must be final')
    ->expect('App\Domain\*\Models')
    ->toBeFinal();
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Bypassing Aggregate Root Boundaries.** Mutating internal entities directly via Eloquent queries without going through the Aggregate Root bypasses business invariant enforcement.
2. **PROHIBITED: Cross-Domain Database Joins.** Joining tables belonging to different Bounded Contexts couples schemas and prevents independent modular evolution.
3. **PROHIBITED: Synchronous Distributed HTTP Chaining.** Cascading synchronous HTTP calls between microservices creates tight coupling, latency amplification, and cascading failure vulnerability.
4. **PROHIBITED: Generic Repository Obsession.** Writing boilerplate repository interfaces that simply mirror Eloquent queries on simple CRUD models adds pointless indirection without architectural benefit.
5. **PROHIBITED: Direct Message Broker Dispatch Inside Local DB Transactions.** Publishing to Kafka/RabbitMQ inside `DB::transaction()` causes message loss or ghost messages upon transaction rollback (Dual-Write Anti-Pattern).
6. **PROHIBITED: Framework Entanglement in Pure Domain Models.** Extending `Illuminate\Database\Eloquent\Model` directly inside `App\Domain` binds core enterprise logic to the database ORM and Laravel lifecycle.
7. **PROHIBITED: Undocumented Architectural Changes.** Altering boundary rules, introducing new module relationships, or adopting new communication patterns without a peer-reviewed ADR is strictly blocked.

---

## 8. Security and Reliability Requirements

1. **Fault Isolation & Blast Radius Containment:** Module failures must fail closed and degrade gracefully. A failure in the Notification module must not prevent Invoice settlement.
2. **Contextual Anti-Corruption Layers (ACL):** When integrating with external third-party APIs or legacy subsystems, domain models must never consume external payloads directly. An explicit ACL adapter must translate external data into internal Value Objects.
3. **Correlation & Causation Identifiers:** Every asynchronous command and domain event must carry `correlation_id` and `causation_id` in its metadata to enable end-to-end distributed tracing across modules.
4. **Tenant Isolation at Boundary:** Multi-tenant boundaries must be validated at the entry port before domain dispatch. An Aggregate Root must never accept commands carrying mismatched `tenant_id` tokens.

---

## 9. Testing and Verification

### 9.1 Verification Hierarchy
1. **Architectural Fitness Tests (Automated CI Gate):** Pest Architecture or Deptrac runs on every pull request. Any layer violation or forbidden dependency fails the build immediately.
2. **Hermetic Domain Unit Tests:** Domain aggregates and value objects must be thoroughly tested in isolation without requiring database connections, external network mocks, or framework booting. Pure in-memory unit tests should achieve near-instantaneous execution (recommended reference target $\le 1\text{ ms} - 5\text{ ms}$ per test suite batch).
3. **Contract & Integration Tests:** Infrastructure adapters implementing domain ports must pass contract verification tests to guarantee behavioral equivalence.

### 9.2 Acceptance Quality Gate
Before submitting any architectural change:
- `pest --group=arch` executes with zero boundary violations.
- All domain classes adhere to the 300-line physical limit.
- ADR is authored, reviewed, and checked into `docs/adr/`.

---

## 10. Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Distributed Monolith** | Microservices sharing a database or relying on synchronous chained HTTP calls. | Enforce modular monolith first; ensure single-database ownership per bounded context. |
| **Entity Anemia & Leaky Invariants** | Domain models with public getters/setters; business logic placed in controllers. | Enforce rich domain models; validate state transitions inside aggregate methods. |
| **Dual-Write Inconsistency** | Modifying database and publishing to message queue in uncoordinated steps. | Implement Transactional Outbox Pattern with guaranteed at-least-once delivery. |
| **God Aggregate Contention** | Oversized aggregate encompassing too many entities, causing severe row-lock contention. | Shrink aggregate boundaries; limit aggregate to immediate consistency requirements. |
| **Circular Module Dependency** | Module A calls Module B, which calls Module A through direct class coupling. | Decouple via Domain Events, Shared Kernel value objects, or Dependency Inversion. |

---

## 11. Acceptance Criteria

Before approving any architectural deliverable or pull request:
- [ ] Code strictly respects layer boundaries: `Domain` has zero dependencies on `Infrastructure` or `Http`.
- [ ] All new domain classes, value objects, and actions are declared `final` and adhere to $\le 300$ physical lines.
- [ ] State mutations in Domain Aggregates enforce invariants and record past-tense Domain Events.
- [ ] No cross-boundary database joins exist across independent domains or bounded contexts.
- [ ] Asynchronous event publishing adheres to the Transactional Outbox Pattern; no queue dispatches inside DB transactions.
- [ ] Architectural fitness tests (`pest --group=arch`) pass with zero warnings or exceptions.
- [ ] Any architectural modification is accompanied by an approved Architecture Decision Record (ADR).

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-PERF-001` (Performance Profiling and Optimization Standards)
  - `SKILL-REVIEW-001` (Rigorous Code Review and Static Analysis Standards)
- **Authoritative Engineering Literature:**
  - Eric Evans: *Domain-Driven Design: Tackling Complexity in the Heart of Software* (Addison-Wesley)
  - Martin Fowler: *Patterns of Enterprise Application Architecture* & *Refactoring*
  - Vaughn Vernon: *Implementing Domain-Driven Design* (Addison-Wesley)
  - Gregor Hohpe & Bobby Woolf: *Enterprise Integration Patterns*
  - Microsoft Azure Architecture Center: *Cloud Design Patterns & Clean Architecture*
  - AWS Well-Architected Framework: *Reliability and Performance Efficiency Pillars*

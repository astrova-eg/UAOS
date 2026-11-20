# Shared Engineering Skill: Software Design and Design Principles Standards

**Skill ID:** SKILL-DESIGN-001  
**Version:** 1.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Software Design and Design Principles Standards** skill establishes the authoritative enterprise discipline for component, class, object, method, and application-level software design across Universal Core and modular Laravel applications.

Its primary purpose is to guide software engineers, designers, and autonomous agents in crafting highly cohesive, loosely coupled, resilient, and evolvable object-oriented and functional components. It provides deep, actionable engineering criteria for applying SOLID design principles, responsibility assignment heuristics (GRASP), encapsulation boundaries, Design by Contract, immutable state modeling, and classical/modern design patterns without falling into the traps of dogmatism, premature abstraction, or speculative overengineering.

---

## 2. Scope and Applicability

This standard governs component-level and class-level design decisions within application codebases:
- **Object-Oriented & Component Design:** Class responsibility assignment, method boundaries, information hiding, polymorphism, dynamic dispatch, and encapsulation.
- **Design Principles & Heuristics:** Practical trade-offs and pragmatic application of SOLID (SRP, OCP, LSP, ISP, DIP), GRASP (General Responsibility Assignment Software Patterns), Composition over Inheritance, Law of Demeter, and "Tell, Don't Ask".
- **State Modeling & Immutability:** Designing immutable Value Objects, state machines using PHP 8.3/8.4 backed enums, class invariant protection, and defensive copying.
- **Design by Contract (DbC):** Rigorous specification and enforcement of method preconditions, postconditions, and class invariants.
- **Design Patterns Taxonomy:** Practical selection criteria, structures, trade-offs, and failure modes for Creational, Structural, and Behavioral patterns (Strategy, Adapter, Decorator, Factory, State, Observer, Specification, Pipeline, and Proxy).
- **Framework Integration:** Clean mapping of modern design patterns to Laravel 11/12 primitives (Service Container bindings, Pipelines, Events, FormRequests, Actions) without compromising domain purity or creating artificial indirection.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified competencies across the six knowledge layers:

1. **Foundations of Object Orientation & Type Systems:**
   - Deep mechanics of dynamic dispatch, single dispatch polymorphism, interface inheritance vs implementation inheritance, and class initialization lifecycles in the PHP Zend Engine.
   - PHP 8.3/8.4 type system capabilities: intersection types (`A&B`), union types (`A|B`), disjunctive normal form (DNF) types (`(A&B)|C`), typed properties, readonly classes and properties, and return-type covariance / parameter-type contravariance.
2. **SOLID Principles Mechanics and Practical Nuance:**
   - *Single Responsibility Principle (SRP):* Understanding responsibility as "a reason to change" originating from a specific stakeholder/actor, rather than the misconception that a class must literally perform only one function.
   - *Open/Closed Principle (OCP):* Designing abstractions that permit behavioral extension via polymorphic composition or strategy injection without editing tested source files.
   - *Liskov Substitution Principle (LSP):* Mathematical behavioral subtyping: subtypes must preserve behavioral contracts, invariant strengths, and exception hierarchies of their supertypes.
   - *Interface Segregation Principle (ISP):* Designing cohesive, client-tailored role interfaces rather than monolithic "kitchen sink" contracts.
   - *Dependency Inversion Principle (DIP):* Ensuring high-level business policies do not depend on low-level infrastructure details; abstractions own the interface contracts.
3. **Responsibility Assignment (GRASP) Patterns:**
   - Information Expert (assigning responsibility to the class that owns the information), Creator, Controller, Low Coupling, High Cohesion, Polymorphism, Pure Fabrication, and Indirection.
4. **Design by Contract (DbC) and Invariant Modeling:**
   - Bertrand Meyer's contract mechanics: preconditions (caller obligations), postconditions (callee guarantees), and class invariants (unbreakable truths across all observable states).
5. **Design Patterns Selection & Trade-Off Forensics:**
   - Mastery of pattern trade-offs: recognizing when a pattern solves real structural tension versus when it merely adds cognitive indirection ("Enterprise Fizzbuzz").
   - Detailed structural mechanics of Strategy, Adapter, Decorator, State, Observer, Specification, Pipeline, Factory Method, and Abstract Factory.

---

## 4. Core Engineering Principles

### 4.1 Pragmatic Design Over Ideological Dogma
Design principles and patterns are cognitive heuristics for managing complexity, not moral mandates. Blindly applying every SOLID principle or design pattern to simple, stable CRUD operations introduces unnecessary abstraction layers, increases cognitive load, and slows development velocity. Balance elegance with simplicity; apply design rigor where business volatility and domain complexity justify the cost.

### 4.2 Composition Over Inheritance
Inheritance couples classes across implementation details, leading to the Fragile Base Class problem, rigid hierarchies, and unintended side effects. Prefer composition (delegating behavior to collaborating objects conforming to interfaces) over subclassing. Inheritance trees must not exceed two levels of depth.

### 4.3 Tell, Don't Ask & The Law of Demeter
1. **Tell, Don't Ask:** Instead of interrogating an object for its internal data and making decisions on its behalf from the outside, command the object to perform the operation itself. This preserves encapsulation and keeps logic adjacent to data.
2. **Law of Demeter (Principle of Least Knowledge):** A method of an object may only invoke methods of:
   - The object itself.
   - An argument passed to the method.
   - An object created within the method.
   - A direct collaborator held as an instance field.
   Avoid chained invocations that navigate deep object graphs (e.g., `$order->getCustomer()->getAddress()->getZipCode()`).

### 4.4 The 10-Step Software Design Decision Framework
Every class, component, or pattern introduction must follow this repeatable ten-step procedure:

```
Step 1: Identify Component Role & Single Reason to Change (SRP)
Step 2: Map Data Ownership & Invariants (Information Expert)
Step 3: Define Explicit Interfaces & Typed Contracts
Step 4: Evaluate Composition vs Direct Collaboration
Step 5: Define Method Preconditions, Postconditions, and Class Invariants (DbC)
Step 6: Evaluate Need for Behavioral or Creational Patterns (Trade-off Analysis)
Step 7: Check Cost of Indirection Against the Rule of Three & YAGNI
Step 8: Enforce Immutability on State Objects and Value Objects
Step 9: Design for Hermetic, Deterministic Unit Testability
Step 10: Verify Absence of Overengineering & Document Design Rationale
```

---

## 5. Mandatory Standards

### 5.1 Universal Code Governance (Universal Core Rule)
- **300-Line Physical Limit:** No class, interface, trait, or enum file may exceed **300 physical lines**. If a class approaches this limit, it is experiencing responsibility sprawl and must be decomposed along cohesive boundaries.
- **Strict Typing:** Every file must declare `declare(strict_types=1);` with explicit parameter, property, and return types.
- **Final by Default:** All concrete classes must be declared `final readonly` or `final` unless explicitly architected as an abstract template.

### 5.2 SOLID Application & Anti-Dogma Standards

#### 5.2.1 Single Responsibility Principle (SRP)
- **Standard:** A class must have only one reason to change, tied to a single business actor or concern (e.g., billing policy vs notification delivery vs database persistence).
- **Contextual Nuance:** Do not fracture cohesive domain operations into dozens of trivial single-method classes if they belong to the same business lifecycle. An `Order` aggregate coordinating its line items is cohesive, not a violation of SRP.

#### 5.2.2 Open/Closed Principle (OCP)
- **Standard:** Classes must be open for extension but closed for modification. When new business variations arise (e.g., new payment gateways, tax calculation methods, export formats), introduce a new strategy or adapter rather than modifying large conditional blocks in existing code.
- **Contextual Nuance:** Do not build speculative plugin architectures or abstract extension points for operations that have never changed and have no foreseeable variants (YAGNI).

#### 5.2.3 Liskov Substitution Principle (LSP)
- **Standard:** Subtypes or interface implementations must be fully substitutable for their base types without altering program correctness.
- **Mandatory Subtyping Rules:**
  - Preconditions cannot be strengthened in a subtype (a subtype cannot demand more than the parent).
  - Postconditions cannot be weakened in a subtype (a subtype must guarantee at least as much as the parent).
  - Subtypes must not throw unexpected checked/domain exceptions not defined by the contract.
  - Subtypes must not return dummy values (`return null;`) or throw `NotImplementedException` to ignore parent methods.

#### 5.2.4 Interface Segregation Principle (ISP)
- **Standard:** Clients should not be forced to depend on methods they do not use. Split fat interfaces into fine-grained role interfaces (e.g., `ExportableInterface`, `AuditableInterface`, `BillableInterface`).
- **Contextual Nuance:** Do not create a separate interface for every single method unless distinct clients genuinely consume them independently.

#### 5.2.5 Dependency Inversion Principle (DIP)
- **Standard:** High-level domain logic must depend on abstractions (interfaces), not concrete low-level infrastructure.
- **Contextual Nuance:** In standard Laravel application services, it is entirely appropriate to depend directly on framework value objects or stable utility classes (e.g., `CarbonImmutable`, `Collection`, `Stringable`). Inverting dependencies is mandatory for volatile infrastructure (payment APIs, mail services, storage providers, third-party integrations).

### 5.3 Encapsulation & Immutability Standards
1. **Immutable Value Objects:**
   - Any domain concept without independent identity (e.g., `Money`, `EmailAddress`, `TaxRate`, `DateRange`) must be modeled as an immutable Value Object (`final readonly class`).
   - Mutations must return new instances (`$money->add($otherMoney): Money`).
2. **Defensive Invariant Protection:**
   - Public properties with direct mutation access are strictly prohibited.
   - Invariants must be validated during object instantiation in the constructor. An object must never exist in an invalid state.

### 5.4 Design Patterns Catalog: Selection Criteria & Anti-Patterns

| Design Pattern | Problem Addressed | When to Apply | When Prohibited (Anti-Pattern) |
| :--- | :--- | :--- | :--- |
| **Strategy** | Multiple interchangeable algorithms or business calculations. | When 2+ algorithmic variants exist (e.g., tax strategies, payment methods) and switch/if statements are sprawling. | When only a single algorithm exists and no future variation is planned. |
| **Adapter** | Incompatible interface between third-party service and domain port. | When integrating external SDKs (Stripe, Twilio, AWS) into domain ports. | When wrapping standard PHP or framework utilities with identical signatures. |
| **Decorator** | Adding cross-cutting behavior (caching, logging, metering) dynamically. | When adding concerns to an interface implementation without modifying the core class. | When deep decorator nesting obscures execution flow and stack traces. |
| **State** | Entity behavior changes radically based on its internal state. | When an entity exhibits complex state-dependent behaviors and transitions (e.g., `Draft -> Submitted -> Approved -> Settled`). | When the state is a simple boolean flag or standard enum with 2-3 static branches. |
| **Observer / Events** | Decoupling primary business action from side effects. | When secondary actions (notifications, search indexing, audit logs) should not block or fail the primary transaction. | For synchronous in-line business logic where order of execution and transactional rollback are required. |
| **Factory Method** | Complex object construction with invariant validation. | When instantiating aggregates requiring complex reconstitution, polymorphic resolution, or dependency wiring. | For simple DTOs or Value Objects that can be instantiated directly via constructor. |
| **Specification** | Combinable business predicates for validation and querying. | When business rules must be reused across query filters, domain validations, and UI permissions. | For trivial one-off boolean checks. |
| **Pipeline** | Sequential data processing workflows. | Multi-stage request/data pipelines where each pipe performs a discrete, isolated transformation. | When simple sequential method calls in an Action class are clearer and shorter. |

---

## 6. Implementation Procedures and Practices

### 6.1 Immutable Value Object with Self-Validation and Invariant Protection (PHP 8.3+)
```php
<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $amountCents,
        public string $currency = 'USD'
    ) {
        if ($this->amountCents < 0) {
            throw new InvalidArgumentException("Money amount cannot be negative: {$this->amountCents}");
        }

        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException("Currency must be a 3-letter ISO code: {$this->currency}");
        }
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountCents + $other->amountCents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountCents - $other->amountCents, $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amountCents > $other->amountCents;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}");
        }
    }
}
```

### 6.2 Strategy Pattern with Strict Role Interface
```php
<?php

declare(strict_types=1);

namespace App\Domain\Tax\Contracts;

use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Tax\Models\TaxContext;

interface TaxCalculationStrategyInterface
{
    public function calculateTax(Money $subtotal, TaxContext $context): Money;
    public function supports(TaxContext $context): bool;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Domain\Tax\Strategies;

use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Tax\Contracts\TaxCalculationStrategyInterface;
use App\Domain\Tax\Models\TaxContext;

final readonly class EuropeanVatTaxStrategy implements TaxCalculationStrategyInterface
{
    public function supports(TaxContext $context): bool
    {
        return $context->isEuropeanUnion();
    }

    public function calculateTax(Money $subtotal, TaxContext $context): Money
    {
        $ratePercent = $context->vatRatePercentage();
        $taxCents = (int) round($subtotal->amountCents * ($ratePercent / 100));

        return new Money($taxCents, $subtotal->currency);
    }
}
```

### 6.3 State Pattern with PHP 8.3 Backed Enums and Explicit State Transitions
```php
<?php

declare(strict_types=1);

namespace App\Domain\Order\Enums;

use App\Domain\Order\Exceptions\InvalidStateTransitionException;

enum OrderState: string
{
    case Draft = 'draft';
    case Placed = 'placed';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Placed, self::Cancelled],
            self::Placed => [self::Paid, self::Cancelled],
            self::Paid => [self::Shipped],
            self::Shipped => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function transitionTo(self $target): self
    {
        if (!$this->canTransitionTo($target)) {
            throw new InvalidStateTransitionException(
                "Cannot transition order from {$this->value} to {$target->value}"
            );
        }

        return $target;
    }
}
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Feature Envy & Leaky Encapsulation.** Calling getter after getter on an external object to perform calculations that belong inside that object violates encapsulation and data ownership.
2. **PROHIBITED: Deep Inheritance Hierarchies.** Subclassing more than two levels deep leads to fragile base classes, high coupling, and difficult reasoning.
3. **PROHIBITED: Speculative Design Patterns (YAGNI Violations).** Introducing abstract factories, strategy resolvers, and dynamic proxies for single implementations with no business variation adds complexity without benefit.
4. **PROHIBITED: Mutable Public Properties.** Declaring non-readonly public properties on classes allows external callers to bypass invariant checks and corrupt object state.
5. **PROHIBITED: Train Wreck Chaining (Law of Demeter Violations).** Calling `$a->getB()->getC()->getD()->doSomething()` creates tight coupling across four classes; changes to `B` or `C` break unrelated callers.
6. **PROHIBITED: Weakening Contracts in Subtypes (LSP Violations).** Subclasses or interface implementers that throw `BadMethodCallException` or return null because they do not support a contract method violate behavioral subtyping.
7. **PROHIBITED: Fat Interfaces.** Forcing classes to implement interfaces containing methods they do not need creates dummy stub methods and brittle coupling.
8. **PROHIBITED: Exceeding 300 Physical Lines Per File.** Classes exceeding 300 lines invariably conflate multiple responsibilities and violate SRP.

---

## 8. Security and Reliability Requirements

1. **Defensive Immutability:** Value objects and DTOs passed across module boundaries must be immutable. This eliminates side effects, race conditions in multi-threaded runtimes (Octane), and state corruption.
2. **Precondition Fail-Fast:** Validate method preconditions at the earliest possible entry point using guard clauses and strong types. Never process invalid input partially before throwing an exception.
3. **Zero State Leakage in Reused Singletons:** Any component registered as a singleton in the Laravel container must be strictly stateless or declare immutable properties.

---

## 9. Testing and Verification

### 9.1 Verification Hierarchy
1. **LSP Contract Testing:** When multiple implementations of an interface exist, write an abstract contract test suite verifying that all implementers satisfy common postconditions and invariant behaviors.
2. **Hermetic Unit Tests for Domain Objects:** Value objects, state machines, and strategies must execute in-memory with zero mocks, zero database access, and 100% path coverage.
3. **Type Strictness Audit:** Static analysis (PHPStan Level 8+) must verify that return types and argument types are strictly compatible and non-null unless explicitly nullable.

---

## 10. Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **God Object Sprawl** | Incrementally adding helper methods to a central class until it exceeds 1,000 lines. | Enforce 300-line limit; separate concerns using SRP and Single-Purpose Action classes. |
| **Fragile Base Class Breakage** | Modifying a protected method in an abstract parent class, silently breaking 10 subclasses. | Replace inheritance with composition; inject collaborating strategies via interfaces. |
| **Invalid State Explosion** | Instantiating models with public setters, leaving required invariants uninitialized. | Validate all invariants inside `__construct()`; enforce immutable value objects. |
| **Enterprise Fizzbuzz Trap** | Over-abstracting simple logic with 5 design patterns, resulting in 20 files for a basic query. | Apply the Rule of Three; keep code concrete until 2+ distinct variations exist. |
| **Silent Contract Violation** | Subtype silently returns null or ignores arguments required by the base interface. | Enforce Liskov Substitution Principle via shared contract test suites. |

---

## 11. Acceptance Criteria

Before approving any software design deliverable:
- [ ] All code files strictly adhere to the $\le 300$ physical lines limit.
- [ ] `declare(strict_types=1);` is declared; classes are `final readonly` or `final` by default.
- [ ] Domain Value Objects are immutable and self-validating in constructors.
- [ ] SOLID principles are applied pragmatically with clear business justification for abstractions.
- [ ] Design patterns solve real complexity and avoid speculative indirection.
- [ ] Methods enforce preconditions via guard clauses and adhere to "Tell, Don't Ask".
- [ ] Law of Demeter is respected; no deep train-wreck object chaining.
- [ ] Contract tests or unit tests prove behavioral substitutability and invariant enforcement.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-ARCH-001` (Software Architecture, DDD and Modular Standards)
  - `SKILL-REVIEW-001` (Rigorous Code Review, Static Analysis and Refactoring Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
- **Authoritative Engineering Literature:**
  - Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides: *Design Patterns: Elements of Reusable Object-Oriented Software* (Addison-Wesley)
  - Martin Fowler: *Refactoring* & *Patterns of Enterprise Application Architecture*
  - Bertrand Meyer: *Object-Oriented Software Construction* (Design by Contract)
  - Craig Larman: *Applying UML and Patterns: An Introduction to Object-Oriented Analysis and Design and Iterative Development* (GRASP)
  - Robert C. Martin: *Agile Software Development, Principles, Patterns, and Practices*

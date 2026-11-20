# Shared Engineering Skill: Rigorous Code Review, Static Analysis and Refactoring Standards

**Skill ID:** SKILL-REVIEW-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Rigorous Code Review, Static Analysis and Refactoring Standards** skill defines the authoritative enterprise discipline for ensuring supreme code quality, enforcing structural elegance, conducting rigorous peer reviews, executing static analysis, and safely modernizing legacy systems across Universal Core and modular Laravel applications.

Its primary purpose is to eliminate technical debt, prevent regressions during code modifications, ensure adherence to software design principles (SOLID, Design by Contract, Immutability), enforce strict typing, detect code smells early, and maintain a sustainable codebase capable of rapid, reliable evolution.

This skill equips software engineers, code auditors, and autonomous agents with practical refactoring catalogs, characterization testing methodologies, static analysis configurations, and automated gating standards.

---

## 2. Scope and Applicability

This standard governs all source code evaluation, refactoring, and quality gating across the codebase:
- **Code Quality Dimensions:** Readability, semantic clarity, type safety, cognitive complexity, naming consistency, and maintainability.
- **Static Analysis & Tooling:** PHPStan / Larastan (Level 8+), Psalm, Rector automated refactoring rules, and Laravel Pint / PSR-12 code style formatting.
- **Refactoring & Modernization:** Safe, incremental transformation of legacy, monolithic, or untyped code into modular, decoupled, and testable components without altering observable runtime behavior.
- **Design Principles & Patterns:** Practical application and trade-off evaluation of SOLID principles, Design by Contract, Immutability, Composition over Inheritance, Strategy, Specification, Pipeline, Adapter, Factory, and Decorator patterns.
- **Code File Governance:** Enforcement of the Universal Core maximum 300 physical lines per file rule and structural anti-fragmentation boundaries.
- **Peer Review Governance:** Reviewer independence, automated verification requirements, and definition of done gating before merge.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified competencies across the six knowledge layers:

1. **Static Analysis Mechanics & Type Systems:**
   - Abstract Syntax Tree (AST) inspection, symbol table resolution, type inference algorithms, union/intersection types, and taint analysis for security vulnerability detection.
   - PHPStan / Larastan configuration, custom baseline management, extension writing, and generic type annotations (`@template`, `@param list<T>`, `@return non-empty-string`).
2. **Complexity Metrics & Code Health:**
   - Cyclomatic Complexity (number of linearly independent execution paths through source code) and Cognitive Complexity (mental effort required by an engineer to understand control flow).
   - Halstead complexity, NPath complexity, and depth of inheritance tree evaluation.
3. **Refactoring Mechanics & Safe Transformations:**
   - Behavior-preserving code transformations, micro-commit methodologies, and AST-level manipulation (Rector).
   - Characterization testing (Golden Master / Approval testing) techniques to establish safety nets around untested legacy code before touching implementation logic.
4. **Object-Oriented & Functional Software Design Principles:**
   - Deep trade-off analysis of SOLID (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion).
   - Design by Contract (preconditions, postconditions, and class invariants) and immutable state management.
5. **Detection & Eradication of Code Smells:**
   - Diagnostic criteria and surgical refactoring recipes for Long Method, God Class, Primitive Obsession, Feature Envy, Data Clumps, Divergent Change, Shotgun Surgery, and Speculative Generality.
6. **Code Review & Quality Gate Governance:**
   - Construction of automated pull request quality gates, mutation score thresholds, change risk assessment, and regression verification evidence.

---

## 4. Core Engineering Principles

### 4.1 Behavior Preservation as the Supreme Law
Refactoring is strictly defined as changing the internal structure of software to make it easier to understand and cheaper to modify without changing its observable external behavior. Adding features, changing database outputs, or altering API contracts during a refactoring task is strictly prohibited. Refactoring and feature development must always be separate commits.

### 4.2 Never Refactor Without a Safety Net
Attempting to refactor code without automated tests is an uncontrolled risk. Untested legacy code must first be locked down with Characterization Tests before any structural alteration is performed. If tests cannot run fast and deterministically, refactoring must halt until the harness is established.

### 4.3 Composition Over Inheritance & Shallow Hierarchies
Deep class inheritance hierarchies create rigid coupling, fragile base class problems, and unpredictable side effects. Favor composition, interfaces, and single-purpose traits/value objects over abstract inheritance trees. Class inheritance must not exceed two levels of depth.

### 4.4 Explicit Design by Contract & Immutability
All interfaces and public methods must establish unambiguous contracts. Validate preconditions at entry points using guard clauses and value objects. Prefer immutable objects: mutate state by returning new instances rather than modifying internal properties in-place.

### 4.5 The 10-Step Code Quality & Safe Refactoring Decision Framework
```
Step 1: Identify Code Smell, Complexity Hotspot, or Quality Defect
Step 2: Measure Current Metrics (Cognitive Complexity, Line Count, Type Coverage)
Step 3: Verify Existing Test Harness; Build Characterization Tests if Test Coverage < 80%
Step 4: Formulate Minimal Step-by-Step Transformation Plan (Micro-Refactorings)
Step 5: Apply Design Principles (Single Responsibility, Composition, Guard Clauses)
Step 6: Execute One Atomic Transformation at a Time (Micro-Commit)
Step 7: Run Automated Test Suite & Static Analysis After Every Micro-Commit
Step 8: Re-measure Metrics to Quantitatively Confirm Improvement
Step 9: Enforce 300-Line Governance and File Layout Consistency
Step 10: Produce Verification Evidence and Submit for Independent Review
```

---

## 5. Mandatory Standards

### 5.1 Universal Code Governance (Universal Core Rule)
1. **The 300-Line Physical Limit:**
   - No PHP code file (class, interface, trait, enum, action, service, or controller) may exceed **300 physical lines** (including docblocks and whitespace).
   - Any class approaching 300 lines is a warning indicator of responsibility dilution (God Class smell).
   - **Exception Governance:** Any exception above 300 lines requires specific technical justification, documented explanation of why decomposition is unsuitable, explicit prior human user approval, and a recorded exception scope and affected file. No agent, automated process, validator, or department manager may grant itself an exception. Approval for one file does not establish a general exception for other files. Unauthorized oversized files must be treated as non-compliant. Non-code artifacts follow their respective standards.
   - **Anti-Fragmentation Rule:** Do not artificially break cohesive code into absurd, unreadable single-line classes solely to satisfy the 300-line limit. Re-architect the module along natural business responsibility boundaries.
2. **Type Safety & Strictness:**
   - Every file must begin with `declare(strict_types=1);`.
   - All method arguments, return types, and class properties must have explicit, concrete types. `mixed` is prohibited unless interacting with un-typed third-party vendor code, in which case it must be narrowed immediately.
   - Untyped associative arrays (`array $data`) for structured domain payloads are strictly prohibited. Use typed Data Transfer Objects (DTOs) or readonly Value Objects.

### 5.2 Static Analysis & Quality Gate Standards

#### 5.2.1 Hard Quality & Correctness Invariants (Non-Negotiable)
1. **Strict Type Declaration:** Every source file must begin with `declare(strict_types=1);` and feature explicit parameter and return types on all functions and methods.
2. **PHPStan / Larastan Baseline (Level 8+):**
   - Zero static analysis errors tolerated on newly authored or refactored files at Level 8 (target Level 9/Max).
   - Suppressing errors via `@phpstan-ignore` is prohibited without a mandatory inline comment explaining the compiler limitation and linking to an issue tracker.
3. **No Untyped Arrays for Domain Payloads:** Public API boundaries, inter-module actions, and domain events must use typed DTOs or Value Objects.

#### 5.2.2 Context-Aware Complexity Thresholds & Decision Guidelines
Complexity metrics are diagnostic signals designed to highlight cognitive strain and defect density, rather than arbitrary universal laws. Thresholds must be calibrated against the inherent domain complexity of the component:

1. **Cognitive Complexity (Mental Effort Metric):**
   - **Recommended Baseline Target:** $\le 15$ per method for standard application services, actions, and controllers.
   - **Decision Criteria & Exemptions:** When a method naturally coordinates multi-variant business rules (e.g., cross-border VAT tax matrices, algorithmic parsing, or multi-step checkout validation), cognitive complexity may legitimately reach $\le 25$ provided the control flow is linear and readable. Forcing artificial class extraction solely to bring an 18 down to 14 creates "fragmentation debt" and increases overall system cognitive load.
   - **Remediation Trigger:** Any method with cognitive complexity $>25$, or $>15$ accompanied by deeply nested conditionals ($>3$ levels), must be decomposed using Guard Clauses, Strategy, or the Specification pattern.

2. **Cyclomatic Complexity (Execution Path Metric):**
   - **Recommended Baseline Target:** $\le 10$ per method for standard business logic.
   - **Decision Criteria & Exemptions:** Flat, cohesive dispatch structures (e.g., PHP `match` expressions mapping 15 enum states to handlers) have higher cyclomatic count but near-zero cognitive load. These are explicitly permitted without fragmentation.

3. **Method Parameter Count:**
   - **Recommended Baseline Target:** $\le 4$ parameters for public methods.
   - **Decision Criteria:** When a method requires 5 or more parameters, evaluate whether the parameters represent cohesive concepts (e.g., `street`, `city`, `state`, `zip`, `country`). If cohesive, encapsulate them into a **Parameter Object** or **DTO** (e.g., `AddressDTO`). If parameters represent disparate service dependencies in a constructor, evaluate whether the class has violated the Single Responsibility Principle. Constructors of aggregate factories assembling multi-attribute entities may accept $>4$ typed arguments where a DTO adds no architectural value.

### 5.3 Code Smells & Remediation Catalog

| Code Smell | Diagnostic Indicators | Root Impact | Mandatory Remediation Technique |
| :--- | :--- | :--- | :--- |
| **Long Method** | Method $>30$ lines; multiple levels of indentation; mixed levels of abstraction. | High cognitive load; impossible to test paths in isolation; defect magnet. | **Extract Method**, **Decompose Conditional**, replace inline algorithms with dedicated domain queries/actions. |
| **God Class / Large Class** | Class $>300$ lines; dozens of public methods; handles unrelated responsibilities. | High coupling; frequent merge conflicts; brittle regressions. | **Extract Class**, divide along Single Responsibility Principle, introduce Action classes. |
| **Primitive Obsession** | Using strings/integers for emails, currencies, IDs, tax numbers, phone numbers. | Scattered regex validations; invalid domain state representation; duplicated logic. | **Introduce Value Object** (e.g., `EmailAddress`, `Money`, `OrderId`) with self-validation in constructor. |
| **Feature Envy** | Method in Class A repeatedly invokes getters on Class B to calculate a result. | Poor encapsulation; logic resides outside data owner; high efferent coupling. | **Move Method** into Class B, or transform Class B into a rich entity that performs the computation. |
| **Shotgun Surgery** | A single business change requires editing 10 different files across the codebase. | Fragmented logic; easily missed update locations; high regression frequency. | **Move Methods & Fields** into a single cohesive module or aggregate root. |
| **Deep Conditionals** | Nested `if / else / switch` statements exceeding 3 levels of indentation. | High cyclomatic complexity; hidden edge-case bugs; difficult reasoning. | **Guard Clauses** (early return), **Replace Conditional with Polymorphism / Strategy**, or **Specification Pattern**. |

### 5.4 Software Design Patterns: Practical Application and Anti-Patterns
1. **Strategy Pattern:**
   - *Use Case:* Multiple interchangeable algorithms (e.g., tax calculation, shipping rate, discount algorithms).
   - *Requirement:* Define a concise interface; inject strategy via container or factory; avoid switch statements.
2. **Specification Pattern:**
   - *Use Case:* Complex, combinable business rules for querying or validating domain entities (e.g., `IsEligibleForLoan`, `IsOverdueInvoice`).
   - *Structure:* Chained logical operations (`and()`, `or()`, `not()`).
3. **Pipeline Pattern:**
   - *Use Case:* Multi-stage request/data processing workflows where each stage performs a discrete transformation (e.g., order processing pipeline).
   - *Implementation:* Leverage Laravel's `Illuminate\Pipeline\Pipeline` with typed payload contracts.
4. **Pattern Warning — Avoiding "Enterprise Fizzbuzz":**
   - Design patterns must never be introduced for their own sake. Do not create an `InvoiceSettlementStrategyFactoryDecorator` for a single, unchanging business operation. Simplicity and clarity trump clever abstraction.

### 5.5 Code Review Standards & Governance
1. **Reviewer Independence:** An author may never approve or merge their own pull request. At least one independent review by a qualified engineering agent or peer is mandatory.
2. **Review Evidence Requirement:** No review approval may be granted without attached test execution logs proving 100% test pass rate and static analysis clean run.
3. **Review Focus Priority:**
   - Priority 1: Correctness, security vulnerabilities, race conditions, edge-case coverage.
   - Priority 2: Architectural boundary preservation and domain invariants.
   - Priority 3: Maintainability, complexity metrics, and 300-line compliance.
   - Priority 4: Code styling (automated by Pint, never debated manually).

---

## 6. Implementation Procedures and Practices

### 6.1 Refactoring Legacy God Controller into Value Objects, Guard Clauses, and Single-Purpose Action

#### Before Refactoring (Code Smell: God Controller, Primitive Obsession, Deep Nesting)
```php
// ANTI-PATTERN: Untyped, 150-line controller method with deep conditionals and raw queries
public function updateSubscription(Request $request, $id) {
    $sub = Subscription::find($id);
    if ($sub != null) {
        if ($sub->status == 'active') {
            if ($request->has('plan') && $request->plan != '') {
                if ($request->plan == 'pro' || $request->plan == 'enterprise') {
                    // 50 lines of payment calculation, Stripe calls, and email sending
                }
            }
        }
    }
}
```

#### After Refactoring (Clean, Decoupled, Type-Safe, <300 Lines)

```php
<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Models;

use InvalidArgumentException;

final readonly class PlanType
{
    private const ALLOWED = ['starter', 'pro', 'enterprise'];

    public function __construct(public string $value)
    {
        if (!in_array(strtolower($this->value), self::ALLOWED, true)) {
            throw new InvalidArgumentException("Invalid subscription plan: {$this->value}");
        }
    }

    public function isEnterprise(): bool
    {
        return strtolower($this->value) === 'enterprise';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Application\Subscription\Actions;

use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use App\Domain\Subscription\Contracts\BillingGatewayInterface;
use App\Domain\Subscription\Exceptions\SubscriptionNotActiveException;
use App\Domain\Subscription\Models\PlanType;
use App\Domain\Subscription\Models\SubscriptionId;
use Illuminate\Support\Facades\DB;

final readonly class UpgradeSubscriptionAction
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptions,
        private BillingGatewayInterface $billingGateway
    ) {}

    public function execute(string $subscriptionId, string $newPlan): void
    {
        $id = new SubscriptionId($subscriptionId);
        $plan = new PlanType($newPlan);
        $subscription = $this->subscriptions->findOrFail($id);

        // Guard Clause: Fail fast
        if (!$subscription->isActive()) {
            throw new SubscriptionNotActiveException("Subscription {$subscriptionId} is not active.");
        }

        if ($subscription->currentPlan()->value === $plan->value) {
            return; // Idempotent no-op
        }

        DB::transaction(function () use ($subscription, $plan) {
            $this->billingGateway->updateSubscriptionPlan($subscription->billingToken(), $plan);
            $subscription->changePlan($plan);
            $this->subscriptions->save($subscription);
        });
    }
}
```

### 6.2 The Specification Pattern for Query & Rule Decomposition
```php
<?php

declare(strict_types=1);

namespace App\Domain\Customer\Specifications;

use App\Domain\Customer\Models\Customer;

interface CustomerSpecificationInterface
{
    public function isSatisfiedBy(Customer $customer): bool;
}

final readonly class HighValueCustomerSpecification implements CustomerSpecificationInterface
{
    public function __construct(private int $thresholdCents = 1000000) {}

    public function isSatisfiedBy(Customer $customer): bool
    {
        return $customer->lifetimeSpendCents() >= $this->thresholdCents
            && $customer->accountAgeDays() > 90
            && !$customer->hasUnresolvedDisputes();
    }
}
```

### 6.3 Building Characterization Tests for Untested Legacy Code
When refactoring legacy code without existing tests, capture current behavior using an Approval / Snapshot Characterization Test:

```php
<?php

declare(strict_types=1);

namespace Tests\Characterization\Legacy;

use Tests\TestCase;
use App\Legacy\PayrollCalculator;

final class PayrollCalculatorCharacterizationTest extends TestCase
{
    /** @test */
    public function it_matches_historical_golden_master_output(): void
    {
        $calculator = new PayrollCalculator();
        
        $testCases = [
            ['hourly_rate' => 25.0, 'hours' => 40, 'tax_state' => 'CA', 'exemptions' => 1],
            ['hourly_rate' => 45.0, 'hours' => 55, 'tax_state' => 'NY', 'exemptions' => 0],
            ['hourly_rate' => 15.0, 'hours' => 20, 'tax_state' => 'TX', 'exemptions' => 2],
        ];

        $results = [];
        foreach ($testCases as $case) {
            $results[] = $calculator->compute($case['hourly_rate'], $case['hours'], $case['tax_state'], $case['exemptions']);
        }

        // Compare against verified frozen snapshot
        $this->assertMatchesJsonSnapshot($results);
    }
}
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Exceeding 300 Lines Per File.** Classes $>300$ lines blur responsibility boundaries, encourage spaghetti dependencies, and degrade readability.
2. **PROHIBITED: Mixing Refactoring with Feature Development or Bug Fixes.** Bundling refactoring into feature PRs obscures functional regressions and makes git bisect impossible.
3. **PROHIBITED: Refactoring Without Characterization Tests.** Altering un-tested code based on assumptions guarantees silent regressions in production.
4. **PROHIBITED: Suppressing Static Analysis Without Documented Justification.** Blank `@phpstan-ignore` annotations hide genuine null pointer exceptions, undefined index accesses, and type mismatches.
5. **PROHIBITED: Self-Approval of Pull Requests.** Merge approvals must come from an independent reviewer to eliminate author cognitive blind spots.
6. **PROHIBITED: Deep Inheritance Hierarchies.** Subclassing beyond two levels creates the Fragile Base Class anti-pattern and tight coupling.
7. **PROHIBITED: Untyped Arrays for Domain Payloads.** Passing arbitrary associative arrays (`$data['amount']`) strips compile-time and static-time safety, inviting missing index errors.
8. **PROHIBITED: Premature Abstraction ("Speculative Generality").** Creating complex abstract factories or multi-layered interfaces for components with only one implementation adds friction without benefit.

---

## 8. Security and Reliability Requirements

1. **Taint Analysis & Input Sanitization:** Refactoring must never bypass existing input validation layers (e.g., migrating from FormRequests to raw request access).
2. **Defensive Invariant Validation:** Value objects and DTOs must enforce validity in constructors. Never allow an invalid object state to be constructed.
3. **Deterministic Exception Hierarchies:** Throw domain-specific, typed exceptions (e.g., `InvalidOrderStateException`) rather than generic `\Exception` or `\RuntimeException`. Ensure all exceptions are properly caught or mapped to API problem details.
4. **Zero State Mutation in Shared Singletons:** When refactoring service classes registered as singletons in Laravel, ensure they contain zero mutable instance properties to prevent state leakage across requests.

---

## 9. Testing and Verification

### 9.1 Verification Pipeline Gates
Every code modification must pass through these three gates prior to merge:
1. **Linting & Style:** `vendor/bin/pint --test` passes with 0 formatting warnings.
2. **Static Analysis:** `vendor/bin/phpstan analyse --level=8` completes with 0 errors.
3. **Test Suite & Mutation Safety:**
   - `php artisan test` passes with 100% success rate.
   - Mutation Testing via Infection (`vendor/bin/infection --min-msi=70`) confirms that tests effectively kill injected mutants in refactored code.

### 9.2 Peer Review Quality Checklist
Reviewers must verify that:
- [ ] No file exceeds 300 physical lines without formal exemption.
- [ ] Cognitive complexity adheres to established guidelines (recommended baseline $\le 15$; any method $>15$ has documented justification and clean linear flow).
- [ ] The change contains zero behavior alterations if labeled as refactoring.
- [ ] Strict types and return types are present on every new/modified function.

---

## 10. Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Big-Bang Refactoring Outage** | Refactoring thousands of lines across 50 files in a single un-segmented PR. | Require micro-refactorings; PRs must be small ($\le 400$ total lines changed), atomic, and continuously deployed. |
| **Silent Regression in Legacy Logic** | Modifying business calculation without characterization tests. | Golden Master / Characterization snapshot tests are mandatory before refactoring legacy code. |
| **Enterprise Fizzbuzz Indirection** | Over-abstracting simple logic with 6 design patterns, making code unnavigable. | YAGNI; follow the Rule of Three; keep abstractions minimal until multiple variations exist. |
| **Singleton State Leakage** | Adding mutable state to a service class that is reused across multiple HTTP requests (Octane / Long-Running). | Enforce stateless services or mark classes readonly; inject state via method parameters. |
| **Broken Serialized State** | Renaming properties on queued jobs or cache payloads during refactoring without backward-compatible unpickling. | Maintain backward-compatible deserialization aliases or flush caches/queues in coordinated deployment. |

---

## 11. Acceptance Criteria

Before approving any code quality or refactoring deliverable:
- [ ] All code files strictly adhere to the $\le 300$ physical lines limit.
- [ ] `declare(strict_types=1);` is present; all arguments and return values are explicitly typed.
- [ ] Zero PHPStan errors at Level 8 (or higher); no untracked suppressions.
- [ ] Complexity metrics meet project targets (recommended baselines: cognitive complexity $\le 15$, cyclomatic complexity $\le 10$, or documented algorithmic justification).
- [ ] Refactored legacy code is backed by passing characterization or unit tests.
- [ ] Guard clauses and Value Objects replace primitive obsession and nested conditionals where appropriate.
- [ ] PR is independently reviewed and approved with attached test execution logs.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-ARCH-001` (Software Architecture, DDD and Modular Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-TEST-001` (Automated Testing and Verification Standards)
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
- **Authoritative Engineering Literature:**
  - Martin Fowler: *Refactoring: Improving the Design of Existing Code* (Addison-Wesley)
  - Michael Feathers: *Working Effectively with Legacy Code* (Prentice Hall)
  - Robert C. Martin: *Clean Code: A Handbook of Agile Software Craftsmanship*
  - Kent Beck: *Tidy First? A Personal Tour of Practical Software Design*
  - SonarSource: *Cognitive Complexity: A new way of measuring understandability*

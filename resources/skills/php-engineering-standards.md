# Shared Engineering Skill: PHP Modern Engineering Standards

**Skill ID:** SKILL-PHP-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **PHP Modern Engineering Standards** skill defines the authoritative enterprise engineering requirements, architectural rules, and implementation practices for all PHP codebases developed within Universal Core. 

Its primary purpose is to enforce modern PHP 8.3+ capabilities, deterministic type safety, rigorous object-oriented design, resilient error handling, memory safety, and high-performance execution. It eliminates runtime ambiguities, silent failures, and insecure type-juggling vulnerabilities, guaranteeing that all PHP components are cohesive, testable, maintainable, and enterprise-grade.

---

## 2. Scope and Applicability

This standard applies universally across all repositories, services, packages, CLI tools, background workers, and libraries running PHP within Universal Core environments.

- **PHP Compatibility:** PHP 8.3 and all forward-compatible minor releases (8.3+).
- **Target Components:** Domain entities, Value Objects, Data Transfer Objects (DTOs), Actions, Services, Repositories, Middlewares, Events, Listeners, Console Commands, and Utility libraries.
- **Interoperability:** Applies to standalone PHP libraries as well as framework-integrated modules (such as Laravel components) without conflicting with framework-specific idioms.

---

## 3. Required Knowledge and Competencies

Agents and engineers utilizing this skill must possess verified proficiency in:
1. **Modern PHP 8.3+ Language Specifications:** Readonly classes, typed class constants, constructor property promotion, backed enums, first-class callables, match expressions, fiber concurrency, and the `#[\SensitiveParameter]` attribute.
2. **Type Theory and Static Analysis:** Level 8/Max static analysis enforcement (PHPStan / Psalm), variance rules (covariance, contravariance), intersection types, and disjunctive normal form (DNF) types.
3. **Enterprise Object-Oriented Design (SOLID):** Single Responsibility Principle, Open/Closed Principle, Liskov Substitution Principle, Interface Segregation Principle, and Dependency Inversion Principle.
4. **Defensive Programming and Error Handling:** Explicit domain exception hierarchies, safe unwrapping, suppression prohibition, and zero swallowed exceptions.
5. **Memory and Resource Profiling:** Memory-efficient stream/generator processing, avoidance of circular references, garbage collection mechanics, and OPcache preloading optimization.

---

## 4. Core Engineering Principles

1. **Deterministic Type Safety:** Eliminate all implicit type coercion and runtime type-juggling risks. If a value can be null, it must be explicitly typed as nullable; if a method produces no value, its return must be `void` or `never`.
2. **Immutability and State Encapsulation by Default:** Objects that represent facts, commands, queries, or configuration must be immutable. Mutable state must be strictly encapsulated, auditable, and restricted to stateful domain models.
3. **Explicit Contracts and Dependency Inversion:** Components must depend on explicit abstractions (interfaces) rather than volatile concrete implementations, enabling predictable unit testing and seamless substitution.
4. **Fail Fast and Fail Explicitly:** Detect invalid states, invariant breaches, or unauthorized inputs at the earliest boundary. Throw domain-specific exceptions immediately; never return null, false, or error codes to represent an unhandled failure.
5. **Zero Implementation Debt:** Every production implementation must be complete, verified, and backed by automated tests. No placeholder methods, stub returns, or mock behavior are allowed in production runtime paths.

---

## 5. Mandatory Standards

### 5.1 Universal Code Quality Standards

#### 5.1.1 File Size and Single Responsibility Limit
- **Maximum 300 lines per code file:** No PHP source file may exceed 300 physical lines of code (including docblocks and imports) by default.
- **Exception Governance:** Any exception above 300 lines requires:
  - Specific technical justification.
  - Documented explanation of why decomposition is unsuitable.
  - Explicit prior human user approval.
  - Recorded exception scope and affected file.
  - No agent, automated process, validator, or department manager may grant itself an exception.
  - Approval for one file does not establish a general exception for other files.
  - Unauthorized oversized files are treated as non-compliant.
- **Cohesive Responsibility:** Each class, interface, enum, or trait must represent a single, focused responsibility. If a class approaches 300 lines, it must be decomposed into collaborating single-purpose components (e.g., extracting sub-actions, domain services, or calculation strategies) without artificial fragmentation.
- **Preservation of Quality:** Readability, semantic cohesion, and domain integrity must never be compromised merely to satisfy the line count.

#### 5.1.2 Code Readability and Semantic Expressiveness
- **Self-Documenting Code:** Code must be understandable to any qualified engineer without relying on guesswork.
- **Meaningful Naming:** Variables, functions, methods, parameters, and classes must use descriptive, unambiguous domain nomenclature (e.g., `calculateProratedSubscriptionAmount()` instead of `calc()`).
- **Complexity and Nesting:** Cyclomatic complexity per method should target $\le 5 - 10$ as a recommended baseline. Flat, linear dispatch structures (such as exhaustive `match` expressions) are permitted when readability is high. Deeply nested conditionals ($\ge 3$ levels) are prohibited; use early returns (guard clauses) and pattern matching.
- **Elimination of Magic Values:** Magic numbers and unanchored string literals are strictly prohibited. Use backed enums or typed class constants (`public const int DEFAULT_TIMEOUT_SECONDS = 30;`).

#### 5.1.3 Implementation Integrity
- **No Pretend or Fake Implementations:** No stubbed, fake, simulated, or placeholder logic is permitted in production code paths.
- **No Empty Implementations:** Empty methods, no-op handlers, or stub returns (`return true;`, `return [];`) masquerading as completed functionality constitute critical defects.
- **No Hardcoded Demo Data:** Production logic must execute genuine domain algorithms against verified data sources.
- **No Swallowed Exceptions:** Every caught exception must be handled, transformed, rethrown, or explicitly logged with contextual metadata. Empty catch blocks are prohibited.
- **Isolation of Temporary Code:** Any temporary diagnostic logic must be isolated behind feature toggles, explicitly documented, and strictly barred from production release.

### 5.2 Strict Typing and Contract Integrity
- **Strict Types Declaration:** Every single PHP file must begin with `declare(strict_types=1);` immediately following the opening `<?php` tag.
- **Complete Type Signatures:** All class properties, method parameters, and return types must be explicitly typed.
- **Prohibition of `mixed`:** The `mixed` pseudo-type is prohibited in domain and application logic. When interfacing with untyped third-party APIs, untyped input must be validated and converted into a typed DTO at the adapter boundary before passing into the domain.
- **Narrow and Specific Types:** Prefer specific types over broad primitives. Use Backed Enums instead of arbitrary `string` or `int` parameters for finite sets of valid values.
- **Final Classes by Default:** All domain classes, DTOs, actions, and services must be marked `final` unless an explicit abstract base class design requires inheritance. Composition must always be preferred over inheritance.

### 5.3 Modern Language Construct Adoption
- **PHP 8.3 Typed Class Constants:** Class constants must define explicit types:
  ```php
  final class OrderLimits
  {
      public const int MAX_LINE_ITEMS = 50;
      public const float MIN_ORDER_TOTAL_USD = 5.00;
  }
  ```
- **Readonly Classes and Properties:** Readonly classes (`readonly final class`) must be used for all DTOs, domain events, and value objects that do not mutate after construction.
- **Constructor Property Promotion:** Use constructor property promotion to eliminate boilerplate assignment in constructors.
- **Match Expressions:** Prefer exhaustive `match` expressions over `switch` statements to enforce compile-time/runtime exhaustiveness.

### 5.4 Secure Handling of Sensitive Information
- **Sensitive Parameter Attribute:** All method arguments handling passwords, tokens, API keys, personal identification numbers, or cryptographic material must be annotated with `#[\SensitiveParameter]`:
  ```php
  public function authenticate(
      string $username,
      #[\SensitiveParameter] string $password
  ): AuthenticationResult;
  ```
- **Memory Hygiene:** Redact sensitive parameters from serialized objects, `__debugInfo()` representations, and exception stack traces.

---

## 6. Implementation Procedures and Practices

### 6.1 Building Resilient Domain Value Objects and DTOs
When creating Data Transfer Objects and Value Objects:
1. Declare strict types and mark the class as `readonly final`.
2. Encapsulate business invariants in constructor validation or dedicated static factory methods.
3. Annotate sensitive fields with `#[\SensitiveParameter]`.
4. Ensure zero external mutation capability.

```php
<?php

declare(strict_types=1);

namespace App\Domain\Identity\ValueObjects;

use App\Domain\Identity\Exceptions\InvalidEmailException;

final readonly class EmailAddress
{
    private string $value;

    public function __construct(string $rawEmail)
    {
        $sanitized = filter_var(trim($rawEmail), FILTER_VALIDATE_EMAIL);
        
        if ($sanitized === false) {
            throw new InvalidEmailException("The provided email '{$rawEmail}' is invalid.");
        }

        $this->value = strtolower($sanitized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### 6.2 Designing Cohesive, Single-Responsibility Actions
Actions must perform a single atomic unit of work, receive typed inputs, execute within safe boundaries, and return a strongly typed result:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Contracts\HashProviderInterface;
use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Events\UserRegisteredEvent;
use App\Domain\Auth\Exceptions\DuplicateEmailException;
use Psr\EventDispatcher\EventDispatcherInterface;

final class RegisterUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly HashProviderInterface $hashProvider,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function execute(RegisterUserDTO $dto): User
    {
        if ($this->userRepository->existsByEmail($dto->email)) {
            throw new DuplicateEmailException("Email {$dto->email->value()} is already registered.");
        }

        $hashedPassword = $this->hashProvider->hash($dto->plainPassword);

        $user = new User(
            id: $this->userRepository->nextIdentity(),
            email: $dto->email,
            passwordHash: $hashedPassword,
            registeredAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        $this->userRepository->save($user);

        $this->eventDispatcher->dispatch(new UserRegisteredEvent($user->id()));

        return $user;
    }
}
```

### 6.3 Resource-Safe Streaming for High-Volume Operations
When processing large datasets or files, use PHP Generators (`yield`) to preserve memory boundaries ($\le 32\text{ MB}$ footprint):

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Streaming;

final class CsvChunkIterator
{
    /**
     * @param resource $stream
     * @return \Generator<int, array<string, string>>
     */
    public function iterateRecords($stream): \Generator
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Valid file stream resource required.');
        }

        $header = fgetcsv($stream);
        if ($header === false) {
            return;
        }

        $rowNumber = 1;
        while (($row = fgetcsv($stream)) !== false) {
            $rowNumber++;
            if (count($row) !== count($header)) {
                throw new \RuntimeException("Malformed row {$rowNumber} encountered.");
            }
            yield $rowNumber => array_combine($header, $row);
        }
    }
}
```

---

## 7. Prohibited Practices

The following patterns violate Universal Core engineering standards and will fail review gates:

1. **PROHIBITED:** Missing `declare(strict_types=1);` in any PHP file.
2. **PROHIBITED:** Using untyped properties, parameters, or return types.
3. **PROHIBITED:** Utilizing `mixed` or array-of-anything (`array`) where a typed collection, DTO, or generic docblock template is required.
4. **PROHIBITED:** Using error suppression operators (`@file_get_contents(...)`). All operations must use proper defensive validation or catch specific exceptions.
5. **PROHIBITED:** Swallowing exceptions in empty `catch (\Throwable $e) {}` blocks.
6. **PROHIBITED:** Relying on loose equality comparisons (`==` or `!=`). Always use strict comparisons (`===` and `!==`).
7. **PROHIBITED:** Exceeding the 300-line code file limit without prior explicit human user approval and documented technical justification.
8. **PROHIBITED:** Modifying framework core files or vendor libraries.
9. **PROHIBITED:** Mutating global state (`$_GET`, `$_POST`, `$_SESSION`, global variables) directly in domain logic.
10. **PROHIBITED:** Passing sensitive credentials (passwords, private keys, API secrets) without the `#[\SensitiveParameter]` attribute.

---

## 8. Security and Reliability Requirements

1. **Defensive Parameter Boundaries:** Validate all incoming primitives against domain rules before instantiation.
2. **Cryptographic Primitives:** Use PHP 8.3 cryptographically secure random number generators (`random_bytes()`, `random_int()`). Never use `rand()` or `mt_rand()` for security, token, or identifier generation.
3. **Safe String Comparison:** Always use `hash_equals()` for comparing hashes, authorization tokens, and signatures to prevent timing attacks.
4. **Exception Masking at System Boundaries:** Domain exceptions may contain detailed debugging information, but exceptions surfaced across public API boundaries must be sanitized to prevent information disclosure (CWE-209).
5. **Deterministic Serialization:** Classes serialized to JSON must implement `\JsonSerializable` with an explicit whitelist of exposed fields; never expose raw object state directly.

---

## 9. Testing and Verification

### 9.1 Static Analysis Gate
- Every file must pass **PHPStan Level 8 / Psalm Level 1** with zero baseline exemptions.
- Code style must conform to PSR-12 / PER-CS 2.0 via PHP-CS-Fixer or Laravel Pint.

### 9.2 Automated Unit Testing Standards
- **Test Framework:** PHPUnit 10+ or Pest 2+.
- **Test Isolation:** Unit tests must be pure and fast, testing domain logic against mocked or fake implementations of interfaces.
- **Coverage Target:** Minimum 90% branch coverage on domain logic, value objects, and actions.
- **Deterministic Assertions:** Tests must make explicit assertions. Tests without assertions or with disabled assertion checks are strictly prohibited.
- **Edge Case Verification:** Test boundary conditions: null inputs, empty collections, maximum integer boundaries, invalid UTF-8 strings, and unexpected network failure states.

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Silent Type Juggling Defect** | PHP implicit conversion (`"0" == false` evaluates to true). | Enforce `declare(strict_types=1);` and use `===` exclusively. |
| **Memory Exhaustion (OOM)** | Loading entire database queries or files into an unconstrained array. | Use PHP Generators (`yield`) and process items in deterministic chunks. |
| **Leaked Passwords in Telemetry** | Unsanitized parameters appearing in stack traces and logs. | Enforce `#[\SensitiveParameter]` on all credentials and sensitive tokens. |
| **Monolithic Bloated Classes** | Gradual accumulation of disparate responsibilities in a single class. | Enforce the strict 300-line file limit and extract single-action classes. |
| **Uncaught Null Pointer Exception** | Calling methods on unverified nullable return types. | Use nullsafe operators (`?->`) or explicit guard assertions (`assertNotNull()`). |

---

## 11. Acceptance Criteria

Before completing any PHP engineering task, verify:
- [ ] File has `declare(strict_types=1);` as the first statement.
- [ ] Physical file length is $\le 300$ lines.
- [ ] Class is marked `final` or `readonly final` unless specifically architected as an abstract base.
- [ ] 100% of method arguments, properties, and returns have explicit types.
- [ ] No `mixed` types exist without explicit domain justification and boundary validation.
- [ ] `#[\SensitiveParameter]` is applied to all sensitive arguments.
- [ ] No magic numbers or strings exist; backed enums or typed constants are used.
- [ ] No empty catch blocks or swallowed exceptions exist.
- [ ] Passes PHPStan Level 8 / Psalm Level 1 with 0 errors.
- [ ] Automated tests cover normal, boundary, and error conditions.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-TEST-001` (Automated Testing and Verification Standards)
- **External Specifications:**
  - PHP 8.3 Release Notes & Type System Reference
  - PSR-12 / PER Coding Style 2.0
  - OWASP Secure Coding Practices

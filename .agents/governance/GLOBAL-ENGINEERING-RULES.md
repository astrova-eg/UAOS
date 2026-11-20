# Global Engineering Rules
**Document ID:** GOV-DOC-003  
**Version:** 1.0.0  
**Classification:** Operational Directive  

## 1. Codebase Cleanliness and Maintainability
- **Strict Typing:** All PHP files must declare `declare(strict_types=1);` at line 3 without exception.
- **Explicit Return Types and Parameter Types:** Every method, function, and closure must define concrete types. Mixed types are strictly prohibited unless interacting with legacy dynamic libraries and explicitly documented.
- **Docblock Integrity:** Preserve all existing docblocks and architectural notes. When adding new docblocks, adhere strictly to PHPDoc standard.
- **Code File Line Limits & Exception Governance:**
  - Every code file MUST contain no more than 300 lines by default.
  - Any exception above 300 lines requires:
    1. A specific technical justification.
    2. A documented explanation of why decomposition is unsuitable.
    3. Explicit prior human user approval.
    4. A recorded exception scope and affected file.
  - An agent, automated process, validator, or department manager MUST NOT grant itself an exception.
  - Approval of an exception for one file MUST NOT establish a general exception for other files.
  - Files should be decomposed into cohesive, maintainable units whenever practical.
  - Non-code artifacts (Markdown documentation, generated files, lockfiles) follow their respective standards and are distinct from code-file limits.
  - Unauthorized oversized code files MUST be treated as non-compliant.

## 2. Laravel Idiomatic Standards
- **Service Container Dependency Injection:** Prefer constructor dependency injection over global facades inside domain services and business models.
- **Form Request Isolation:** Controllers must never execute manual inline validation. All request validation must reside in dedicated, authorization-aware `FormRequest` classes.
- **Fat Models and Fat Controllers Prohibited:** Keep controllers ultra-lean (Single Responsibility Action classes or invokable controllers). Domain logic belongs in Domain Services, Actions, or Aggregates.
- **Database Transactions:** Every multi-record persistence mutation must be wrapped in `DB::transaction()` with explicit retry handling and deadlock avoidance.

## 3. Database Integrity
- **Foreign Key Constraints:** All relations must be enforced at the relational engine level with appropriate cascading (`ON DELETE RESTRICT` or `ON DELETE CASCADE` according to domain invariants).
- **Migration Idempotency:** Migrations must possess deterministic `up()` and `down()` operations. Column additions must be non-blocking for large tables.
- **Index Optimization:** Every foreign key, polymorphic identifier, and filtering column must have deliberate, composite, or covering indexes justified by query access patterns.

## 4. Frontend Standards
- **RTL-Native Design:** All UI components, Blade templates, and Tailwind classes must inherently support Right-To-Left (RTL) alongside LTR using logical properties (`ps-`, `pe-`, `start-`, `end-`).
- **Semantic HTML & WCAG 2.1 AA:** High-contrast color ratios, ARIA attributes, keyboard focus states, and zero unlabeled interactive elements.
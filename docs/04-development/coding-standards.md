# Coding Standards & Style Guide

**Document ID:** DEV-DOC-002  
**Version:** 2.0.0  
**Classification:** Coding Standard  
**Status:** [Active Reference]  

---

## 1. Mandatory Code Standards
1. **Strict Types:** Every PHP file must declare `declare(strict_types=1);` on line 3.
2. **Type Annotations:** All method parameters, return types, and properties must be explicitly typed.
3. **Automated Linting:** Code must pass Laravel Pint (`./vendor/bin/pint --test`) adhering to PSR-12.
4. **Max File Length & Exception Governance:**
   - Code files must contain no more than 300 lines by default; complex components must be decomposed into collaborating Action classes or services.
   - Any exception above 300 lines requires:
     - Specific technical justification.
     - Documented explanation of why decomposition is unsuitable.
     - Explicit prior human user approval.
     - Recorded exception scope and affected file.
   - No agent, automated process, validator, or department manager may grant itself an exception.
   - Approval of an exception for one file does not establish a general exception for other files.
   - Unauthorized oversized files are treated as non-compliant.
   - Non-code artifacts (Markdown documentation, generated files, lockfiles) follow their respective standards.
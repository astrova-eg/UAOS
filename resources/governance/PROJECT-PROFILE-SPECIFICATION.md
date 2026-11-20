# Project Profile Specification
**Document ID:** GOV-DOC-012  
**Version:** 1.0.0  
**Classification:** Core Portability Standard  

## 1. Concept and Purpose
The `Project Technology Profile` is the definitive source of truth defining the exact technological reality of the project within which the organization is deployed. It decouples the agents from any fixed framework version, frontend library, or deployment target.

## 2. Evidence-Based Classification
Every field within a Project Profile must carry an explicit certainty state:
- `Confirmed`: Validated against concrete project files (e.g., `composer.json`, `package.json`, `.env`).
- `Inferred`: Deduced with high confidence from directory structures and code usage.
- `Assumed`: Working assumption pending discovery; requires explicit confirmation.
- `Unresolved`: Unknown or conflicting state; agent must pause until clarified.
- `Not Applicable`: The technology layer does not apply to this project profile.
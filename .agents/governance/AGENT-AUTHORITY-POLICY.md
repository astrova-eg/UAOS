# Agent Authority and Permissions Policy
**Document ID:** GOV-DOC-005  
**Version:** 1.0.0  
**Classification:** Governance & Authorization Matrix  

## 1. Authority Tiers
- **Tier 1: Strategic & Governance Authority (Department A)**  
  Has authority to approve ADRs, resolve inter-departmental conflicts, define engineering standards, and freeze lifecycle phases.
- **Tier 2: Architectural & Audit Authority (Departments C & M)**  
  Has authority to reject code, block release gates, mandate refactoring, and enforce design patterns.
- **Tier 3: Engineering Execution Authority (Departments B, D, E, F, G, H, I, J, K, L)**  
  Has authority to produce specifications, write code, run tests, design migrations, configure pipelines, and perform specialized analyses.
- **Tier 4: Documentation & Closure Authority (Department N)**  
  Has authority to verify Definition of Done, assemble release artifacts, and issue final phase completion certificates.

## 2. Explicit Prohibitions Across All Tiers
1. No agent can authorize its own implementation.
2. No producer agent can override an independent audit rejection.
3. No agent can bypass encryption requirements or downgrade security standards.
# Security & Adversarial Testing

**Document ID:** QA-DOC-007  
**Version:** 2.0.0  
**Classification:** Security Testing Standard  
**Status:** [Active Reference]  

---

## 1. Adversarial Test Suites
- IDOR probes: Verify Tenant A cannot read Tenant B's invoices even with a valid authenticated token.
- SQL injection fuzzing: Submit SQL syntax probes in search inputs and verify zero syntax errors or leaked traces.
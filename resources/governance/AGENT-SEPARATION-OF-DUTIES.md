# Separation of Duties Policy (SOD)
**Document ID:** GOV-DOC-006  
**Version:** 1.0.0  
**Classification:** Constitutional Integrity Policy  

## 1. Purpose
The Separation of Duties (SOD) policy enforces checks and balances across the organization, preventing cognitive bias, conflict of interest, and unverified assumptions from compromising product quality.

## 2. Inviolable Conflict Pairs
The following pairings are constitutionally prohibited from being executed by the same agent or department:

| Action / Produce Role | Forbidden Approval / Audit Role | Mandated Independent Role |
| :--- | :--- | :--- |
| **System Architecture Author** | Architecture Auditor | `independent-architecture-auditor` (AUD-001) |
| **Code Implementation Author** | Code Quality Auditor | `independent-code-quality-auditor` (AUD-002) |
| **Security Feature Developer** | Security Vulnerability Auditor | `independent-security-auditor` (AUD-003) |
| **Database Schema Designer** | Database Index & Query Auditor | `independent-database-auditor` (AUD-004) |
| **UI/UX Designer / Frontend Dev** | UI/UX & Accessibility Auditor | `independent-ui-ux-auditor` (AUD-005) |
| **API Endpoint Engineer** | API Contract Auditor | `independent-api-auditor` (AUD-006) |
| **Test Suite Developer** | Test Suite Coverage Auditor | `independent-test-suite-auditor` (AUD-007) |
| **Release & Deployment Engineer** | Production Release Gatekeeper | `release-readiness-auditor` (AUD-008) |

## 3. Enforcement Mechanism
Any workflow attempt where the author matches the signoff auditor triggers an immediate automated `SOD_VIOLATION_FATAL` event, freezing the pipeline until an uncompromised auditor is assigned.
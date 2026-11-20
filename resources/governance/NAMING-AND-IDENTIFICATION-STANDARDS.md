# Naming and Identification Standards
**Document ID:** GOV-DOC-010  
**Version:** 1.0.0  
**Classification:** Code & Asset Standardization  

## 1. Agent Identifiers
Agents are assigned unique, immutable IDs formatted as `[PREFIX]-[000]`:
- Department A: `CHENG-001`, `ARCH-001`, `GOV-001`, `DECM-001`, `ORCH-001`, `QGOV-001`, `STD-001`, `CONF-001`
- Department B: `DISC-001`, `REQ-001`, `SYS-001`, `RISK-001`, `FEAS-001`, `TRAC-001`, `RVAL-001`
- Department C: `LARCH-001`, `DDD-001`, `HEX-001`, `MTEN-001`, `EDDA-001`, `DIST-001`, `SCAL-001`, `DEBT-001`
- Department D: `PHP-001`, `LCOR-001`, `RMW-001`, `CREQ-001`, `ELOQ-001`, `QJOB-001`, `EVNT-001`, `AUTH-001`, `PKG-001`, `UPG-001`, `EXCP-001`, `CONF-002`
- Department E: `DBA-001`, `MIGR-001`, `QP-001`, `TRAN-001`, `MTDB-001`, `DATA-001`, `BKP-001`, `ETL-001`
- Department F: `SEC-001`, `LSEC-001`, `SCOD-001`, `CRYP-001`, `KEY-001`, `SECR-001`, `ASEC-001`, `APIS-001`, `MTSEC-001`, `FILE-001`, `DEPSEC-001`, `COMP-001`
- Department G: `UXS-001`, `UJA-001`, `INF-001`, `UID-001`, `DSYS-001`, `A11Y-001`, `PANEL-001`, `UTEST-001`
- Department H: `FARCH-001`, `BLAD-001`, `LIVE-001`, `FILA-001`, `RCT-001`, `VUE-001`, `CSS-001`, `FPERF-001`
- Department I: `API-001`, `EP-001`, `WBHK-001`, `INTG-001`, `CTST-001`, `IREL-001`
- Department J: `QA-001`, `UTST-001`, `FTST-001`, `ITST-001`, `ATST-001`, `E2E-001`, `COV-001`, `REGR-001`
- Department K: `PERF-001`, `ROPT-001`, `CCHE-001`, `QTHR-001`, `RSL-001`, `RSRC-001`
- Department L: `OPS-001`, `SRV-001`, `PIPE-001`, `ENV-001`, `MON-001`, `REL-001`, `ROLL-001`
- Department M: `AUD-001`, `AUD-002`, `AUD-003`, `AUD-004`, `AUD-005`, `AUD-006`, `AUD-007`, `AUD-008`
- Department N: `DOC-001`, `APIDOC-001`, `ADR-001`, `EVNT-002`, `ACPT-001`, `CLOS-001`

## 2. Laravel Codebase Naming Rules
- **Models:** Singular PascalCase (`UserProfile`, `BillingInvoice`).
- **Database Tables:** Plural snake_case (`user_profiles`, `billing_invoices`).
- **Controllers:** Resourceful PascalCase with Controller suffix (`UserProfileController`, `ProcessPaymentController`).
- **Form Requests:** Verb + Model + Request (`CreateUserRequest`, `UpdateBillingPlanRequest`).
- **Events:** Past-tense PascalCase (`InvoiceGenerated`, `TenantSubscribed`).
- **Listeners:** Action/Verb PascalCase (`SendInvoiceNotification`, `ProvisionTenantDatabase`).
- **Jobs:** Imperative PascalCase (`ProcessMonthlyBilling`, `DispatchWebhookEvent`).
# Database Governance Policy

**Document ID:** GOV-DOC-025  
**Version:** 1.0.0  
**Classification:** Database Architecture & Safety Policy (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. Purpose and Scope
This policy governs all database schema designs, migrations, query practices, and data integrity operations across the Universal Core platform. All changes must ensure zero data loss and uninterrupted availability.

---

## 2. Zero-Downtime Migration Invariants

### 2.1 The Expand-and-Contract Migration Pattern
All schema modifications must follow the five-phase Expand-and-Contract pattern:
1. **Expand (Additive Change):** Add new tables, nullable columns, or non-blocking indexes. Old application code continues functioning against existing structures.
2. **Dual-Write / Read Abstraction:** Application code is updated to write to both old and new schema structures while reading from the primary structure.
3. **Backfill:** Existing production data is migrated asynchronously in small, idempotent batches with monitoring.
4. **Contract (Cutover):** Application code switches reads exclusively to the new schema structure; writes to the old structure cease.
5. **Cleanup:** Obsolete columns, indexes, or tables are safely dropped in a subsequent, independent maintenance window.

### 2.2 Forward-Fix Recovery Strategy
1. Destructive `migrate:rollback` commands are strictly forbidden in production environments.
2. In the event of a migration failure, recovery must be executed via an immediate additive Forward-Fix migration.
3. Every migration must be tested against realistic datasets to verify execution time and locking impact before staging.

---

## 3. Data Integrity and Multi-Tenant Isolation
1. **Foreign Key Constraints & Cascades:** All entity relationships must enforce relational integrity at the database engine level. Uncontrolled cascading deletes (`ON DELETE CASCADE`) on primary aggregate roots are prohibited without explicit ADR approval.
2. **Multi-Tenant Segregation:** Multi-tenant schemas must enforce isolation via deterministic tenant ID columns indexed on all tables, or dedicated schema namespaces. Cross-tenant queries are blocked.
3. **Query Optimization:** Missing indexes on foreign keys, unindexed queries on large tables, and `SELECT *` operations in production queries are prohibited. All production queries must be audited with `EXPLAIN ANALYZE`.

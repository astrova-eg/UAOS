# Shared Engineering Skill: Database Engineering and Schema Governance Standards

**Skill ID:** SKILL-DB-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Database Engineering and Schema Governance Standards** skill establishes the authoritative enterprise standard for relational schema architecture, query performance tuning, migration safety, and data lifecycle management across Universal Core.

Its primary purpose is to guarantee relational data integrity, achieve sub-millisecond to low-millisecond OLTP query execution, ensure zero-downtime schema evolution, and prevent deadlocks and race conditions. This skill enforces **MySQL 8.4 LTS** as the primary database standard while preserving compatibility with supported **MySQL 8.0+** environments.

---

## 2. Scope and Applicability

This standard applies to all relational database schemas, DDL migrations, Eloquent query definitions, indexing strategies, transactions, backup procedures, and multi-tenant partitioning strategies.

- **Primary Database Engine:** MySQL 8.4 LTS (InnoDB storage engine) with backward compatibility across MySQL 8.0+.
- **Architectural Scope:** Schema design, primary key selection, foreign key integrity, index optimization, locking mechanisms, query execution plans (`EXPLAIN ANALYZE`), zero-downtime migrations, and disaster recovery.
- **Data Scope:** Multi-tenant isolation boundaries, cryptographic column protection, audit logs, and GDPR-compliant data lifecycle retention.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified expertise in:
1. **Relational Theory and Normalization:** Third Normal Form (3NF) baseline, domain constraints, intentional denormalization strategies backed by benchmarks.
2. **InnoDB Storage Mechanics:** Clustered B-tree index structures, secondary indexes, buffer pool allocation, undo logs, redo logs, and row-level locking.
3. **High-Performance Indexing:** Leftmost prefix matching, composite index cardinality, covering indexes (index-only scans), and redundant index detection.
4. **Concurrency and Transaction Isolation:** ACID properties, REPEATABLE READ versus READ COMMITTED isolation levels, deadlocks, and gap locks.
5. **Zero-Downtime Migration Patterns:** Expand-Contract (Parallel Run) migration design, MySQL Online DDL algorithms (`INSTANT`, `INPLACE`), and non-locking DDL operations.
6. **Query Optimization and Forensics:** Interpreting `EXPLAIN` and `EXPLAIN ANALYZE` outputs, eliminating filesort, disk-based temporary tables, and full table scans (`type: ALL`).

---

## 4. Core Engineering Principles

1. **Relational Integrity by Default:** Enforce data invariants directly in the database engine using foreign keys, check constraints, unique constraints, and strict NOT NULL rules—never rely solely on application-layer validation.
2. **Sequential Clustered Index Locality:** Primary keys must be sequential or time-ordered (ULID, UUIDv7, or BigIncrements) to prevent costly B-tree page splits in InnoDB.
3. **Zero-Downtime Schema Evolution:** Schema changes in production must never take exclusive table locks (`LOCK=EXCLUSIVE`) that block read or write operations. All breaking changes must follow multi-phase Expand-Contract migrations.
4. **Complete Isolation and Tenant Scoping:** Every multi-tenant table must feature an indexed `tenant_id` column, backed by compound unique constraints and automated query scoping.
5. **Zero Implementation Debt:** Migration scripts must be fully implemented, with tested, bidirectional (`up()` and `down()`) rollback routines. No incomplete migrations or skipped constraints are tolerated.

---

## 5. Mandatory Standards

### 5.1 Universal Code Quality Standards

#### 5.1.1 File Size and Single Responsibility Limit
- **Maximum 300 lines per code file:** No migration, seeder, repository, or database action file may exceed 300 physical lines.
- **Mandatory by default:** Any exception requires documented technical justification and formal database architecture sign-off.
- **Decomposition:** Large database operations must be divided into focused, single-table migration files or modular seeding classes.
- **Preservation of Quality:** Readability and SQL clarity must never be compromised to satisfy line limits.

#### 5.1.2 Code Readability and Semantic Expressiveness
- **Predictable Naming Conventions:**
  - Tables: snake_case, plural (e.g., `invoices`, `tenant_users`).
  - Columns: snake_case, singular (e.g., `created_at`, `total_amount_cents`).
  - Foreign Keys: `{singular_table}_id` (e.g., `tenant_id`, `invoice_id`).
  - Indexes: `idx_{table}_{columns}` (e.g., `idx_invoices_tenant_status`).
  - Unique Constraints: `uniq_{table}_{columns}` (e.g., `uniq_invoices_tenant_number`).
- **No Magic Numbers:** Monetary amounts must be stored as integer cents/subunits (`amount_cents BIGINT`) or exact decimal (`DECIMAL(12, 4)`). Floating-point types (`FLOAT`, `DOUBLE`) are prohibited for financial calculations.

#### 5.1.3 Implementation Integrity
- **No Fake Migrations:** Every migration must execute genuine DDL statements against verified schemas.
- **No Swallowed Database Exceptions:** PDO exceptions and deadlock errors must not be caught and discarded silently.
- **Tested Rollbacks:** Every migration `down()` method must cleanly revert the exact state established in `up()`.

### 5.2 Schema Design and Data Integrity
1. **Primary Key Standard:**
   - Use **ULID** or **UUIDv7** (ordered 128-bit identifiers) or `BIGINT UNSIGNED AUTO_INCREMENT`.
   - Random UUIDv4 is strictly prohibited as a clustered primary key due to severe B-tree page fragmentation.
2. **Foreign Key Integrity:**
   - All foreign keys must define explicit referential actions: `ON DELETE RESTRICT` (default for financial/audit data) or `ON DELETE CASCADE` (only for owned child records).
   - Every foreign key column must be indexed. Unindexed foreign keys cause full-table locks during parent row updates or deletions.
3. **Default Values & Nullability:**
   - Columns must be `NOT NULL` by default with meaningful default values where applicable. Nullable columns are permitted only when representing the genuine absence of data.
4. **Check Constraints:**
   - Use MySQL 8.0/8.4 check constraints for boundary validations:
     ```sql
     CONSTRAINT chk_invoices_amount CHECK (total_amount_cents >= 0)
     ```

### 5.3 Indexing Governance and Performance
1. **Leftmost Prefix Rule:** Design compound indexes matching query filter cardinality from most selective to least selective, followed by sort and group columns.
2. **Covering Indexes:** For critical high-throughput queries, use covering indexes containing all selected columns to allow InnoDB to satisfy the query directly from the secondary index leaf without accessing the clustered index.
3. **No Duplicate or Redundant Indexes:** An index on `(tenant_id, status)` makes a separate index on `(tenant_id)` redundant. CI/CD linting must flag and reject redundant indexes.
4. **Execution Plan Benchmark & Table Scan Governance:** All OLTP queries must achieve target execution times $\le 10\text{ ms}$ ($p99$). Do not classify every full-table scan (`type: ALL`) as inherently defective: query optimizers intentionally choose sequential scans on small tables, in batch routines, or when high selectivity ($>20\%-30\%$ of rows) makes sequential reads faster than random index lookups. Require compound indexes, covering indexes, or query redesign when empirical profiling reveals unintended full-table scans causing high I/O wait, latency spikes, or lock contention on high-frequency OLTP endpoints.

### 5.4 Transaction Governance and Concurrency Control
1. **Short Transaction Lifecycles:** Keep database transactions as short as possible. Never invoke external HTTP requests, send emails, or execute time-consuming operations inside an active `DB::transaction()`.
2. **Pessimistic Locking for Finite State:** Financial balances, voucher redemptions, and inventory reservations must use pessimistic locking:
   ```php
   $account = Account::query()
       ->where('id', $accountId)
       ->lockForUpdate()
       ->firstOrFail();
   ```
3. **Deadlock Mitigation:** Always acquire locks in a deterministic order across all application endpoints (e.g., sort entities by ID before locking).
4. **Automated Transaction Retries:** Wrap transactions that modify high-contention rows with retry logic:
   ```php
   DB::transaction(function () {
       // Atomic update
   }, attempts: 3);
   ```

### 5.5 Zero-Downtime Migrations (Expand-Contract Pattern)
Breaking schema modifications (renaming columns, changing column types, dropping columns) must be deployed across multiple releases:

```
Phase 1 (Expand):   Add new column alongside old column; application writes to both.
Phase 2 (Backfill): Run background chunked migration to sync existing records.
Phase 3 (Switch):   Update application to read strictly from new column.
Phase 4 (Contract): Drop old column and triggers after full verification.
```

- **Online DDL:** Use `ALGORITHM=INSTANT` where supported by MySQL 8.4 (e.g., adding nullable columns, dropping check constraints). Use `ALGORITHM=INPLACE, LOCK=NONE` for index creation.

### 5.6 Multi-Tenant Partitioning and Isolation
1. **Mandatory Tenant Foreign Key:** Every tenant-specific table must contain a `tenant_id` column.
2. **Compound Unique Scoping:** Unique constraints on tenant entities must include `tenant_id`:
   ```sql
   UNIQUE KEY uniq_tenant_invoices (tenant_id, invoice_number)
   ```
3. **Cross-Tenant Leakage Prevention:** Global scopes in ORM layers must fail closed if the current tenant context is uninitialized.

### 5.7 Backup, Retention, and Disaster Recovery
1. **Point-in-Time Recovery (PITR):** Binary logging (`binlog`) must be active with row-based format (`binlog_format=ROW`).
2. **Disaster Recovery Targets:**
   - Recovery Point Objective (RPO): $\le 5\text{ minutes}$.
   - Recovery Time Objective (RTO): $\le 30\text{ minutes}$.
3. **Data Retention & Pruning:** Pruning historical or soft-deleted records must execute in batch chunks ($\le 1,000$ rows per delete) with transaction commits between chunks to avoid replication lag and lock escalation:
   ```php
   do {
       $deleted = DB::table('logs')->where('created_at', '<', now()->subDays(90))->limit(1000)->delete();
   } while ($deleted > 0);
   ```

---

## 6. Implementation Procedures and Practices

### 6.1 Enterprise MySQL 8.4 Migration with Online DDL and Check Constraints

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->index();
            $table->string('invoice_number', 64);
            $table->string('status', 32)->default('draft');
            $table->bigInteger('total_amount_cents')->unsigned();
            $table->char('currency', 3)->default('USD');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Foreign Key Enforcement
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('restrict');

            // Compound Index for Tenant Filtering & Sorting
            $table->index(['tenant_id', 'status', 'created_at'], 'idx_invoices_tenant_status_created');

            // Multi-tenant Unique Constraint
            $table->unique(['tenant_id', 'invoice_number'], 'uniq_invoices_tenant_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
```

### 6.2 Safe Chunked Processing with Generators
```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Illuminate\Database\Eloquent\Builder;

final class SafeQueryChunker
{
    /**
     * @return \Generator<int, array<int, object>>
     */
    public function streamChunks(Builder $query, int $chunkSize = 500): \Generator
    {
        $lastId = null;

        while (true) {
            $clonedQuery = clone $query;
            if ($lastId !== null) {
                $clonedQuery->where('id', '>', $lastId);
            }

            $records = $clonedQuery->orderBy('id', 'asc')
                ->limit($chunkSize)
                ->get();

            if ($records->isEmpty()) {
                break;
            }

            $lastId = $records->last()->id;
            yield $records;
        }
    }
}
```

---

## 7. Prohibited Practices

1. **PROHIBITED:** Executing non-parameterized raw SQL or concatenating strings into queries.
2. **PROHIBITED:** Using random UUIDv4 as an InnoDB clustered primary key.
3. **PROHIBITED:** Dropping or renaming columns directly in a single production migration without following the Expand-Contract pattern.
4. **PROHIBITED:** Creating foreign keys without corresponding indexes on the referencing columns.
5. **PROHIBITED:** Executing external network calls (HTTP requests, email, third-party APIs) inside an active database transaction.
6. **PROHIBITED:** Storing floating-point data (`FLOAT`, `DOUBLE`) for financial amounts.
7. **PROHIBITED:** Exceeding the 300-line code file limit without documented formal exemption.
8. **PROHIBITED:** Deleting large volumes of data in an unconstrained single `DELETE` query without batch chunking.
9. **PROHIBITED:** Missing or non-functional `down()` methods in migration files.
10. **PROHIBITED:** Creating unique constraints on multi-tenant tables without including `tenant_id`.

---

## 8. Security and Reliability Requirements

1. **Tablespace Encryption:** Enable InnoDB tablespace encryption at rest (`ENCRYPTION='Y'`).
2. **Column-Level Encryption:** Sensitive columns (PII, SSN, bank credentials) must be encrypted using application-managed authenticated encryption before insertion.
3. **Sanitized Database Dumps:** Development and staging database dumps must be scrubbed of all production customer PII, credentials, and financial balances.
4. **Connection Security:** All application-to-database network traffic must enforce TLS 1.3 (`MYSQL_ATTR_SSL_CA`).
5. **Principle of Least Privilege Database Users:** The web application database user must not possess DDL privileges (`ALTER`, `DROP`, `CREATE`) in production; DDL must be restricted to dedicated migration runners.

---

## 9. Testing and Verification

### 9.1 Migration Rollback Testing
- CI/CD pipelines must execute:
  ```bash
  php artisan migrate:fresh --seed
  php artisan migrate:rollback
  php artisan migrate
  ```
- All migrations must successfully roll back with zero errors and no lingering orphaned foreign keys or indexes.

### 9.2 Query Performance and Inspection
- Run `EXPLAIN ANALYZE` on all primary application queries.
- Verify absence of unintended `Using filesort`, `Using temporary`, or unintended `type: ALL` full-table scans on high-concurrency OLTP endpoints.

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **B-Tree Page Split Degradation** | Using random UUIDv4 as primary keys. | Enforce ordered ULID, UUIDv7, or auto-incrementing BigInt keys. |
| **Table Lock on Migration** | Running `ALTER TABLE` requiring table rebuild without Online DDL. | Specify `ALGORITHM=INSTANT` or `ALGORITHM=INPLACE, LOCK=NONE`. |
| **Deadlock Cascade** | Two transactions acquiring row locks in differing sequence. | Sort entities by ID prior to row locking; implement 3-attempt retries. |
| **Replication Lag on Mass Delete** | Running `DELETE FROM logs WHERE created_at < ...` in one batch. | Chunk deletions into $\le 1,000$ row batches with sleep intervals. |
| **Cross-Tenant Data Exposure** | Query missing tenant scope; unique key missing `tenant_id`. | Enforce compound unique keys `(tenant_id, ...)` and global scopes. |

---

## 11. Acceptance Criteria

Before approving any database engineering deliverable:
- [ ] File has `declare(strict_types=1);` and physical line length is $\le 300$ lines.
- [ ] Primary keys use ULID, UUIDv7, or BigIncrements; no random UUIDv4 keys.
- [ ] All foreign keys are indexed and define explicit `ON DELETE` referential rules.
- [ ] Financial data uses integer subunits or `DECIMAL(12, 4)`; zero `FLOAT`/`DOUBLE`.
- [ ] Multi-tenant tables include `tenant_id` and compound unique constraints.
- [ ] Database transactions contain zero external HTTP/network invocations.
- [ ] High-contention financial or inventory updates use `lockForUpdate()`.
- [ ] Migration scripts have fully tested, functional `down()` rollback methods.
- [ ] Breaking schema evolutions adhere to the Expand-Contract pattern.
- [ ] Query analysis via `EXPLAIN` confirms appropriate index usage with no full table scans.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
  - `SKILL-PERF-001` (Performance Profiling and Optimization Standards)
- **External Specifications:**
  - MySQL 8.4 Reference Manual (InnoDB Storage Engine, Online DDL)
  - SQL-92 / SQL-99 Relational Specifications
  - OWASP Top 10 Database Security Best Practices

# Shared Engineering Skill: CI/CD Pipeline and DevOps Infrastructure Standards

**Skill ID:** SKILL-DEVOPS-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **CI/CD Pipeline and DevOps Infrastructure Standards** skill establishes the authoritative enterprise standard for automated continuous integration, continuous delivery, infrastructure operations, runtime tuning, reliability engineering, and production release governance across Universal Core.

Its primary purpose is to automate multi-stage quality gates (Pint, PHPStan Level 8+, `composer audit`, `npm audit`, Pest/PHPUnit parallel test execution), enforce atomic zero-downtime symlink deployment pipelines, govern production runtime environments (PHP 8.3+ OPcache, PHP-FPM, MySQL 8.4 LTS, mandatory Redis, and supervised queue daemons), and establish verified, evidence-based disaster recovery aligned with project-specific Recovery Point Objectives (RPO) and Recovery Time Objectives (RTO).

This skill strictly enforces project environment boundaries: **local development is conducted natively on Windows Laragon using PowerShell** (PHP 8.3+, MySQL 8.4 LTS, Redis, Composer 2.x, Node.js 20+); **Docker and Laravel Sail are strictly prohibited as local development requirements**. Hosted CI/CD pipelines run in automated cloud environments (such as GitHub Actions).

---

## 2. Scope and Applicability

This standard applies across all application lifecycles, operational platforms, and deployment workflows:
- **Local Development Environment:** Native Windows Laragon with PowerShell. Direct execution of PHP 8.3 CLI, Composer 2.x, MySQL 8.4, and native Redis service without hypervisors or containerization layers.
- **CI/CD Pipelines:** Automated hosted runners executing quality gating, static analysis, vulnerability auditing, parallel test suites, and build artifact creation.
- **Production Infrastructure:** Linux runtime servers (Nginx/FastCGI, PHP-FPM 8.3+, FrankenPHP/Octane where specified), MySQL 8.4 LTS relational engines, dedicated Redis 7.x instances, and systemd / Supervisord process managers.
- **Delivery Engineering:** Zero-downtime atomic symlink releases, pre-traffic database migrations, cache warm-up, queue worker restarts, and instant rollback automation.
- **Observability & Recovery:** RED/USE metrics, structured JSON logging, encrypted backup schedules, evidence-based restore verification, and incident escalation protocols.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified expertise in:
1. **Pipeline Engineering:** GitHub Actions workflow design, concurrency group cancellation on outdated commits, dependency cache key hashing, and parallel matrix execution.
2. **Quality Gating Enforcement:** Automated static analysis (PHPStan Level 8+), code style enforcement (Laravel Pint), and supply-chain vulnerability detection (`composer audit`, `npm audit`).
3. **Atomic Symlink Deployment Mechanics:** Shared directory structures (`releases/`, `shared/`, `current`), atomic symlink swapping (`ln -nfs`), deployment state transitions, and zero-downtime OPcache invalidation (`opcache_reset()` via FastCGI).
4. **Database Migration Sequencing:** Coordinating Expand-Contract database migrations before traffic routing, preventing lock contention, and managing rollback paths.
5. **Runtime System Tuning:** Configuring PHP-FPM worker pools (`pm = dynamic`), OPcache preloading and timestamp validation, MySQL 8.4 InnoDB buffer pools, and Redis server-level memory eviction policies.
6. **Daemon & Scheduler Supervision:** Process supervision via Supervisord or systemd, graceful shutdown signals (`SIGTERM`), and scheduler execution (`php artisan schedule:run`).
7. **Disaster Recovery & Backup Forensics:** Hot physical and logical backups (`mysqldump` / Percona XtraBackup), point-in-time recovery via binary logs (`binlog_format=ROW`), evidence-based restore testing, and backup encryption before offsite transport.

---

## 4. Core Engineering Principles

### 4.1 Native Local Workflows (Docker Prohibited Locally)
In accordance with Universal Core architectural governance, local development workflows rely strictly on the native Windows Laragon stack and PowerShell. Authoring, introducing, or requiring Dockerfiles, `docker-compose.yml`, or Laravel Sail for local development is strictly prohibited. Docker installed on a developer's workstation does not violate policy; however, making local application execution, testing, or building dependent on Docker containers is forbidden.

### 4.2 Fail-Fast, Deterministic CI Gating
CI pipelines must run fast, low-cost static checks (Pint linting, PHPStan Level 8+, security audits) before initiating database-intensive feature test suites. Outdated commits pushed to an open pull request must immediately cancel redundant in-flight workflow executions.

### 4.3 Atomic and Rollback-Safe Deployments
Every production deployment must be atomic. The application codebase is deployed into a new timestamped release folder. Migration scripts execute in a backward-compatible manner. Configuration, routes, and views are pre-cached. Only when all pre-flight checks and health endpoints pass is the `current` symlink atomically repointed. If any step fails, the pipeline aborts cleanly without impacting the running release.

### 4.4 Redis Architecture, Eviction Boundaries, and Workload Segregation
Redis is mandatory across all environments and must be continuously available.

#### 4.4.1 Server-Level Eviction Mechanics (Crucial Technical Invariant)
In Redis, the `maxmemory` limit and `maxmemory-policy` directives are configured strictly at the **server/instance level**, governing the entire Redis process. Redis logical databases (`SELECT 0`, `SELECT 1`, etc.) share the exact same memory allocation, single-threaded execution loop, and eviction policy. **Logical databases do not provide memory isolation or independent eviction policies.**

Recommending `noeviction` for one logical database and `volatile-lru` or `allkeys-lru` for another database on the same Redis instance is technically impossible in Redis architecture.

#### 4.4.2 Operational Risks of Shared Queue and Cache Workloads
Sharing a single Redis instance between asynchronous queues and application caching introduces severe operational risks:
1. **Silent Job Eviction Hazard:** If the shared instance is configured with an eviction policy (e.g., `allkeys-lru` or `volatile-lru`), a burst in application caching under high load can cause Redis to silently evict queued jobs, delayed job timestamps, or queue metadata keys, resulting in irrecoverable data loss and unprocessed business transactions.
2. **Cache Write Failure Hazard:** If the shared instance is configured with `noeviction` to protect queue durability, once the cache fills the allocated `maxmemory`, every subsequent cache write command (`SET`, `SETEX`) will immediately fail with an `OOM command not allowed when used memory > 'maxmemory'` error, cascading into application-level exceptions unless explicitly handled.

#### 4.4.3 Multi-Instance Separation (Recommended Standard)
To guarantee zero job loss while allowing efficient ephemeral caching, production architectures should deploy two distinct Redis server processes or managed instances:
- **Redis Queue Instance:** Dedicated port (e.g., `6380`) or host. Configured with `maxmemory-policy noeviction` and persistent storage (AOF enabled). When memory limits are approached, alerts page operations while preserving all existing queued jobs.
- **Redis Cache Instance:** Dedicated port (e.g., `6379`) or host. Configured with `maxmemory-policy allkeys-lru` or `volatile-lru`. Memory pressure gracefully evicts least-recently-used cache items without impacting background job queues.

#### 4.4.4 Single-Instance Alternative Architecture (Resource-Constrained)
When infrastructure constraints preclude running separate Redis instances (such as minimal single-server staging environments):
- The single instance **must** be configured with `maxmemory-policy noeviction` to ensure queue durability.
- Application caching must enforce explicit TTLs on all cache keys at the application layer.
- Operational monitoring must aggressively track memory saturation (`used_memory / maxmemory`). If memory exceeds safety thresholds ($>80\%$), operations must scale memory or flush non-critical cache keys.
- Alternatively, the project should configure Laravel to use a non-Redis cache store (such as database or file cache) while reserving the single Redis instance exclusively for queue operations.

### 4.5 The 10-Step DevOps & Infrastructure Delivery Framework
```
Step 1: Validate Native Windows Laragon Environment (PHP 8.3+, MySQL 8.4 LTS, Redis)
Step 2: Configure CI Triggers, Concurrency Groups & Branch Protections
Step 3: Setup Automated Dependency Caching (Composer & npm Lock Hashes)
Step 4: Execute Gate 1: Code Style & Syntax Compliance (Laravel Pint)
Step 5: Execute Gate 2: Static Analysis & Type Safety (PHPStan Level 8+)
Step 6: Execute Gate 3: Supply-Chain Vulnerability Audit (composer & npm audit)
Step 7: Execute Gate 4: Deterministic Parallel Automated Tests (Pest/PHPUnit)
Step 8: Orchestrate Atomic Deployment (Release Build, Pre-Migration, Symlink Swap)
Step 9: Warm Caches & Restart Daemons (config/route/view cache, queue:restart)
Step 10: Validate Health Probes (/up) & Monitor Telemetry
```

---

## 5. Mandatory Standards

### 5.1 Environment Management and Isolation
1. **Environment Tiers:**
   - Universal Core recognizes four strictly segregated environments: `local`, `testing`, `staging`, and `production`.
   - Databases, Redis instances, storage disks, and third-party API credentials must never be shared across tiers.
2. **Secrets Governance:**
   - Production secrets must never exist in `.env.example`, code repositories, or unencrypted CI logs.
   - Production secrets are injected at runtime via secure secret managers (e.g., AWS Secrets Manager, HashiCorp Vault, or encrypted deployment runner secrets).
   - Development and staging databases must be populated using sanitized seeders; never import raw production database dumps containing customer credentials, PII, or financial records.
3. **Configuration Caching:**
   - In production, `php artisan config:cache`, `route:cache`, and `view:cache` are mandatory. Application code must never invoke `env()` outside of configuration files (`config/*.php`).

### 5.2 CI/CD Pipeline Gating and Verification
Every pull request targeting `main` or `develop` must sequentially pass all four automated gates:
1. **Gate 1 (Style):** `vendor/bin/pint --test` (zero style deviations).
2. **Gate 2 (Static Analysis):** `vendor/bin/phpstan analyse --level=8` (zero errors).
3. **Gate 3 (Security Audit):** `composer audit` and `npm audit --audit-level=high` (zero high or critical CVEs).
4. **Gate 4 (Test Pyramid):** `php artisan test --parallel` (100% pass rate).

### 5.3 Deployment Engineering and Zero-Downtime Mechanics
1. **Directory Structure:**
   ```
   /var/www/application/
   ├── current -> /var/www/application/releases/20261114210000
   ├── shared/
   │   ├── .env
   │   └── storage/
   └── releases/
       ├── 20261114200000/
       └── 20261114210000/
   ```
2. **Atomic Symlink Deployment Script Flow:**
   - Clone target commit SHA into `/var/www/application/releases/<timestamp>`.
   - Symlink `/var/www/application/shared/.env` and `/var/www/application/shared/storage` into release folder.
   - Run `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`.
   - Run `npm ci && npm run build` (or pull pre-compiled frontend assets).
   - Execute database migrations: `php artisan migrate --force`.
   - Optimize application caches: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
   - Atomically swap symlink: `ln -nfs /var/www/application/releases/<timestamp> /var/www/application/current`.
   - Reload PHP-FPM gracefully and reset OPcache.
   - Signal queue workers: `php artisan queue:restart`.
   - Verify health: query `http://127.0.0.1/up` expecting HTTP 200 within 5 seconds.
   - Prune old releases (retaining the last 5 releases).

### 5.4 Infrastructure and Runtime Tuning

#### 5.4.1 PHP 8.3 & PHP-FPM Configuration
- **OPcache Production Invariants:**
  ```ini
  opcache.enable=1
  opcache.enable_cli=1
  opcache.memory_consumption=256
  opcache.interned_strings_buffer=32
  opcache.max_accelerated_files=20000
  opcache.validate_timestamps=0
  opcache.revalidate_freq=0
  opcache.save_comments=1
  ```
- **PHP-FPM Worker Pool (`www.conf`):**
  ```ini
  pm = dynamic
  pm.max_children = 50
  pm.start_servers = 10
  pm.min_spare_servers = 5
  pm.max_spare_servers = 15
  pm.max_requests = 1000
  catch_workers_output = yes
  decorate_workers_output = no
  ```

#### 5.4.2 MySQL 8.4 LTS Operational Governance
- InnoDB buffer pool size must be configured to $60\%-75\%$ of dedicated database server RAM.
- Binary logging must be enabled (`binlog_format=ROW`) with a 7-day retention period for point-in-time recovery.
- Set `max_connections` appropriately with connection limits monitored.
- Enforce strict SQL modes (`STRICT_TRANS_TABLES`, `ERROR_FOR_DIVISION_BY_ZERO`, `NO_ENGINE_SUBSTITUTION`).

#### 5.4.3 Redis Operational Governance
1. **Workload Isolation Configuration:**
   - **Queue Instance Configuration (`redis-queue.conf` on port 6380):**
     ```ini
     port 6380
     maxmemory 2gb
     maxmemory-policy noeviction
     appendonly yes
     appendfsync everysec
     ```
   - **Cache Instance Configuration (`redis-cache.conf` on port 6379):**
     ```ini
     port 6379
     maxmemory 4gb
     maxmemory-policy allkeys-lru
     appendonly no
     save ""
     ```
2. **Single-Instance Deployment Invariants:**
   - If running a single instance on port 6379:
     ```ini
     maxmemory 4gb
     maxmemory-policy noeviction
     appendonly yes
     appendfsync everysec
     ```
   - All application cache drivers must specify finite TTLs, and cache memory usage must be monitored with alerts at 80% saturation.

#### 5.4.4 Daemon and Process Supervision (Supervisord)
- Queue workers must run under continuous supervision:
  ```ini
  [program:laravel-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=php /var/www/application/current/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=120
  autostart=true
  autorestart=true
  stopasgroup=true
  killasgroup=true
  user=www-data
  numprocs=4
  redirect_stderr=true
  stdout_logfile=/var/log/supervisor/laravel-worker.log
  stopwaitsecs=120
  stopsignal=SIGTERM
  ```
- Task Scheduler must execute via system crontab:
  ```crontab
  * * * * * www-data php /var/www/application/current/artisan schedule:run >> /dev/null 2>&1
  ```

### 5.5 Reliability, Disaster Recovery, and Evidence-Based RPO/RTO Governance

#### 5.5.1 Accurate Definitions of RPO and RTO
- **Recovery Point Objective (RPO):** The maximum acceptable age of data that can be lost in the event of a catastrophic disaster, expressed as a duration (e.g., 5 minutes, 1 hour, 24 hours). RPO directly determines required backup frequency, log shipping schedules, and replication topologies.
- **Recovery Time Objective (RTO):** The maximum acceptable duration of system downtime from the moment a disruption is declared until full service restoration, expressed as a duration (e.g., 30 minutes, 4 hours). RTO dictates automation level, failover architecture, restore procedure efficiency, and warm-standby provisioning.

#### 5.5.2 Establishing Project-Specific RPO and RTO Targets
RPO and RTO are **not** universal constants and must not be treated as arbitrary mandates for every project. They must be established formally based on:
1. **Business Impact Analysis (BIA):** Financial, legal, and operational cost of downtime vs cost of rapid recovery solutions.
2. **Data Criticality:** Differentiating transactional customer financial records from read-only reporting logs or internal staging environments.
3. **Infrastructure Capabilities & Architecture:** High-availability multi-zone replication achieves near-zero RPO/RTO at high infrastructure cost, whereas periodic cold snapshot restoration provides larger RPO/RTO at low operational cost.

**Illustrative Enterprise Tier Baselines:**
- **Tier 1 (Mission-Critical / Financial):** RPO $\le 5\text{ minutes}$, RTO $\le 30\text{ minutes}$. Requires real-time MySQL binary log streaming, multi-zone replica failover, and automated DNS switching.
- **Tier 2 (Standard Business Applications):** RPO $\le 1\text{ hour}$, RTO $\le 4\text{ hours}$. Requires hourly database snapshots, automated offsite S3 sync, and tested automated restore scripts.
- **Tier 3 (Internal / Non-Critical Systems):** RPO $\le 24\text{ hours}$, RTO $\le 12\text{ hours}$. Daily logical dumps and manual infrastructure reconstitution.

#### 5.5.3 Architecture Dictating Achievable Targets
Achieving target RPO and RTO depends directly on specific technical capabilities:
- **RPO Enablers:** Continuous binary logging (`binlog_format=ROW`), semi-synchronous replication, point-in-time recovery (PITR), and transaction log shipping. If backups run only once every 24 hours without binary logging, the true achievable RPO is 24 hours, regardless of declared objectives.
- **RTO Enablers:** Automated infrastructure-as-code provisioning, pre-built deployment artifacts, automated database restore pipelines, fast network storage I/O, and health check validation.

#### 5.5.4 Mandatory Evidence-Based Recovery Testing
Configured backup schedules alone **do not prove** that an RPO or RTO target is achievable.
- Projects must conduct scheduled, empirical disaster recovery drills (at least quarterly) in an isolated staging or sandbox environment.
- The recovery drill must measure the exact wall-clock time required to restore the latest database dump, replay binary logs up to a target timestamp, warm application caches, and verify HTTP 200 health on `/up`.
- The empirically measured recovery duration constitutes the project's **verified RTO**. Any divergence between target RTO and verified RTO must trigger architectural remediation.

#### 5.5.5 Backup Encryption & Offsite Transport
- Database dumps must be streamed and encrypted before leaving the server:
  ```bash
  mysqldump --single-transaction --quick --routines --triggers --events \
    -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    | gzip | openssl enc -aes-256-cbc -salt -pbkdf2 -pass env:BACKUP_ENCRYPTION_KEY \
    | aws s3 cp - "s3://backups-bucket/mysql/$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz.enc"
  ```

### 5.6 Telemetry, Observability, and Operational Security
1. **Health Check Probes:**
   - The application must provide a lightweight `/up` endpoint (HTTP 200) verifying database and cache responsiveness:
     ```php
     Route::get('/up', function () {
         DB::connection()->getPdo();
         Cache::store('redis')->get('health_check');
         return response()->json(['status' => 'healthy', 'timestamp' => now()->toIso8601String()]);
     });
     ```
2. **Structured Logging:**
   - Production logs must output structured JSON via Monolog to `stdout` or log collectors (Elasticsearch, CloudWatch, Datadog).
   - Sensitive fields (`password`, `token`, `secret`, `credit_card`) must be scrubbed automatically by log processors.
3. **Operational Security & Least Privilege:**
   - Web server process (`www-data`) must only have write permissions to `storage/` and `bootstrap/cache/`. All application PHP files must be read-only (`chmod 644`, directories `chmod 755`).
   - SSH access must enforce key-based authentication (Ed25519) with password authentication disabled and direct root login prohibited.

---

## 6. Implementation Procedures and Practices

### 6.1 GitHub Actions Enterprise CI Pipeline (`.github/workflows/ci.yml`)

```yaml
name: Continuous Integration

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

concurrency:
  group: ci-${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  quality-gate:
    name: Code Quality & Security Gate
    runs-on: ubuntu-latest
    steps:
      - name: Checkout Code
        uses: actions/checkout@v4

      - name: Setup PHP 8.3
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, bcmath, redis, opcache, sodium
          coverage: none

      - name: Get Composer Cache Directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer Dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: composer-${{ runner.os }}-${{ hashFiles('**/composer.lock') }}
          restore-keys: composer-${{ runner.os }}-

      - name: Validate Composer Configuration
        run: composer validate --strict --check-lock

      - name: Install Composer Dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Verify Code Style (Laravel Pint)
        run: vendor/bin/pint --test

      - name: Static Analysis (PHPStan Level 8)
        run: vendor/bin/phpstan analyse --level=8

      - name: Supply-Chain Security Audit
        run: composer audit

  test-suite:
    name: Automated Parallel Test Pyramid
    needs: quality-gate
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.4
        env:
          MYSQL_DATABASE: testing
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

      redis:
        image: redis:7.2-alpine
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - name: Checkout Code
        uses: actions/checkout@v4

      - name: Setup PHP 8.3
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, bcmath, redis, sodium
          coverage: none

      - name: Cache Composer Dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: composer-${{ runner.os }}-${{ hashFiles('**/composer.lock') }}

      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Run Test Suite
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
          REDIS_HOST: 127.0.0.1
          REDIS_PORT: 6379
        run: php artisan test --parallel
```

### 6.2 Atomic Production Deployment Script (`deploy.sh`)

```bash
#!/usr/bin/env bash
set -euo pipefail

# Enterprise Atomic Symlink Deployment Script
APP_DIR="/var/www/application"
SHARED_DIR="$APP_DIR/shared"
RELEASES_DIR="$APP_DIR/releases"
TIMESTAMP=$(date -u +%Y%m%d%H%M%S)
NEW_RELEASE_DIR="$RELEASES_DIR/$TIMESTAMP"

echo "==> Initiating release deployment: $TIMESTAMP"

# 1. Prepare Release Directory
mkdir -p "$NEW_RELEASE_DIR"
git clone --depth 1 --branch main git@github.com:org/repo.git "$NEW_RELEASE_DIR"
cd "$NEW_RELEASE_DIR"

# 2. Link Shared Storage and Environment
ln -nfs "$SHARED_DIR/.env" "$NEW_RELEASE_DIR/.env"
rm -rf "$NEW_RELEASE_DIR/storage"
ln -nfs "$SHARED_DIR/storage" "$NEW_RELEASE_DIR/storage"

# 3. Install Dependencies & Build
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --quiet
npm ci --silent && npm run build --silent

# 4. Backward-Compatible Migrations & Cache Pre-compilation
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Atomic Symlink Switch
ln -nfs "$NEW_RELEASE_DIR" "$APP_DIR/current_new"
mv -Tf "$APP_DIR/current_new" "$APP_DIR/current"

# 6. Service Reloads & Worker Restarts
sudo systemctl reload php8.3-fpm
php "$APP_DIR/current/artisan" queue:restart

# 7. Post-Deployment Health Check
echo "==> Verifying application health endpoint..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1/up || echo "000")
if [ "$STATUS_CODE" -ne 200 ]; then
    echo "ERROR: Health check failed with status $STATUS_CODE. Rollback advised."
    exit 1
fi

# 8. Prune Old Releases (Keep latest 5)
cd "$RELEASES_DIR"
ls -1dt */ | tail -n +6 | xargs -I {} rm -rf "{}"
echo "==> Deployment successfully completed: $TIMESTAMP"
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Requiring Docker for Local Development.** Windows Laragon provides high-performance native PHP 8.3, MySQL 8.4, and Redis; forcing Docker introduces hypervisor overhead and violates workspace conventions.
2. **PROHIBITED: Direct In-Place Deployments (`git pull` in web root).** Causes race conditions, missing files, and broken classes for concurrent users while code updates.
3. **PROHIBITED: Bypassing Automated CI Quality Gates.** Disabling Pint, PHPStan, or `composer audit` allows technical debt and critical vulnerabilities into production.
4. **PROHIBITED: Committing Production Secrets to Git Repositories.** Secrets in version control are permanently exposed and breach organizational compliance.
5. **PROHIBITED: Deploying Breaking Schema Changes Before Code (Ignoring Expand-Contract).** Overwriting or deleting columns immediately breaks live application processes running previous release code.
6. **PROHIBITED: Forgetting `queue:restart` on Release.** Queue workers running long-lived daemon loops retain outdated code definitions in memory indefinitely.
7. **PROHIBITED: Unencrypted Backups or Storing Backups on Web Root.** Exposes full customer database dumps to unauthorized download or extraction upon server intrusion.
8. **PROHIBITED: Recommending Per-Database Eviction Policies on a Shared Redis Instance.** `maxmemory-policy` operates globally across the entire Redis process. Sharing one instance with LRU eviction risks silent job loss, while sharing with `noeviction` risks cache write failures.
9. **PROHIBITED: Claiming RPO/RTO Capabilities Without Empirical Restore Testing.** A configured backup schedule without validated restore testing does not guarantee recovery objectives can be met during an incident.

---

## 8. Security and Reliability Requirements

1. **Principle of Least Privilege File Permissions:**
   The `current/` codebase must be owned by `deploy:www-data`, files set to `0644`, directories to `0755`. Only `shared/storage` and `bootstrap/cache` are writable by `www-data`.
2. **Transport Security:** Production web servers must mandate HTTPS with TLS 1.3/1.2, HTTP/2, and strict security headers (`HSTS`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`).
3. **Database Credentials Isolation:** CI/CD runners and deployment scripts must use dedicated migration database users, while web workers use restricted DML-only credentials (`SELECT`, `INSERT`, `UPDATE`, `DELETE`).

---

## 9. Testing and Verification

### 9.1 Verification Hierarchy
1. **Pre-Push Local Verification (Windows PowerShell):**
   ```powershell
   vendor/bin/pint --test
   vendor/bin/phpstan analyse --level=8
   composer audit
   php artisan test --parallel
   ```
2. **Hosted CI Execution:**
   Every commit and pull request must report 100% green status on all workflow jobs before merge authorization.
3. **Pre-Deployment Smoke Verification:**
   Execute `/up` health probe before routing customer traffic; automated script must abort and maintain previous symlink if health check fails.
4. **Empirical Disaster Recovery Validation:**
   Conduct scheduled restore drills measuring actual restore duration to certify empirical RPO and RTO compliance.

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Worker Daemon Crash Post-Deploy** | Long-running queue worker runs obsolete class byte-code. | Issue `php artisan queue:restart` during atomic deployment script. |
| **Silent Job Loss Under High Load** | Shared Redis instance configured with `allkeys-lru` evicts queue keys. | Segregate Redis instances; configure queue instance with `noeviction` and AOF. |
| **Cache Write Failures on Shared Redis** | Shared Redis instance configured with `noeviction` runs out of memory. | Separate cache to dedicated instance with `allkeys-lru`, or enforce strict application TTLs. |
| **Unachievable RTO During Disaster** | Large database restore takes 8 hours despite a 30-minute target. | Perform quarterly evidence-based restore drills; benchmark restore speeds. |
| **Mid-Deploy Database Fatal Errors** | New code deployed before migration runs, or breaking DDL run prematurely. | Follow Expand-Contract migration rules; run migrations before symlink swap. |
| **In-Flight Request Corruption** | Writing files directly into active document root. | Mandate atomic symlink releases (`current -> releases/<timestamp>`). |
| **CI Build Redundancy Storm** | Multiple commits pushed to PR triggering redundant parallel runs. | Configure `concurrency.cancel-in-progress: true` in GitHub Actions. |
| **Local Windows Container Friction** | Attempting to run Docker / Sail on Windows Laragon. | Prohibit Docker locally; enforce native Laragon PHP 8.3 + MySQL 8.4 tooling. |

---

## 11. Acceptance Criteria

Before approving any DevOps or CI/CD deliverable:
- [ ] No local instructions or configurations require Docker, Docker Compose, or Laravel Sail.
- [ ] Local environment directives use native Windows Laragon and PowerShell commands.
- [ ] CI pipeline executes Pint, PHPStan Level 8+, `composer audit`, `npm audit`, and parallel tests.
- [ ] Concurrency controls cancel redundant in-flight workflow runs automatically.
- [ ] Atomic symlink deployment strategy is documented and implemented.
- [ ] Database migrations execute prior to traffic routing using backward-compatible patterns.
- [ ] Production runtime specifies OPcache tuning, PHP-FPM dynamic pools, and supervised queues.
- [ ] Redis architecture acknowledges server-level eviction policies; separates queue workloads (`noeviction`) from cache workloads, or documents single-instance trade-offs.
- [ ] RPO and RTO are defined as project-specific metrics based on business impact and evidence-based restore testing.
- [ ] Health check probe `/up` is configured and validated.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-CRYP-001` (Cryptography and Key Lifecycle Management Standards)
  - `SKILL-VER-001` (Framework and PHP Version Compatibility Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
- **Authoritative Specifications:**
  - GitHub Actions Documentation (Workflow Syntax, Cache Action v4)
  - Laravel 11.x Deployment & Optimization Documentation
  - PHP-FPM Production Configuration Guide
  - Redis Administration & Memory Eviction Reference (Official Redis Documentation on `maxmemory-policy`)
  - NIST SP 800-34 Rev. 1 (Contingency Planning Guide for Federal Information Systems - RPO/RTO)

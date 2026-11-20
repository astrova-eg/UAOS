# Operations & DevOps Domain

**Domain:** 09-Operations  
**Classification:** DevOps & Site Reliability  
**Status:** [Active Operations Governance]  

---

## 1. Overview
The **09-Operations** domain defines production deployment pipelines, runtime configuration (PHP-FPM, OPcache, Redis), monitoring telemetry, logging standards, and disaster recovery runbooks.

---

## 2. Directory Contents
- **[deployment.md](deployment.md):** Atomic zero-downtime symlink deployment pipelines.
- **[environment-configuration.md](environment-configuration.md):** Production PHP-FPM, OPcache, and Redis configuration.
- **[monitoring-and-observability.md](monitoring-and-observability.md):** Health probes, RED/USE metrics, and alerting.
- **[logging.md](logging.md):** Structured JSON logging with Monolog and log rotation.
- **[backup-and-recovery.md](backup-and-recovery.md):** Physical backups, point-in-time recovery, and RPO/RTO.
- **[queue-operations.md](queue-operations.md):** Supervisord daemon management and Redis queue workers.
- **[incident-response.md](incident-response.md):** Operational escalation, runbooks, and disaster mitigation.
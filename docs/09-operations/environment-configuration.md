# Production Environment Configuration

**Document ID:** OPS-DOC-002  
**Version:** 2.0.0  
**Classification:** Operational Guide  
**Status:** [Active Reference]  

---

## 1. Production Runtime Invariants
- **OPcache:** Enabled with `opcache.validate_timestamps=0` in production.
- **PHP-FPM:** Configured with `pm = dynamic`, tuned to CPU and RAM baselines.
- **Redis Multi-Instance:** Dedicated port 6380 for queue (`noeviction`) and port 6379 for cache (`allkeys-lru`).
# Atomic Zero-Downtime Deployment Standards

**Document ID:** OPS-DOC-001  
**Version:** 2.0.0  
**Classification:** Deployment Standard  
**Status:** [Active Reference]  

---

## 1. Symlink Architecture
```text
/var/www/core/
├── releases/
│   ├── 20261115120000/
│   └── 20261115130000/
├── shared/
│   ├── .env
│   └── storage/
└── current -> /var/www/core/releases/20261115130000
```
- Deployment script extracts new release into `releases/<timestamp>`.
- Executes `php artisan migrate --force`.
- Pre-caches config, routes, and views (`optimize`).
- Atomically repoints `current` symlink.
- Reloads PHP-FPM to reset OPcache cleanly without dropping active requests.
# Queue Daemon Supervision & Operations

**Document ID:** OPS-DOC-006  
**Version:** 2.0.0  
**Classification:** Queue Operations Standard  
**Status:** [Active Reference]  

---

## 1. Supervisord Management
Queue workers are supervised via Supervisord:
`php artisan queue:work redis --queue=default,high,low --tries=3 --timeout=90`

Deployments execute `php artisan queue:restart` to signal workers to gracefully reload code.
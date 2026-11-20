# Local Environment Setup (Windows Laragon & PowerShell)

**Document ID:** GS-DOC-004  
**Version:** 2.0.0  
**Classification:** Operational Guide  
**Status:** [Active Reference]  

---

## 1. Windows Laragon Configuration
- **Root Directory:** `C:\laragon\www\core`
- **Apache/Nginx Virtual Host:** `http://core.test` pointing to `C:\laragon\www\core\public`
- **MySQL Service:** Configured on port 3306 with default collation `utf8mb4_unicode_ci`.
- **Redis Service:** Configured and running on port 6379.

### Local Prohibition of Docker & Laravel Sail
Under Universal Core engineering governance, authoring or requiring Dockerfiles, `docker-compose.yml`, or Laravel Sail for local development is strictly prohibited. Workflows rely natively on the Laragon stack and PowerShell.
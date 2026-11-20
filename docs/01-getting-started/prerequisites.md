# System Prerequisites

**Document ID:** GS-DOC-002  
**Version:** 2.0.0  
**Classification:** Developer Guide  
**Status:** [Active Reference]  

---

## 1. Software Baselines
To develop on Universal Core, your local environment must satisfy these minimum versions:
- **Operating System:** Windows 10/11 or Windows Server.
- **Local Server Stack:** Windows Laragon (v6.0+).
- **PHP:** PHP 8.3+ (ZTS or NTS 64-bit) with extensions: `pdo_mysql`, `redis`, `mbstring`, `openssl`, `bcmath`, `curl`, `opcache`.
- **Relational Database:** MySQL 8.4 LTS or MySQL 8.0+.
- **In-Memory Cache & Queue:** Redis 7.x (running locally via native Windows service or Laragon Redis).
- **Package Managers:** Composer 2.7+ and Node.js 20+ LTS (with NPM 10+).
- **CLI Shell:** Windows PowerShell 5.1+ or PowerShell 7+.
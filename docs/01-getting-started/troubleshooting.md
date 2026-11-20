# Local Troubleshooting Runbook

**Document ID:** GS-DOC-007  
**Version:** 2.0.0  
**Classification:** Troubleshooting Guide  
**Status:** [Active Reference]  

---

## 1. Common Issues and Resolutions

### Issue 1: Redis Connection Refused (`127.0.0.1:6379`)
- **Symptom:** `ConnectionException: No connection could be made because the target machine actively refused it`.
- **Cause:** Redis server daemon is not running in Laragon.
- **Remediation:** In Laragon UI, click `Menu -> Redis -> Start Redis`, or run `redis-server.exe` in PowerShell.

### Issue 2: MySQL Access Denied for User `root`
- **Symptom:** `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`.
- **Cause:** Incorrect DB credentials in `.env`.
- **Remediation:** Verify `DB_USERNAME=root` and `DB_PASSWORD=""` (default Laragon password is empty).
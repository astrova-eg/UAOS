# First Steps & Health Verification

**Document ID:** GS-DOC-006  
**Version:** 2.0.0  
**Classification:** Verification Guide  
**Status:** [Active Reference]  

---

## 1. Verifying Local Health
Execute these commands in PowerShell to verify system health:

```powershell
# 1. Check PHP version and extensions
php -v
php -m | Select-String -Pattern "pdo_mysql|redis|openssl|mbstring"

# 2. Test database connection
php artisan db:monitor

# 3. Test Redis connection
php artisan cache:clear

# 4. Run automated test suite
php artisan test --parallel

# 5. Run static analysis quality gate
./vendor/bin/phpstan analyse
```
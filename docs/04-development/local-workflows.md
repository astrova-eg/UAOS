# Daily Local Workflows & Artisan Routines

**Document ID:** DEV-DOC-010  
**Version:** 2.0.0  
**Classification:** Workflow Runbook  
**Status:** [Active Reference]  

---

## 1. Common PowerShell Commands

```powershell
# Clear all cached configuration, routes, and views
php artisan optimize:clear

# Run database migrations
php artisan migrate

# Execute Pint style check
./vendor/bin/pint --test

# Execute PHPStan static analysis
./vendor/bin/phpstan analyse

# Execute Pest test suite
php artisan test
```
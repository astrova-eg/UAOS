# Installation Guide

**Document ID:** GS-DOC-003  
**Version:** 2.0.0  
**Classification:** Developer Runbook  
**Status:** [Active Reference]  

---

## 1. Step-by-Step Installation

```powershell
# 1. Clone repository into Laragon web root
cd C:\laragon\www
git clone <repository-url> core
cd core

# 2. Install PHP Composer dependencies
composer install

# 3. Initialize environment configuration
Copy-Item .env.example .env
php artisan key:generate

# 4. Configure database and Redis credentials in .env
# DB_DATABASE=universal_core
# DB_USERNAME=root
# DB_PASSWORD=
# REDIS_HOST=127.0.0.1
# REDIS_PORT=6379

# 5. Execute database migrations
php artisan migrate

# 6. Install frontend Node dependencies (if applicable)
npm install
npm run build

# 7. Run test suite to verify installation
php artisan test
```
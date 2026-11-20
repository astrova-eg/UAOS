# Environment Variables Reference (`.env`)

**Document ID:** CONF-DOC-002  
**Version:** 2.0.0  
**Classification:** Configuration Dictionary  
**Status:** [Active Reference]  

---

## 1. Master Environment Dictionary

| Variable Name | Required | Default Value | Description |
| :--- | :--- | :--- | :--- |
| `APP_ENV` | Yes | `local` | Environment name (`local`, `staging`, `production`) |
| `APP_DEBUG` | Yes | `true` (local) / `false` (prod) | Toggle detailed error stack traces |
| `APP_KEY` | Yes | *Generated* | 32-character AES-256 base64 encryption key |
| `DB_CONNECTION` | Yes | `mysql` | Relational database connection driver |
| `DB_HOST` | Yes | `127.0.0.1` | MySQL server host address |
| `DB_PORT` | Yes | `3306` | MySQL server port |
| `DB_DATABASE` | Yes | `universal_core` | Relational database name |
| `REDIS_HOST` | Yes | `127.0.0.1` | Redis host address |
| `REDIS_PORT` | Yes | `6379` | Redis port |
| `QUEUE_CONNECTION` | Yes | `redis` | Queue driver (`redis` mandatory in production) |
| `CACHE_STORE` | Yes | `redis` | Cache driver |
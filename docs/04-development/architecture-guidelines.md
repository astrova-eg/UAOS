# Architecture Guidelines & Action Pattern

**Document ID:** DEV-DOC-003  
**Version:** 2.0.0  
**Classification:** Architecture Standard  
**Status:** [Active Reference]  

---

## 1. Single Responsibility Action Pattern
Domain mutations are encapsulated in invokable Action classes:

```php
<?php

declare(strict_types=1);

namespace App\Core\Billing\Actions;

use App\Core\Billing\DTOs\InvoiceData;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

final readonly class CreateInvoiceAction
{
    public function execute(InvoiceData $data): Invoice
    {
        return DB::transaction(function () use ($data): Invoice {
            return Invoice::create($data->toArray());
        });
    }
}
```
# Shared Engineering Skill: Filament 5.x Panel and Livewire Standards

**Skill ID:** SKILL-FILA-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Filament 5.x Panel and Livewire Standards** skill establishes the authoritative enterprise standard for constructing, securing, optimizing, operating, and verifying administrative back-offices, multi-tenant portals, and reactive component interfaces across Universal Core.

Its primary purpose is to guarantee absolute technical alignment with **Filament 5.x** and **Livewire 4.x** running on **Laravel 11.x**, **PHP 8.3+**, **MySQL 8.4 LTS** (preserving **8.0+** compatibility), and **Redis 7.x**. This skill ensures that complex administrative and operational workflows achieve sub-50ms component rendering (p95), eliminate N+1 relational query cascades, enforce strict policy-driven server-side authorization at every execution boundary, eliminate Insecure Direct Object References (IDOR) and client state manipulation in Livewire components, guarantee data integrity via database transactions and concurrency controls, and provide accessible, responsive user interfaces adhering to WCAG 2.1 AA standards.

---

## 2. Scope and Applicability

This standard governs all Filament administrative panels, operational tools, custom pages, widgets, relation managers, and standalone or embedded Livewire 4 reactive components across Universal Core:
- **Framework & Package Targets:** Filament 5.x, Livewire 4.x, Laravel 11.x (11.28+), PHP 8.3+, Tailwind CSS v4.x, Alpine.js v3.x.
- **Filament Architecture:** Panel Providers (`Filament\PanelProvider`), Panels (`Filament\Panel`), Resources (`Filament\Resources\Resource`), Resource Pages (`ListRecords`, `CreateRecord`, `EditRecord`, `ViewRecord`), Relation Managers (`Filament\Resources\RelationManagers\RelationManager`), Custom Pages, Dashboard Widgets (`StatsOverviewWidget`, `ChartWidget`, `TableWidget`), Unified Schemas (`Filament\Schemas\Schema`), Schema Components (`Filament\Schemas\Components\...`), Form Fields (`Filament\Forms\Components\...`), Table Schemas (`Filament\Tables\Table`), Table Columns (`Filament\Tables\Columns\...`), Infolists (`Filament\Infolists\Infolist`), Actions (`Filament\Actions\...`, `Filament\Tables\Actions\...`), and Notifications (`Filament\Notifications\Notification`).
- **Livewire 4 Engineering:** Full-page and nested Livewire 4 components, lifecycle hooks, state snapshot hydration/dehydration, typed attributes (`#[\Livewire\Attributes\Locked]`, `#[\Livewire\Attributes\Validate]`, `#[\Livewire\Attributes\Computed]`, `#[\Livewire\Attributes\Url]`, `#[\Livewire\Attributes\On]`, `#[\Livewire\Attributes\Lazy]`, `#[\Livewire\Attributes\Renderless]`, `#[\Livewire\Attributes\Modelable]`, `#[\Livewire\Attributes\Reactive]`), Form Objects (`Livewire\Form`), DOM morphing, event bus communication (`$this->dispatch()`), and streaming responses.
- **Data Isolation & Multi-Tenancy:** Multi-tenant panel scoping (`Panel::make()->tenant(Tenant::class)`), tenant resolution pipelines, relationship-scoped query constraints, cross-tenant leak elimination, and storage isolation.
- **Persistence & Concurrency:** Atomic database transactions, optimistic locking (`lock_version` / timestamps), pessimistic locking (`lockForUpdate()`), compound unique validations, and idempotency ledgers.
- **Runtime Environments:** Native Windows Laragon / PowerShell local development environments and production-grade PHP-FPM / Laravel Octane / Redis 7.x runtimes.

---

## 3. Required Knowledge and Competencies

Engineers and automated agents applying this skill must demonstrate verified expertise across:
1. **Filament 5.x Architecture & Lifecycles:** The Unified Schema API (`form(Schema $schema): Schema`), separation of concerns between Resources, Pages, and RelationManagers, lifecycle hooks (`mutateFormDataBeforeCreate`, `mutateFormDataBeforeSave`, `afterCreate`, `afterSave`), and the panel provider configuration hierarchy.
2. **Livewire 4 Internals & Reactivity:** The client-server request lifecycle, cryptographic snapshot checksum verification, Morphdom DOM reconciliation, reactive property hydration, Form Object encapsulation, and query-string synchronization.
3. **Relational Query Efficiency & Index Design:** MySQL 8.4 query planning, EXPLAIN analysis, composite indexing covering search and sort operations, eager loading constraints (`with()`, `withCount()`), keyset pagination, and subquery aggregations.
4. **Defensive Authorization & OWASP ASVS:** Server-side operation authorization, Laravel Model Policies (`viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`), action-level authorization closures, IDOR prevention in dropdown relationships, and mass-assignment protection.
5. **State Concurrency & Transaction Integrity:** ACID transaction boundaries, dead-lock prevention, race-condition mitigation in financial or inventory mutations, and optimistic/pessimistic locking semantics.
6. **Accessible Frontend Engineering:** WCAG 2.1 AA criteria, semantic HTML, WAI-ARIA roles, keyboard focus management in modals/drawers, screen-reader feedback, and contrast compliance.

---

## 4. Core Architectural Principles

### 4.1 Thin UI, Domain-Driven Execution
Filament Resources, Pages, and Livewire components are strictly presentation and orchestration layers. They must never contain complex business calculations, multi-table write orchestrations, third-party payment calls, or raw infrastructure manipulation. All business mutations must be delegated to dedicated, single-responsibility Domain Action classes or Application Services.

```
[ HTTP / Livewire Request ]
           │
           ▼
[ Filament Resource / Livewire Component ]  <-- Presentation, Schema, Validation & Input Parsing
           │
           ▼
[ Model Policy / Gate Authorization ]        <-- Server-Side Permission Check
           │
           ▼
[ Domain Action / Application Service ]     <-- Transaction Boundary, Business Invariants & Math
           │
           ▼
[ Eloquent Models & DB Transactions ]       <-- Persistence, Constraints & Domain Events
```

### 4.2 Minimalist Wire Payloads & Snapshot Hygiene
Livewire 4 serializes component state into an encrypted JSON snapshot sent to the client and returned on every update. Storing heavy Eloquent model instances, large collections, or circular object graphs in public component properties causes severe memory overhead, slow network serialization, and client-side exposure. Public properties must store only scalar IDs, typed enums, or minimal DTOs. Rich models must be hydrated lazily or accessed via computed properties (`#[\Livewire\Attributes\Computed]`).

### 4.3 Mandatory Server-Side Authorization Boundary
Hiding a navigation link, button, form field, or table column (`visible()`, `hidden()`) is purely a user-experience enhancement and does **NOT** constitute security. A malicious client can forge Livewire action requests, alter serialized IDs, or invoke public methods directly. Every state-altering action, form save, and record lookup must be protected by explicit, server-side Model Policy checks executed immediately before mutation.

### 4.4 Defense-in-Depth Multi-Tenancy (The Triple Barrier)
Multi-tenant security in Filament must never rely exclusively on Eloquent Global Scopes. Isolation requires three concurrent enforcement barriers:
1. **Panel Context Routing:** Filament panel tenant registration (`tenant(Tenant::class, slugAttribute: 'slug')`) validating tenant membership on every request.
2. **Scoped Relation Constraints:** All relationship select inputs and lookups must explicitly enforce tenant boundaries (`Select::relationship('customer', 'name', modifyQueryUsing: fn ($query) => $query->where('tenant_id', ...))`).
3. **Domain Policy Verification:** Explicit tenant ownership verification in every Policy (`$user->tenant_id === $record->tenant_id`).

### 4.5 The 10-Step Component Architecture & Lifecycle Framework
When architecting any administrative interface or reactive component, follow this mandatory decision process:
```
Step  1: Identify Administrative Use Case, Aggregate Root & Bounded Context
Step  2: Define Explicit Laravel Authorization Policy Rules (viewAny, create, update, delete)
Step  3: Design Database Indexes in MySQL 8.4 Covering Foreign Keys, Search & Sort
Step  4: Define Unified Schema (Filament 5.x Schema) with Strict Validation Rules
Step  5: Configure Table Schema with Explicit Eager Loading (with/withCount) & Defer Loading
Step  6: Separate Inline Actions from Bulk Batch Operations; Enforce Atomic Transactions
Step  7: Formulate Livewire 4 State Payload: Apply #[Locked] to all IDs and Sensitive State
Step  8: Enforce Tenant Isolation Barriers on Queries, Options, and Disks
Step  9: Execute Pest Automated Tests: Policies, Form Validation, Actions & Negative Security
Step 10: Profile Query Execution via Laravel Telescope / Debugbar: Verify Zero N+1 Queries
```

---

## 5. Mandatory Engineering Standards

### 5.1 Universal Core Governance & Code Structure
- **300-Physical-Line Limit:** No Filament resource, page, widget, relation manager, or Livewire component file may exceed **300 physical lines**. If a class approaches this limit, decompose it:
  - Extract form schemas into dedicated Schema classes (`app/Filament/Schemas/...`).
  - Extract table definitions into dedicated Table classes (`app/Filament/Tables/...`).
  - Move complex actions into dedicated Action classes (`app/Filament/Actions/...`).
  - Isolate relationships into dedicated Relation Managers.
- **Strict Typing:** Every PHP file must begin with `declare(strict_types=1);` and feature complete parameter and return type declarations.
- **Final Classes:** Resource pages, relation managers, widgets, and Livewire components must be marked `final` unless explicitly designed as an abstract base class.

### 5.2 Filament Panel & Resource Architecture

#### 5.2.1 Panel Separation & Responsibility
- Enterprise applications must isolate user audiences into distinct Filament Panels (e.g., `AdminPanelProvider`, `AppPanelProvider`, `VendorPanelProvider`).
- Each panel must declare its own authentication guard, session configuration, route path prefix, and middleware pipeline.
- Cross-panel contamination is prohibited: an admin guard user must not access tenant panels without passing explicit panel access authorization (`FilamentUser::canAccessPanel(Panel $panel)`).

#### 5.2.2 Resource Lifecycles & Decomposition
Filament 5.x resources organize database entity management. Adhere to the following conventions:
- **Unified Schema Method Signatures (Filament 5.x):** Resource forms must implement the Unified Schema API:
  ```php
  public static function form(Schema $schema): Schema
  {
      return $schema->components([
          // Schema and Form Components
      ]);
  }
  ```
  *Note: Legacy Filament 3 signatures (`form(Form $form): Form`) are obsolete and prohibited.*
- **Custom Pages vs. Resources:** Use Resources for standard CRUD management on Eloquent aggregates. Use Custom Pages (`Filament\Pages\Page`) for orchestrations involving multiple models, operational wizards, or system configuration dashboards.
- **Navigation Information Architecture:** Group resources using typed navigation groups (`protected static ?string $navigationGroup = 'Billing';`) and explicit navigation sort indexes (`protected static ?int $navigationSort = 1;`).

### 5.3 Livewire 4 Engineering & Reactivity Mechanics

#### 5.3.1 Component Lifecycle Governance
Livewire 4 executes a strict lifecycle pipeline on every round-trip. Agents must place logic in the correct lifecycle hook:
1. `boot()` / `booted()`: Dependency injection resolution and runtime services initialization.
2. `mount(...)`: Initial state hydration from parameters. Runs only once on initial page render.
3. `hydrate()`: Re-initializes non-persisted dependencies or decodes custom snapshot types on subsequent requests.
4. `updating($property, $value)` / `updated($property, $value)`: Reactive side-effects and field-level validation.
5. `rendering()` / `rendered()`: Preparation of view variables immediately before Blade evaluation.
6. `dehydrate()`: Cleans up ephemeral resources and encodes state into the client snapshot.

#### 5.3.2 Strict State Locking with Attributes
Client tampering of public properties is a primary attack vector in reactive web applications.
- **Rule:** Every public property representing an entity ID, tenant ID, status enum, balance, or authorization flag must be attributed with `#[\Livewire\Attributes\Locked]`.
- Livewire 4 cryptographically signs locked properties in the snapshot checksum. If a client attempts to alter a locked property via DevTools, Livewire rejects the request with an exception.

```php
#[\Livewire\Attributes\Locked]
public int $recordId;

#[\Livewire\Attributes\Locked]
public string $tenantId;
```

#### 5.3.3 Computed Properties for Derived Data
Never store database query results or Eloquent models directly in public properties. Use computed properties:
```php
#[\Livewire\Attributes\Computed]
public function invoice(): Invoice
{
    return Invoice::query()
        ->where('tenant_id', auth()->user()->tenant_id)
        ->findOrFail($this->invoiceId);
}
```
- Computed properties are evaluated once per request cycle and cached in memory.
- For expensive calculations needed across multiple Livewire updates within the same component session, use `#[\Livewire\Attributes\Computed(persist: true)]`.

#### 5.3.4 Form Objects for Complex Reactive Forms
When a Livewire component manages more than 4 form inputs, extract state and validation into a dedicated `Livewire\Form` object to preserve the single-responsibility principle:
```php
final class UpdateInvoiceForm extends \Livewire\Form
{
    #[\Livewire\Attributes\Validate('required|string|max:64')]
    public string $invoiceNumber = '';

    #[\Livewire\Attributes\Validate('required|numeric|min:0')]
    public int $totalAmountCents = 0;

    #[\Livewire\Attributes\Validate('required|date')]
    public string $dueDate = '';
}
```

#### 5.3.5 DOM Morphing & `wire:key` Discipline
Livewire 4 relies on DOM morphing algorithms to update the browser DOM without destroying active inputs.
- **Rule:** Every element inside a `@foreach` loop, modal container, conditional branch (`@if`), or dynamic component list must have a unique, stable `wire:key` attribute.
- Format: `wire:key="record-item-{{ $record->id }}"`.
- Never use loop iteration indexes (`$loop->index`) as `wire:key` values when items can be filtered, sorted, added, or removed.

### 5.4 Advanced Forms Engineering

#### 5.4.1 Schema Organization & Container Composition
- Group related fields using structured layout components (`Filament\Schemas\Components\Section`, `Fieldset`, `Grid`, `Wizard`).
- Use `columns(['sm' => 1, 'md' => 2, 'xl' => 3])` to ensure responsive form rendering across screen breakpoints.
- Large forms ($> 8$ inputs) must be split into logical Sections with collapsible states (`collapsible()`, `collapsed()`).

#### 5.4.2 Reactive Dependencies & Field Interactions
- Use `live()` only when immediate server evaluation is required (e.g., dynamically calculating order totals or showing/hiding dependent fields).
- Prefer `live(onBlur: true)` or debounced reactivity `live(debounce: '500ms')` to prevent high-frequency HTTP requests during typing.
- Conditional visibility must inspect state safely via the typed `Get` closure:
  ```php
  Select::make('payment_type')
      ->options(PaymentType::class)
      ->live(),
  TextInput::make('bank_account_number')
      ->visible(fn (Get $get): bool => $get('payment_type') === PaymentType::BankTransfer->value)
      ->required(fn (Get $get): bool => $get('payment_type') === PaymentType::BankTransfer->value),
  ```

#### 5.4.3 Relationship-Aware Forms
- Use `Select::relationship()` for `BelongsTo` relationships with indexed display columns.
- For tables with over 50 records, `searchable()` is **mandatory** to prevent loading entire tables into HTML `<select>` elements.
- When saving complex relationships (e.g., `BelongsToMany`, `HasMany` repeaters), wrap form submission in database transactions to prevent partial writes.

#### 5.4.4 Nested Data & Repeaters
- Repeaters (`Filament\Forms\Components\Repeater`) managing database relationships must declare `relationship('items')` and enforce schema bounds:
  - `maxItems(50)`: Prevent memory exhaustion and payload bloat.
  - `orderColumn('sort_order')`: Guarantee deterministic ordering.
  - `defaultItems(1)`: Provide intuitive initial UX.
- Ensure all foreign keys within repeater items inherit tenant scoping automatically during creation.

#### 5.4.5 File Uploads & Secure Asset Pipelines
- **Rule:** Public storage disks (`public`, `s3-public`) must **never** be used for documents containing PII, financial statements, contracts, or proprietary tenant assets.
- Use private, non-public disks with signed temporary URLs.
- Always configure strict validation rules on `FileUpload`:
  ```php
  FileUpload::make('attachment')
      ->disk('private')
      ->directory(fn (): string => 'tenants/' . Filament::getTenant()->id . '/attachments')
      ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg'])
      ->maxSize(10240) // 10 MB limit
      ->preserveFilenames(false) // Mitigate path traversal via generated UUIDs
      ->required(),
  ```
- Sanitize original filenames and use random hashes or UUIDs to eliminate path-traversal vulnerabilities.

### 5.5 Advanced Tables & Data Management

#### 5.5.1 Query Optimization & N+1 Prevention
- **Rule:** Every relationship column rendered in a table must be explicitly eager-loaded using `modifyQueryUsing()` or `with()` / `withCount()`:
  ```php
  public static function table(Table $table): Table
  {
      return $table
          ->modifyQueryUsing(fn (Builder $query) => $query->with([
              'customer:id,name,email',
              'creator:id,name',
          ])->withCount('items'))
          ->columns([...]);
  }
  ```
- **Aggregate Columns:** For counts or sums, use dedicated count columns (`TextColumn::make('items_count')->counts('items')`) or subqueries rather than loading entire relationship models into memory.

#### 5.5.2 Search, Filtering, and Sorting Governance
- **Rule:** Every column marked `searchable()` or `sortable()` must be backed by a corresponding B-Tree database index in MySQL 8.4.
- Table searches on unindexed columns trigger full table scans (`ALL` in EXPLAIN), creating database deadlocks and severe CPU spikes under production concurrency.
- For large tables ($> 100,000$ rows), disable global prefix wildcard searching and use exact matching, full-text indexes, or dedicated search engines (Meilisearch/Scout).
- Implement filters using typed `SelectFilter`, `TernaryFilter`, or custom `Filter` classes that apply indexed `where` constraints.

#### 5.5.3 Keyset Pagination & Large Datasets
- For tables exceeding 50,000 rows, standard offset-based pagination (`OFFSET N LIMIT M`) degrades exponentially because MySQL must scan and discard $N$ rows.
- Use `deferLoading()` on large tables to render the page layout immediately while fetching table records via an asynchronous background request.
- Configure sensible pagination options: `defaultPaginationPageOption(25)` with allowed options `paginationPageOptions([10, 25, 50, 100])`. Disallow unbounded page sizes.

#### 5.5.4 Bulk Actions & Safe Batch Modifications
- Bulk actions that update hundreds of records must not loop through individual Eloquent models in memory.
- For lightweight updates, use direct database chunking or batch queries.
- For operations requiring domain events and policy checks, execute in transactional chunks of 100 records:
  ```php
  Tables\Actions\BulkAction::make('markAsIssued')
      ->requiresConfirmation()
      ->action(function (Collection $records, SettleInvoiceAction $action): void {
          DB::transaction(function () use ($records, $action): void {
              foreach ($records as $record) {
                  gate()->authorize('update', $record);
                  $action->execute($record);
              }
          });
      })
      ->deselectRecordsAfterCompletion();
  ```

#### 5.5.5 Asynchronous Exports for Large Datasets
- Exporting records directly in an HTTP request worker causes memory exhaustion (`Allowed memory size exhausted`) and gateway timeouts ($504\text{ Gateway Timeout}$).
- Tables handling $> 500$ exportable records must use Filament's queue-backed export actions (`ExportAction`, `ExportBulkAction`) or dispatch background jobs that stream CSV chunks into private S3 buckets, notifying the user via Filament Database Notifications upon completion.

### 5.6 Actions, Business Operations & Concurrency Control

#### 5.6.1 Execution-Time Authorization
Never rely on `visible(fn () => ...)` or `hidden(fn () => ...)` to protect actions. Malicious actors can dispatch Livewire calls targeting hidden actions.
- **Rule:** Every Action closure must enforce server-side authorization at runtime using `$this->authorize()` or `Gate::authorize()`:
  ```php
  Action::make('cancel')
      ->requiresConfirmation()
      ->action(function (Order $record): void {
          Gate::authorize('cancel', $record);
          app(CancelOrderAction::class)->execute($record);
      });
  ```

#### 5.6.2 Destructive Operations & Re-Authentication
- Actions that permanently delete records, alter financial ledgers, or reset security credentials must require explicit confirmation (`requiresConfirmation()`).
- High-risk operations (e.g., executing refunds or purging audit records) should require password re-authentication or one-time passcodes (OTP).

#### 5.6.3 Concurrency Control & Race Condition Prevention
When multiple administrators or automated processes edit the same record simultaneously, standard writes cause silent data overwrites (lost update anomaly).
- **Optimistic Locking:** Add a `lock_version` integer column to critical models. When submitting forms, verify that the submitted version matches the database version:
  ```php
  // In domain action or form save hook:
  $affected = DB::table('invoices')
      ->where('id', $invoice->id)
      ->where('lock_version', $submittedVersion)
      ->update([...$data, 'lock_version' => $submittedVersion + 1]);

  if ($affected === 0) {
      throw new StaleModelLockException('Record has been modified by another administrator.');
  }
  ```
- **Pessimistic Locking:** For operations modifying account balances, inventory stock, or sequential invoice numbers, acquire an exclusive database lock inside a transaction:
  ```php
  DB::transaction(function () use ($invoiceId): void {
      $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoiceId);
      // Execute state transition
  });
  ```

#### 5.6.4 Audit Logging
Every privileged action (status change, refund, configuration update, deletion) must record an immutable audit trail entry containing:
- `actor_id`: Authenticated user ID.
- `tenant_id`: Current tenant ID.
- `action_type`: Structured string (e.g., `invoice.voided`).
- `record_type` & `record_id`: Target entity.
- `payload_diff`: JSON representation of old vs. new state (with sensitive fields redacted).
- `ip_address` & `user_agent`.

### 5.7 Enterprise Security, Authorization & Vulnerability Shielding

#### 5.7.1 Policy-Driven Authorization Matrix
Every Filament Resource must have a corresponding Model Policy registered in `AuthServiceProvider` or discovered by Laravel. Policies must implement all standard operations:
- `viewAny(User $user): bool`: Controls panel navigation link visibility and list page access.
- `view(User $user, Model $record): bool`: Controls read-only inspection access.
- `create(User $user): bool`: Controls access to the creation form.
- `update(User $user, Model $record): bool`: Controls access to edit forms and update actions.
- `delete(User $user, Model $record): bool`: Controls single-record deletion.
- `deleteAny(User $user): bool`: Controls bulk deletion authorization.
- `restore(User $user, Model $record): bool` & `forceDelete(User $user, Model $record): bool`.

#### 5.7.2 IDOR Prevention in Form Dropdowns
A critical security vulnerability occurs when an edit form contains a `Select` dropdown populated with records belonging to other tenants.
- **Rule:** All relationship dropdowns must be explicitly constrained to the active tenant:
  ```php
  Select::make('customer_id')
      ->relationship(
          name: 'customer',
          titleAttribute: 'name',
          modifyQueryUsing: fn (Builder $query) => $query->where('tenant_id', Filament::getTenant()->id)
      )
      ->searchable()
      ->preload(false)
      ->required();
  ```
- If `modifyQueryUsing` is omitted, an attacker can modify the selected ID in the browser DevTools to link an entity to a competitor's customer, causing unauthorized cross-tenant data leakage.

#### 5.7.3 Mass-Assignment & Schema Dehydration Protection
- Non-persisted or auxiliary form fields (e.g., confirmation checkboxes, temporary calculators, password confirmation) must be marked `dehydrated(false)`.
- Explicitly guard against mass-assignment by defining `$fillable` on all Eloquent models, never using unvetted `$guarded = []`.

#### 5.7.4 XSS Prevention & HTML Sanitization
- By default, Filament escapes table text columns and form field values.
- If a column or field requires HTML rendering (`html()`), the input **must** be sanitized using a strict HTML sanitizer (e.g., `Stevebauman\Purify` or `Mews\Purifier`) before display to prevent Stored Cross-Site Scripting (XSS).
- Never render raw user-controlled markdown or rich-text without server-side tag filtering.

#### 5.7.5 Secure Error Responses
- Database query exceptions (e.g., PDOException with SQL strings, table names, or constraint keys) must never be displayed to end users in Filament Notifications or modal dialogues.
- Catch database exceptions at the boundary, log full diagnostic context with correlation IDs to structured logs, and return a clean, localized error message:
  ```php
  Notification::make()
      ->title('Unable to process operation')
      ->body('An error occurred while updating the record. Reference: ' . $correlationId)
      ->danger()
      ->send();
  ```

### 5.8 Multi-Tenancy & Absolute Data Isolation

#### 5.8.1 Filament Tenant Configuration
Multi-tenant panels must configure tenant resolution directly on the Panel Provider:
```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('app')
        ->path('app')
        ->tenant(Tenant::class, slugAttribute: 'slug')
        ->tenantRegistration(RegisterTenant::class)
        ->tenantProfile(EditTenantProfile::class)
        ->tenantMiddleware([
            EnsureTenantIsActive::class,
        ], isPersistent: true);
}
```

#### 5.8.2 Prevention of Cross-Tenant Context Bleed
In long-running process environments (Laravel Octane, FrankenPHP, Horizon), static state persists across requests.
- **Rule:** Never store the current tenant in static class properties or singletons without resetting them in the application loop.
- In Filament, always access the tenant via `Filament::getTenant()` within the request scope.
- In background queue jobs dispatched from Filament panels, pass the `tenant_id` as an immutable scalar payload and restore the tenant context inside the job's `handle()` method.

#### 5.8.3 Tenant Asset & Export Isolation
- All files uploaded within a tenant context must be partitioned into tenant-specific directory paths:
  `storage/app/private/tenants/{tenant_id}/...`.
- File download actions must verify that the requesting user's `tenant_id` matches the file's owning tenant before returning a streamed response or redirecting to a signed S3 URL.

### 5.9 Database Integrity, Transactions & Resilience

#### 5.9.1 Multi-Table Transaction Boundaries
Any operation that updates multiple records, manages repeater relationships, or alters related balances must execute inside `DB::transaction()`:
```php
protected function handleRecordCreation(array $data): Model
{
    return DB::transaction(function () use ($data): Model {
        $record = static::getModel()::create($data);
        // Persist child relations or dispatch required domain operations
        return $record;
    });
}
```

#### 5.9.2 Compound Uniqueness Governance in Multi-Tenant Schemas
In multi-tenant schemas, unique constraints must include `tenant_id` to prevent cross-tenant collision while allowing duplicate values across different tenants (e.g., two tenants each having an invoice `#INV-001`).
- When validating unique inputs in Filament forms, always apply a compound unique rule:
  ```php
  TextInput::make('invoice_number')
      ->required()
      ->unique(
          table: 'invoices',
          column: 'invoice_number',
          ignoreRecord: true,
          modifyRuleUsing: fn (Unique $rule) => $rule->where('tenant_id', Filament::getTenant()->id)
      );
  ```

### 5.10 Performance, Scalability & Caching Standards

#### 5.10.1 Component Rendering Budgets & Metrics
- Standard Filament resource list pages must render within $\le 50\text{ ms}$ (p95 server response time) under local development and production-grade hardware.
- Number of database queries per table render must be bounded:
  $$\text{Query Count} \le 5 \quad \text{for a page of 25 records with } \ge 2 \text{ relations}$$
- Prohibit duplicate queries on any page. If the same `SELECT` executes more than once with identical parameters, it must be eliminated via eager loading, request-scoped memoization, or Redis caching.

#### 5.10.2 Livewire Polling & Refresh Governance
- Unbounded polling (`wire:poll`) places severe load on web servers and database instances.
- Standard forms and tables must **not** poll by default.
- Operational dashboards requiring updates must set polling intervals of $\ge 30\text{ seconds}$ (`wire:poll.30s`) and disable polling when the browser tab is hidden or inactive.
- For high-frequency operational updates, use Laravel Echo WebSockets (Pusher/Reverb) rather than HTTP polling.

#### 5.10.3 Static Lookup Caching
- Options for country codes, currency lists, tax rates, and static system roles must be cached in Redis with appropriate time-to-live (TTL) rather than querying the database on every form render:
  ```php
  Select::make('country_code')
      ->options(fn () => Cache::remember('static:countries', 86400, fn () => Country::pluck('name', 'code')->toArray()))
      ->searchable();
  ```

### 5.11 Dashboard & Widget Engineering

#### 5.11.1 Reusable Widget Architecture
- Filament dashboards utilize three standard widget types:
  1. `StatsOverviewWidget`: High-level numeric KPIs.
  2. `ChartWidget`: Line, bar, and doughnut trend graphs.
  3. `TableWidget`: Compact summary tables for pending approvals or urgent alerts.
- Widgets must declare authorization guards using `canView()` to ensure unprivileged users never see aggregated metrics they cannot access at the record level.

#### 5.11.2 Database Aggregation vs. Collection Loading
- **PROHIBITED:** Loading Eloquent model collections into PHP memory to compute metrics:
  ```php
  // DANGEROUS: Loads 500,000 models into memory!
  $revenue = Order::all()->sum('total_amount_cents');
  ```
- **MANDATORY:** Execute aggregate SQL queries at the database engine level:
  ```php
  $revenue = Order::query()
      ->where('tenant_id', Filament::getTenant()->id)
      ->where('status', OrderStatus::Completed)
      ->sum('total_amount_cents');
  ```

#### 5.11.3 Widget Query Caching
Expensive dashboard queries must be cached with a sensible TTL (e.g., 5 to 15 minutes) and tagged with the tenant identifier:
```php
protected function getStats(): array
{
    $tenantId = Filament::getTenant()->id;

    $totalRevenue = Cache::tags(["tenant:{$tenantId}", 'metrics'])
        ->remember("metrics:revenue:{$tenantId}", 300, function () use ($tenantId) {
            return Order::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->sum('total_amount_cents');
        });

    return [
        Stat::make('Total Revenue', '$' . number_format($totalRevenue / 100, 2))
            ->chart([7, 4, 6, 8, 9, 10])
            ->color('success'),
    ];
}
```

### 5.12 Accessibility (a11y) & User Experience (UX)

#### 5.12.1 WCAG 2.1 AA Compliance in Admin Interfaces
Accessibility is an enterprise engineering requirement, not an optional decorative enhancement:
- **Keyboard Navigation:** All interactive elements (inputs, select triggers, buttons, modal dismiss controls) must be reachable and actionable via keyboard alone (`Tab`, `Shift+Tab`, `Enter`, `Space`, `Escape`).
- **Focus Management:** When a Filament modal or slide-over drawer opens, focus must be trapped within the dialogue. When dismissed, focus must return to the triggering control.
- **Form Labels & Error Feedback:** Every input must have a visible `<label>` associated with its input `id`. Inlined validation errors must be linked via `aria-describedby` and feature an `alert` role.
- **Screen Reader Text:** Icon-only buttons must provide explicit accessible text using `extraAttributes(['aria-label' => 'Delete invoice'])` or screen-reader-only helper text (`<span class="sr-only">Delete</span>`).
- **Loading & Processing States:** Submit buttons must display accessible loading spinners and disable duplicate clicks via `wire:loading.attr="disabled"`.

### 5.13 Production Operations, Diagnostics & Maintainability

#### 5.13.1 Production Deployment Optimization
Every production deployment pipeline must execute Filament optimization commands to compile blade views, cache panel configurations, and compile icons:
```bash
php artisan filament:cache-components
php artisan icons:cache
php artisan view:cache
php artisan route:cache
php artisan config:cache
```
*Note: In local development environments, caching must be disabled or cleared after schema modifications.*

#### 5.13.2 Maintainable Customization & Extension
- Do not override vendor files or monkey-patch Filament internals.
- Extend Filament through documented extension points:
  - Custom Form Components (`Filament\Forms\Components\Component`).
  - Custom Table Columns (`Filament\Tables\Columns\Column`).
  - Custom Panel Plugins (`Filament\Contracts\Plugin`).
  - Custom Livewire components embedded inside schemas via `ViewField`.

---

## 6. Implementation Procedures and Reference Patterns

### 6.1 Pattern 1: Complete Filament 5.x Resource with Unified Schema & Optimized Table
The following pattern demonstrates a complete, production-grade Filament 5.x resource conforming to strict typing, sub-50ms query optimization, and the 300-line physical limit:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Billing\Enums\InvoiceStatus;
use App\Domain\Billing\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

final class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Billing';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Overview')
                    ->description('Essential billing metadata and debtor assignment')
                    ->components([
                        Grid::make(['sm' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->required()
                                    ->maxLength(64)
                                    ->unique(
                                        table: 'invoices',
                                        column: 'invoice_number',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->where(
                                            'tenant_id',
                                            Filament::getTenant()->id
                                        )
                                    )
                                    ->disabledOn('edit'),
                                Select::make('customer_id')
                                    ->relationship(
                                        name: 'customer',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => $query->where(
                                            'tenant_id',
                                            Filament::getTenant()->id
                                        )
                                    )
                                    ->searchable()
                                    ->preload(false)
                                    ->required(),
                                TextInput::make('total_amount_cents')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->maxValue(100000000),
                                Select::make('status')
                                    ->options(InvoiceStatus::class)
                                    ->default(InvoiceStatus::Draft->value)
                                    ->required(),
                                DatePicker::make('issue_date')
                                    ->default(now())
                                    ->required(),
                                DatePicker::make('due_date')
                                    ->afterOrEqual('issue_date')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'customer:id,name,email',
            ]))
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount_cents')
                    ->label('Total')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                IconColumn::make('status')
                    ->icon(fn (InvoiceStatus $state): string => match ($state) {
                        InvoiceStatus::Paid => 'heroicon-o-check-circle',
                        InvoiceStatus::Issued => 'heroicon-o-clock',
                        InvoiceStatus::Void => 'heroicon-o-x-circle',
                        InvoiceStatus::Draft => 'heroicon-o-pencil-square',
                    })
                    ->color(fn (InvoiceStatus $state): string => match ($state) {
                        InvoiceStatus::Paid => 'success',
                        InvoiceStatus::Issued => 'warning',
                        InvoiceStatus::Void => 'danger',
                        InvoiceStatus::Draft => 'gray',
                    }),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading();
    }
}
```

### 6.2 Pattern 2: Multi-Tenant Scoped Relation Manager with Atomic Transactions
```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Domain\Billing\Models\InvoiceItem;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'description';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('description')
                ->required()
                ->maxLength(255),
            TextInput::make('quantity')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),
            TextInput::make('unit_price_cents')
                ->label('Unit Price ($)')
                ->numeric()
                ->prefix('$')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description'),
                TextColumn::make('quantity'),
                TextColumn::make('unit_price_cents')->money('USD', divideBy: 100),
                TextColumn::make('total_price_cents')
                    ->label('Line Total')
                    ->money('USD', divideBy: 100)
                    ->state(fn (InvoiceItem $record): int => $record->quantity * $record->unit_price_cents),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data): Model {
                            /** @var Model $item */
                            $item = $this->getOwnerRecord()->items()->create($data);
                            $this->getOwnerRecord()->recalculateTotals();
                            return $item;
                        });
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotals()),
            ]);
    }
}
```

### 6.3 Pattern 3: Enterprise Livewire 4 Component with Locked State & Form Object
```php
<?php

declare(strict_types=1);

namespace App\Livewire\Billing;

use App\Domain\Billing\Actions\ProcessPaymentAction;
use App\Domain\Billing\Models\Invoice;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class SettleInvoiceDialogue extends Component
{
    #[\Livewire\Attributes\Locked]
    public int $invoiceId;

    public ProcessPaymentForm $form;

    public bool $isProcessing = false;

    public function mount(int $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
        $this->form->initForInvoice($this->invoice);
    }

    #[\Livewire\Attributes\Computed]
    public function invoice(): Invoice
    {
        return Invoice::query()
            ->where('tenant_id', Filament::getTenant()->id)
            ->findOrFail($this->invoiceId);
    }

    public function settle(ProcessPaymentAction $action): void
    {
        Gate::authorize('update', $this->invoice);
        $this->form->validate();

        $this->isProcessing = true;

        try {
            $action->execute(
                invoice: $this->invoice,
                paymentMethodId: $this->form->paymentMethodId,
                amountCents: $this->form->amountCents
            );

            $this->dispatch('invoice-settled', invoiceId: $this->invoiceId);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render(): View
    {
        return view('livewire.billing.settle-invoice-dialogue');
    }
}
```

### 6.4 Pattern 4: Concurrency-Safe Action with Pessimistic Locking & Audit Trail
```php
<?php

declare(strict_types=1);

namespace App\Filament\Actions\Billing;

use App\Domain\Audit\Actions\RecordAuditTrailAction;
use App\Domain\Billing\Models\Invoice;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class VoidInvoiceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'void';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Void Invoice')
            ->color('danger')
            ->icon('heroicon-o-no-symbol')
            ->requiresConfirmation()
            ->modalHeading('Void Invoice Confirmation')
            ->modalDescription('Are you certain you wish to void this invoice? This transaction is irreversible.')
            ->action(function (Invoice $record, RecordAuditTrailAction $auditor): void {
                Gate::authorize('void', $record);

                DB::transaction(function () use ($record, $auditor): void {
                    // Pessimistic row-lock to prevent race conditions
                    $lockedInvoice = Invoice::query()
                        ->lockForUpdate()
                        ->findOrFail($record->id);

                    if ($lockedInvoice->status === 'void') {
                        throw new \DomainException('Invoice is already marked as void.');
                    }

                    $previousState = $lockedInvoice->toArray();
                    $lockedInvoice->status = 'void';
                    $lockedInvoice->voided_at = now();
                    $lockedInvoice->save();

                    $auditor->execute(
                        action: 'invoice.voided',
                        model: $lockedInvoice,
                        oldValues: $previousState,
                        newValues: $lockedInvoice->toArray()
                    );
                });

                Notification::make()
                    ->title('Invoice Voided')
                    ->success()
                    ->send();
            });
    }
}
```

---

## 7. Prohibited Practices and Anti-Patterns

| Anti-Pattern | Root Cause & Mechanism | Architectural & Security Risk | Compliant Engineering Alternative |
| :--- | :--- | :--- | :--- |
| **Missing `#[\Livewire\Attributes\Locked]`** | Exposing record IDs or permission flags as naked public properties. | **Critical IDOR:** Client manipulates snapshot JSON to view/mutate records belonging to other tenants. | Apply `#[\Livewire\Attributes\Locked]` to all IDs, keys, and authorization flags. |
| **UI Hiding as Security** | Using `visible()` or `hidden()` without server-side policy verification in action handlers. | **Privilege Escalation:** Attacker directly invokes Livewire action method via WebSocket or HTTP payload. | Always invoke `Gate::authorize()` or `$this->authorize()` at the start of the action closure. |
| **Un-scoped Relationship Selects** | Calling `Select::relationship('customer')` without `modifyQueryUsing` in multi-tenant panels. | **Cross-Tenant Leakage:** Dropdown renders records from other tenants; allows linking records cross-tenant. | Enforce `modifyQueryUsing(fn ($q) => $q->where('tenant_id', Filament::getTenant()->id))`. |
| **N+1 Relational Table Cascades** | Rendering relation columns (e.g. `customer.name`) without eager loading. | **Database Saturation:** Table with 50 rows executes 51 separate SQL queries; causes high latency. | Declare `->modifyQueryUsing(fn ($q) => $q->with(['customer']))` on the Table schema. |
| **Unindexed Search Columns** | Calling `searchable()` on database columns without a matching MySQL B-Tree index. | **Full Table Scans:** CPU saturation, locks database tables under load, crashes production servers. | Ensure every searchable/sortable column has a migration index (`$table->index('column')`). |
| **Exceeding 300 Physical Lines** | Cramming form schemas, table columns, relation managers, and custom actions into one file. | **Architectural Degradation:** Violates SRP, inhibits code review, causes merge conflicts. | Decompose into separate Schema, Table, Action, and RelationManager classes. |
| **Unbounded In-Memory Aggregations** | Fetching collections via `Model::all()` in widgets to calculate sums or counts. | **Out of Memory Crash:** Consumes gigabytes of RAM; triggers fatal PHP memory errors. | Execute SQL aggregations via `Builder::sum()`, `count()`, or `avg()`. |
| **Direct Model Mutation in UI** | Writing complex multi-step transactions directly inside Resource Page methods. | **Business Logic Leakage:** Bypasses domain invariant checks, untestable without UI harness. | Delegate domain mutations to dedicated single-action classes in `app/Domain/...`. |
| **Unsanitized HTML Rendering** | Calling `TextColumn::make()->html()` on user-supplied raw text. | **Stored XSS:** Malicious script executes in the browser of administrative users. | Sanitize HTML using a server-side HTML purifier before rendering. |
| **Unbounded Table Exports** | Querying all records into an in-memory collection during an HTTP request export. | **HTTP Timeout / OOM:** Worker times out ($504$) or crashes; breaks server capacity. | Offload exports to background queue jobs streaming into private storage. |

---

## 8. Forensic Failure Scenarios & Prevention Matrix

| Failure Scenario | Root Cause | Forensic Manifestation | Engineering Prevention Rule |
| :--- | :--- | :--- | :--- |
| **Cross-Tenant IDOR via Dropdown** | `Select::relationship()` in Invoice Resource lacks tenant scoping closure. | Tenant A inspects `<select>` options in browser; discovers and links Tenant B's customer IDs. | Enforce `modifyQueryUsing(fn ($q) => $q->where('tenant_id', Filament::getTenant()->id))` on every relationship select. |
| **Lost Update Anomaly in Form Edit** | Two admins open the same invoice edit page simultaneously; Admin B overwrites Admin A's changes. | Silent data loss; previous administrator updates are wiped out without audit trace. | Implement optimistic locking via `lock_version` integer verification on form saves. |
| **Database Deadlock under High Traffic** | Bulk actions execute unbounded row updates without sorting by primary key or using chunking. | MySQL error `1213: Deadlock found when trying to get lock; try restarting transaction`. | Enforce deterministic row ordering (`orderBy('id')`) and chunk transactions in groups of 100. |
| **Memory Exhaustion on CSV Export** | Export action calls `Invoice::all()` without streaming or queue offloading. | PHP Fatal Error: `Allowed memory size of 268435456 bytes exhausted (tried to allocate...)`. | Enforce queue-backed exports (`ExportAction`) streaming directly to private disk. |
| **Livewire Checksum Mismatch Exception** | Public component property modified on client without matching HMAC signature. | Livewire client error: `CorruptComponentPayloadException` returned on user interaction. | Mark non-editable state with `#[Locked]`; never mutate signed properties directly on client. |
| **Full Table Scan on Global Search** | Filament global search enabled on `users.email` and `companies.name` without composite indexes. | Slow query log flags `SELECT ... WHERE email LIKE '%term%'` with `rows_examined > 500,000`. | Add explicit B-Tree / Full-Text indexes and replace prefix wildcards with indexed lookups. |
| **Stale Component State in Octane/FPM** | Singleton service bound in Livewire component caches tenant or user across worker requests. | User X in Tenant A sees cached data belonging to User Y in Tenant B on request reload. | Clear request-scoped singletons in `dehydrate()` or use fresh container resolutions via `app()`. |

---

## 9. Testing, Verification & Quality Assurance Standards

### 9.1 Verification Hierarchy & Test Matrix
Every Filament resource and Livewire component must undergo rigorous automated testing using Pest PHP and Filament's testing utilities. Happy-path testing alone is strictly insufficient. The test suite must verify:
1. **Resource Table Rendering:** Verifies column visibility, sort orders, and eager loading query counts.
2. **Form Submission & Validation:** Verifies required fields, unique constraints, and invalid payload rejection.
3. **Execution-Time Authorization:** Verifies that unauthenticated or unauthorized users receive `403 Forbidden`.
4. **Tenant Isolation (Negative Testing):** Verifies that Tenant A cannot view, edit, or delete records belonging to Tenant B.
5. **Atomic Transaction Rollback:** Verifies that a failure during multi-model operations rolls back all database changes.

### 9.2 Practical Pest Test Examples

```php
<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Customer;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Users\Models\User;
use App\Filament\Resources\InvoiceResource\Pages\CreateInvoice;
use App\Filament\Resources\InvoiceResource\Pages\EditInvoice;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
    Filament::setTenant($this->tenant);
});

it('renders invoice table with eager-loaded records within query limits', function (): void {
    Invoice::factory()->count(10)->create(['tenant_id' => $this->tenant->id]);

    \DB::enableQueryLog();

    livewire(ListInvoices::class)
        ->assertCanSeeTableRecords(Invoice::where('tenant_id', $this->tenant->id)->get())
        ->assertSuccessful();

    $queries = count(\DB::getQueryLog());
    expect($queries)->toBeLessThanOrEqual(5);
});

it('validates required fields on invoice creation', function (): void {
    livewire(CreateInvoice::class)
        ->fillForm([
            'invoice_number' => '',
            'customer_id' => null,
            'total_amount_cents' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'invoice_number' => 'required',
            'customer_id' => 'required',
            'total_amount_cents' => 'required',
        ]);
});

it('prevents cross-tenant IDOR when selecting customer', function (): void {
    $foreignTenant = Tenant::factory()->create();
    $foreignCustomer = Customer::factory()->create(['tenant_id' => $foreignTenant->id]);

    livewire(CreateInvoice::class)
        ->fillForm([
            'invoice_number' => 'INV-TEST-001',
            'customer_id' => $foreignCustomer->id,
            'total_amount_cents' => 5000,
            'due_date' => now()->addDays(30)->toDateString(),
        ])
        ->call('create')
        ->assertHasFormErrors(['customer_id']);
});

it('enforces policy authorization on invoice deletion', function (): void {
    $invoice = Invoice::factory()->create(['tenant_id' => $this->tenant->id]);

    // Simulate user lacking delete permission
    $restrictedUser = User::factory()->create(['tenant_id' => $this->tenant->id, 'role' => 'viewer']);
    $this->actingAs($restrictedUser);

    livewire(EditInvoice::class, ['record' => $invoice->getRouteKey()])
        ->assertActionHidden('delete');
});
```

---

## 10. Acceptance Criteria & Quality Gate Checklist

Before approving any Filament 5.x or Livewire deliverable across Universal Core, the engineer or automated review agent must verify:

- [ ] **Strict Typing & Line Limits:** File declares `declare(strict_types=1);` and physical line length is $\le 300$ lines.
- [ ] **Filament 5.x Unified Schema:** Resource forms use `form(Schema $schema): Schema` and Unified Schema components.
- [ ] **Eager Loading Enforcement:** Tables eager-load all relationship columns via `with()` or `modifyQueryUsing()`.
- [ ] **Indexed Query Columns:** Every table column marked `searchable()` or `sortable()` is backed by an index in MySQL 8.4.
- [ ] **Livewire Attribute Security:** All record IDs, tenant IDs, and authorization flags use `#[\Livewire\Attributes\Locked]`.
- [ ] **Computed Properties:** Derived entity lookups use `#[\Livewire\Attributes\Computed]`. No naked model graphs in state.
- [ ] **Server-Side Authorization:** Every action closure enforces server-side policy authorization; never relies on UI hiding.
- [ ] **Multi-Tenant Scoping:** All relationship dropdowns and queries explicitly filter by active tenant (`Filament::getTenant()->id`).
- [ ] **Private Asset Storage:** File uploads containing business or personal data target private storage disks with signed URLs.
- [ ] **Database Integrity:** Multi-model updates and destructive actions execute inside atomic `DB::transaction()` boundaries.
- [ ] **Query Efficiency:** Table rendering for 25 records executes $\le 5$ database queries with zero duplicate queries.
- [ ] **WCAG 2.1 AA Compliance:** Forms, tables, and modal dialogues are fully navigable and operable via keyboard alone.
- [ ] **Automated Test Coverage:** Pest test suite verifies happy paths, validation failures, query counts, and negative IDOR attempts.

---

## 11. References and Related Skills

- **Related Universal Core Shared Skills:**
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-SEC-001` (Secure Coding and Application Security Standards)
  - `SKILL-PERF-001` (Performance Profiling and Optimization Standards)
  - `SKILL-TEST-001` (Testing and Quality Assurance Standards)
  - `SKILL-ARCH-001` (Software Architecture, DDD and Modular Standards)
  - `SKILL-TENANT-001` (Multi-Tenant Architecture and Isolation Standards)
  - `SKILL-QUEUE-001` (Queue and Asynchronous Architecture Standards)
  - `SKILL-ERR-001` (Exception Handling, Telemetry and Observability Standards)
  - `SKILL-A11Y-001` (Web Accessibility and WCAG 2.1 AA Standards)
  - `SKILL-REV-001` (Code Review and Architecture Quality Standards)

- **Authoritative Technical Documentation:**
  - Filament 5.x Documentation (`https://filamentphp.com/docs`)
  - Livewire 4.x Official Documentation (`https://livewire.laravel.com/docs`)
  - Laravel 11.x Official Documentation (`https://laravel.com/docs/11.x`)
  - PHP 8.3+ Reference Manual (`https://www.php.net/docs.php`)
  - OWASP Application Security Verification Standard (ASVS 4.0.3)
  - W3C Web Content Accessibility Guidelines (WCAG 2.1 AA)

# Shared Engineering Skill: Vue.js and Inertia Enterprise Architecture Standards

**Skill ID:** SKILL-VUE-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  
**Approved Baseline:** Vue 3, TypeScript 5+, Inertia.js (Official Laravel & Vue Adapters), Laravel 11.x, PHP 8.3+, Node.js 20+, npm 10+, Vite, Pinia (conditional), Vitest, Vue Test Utils, Playwright

---

## 1. Skill Identity, Scope, and Enterprise Baseline

The **Vue.js and Inertia Enterprise Architecture Standards** skill establishes the authoritative engineering standard for architecting, implementing, reviewing, securing, optimizing, testing, and operating Vue 3 applications integrated with Laravel via Inertia.js across Universal Core.

### 1.1 Mission and Purpose
This skill equips Principal Vue Engineers and autonomous engineering agents to deliver cohesive, type-safe, accessible, and high-performance frontend applications seamlessly coupled to Laravel server-driven architectures. It enforces:
- **Flawless Type Soundness:** End-to-end type safety powered by TypeScript 5+ strict mode, Vue compiler macros (`defineProps`, `defineEmits`, `defineSlots`), and strongly typed Inertia page contracts.
- **Server-Driven Invariants:** Uncompromising preservation of Laravel as the single source of truth for business logic, validation, authorization, and data persistence, eliminating split-brain client-server state.
- **Explicit Version Boundaries:** Precise delineation between Inertia v1 baseline capabilities and Inertia v2 advanced features, preventing version contamination and invalid API assumptions.
- **Defense-in-Depth Security:** Robust protection against Cross-Site Scripting (XSS), token leakage, Cross-Site Request Forgery (CSRF), and Insecure Direct Object References (IDOR), treating all client inputs and route parameters as untrusted.
- **Strict Multi-Tenant Isolation:** Ironclad data isolation enforced at the server query boundary, ensuring client state never leaks cross-tenant information.
- **Accessible & Inclusive UX:** Full conformance to WCAG 2.2 Level AA standards verified via automated scanners (`axe-core`) and manual assistive interaction models.
- **Measurable Operability:** Measurable performance budgets (Core Web Vitals), graceful degradation, observable error boundaries, and deterministic automated testing across unit, component, and E2E suites.

### 1.2 Approved Technology Baseline and Dependency Classifications
Engineering artifacts, canonical code examples, and standard toolchains governed by this skill adhere to the approved Universal Core baseline while accommodating project-specific profiles.

#### 1.2.1 Universal Core Baseline and Version Boundaries

| Technology | Approved Core Baseline | Operational Boundary & Version-Specific Invariants | Classification |
| :--- | :--- | :--- | :--- |
| **Vue Runtime** | `Vue 3.4+` | **Baseline Primitives:** Composition API with `<script setup lang="ts">`, native compiler macros (`defineProps`, `defineEmits`, `defineSlots`, `defineExpose`, `withDefaults`), `shallowRef`, `computed`, `watchEffect`.<br>**Prohibitions:** Options API is prohibited for new production code. Vue 2 legacy APIs (`Vue.set`, `Vue.delete`, `$listeners`, filter pipes `{{ val \| filter }}`) are strictly forbidden. | Mandatory Core Invariant |
| **TypeScript** | `TypeScript 5.x` | `"strict": true`, `"noUncheckedIndexedAccess": true`, `"exactOptionalPropertyTypes": true`. Use `satisfies`, discriminated unions, generic components, and typed composables. | Mandatory Core Invariant |
| **Inertia.js Client** | `@inertiajs/vue3` (v1.x baseline / v2.x progressive) | **Baseline (v1.x):** `useForm`, `usePage`, `router.visit`, `router.get`, `router.post`, `router.put`, `router.patch`, `router.delete`, `router.reload`, `Link`, `Head`, partial reloads (`only`), preserve scroll/state.<br>**Progressive (v2.x):** `Inertia::optional()`, `Inertia::defer()` on server with `<Deferred>` on client, `Inertia::merge()`, `Inertia::scroll()` on server with `<InfiniteScroll>` on client, polling (`usePoll`, `router.poll`), prefetching (`<Link prefetch>`, `router.prefetch()`), reload exclusions (`except`).<br>**Deprecation Notice:** `Inertia::lazy()` is deprecated in Inertia v2 in favor of `Inertia::optional()` (and removed in v3).<br>**Rule:** Never invoke v2 APIs without verifying `package.json` and `composer.json` versions. | Mandatory Core Invariant |
| **Laravel Backend** | `Laravel 11.x` / `PHP 8.3+` | `inertiajs/inertia-laravel` adapter (`^1.0.0` or `^2.0.0`), `HandleInertiaRequests` middleware, Form Requests for validation, Policies for authorization, API Resources / DTOs for prop serialization. | Mandatory Core Invariant |
| **Node.js** | `Node.js 20 LTS` | Active LTS runtime for local development, build tooling (Vite), and CI pipeline execution. | Standard Core Runtime Baseline |
| **Package Manager**| `npm 10+` | Standard reference package manager with deterministic lockfile enforcement (`npm ci`). Alternative package managers (e.g., `pnpm`, `yarn`) require designated Project Profiles. | Reference Toolchain Standard |
| **Build Tooling** | `Vite 5+` | Native ES module bundling with `@vitejs/plugin-vue`, asset fingerprinting, rollup chunking, and Inertia page resolution via `laravel-vite-plugin`. | Mandatory Core Invariant |
| **State Management**| `Pinia 2.x` (Conditional) | Permitted **only** when cross-page client-only state is technically justified (e.g. multi-step client workflows, transient UI drawer states). Forbidden for caching server data that belongs in Inertia page props. | Conditional Architectural Library |
| **Testing** | `Vitest` + `Vue Test Utils` | Component and unit testing utilizing `@vue/test-utils` and `happy-dom`/`jsdom`. Interception and mock boundaries strictly isolated. | Reference Toolchain Standard |
| **Browser E2E** | `Playwright` | Multi-browser headless automation (Chromium, Firefox, WebKit) for end-to-end integration and accessibility audits via `@axe-core/playwright`. | Reference Toolchain Standard |
| **Static Analysis**| `ESLint 9+` & `Prettier 3+` | Flat configuration (`eslint.config.js`) enforcing `plugin:vue/vue3-recommended`, `@typescript-eslint/recommended-type-checked`, and `eslint-plugin-vuejs-accessibility`. | Reference Toolchain Standard |

#### 1.2.2 Dependency Classification: Mandatory Dependencies vs. Optional Architectural Libraries
To prevent architectural bloat and fragile dependencies, consuming projects must distinguish mandatory dependencies from optional libraries:

- **Mandatory Architectural Dependencies:**
  - `vue` (`^3.4.0`): Core reactive UI runtime.
  - `@inertiajs/vue3` (`^1.2.0` or `^2.0.0` depending on Project Profile): Official client adapter.
  - `typescript` (`^5.0.0`): Static typing and compiler checks.
  - `@vitejs/plugin-vue` and `laravel-vite-plugin`: Vite integration and asset bundling.
  - Runtime Schema Validation (e.g., `zod` `^3.22.0` or `valibot`): Enforces boundary safety for external or untrusted client-side payloads (e.g. webhooks, third-party embeds, localStorage).
- **Optional / Conditional Architectural Libraries:**
  - **Shared Client State:** `pinia` (`^2.1.0`). Use only when client-side state cannot be maintained via Inertia props, URL query parameters, or Vue Provide/Inject.
  - **DOM Sanitization:** `dompurify` (`^3.0.0`). Strictly required whenever rendering external or rich-text HTML via `v-html`.
  - **Virtualization:** `@tanstack/vue-virtual` (`^3.0.0`). Recommended when rendering collections exceeding 100 DOM nodes.
  - **Icons:** `lucide-vue-next`. Accessible, tree-shakeable iconography.

> [!IMPORTANT]
> **Universal Core Inertia Baseline Status and Ambiguity Governance:**  
> The approved Universal Core ecosystem matrix (`version-compatibility-standards.md` §5.1) authorizes `Vue 3.x / Inertia.js 1.x+` alongside `PHP 8.3+` and `Laravel 11.x`. However, no repository-level package manifest (`package.json`, `composer.json`) or authoritative Architecture Decision Record (ADR) currently designates whether Inertia v1 (`@inertiajs/vue3` 1.x / `inertia-laravel` 1.x) or Inertia v2 (`@inertiajs/vue3` 2.x / `inertia-laravel` 2.x) serves as the singular Universal Core mandatory baseline.  
> To guarantee architectural integrity and prevent unauthorized baseline shifts:
> 1. Neither Inertia v1 nor Inertia v2 is silently assumed or designated as the exclusive Universal Core baseline.
> 2. Both version tracks are maintained with strict API separation (see Section 7.3).
> 3. Autonomous agents and engineers must inspect the consuming application's `package.json` and `composer.json` before authoring version-specific Inertia APIs. Never cross-contaminate v1 and v2 APIs.

#### 1.2.3 Tooling Baseline Governance
Tooling versions represent the approved Universal Core reference standard for new applications and canonical CI pipelines. Tool versions are **not** universally mandatory across every legacy application unless mandated by an approved Project Technology Profile. Consuming projects maintain compliance by adhering to the core architectural principles, TypeScript strictness, security controls, and verification gates without incurring unnecessary tooling churn.

### 1.3 Architectural Scope and Technology Separation
- **Inertia-Driven Architecture:** Vue serves as the client-side view layer of a monolithic Laravel application. Laravel handles routing, authentication, authorization, session management, and business rules. Vue renders view components using props provided by Laravel controllers.
- **Separation from Decoupled SPAs:** Inertia applications must **never** be architected as standalone SPAs consuming independent REST/GraphQL APIs. Client-side routing libraries (such as `vue-router`) are strictly prohibited in Inertia applications, as routing is exclusively governed by Laravel routes.
- **Universal Core Governance (300-Line Rule):** No code file (including `.vue`, `.ts`, `.tsx`, `.js`, `.jsx`, and `.css`) may exceed **300 physical lines** by default. If a component, composable, or utility approaches this limit, it must be decomposed into cohesive sub-components, custom composables, or domain utilities. Any exception requires specific technical justification, documented explanation of why decomposition is unsuitable, explicit prior human user approval, and a recorded exception scope and affected file. No agent, automated process, validator, or department manager may grant itself an exception. Approval for one file does not establish a general exception for other files. Unauthorized oversized files must be treated as non-compliant. Non-code artifacts follow their respective standards.

### 1.4 Ten Principles of Enterprise Vue + Inertia Engineering
1. **Server-Side Primacy:** Business rules, validation, permissions, and database operations belong exclusively to Laravel. Vue is an expressive, reactive view layer.
2. **Type Soundness at All Boundaries:** All props, emits, page data, and composable returns must be statically typed with zero implicit `any`.
3. **Explicit State Ownership:** Maintain exactly one source of truth. Never mirror Inertia page props into local reactive state unless explicitly creating an isolated mutable draft.
4. **Defense-in-Depth Security:** Never rely on frontend controls or navigation guards for security. Every mutation and query must be independently authenticated and authorized on the server.
5. **Strict Tenant and Data Isolation:** Multi-tenancy is enforced on the server via query scoping and authorization policies. The client never participates in isolation decisions.
6. **Measurable Performance Over Speculation:** Optimize rendering, network visits, and asset loading based on measured profiler data and Core Web Vitals, not unverified assumptions.
7. **Accessible by Default:** All user interfaces must achieve WCAG 2.2 AA compliance through semantic HTML, full keyboard operability, and proper ARIA labeling.
8. **Composition Over Premature Abstraction:** Build reusable composables and UI primitives following the Rule of Three. Do not author speculative wrapper layers over native framework primitives.
9. **Resilient Error Recovery:** Handle network drops, 419 session expirations, and 422 validation errors gracefully with clear user feedback and recovery flows.
10. **Zero Completion Without Verification:** No feature is complete until verified by `vue-tsc --noEmit`, ESLint, unit/component tests, and end-to-end user journey validations.

---

## 2. Domain A: Vue Architecture and Application Design

### 2.1 Directory Structure and Feature-Oriented Organization
Enterprise Vue + Inertia applications within Laravel must be organized under `resources/js/` utilizing a hybrid domain-feature architecture that maintains high cohesion and loose coupling.

```
resources/js/
├── app.ts                      # Application bootstrapper (createInertiaApp)
├── ssr.ts                      # Server-side rendering bootstrapper (if enabled)
├── components/                 # Shared domain-agnostic UI primitives (Design System)
│   ├── ui/
│   │   ├── BaseButton.vue
│   │   ├── BaseInput.vue
│   │   ├── BaseModal.vue
│   │   └── BaseTable.vue
│   └── feedback/
│       ├── ErrorBoundary.vue
│       └── ToastContainer.vue
├── composables/                # Global domain-agnostic composables
│   ├── useDebounce.ts
│   ├── useFocusTrap.ts
│   └── useMediaQuery.ts
├── layouts/                    # Persistent layout components
│   ├── AppLayout.vue
│   ├── GuestLayout.vue
│   └── SettingsLayout.vue
├── features/                   # Feature-specific modules (business domain logic)
│   ├── billing/
│   │   ├── components/         # Feature-private components
│   │   │   ├── InvoiceTable.vue
│   │   │   └── PaymentMethodCard.vue
│   │   ├── composables/        # Feature-private composables
│   │   │   └── useInvoiceFilter.ts
│   │   └── types.ts            # Feature-private DTOs and contracts
│   └── user-management/
├── pages/                      # Inertia page components mapped 1:1 to Laravel controllers
│   ├── Billing/
│   │   ├── Index.vue
│   │   └── Show.vue
│   ├── Dashboard.vue
│   └── Profile/
│       └── Edit.vue
└── types/                      # Global TypeScript definitions
    ├── generated.d.ts          # Server-generated TypeScript types (via Spatie or similar)
    ├── global.d.ts             # Window & environment augmentations
    └── inertia.d.ts            # Shared PageProps contracts
```

### 2.2 Four-Layer Responsibility Separation
Every Vue application integrated with Inertia must maintain rigid boundaries across four architectural layers:

```
[ User Interaction ] ──> [ Presentation Layer (.vue SFCs) ]
                                    │
                                    ▼
                         [ Application Logic (Composables) ]
                               │                 │
                               ▼                 ▼
          [ Domain Logic (Pure TS) ]     [ Infrastructure Layer (Inertia router / API) ]
                                                 │
                                                 ▼
                                    [ Laravel Server Boundary ]
```

1. **Presentation Layer (SFCs):** Responsible for template rendering, styling, accessible attributes, and dispatching user events. Must contain minimal inline business logic.
2. **Application Logic Layer (Composables):** Encapsulates reactive state transitions, form coordination, debouncing, and interaction flows.
3. **Domain Logic Layer (Pure TypeScript):** Pure functions executing calculations, data transformations, formatters, and invariants. Completely decoupled from Vue reactivity and DOM.
4. **Infrastructure Layer:** The Inertia `router`, HTTP clients, WebSocket listeners (Laravel Echo), and browser APIs (storage, clipboard).

### 2.3 Composable Architecture Standards
Composables represent the standard mechanism for encapsulating and sharing stateful application logic. All composables must adhere to the following rules:
- **Naming Convention:** Must begin with the prefix `use` (e.g., `useInvoiceFilter`, `usePagination`).
- **Flexible Argument Unwrapping:** Accept `MaybeRefOrGetter<T>` where appropriate to support raw values, refs, or reactive getter functions, utilizing `toValue()` from Vue 3.3+.
- **Explicit Return Contracts:** Always return a plain object containing strongly typed `Ref<T>`, `ComputedRef<T>`, or methods. Never return a reactive object that loses reactivity upon destructuring.
- **Resource Cleanup Invariant:** Any composable that registers event listeners, intervals, timers, or WebSocket subscriptions must clean them up within `onScopeDispose()` or `onUnmounted()`.

```ts
// resources/js/composables/useDebounceSearch.ts
import { ref, watch, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';

export interface UseDebounceSearchOptions {
    baseUrl: string;
    searchParam?: string;
    delayMs?: number;
    preserveScroll?: boolean;
}

export function useDebounceSearch(
    initialValue: string,
    options: UseDebounceSearchOptions
): {
    searchTerm: Ref<string>;
    isSearching: Ref<boolean>;
    resetSearch: () => void;
} {
    const searchTerm = ref<string>(initialValue);
    const isSearching = ref<boolean>(false);
    const paramKey = options.searchParam ?? 'search';
    const delay = options.delayMs ?? 300;

    let timeoutId: ReturnType<typeof setTimeout> | null = null;

    watch(searchTerm, (newQuery) => {
        isSearching.value = true;
        if (timeoutId) clearTimeout(timeoutId);

        timeoutId = setTimeout(() => {
            router.get(
                options.baseUrl,
                { [paramKey]: newQuery || undefined },
                {
                    preserveState: true,
                    preserveScroll: options.preserveScroll ?? true,
                    replace: true,
                    onFinish: () => {
                        isSearching.value = false;
                    },
                }
            );
        }, delay);
    });

    const resetSearch = (): void => {
        searchTerm.value = '';
    };

    return { searchTerm, isSearching, resetSearch };
}
```

### 2.4 Application Bootstrapping and Plugin Registration
Inertia applications bootstrap in `resources/js/app.ts` via `createInertiaApp`. Bootstrapping must register essential plugins safely and configure the global error handler.

```ts
// resources/js/app.ts
import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel Application';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);

        app.config.errorHandler = (err, instance, info) => {
            console.error('[Vue Global Error]:', err, info);
            // Telemetry reporting with PII scrubbing should be invoked here
        };

        app.mount(el);
    },
    progress: {
        color: '#4F46E5',
        showSpinner: false,
    },
});
```

### 2.5 Page Routing and Persistent Layouts
Inertia eliminates client routers by swapping root page components based on server responses. To prevent layout re-rendering and preserve UI state across page navigations, persistent layouts must be assigned using the `layout` property via `defineOptions`:

```vue
<!-- resources/js/pages/Billing/Index.vue -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps } from '@/types/inertia';

defineOptions({ layout: AppLayout });

interface BillingIndexProps extends PageProps {
    invoices: Array<{ id: string; amount: number; status: string }>;
}

defineProps<BillingIndexProps>();
</script>

<template>
    <Head title="Billing & Invoices" />
    <section aria-labelledby="billing-heading">
        <h1 id="billing-heading" class="text-2xl font-bold">Billing & Invoices</h1>
        <!-- Content rendered inside persistent AppLayout -->
    </section>
</template>
```

### 2.6 Architecture Decision Records (ADRs)
When making significant architectural choices (e.g. adopting Pinia, introducing large list virtualization, selecting a rich-text editor), document the rationale in `docs/adr/` using the following schema:
- **Title & Status:** ADR-00X: Decision Title (Proposed / Accepted / Deprecated).
- **Context:** Business drivers, technical constraints, and alternatives evaluated.
- **Decision:** The chosen architecture, library, or design pattern.
- **Consequences:** Trade-offs, bundle impact, maintenance burden, and security considerations.

---

## 3. Domain B: Advanced Vue Component Engineering

### 3.1 Single File Components with `<script setup lang="ts">`
All new components across Universal Core must use `<script setup lang="ts">`. Options API and untyped script blocks are strictly prohibited. The compiler-macro execution model ensures optimal runtime performance, minimal boilerplate, and zero-cost type inference.

### 3.2 Props, Emits, and Component Contracts
Define component APIs strictly using TypeScript type arguments within compiler macros. Never use runtime object prop declarations.

```vue
<!-- resources/js/components/ui/StatusBadge.vue -->
<script setup lang="ts">
export type BadgeVariant = 'success' | 'warning' | 'danger' | 'neutral';

export interface StatusBadgeProps {
    label: string;
    variant?: BadgeVariant;
    interactive?: boolean;
}

const props = withDefaults(defineProps<StatusBadgeProps>(), {
    variant: 'neutral',
    interactive: false,
});

const emit = defineEmits<{
    (e: 'dismiss'): void;
    (e: 'click', event: MouseEvent): void;
}>();
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            variant === 'success' && 'bg-green-100 text-green-800',
            variant === 'warning' && 'bg-yellow-100 text-yellow-800',
            variant === 'danger' && 'bg-red-100 text-red-800',
            variant === 'neutral' && 'bg-gray-100 text-gray-800',
            interactive && 'cursor-pointer hover:opacity-80'
        ]"
        @click="interactive && emit('click', $event)"
    >
        {{ label }}
        <button
            v-if="interactive"
            type="button"
            class="ml-1 inline-flex text-gray-400 hover:text-gray-600 focus:outline-none"
            aria-label="Dismiss badge"
            @click.stop="emit('dismiss')"
        >
            &times;
        </button>
    </span>
</template>
```

### 3.3 Controlled Component Patterns and Multiple `v-model` Bindings
Vue 3 natively supports multiple two-way bindings on a single component. When engineering form controls, use `defineModel` (Vue 3.4+) or explicit prop/emit pairs for backwards compatibility with Vue 3.3:

```vue
<!-- resources/js/components/ui/DateRangePicker.vue -->
<script setup lang="ts">
// Vue 3.4+ defineModel syntax provides compile-time macro safety:
const startDate = defineModel<string>('startDate', { required: true });
const endDate = defineModel<string>('endDate', { required: true });

defineProps<{
    disabled?: boolean;
    label: string;
}>();
</script>

<template>
    <fieldset class="space-y-2">
        <legend class="text-sm font-medium text-gray-700">{{ label }}</legend>
        <div class="flex items-center gap-2">
            <input
                type="date"
                v-model="startDate"
                :disabled="disabled"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                aria-label="Start date"
            />
            <span class="text-gray-500">to</span>
            <input
                type="date"
                v-model="endDate"
                :disabled="disabled"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                aria-label="End date"
            />
        </div>
    </fieldset>
</template>
```

### 3.4 Typed Slots via `defineSlots`
When designing container or template-driven components, declare slot parameters strictly using `defineSlots` (Vue 3.3+):

```vue
<!-- resources/js/components/ui/DataList.vue -->
<script setup lang="ts" generic="T extends { id: string | number }">
defineProps<{
    items: T[];
    loading?: boolean;
}>();

defineSlots<{
    default(props: { item: T; index: number }): any;
    empty(): any;
    header(): any;
}>();
</script>

<template>
    <div class="divide-y divide-gray-200">
        <header v-if="$slots.header" class="py-2">
            <slot name="header" />
        </header>
        <template v-if="items.length > 0">
            <div v-for="(item, index) in items" :key="item.id" class="py-3">
                <slot :item="item" :index="index" />
            </div>
        </template>
        <div v-else-if="$slots.empty" class="py-6 text-center text-gray-500">
            <slot name="empty" />
        </div>
    </div>
</template>
```

### 3.5 Provide / Inject with Explicit Ownership Rules
To avoid rogue mutability and untyped injection bugs across deep component hierarchies:
1. **Typed Injection Keys:** Always define an `InjectionKey<T>` using `Symbol()`.
2. **Readonly Invariant:** Never provide raw mutable state directly. Provide a `Readonly<Ref<T>>` and expose explicit mutating methods.

```ts
// resources/js/types/keys.ts
import type { InjectionKey, Ref } from 'vue';

export interface AccordionContext {
    activeId: Readonly<Ref<string | null>>;
    setActiveId: (id: string) => void;
}

export const ACCORDION_KEY: InjectionKey<AccordionContext> = Symbol('ACCORDION_KEY');
```

```vue
<!-- resources/js/components/ui/Accordion.vue -->
<script setup lang="ts">
import { ref, readonly, provide } from 'vue';
import { ACCORDION_KEY } from '@/types/keys';

const props = defineProps<{ defaultActiveId?: string }>();
const activeId = ref<string | null>(props.defaultActiveId ?? null);

const setActiveId = (id: string): void => {
    activeId.value = activeId.value === id ? null : id;
};

provide(ACCORDION_KEY, {
    activeId: readonly(activeId),
    setActiveId,
});
</script>

<template>
    <div class="divide-y divide-gray-200 rounded-lg border border-gray-200">
        <slot />
    </div>
</template>
```

### 3.6 Error Boundaries and Component Failure Isolation
To prevent a rendering error in an isolated component (e.g., a data visualization widget) from crashing the entire page, implement component error boundaries using `onErrorCaptured`:

```vue
<!-- resources/js/components/feedback/ErrorBoundary.vue -->
<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue';

const error = ref<Error | null>(null);

onErrorCaptured((err, instance, info) => {
    error.value = err instanceof Error ? err : new Error(String(err));
    console.error('[Component Error Captured]:', err, info);
    // Return false to prevent the error from propagating to the global handler
    return false;
});

const reset = (): void => {
    error.value = null;
};
</script>

<template>
    <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4" role="alert">
        <h3 class="text-sm font-semibold text-red-800">Widget encountered an error</h3>
        <p class="mt-1 text-xs text-red-600">{{ error.message }}</p>
        <button
            type="button"
            class="mt-3 rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-500"
            @click="reset"
        >
            Retry
        </button>
    </div>
    <slot v-else />
</template>
```

---

## 4. Domain C: TypeScript Engineering

### 4.1 Strict Configuration and Compilation Standard
All Vue + Inertia applications governed by Universal Core must enforce strict TypeScript configuration in `tsconfig.json`:

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "exactOptionalPropertyTypes": true,
    "noImplicitOverride": true,
    "allowSyntheticDefaultImports": true,
    "isolatedModules": true,
    "skipLibCheck": true,
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.d.ts", "resources/js/**/*.vue"]
}
```

CI pipelines must execute `vue-tsc --noEmit` to verify type safety across both TypeScript source files and `.vue` Single File Components.

### 4.2 Strongly Typed Inertia Page Props
Define global page properties and domain props using an extensible interface structure in `resources/js/types/inertia.d.ts`:

```ts
// resources/js/types/inertia.d.ts
export interface UserSession {
    id: number;
    name: string;
    email: string;
    tenant_id: number;
    roles: string[];
}

export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export interface PageProps<T = Record<string, unknown>> {
    auth: {
        user: UserSession | null;
        permissions: Record<string, boolean>;
    };
    flash: FlashMessages;
    errors: Record<string, string>;
    [key: string]: unknown;
}
```

Inertia page components extend `PageProps` to guarantee compile-time safety when accessing props or the Inertia `usePage` hook:

```vue
<!-- resources/js/pages/Profile/Edit.vue -->
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PageProps, UserSession } from '@/types/inertia';

interface ProfileEditProps extends PageProps {
    mustVerifyEmail: boolean;
    status?: string;
}

const props = defineProps<ProfileEditProps>();
const page = usePage<ProfileEditProps>();

// page.props.auth.user is typed as UserSession | null
</script>
```

### 4.3 Discriminated Unions and Exhaustive Type Narrowing
When handling dynamic state machines (e.g. async operations, workflow states), use discriminated unions and enforce exhaustive type checks using the `assertNever` helper:

```ts
// resources/js/utils/typeGuards.ts
export function assertNever(x: never): never {
    throw new Error(`Unhandled union variant encountered: ${JSON.stringify(x)}`);
}

export type AsyncState<T> =
    | { status: 'idle' }
    | { status: 'loading' }
    | { status: 'success'; data: T }
    | { status: 'error'; error: Error };

export function formatStatus<T>(state: AsyncState<T>): string {
    switch (state.status) {
        case 'idle':
            return 'Ready';
        case 'loading':
            return 'Loading...';
        case 'success':
            return 'Completed';
        case 'error':
            return `Failed: ${state.error.message}`;
        default:
            return assertNever(state);
    }
}
```

### 4.4 Runtime Schema Validation at Untrusted Boundaries
While TypeScript ensures compile-time contracts, untrusted external data (such as data retrieved from third-party APIs, webhooks, or browser `localStorage`) must be validated at runtime using a schema library (e.g., Zod):

```ts
import { z } from 'zod';

export const UserPreferenceSchema = z.object({
    theme: z.enum(['light', 'dark', 'system']),
    pageSize: z.number().int().min(10).max(100),
    notificationsEnabled: z.boolean(),
});

export type UserPreferences = z.infer<typeof UserPreferenceSchema>;

export function loadUserPreferences(): UserPreferences {
    const raw = localStorage.getItem('user_prefs');
    if (!raw) {
        return { theme: 'system', pageSize: 25, notificationsEnabled: true };
    }
    try {
        const parsed = JSON.parse(raw);
        return UserPreferenceSchema.parse(parsed);
    } catch (e) {
        console.warn('Invalid user preferences detected in storage. Resetting to defaults.');
        return { theme: 'system', pageSize: 25, notificationsEnabled: true };
    }
}
```

---

## 5. Domain D: Reactivity System and State Management

### 5.1 Reactivity Foundations: `ref` vs `reactive`
- **Standard Preference:** Always prefer `ref()` for reactive state. `ref` provides clear `.value` access semantics, works with primitive and complex types, and survives destructuring when passed across function boundaries.
- **`reactive()` Cautions:** Reserve `reactive()` exclusively for state packages that are never destructured directly. Destructuring properties from a `reactive()` object strips Vue reactivity unless wrapped in `toRefs()`.

```ts
// INCORRECT: Destructuring loses reactivity
import { reactive } from 'vue';
const state = reactive({ count: 0, query: '' });
const { count } = state; // count is now a static number!

// CORRECT: Using toRefs or ref
import { toRefs } from 'vue';
const { count: reactiveCount } = toRefs(state); // reactiveCount is Ref<number>
```

### 5.2 Deep vs Shallow Reactivity
Vue 3's `ref()` creates deep reactivity by default, wrapping every nested object in a `Proxy`. When storing large, read-only datasets (e.g. charting datasets, complex geometry, or external SDK instances), deep reactivity creates substantial memory overhead. Use `shallowRef()` to optimize performance:

```ts
import { shallowRef } from 'vue';
import type { ChartInstance } from '@/types/chart';

// shallowRef tracks mutations only when .value is reassigned, avoiding proxying the complex chart instance
const chartInstance = shallowRef<ChartInstance | null>(null);
```

### 5.3 Computed State vs Duplicated State
**Anti-Pattern:** Duplicating props into local state. Never copy an Inertia prop into a local `ref` unless explicitly creating an isolated, temporary draft for editing:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    user: { firstName: string; lastName: string };
}>();

// INCORRECT: Split-brain state. If server updates user prop, fullName stays stale!
const fullNameStale = ref(`${props.user.firstName} ${props.user.lastName}`);

// CORRECT: Computed property automatically updates when props change
const fullName = computed(() => `${props.user.firstName} ${props.user.lastName}`);
</script>
```

### 5.4 Watcher Hygiene and Stale Asynchronous Race Conditions
Watchers that trigger asynchronous requests must handle cancellation of stale requests to avoid race conditions. Use the `onCleanup` callback provided by `watch`:

```ts
import { ref, watch } from 'vue';

const searchQuery = ref('');
const searchResults = ref<string[]>([]);

watch(searchQuery, (newQuery, _, onCleanup) => {
    const controller = new AbortController();

    // Register cleanup to cancel fetch if searchQuery changes before request resolves
    onCleanup(() => {
        controller.abort();
    });

    fetch(`/api/search?q=${encodeURIComponent(newQuery)}`, { signal: controller.signal })
        .then((res) => res.json())
        .then((data) => {
            searchResults.value = data;
        })
        .catch((err) => {
            if (err.name !== 'AbortError') {
                console.error('Search request failed', err);
            }
        });
});
```

### 5.5 State Colocation Hierarchy and Pinia Usage
State must be colocated as close as possible to the components that consume it:
1. **Local Component State:** Ephemeral UI state (e.g., dropdown open, modal visible) lives inside the component via `ref()`.
2. **Feature / Composable State:** Shared across a feature boundary via custom composables or Provide/Inject.
3. **Inertia Page Props:** All business and domain data provided by Laravel.
4. **Pinia Stores:** Permitted **only** for client-side state that spans across multiple page navigations and cannot be maintained in the URL or server session.

#### Justified vs Unjustified Pinia Usage Matrix:

| Use Case | Recommended Architecture | Pinia Permitted? | Rationale |
| :--- | :--- | :--- | :--- |
| **Inertia Page Data (e.g. Invoices list)** | Inertia Page Props | **NO** | Server owns the data. Mirroring into Pinia creates synchronization bugs. |
| **User Authentication / Permissions** | `HandleInertiaRequests` shared props | **NO** | Delivered on every Inertia visit automatically. |
| **Multi-step Client Wizard / Draft** | Pinia Setup Store | **YES** | Multi-screen state held exclusively on client before submission. |
| **Client UI Preferences (e.g. Sidebar collapsed)** | Composable + localStorage | **NO** | Lightweight composable (`useSidebar`) is sufficient; Pinia adds overhead. |
| **Global Client Notifications Queue** | Pinia Setup Store | **YES** | Cross-cutting client-triggered notifications surviving page visits. |

When Pinia is justified, always author **Setup Stores** using Composition API idioms:

```ts
// resources/js/stores/useNotificationStore.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface NotificationItem {
    id: string;
    message: string;
    type: 'info' | 'success' | 'warning' | 'error';
}

export const useNotificationStore = defineStore('notifications', () => {
    const items = ref<NotificationItem[]>([]);

    function addNotification(item: Omit<NotificationItem, 'id'>): void {
        const id = crypto.randomUUID();
        items.value.push({ ...item, id });
        setTimeout(() => removeNotification(id), 5000);
    }

    function removeNotification(id: string): void {
        items.value = items.value.filter((i) => i.id !== id);
    }

    return { items, addNotification, removeNotification };
});
```

---

## 6. Domain E: Rendering and Performance Engineering

### 6.1 Rendering Lifecycle and Update Boundaries
Vue 3 compiles templates into optimized Virtual DOM render functions with compile-time hints (patch flags, dynamic children tracking). To ensure high-velocity rendering:
- **Stable Keys:** Always supply a unique, primitive key (such as a database `id` or `uuid`) to `v-for`. Never use array indices for lists that can be filtered, sorted, or mutated.
- **Computed Stability:** Computed getters must be pure functions free of side effects. Never mutate reactive state inside a computed property.
- **Directive Optimization:**
  - Use `v-once` for static headers or content blocks that never change after initial render.
  - Use `v-memo="[dep1, dep2]"` for computationally heavy table rows or list items, skipping VNode creation unless specified dependencies change.

```vue
<!-- Optimizing large table row rendering with v-memo -->
<template>
    <tr
        v-for="invoice in invoices"
        :key="invoice.id"
        v-memo="[invoice.status, invoice.amount, isSelected(invoice.id)]"
    >
        <td>{{ invoice.number }}</td>
        <td>{{ invoice.amount }}</td>
        <td>{{ invoice.status }}</td>
    </tr>
</template>
```

### 6.2 Code Splitting and Asynchronous Components
Inertia applications bundled with Vite automatically code-split pages via `import.meta.glob('./pages/**/*.vue')` in `app.ts`. For heavy modals, charts, or editors within a page, defer loading using `defineAsyncComponent`:

```vue
<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import ComponentSkeleton from '@/components/feedback/ComponentSkeleton.vue';

const HeavyChart = defineAsyncComponent({
    loader: () => import('@/features/analytics/components/HeavyChart.vue'),
    loadingComponent: ComponentSkeleton,
    delay: 200,
    timeout: 10000,
});
</script>

<template>
    <div class="mt-8">
        <HeavyChart :data="chartData" />
    </div>
</template>
```

### 6.3 Large Collection Rendering and Virtualization
When rendering tables or lists exceeding 100 DOM nodes, native DOM rendering causes input latency and memory pressure. Virtualize collections using a virtualizer (e.g. `@tanstack/vue-virtual`):

```vue
<!-- Virtualized list pattern -->
<script setup lang="ts">
import { ref } from 'vue';
import { useVirtualizer } from '@tanstack/vue-virtual';

const props = defineProps<{ items: Array<{ id: string; name: string }> }>();
const parentRef = ref<HTMLElement | null>(null);

const rowVirtualizer = useVirtualizer({
    count: props.items.length,
    getScrollElement: () => parentRef.value,
    estimateSize: () => 48,
    overscan: 5,
});
</script>

<template>
    <div ref="parentRef" class="h-96 overflow-auto border rounded">
        <div :style="{ height: `${rowVirtualizer.getTotalSize()}px`, position: 'relative' }">
            <div
                v-for="virtualRow in rowVirtualizer.getVirtualItems()"
                :key="virtualRow.key"
                :style="{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: `${virtualRow.size}px`,
                    transform: `translateY(${virtualRow.start}px)`,
                }"
                class="flex items-center px-4 border-b"
            >
                {{ items[virtualRow.index]?.name }}
            </div>
        </div>
    </div>
</template>
```

### 6.4 Measurable Performance Budgets and Core Web Vitals
Performance optimization must be driven by empirical measurements rather than speculative guessing. Projects must adhere to context-specific performance budgets:

| Metric | Target (P75) | Measurement Method | Applicability & Assumptions |
| :--- | :--- | :--- | :--- |
| **LCP (Largest Contentful Paint)** | `< 2.5s` | Chrome UX Report / Lighthouse / Web-Vitals library | Standard broadband, desktop and mobile 4G. Assumes optimized CDN asset delivery. |
| **INP (Interaction to Next Paint)** | `< 200ms` | Field telemetry (Web-Vitals script) | User clicks, keypresses, and form inputs on live dashboard views. |
| **CLS (Cumulative Layout Shift)** | `< 0.1` | Field telemetry & automated Playwright runs | Explicit dimensions reserved for images, avatars, and asynchronous skeleton loaders. |
| **Initial JS Bundle Size** | `< 200KB` (gzip) | `vite-bundle-visualizer` / Rollup output | Shared vendor + app runtime chunk on initial landing. Defer feature modules. |

---

## 7. Domain F: Inertia and Laravel Integration Architecture

### 7.1 The Inertia Request-Response Lifecycle
Inertia acts as the bridge connecting Laravel server-side routing with client-side Vue views:
1. **Initial Visit:** Browser requests a URL. Laravel returns a standard HTML response containing the root layout (`app.blade.php`), Vite script tags, and a `<div id="app" data-page="...">` element containing serialized page props.
2. **Subsequent Navigations:** Clicks on `<Link>` or `router.visit()` trigger an asynchronous `XMLHttpRequest`/`fetch` with the `X-Inertia: true` header.
3. **Server Response:** The Laravel controller detects the Inertia header and returns a JSON payload containing the component name, props, URL, and asset version instead of full HTML.
4. **Client Swap:** `@inertiajs/vue3` intercepts the JSON, resolves the corresponding Vue component, updates browser history via `history.pushState`, and updates the view without a full page reload.

### 7.2 Controller Responsibilities and Eloquent Resource Serialization
Laravel controllers must own data querying, authorization, and serialization. Controllers must never pass unformatted Eloquent models with hidden relations or sensitive columns:

```php
// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->input('search'), function ($query, $search) {
                $query->where('number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Billing/Index', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => $request->only(['search', 'status']),
            'can' => [
                'create_invoice' => $request->user()->can('create', Invoice::class),
            ],
        ]);
    }
}
```

### 7.3 Version Boundaries: Inertia v1 vs Inertia v2 Architecture

To maintain rigorous technical accuracy and prevent version contamination, autonomous engineering agents and engineers must strictly distinguish Inertia v1 baseline APIs from Inertia v2 advanced capabilities. Consuming projects must never import or invoke an API without first verifying adapter versions across both client and server manifests.

#### 7.3.1 Inertia v1 vs Inertia v2 Architectural Capability Matrix

| Architectural Capability | Inertia v1 Baseline (`@inertiajs/vue3` 1.x / `inertia-laravel` 1.x) | Inertia v2 Advanced (`@inertiajs/vue3` 2.x / `inertia-laravel` 2.x) | Package & Adapter Compatibility Invariants |
| :--- | :--- | :--- | :--- |
| **Core Primitives** | `useForm()`, `usePage()`, `router.visit()`, `router.get/post/put/patch/delete/reload()`, `<Link>`, `<Head>`, `Inertia::render()`, `Inertia::share()` | Retained and backward-compatible with v1. | Supported across both v1 and v2 runtimes. |
| **Partial Reloads** | `router.reload({ only: ['prop'] })`, `router.visit(url, { only: ['prop'] })`, or `<Link :only="['prop']">`. | Supported, plus `except: ['prop']` exclusion option to reload all props except designated keys. | Inertia v1 supports `only` exclusively. `except` requires `@inertiajs/vue3` ^2.0.0 and `inertia-laravel` ^2.0.0. |
| **Lazy Props** | `Inertia::lazy(fn () => ...)` on server. Omitted on initial page load; evaluated only when requested by client via `only`. | **DEPRECATED** in Inertia v2 in favor of `Inertia::optional()` (and removed in Inertia v3). | Do not introduce `Inertia::lazy()` in new Inertia v2 codebases. |
| **Optional Props** | **UNSUPPORTED.** Does not exist in `inertia-laravel` 1.x. | `Inertia::optional(fn () => ...)` on server. Official successor to `Inertia::lazy()`. Evaluated on demand via `only`/`except` or `<WhenVisible>`. | Requires `inertia-laravel` ^2.0.0 on server. Calling `Inertia::optional()` on v1 triggers a runtime exception. |
| **Deferred Props** | **UNSUPPORTED.** Heavy props block initial page render or require independent manual AJAX endpoints. | `Inertia::defer(fn () => ...)` on server with `<Deferred data="...">` on client. Evaluated asynchronously in background post-initial-render. | Requires `@inertiajs/vue3` ^2.0.0 AND `inertia-laravel` ^2.0.0. Prohibited in Inertia v1. |
| **Prop Merging & Infinite Scroll** | **UNSUPPORTED.** Requires manual client-side array concatenation (`items.value.push(...)`). | `Inertia::merge(fn () => ...)` and `Inertia::scroll(fn () => ...)` on server; `<InfiniteScroll data="...">` on client. | Requires `@inertiajs/vue3` ^2.0.0 AND `inertia-laravel` ^2.0.0. |
| **Polling** | Manual `setInterval` invoking `router.reload({ only: [...] })` with manual `clearInterval` in `onUnmounted()`. | Native `usePoll(intervalMs, visitOptions?, pollOptions?)` composable and `router.poll()`. | `usePoll` is exported only in `@inertiajs/vue3` ^2.0.0. Prohibited in v1. |
| **Prefetching** | **UNSUPPORTED.** | Native `<Link href="..." prefetch>` and `router.prefetch(url, options)`. | Requires `@inertiajs/vue3` ^2.0.0. Prohibited in v1. |

#### 7.3.2 Technical Differentiation: Lazy Props vs. Optional Props vs. Deferred Props

A common architectural defect is treating lazy, optional, and deferred props as interchangeable APIs. They operate on distinct execution lifecycles and solve fundamentally different performance problems:

1. **Inertia v1 Lazy Props (`Inertia::lazy()`):**
   - **Applicable Runtime:** Inertia v1 (`inertia-laravel` 1.x, `@inertiajs/vue3` 1.x).
   - **Execution Lifecycle:** The prop is completely omitted from the initial page visit payload. The server callback is **never evaluated** until the client issues an explicit partial reload requesting that specific prop via `router.reload({ only: ['propName'] })`.
   - **Deprecation Status:** Deprecated in Inertia v2; replaced by `Inertia::optional()`. Removed in Inertia v3.

2. **Inertia v2 Optional Props (`Inertia::optional()`):**
   - **Applicable Runtime:** Inertia v2 (`inertia-laravel` ^2.0.0, `@inertiajs/vue3` ^2.0.0).
   - **Execution Lifecycle:** The direct successor to `Inertia::lazy()`. The prop is omitted from initial page loads and is evaluated strictly when explicitly requested via partial reloads (`only` or `except` options) or triggered when a client `<WhenVisible data="propName">` wrapper intersects the viewport.
   - **Compatibility Guard:** Server-side `Inertia::optional()` does not exist in `inertia-laravel` 1.x.

3. **Inertia v2 Deferred Props (`Inertia::defer()` & `<Deferred>`):**
   - **Applicable Runtime:** Inertia v2 (`inertia-laravel` ^2.0.0, `@inertiajs/vue3` ^2.0.0).
   - **Execution Lifecycle:** Fundamentally different from lazy and optional props. Deferred props **do not wait for user interactions or manual reload triggers**. The initial page response renders immediately without blocking on deferred data, optimizing First Contentful Paint (FCP). The client adapter automatically issues an asynchronous background request immediately after component mount to fetch the deferred data. The `<Deferred>` component displays a `#fallback` skeleton until the payload resolves.
   - **Compatibility Guard:** Neither `Inertia::defer()` on the server nor `<Deferred>` on the client exists in Inertia v1.

> [!CAUTION]
> **API Conflation Prohibition:**  
> Never treat `Inertia::lazy()`, `Inertia::optional()`, and `Inertia::defer()` as equivalent APIs.  
> - Lazy/Optional props require an **explicit client trigger** (partial reload or viewport intersection).
> - Deferred props execute **automatically in the background** immediately post-initial-mount.
> - Conflating these APIs leads to missed data fetches, broken skeleton states, or fatal runtime exceptions.

#### 7.3.3 Server and Client Package Compatibility Requirements

| API Feature | Server Package Requirement | Client Package Requirement | Cross-Version Policy |
| :--- | :--- | :--- | :--- |
| `Inertia::lazy()` | `inertiajs/inertia-laravel` `^1.0.0` (or `^2.0.0` deprecated) | `@inertiajs/vue3` `^1.0.0` or `^2.0.0` | Standard v1 API. Deprecated in v2; avoid in greenfield v2 code. |
| `Inertia::optional()` | `inertiajs/inertia-laravel` `^2.0.0` | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `Inertia::defer()` / `<Deferred>` | `inertiajs/inertia-laravel` `^2.0.0` | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `Inertia::merge()` / `<InfiniteScroll>` | `inertiajs/inertia-laravel` `^2.0.0` | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `usePoll()` / `router.poll()` | N/A (Standard partial reload backend) | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `<Link prefetch>` / `router.prefetch()` | N/A (Standard Inertia backend) | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `except` Reload Option | `inertiajs/inertia-laravel` `^2.0.0` | `@inertiajs/vue3` `^2.0.0` | **v2 Only.** Prohibited in Inertia v1 codebases. |
| `useForm()`, `usePage()`, `router.visit()` | `inertiajs/inertia-laravel` `^1.0.0` or `^2.0.0` | `@inertiajs/vue3` `^1.0.0` or `^2.0.0` | Universal Core Primitives. |

#### 7.3.4 Canonical Verified Code Examples

##### Pattern 1: Inertia v1 Lazy Evaluation Pattern (Verified v1 Baseline)
```php
// app/Http/Controllers/BillingController.php (Inertia v1 Baseline)
namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\BillingAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Billing/Index', [
            'invoices' => InvoiceResource::collection(Invoice::latest()->paginate(15)),
            // Evaluated ONLY when client explicitly requests via partial reload:
            'analytics' => Inertia::lazy(fn () => BillingAnalyticsService::generate($request->user()->tenant_id)),
        ]);
    }
}
```

```vue
<!-- resources/js/pages/Billing/Index.vue (Inertia v1 Baseline Client) -->
<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface AnalyticsPayload {
    mrr: number;
    churnRate: number;
}

defineProps<{
    invoices: Record<string, unknown>;
    analytics?: AnalyticsPayload;
}>();

const isLoadingAnalytics = ref<boolean>(false);

const loadAnalytics = (): void => {
    isLoadingAnalytics.value = true;
    router.reload({
        only: ['analytics'],
        onFinish: () => {
            isLoadingAnalytics.value = false;
        },
    });
};
</script>

<template>
    <div>
        <button
            v-if="!analytics"
            type="button"
            :disabled="isLoadingAnalytics"
            class="px-4 py-2 bg-indigo-600 text-white rounded"
            @click="loadAnalytics"
        >
            {{ isLoadingAnalytics ? 'Loading...' : 'Load Analytics' }}
        </button>
        <div v-else class="mt-4 p-4 border rounded">
            <p>MRR: {{ analytics.mrr }}</p>
            <p>Churn: {{ analytics.churnRate }}%</p>
        </div>
    </div>
</template>
```

##### Pattern 2: Inertia v2 Optional Props Pattern (Verified v2 Advanced)
```php
// app/Http/Controllers/UserController.php (Inertia v2 ONLY)
namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\UserPublicResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => new UserPublicResource($user),
            // Successor to Inertia::lazy(); evaluated ONLY when requested by partial reload:
            'activityLogs' => Inertia::optional(fn () => ActivityLogResource::collection($user->activityLogs()->latest()->take(20)->get())),
        ]);
    }
}
```

```vue
<!-- resources/js/pages/Users/Show.vue (Inertia v2 Optional Client) -->
<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface UserShowProps {
    user: { id: number; name: string; email: string };
    activityLogs?: Array<{ id: number; description: string; created_at: string }>;
}

defineProps<UserShowProps>();

const fetchLogs = (): void => {
    router.reload({ only: ['activityLogs'] });
};
</script>

<template>
    <div>
        <h1>{{ user.name }}</h1>
        <button v-if="!activityLogs" type="button" @click="fetchLogs">View Activity Logs</button>
        <ul v-else class="mt-4 space-y-2">
            <li v-for="log in activityLogs" :key="log.id">{{ log.description }}</li>
        </ul>
    </div>
</template>
```

##### Pattern 3: Inertia v2 Deferred Props Pattern (Verified v2 Advanced)
```php
// app/Http/Controllers/DashboardController.php (Inertia v2 ONLY)
namespace App\Http\Controllers;

use App\Http\Resources\UserPublicResource;
use App\Services\ComplexMetricsCalculator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'user' => new UserPublicResource($request->user()),
            // Resolves automatically in background post-initial-render:
            'stats' => Inertia::defer(fn () => ComplexMetricsCalculator::calculate($request->user()->tenant_id)),
        ]);
    }
}
```

```vue
<!-- resources/js/pages/Dashboard.vue (Inertia v2 ONLY) -->
<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';

interface DashboardStats {
    activeUsers: number;
    revenue: number;
}

defineProps<{
    user: { id: number; name: string };
    stats?: DashboardStats;
}>();
</script>

<template>
    <div class="space-y-6">
        <h1>Welcome, {{ user.name }}</h1>

        <!-- Deferred component renders fallback until background fetch completes -->
        <Deferred data="stats">
            <template #fallback>
                <div class="animate-pulse h-32 bg-gray-100 rounded-lg p-4" aria-busy="true">
                    <p class="text-gray-500">Loading performance metrics...</p>
                </div>
            </template>
            <div v-if="stats" class="grid grid-cols-2 gap-4">
                <div class="p-4 border rounded">Active Users: {{ stats.activeUsers }}</div>
                <div class="p-4 border rounded">Revenue: ${{ stats.revenue.toLocaleString() }}</div>
            </div>
        </Deferred>
    </div>
</template>
```

##### Pattern 4: Inertia v2 Declarative Polling Pattern (Verified v2 Advanced)
```vue
<!-- resources/js/pages/Notifications/Index.vue (Inertia v2 ONLY) -->
<script setup lang="ts">
import { usePoll } from '@inertiajs/vue3';

interface NotificationProps {
    unreadCount: number;
}

defineProps<NotificationProps>();

// Polls every 5000ms with partial reload; automatically paused when tab is inactive
usePoll(5000, { only: ['unreadCount'] }, { background: 'pause' });
</script>

<template>
    <div>
        <p>Unread Notifications: {{ unreadCount }}</p>
    </div>
</template>
```

##### Pattern 5: Inertia v2 Link Prefetching Pattern (Verified v2 Advanced)
```vue
<!-- resources/js/components/NavMenu.vue (Inertia v2 ONLY) -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav class="flex space-x-4">
        <!-- Prefetches page component and data on link hover -->
        <Link href="/dashboard" prefetch class="text-gray-700 hover:text-indigo-600">Dashboard</Link>
        <Link href="/analytics" prefetch="mount" class="text-gray-700 hover:text-indigo-600">Analytics</Link>
    </nav>
</template>
```

##### Pattern 6: Universal Inertia Primitives Pattern (v1 & v2 Universal)
```vue
<!-- resources/js/components/UserNav.vue (Inertia v1 & v2 Universal) -->
<script setup lang="ts">
import { Link, Head, usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types/inertia';

const page = usePage<PageProps>();

const handleLogout = (): void => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Account Settings" />
    <header class="flex justify-between items-center p-4">
        <span>Logged in as: {{ page.props.auth?.user?.name }}</span>
        <nav class="flex space-x-2">
            <Link href="/profile" class="text-sm font-medium">Profile</Link>
            <button type="button" class="text-sm text-red-600" @click="handleLogout">Logout</button>
        </nav>
    </header>
</template>
```

#### 7.3.5 Strict Version Compatibility Governance Rules
1. **Mandatory Manifest Audit:** Before authoring any Inertia code, agents and engineers must inspect `package.json` (`@inertiajs/vue3`) and `composer.json` (`inertiajs/inertia-laravel`).
2. **Prohibition of Forward API Contamination:** Never author Inertia v2 APIs (`Inertia::optional`, `Inertia::defer`, `<Deferred>`, `<WhenVisible>`, `Inertia::merge`, `Inertia::scroll`, `<InfiniteScroll>`, `usePoll`, `router.poll`, `<Link prefetch>`, `router.prefetch`, reload `except`) in an Inertia v1 project.
3. **Prohibition of Deprecated API Usage in v2:** When developing in an Inertia v2 codebase, do not author `Inertia::lazy()`. Use `Inertia::optional()` for on-demand partial reloads or `Inertia::defer()` for non-blocking post-mount metrics.
4. **Adapter Parity Rule:** Both `@inertiajs/vue3` and `inertiajs/inertia-laravel` must be updated in lockstep. Features requiring server and client coordination (such as deferred props or prop merging) fail if one adapter remains on v1.x.
5. **No Package Upgrades Without Authorization:** Engineering agents must not update `package.json` or `composer.json` dependencies unless explicitly instructed under an approved Project Profile or upgrade mandate.

### 7.4 Handling Shared Data via `HandleInertiaRequests`
Global data required across all pages (current user, permissions, flash messages) must be shared via the `HandleInertiaRequests` middleware. Keep shared payloads minimal to avoid inflating every Inertia request:

```php
// app/Http/Middleware/HandleInertiaRequests.php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'tenant_id' => $request->user()->tenant_id,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
```

### 7.5 State Preservation and Scroll Management
When executing filtering, pagination, or tab changes, preserve component state and window scroll:

```ts
import { router } from '@inertiajs/vue3';

function navigatePage(url: string): void {
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
        replace: true, // Prevents polluting history with intermediate filter states
    });
}
```

---

## 8. Domain G: Forms and Validation Engineering

### 8.1 Inertia `useForm` Architecture
The `useForm` helper is the authoritative mechanism for managing forms in Vue + Inertia applications. It tracks field state, processing state, dirty state, and automatically synchronizes server validation errors returned by Laravel Form Requests.

```vue
<!-- resources/js/pages/Users/Create.vue -->
<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import BaseInput from '@/components/ui/BaseInput.vue';

interface CreateUserForm {
    name: string;
    email: string;
    role: string;
    avatar: File | null;
}

const form = useForm<CreateUserForm>({
    name: '',
    email: '',
    role: 'member',
    avatar: null,
});

const submit = (): void => {
    form.post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create User" />
    <form @submit.prevent="submit" class="max-w-xl space-y-6" novalidate>
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                :aria-invalid="!!form.errors.name"
                :aria-describedby="form.errors.name ? 'name-error' : undefined"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @input="form.clearErrors('name')"
            />
            <p v-if="form.errors.name" id="name-error" class="mt-1 text-xs text-red-600" role="alert">
                {{ form.errors.name }}
            </p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                :aria-invalid="!!form.errors.email"
                :aria-describedby="form.errors.email ? 'email-error' : undefined"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @input="form.clearErrors('email')"
            />
            <p v-if="form.errors.email" id="email-error" class="mt-1 text-xs text-red-600" role="alert">
                {{ form.errors.email }}
            </p>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button
                type="button"
                :disabled="!form.isDirty || form.processing"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                @click="form.reset()"
            >
                Reset
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600"
            >
                <span v-if="form.processing">Saving...</span>
                <span v-else>Create User</span>
            </button>
        </div>
    </form>
</template>
```

### 8.2 Complex Dynamic Forms and Nested Arrays
When editing nested collections (e.g. line items on an invoice), bind form items to indexed keys. Laravel returns nested validation errors using dot notation (e.g., `items.0.amount`), which maps directly to `form.errors['items.0.amount']`:

```vue
<template>
    <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 items-start">
        <div class="flex-1">
            <input v-model="item.description" placeholder="Item description" class="w-full rounded border" />
            <p v-if="form.errors[`items.${index}.description`]" class="text-xs text-red-600" role="alert">
                {{ form.errors[`items.${index}.description`] }}
            </p>
        </div>
        <div class="w-32">
            <input v-model.number="item.amount" type="number" placeholder="Amount" class="w-full rounded border" />
            <p v-if="form.errors[`items.${index}.amount`]" class="text-xs text-red-600" role="alert">
                {{ form.errors[`items.${index}.amount`] }}
            </p>
        </div>
        <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">&times;</button>
    </div>
</template>
```

### 8.3 File Uploads, Progress Tracking, and Cancellation
Inertia automatically converts requests to `multipart/form-data` when a `File` or `Blob` is detected in the `useForm` state:

```ts
const form = useForm({
    title: '',
    document: null as File | null,
});

function uploadFile(): void {
    form.post('/documents', {
        forceFormData: true,
        onProgress: (progress) => {
            if (progress.percentage) {
                uploadPercent.value = progress.percentage;
            }
        },
        onError: () => {
            uploadPercent.value = 0;
        },
    });
}
```

### 8.4 Dirty State Tracking and Accidental Navigation Prevention
To prevent data loss when users attempt to navigate away with unsaved changes, register an Inertia router navigation hook:

```ts
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

export function usePreventUnsavedNavigation(isDirtyGetter: () => boolean): void {
    let removeListener: (() => void) | null = null;

    onMounted(() => {
        removeListener = router.on('before', (event) => {
            if (isDirtyGetter() && !confirm('You have unsaved changes. Are you sure you want to leave?')) {
                event.preventDefault();
            }
        });
    });

    onUnmounted(() => {
        if (removeListener) removeListener();
    });
}
```

---

## 9. Domain H: Security Engineering

### 9.1 Cross-Site Scripting (XSS) Prevention and `v-html` Prohibitions
Vue templates automatically escape text interpolated via `{{ }}` and bound to attributes via `:attr`. However, `v-html` introduces direct XSS vectors:
- **Mandatory Prohibitions:** Never use `v-html` on untrusted user input, URL parameters, or unverified server payloads.
- **Sanitization Invariant:** If rendering CMS rich text is unavoidable, the HTML must be passed through `DOMPurify` configured with an explicit allowlist before binding to `v-html`:

```vue
<script setup lang="ts">
import DOMPurify from 'dompurify';

const props = defineProps<{ rawCmsHtml: string }>();

// Sanitize with strict tag and attribute allowlist
const sanitizedHtml = DOMPurify.sanitize(props.rawCmsHtml, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'ul', 'ol', 'li', 'code'],
    ALLOWED_ATTR: ['href', 'target', 'rel'],
});
</script>

<template>
    <div class="prose max-w-none" v-html="sanitizedHtml" />
</template>
```

### 9.2 Safe URL and Redirect Handling
When accepting external or user-provided URLs for link tags, validate protocols to prevent `javascript:` execution attacks:

```ts
export function isSafeUrl(url: string): boolean {
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch {
        return false;
    }
}
```

All external links must specify `target="_blank"` and `rel="noopener noreferrer"` to prevent window opener hijacking.

### 9.3 CSRF Protection and Session Expiry
Inertia automatically includes Laravel's `X-XSRF-TOKEN` cookie value in request headers. When a user's session expires, Laravel returns an HTTP 419 Status Code. Intercept 419 errors globally in `app.ts` to provide a clear re-authentication prompt instead of unhandled promise rejections:

```ts
import { router } from '@inertiajs/vue3';

router.on('invalid', (event) => {
    if (event.detail.response.status === 419) {
        event.preventDefault();
        alert('Your session has expired. Please refresh the page to log in again.');
        window.location.reload();
    }
});
```

### 9.4 Secret Hygiene in Frontend Bundles
- **Rule:** Never prefix server secrets or private API keys with `VITE_`. Any variable starting with `VITE_` is statically embedded into client JavaScript bundles and readable by anyone inspecting the code.
- **Auditing:** In CI pipelines, run a regex scanner across `public/build/assets/*.js` to ensure private tokens (`AWS_SECRET_ACCESS_KEY`, `DB_PASSWORD`, `STRIPE_SECRET_KEY`) never leak into production builds.

### 9.5 Information Leakage Prevention in Page Props
Controllers must serialize only the exact fields required by the frontend view. Do not pass Eloquent models directly to `Inertia::render()`, as this risks exposing sensitive database columns (`password`, `remember_token`, `two_factor_secret`, `ssn`). Always use API Resources or DTOs:

```php
// INCORRECT: Exposes internal database columns
return Inertia::render('Users/Show', ['user' => User::find($id)]);

// CORRECT: Strictly scopes exposed attributes
return Inertia::render('Users/Show', ['user' => new UserPublicResource(User::findOrFail($id))]);
```

### 9.6 Server-Side Primacy for Authorization
Frontend UI elements (e.g. hiding an "Edit" button or disabling a form) are UX enhancements, **never** security boundaries. Every state mutation and query must be authorized on the server via Laravel Policies:

```php
// app/Policies/InvoicePolicy.php
public function update(User $user, Invoice $invoice): bool
{
    return $user->tenant_id === $invoice->tenant_id && $user->can('invoices.manage');
}
```

---

## 10. Domain I: Multi-Tenancy and Data Isolation

### 10.1 Multi-Tenant Architectural Invariants
In a multi-tenant application, data isolation must be enforced with zero reliance on client state:
- **Server Ownership:** Tenant context (`tenant_id`) is owned entirely by the authenticated server session or verified route domain. The frontend never dictates the active tenant for data access.
- **Untrusted Identifiers:** Any entity ID sent from the frontend (`invoice_id`, `user_id`) must be verified on the server to ensure it belongs to the authenticated tenant.
- **Prohibition on Client Filtering:** Never fetch un-scoped records and filter by `tenant_id` on the client. Filtering must occur strictly in the database query (via Eloquent global scopes or explicit query constraints).

### 10.2 Tenant-Scoped Navigation and Routing
When URLs include tenant slugs (e.g., `/{tenant}/dashboard`), verify route consistency. On tenant transition, perform a clean Inertia navigation that refreshes shared page props:

```ts
import { router } from '@inertiajs/vue3';

export function switchTenant(tenantSlug: string): void {
    // Navigating to new tenant root resets all shared props and flushes client state
    router.visit(`/${tenantSlug}/dashboard`, {
        replace: true,
        preserveState: false, // Ensures previous tenant state is garbage-collected
    });
}
```

### 10.3 Client Storage and Cache Isolation
If any transient data must be persisted in browser storage (e.g. table column visibility, draft notes), the storage key must be strictly partitioned by tenant ID:

```ts
export function getTenantStorageKey(tenantId: number, feature: string): string {
    return `tenant_${tenantId}_${feature}`;
}
```

When a user logs out or switches tenants, all tenant-scoped browser storage keys must be purged immediately.

---

## 11. Domain J: Testing and Quality Assurance

### 11.1 The Enterprise Testing Pyramid
A robust Vue + Inertia application requires a layered testing strategy:

```
           /   Playwright E2E Tests   \        (Critical User Journeys, WCAG scans)
          /----------------------------\
         /  Laravel Inertia HTTP Tests  \       (Controllers, Form Requests, Policies)
        /--------------------------------\
       /   Vue Test Utils Component Tests \     (Props, Emits, Slots, Form Interactions)
      /------------------------------------\
     /       Vitest Unit Tests (Composables) \   (Pure TS functions, schema validators)
    /------------------------------------------\
```

### 11.2 Vitest Unit Testing: Composables
Test composables utilizing an isolated test harness that mounts the composable within an active Vue effect scope:

```ts
// resources/js/composables/__tests__/useCounter.spec.ts
import { describe, it, expect } from 'vitest';
import { ref } from 'vue';
import { withSetup } from '@/test-utils/withSetup';
import { useCounter } from '../useCounter';

describe('useCounter composable', () => {
    it('increments and decrements count reactively', () => {
        const [result, app] = withSetup(() => useCounter(10));

        expect(result.count.value).toBe(10);
        result.increment();
        expect(result.count.value).toBe(11);
        result.decrement();
        expect(result.count.value).toBe(10);

        app.unmount();
    });
});
```

The `withSetup` utility ensures proper Vue lifecycle and scope management:

```ts
// resources/js/test-utils/withSetup.ts
import { createApp, type App } from 'vue';

export function withSetup<T>(composable: () => T): [T, App] {
    let result!: T;
    const app = createApp({
        setup() {
            result = composable();
            return () => {};
        },
    });
    app.mount(document.createElement('div'));
    return [result, app];
}
```

### 11.3 Vue Test Utils: Component Interaction Testing
Component tests must assert observable DOM behavior and emitted events rather than internal component implementation details:

```ts
// resources/js/components/ui/__tests__/BaseButton.spec.ts
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BaseButton from '../BaseButton.vue';

describe('BaseButton component', () => {
    it('renders label and handles click events', async () => {
        const wrapper = mount(BaseButton, {
            props: { label: 'Submit Payment' },
        });

        expect(wrapper.text()).toContain('Submit Payment');
        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toHaveLength(1);
    });

    it('disables button and displays spinner when loading is true', () => {
        const wrapper = mount(BaseButton, {
            props: { label: 'Submit Payment', loading: true },
        });

        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
        expect(wrapper.find('[role="status"]').exists()).toBe(true);
    });
});
```

### 11.4 Laravel Server-Side Inertia Testing (Pest / PHPUnit)
Verify Inertia responses, component resolution, and prop contracts at the server integration boundary:

```php
// tests/Feature/InvoiceControllerTest.php
use App\Models\Invoice;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can view tenant invoices', function () {
    $user = User::factory()->create();
    $invoices = Invoice::factory()->count(3)->create(['tenant_id' => $user->tenant_id]);

    $this->actingAs($user)
        ->get('/billing/invoices')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Billing/Index')
            ->has('invoices.data', 3)
            ->where('invoices.data.0.tenant_id', $user->tenant_id)
            ->has('can.create_invoice')
        );
});
```

### 11.5 Playwright End-to-End and Accessibility Testing
Validate end-to-end browser journeys and verify automated accessibility conformance:

```ts
// tests/e2e/billing.spec.ts
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Billing workflow', () => {
    test('renders billing dashboard without accessibility violations', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'admin@example.com');
        await page.fill('#password', 'secret-password');
        await page.click('button[type="submit"]');

        await page.goto('/billing/invoices');
        await expect(page.locator('h1')).toHaveText('Billing & Invoices');

        // Automated WCAG 2.2 AA scan
        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag22aa'])
            .analyze();

        expect(accessibilityScanResults.violations).toEqual([]);
    });
});
```

---

## 12. Domain K: Accessibility and Inclusive Engineering (WCAG 2.2 AA)

### 12.1 Semantic HTML and Landmark Structure
Templates must use appropriate semantic landmarks (`<main>`, `<nav>`, `<aside>`, `<header>`, `<footer>`, `<section>`). Form inputs must be grouped with `<fieldset>` and `<legend>` where applicable.

### 12.2 Keyboard Navigation and Focus Management
- **Full Keyboard Operability:** Every interactive element must be reachable and operable via Tab, Shift+Tab, Enter, Space, and Esc.
- **Focus Rings:** Never remove focus outlines with `outline: none` without providing a distinct, high-contrast `:focus-visible` replacement.
- **Modal Focus Traps:** Modal dialogs must trap keyboard focus within the modal container while open and restore focus to the triggering element upon closing.

```vue
<!-- Focus restoration and trap composable pattern -->
<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps<{ isOpen: boolean }>();
const emit = defineEmits<{ (e: 'close'): void }>();

const modalRef = ref<HTMLElement | null>(null);
let triggerElement: HTMLElement | null = null;

const onKeyDown = (e: KeyboardEvent): void => {
    if (e.key === 'Escape' && props.isOpen) {
        emit('close');
    }
};

onMounted(() => {
    triggerElement = document.activeElement as HTMLElement;
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
    triggerElement?.focus(); // Restore focus
});
</script>

<template>
    <div
        v-if="isOpen"
        ref="modalRef"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    >
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
            <h2 id="modal-title" class="text-lg font-bold">Confirm Action</h2>
            <slot />
        </div>
    </div>
</template>
```

### 12.3 Screen Reader Announcements and Form Feedback
- **Live Regions:** Dynamic status changes and flash notifications must be rendered inside an `aria-live="polite"` region.
- **Accessible Form Error Association:** Every field with a validation error must bind `aria-invalid="true"` and `aria-describedby="{fieldId}-error"`.

### 12.4 Contrast and Target Size Standards
- **Color Contrast:** Text must achieve a minimum contrast ratio of 4.5:1 against its background (3:1 for large text $\ge$ 18pt or bold $\ge$ 14pt). UI components and graphical objects must achieve 3:1.
- **Target Size (WCAG 2.2 SC 2.5.8):** All interactive targets must provide a minimum bounding box of 24x24 CSS pixels, with 44x44 CSS pixels recommended for primary touch targets.

---

## 13. Domain L: Design Systems and UI Engineering

### 13.1 Token-Based Styling and Tailwind Integration
Design tokens (colors, spacing, typography, radii, elevation) must be managed centrally in `tailwind.config.js` or via CSS custom properties. Component styling must consume semantic design tokens rather than arbitrary values:

```css
/* resources/css/app.css */
:root {
  --color-primary: 79 70 229;
  --color-surface: 255 255 255;
  --color-text-main: 17 24 39;
  --radius-interactive: 0.375rem;
}

.dark {
  --color-surface: 17 24 39;
  --color-text-main: 243 244 246;
}
```

### 13.2 Component Variants and API Consistency
Shared design system components must expose consistent, type-safe variant props:

```vue
<!-- resources/js/components/ui/BaseButton.vue -->
<script setup lang="ts">
export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost';
export type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps {
    variant?: ButtonVariant;
    size?: ButtonSize;
    disabled?: boolean;
    loading?: boolean;
}

withDefaults(defineProps<ButtonProps>(), {
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
});
</script>

<template>
    <button
        type="button"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-medium rounded-md transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
            variant === 'primary' && 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
            variant === 'secondary' && 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus-visible:outline-indigo-600',
            variant === 'danger' && 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
            variant === 'ghost' && 'text-gray-700 hover:bg-gray-100 focus-visible:outline-gray-600',
            size === 'sm' && 'px-2.5 py-1.5 text-xs',
            size === 'md' && 'px-3.5 py-2 text-sm',
            size === 'lg' && 'px-4 py-2.5 text-base',
        ]"
    >
        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" role="status">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>
        <slot />
    </button>
</template>
```

---

## 14. Domain M: Reliability and Production Readiness

### 14.1 Production Diagnostics and PII-Safe Error Reporting
Configure client-side telemetry (e.g. Sentry or Bugsnag) in `app.ts`. Sanitize all error payloads to strip passwords, credit card numbers, and authorization headers before transmission:

```ts
app.config.errorHandler = (err, instance, info) => {
    const errorPayload = {
        message: err instanceof Error ? err.message : String(err),
        stack: err instanceof Error ? err.stack : undefined,
        info,
        url: window.location.pathname, // Omit query parameters that may contain PII
    };
    // Dispatch to telemetry service
};
```

### 14.2 Asset Versioning and Deployment Synchronization
When deploying new frontend assets, users with open browser tabs must receive updated scripts without breaking. Inertia provides automated asset versioning via the `version` method in `HandleInertiaRequests`:

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function version(Request $request): ?string
{
    return parent::version($request); // Automatically hashes mix-manifest.json or manifest.json
}
```

When Laravel detects a mismatch between the client's `X-Inertia-Version` and the server's asset hash, it responds with an HTTP 409 Conflict, causing Inertia to perform a full window reload and download the fresh bundles automatically.

### 14.3 Vite Build Optimization
Optimize production bundles in `vite.config.ts`:

```ts
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        sourcemap: false, // Prevent exposing source maps in production
        rollupOptions: {
            output: {
                manualChunks: {
                    vue_vendor: ['vue', '@inertiajs/vue3'],
                },
            },
        },
    },
});
```

---

## 15. Domain N: Code Quality, Maintainability, and Engineering Governance

### 15.1 Universal Core Code-File Governance (300-Line Rule)
**Strict Core Invariant:** No code file—including `.vue`, `.ts`, `.tsx`, `.js`, `.jsx`, and `.css`—may exceed **300 physical lines**.
- When a component approaches 300 lines, decompose it into smaller child components, extract business logic into composables, or move pure formatting functions into domain utilities.
- Any exception requires specific technical justification, documented explanation of why decomposition is unsuitable, explicit prior human user approval, and a recorded exception scope and affected file. No agent or architect may grant itself an exception. Approval for one file does not establish a general exception for other files.

### 15.2 Naming and File Conventions
- **Single File Components:** Use PascalCase for all component filenames (e.g., `InvoiceTable.vue`, `UserProfileCard.vue`).
- **Composables:** Use camelCase prefixed with `use` (e.g., `useDebounceSearch.ts`).
- **Utilities and Helpers:** Use camelCase (e.g., `formatCurrency.ts`).
- **Custom Events:** Use kebab-case when emitting events from components (`update:model-value`, `subscription-updated`).

### 15.3 Prohibited Engineering Practices (Anti-Patterns)
1. **No Fake or Mock Implementations in Production:** Every production code file must be fully implemented. Stubs, dummy comments (`// TODO: implement later`), and fake fallbacks are strictly prohibited.
2. **No Silent Error Suppression:** Never leave catch blocks empty. Log, report, or recover gracefully from errors.
3. **No Direct DOM Mutation:** Never manipulate the DOM directly using `document.getElementById` or `document.querySelector` inside Vue components; use template refs (`ref<HTMLElement>()`).
4. **No Direct Server State Mutation:** Never mutate props directly within child components. Always emit events or utilize `v-model`.

---

## 16. Decision Matrices and Forensic Failure Prevention

### 16.1 Architectural Choice Decision Matrix

| Requirement / Scenario | Recommended Architectural Choice | Rejected Alternative | Technical Rationale |
| :--- | :--- | :--- | :--- |
| **Server Data Fetching** | Inertia Page Props + Controller Resource | Client Axios / Fetch calls | Inertia provides unified session, CSRF, and routing without independent API churn. |
| **Heavy Metrics Loading (Inertia v2)** | Inertia v2 `Inertia::defer()` with `<Deferred>` | Polling independent custom API | Resolves asynchronously in background without blocking initial page render; client `<Deferred>` renders `#fallback` skeleton. |
| **Heavy Metrics Loading (Inertia v1)** | Inertia v1 `Inertia::lazy()` with on-demand partial reload | Blocking initial page load | Inertia v1 has NO native deferred props; `Inertia::lazy()` evaluates on explicit client demand (`router.reload({ only: ['metrics'] })`), keeping initial response lean. |
| **Component Two-Way State** | Controlled Component (`defineModel` / `v-model`) | Internal `ref` initialized from prop | Prevents split-brain state where parent and child become desynchronized. |
| **Cross-Component Shared State** | Vue Provide/Inject with `InjectionKey<T>` | Global Pinia Store | Scope is restricted to the component subtree; avoids polluting global state. |
| **Rich Text Rendering** | `DOMPurify.sanitize()` + `v-html` | Raw unescaped `v-html` | Raw `v-html` introduces severe, exploitable XSS vulnerabilities. |

### 16.2 Forensic Failure Scenarios & Prevention Matrix

| Failure Mode | Root Cause | Observable Symptom | Authoritative Prevention Standard |
| :--- | :--- | :--- | :--- |
| **Reactivity Loss on Destructuring** | Destructuring a `reactive()` object or `props` directly in `<script setup>`. | UI fails to re-render when underlying state or prop changes. | Use `toRefs()` on reactive objects, or access properties directly via `props.propName`. |
| **Stale Async Race Condition** | Fast successive search inputs trigger multiple fetch requests resolving out-of-order. | Search results display results for previous query, not current input. | Cancel previous requests using `AbortController` inside `watch(..., { onCleanup })`. |
| **Memory Leak in SPA Navigation** | Registering `window.addEventListener` or intervals without cleanup. | Memory usage increases continuously across page navigations; sluggish UI. | Always unregister listeners and clear timers in `onUnmounted()` or `onScopeDispose()`. |
| **Session Expiration (419 Error)** | Inactive user attempts Inertia form submission with expired CSRF token. | Form fails silently or logs unhandled 419 error; user data lost. | Intercept 419 globally in `router.on('invalid')` and prompt re-authentication. |
| **Cross-Tenant Data Exposure** | Controller queries entity by ID without scoping by authenticated user's `tenant_id`. | User in Tenant A views or edits records belonging to Tenant B (IDOR). | Enforce Eloquent global scopes and verify tenant ownership in Laravel Policies. |

---

## 17. Pull Request Review Checklist and Verification Runbook

### 17.1 Principal Engineer Gating Checklist
Prior to approving any pull request containing Vue + Inertia code, verify:
- [ ] **Baseline Compliance:** Code strictly adheres to Vue 3 Composition API (`<script setup lang="ts">`) and TypeScript 5+ strict mode.
- [ ] **Inertia Version Fidelity:** In Inertia v1 codebases, no Inertia v2 APIs (`Inertia::optional`, `Inertia::defer`, `<Deferred>`, `<WhenVisible>`, `Inertia::merge`, `Inertia::scroll`, `<InfiniteScroll>`, `usePoll`, `router.poll`, `<Link prefetch>`, `router.prefetch`, `except` reloads) are used without verified package upgrades. In Inertia v2 codebases, deprecated `Inertia::lazy()` is avoided in favor of `Inertia::optional()` or `Inertia::defer()`. Lazy, optional, and deferred props are never conflated.
- [ ] **300-Line Governance:** No `.vue`, `.ts`, or `.css` file exceeds 300 physical lines.
- [ ] **State Hygiene:** Inertia props are never duplicated into local reactive state without explicit mutable draft justification.
- [ ] **Reactivity Preservation:** No props or reactive objects are destructured without `toRefs()`.
- [ ] **Form Handling:** Forms use `useForm()` with error display, dirty state tracking, and disabled submit buttons during processing.
- [ ] **Accessibility:** Semantic HTML elements used; all form inputs have associated `<label>` elements; interactive targets meet minimum size targets (WCAG 2.2 AA).
- [ ] **Security:** No raw `v-html` on un-sanitized content; no secrets prefixed with `VITE_`; authorization enforced on Laravel controllers/policies.
- [ ] **Multi-Tenancy:** All queries and mutations strictly scoped to the authenticated tenant on the server.
- [ ] **Zero Compilation Errors:** `vue-tsc --noEmit` and ESLint pass with zero errors and zero warnings.

### 17.2 Verification Runbook
Execute the following verification sequence in the project root:

```bash
# 1. Type-check Vue components and TypeScript files
npm run type-check # Runs: vue-tsc --noEmit

# 2. Execute static analysis and linting
npm run lint       # Runs: eslint . --max-warnings=0

# 3. Execute component and composable unit tests
npm run test:unit  # Runs: vitest run

# 4. Verify production bundle build and chunk distribution
npm run build      # Runs: vite build

# 5. Execute browser E2E and accessibility audits
npx playwright test
```

---

## 18. References and Authoritative Standards

1. **Vue.js Official Documentation:**
   - Composition API Guide: `https://vuejs.org/guide/extras/composition-api-faq.html`
   - TypeScript with Composition API: `https://vuejs.org/guide/typescript/composition-api.html`
   - Vue Core API Reference: `https://vuejs.org/api/`
2. **Inertia.js Documentation:**
   - Inertia.js Official Guide: `https://inertiajs.com/`
   - Forms & Validation: `https://inertiajs.com/forms`
   - Partial Reloads: `https://inertiajs.com/partial-reloads`
   - Deferred Props (v2): `https://inertiajs.com/deferred-props`
   - Merging Props & Infinite Scroll (v2): `https://inertiajs.com/merging-props`
   - Polling (v2): `https://inertiajs.com/polling`
   - Prefetching (v2): `https://inertiajs.com/prefetching`
   - Upgrade Guide: `https://inertiajs.com/upgrade`
   - Inertia Documentation Repository: `https://github.com/inertiajs/inertia`
3. **Laravel 11.x Documentation:**
   - Laravel Inertia Integration: `https://laravel.com/docs/11.x/starter-kits#breeze-and-inertia`
   - Authorization & Policies: `https://laravel.com/docs/11.x/authorization`
   - Validation & Form Requests: `https://laravel.com/docs/11.x/validation`
4. **Vite & Vitest Documentation:**
   - Vite Build & Plugin Architecture: `https://vite.dev/guide/`
   - Vitest Testing Framework: `https://vitest.dev/guide/`
5. **Accessibility & Security Standards:**
   - W3C Web Content Accessibility Guidelines (WCAG) 2.2: `https://www.w3.org/WAI/standards-guidelines/wcag/`
   - OWASP Top 10 Web Application Security Risks: `https://owasp.org/www-project-top-ten/`

# Shared Engineering Skill: React and TypeScript Enterprise Architecture Standards

**Skill ID:** SKILL-REACT-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  
**Approved Baseline:** React 18+, TypeScript 5+, Node.js 20+, npm, Vitest, React Testing Library, Playwright  

---

## 1. Skill Identity, Scope, and Enterprise Baseline

The **React and TypeScript Enterprise Architecture Standards** skill establishes the authoritative engineering standard for architecting, engineering, reviewing, securing, testing, and operating decoupled client-side applications across Universal Core.

### 1.1 Mission and Purpose
This skill equips Principal React Engineers and autonomous engineering agents to deliver decoupled Single Page Applications (SPAs) and modular frontend clients that achieve:
- **Flawless Type Soundness:** Complete type safety powered by TypeScript 5+ strict mode with zero `any` and strict runtime validation at untrusted boundaries.
- **Concurrent-Safe Architecture:** Full utilization of React 18 concurrent primitives (`useTransition`, `useDeferredValue`, `Suspense`, `useId`) with deterministic render-phase behavior.
- **Resilient State & Network Layers:** Clean segregation of server cache from client state, automatic race condition mitigation via `AbortController`, and optimistic UI updates with automatic rollback.
- **Defense-in-Depth Security:** Multi-layered defense against Cross-Site Scripting (XSS), token leakage, and unauthorized client state manipulation, enforcing the fundamental invariant that client controls are never security boundaries.
- **Accessible & Inclusive UX:** Full compliance with WCAG 2.2 Level AA accessibility standards, verified through automated AST/DOM scanners and manual keyboard/screen-reader workflows.
- **High-Velocity Operability:** Measurable performance budgets (Core Web Vitals), widget-level error boundaries preventing cascade failures, and deterministic regression testing across unit, integration, and E2E suites.

### 1.2 Technology Baseline, Toolchains, and Dependency Classifications
Engineering artifacts, canonical code examples, and standard toolchains governed by this skill adhere to the approved Universal Core baseline while accommodating project-specific profiles.

#### 1.2.1 Universal Core Baseline and Version Boundaries
The approved Core baseline establishes the standard runtime and language requirements for governed React client code:

| Technology | Approved Core Baseline | Operational Boundary & Version-Specific Rules | Classification |
| :--- | :--- | :--- | :--- |
| **React Runtime** | `React 18.2+` (or `18.3+`) | **Baseline Primitives:** `Suspense`, `useTransition`, `useDeferredValue`, `useId`, `useSyncExternalStore`.<br>**Version Separation:** React 19 additions (e.g., `useActionState`, `useFormStatus`, `useOptimistic`, the `use` API, React Server Components) must **not** be presented as universally available in React 18 baseline applications. When React 19 capabilities are introduced in progressive environments, they must be gated behind feature detection or version-isolated modules. | Mandatory Core Invariant |
| **TypeScript** | `TypeScript 5.x` | `"strict": true`, `"noUncheckedIndexedAccess": true`, `"exactOptionalPropertyTypes": true`. Use `satisfies`, `const` type parameters, discriminated unions, and branded types. | Mandatory Core Invariant |
| **Node.js** | `Node.js 20 LTS` | Active LTS runtime for local development, build tooling (Vite), and CI pipeline execution across Universal Core. | Standard Core Runtime Baseline |
| **Package Manager**| `npm 10+` | Standard reference package manager with deterministic dependency tree locking (`package-lock.json`) and CI execution via `npm ci`. Consuming projects may utilize approved alternatives (e.g., `pnpm`, `yarn`) where designated in their Project Profile. | Reference Toolchain Standard |
| **Static Analysis**| `ESLint 9+` & `Prettier 3+` | Flat configuration (`eslint.config.js`) enforcing `@typescript-eslint/recommended-type-checked`, `eslint-plugin-react-hooks`, and `eslint-plugin-jsx-a11y`. Consuming projects with legacy configs (ESLint 8 `.eslintrc`) remain valid during transition without forced breaking upgrades. | Reference Toolchain Standard |
| **Unit / Component**| `Vitest` + `Testing Library` | User-centric testing with `@testing-library/react` and `@testing-library/user-event`. Network interception strictly via Mock Service Worker (`msw`). Consuming projects utilizing `Jest` remain supported under their respective project profiles. | Reference Toolchain Standard |
| **Browser E2E** | `Playwright` | Multi-browser headless automation (Chromium, Firefox, WebKit) with automated accessibility scans (`@axe-core/playwright`). Consuming projects with existing `Cypress` suites remain supported under their project profile. | Reference Toolchain Standard |

#### 1.2.2 Dependency Classification: Mandatory Dependencies vs. Recommended Libraries
To prevent accidental over-specification, consuming projects must distinguish mandatory dependencies from recommended libraries:

- **Mandatory Architectural Dependencies:**
  - `react` (`^18.2.0` or `^18.3.0`) and `react-dom` (`^18.2.0` or `^18.3.0`): Core UI primitives and rendering tree.
  - `typescript` (`^5.0.0`): Static typing and compiler checks.
  - Runtime Schema Validation (e.g., `zod` `^3.22.0`): Enforces boundary safety for all external and untrusted data payloads (API responses, form inputs, route parameters).
- **Recommended Architectural Libraries (Standard Reference Stack):**
  - **Server State Management:** TanStack Query (`@tanstack/react-query` v5). Strongly recommended over custom cache mechanisms; projects may substitute equivalent vetted libraries (e.g., SWR) if approved in their Project Profile.
  - **Form State Management:** React Hook Form (`react-hook-form` v7) with `@hookform/resolvers/zod`. Recommended for complex forms; native controlled inputs remain appropriate for simple 1-2 field interactions.
  - **Large List Virtualization:** TanStack Virtual (`@tanstack/react-virtual` v3). Recommended when rendering large collections exceeding DOM performance targets.
  - **Iconography & Design Tokens:** `lucide-react` and Tailwind CSS / CSS Modules. Recommended for accessible, lightweight design system implementation.
  - **Accessibility Auditing:** `vitest-axe` and `@axe-core/playwright`. Recommended for automated WCAG 2.2 AA verification.

#### 1.2.3 Project Scope and Tooling Baseline Governance
Tooling versions (such as Node.js 20 LTS, npm 10+, ESLint 9+, Prettier 3+, Vitest, and Playwright) represent the approved Universal Core reference standard for new applications, shared libraries, and canonical CI pipelines. These tool versions are **not** universally mandatory for every consuming project across an enterprise portfolio unless explicitly mandated by an approved Project Technology Profile or enterprise governance directive. Consuming applications with existing toolchains (e.g., Node.js 18 maintenance LTS, Yarn/pnpm, ESLint 8 legacy configs, or Jest) maintain compliance by adhering to the core architectural principles, TypeScript strictness, security controls, and verification gates without incurring unnecessary tooling churn.

### 1.3 Architectural Scope and Technology Separation
- **Decoupled Client SPAs:** React is designated for decoupled client applications consuming backend REST, JSON:API, or GraphQL services over secure HTTP/HTTPS boundaries.
- **Separation from Monolithic Server Routing:** React workflows must **never** be conflated with monolithic server-driven routing via Inertia.js (which is governed separately by `SKILL-VUE-001`). React applications maintain their own client routing, caching layers, and decoupled deployment pipelines.
- **Universal Core Governance (300-Line Rule):** No code file (including `.ts`, `.tsx`, `.js`, `.jsx`, `.css`) may exceed **300 physical lines**. If a component, hook, or utility approaches this limit, it must be decomposed into cohesive sub-components, custom hooks, or domain utilities. Any exception requires explicit, documented architectural justification.

### 1.4 Ten Principles of Enterprise React Engineering
1. **Correctness and Security Before Convenience:** Never bypass type safety, input sanitization, or authorization checks for rapid prototyping.
2. **Explicit Ownership of State and Side Effects:** Every piece of state must have exactly one authoritative owner. Never duplicate server state in local state.
3. **Server-Side Enforcement of Security Boundaries:** Client route guards, disabled buttons, and hidden inputs are UX conveniences; backend APIs must independently authenticate and authorize every mutation.
4. **Strict Boundaries Between Trusted and Untrusted Data:** All external inputs (API responses, URL parameters, local storage, postMessage) are untrusted until parsed by runtime schemas (e.g., Zod).
5. **Measurable Performance Over Speculative Optimization:** Profile before optimizing. Base memoization and code-splitting on measured render costs and bundle budgets, not guesswork.
6. **Testable Architecture and Observable Behavior:** Write tests that simulate real user interactions and assert observable DOM outputs, not internal component implementation details.
7. **Reusable Abstractions Only When Justified:** Adhere to the Rule of Three. Do not create speculative wrappers around standard React hooks or primitives.
8. **Minimal Dependency Footprint:** Scrutinize every third-party package for bundle weight, maintenance trajectory, and supply-chain vulnerabilities.
9. **No Silent Suppression of Errors:** Catch blocks must log, report, or recover gracefully; empty catch blocks and unhandled promise rejections are strictly prohibited.
10. **Zero Claims of Completion Without Verification:** A feature is not complete until verified by `tsc --noEmit`, ESLint, unit/component tests, and accessibility scans.

---

## 2. Domain A: React Architecture and Application Design

### 2.1 Domain-Driven and Feature-Based Architecture
Enterprise React codebases must be organized by domain features rather than technical layers (e.g., avoiding global `components/`, `hooks/`, `utils/` buckets). Feature-based packaging ensures high cohesion, loose coupling, and clear team boundaries.

```
src/
├── app/                        # Application shell & orchestration
│   ├── providers/              # Combined Context / QueryClient providers
│   ├── router/                 # Scalable route definitions & layouts
│   └── index.tsx               # Root component & error boundary
├── assets/                     # Global static assets (icons, fonts, images)
├── components/                 # Shared UI primitives (domain-agnostic design system)
│   ├── Button/
│   ├── Modal/
│   └── Table/
├── features/                   # Domain features (vertical slices)
│   ├── billing/
│   │   ├── api/                # Feature-specific API clients & queries
│   │   ├── components/         # Feature-specific UI components
│   │   ├── hooks/              # Feature-specific business hooks
│   │   ├── types/              # Feature domain models & DTOs
│   │   └── index.ts            # Public feature API (barrel file)
│   └── identity/
├── hooks/                      # Shared, domain-agnostic utility hooks
├── lib/                        # Infrastructure clients (Axios, TanStack Query, Analytics)
├── styles/                     # Global CSS, tokens, and Tailwind configuration
└── types/                      # Global cross-cutting TypeScript types
```

### 2.2 Layered Responsibility Separation
Every feature must maintain strict boundaries between four distinct layers:
1. **Presentation Layer (UI Components):** Pure, declarative components responsible for rendering JSX, binding accessibility attributes, and capturing user events. Free of direct network calls.
2. **Application Logic Layer (Custom Hooks):** Encapsulates state transitions, coordinates multiple hooks, manages side effects, and translates UI events into domain commands.
3. **Domain Logic Layer (Pure Models & Services):** Pure TypeScript functions containing business calculations, invariant validation, and data formatting. Completely decoupled from React lifecycles.
4. **Infrastructure Layer (API Clients & Storage):** HTTP clients, WebSocket connections, token storage, and third-party SDK adapters.

```
[ User Interaction ] ──> [ UI Component (Presentation) ]
                               │
                               ▼
                         [ Custom Hook (Application Logic) ]
                           │              │
                           ▼              ▼
     [ Pure Functions (Domain) ]      [ API Client / Query Cache (Infrastructure) ]
```

### 2.3 Composition Over Premature Abstraction
Avoid deep component inheritance hierarchies or wrapper abstraction layers around simple standard elements. Leverage React component composition:
- Use **Slots** (`children` or named prop slots like `header={<Header />}`, `footer={<Footer />}`) to decouple container layout from content.
- Distinguish **Reusable UI Primitives** (design system components with no domain knowledge) from **Domain Components** (bind domain models, handle feature events).

### 2.4 Dependency Boundaries and Circular Dependency Prevention
- **Explicit Barrel Hygiene:** Barrel files (`index.ts`) must only export public APIs of a feature. Internal sub-modules within a feature must import via relative paths, not through the feature's own barrel file.
- **Cross-Feature Imports:** Feature A must never import from Feature B's private internals; it may only consume Feature B's public barrel file. Circular imports (`A -> B -> A`) are caught in CI using `eslint-plugin-import` rule `import/no-cycle`.

### 2.5 Architecture Decision Records (ADRs)
When introducing major architectural decisions (e.g., selecting an external state store, introducing a data virtualization engine, modifying the auth flow), author an ADR in `docs/adr/` using the following schema:
- **Title & Status:** ADR-00X: Decision Title (Proposed / Accepted / Superseded).
- **Context:** Business driver, technical constraints, and alternatives evaluated.
- **Decision:** The chosen architecture with rationale.
- **Consequences:** Trade-offs, migration burden, bundle impact, and security implications.

---

## 3. Domain B: Advanced Component Engineering

### 3.1 Component API Design: Controlled vs Uncontrolled
Design component APIs to be unambiguous regarding state ownership:
- **Controlled Components:** Receive `value` and emit `onChange`. The parent is the sole source of truth.
- **Uncontrolled Components:** Maintain internal state via `defaultValue` and expose imperatively via `ref` when needed.
- **Hybrid Anti-Pattern:** Never duplicate props into state (`useState(props.value)`) without explicit synchronization patterns, as this introduces split-brain state bugs.

### 3.2 Compound Components Pattern
For complex UI widgets with coordinated state (e.g., Tabs, Accordions, Dropdowns), implement the Compound Component pattern utilizing React Context to isolate communication:

```tsx
import React, { createContext, useContext, useState, useId, useMemo } from 'react';

interface TabsContextValue {
  activeTab: string;
  setActiveTab: (id: string) => void;
  baseId: string;
}

const TabsContext = createContext<TabsContextValue | null>(null);

function useTabsContext(): TabsContextValue {
  const context = useContext(TabsContext);
  if (!context) {
    throw new Error('Tabs compound components must be rendered within <Tabs>');
  }
  return context;
}

export interface TabsProps {
  defaultTab: string;
  children: React.ReactNode;
}

export function Tabs({ defaultTab, children }: TabsProps): React.JSX.Element {
  const [activeTab, setActiveTab] = useState<string>(defaultTab);
  const baseId = useId();

  const value = useMemo(
    () => ({ activeTab, setActiveTab, baseId }),
    [activeTab, baseId]
  );

  return (
    <TabsContext.Provider value={value}>
      <div className="tabs-container">{children}</div>
    </TabsContext.Provider>
  );
}

export interface TabTriggerProps {
  id: string;
  children: React.ReactNode;
}

export function TabTrigger({ id, children }: TabTriggerProps): React.JSX.Element {
  const { activeTab, setActiveTab, baseId } = useTabsContext();
  const isSelected = activeTab === id;

  return (
    <button
      type="button"
      role="tab"
      id={`${baseId}-tab-${id}`}
      aria-selected={isSelected}
      aria-controls={`${baseId}-panel-${id}`}
      tabIndex={isSelected ? 0 : -1}
      onClick={() => setActiveTab(id)}
      className={`px-4 py-2 font-medium text-sm transition-colors ${
        isSelected ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700'
      }`}
    >
      {children}
    </button>
  );
}

export interface TabPanelProps {
  id: string;
  children: React.ReactNode;
}

export function TabPanel({ id, children }: TabPanelProps): React.JSX.Element | null {
  const { activeTab, baseId } = useTabsContext();
  if (activeTab !== id) return null;

  return (
    <div
      role="tabpanel"
      id={`${baseId}-panel-${id}`}
      aria-labelledby={`${baseId}-tab-${id}`}
      tabIndex={0}
      className="p-4 focus:outline-none"
    >
      {children}
    </div>
  );
}
```

### 3.3 Stable Keys and Reconciliation Determinism
React's Fiber reconciler uses the `key` prop to match virtual elements across renders:
- **Strict Rule:** Never use array index (`key={index}`) for dynamic, reorderable, filterable, or mutable collections. Index keys cause silent UI corruption, incorrect form input state mapping, and broken CSS animations.
- **Mandatory Practice:** Always use globally unique, persistent entity identifiers (`key={item.id}`). If an item lacks an ID, construct a deterministic composite key based on stable entity fields (`key={`${item.serial}-${item.timestamp}`}`).

### 3.4 Error Boundaries and Failure Isolation
A single runtime error in an unhandled component will unmount the entire React application tree. Enterprise applications must wrap distinct layout sections in dedicated, resilient Error Boundaries:

```tsx
import React, { Component, ErrorInfo, ReactNode } from 'react';

interface ErrorBoundaryProps {
  fallbackTitle?: string;
  onReset?: () => void;
  children: ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  public override state: ErrorBoundaryState = {
    hasError: false,
    error: null,
  };

  public static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error };
  }

  public override componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    // Forward to centralized telemetry scrubbed of PII
    console.error('Unhandled component error caught by boundary:', error, errorInfo);
  }

  private handleReset = (): void => {
    this.setState({ hasError: false, error: null });
    this.props.onReset?.();
  };

  public override render(): ReactNode {
    if (this.state.hasError) {
      return (
        <div role="alert" className="p-6 border border-red-200 bg-red-50 rounded-xl">
          <h3 className="text-base font-semibold text-red-900">
            {this.props.fallbackTitle ?? 'Component Failure'}
          </h3>
          <p className="mt-2 text-sm text-red-700">
            An unexpected error occurred while rendering this widget.
          </p>
          <button
            type="button"
            onClick={this.handleReset}
            className="mt-4 px-3 py-1.5 bg-red-600 text-white rounded text-xs font-semibold hover:bg-red-700"
          >
            Retry Section
          </button>
        </div>
      );
    }
    return this.props.children;
  }
}
```

---

## 4. Domain C: TypeScript Engineering

### 4.1 Strict TypeScript Configuration
Every enterprise project must enforce strict compiler settings in `tsconfig.json`:
```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["DOM", "DOM.Iterable", "ES2022"],
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "exactOptionalPropertyTypes": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "useUnknownInCatchVariables": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx"
  }
}
```

### 4.2 Generic Components with Strict Constraints
Generic components allow type-safe rendering of arbitrary domain items while enforcing property contracts:

```tsx
import React from 'react';

export interface Identifiable {
  id: string;
}

export interface SelectListProps<T extends Identifiable> {
  items: readonly T[];
  selectedId: string | null;
  onSelect: (item: T) => void;
  getLabel: (item: T) => string;
}

export function SelectList<T extends Identifiable>({
  items,
  selectedId,
  onSelect,
  getLabel,
}: SelectListProps<T>): React.JSX.Element {
  return (
    <ul role="listbox" className="divide-y divide-gray-200 border rounded-lg">
      {items.map((item) => {
        const isSelected = item.id === selectedId;
        return (
          <li
            key={item.id}
            role="option"
            aria-selected={isSelected}
            onClick={() => onSelect(item)}
            className={`p-3 cursor-pointer text-sm ${isSelected ? 'bg-indigo-50 font-semibold' : 'hover:bg-gray-50'}`}
          >
            {getLabel(item)}
          </li>
        );
      })}
    </ul>
  );
}
```

### 4.3 Discriminated Unions and Exhaustiveness Verification
Model asynchronous state and heterogeneous UI variants using discriminated unions. Enforce compile-time exhaustiveness checking using the `assertNever` helper:

```typescript
export type AsyncState<TData, TError = Error> =
  | { readonly status: 'idle'; readonly data: null; readonly error: null }
  | { readonly status: 'loading'; readonly data: null; readonly error: null }
  | { readonly status: 'success'; readonly data: TData; readonly error: null }
  | { readonly status: 'error'; readonly data: null; readonly error: TError };

export function assertNever(value: never, message = 'Unexpected unreachable union value'): never {
  throw new Error(`${message}: ${JSON.stringify(value)}`);
}

// In component render:
export function renderAsyncView<T>(state: AsyncState<T>): React.JSX.Element {
  switch (state.status) {
    case 'idle':
      return <div>Ready to load.</div>;
    case 'loading':
      return <div role="status">Loading data...</div>;
    case 'success':
      return <div>Loaded successfully.</div>;
    case 'error':
      return <div role="alert">Error: {state.error.message}</div>;
    default:
      return assertNever(state); // Compile error if new status is added without handling
  }
}
```

### 4.4 Runtime Type Narrowing at Untrusted Boundaries
Never cast untrusted network responses using unsafe type assertions (`as UserDto`). Always parse external data with runtime validation schemas (e.g., Zod):

```typescript
import { z } from 'zod';

export const UserDtoSchema = z.object({
  id: z.string().uuid(),
  name: z.string().min(1),
  email: z.string().email(),
  role: z.enum(['admin', 'member', 'guest']),
});

export type UserDto = z.infer<typeof UserDtoSchema>;

export async function parseIncomingUser(rawJson: unknown): Promise<UserDto> {
  const result = await UserDtoSchema.safeParseAsync(rawJson);
  if (!result.success) {
    throw new Error(`Data contract validation failed: ${result.error.message}`);
  }
  return result.data;
}
```

---

## 5. Domain D: Hooks and State Management

### 5.1 Rules of Hooks and Dependency Hygiene
- Never call hooks inside loops, conditions, or nested functions.
- Never suppress the `react-hooks/exhaustive-deps` ESLint rule. If a dependency triggers unwanted re-renders, the root cause is almost always an unstable function reference (solve with `useCallback`) or improper state colocation.

### 5.2 State Colocation and Ownership
State must reside as close as possible to the components that read and write it:
- **Local State:** Component-internal UI toggles (isModalOpen, dropdownOpen) belong in `useState` or `useReducer`.
- **Lifted State:** When two sibling components need to coordinate, lift the state to their immediate common ancestor.
- **Server Cache:** Remote entity data belongs in a dedicated cache (e.g., TanStack Query), **not** in global client state.
- **Client Global Store:** Reserve Zustand or Context for truly global UI settings (authenticated user profile, active workspace, theme).

### 5.3 Context API Limitations and Context Splitting
React Context is designed for low-frequency updates (theming, auth session, localization) or dependency injection. Every consumer of a Context re-renders whenever the Context value reference changes.
- **Context Splitting:** Split state from dispatch actions so that components needing only to dispatch events do not re-render on state changes:

```tsx
import React, { createContext, useContext, useReducer, Dispatch } from 'react';

type Action = { type: 'increment' } | { type: 'decrement' };
type State = { count: number };

const StateContext = createContext<State | null>(null);
const DispatchContext = createContext<Dispatch<Action> | null>(null);

function counterReducer(state: State, action: Action): State {
  switch (action.type) {
    case 'increment': return { count: state.count + 1 };
    case 'decrement': return { count: state.count - 1 };
  }
}

export function CounterProvider({ children }: { children: React.ReactNode }): React.JSX.Element {
  const [state, dispatch] = useReducer(counterReducer, { count: 0 });

  return (
    <StateContext.Provider value={state}>
      <DispatchContext.Provider value={dispatch}>
        {children}
      </DispatchContext.Provider>
    </StateContext.Provider>
  );
}

export function useCounterState(): State {
  const context = useContext(StateContext);
  if (!context) throw new Error('useCounterState must be used within CounterProvider');
  return context;
}

export function useCounterDispatch(): Dispatch<Action> {
  const context = useContext(DispatchContext);
  if (!context) throw new Error('useCounterDispatch must be used within CounterProvider');
  return context;
}
```

### 5.4 Elimination of Unnecessary Effects
Effects (`useEffect`) must only be used to synchronize React state with **external systems** (browser DOM, timers, WebSockets, non-React widgets).
- **Anti-Pattern (Derived State in Effect):** Do not calculate derived values inside `useEffect` and set another state variable. Calculate during render, wrapping in `useMemo` only if computationally intensive.
- **Anti-Pattern (User Event Handling in Effect):** Do not trigger side effects (API calls, toasts) based on state changes triggered by user events. Handle them directly in the event handler.

### 5.5 Stale Closures and Concurrency Bug Prevention
When scheduling asynchronous callbacks or event listeners that reference state, beware of stale closures:
- Use functional updates when next state depends on prior state: `setCount((prev) => prev + 1)`.
- Use `useRef` to hold the latest value of a callback or variable without triggering re-renders when consumed in long-lived subscriptions.

---

## 6. Domain E: React Rendering and Performance Engineering

### 6.1 The Render and Commit Architecture
Understanding the distinction between Render and Commit is essential for performance engineering:
1. **Render Phase:** React invokes the component function, computes the new virtual DOM tree (JSX), and diffs it against the prior tree. This phase is pure, side-effect free, and can be paused/restarted in React 18 Concurrent Mode.
2. **Commit Phase:** React applies the calculated DOM mutations, updates refs, and runs layout effects (`useLayoutEffect`), followed by passive effects (`useEffect`).

### 6.2 Memoization Discipline: When to Use and When to Avoid
Indiscriminate memoization adds memory overhead and dependency tracking costs. Apply memoization under strict conditions:

| Primitive | Mandatory Use Case | Prohibited / Pointless Use Case |
| :--- | :--- | :--- |
| `React.memo` | Heavy components with complex sub-trees receiving primitive or reference-stable props that re-render frequently due to parent churn. | Leaf components rendering simple HTML or components that always receive brand-new object/array literals. |
| `useMemo` | Heavy CPU computations (sorting/filtering 1,000+ items) or preserving reference stability for objects passed to memoized children. | Simple primitive operations (`const total = a + b`), mapping short arrays (< 50 items), or wrapping object literals passed to non-memoized components. |
| `useCallback`| Stabilizing callback references passed to `React.memo` children, custom hook return APIs, or dependency arrays of other hooks. | Inline event handlers passed to native DOM elements (`<button onClick={...}>`). |

### 6.3 Eliminating Unnecessary Rerenders via Component Hoisting
Before applying `React.memo`, restructure component composition:
- **Move State Down:** If only an input or dropdown needs state, isolate it in a dedicated small child component.
- **Pass Components as Children (`children` prop):** When a parent component manages state (e.g., scroll position or animation tick), pass its expensive children via `props.children`. React knows `children` has not changed and skips re-rendering the subtree.

### 6.4 Large Collections and Virtualization
Never render unbounded lists directly into the DOM. Over 500 complex DOM nodes degrades scroll performance and causes memory bloat.
- Virtualize large datasets with `@tanstack/react-virtual`, rendering only visible items plus a buffer overscan.
- **DOM Node Optimization Target:** Aim to maintain total DOM node counts below **1,500 elements** per view as a recommended reference target to preserve 60fps scrolling and prevent memory bloat. This is an optimization target rather than a universal architectural law; views with dense specialized content (such as analytical dashboards, financial ledgers, or document canvases) may exceed this target when justified, provided virtualization or incremental rendering is employed where practical.

### 6.5 Code Splitting and Dynamic Imports
- Break application bundles at route boundaries using `React.lazy` and `Suspense`.
- Heavy modal dialogues, data visualization charts, or document editors should be lazy-loaded on user intent (e.g., hover or button click).

```tsx
import React, { Suspense, lazy } from 'react';

const HeavyAnalyticsReport = lazy(() => import('./HeavyAnalyticsReport'));

export function AnalyticsDashboard(): React.JSX.Element {
  return (
    <div className="space-y-6">
      <h2>Operational Analytics</h2>
      <Suspense fallback={<div className="h-64 animate-pulse bg-gray-100 rounded-lg" />}>
        <HeavyAnalyticsReport />
      </Suspense>
    </div>
  );
}
```

### 6.6 Performance Targets and Project-Specific Budgets (Core Web Vitals)

Performance budgets are not universal hard architectural laws imposed identically on every React application; they must be established according to **application purpose**, **workload characteristics**, **target devices**, and **user network conditions** (e.g., lightweight customer-facing portals versus compute-intensive enterprise back-office consoles).

The numerical thresholds below serve as **recommended reference baselines and measurable optimization targets** for typical enterprise SPAs. They are evaluated through continuous CI synthetic benchmarks and production telemetry:

- **Interaction to Next Paint (INP):** $\le 200\text{ ms}$ (p75 recommended target). Ensures responsive keystroke, tap, and click feedback during user interactions.
- **Largest Contentful Paint (LCP):** $\le 2.5\text{ s}$ (p75 recommended target) under standard mobile/broadband network profiles.
- **Cumulative Layout Shift (CLS):** $\le 0.1$ (recommended target). Pre-allocate image dimensions, aspect-ratio boxes, and skeleton placeholders to prevent layout jank.
- **JavaScript Initial Bundle Budget:** $\le 170\text{ KB}$ gzipped (recommended entry-point target) for critical initial render paths.

#### 6.6.1 Measurement Methodology and Evidence Sources
Performance targets and Core Web Vitals must be validated using dual-layer evidence:
1. **Real-User Monitoring (RUM / Field Data):** Capture 75th percentile ($p75$) metrics across actual user sessions using the `web-vitals` library and observability pipelines (e.g., Sentry, OpenTelemetry, Datadog) to reflect real device diversity and network conditions.
2. **Laboratory & Synthetic Benchmarks (Lab Data):** Measure consistent, reproducible performance in CI pipelines using Lighthouse, WebPageTest, or Playwright trace metrics under throttled CPU and network conditions.

#### 6.6.2 Exception Protocol and Contextual Justification
DOM node counts ($\le 1,500$ elements) and initial bundle thresholds ($\le 170\text{ KB}$ gzipped) are optimization targets, not unyielding architectural constraints. Projects may establish higher or specialized budgets (e.g., complex data-dense workspaces, offline-capable progressive web apps, or rich visualization dashboards) provided that:
1. The technical necessity and rationale are formally documented in the endpoint or feature specification, or an approved Architecture Decision Record (ADR).
2. The user experience remains demonstrably fluid, verified through empirical RUM telemetry or laboratory profiling.
3. Code-splitting, virtualization, and lazy loading strategies have been thoroughly applied to minimize unnecessary asset transmission.

---

## 7. Domain F: Data Fetching and Server Communication

### 7.1 Server State Separation and Cache Management
Server data is a remote cache, not local application state. Use TanStack Query (React Query) to manage server state:
- Treat the query cache as the single source of truth for remote entities.
- Never mirror server data into local `useState` unless editing a temporary form draft.

```tsx
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/lib/api-client';

export interface Customer {
  id: string;
  name: string;
  status: 'active' | 'suspended';
}

export const customerKeys = {
  all: ['customers'] as const,
  lists: () => [...customerKeys.all, 'list'] as const,
  detail: (id: string) => [...customerKeys.all, 'detail', id] as const,
};

export function useCustomer(id: string) {
  return useQuery({
    queryKey: customerKeys.detail(id),
    queryFn: async ({ signal }) => {
      const response = await apiClient.get<Customer>(`/api/v1/customers/${id}`, { signal });
      return response.data;
    },
    staleTime: 5 * 60 * 1000, // Fresh for 5 minutes
    gcTime: 15 * 60 * 1000,    // Retained in garbage collection for 15 minutes
  });
}
```

### 7.2 Request Cancellation and Race-Condition Prevention
When a user navigates away or types into an auto-complete search field, prior pending requests must be aborted to conserve network bandwidth and eliminate race conditions:
- Always forward the query's `signal` parameter (`AbortSignal`) to the underlying HTTP client.
- Cancel inflight queries when query keys change.

### 7.3 Optimistic Updates with Automatic Rollback
For low-latency interactions, mutate UI optimistically while preserving rollback state in case the server rejects the command:

```tsx
export function useUpdateCustomerStatus() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, status }: { id: string; status: 'active' | 'suspended' }) => {
      await apiClient.patch(`/api/v1/customers/${id}/status`, { status });
    },
    onMutate: async ({ id, status }) => {
      // 1. Cancel outgoing queries to prevent overwriting optimistic state
      await queryClient.cancelQueries({ queryKey: customerKeys.detail(id) });

      // 2. Snapshot current state for rollback
      const previousCustomer = queryClient.getQueryData<Customer>(customerKeys.detail(id));

      // 3. Optimistically set new data
      if (previousCustomer) {
        queryClient.setQueryData<Customer>(customerKeys.detail(id), {
          ...previousCustomer,
          status,
        });
      }

      return { previousCustomer };
    },
    onError: (_err, { id }, context) => {
      // 4. Rollback on failure
      if (context?.previousCustomer) {
        queryClient.setQueryData(customerKeys.detail(id), context.previousCustomer);
      }
    },
    onSettled: (_data, _err, { id }) => {
      // 5. Invalidate to resync with authoritative server state
      queryClient.invalidateQueries({ queryKey: customerKeys.detail(id) });
    },
  });
}
```

### 7.4 Idempotency and Duplicate Submission Prevention
State-altering mutations (payments, status updates, resource creation) must prevent accidental duplicate submissions:
- Generate a client UUIDv4 idempotency key per intent and attach via the `X-Idempotency-Key` header.
- Disable submit buttons while mutation status is `'pending'`.

---

## 8. Domain G: Forms and Validation

### 8.1 Reusable Field Architecture with React Hook Form
Uncontrolled form architectures powered by `react-hook-form` minimize re-renders during text entry. Pair with Zod for strict declarative validation:

```tsx
import React, { useId } from 'react';
import { useForm, SubmitHandler } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

export const CreateUserSchema = z.object({
  fullName: z.string().min(2, 'Full name must contain at least 2 characters'),
  email: z.string().email('Please enter a valid email address'),
  role: z.enum(['admin', 'editor', 'viewer'], { errorMap: () => ({ message: 'Please select a role' }) }),
});

export type CreateUserInput = z.infer<typeof CreateUserSchema>;

interface UserFormProps {
  onSubmit: (data: CreateUserInput) => Promise<void>;
}

export function UserForm({ onSubmit }: UserFormProps): React.JSX.Element {
  const formId = useId();
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting, isDirty },
    setError,
  } = useForm<CreateUserInput>({
    resolver: zodResolver(CreateUserSchema),
    defaultValues: { fullName: '', email: '', role: 'viewer' },
  });

  const onFormSubmit: SubmitHandler<CreateUserInput> = async (data) => {
    try {
      await onSubmit(data);
    } catch (err: unknown) {
      // Handle server-side 422 validation errors
      if (err instanceof Error && 'fieldErrors' in err) {
        const fieldErrors = (err as { fieldErrors: Record<string, string> }).fieldErrors;
        Object.entries(fieldErrors).forEach(([field, msg]) => {
          setError(field as keyof CreateUserInput, { message: msg });
        });
      }
    }
  };

  return (
    <form onSubmit={handleSubmit(onFormSubmit)} noValidate className="space-y-4 max-w-md">
      <div>
        <label htmlFor={`${formId}-name`} className="block text-sm font-medium text-gray-700">
          Full Name
        </label>
        <input
          id={`${formId}-name`}
          type="text"
          aria-invalid={Boolean(errors.fullName)}
          aria-describedby={errors.fullName ? `${formId}-name-err` : undefined}
          {...register('fullName')}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
        />
        {errors.fullName && (
          <p id={`${formId}-name-err`} role="alert" className="mt-1 text-xs text-red-600">
            {errors.fullName.message}
          </p>
        )}
      </div>

      <div>
        <label htmlFor={`${formId}-email`} className="block text-sm font-medium text-gray-700">
          Email Address
        </label>
        <input
          id={`${formId}-email`}
          type="email"
          aria-invalid={Boolean(errors.email)}
          aria-describedby={errors.email ? `${formId}-email-err` : undefined}
          {...register('email')}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
        />
        {errors.email && (
          <p id={`${formId}-email-err`} role="alert" className="mt-1 text-xs text-red-600">
            {errors.email.message}
          </p>
        )}
      </div>

      <button
        type="submit"
        disabled={isSubmitting || !isDirty}
        className="w-full rounded-md bg-indigo-600 py-2 px-4 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
      >
        {isSubmitting ? 'Submitting...' : 'Create Account'}
      </button>
    </form>
  );
}
```

### 8.2 Client-Side vs Server-Side Validation Responsibilities
- **Client-Side Validation:** Provides immediate, accessible feedback to enhance user experience. It must never be relied upon as an enforcement barrier.
- **Server-Side Validation:** Enforces business invariants and data integrity. The frontend form must handle HTTP `422 Unprocessable Entity` responses gracefully, mapping backend validation error bags back to individual form inputs.

---

## 9. Domain H: Security Engineering

### 9.1 Cross-Site Scripting (XSS) Prevention
- React natively escapes strings embedded in JSX expressions (`<div>{untrustedString}</div>`).
- **Strict Ban on Unsanitized HTML:** Usage of `dangerouslySetInnerHTML` is strictly prohibited unless authorized by a security review. When trusted rich text must be rendered, sanitize using DOMPurify with strict tag allowlists:

```tsx
import DOMPurify from 'dompurify';

export function SafeRichTextView({ rawHtml }: { rawHtml: string }): React.JSX.Element {
  const cleanHtml = DOMPurify.sanitize(rawHtml, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'ul', 'li'],
    ALLOWED_ATTR: ['href', 'target', 'rel'],
  });

  return (
    <div
      dangerouslySetInnerHTML={{ __html: cleanHtml }}
      className="prose prose-sm max-w-none"
    />
  );
}
```

### 9.2 Token Storage and Session Security
- **Prohibition of LocalStorage for Secrets:** Never store sensitive authentication tokens (JWTs, session keys, access tokens) in `localStorage` or `sessionStorage`. Any script injected via XSS can exfiltrate local storage contents.
- **HttpOnly Cookie Authentication:** Maintain sessions using `HttpOnly`, `Secure`, `SameSite=Lax` (or `Strict`) cookies issued by the backend (e.g., Laravel Sanctum SPA authentication).
- **CSRF Token Handling:** Read the `XSRF-TOKEN` cookie issued by the server and forward it automatically in the `X-XSRF-TOKEN` request header for mutating HTTP verbs (`POST`, `PUT`, `PATCH`, `DELETE`).

### 9.3 Client Guards Are Not Security Boundaries
Hiding a button, disabling a menu item, or redirecting an unauthenticated user via React Router is purely a user experience convenience.
- **Authoritative Rule:** Backend APIs must enforce authentication and policy authorization on every request. A client-side guard provides **zero** security against direct HTTP requests executed via cURL or Postman.

### 9.4 Open Redirect and Safe URL Handling
When redirecting users based on URL query parameters (e.g., `?returnUrl=/dashboard`), validate the target path:
- Enforce that the target is a relative path starting with a single forward slash (`/`) and **not** a double slash (`//`, which triggers protocol-relative external redirects).
- Reject schemes like `javascript:` or `data:`.

---

## 10. Domain I: Accessibility and Inclusive Engineering (WCAG 2.2 AA)

### 10.1 Semantic HTML and Landmark Structure
Structure views using native HTML5 landmark elements:
- Use `<header>`, `<nav>`, `<main>`, `<aside>`, and `<footer>` rather than generic `<div>` containers.
- Use native `<button>` elements for interactive actions and native `<a>` elements for navigation. Never place click handlers on non-interactive `<div>` or `<span>` elements without full keyboard (`Enter`, `Space`) and ARIA role emulation.

### 10.2 Focus Management and Dialog Trapping
Modal dialogues and slide-over drawers must trap keyboard focus while open and restore focus upon dismissal:
- Prevent background tab cycling while a modal is mounted.
- When the modal closes, return focus to the trigger element that opened it.
- Listen for the `Escape` key to close active overlays.

### 10.3 Color Contrast and Non-Color Status Indicators
Adhere to WCAG 2.2 AA contrast thresholds:
- **Normal Text (< 18pt / < 14pt bold):** Minimum contrast ratio of **4.5:1** against the background.
- **Large Text ($\ge$ 18pt / $\ge$ 14pt bold) & UI Components:** Minimum contrast ratio of **3.0:1**.
- **Non-Color Indicators:** Never convey status (success, error, warning) using color alone. Always combine color with descriptive text, accessible badges, or distinct icons.

### 10.4 Key WCAG 2.2 Additions
- **Target Size (Minimum) (Criterion 2.5.8):** All interactive touch targets must measure at least **$24 \times 24\text{ CSS pixels}$**, or provide sufficient offset spacing.
- **Focus Not Obscured (Criterion 2.4.11):** When an element receives keyboard focus, it must not be completely hidden beneath sticky headers, floating footers, or non-modal banners.

---

## 11. Domain J: Testing and Quality Assurance

### 11.1 The Testing Pyramid and Boundary Definitions
Enterprise testing must balance speed, confidence, and maintenance cost:
- **Unit Tests (Vitest):** Validate pure domain logic, mathematical calculations, utility functions, and custom hook state transitions in isolation.
- **Component Tests (Vitest + React Testing Library):** Render individual components or composite feature widgets in a simulated DOM. Test observable user behavior, ARIA roles, and form interactions.
- **Integration Tests (Vitest + MSW):** Test complete feature workflows (e.g., user registration or checkout flow) with real query hooks communicating against Mock Service Worker (MSW) at the network layer.
- **End-to-End Tests (Playwright):** Validate critical business paths across production-bundled builds in headless Chromium, Firefox, and WebKit browsers.

### 11.2 React Testing Library: Behavior Over Implementation
Never test component internal state, hook references, or child component names. Test what the user sees and interacts with:

```tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { UserForm } from './UserForm';

describe('UserForm Component', () => {
  it('submits valid data and announces accessible errors on invalid input', async () => {
    const user = userEvent.setup();
    const handleSubmit = vi.fn().mockResolvedValue(undefined);

    render(<UserForm onSubmit={handleSubmit} />);

    const submitBtn = screen.getByRole('button', { name: /create account/i });
    expect(submitBtn).toBeDisabled();

    // Fill valid data
    await user.type(screen.getByLabelText(/full name/i), 'Ada Lovelace');
    await user.type(screen.getByLabelText(/email address/i), 'ada@example.com');
    expect(submitBtn).toBeEnabled();

    await user.click(submitBtn);

    expect(handleSubmit).toHaveBeenCalledTimes(1);
    expect(handleSubmit).toHaveBeenCalledWith({
      fullName: 'Ada Lovelace',
      email: 'ada@example.com',
      role: 'viewer',
    });
  });
});
```

### 11.3 Automated and Manual Accessibility Verification
- Integrate `axe-core` into component tests using `vitest-axe` to assert zero detectable automated accessibility violations.
- Run Playwright automated accessibility scans on all critical route layouts.
- Perform manual keyboard audits (`Tab`, `Shift+Tab`, `Enter`, `Space`, `Escape`, `Arrow keys`) and screen-reader spot checks (NVDA, VoiceOver) on all interactive compound widgets.

---

## 12. Domain K: Frontend Design Systems and UI Engineering

### 12.1 Design Tokens and Semantic Variables
UI styling must be governed by semantic design tokens rather than arbitrary values:
- Define tokens via CSS custom properties (`--color-surface-primary`, `--color-text-brand`) or Tailwind theme extensions.
- Strictly prohibit arbitrary Tailwind utility classes (e.g., `p-[13px]`, `text-[#324567]`). Enforce semantic scale variables (`p-3`, `text-brand-600`).

### 12.2 Component Contract Consistency via Class Variance Authority (CVA)
Structure UI primitives using `class-variance-authority` (cva) to guarantee predictable variant, size, and state combinations:

```tsx
import React, { forwardRef } from 'react';
import { cva, type VariantProps } from 'class-variance-authority';

const buttonVariants = cva(
  'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none',
  {
    variants: {
      variant: {
        primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus:ring-indigo-600',
        secondary: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        danger: 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-600',
      },
      size: {
        sm: 'h-8 px-3 text-xs',
        md: 'h-10 px-4 text-sm',
        lg: 'h-12 px-6 text-base',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  isLoading?: boolean;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, isLoading, children, disabled, ...props }, ref) => {
    return (
      <button
        ref={ref}
        disabled={disabled || isLoading}
        className={buttonVariants({ variant, size, className })}
        {...props}
      >
        {isLoading ? <span className="animate-spin mr-2">●</span> : null}
        {children}
      </button>
    );
  }
);

Button.displayName = 'Button';
```

---

## 13. Domain L: Routing and Navigation

### 13.1 Scalable Route Architecture and Nested Layouts
Organize routes into nested layouts that preserve common chrome (navbars, sidebars) and avoid unmounting shared layout trees during child navigation:

```tsx
import { createBrowserRouter, Outlet, RouterProvider } from 'react-router-dom';
import { lazy, Suspense } from 'react';
import { ErrorBoundary } from '@/components/ErrorBoundary';

const DashboardView = lazy(() => import('@/features/dashboard/DashboardView'));
const BillingView = lazy(() => import('@/features/billing/BillingView'));

function AppLayout(): React.JSX.Element {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <nav className="h-16 border-b bg-white px-6 flex items-center">
        <span className="font-bold text-lg">Universal Core</span>
      </nav>
      <main className="flex-1 p-6 max-w-7xl mx-auto w-full">
        <ErrorBoundary fallbackTitle="Section Error">
          <Suspense fallback={<div className="h-48 animate-pulse bg-gray-100 rounded" />}>
            <Outlet />
          </Suspense>
        </ErrorBoundary>
      </main>
    </div>
  );
}

export const appRouter = createBrowserRouter([
  {
    path: '/',
    element: <AppLayout />,
    children: [
      { index: true, element: <DashboardView /> },
      { path: 'billing', element: <BillingView /> },
    ],
  },
]);
```

### 13.2 URL State Synchronization
Filters, search terms, pagination offsets, and active tabs must be synchronized with URL query parameters (`useSearchParams`):
- Users must be able to bookmark, refresh, or share filtered view links without losing filter context.
- Use `navigate(..., { replace: true })` for rapid keystroke search filters to avoid polluting the browser history stack.

---

## 14. Domain M: Reliability and Production Readiness

### 14.1 Observability and Telemetry Scrubbing
Integrate frontend observability (e.g., Sentry, OpenTelemetry) with explicit data-scrubbing hooks:
- Scrub PII (Personally Identifiable Information), email addresses, authorization headers, passwords, and credit card numbers from error payloads before network dispatch.
- Attach breadcrumbs for navigation changes, route transitions, and user-initiated actions.

### 14.2 Resilient Feature Flags
Feature flag evaluations must be safe and non-blocking:
- Always define safe fallback defaults if the remote flag configuration service is unreachable.
- Never block application initialization waiting for non-critical feature flag endpoints.

### 14.3 Deployment Hygiene and Chunk Load Failures
When new production releases deploy, old JavaScript asset chunks are replaced on the hosting CDN, causing dynamic import failures for users on older open sessions:
- Catch dynamic chunk loading errors and trigger a graceful window reload or prompt the user to refresh.
- Serve `index.html` with `Cache-Control: no-cache, no-store, must-revalidate`.
- Serve hashed assets (`/assets/*.js`, `/assets/*.css`) with long-lived immutable caching (`Cache-Control: public, max-age=31536000, immutable`).

---

## 15. Domain N: Code Quality, Maintainability, and Governance

### 15.1 Universal Core 300-Line Governance Rule
Every source file (`.ts`, `.tsx`, `.js`, `.jsx`, `.css`) in a React application must remain within **300 physical lines of code**.
- **Refactoring Strategy for Large Files:**
  - Extract complex form fields into standalone sub-components.
  - Extract multi-step asynchronous logic and mutations into custom hooks (`useFeatureMutation`).
  - Extract static data tables, column definitions, and Zod schemas into dedicated configuration files.
  - Decompose monolithic compound components into separate sibling files under a unified directory.
- Any exception must be registered in the codebase with documented justification approved by the Frontend Architecture Authority.

### 15.2 Strict Prohibition of Incomplete Implementations
All code delivered by engineering agents must be complete and production-grade:
- Strict prohibition of mock delay functions (`await sleep(1000)`), placeholder stubs (`// TODO: implement later`), or fake security checks.
- All error branches must provide real recovery mechanisms, accessible user feedback, and telemetry reporting.

---

## 16. Pull Request Review Checklist & Verification Runbook

### 16.1 Principal Engineer PR Review Checklist
Before approving any React/TypeScript pull request, verify:
- [ ] **Line Limit:** Every modified and newly added file does not exceed 300 physical lines.
- [ ] **Type Soundness:** Zero `any` types; all external API boundaries validated with Zod; zero `tsc --noEmit` errors.
- [ ] **State Colocation:** Server state is managed via query hooks; zero unnecessary effects; zero duplicate state copies.
- [ ] **Accessibility:** Semantic HTML used; interactive elements keyboard-navigable; ARIA labels and roles correctly mapped; zero axe-core violations.
- [ ] **Security:** No un-sanitized `dangerouslySetInnerHTML`; tokens not in `localStorage`; all sensitive actions authorized server-side.
- [ ] **Performance:** Dynamic imports for heavy routes/dialogs; virtualized rendering for large lists; stable keys used in all lists.
- [ ] **Testing:** Meaningful assertions on user-observable behavior; MSW used for network boundaries; regression tests covering happy and error paths.

### 16.2 Verification Command Runbook
Execute the following verification sequence in the application workspace prior to merging:

```bash
# 1. Type safety check
npx tsc --noEmit

# 2. Linting and accessibility rules
npx eslint . --max-warnings 0

# 3. Unit and component tests with coverage
npx vitest run --coverage

# 4. Automated accessibility and E2E verification
npx playwright test
```

*(Note: The commands above reflect the standard Universal Core reference toolchain. Consuming projects utilizing approved alternative test runners or package managers, such as Jest or Cypress, execute their corresponding equivalent verification scripts as defined in their Project Technology Profile).*

---

## 17. References and Authoritative Standards

- **React Documentation:** [https://react.dev/](https://react.dev/)
- **TypeScript Handbook:** [https://www.typescriptlang.org/docs/](https://www.typescriptlang.org/docs/)
- **Node.js Documentation:** [https://nodejs.org/docs/](https://nodejs.org/docs/)
- **Vitest Guide:** [https://vitest.dev/guide/](https://vitest.dev/guide/)
- **React Testing Library:** [https://testing-library.com/docs/react-testing-library/intro/](https://testing-library.com/docs/react-testing-library/intro/)
- **Playwright Documentation:** [https://playwright.dev/docs/](https://playwright.dev/docs/)
- **W3C WAI-ARIA APG:** [https://www.w3.org/WAI/ARIA/apg/](https://www.w3.org/WAI/ARIA/apg/)
- **W3C WCAG 2.2 Standard:** [https://www.w3.org/TR/WCAG22/](https://www.w3.org/TR/WCAG22/)
- **OWASP ASVS & Top 10:** [https://owasp.org/](https://owasp.org/)

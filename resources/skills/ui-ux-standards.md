# Shared Engineering Skill: UI/UX Engineering and Design System Standards

**Skill ID:** SKILL-UIUX-001  
**Version:** 2.1.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  
**Approved Baseline:** HTML5, CSS3, Tailwind CSS v3.4+/v4.x, Design Tokens (W3C Design Tokens Community Group Format / CSS Custom Properties), Arabic Typography (Cairo, Tajawal, Alexandria, Amiri, IBM Plex Sans Arabic), English Typography (Inter, Roboto, Plus Jakarta Sans), Variable Fonts, WCAG 2.2 AA / AAA Interoperability, CSS Logical Properties  

---

## 1. Skill Identity, Scope, and Enterprise Baseline

The **UI/UX Engineering and Design System Standards** skill establishes the authoritative engineering standard for architecting, designing, implementing, reviewing, and validating user interfaces, user experiences, and visual identity customization systems across Universal Core applications.

### 1.1 Mission and Purpose
This skill equips UI/UX Architects, Frontend Engineers, and autonomous engineering agents to deliver cohesive, ergonomically sound, cognitively optimized, responsive, and visually customizable digital interfaces. It establishes:
- **Design-Driven Engineering Rigor:** Eliminating ad-hoc, uncontrolled styling and inconsistent interaction models by grounding all UI development in structured design systems and semantic tokens.
- **Enterprise Design Customization System:** Defining an operational, configurable, and extensible standard for managing complete visual identity—including color palettes, typography scales, design presets, themes, component styling, and live previews.
- **Cognitive Ergonomics & Predictability:** Minimizing cognitive load through consistent information hierarchies, progressive disclosure, and predictable interaction flows.
- **First-Class Bidirectional Engineering (RTL/LTR):** Seamless, native support for Arabic and English languages, leveraging CSS logical properties, bidirectional typography scales, and direction-aware layout structures.
- **Holistic State Architecture:** Enforcing exhaustive coverage of the complete 12-state interactive lifecycle across every component and view (Initial, Loading, Empty, Populated, Active, Submitting, Success, Validation Error, Server Error, Network Offline, Read-Only, Unsaved Changes).
- **Technology-Agnostic Reusability:** Providing framework-independent design tokens and interaction standards that integrate cleanly with Laravel Blade, Filament 5.x, Livewire 4.x, React 18+, and Vue 3 / Inertia.js.
- **Strict Quality Gating & Acceptance:** Enforcing objective review criteria, verifiable design metrics, and defect severities to guarantee that no defective or inconsistent interface reaches production.

### 1.2 Architectural Boundaries and Technology Scope
UI/UX Engineering interacts with multiple engineering layers but maintains strict separation of concerns:

```
┌─────────────────────────────────────────────────────────────────┐
│                     UI/UX Engineering Layer                     │
│  - User Experience Workflows & Task Flow Mapping                │
│  - Centralized Design Tokens (Color, Spacing, Typography, etc.) │
│  - Theme Management, Design Presets & Visual Customization      │
│  - Information Architecture, Navigation & Layout Hierarchy      │
│  - Interaction Design, State Machines & Micro-Interactions      │
│  - Responsive Breakpoints, Fluid Grids & Container Queries      │
│  - RTL / LTR Bidirectional Layouts & Localized Typography       │
└─────────────────────────────────┬───────────────────────────────┘
                                  │ Governs & Guides
         ┌────────────────────────┴────────────────────────┐
         ▼                                                 ▼
┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│  Accessibility Standards (A11Y) │       │   Frontend Implementation       │
│  - WCAG 2.2 AA/AAA Rules        │       │   - Laravel Blade Templates     │
│  - Semantic HTML & ARIA Roles   │       │   - Filament 5.x Admin Panels   │
│  - Keyboard Focus Trapping & APG│       │   - Livewire 4.x Components     │
│  - Screen Reader Announcements   │       │   - React 18+ / TypeScript SPAs │
│  - Color Contrast Compliance    │       │   - Vue 3 / Inertia Components  │
│  [Cross-Reference:              │       │   [Cross-Reference:             │
│   SKILL-A11Y-001]               │       │    SKILL-FILA-001, REACT, VUE]  │
└─────────────────────────────────┘       └─────────────────────────────────┘
```

#### 1.2.1 Core Distinctions and Terminology
To maintain precision across engineering disciplines, engineers and agents must strictly adhere to standard definitions:
- **User Interface (UI):** The visual, auditory, and tactile presentation layer of the application (typography, colors, borders, icons, layout, and visual affordances).
- **User Experience (UX):** The overall internal journey, usability, efficiency, and cognitive friction experienced by a human interacting with the system.
- **Interaction Design (IxD):** The behavioral definition of how controls respond across state transitions (hover, active, focus, loading, success, failure) and how time-based changes unfold.
- **Visual Design:** The aesthetic system establishing visual weight, harmony, contrast, hierarchy, alignment, balance, and brand consistency.
- **Design System:** The single source of truth containing design tokens, component patterns, governance policies, and reusable software assets ensuring coherence across applications.
- **Design Customization Specification:** The formalized rules, token contracts, and validation constraints governing runtime or administrative modification of visual identities. (This skill defines these engineering requirements; consuming projects implement the actual customization runtime or administration dashboard when approved project requirements call for it).

#### 1.2.2 Universal Core Frontend Rules
1. **The 300-Line Modularity Invariant:** No frontend template, component, or style definition file may exceed **300 physical lines**. Complex screens must be decomposed into atomic, cohesive sub-components, partials, or layout slots.
2. **Separation of Presentation, Behavior, and Business Logic:**
   - Templates and UI components govern markup, visual tokens, and local presentation state.
   - Interaction behavior is encapsulated in custom hooks, Alpine.js directives, or component controllers.
   - Core business logic, pricing algorithms, permissions, and database operations belong exclusively in backend domain layers. A UI control must **never** execute raw database queries or make unvalidated assumptions about authorization.
3. **No Decorative Phantom Controls:** Every button, menu item, toggle, or link rendered in an interface must bind to a fully implemented, functional backend handler or client workflow. Non-functional placeholder buttons or "under construction" dead ends are strictly forbidden in production.

---

## 2. UI/UX Architecture and Engineering Principles

### 2.1 User-Centered Design Principles
1. **Consistency and Predictability:** Interfaces must behave predictably. Equivalent actions across different sections of an application must look identical, occupy consistent positions, and produce predictable outcomes (Principle of Least Astonishment).
2. **Cognitive Load Minimization:**
   - **Hick’s Law:** The time to make a decision increases logarithmically with the number and complexity of choices. Break complex forms and multidimensional filters into logical groupings or staged disclosures.
   - **Miller’s Law:** The working memory of an average person holds $7 \pm 2$ chunks of information. Structure dashboards, forms, and data grids into chunks of 5–9 related items.
   - **Fitts’s Law:** The time required to rapidly move to a target area is a function of the ratio between the distance to the target and the width of the target. Critical interactive elements must have sufficient target size (minimum 44×44px touch targets) and be positioned within ergonomic reach.
3. **Progressive Disclosure:** Present primary workflows and essential fields immediately. Defer advanced configurations, optional metadata, and edge-case settings behind clear, expandable contextual controls (e.g., accordions, tabs, or "Show Advanced Options" disclosures) without obscuring critical data.
4. **Information Hierarchy & Visual Weight:** Guide the user’s eye sequentially:
   - Primary: Page title, primary action button, main metric.
   - Secondary: Supporting section headers, filter bars, secondary actions.
   - Tertiary: Meta-labels, timestamps, breadcrumbs, helper descriptions.

---

## 3. User Research, Personas, and UX Analysis

### 3.1 UX Research Methodology in Enterprise Software
Enterprise software design must be grounded in verified user context, not subjective designer assumptions. Agents and engineers must document and analyze:
1. **User Archetypes and Work Context:**
   - *Operational Specialists:* High-volume, daily data entry workers who prioritize keyboard shortcuts, compact data density, speed, and zero latency over decorative whitespace.
   - *Executive/Supervisory Users:* Decision-makers who require high-level aggregated metrics, anomaly indicators, trend visualizations, and rapid export capabilities.
   - *End Consumers / External Clients:* Infrequent users who require spacious layouts, high guidance, conversational validation, and frictionless self-service.
2. **Task Flow Mapping:** Map the complete end-to-end user path before creating components:
   - Identify the trigger event.
   - Enumerate every decision point, validation step, and failure mode.
   - Quantify the minimum number of user interactions (clicks, keypresses) required to complete the task.
   - Eliminate redundant steps, unnecessary confirmation dialogs for non-destructive actions, and circular navigation loops.
3. **Fact vs. Assumption Discipline:**
   > [!IMPORTANT]
   > Agents and engineers must never invent imaginary user research, fabricate usability test metrics, or cite nonexistent user interviews. When empirical user data is unavailable, design decisions must be explicitly justified by documented design system tokens, WCAG 2.2 AA criteria, or established cognitive ergonomics.

---

## 4. Design Systems, Design Tokens, and Centralized Token Architecture

Design tokens are the foundational, immutable values of a design system stored in a technology-agnostic format (W3C Design Tokens Community Group DTCG JSON / CSS Custom Properties) and transformed into platform-specific variables (Tailwind configuration, CSS, Sass, TypeScript constants).

### 4.1 Token Tier Architecture & Centralized Token Governance
Universal Core enforces a strict three-tier token hierarchy governing all visual attributes:

```
┌─────────────────────────────────────────────────────────────────┐
│                     1. Global Primitive Tokens                  │
│  Raw values without semantic context:                           │
│  blue-600: #2563eb | space-4: 16px | font-family-cairo: 'Cairo' │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Informs & Binds
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                     2. Semantic Design Tokens                   │
│  Intent-driven tokens mapping primitives to contextual roles:    │
│  color-primary: {primitive.blue-600} | space-card: {space-4}    │
│  surface-canvas: {primitive.slate-50} | font-body: {font-sans}  │
└────────────────────────────────┬────────────────────────────────┘
                                 │ Resolves & Scopes
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                     3. Component Design Tokens                  │
│  Scoped tokens consumed by specific component slots & states:   │
│  btn-primary-bg: {semantic.color-primary}                       │
│  table-row-hover-bg: {semantic.surface-hover}                   │
│  input-border-focus: {semantic.border-focus}                    │
└─────────────────────────────────────────────────────────────────┘
```

#### 4.1.1 Token Taxonomy and Property Domains
The centralized design token architecture governs ten comprehensive property domains:
1. **Color Tokens:** Raw color shades, semantic intents, alpha channels, and stateful tints.
2. **Typography Tokens:** Families, weights, sizes, line heights, letter tracking, word spacing.
3. **Spacing Tokens:** Layout gutters, component padding, inner gaps, section offsets.
4. **Sizing Tokens:** Control heights, icon dimensions, container max-widths, modal boundaries.
5. **Border and Radius Tokens:** Border widths, border styles, corner curvature scales.
6. **Shadow and Elevation Tokens:** Box shadows, drop shadows, z-depth elevation levels.
7. **Opacity Tokens:** Disabled states, backdrop overlays, hover surface tints.
8. **Motion and Transition Tokens:** Durations, easing bezier curves, transform scales.
9. **Z-Index Tokens:** Layer stacking order across dropdowns, sticky headers, modals, toasts.
10. **Breakpoint Tokens:** Viewport boundary definitions for responsive layout adaptations.

#### 4.1.2 Deterministic Naming Conventions
All design tokens must strictly adhere to the deterministic naming schema:
```
[tier]-[domain]-[property]-[concept]-[variant]-[state]
```
- **Tier:** `primitive`, `semantic`, or `component` (omitted in shorthand CSS custom properties when prefixed by domain, e.g., `--color-primary`).
- **Domain:** `color`, `font`, `space`, `size`, `radius`, `shadow`, `opacity`, `motion`, `z`.
- **Property:** `bg`, `text`, `border`, `size`, `weight`, `height`.
- **Concept / Intent:** `primary`, `neutral`, `surface`, `danger`, `h1`, `card`.
- **Variant:** `subtle`, `prominent`, `compact`, `sm`, `lg`.
- **State:** `default`, `hover`, `active`, `focus`, `disabled`.

*Examples:*
- Primitive: `primitive.color.blue.600` (`#2563eb`)
- Semantic: `semantic.color.action.primary.default` (`var(--color-primary)`)
- Component: `component.btn.primary.bg.hover` (`var(--btn-primary-bg-hover)`)

#### 4.1.3 Token Ownership, Inheritance, and Override Rules
- **Ownership Boundaries:**
  - *Core Design System Authority:* Owns global primitives, default semantic mappings, and base component contracts.
  - *Theme Authors / Customizers:* May override semantic mappings and preset definitions. They **MUST NOT** mutate global primitive values directly.
  - *Component Implementers:* Consume component tokens or semantic tokens. Components **MUST NOT** invent disconnected, ad-hoc style values.
- **Inheritance Cascade:**
  `Global Primitive → Default Semantic → Theme Semantic → Tenant Override → User Ergonomic Preference`
  - A component resolves to its component token; if undefined, it falls back to the semantic token.
  - A semantic token resolves to its theme-specific primitive mapping; if undefined, it falls back to the core default primitive.
- **Token Dependency Validation:**
  The token compiler and validation layer must enforce:
  1. *Acyclic Graphs:* Circular references (e.g., Token A references Token B, which references Token A) are strictly prohibited and must fail compilation.
  2. *Dangling Pointer Prevention:* Every token reference (`{semantic.color.primary}`) must resolve to an existing registered token definition.
  3. *Syntax Conformance:* Validated against the W3C DTCG format specification.

---

### 4.2 Comprehensive Color Management Architecture

Color management must be treated as a rigorous, configurable engineering subsystem rather than a static list of color variables.

#### 4.2.1 Color Families and Tonal Shade Scales
A complete color architecture provides structured tonal stops across all color families:
- **Tonal Levels:** Standard 11-stop scales (`50`, `100`, `200`, `300`, `400`, `500`, `600`, `700`, `800`, `900`, `950`).
- **Configurable Tonal Stop Support:** When a project's selected color system utilizes alternate tonal stops (e.g., Material Design 50–900 or custom 3-stop high-contrast scales), the system must configure and validate those specific stops without forcing artificial interpolation.
- **Palette Classifications:**
  1. *Primary Palette:* Core brand identity and primary user workflow drivers.
  2. *Secondary Palette:* Supporting brand accents, secondary actions, and badges.
  3. *Tertiary Palette:* Specialized analytical categorizations, tags, and data visualization series.
  4. *Neutral Palette:* Canvas backgrounds, surface cards, dividers, borders, and body typography.
  5. *Accent Palettes:* High-visibility highlights, selection indicators, and callouts.

#### 4.2.2 Complete Semantic Color Roles
Components must never bind directly to raw palette names (e.g., `text-blue-600`). All styling must consume semantic color tokens:

| Semantic Role | Purpose | Light Mode Baseline | Dark Mode Baseline |
| :--- | :--- | :--- | :--- |
| `surface-canvas` | Base application background | `#f8fafc` (slate-50) | `#0f172a` (slate-900) |
| `surface-card` | Container, panel, and card background | `#ffffff` (white) | `#1e293b` (slate-800) |
| `surface-elevated` | Dropdowns, dialogs, popovers | `#ffffff` (white) | `#334155` (slate-700) |
| `surface-hover` | Subtle background hover tint | `#f1f5f9` (slate-100) | `#334155` (slate-700) |
| `surface-active` | Pressed control surface | `#e2e8f0` (slate-200) | `#475569` (slate-600) |
| `surface-disabled` | Inactive control background | `#f1f5f9` (slate-100) | `#1e293b` (slate-800) |
| `text-primary` | Headings, high-emphasis body text | `#0f172a` (slate-900) | `#f8fafc` (slate-50) |
| `text-secondary` | Labels, supporting text, timestamps | `#475569` (slate-600) | `#94a3b8` (slate-400) |
| `text-muted` | Placeholders, secondary metadata | `#94a3b8` (slate-400) | `#64748b` (slate-500) |
| `text-disabled` | Inactive text and icons | `#cbd5e1` (slate-300) | `#475569` (slate-600) |
| `text-on-primary` | Text rendered on primary action surfaces | `#ffffff` (white) | `#ffffff` (white) |
| `text-on-danger` | Text rendered on destructive surfaces | `#ffffff` (white) | `#ffffff` (white) |
| `border-subtle` | Dividers, subtle container borders | `#e2e8f0` (slate-200) | `#334155` (slate-700) |
| `border-default` | Standard input borders, card frames | `#cbd5e1` (slate-300) | `#475569` (slate-600) |
| `border-strong` | Active input boundaries, table outlines | `#94a3b8` (slate-400) | `#64748b` (slate-500) |
| `border-focus` | Keyboard focus ring indicator | `#2563eb` (blue-600) | `#3b82f6` (blue-500) |
| `primary` | Primary action, focus rings, brand identity | `#2563eb` (blue-600) | `#3b82f6` (blue-500) |
| `primary-hover` | Hovered primary interactive state | `#1d4ed8` (blue-700) | `#60a5fa` (blue-400) |
| `primary-active` | Pressed primary interactive state | `#1e40af` (blue-800) | `#2563eb` (blue-600) |
| `success` | Positive states, completed operations | `#16a34a` (green-600) | `#22c55e` (green-500) |
| `success-surface`| Background for positive alert banners | `#f0fdf4` (green-50) | `#052e16` (green-950) |
| `warning` | Cautions, non-blocking attention | `#d97706` (amber-600) | `#f59e0b` (amber-500) |
| `warning-surface`| Background for caution banners | `#fffbeb` (amber-50) | `#451a03` (amber-950) |
| `danger` | Errors, destructive actions, blockers | `#dc2626` (red-600) | `#ef4444` (red-500) |
| `danger-surface` | Background for error alert banners | `#fef2f2` (red-50) | `#450a0a` (red-950) |
| `info` | Contextual assistance, neutral notices | `#0284c7` (sky-600) | `#38bdf8` (sky-400) |
| `info-surface` | Background for informational notices | `#f0f9ff` (sky-50) | `#082f49` (sky-950) |

#### 4.2.3 Palette Generation and Curation Models
Color scales can be provided through two authorized models:
1. **Curated Discrete Stops:** Hand-tuned color values designed specifically for enterprise contrast and perceptual harmony across every stop.
2. **Algorithmic Perceptual Generation:** Generated from a seed brand hex code using perceptual color spaces such as **OKLCH** (Lightness, Chroma, Hue). Algorithmic generation must:
   - Interpolate Lightness ($L$) monotonically from $0.98$ (shade 50) down to $0.15$ (shade 950).
   - Constrain Chroma ($C$) to avoid gamut clipping in standard sRGB displays.
   - Maintain uniform Hue ($H$) across the tonal ramp to prevent hue-shift artifacts.

#### 4.2.4 Color Customization Lifecycle & Validation Rules
When an authorized project enables visual customization, custom palette operations must follow strict lifecycle rules:
- **Supported Operations:** Create, Edit, Duplicate, Import (DTCG JSON), Export (DTCG JSON), and Delete (only permitted for unassigned custom palettes; default system palettes are immutable).
- **Format Validation:** Values must conform to standard formats (`#RRGGBB`, `#RRGGBBAA`, `rgb()`, `hsl()`, or `oklch()`). Unrecognized strings must fail validation.
- **Missing Shade Detection:** If a theme specifies a custom palette, all expected tonal stops must either be explicitly defined or generated via an approved algorithmic fallback.
- **Contrast Verification & Accessibility:**
  - Automated mathematical evaluation of relative luminance ($L$):
    $$L = 0.2126 \cdot R_{lin} + 0.7152 \cdot G_{lin} + 0.0722 \cdot B_{lin}$$
  - Verification of contrast ratios against WCAG 2.2 criteria:
    - Normal text against background: minimum **$4.5:1$** (WCAG AA) / **$7:1$** (WCAG AAA).
    - Large text ($\ge 18\text{pt}$ or $\ge 14\text{pt}$ bold) and UI components/borders: minimum **$3:1$**.
  - Any customization creating text-on-surface combinations failing the $4.5:1$ boundary must be blocked or visually flagged with a non-suppressible accessibility warning.
- **Prevention of Arbitrary Hardcoded Colors:**
  Static analysis linters (e.g., Stylelint, ESLint, Blade Formatter) and build checks must prohibit raw color hex literals inside template files (`.blade.php`, `.tsx`, `.vue`) and CSS rules.

---

### 4.3 Modular Spacing and Sizing Scales

#### 4.3.1 Spacing Scale (4px/8px Base)
All margins, paddings, gaps, and structural offsets must strictly derive from the 4px/8px modular scale:

| Token Name | Value | Rem Equivalent | Primary Application |
| :--- | :--- | :--- | :--- |
| `space-0.5` | 2px | 0.125rem | Micro-offsets, border alignments, hairline dividers |
| `space-1` | 4px | 0.25rem | Icon gaps, badge padding, compact tag margins |
| `space-1.5` | 6px | 0.375rem | Small button padding, status indicators |
| `space-2` | 8px | 0.5rem | Button vertical padding, compact form gaps |
| `space-3` | 12px | 0.75rem | Standard input vertical padding, table cell padding |
| `space-4` | 16px | 1.0rem | Standard card padding, component margins, form field gaps |
| `space-5` | 20px | 1.25rem | Moderate panel padding, list group spacing |
| `space-6` | 24px | 1.5rem | Section spacing, container padding, modal body padding |
| `space-8` | 32px | 2.0rem | Major layout gutters, header-to-content separation |
| `space-10` | 40px | 2.5rem | Extended section spacing, spacious card padding |
| `space-12` | 48px | 3.0rem | Page section breaks, empty state vertical breathing room |
| `space-16` | 64px | 4.0rem | Landing layout margins, high-density boundaries |
| `space-20` | 80px | 5.0rem | Hero section breathing room, large viewport margins |

#### 4.3.2 Sizing Scale
Component dimensions (heights, widths, min/max bounds) derive from standard size tokens:
- **Control Heights:**
  - `size-control-sm`: 32px (0.5rem vertical padding, compact tables, dense filters).
  - `size-control-md`: 40px (standard operational buttons, inputs, selects).
  - `size-control-lg`: 48px (high-visibility forms, touch-friendly kiosks, onboarding).
- **Target Size Rule:** All interactive targets must satisfy a minimum boundary of 44×44px (or provide 44px inclusive touch margins per WCAG 2.2 SC 2.5.8).

---

### 4.4 Comprehensive Typography Management Architecture

Typography conveys authority, structure, and comprehension. The typography architecture must support multiple languages, script systems, actual font assets, and rigorous typographic controls.

#### 4.4.1 Font Families and Script Stacks
The typography system provides dedicated stacks for distinct script systems:
1. **Arabic Font Families:**
   - *Primary Geometric/Modern:* `Cairo`, `Tajawal`.
   - *Data & Financial:* `Alexandria`.
   - *Traditional/Editorial:* `Amiri`, `IBM Plex Sans Arabic`.
2. **English & Latin Font Families:**
   - *Neutral Grotesque:* `Inter`, `Roboto`, `Plus Jakarta Sans`.
   - *System UI Fallback:* `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`.
3. **Multilingual Font Families:**
   - Font stacks providing unified glyph metrics across Latin and Arabic (e.g., `Noto Sans`, `IBM Plex Sans`).
4. **Monospace Families:**
   - Code inspection, system logs, cryptographic keys: `JetBrains Mono`, `Fira Code`, `Alexandria Monospace`, `ui-monospace, SFMono-Regular, monospace`.
5. **Numeric and Tabular Data Typography:**
   - Financial ledgers, transaction tables, and analytics counters must enforce tabular figures (`font-variant-numeric: tabular-nums;`) and slashed zeros where supported (`font-feature-settings: "tnum" 1, "zero" 1;`) to ensure perfect vertical decimal alignment.

```css
/* Baseline Typography Stacks */
:root {
  --font-family-sans-ar: 'Cairo', 'Tajawal', system-ui, sans-serif;
  --font-family-sans-en: 'Inter', system-ui, -apple-system, sans-serif;
  --font-family-data: 'Alexandria', 'Inter', monospace;
  --font-family-mono: 'JetBrains Mono', 'Fira Code', monospace;
}
```

#### 4.4.2 Font Weights and Variable Fonts
The CSS font-weight scale spans from 100 to 900:
- `100` Thin, `200` Extra Light, `300` Light, `400` Regular/Normal, `500` Medium, `600` Semi-Bold, `700` Bold, `800` Extra Bold, `900` Black.

**Critical Font Asset Governance Rules:**
1. **True Asset Availability Verification:** An agent or engineer **MUST NOT** configure or register a font weight unless the actual physical font file (e.g., `.woff2`) for that weight is present and loaded.
2. **Prevention of Faux Browser Synthesis:**
   Browsers automatically synthesize "faux bold" or "faux italic" when an unavailable weight or style is requested. This causes blurry rasterization, clipped descenders, and unpredictable layout shifts. The design system must enforce:
   ```css
   * {
     font-synthesis: none;
   }
   ```
3. **Variable Fonts:** When variable font assets are available (e.g., `Inter-Variable.woff2`, `Cairo-Variable.woff2`), the system registers continuous weight ranges:
   ```css
   @font-face {
     font-family: 'Inter';
     src: url('/fonts/Inter-Variable.woff2') format('woff2-variations');
     font-weight: 100 900;
     font-display: swap;
   }
   ```
4. **Quantization Fallback Policy:** If an application requests weight `600` but the registered static font files only supply `400` and `700`, the system must explicitly map `600 -> 700` in the token configuration rather than allowing the browser to guess.

#### 4.4.3 Typography Controls and Scales
Typography controls must be governed by harmonized tokens across viewport contexts:

| Token | Size (px / rem) | Latin Line-Height | Arabic Line-Height | Tracking | Purpose |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `font-display` | 32px (2.0rem) | 40px (1.25) | 48px (1.50) | -0.02em | Hero titles, executive dashboard overviews |
| `font-h1` | 24px (1.5rem) | 32px (1.33) | 40px (1.66) | -0.015em | Main page titles (`<h1>`) |
| `font-h2` | 20px (1.25rem) | 28px (1.40) | 34px (1.70) | -0.01em | Major section headers (`<h2>`) |
| `font-h3` | 16px (1.0rem) | 24px (1.50) | 28px (1.75) | -0.005em | Subsections, card titles, table headers (`<h3>`) |
| `font-body-lg` | 16px (1.0rem) | 24px (1.50) | 28px (1.75) | 0.0em | Featured intro text, spacious modals |
| `font-body` | 14px (0.875rem)| 20px (1.43) | 24px (1.71) | 0.0em | Standard body copy, form inputs, table data |
| `font-body-sm` | 13px (0.8125rem)| 18px (1.38) | 22px (1.69) | 0.0em | Compact tables, sidebar items, data cards |
| `font-caption` | 12px (0.75rem) | 16px (1.33) | 20px (1.66) | +0.01em | Badges, helper texts, table column metadata |
| `font-micro` | 11px (0.6875rem)| 14px (1.27) | 18px (1.63) | +0.02em | Micro-labels, keyboard shortcut indicators |

#### 4.4.4 Font Asset Management, Licensing, and Performance
1. **Asset Delivery Format:** Modern **WOFF2** (`format('woff2')`) is mandatory for all web fonts. Legacy TTF/OTF formats are prohibited in production due to 3–4× file size overhead.
2. **Self-Hosting Mandate:** To comply with enterprise privacy governance, GDPR, and air-gapped security boundaries, font files must be self-hosted on the project origin or dedicated asset CDN. Loading font files from third-party tracking services (such as public Google Fonts endpoints) in secure production environments is prohibited.
3. **Cumulative Layout Shift (CLS) Mitigation:** To prevent visual jarring when web fonts load, define fallback metric overrides:
   ```css
   @font-face {
     font-family: 'Fallback-Inter';
     src: local('Arial');
     ascent-override: 90%;
     descent-override: 22%;
     line-gap-override: 0%;
     size-adjust: 107%;
   }
   ```
4. **Font-Display Strategy:** Use `font-display: swap;` for primary content and UI typography. Use `font-display: optional;` for non-critical, decorative fonts on high-latency mobile networks.
5. **Asset Preloading:** Preload at most 1–2 critical font files (the primary regular Latin and Arabic WOFF2 subsets) in the document `<head>`:
   ```html
   <link rel="preload" href="/fonts/cairo-regular.woff2" as="font" type="font/woff2" crossorigin>
   ```

---

### 4.5 Elevation and Shadows
Elevation establishes spatial z-axis hierarchy without harsh boundaries:
- `elevation-0`: Flat borders only (`border: 1px solid var(--border-subtle)`). Used for inner cards and table rows.
- `elevation-1`: Subtle card depth (`box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)`). Standard panels and cards.
- `elevation-2`: Raised state (`box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)`). Hovered cards, floating toolbars.
- `elevation-3`: Overlaid navigation (`box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)`). Dropdown menus, popovers.
- `elevation-4`: Modal surfaces (`box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.15), 0 8px 10px -6px rgb(0 0 0 / 0.1)`). Dialogs, drawers.

---

### 4.6 Multi-Domain Design Token Taxonomy

#### 4.6.1 Border and Radius Tokens
- `radius-none`: `0px` (Square, high-density enterprise data grids).
- `radius-sm`: `4px` (Tags, badges, micro-buttons, table cells).
- `radius-md`: `8px` (Standard buttons, inputs, card containers).
- `radius-lg`: `12px` (Panels, modal dialogs, flyout sheets).
- `radius-xl`: `16px` (Prominent hero containers, marketing cards).
- `radius-full`: `9999px` (Pill badges, rounded avatars, circular action buttons).

#### 4.6.2 Opacity Tokens
- `opacity-0`: `0` (Completely transparent).
- `opacity-10`: `0.1` (Subtle hover surface tint on colored backgrounds).
- `opacity-25`: `0.25` (Disabled surface tints).
- `opacity-50`: `0.5` (Standard disabled control state, placeholder icons).
- `opacity-75`: `0.75` (Modal backdrop dimming overlays).
- `opacity-100`: `1.0` (Fully opaque default).

#### 4.6.3 Motion and Transition Tokens
- **Durations:**
  - `motion-duration-fast`: `100ms` (Micro-interactions, button active states, icon rotations).
  - `motion-duration-normal`: `150ms` (Dropdowns, tooltips, tab indicator shifts).
  - `motion-duration-moderate`: `200ms` (Accordions, collapse animations).
  - `motion-duration-slow`: `300ms` (Modal dialog entrances, off-canvas drawers).
  - Invariant: Operational transitions must never exceed `300ms`.
- **Easing Bezier Curves:**
  - `motion-ease-standard`: `cubic-bezier(0.2, 0.0, 0, 1.0)` (General UI movements).
  - `motion-ease-entrance`: `cubic-bezier(0.0, 0.0, 0.2, 1.0)` (Elements entering viewport).
  - `motion-ease-exit`: `cubic-bezier(0.4, 0.0, 1.0, 1.0)` (Elements exiting viewport).

#### 4.6.4 Z-Index Tokens
Explicit stacking order prevents arbitrary, uncontrollable `z-index: 99999` wars:
- `z-base`: `0` (Default stacking context).
- `z-elevated`: `10` (Floating headers within cards, sticky table columns).
- `z-sticky`: `100` (Sticky table headers, fixed sub-navigation).
- `z-fixed`: `200` (Main application navbar, permanent sidebar).
- `z-backdrop`: `500` (Modal and drawer backdrop overlays).
- `z-drawer`: `600` (Off-canvas side sheets).
- `z-modal`: `700` (Modal dialog containers).
- `z-popover`: `800` (Dropdown menus, autocompletes, select menus).
- `z-toast`: `900` (Toast notifications, system snackbars).
- `z-tooltip`: `1000` (Accessible tooltips, highest floating context).

#### 4.6.5 Breakpoint Tokens
- `breakpoint-sm`: `640px` (Large mobile phones in landscape).
- `breakpoint-md`: `768px` (Tablets and compact notebooks).
- `breakpoint-lg`: `1024px` (Standard desktop monitors).
- `breakpoint-xl`: `1280px` (High-resolution desktop displays).
- `breakpoint-2xl`: `1536px` (Ultra-wide monitors and multi-window environments).

---

## 5. Visual Design Quality and Information Density

### 5.1 Density Standards
Enterprise software requires context-sensitive information density:
1. **Compact Density (High Efficiency):**
   - *Target:* Data grids, transaction logs, admin back-offices, financial audits.
   - *Metrics:* Row height 36–40px, font size 13–14px, cell padding 8–12px horizontal.
   - *Benefit:* Minimizes scrolling, displays maximum actionable rows above the fold.
2. **Comfortable Density (Standard Operational):**
   - *Target:* Settings forms, detail views, customer support tickets, standard CRUD.
   - *Metrics:* Input/button height 40–44px, row height 48–56px, container padding 16–24px.
3. **Spacious Density (Onboarding & Guidance):**
   - *Target:* Public portals, user onboarding wizards, account setup.
   - *Metrics:* Input/button height 48px, line height 1.6, container padding 32–48px.

### 5.2 Data-Heavy Tables and Grids
Tables are the operational backbone of enterprise systems. The following patterns are mandatory:
- **Header Stickiness:** Table headers must remain fixed (`position: sticky; top: 0`) when scrolling through datasets.
- **Alignment Rules:**
  - Left-align textual columns in LTR (Right-align in RTL).
  - Right-align numeric, financial, and date quantities in LTR (Left-align in RTL) with tabular figures (`font-variant-numeric: tabular-nums`) to ensure vertical alignment of decimals.
  - Center-align status badges, booleans, and single-icon actions.
- **Truncation and Tooltips:** Long text (e.g., URLs, descriptions) must truncate with an ellipsis (`truncate` / `text-overflow: ellipsis`) and provide the full value on hover/focus via an accessible tooltip.
- **Empty States:** When a table has zero records, render an intentional empty state containing:
  1. Contextual, non-distracting illustration or icon.
  2. Clear title explaining why the table is empty (e.g., "No Invoices Found").
  3. Actionable next step (e.g., "Create First Invoice" or "Clear Active Filters").

---

## 6. Interaction Design, State Machines, and Micro-Interactions

### 6.1 Interactive Element State Machine
Every interactive component (buttons, inputs, toggles, list items) must implement visual and programmatic representations of all fundamental states:

```
[ Default ] ──hover──> [ Hover ] ──press──> [ Active / Pressed ]
     │                                             │
   focus                                        trigger
     ▼                                             ▼
[ Focus-Visible ]                             [ Loading / Busy ]
     │                                             │
     │                                             ▼
  disable                                    [ Success / Error ]
     ▼                                             │
[ Disabled ]                                  [ Default ]
```

1. **Hover State:** Subtle surface lightening/darkening (e.g., 5–10% luminance shift) or border transition within 150ms. Never trigger layout shift or resize on hover.
2. **Focus-Visible State:** High-contrast 2px or 3px ring offset by 2px (`outline: 2px solid var(--primary); outline-offset: 2px`). Visible only on keyboard interaction or assistive focus, suppressing focus rings on mouse clicks where supported (`:focus-visible`).
3. **Active/Pressed State:** Tactile confirmation (e.g., 1px downward translation or subtle scale `scale(0.98)`).
4. **Disabled State:** Reduced opacity (`opacity: 0.5` or `0.6`), `cursor: not-allowed`, programmatic pointer-events suppression (`pointer-events: none` on click, preserving accessible explanations).
5. **Loading / Busy State:** Replace icon or label with an animated spinner or indeterminate progress bar. Maintain fixed button width to prevent layout jump. Set `aria-busy="true"` and disable repeated submissions.

### 6.2 Animation and Motion Standards
Animations must serve a clear cognitive purpose: orienting the user, showing causality, or smoothing state transitions. Decorative, sluggish animations that impede user efficiency are strictly prohibited.
- **Durations:**
  - Micro-interactions (hover, toggle, focus): `100ms - 150ms`.
  - Component transitions (dropdowns, tooltips, accordions): `150ms - 200ms`.
  - Page-level or modal entrances: `200ms - 300ms`.
  - Never exceed `300ms` for operational interface transitions.
- **Easing:** Use standard ease-out curves for entrances (`cubic-bezier(0, 0, 0.2, 1)`) and ease-in curves for exits (`cubic-bezier(0.4, 0, 1, 1)`).
- **Reduced Motion Compliance:**
  All CSS animations and transitions must respect operating-system reduced-motion settings:
  ```css
  @media (prefers-reduced-motion: reduce) {
    *, ::before, ::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
  ```

---

## 7. Navigation Architecture and Information Architecture

### 7.1 Wayfinding and Navigation Systems
1. **Global Hierarchy:**
   - **Level 1 (Application Shell):** Primary sidebar or top bar representing top-level domains (e.g., "Dashboard", "Orders", "Inventory", "Settings").
   - **Level 2 (Section Navigation):** Sub-navigation, horizontal tabs, or nested sidebars within a domain.
   - **Level 3 (Contextual Navigation):** Page-level tabs, filters, and anchor links.
2. **Breadcrumb Trails:**
   - Mandatory on all hierarchical detail and edit views (`Home > Invoices > INV-2026-001 > Edit`).
   - The final item represents the current page and must be non-clickable with `aria-current="page"`.
   - Never use breadcrumbs as a substitute for primary navigation.
3. **Sidebar Interaction Patterns:**
   - Collapsible sidebars must preserve recognizable iconography when collapsed to mini-rail mode (64px width).
   - Expanding the sidebar must not cause sudden horizontal layout breakage of content tables; use fluid flex/grid containers.
4. **Search and Filtering UX:**
   - Global search input must indicate keyboard shortcut (`Ctrl+K` or `Cmd+K` / `Alt+S`).
   - Search inputs must offer an immediate clear button (`✕`) when text is present.
   - Filter bars must visually summarize active filter counts and provide a single-click "Clear all filters" mechanism.

---

## 8. Complete UX Workflows and State Management

Every operational screen and interactive workflow must formally account for and implement the **12-State Journey**:

| State | Definition & UX Requirement | Implementation Requirement |
| :--- | :--- | :--- |
| **1. Initial** | Default unmodified state prior to user input. | Clear default values or clean empty placeholders. |
| **2. Loading** | Asynchronous fetch or data hydration in progress. | Skeleton loaders mirroring final content geometry. |
| **3. Empty** | Dataset query returned zero items. | Intentional empty state graphic, explanation, and CTA. |
| **4. Populated** | Normal display of hydrated data. | Complete table, card grid, or form layout. |
| **5. Active / Editing** | User is actively inputting or altering state. | Visible dirty indicator, focus rings on active field. |
| **6. Submitting** | Network mutation dispatched to backend. | Submit button shows spinner, inputs locked from re-edit. |
| **7. Success** | Backend mutation completed successfully. | Non-blocking toast notification, optimistic UI reconciled. |
| **8. Validation Error** | Input failed client or server validation rules. | Inline field errors, field focused, error summary shown. |
| **9. Server Error** | 500-level error or unhandled backend exception. | User-friendly error message with reference code & retry CTA. |
| **10. Network Offline** | Device lost network connectivity during workflow. | Offline banner, queue mutations locally if supported. |
| **11. Read-Only** | User has view permissions but lacks edit authorization. | Form controls disabled, helper badge: "Read-only mode". |
| **12. Unsaved Changes** | User attempts to navigate away with dirty form state. | Browser `beforeunload` or router dialog confirmation. |

---

## 9. Forms and Data Entry Experience

### 9.1 Form Structure and Ergonomics
- **Single-Column Preference:** Lay out form fields in a single vertical column for standard inputs to ensure a clean, natural vertical eye path. Multi-column layouts are permitted only for logically related, tightly coupled pairs (e.g., "City" and "Postal Code", or "First Name" and "Last Name").
- **Explicit Field Labelling:**
  - Form inputs must **always** have visible, permanent labels placed above the input field.
  - **Placeholder Anti-Pattern:** Never use placeholder text as a replacement for a label. Placeholders disappear upon typing, reducing recall and causing cognitive disorientation. Use placeholders only to illustrate format examples (e.g., `e.g. user@company.com`).
- **Required vs. Optional Convention:** Indicate required fields with an asterisk `*` (with `aria-hidden="true"` and an explanatory note at the top of the form) or explicitly label `(Optional)` on optional fields when the vast majority of fields are required.
- **Validation Timing Rules:**
  - **Do NOT validate on every keystroke:** Validating while the user is actively typing produces high cognitive anxiety (e.g., shouting "Invalid email!" when the user has only typed `user@`).
  - **Validate on Blur:** Trigger validation when the user tabs away or moves focus out of the field.
  - **Re-validate on Change after Error:** Once a field has displayed an error, re-validate immediately on keystroke/change so the error clears as soon as the user corrects it.
  - **Full Form Validation on Submit:** Validate all fields upon submit button click. If errors exist, automatically scroll to and focus the first invalid field.

---

## 10. Responsive, Fluid, and Adaptive Design

### 10.1 Responsive Breakpoint Matrix
Universal Core defines standard content-driven breakpoints aligned with modern viewport distributions:

| Breakpoint | Minimum Width | Target Devices | Layout Behavior |
| :--- | :--- | :--- | :--- |
| `sm` | `640px` | Large smartphones, landscape mobiles | Single-column stacks, full-width inputs, bottom sheets |
| `md` | `768px` | Tablets, foldables | 2-column forms, collapsible off-canvas sidebars |
| `lg` | `1024px` | Laptops, desktop monitors | Full permanent sidebars, multi-column dashboard cards |
| `xl` | `1280px` | Standard desktop screens | 12-column grid systems, multi-pane master-detail views |
| `2xl` | `1536px` | Ultra-wide monitors, multi-window setups | Max-width content boundaries (`max-w-7xl`), expanded data tables |

### 10.2 Fluid Typography and Layouts
Avoid fixed-pixel layouts that break on minor viewport variations or when browser zoom is applied. Use CSS `clamp()`, `min()`, `max()`, and modern CSS Grid:
```css
/* Fluid container with dynamic padding */
.app-container {
  width: 100%;
  max-width: 1440px;
  margin-inline: auto;
  padding-inline: clamp(1rem, 2.5vw, 2.5rem);
}

/* Fluid responsive typography */
.text-fluid-h1 {
  font-size: clamp(1.5rem, 1.2rem + 1.5vw, 2.25rem);
  line-height: 1.25;
}
```

### 10.3 Responsive Table Strategies
On mobile screens (`< 768px`), standard wide data tables must gracefully adapt via one of three approved strategies:
1. **Card Transformation (Recommended for records):** Transform each table row into a self-contained card showing labeled key-value pairs.
2. **Horizontal Scroll Container with Sticky Column:** Wrap the table in `overflow-x: auto; -webkit-overflow-scrolling: touch;`, pinning the identifier column (e.g., Customer Name or ID) via `position: sticky; inset-inline-start: 0`.
3. **Column Priority Toggle:** Provide a dropdown menu enabling users to selectively toggle visible columns based on their current task.

---

## 11. Arabic, English, and Bidirectional (RTL/LTR) Engineering

Internationalization is a mandatory, first-class requirement. Universal Core applications must render flawlessly in both Left-to-Right (LTR) and Right-to-Left (RTL) locales.

### 11.1 CSS Logical Properties Invariant
Engineers must **never** use physical directional properties (`left`, `right`, `margin-left`, `margin-right`, `padding-left`, `padding-right`, `border-left`, `border-right`, `text-align: left`) in component styling. All directional styling must use standard **CSS Logical Properties**:

| Deprecated Physical Property | Mandatory CSS Logical Property | Tailwind v3.4+ / v4.x Utility |
| :--- | :--- | :--- |
| `margin-left: 1rem;` | `margin-inline-start: 1rem;` | `ms-4` |
| `margin-right: 1rem;` | `margin-inline-end: 1rem;` | `me-4` |
| `padding-left: 1rem;` | `padding-inline-start: 1rem;` | `ps-4` |
| `padding-right: 1rem;` | `padding-inline-end: 1rem;` | `pe-4` |
| `left: 0;` | `inset-inline-start: 0;` | `start-0` |
| `right: 0;` | `inset-inline-end: 0;` | `end-0` |
| `text-align: left;` | `text-align: start;` | `text-start` |
| `text-align: right;` | `text-align: end;` | `text-end` |
| `border-left: 1px solid;` | `border-inline-start: 1px solid;` | `border-s` |
| `border-right: 1px solid;` | `border-inline-end: 1px solid;` | `border-e` |
| `border-top-left-radius: 8px;` | `border-start-start-radius: 8px;` | `rounded-ss-lg` |
| `border-top-right-radius: 8px;` | `border-start-end-radius: 8px;` | `rounded-se-lg` |

### 11.2 Typography and Font Presets
Typography in Arabic requires dedicated font metrics, line-height scaling, and vertical breathing room. Arabic characters have vertical ascenders and descenders that collide if rendered with Latin line-heights ($1.2 - 1.4$). In Arabic, line-heights must be expanded to at least $1.5 - 1.7$.

Universal Core establishes the following standard font presets:
- **Arabic Presets:** `Cairo` (modern, legible, geometric) or `Tajawal` (clean, rounded, contemporary).
- **English / Latin Preset:** `Inter` (neutral, highly readable, variable font).
- **Data & Numerical Preset:** `Alexandria` (optimized for dense numbers, financial figures, and technical tables).

```css
/* Core Bidirectional Font Configuration */
:root {
  --font-sans-en: 'Inter', system-ui, -apple-system, sans-serif;
  --font-sans-ar: 'Cairo', 'Tajawal', system-ui, -apple-system, sans-serif;
  --font-data: 'Alexandria', monospace;
}

[dir="ltr"] {
  font-family: var(--font-sans-en);
}

[dir="rtl"] {
  font-family: var(--font-sans-ar);
  line-height: 1.6; /* Expanded line-height for Arabic script */
}
```

### 11.3 Icon Mirroring and Directional Controls
Not all icons should flip in RTL! Applying an indiscriminate `transform: scaleX(-1)` across all icons breaks semantics. Follow these strict rules:

| Category | Flip in RTL? | Examples | Rationale |
| :--- | :---: | :--- | :--- |
| **Directional & Progress** | **YES** | Chevrons (`ChevronRight`), Back/Forward arrows, Pagination, Breadcrumb separators, Undo/Redo | Progress moves from Right to Left in RTL. "Forward" points left. |
| **Symmetrical & Non-Directional** | **NO** | Search (`MagnifyingGlass`), User, Trash, Settings, Checkmark, Cross/Close, Star, Heart, Calendar | Symmetrical objects or objects with no inherent directional motion. |
| **Media Playback Controls** | **NO** | Play (`▶`), Fast Forward (`⏩`), Rewind (`⏪`), Pause, Volume | Media controls represent physical tape transport direction, universally left-to-right. |
| **Clocks & Time** | **NO** | Clock, Stopwatch, Timer | Physical clocks rotate clockwise universally in both RTL and LTR regions. |
| **Brand Logos** | **NO** | Company logos, payment card icons (Visa, Mastercard) | Brand marks maintain legal identity and must never be mirrored. |

### 11.4 Bidirectional Isolation
When rendering mixed content (e.g., an English email address, phone number, or IBAN inside an Arabic sentence), wrap the untrusted or foreign text in `<bdi>` (Bidirectional Isolation) to prevent layout distortion:
```html
<p class="text-sm text-secondary">
  <span>تم إرسال رمز التحقق إلى: </span>
  <bdi class="font-mono text-primary">+966 50 123 4567</bdi>
</p>
```

### 11.5 Bidirectional Customization & Typography Scaling
When customizing visual themes and typography stacks, the bidirectional architecture enforces:
1. **Dual-Script Font Tokens:** Themes must configure Latin font tokens (`--font-family-en`) and Arabic font tokens (`--font-family-ar`) independently. Font fallbacks must pair script-appropriate glyph sets.
2. **Dynamic Script Scaling:** Arabic script exhibits a smaller perceived x-height than Latin at identical font sizes. In RTL mode, base font sizes may be compensated upwards by 0.5–1px, with line-height ratios automatically elevated to $\ge 1.6$.
3. **Direction-Sensitive Component Tokens:** All custom component tokens (padding, border offsets, flyout alignments) must resolve to CSS logical properties (`*-inline-start`, `*-inline-end`).
4. **Live Bidirectional Preview:** Any customization interface must support instant one-click toggling between `dir="ltr"` and `dir="rtl"` to evaluate layout integrity across both directions.

---

## 12. Enterprise Theme Management, Design Presets, and Visual Identity Customization System

This section defines the operational engineering standards for specifying, architecting, implementing, validating, and governing visual identity customization systems.

> [!IMPORTANT]
> **Engineering Standard vs. Application Implementation Boundary:**
> This skill defines the engineering requirements, token contracts, and operational validation workflows for visual customization. It does not construct or bundle an active visual customization dashboard or runtime engine inside Universal Core itself. Consuming projects must implement the actual runtime engines, admin interfaces, and persistence stores when their specific approved requirements call for them.

### 12.1 Semantic Theme Decoupling & Light/Dark Mode Baseline
Dark mode must **never** be generated through CSS inversion filters (`filter: invert(1)`). It requires dedicated semantic token mapping:
- Avoid pure black (`#000000`) for canvas backgrounds; use deep slate or neutral gray (`#0f172a` or `#121212`) to prevent high-contrast ocular fatigue.
- Elevate dark surfaces by lightening them slightly as they approach the user (Elevation 0: `#0f172a`, Elevation 1: `#1e293b`, Elevation 2: `#334155`).
- Ensure all text and icons meet WCAG 2.2 AA contrast ratios ($4.5:1$ for body text) across both themes.

```css
/* Token Definition for Light and Dark Modes */
:root {
  --color-surface-canvas: #f8fafc;
  --color-surface-card: #ffffff;
  --color-text-primary: #0f172a;
  --color-text-secondary: #475569;
  --color-border-subtle: #e2e8f0;
}

[data-theme="dark"],
.dark {
  --color-surface-canvas: #0f172a;
  --color-surface-card: #1e293b;
  --color-text-primary: #f8fafc;
  --color-text-secondary: #94a3b8;
  --color-border-subtle: #334155;
}
```

---

### 12.2 Multi-Theme Architecture & Identity Lifecycle

#### 12.2.1 Multi-Theme Support and Theme Variants
An enterprise theme management system must support:
1. **Multiple Independently Configurable Themes:** Beyond default light and dark modes, systems must support specialized themes (e.g., Enterprise Corporate, Slate Midnight, Emerald Finance, High-Contrast Accessible, Tenant Custom).
2. **Theme Variants:** Each theme may supply paired variants:
   - `light`: Baseline daytime operational appearance.
   - `dark`: Low-light optimized luminance mapping.
   - `high-contrast`: WCAG AAA compliant $7:1$ high-contrast accessibility variant.
   - `compact`: Tightened spacing and font density variant for operational specialists.

#### 12.2.2 Theme Metadata and Identity Specification
Every registered theme must include an immutable metadata header adhering to the theme manifest contract:

```json
{
  "$schema": "https://universal-core.agent/schemas/theme-manifest.json",
  "id": "theme-corporate-slate",
  "name": "Corporate Slate",
  "version": "1.2.0",
  "author": "Enterprise Architecture Team",
  "description": "Authoritative corporate theme with cool slate neutrals and sapphire primary accents.",
  "is_builtin": true,
  "is_active": false,
  "supported_directions": ["ltr", "rtl"],
  "variants": ["light", "dark", "high-contrast"],
  "parent_theme": null,
  "tokens": {
    "semantic": {
      "color": {
        "primary": "{primitive.color.sapphire.600}",
        "surface-canvas": "{primitive.color.slate.50}",
        "surface-card": "{primitive.color.neutral.0}"
      }
    }
  }
}
```

#### 12.2.3 Theme Management Lifecycle Operations
Customization systems must implement the full theme lifecycle:
1. **Create:** Instantiate a new theme manifest inheriting from a verified parent theme or default baseline.
2. **Edit:** Modify semantic mappings, palette assignments, or typography references in draft mode.
3. **Duplicate:** Clone an existing verified theme into a new customizable tenant or project theme.
4. **Save / Persist:** Commit draft theme state to permanent storage with cryptographic integrity validation.
5. **Activate:** Set the theme as the active styling context globally, per tenant, or per user session.
6. **Delete:** Remove unassigned custom themes. System built-in themes and actively assigned themes **MUST NOT** be deleted.

#### 12.2.4 Runtime Theme Switching Without Page Reload
Theme switching must occur dynamically at runtime without triggering a full browser refresh:
- **Root Attribute Toggle:** Update `data-theme` or `data-theme-variant` on the document root element (`<html data-theme="corporate-slate" data-mode="dark">`).
- **CSS Custom Properties Swap:** When switching custom themes with dynamic values, the runtime updates scoped CSS custom properties on `:root` or a scoped container.
- **Event Dispatch:** Dispatch a standard DOM event (`window.dispatchEvent(new CustomEvent('theme:changed', { detail: { themeId, mode } }))`) enabling charts, canvases, and WebGL components to re-render.

#### 12.2.5 Theme Persistence and Hydration
Theme selections must be persisted across sessions without causing a Flash of Unstyled Content (FOUC):
1. **Client Storage:** Persist active theme ID and mode preference in `localStorage`.
2. **SSR / Server Cookie Sync:** Write the active theme identifier to a lightweight HTTP cookie (`theme_preference=corporate-slate; path=/; SameSite=Lax`). Server-rendered Blade or Inertia templates read this cookie and inject `data-theme` directly into `<html>` before first paint, eliminating visual flicker.
3. **Database User Preference:** Authenticated user profiles synchronize active theme selections to their backend preferences table.

---

### 12.3 Conceptual Distinctions: Theme vs. Palette vs. Design Preset vs. User Preference

To eliminate architectural conflation, engineers must adhere to the standard distinctions:

| Concept | Scope & Definition | Mutability & Ownership | Example |
| :--- | :--- | :--- | :--- |
| **Color Palette** | A collection of raw, harmonized primitive color shades without intrinsic semantic purpose. | Defined by design system; extended by authorized admins. | `slate: [50..950]`, `sapphire: [50..950]`. |
| **Semantic Token** | A contextual purpose-driven pointer linking a UI role to a specific palette shade. | Configured by themes and design system contracts. | `--color-primary: var(--color-sapphire-600)`. |
| **Theme** | A complete, coordinated mapping of semantic tokens across color, surface, and border roles. | Configured per project, organization, or tenant. | `Corporate Slate Theme` (Light, Dark variants). |
| **Design Preset** | A comprehensive bundled visual identity package specifying palette, typography, radius, density, and shadows. | Reusable off-the-shelf starter profile; can be modified. | `Modern Vibrant`, `Compact Fintech`, `Government Strict`. |
| **User Preference** | Ergonomic runtime selection made by an individual end user within boundaries allowed by active theme. | Owned by end user; persisted in local/user profile. | `Mode: Dark`, `Density: Compact`, `Reduced Motion: True`. |

---

### 12.4 Operational Architecture and End-to-End Customization Pipeline

#### 12.4.1 Complete Structural Hierarchy
The complete visual identity customization system resolves deterministically through six cascading stages:

```
[ 1. Color Palettes & Font Assets ]
         │ (Provides raw primitive shades & font weights)
         ▼
[ 2. Semantic Design Tokens ]
         │ (Maps primitives to intent: primary, surface, text-primary, font-sans)
         ▼
[ 3. Themes (Light / Dark / High-Contrast) ]
         │ (Bundles coordinated semantic token assignments & variant mappings)
         ▼
[ 4. Design Presets (Visual Identities) ]
         │ (Combines theme, typography stack, density, radius, and elevation)
         ▼
[ 5. Component Design Tokens ]
         │ (Binds semantic tokens to component slots: btn-bg, table-header-bg)
         ▼
[ 6. Rendered User Interface ]
           (Accessible, responsive, bidirectional, 12-state compliant UI)
```

#### 12.4.2 The 9-Stage Customization Lifecycle
Any visual customization capability must adhere to the 9-stage engineering lifecycle:

```
Define ──> Register ──> Validate ──> Preview ──> Approve ──> Persist ──> Apply ──> Monitor ──> Migrate
```

1. **Define:** Specify token keys, default fallback values, and metadata in standard DTCG JSON format.
2. **Register:** Register token definitions and manifests into the project's centralized token registry.
3. **Validate:** Verify JSON schema conformance, check for circular token references, detect missing shades, and evaluate WCAG contrast ratios.
4. **Preview:** Render draft changes in an isolated live preview sandbox without altering active user sessions.
5. **Approve:** Verify changes against RBAC authorization gates and governance policies.
6. **Persist:** Atomically store the approved theme or preset in persistent storage with cryptographic checksums.
7. **Apply:** Compile and deploy updated CSS custom properties or theme attributes to production runtimes.
8. **Monitor:** Continuously audit visual regression, performance metrics (CLS, FCP), and accessibility compliance.
9. **Migrate:** Safely transform deprecated tokens to new token contracts when design system versions advance.

---

### 12.5 Design Presets and Brand Identity Packages

#### 12.5.1 Preset Structure and Properties
A Design Preset is an off-the-shelf, reusable visual identity package that bundles:
- **Palette Assignment:** Primary, secondary, neutral, and accent color families.
- **Typography Configuration:** Latin and Arabic font families, weight distributions, and heading scale ratios.
- **Density Setting:** `compact`, `comfortable`, or `spacious`.
- **Spacing Scale:** Standard 4px/8px modular scale or tightened operational scale.
- **Border Curvature:** Radius preset (`square`: 0px, `subtle`: 4px, `rounded`: 8px, `pill`: 9999px).
- **Shadows and Elevation:** Depth preset (`flat`, `subtle`, `prominent`).
- **Light/Dark Preference:** Default mode behavior and contrast tuning.

#### 12.5.2 Built-in Presets vs. Custom Presets
1. **Built-in Presets:** Immutable, pre-validated profiles provided by the design system:
   - *Enterprise Neutral:* Standard cool slate neutrals, blue-600 primary, comfortable density, 8px radius.
   - *Fintech Compact:* High-density tables, 4px radius, Alexandria data typography, subtle elevation.
   - *Creative Modern:* Vibrant sapphire/emerald accents, spacious padding, 12px radius, Inter/Cairo typography.
   - *High-Contrast Accessible:* Black/white high-contrast surfaces, 3px focus rings, WCAG AAA 7:1 contrast.
2. **Custom Presets:** Organizations or tenants can clone any built-in preset and fine-tune specific tokens. Presets serve as starting baselines, not rigid constraints.
3. **Preset Compatibility & Migration:** When the underlying design system releases a new token domain (e.g., new z-index or motion tokens), presets must automatically inherit default fallback values through schema migration transformers.

---

### 12.6 Component Appearance Customization Contracts

To prevent individual components from introducing rogue, uncontrollable styling values, all UI components must customize exclusively through defined token contracts.

The following matrix specifies the customization contracts across all 18 standard component categories:

| Component Category | Customizable Properties via Shared Tokens | Component-Specific Tokens | Strict Invariants & Accessibility Rules |
| :--- | :--- | :--- | :--- |
| **1. Buttons & Button Groups** | Background, text color, border color, border radius, elevation, font weight, focus ring color. | `--btn-padding-x`, `--btn-padding-y`, `--btn-icon-gap`. | Minimum 44×44px touch target; visible `:focus-visible` ring; spinner retains button width in loading state. |
| **2. Inputs & Textareas** | Surface color, border color, border radius, text color, placeholder color, focus border color. | `--input-padding-x`, `--input-padding-y`, `--input-height`. | Permanent label above field; never placeholder-only; red border and helper error on validation failure. |
| **3. Selects & Dropdowns** | Menu surface, item hover color, border color, radius, elevation. | `--select-item-padding`, `--select-menu-max-height`. | Keyboard navigable (Up/Down/Enter/Escape); `aria-expanded` and `aria-haspopup` bindings mandatory. |
| **4. Checkboxes & Radios** | Checked surface color, checkmark color, border color, focus ring, radius (radios: full). | `--checkbox-size`, `--radio-size`, `--check-indicator-size`. | Touch target $\ge 44\times 44$px via inclusive hit padding; high-contrast checked indicator. |
| **5. Switches / Toggles** | Track active/inactive background, thumb surface color, focus ring. | `--switch-track-width`, `--switch-track-height`, `--switch-thumb-size`. | `role="switch"` and `aria-checked`; smooth thumb translation without horizontal layout shift. |
| **6. Tables & Data Grids** | Header background, header text color, border subtle, row hover background, striped row surface. | `--table-cell-padding-x`, `--table-cell-padding-y`, `--table-header-height`. | Sticky headers (`top: 0`); numeric data aligned to end with `tabular-nums`; text truncated with accessible tooltips. |
| **7. Cards & Panels** | Card background, card border, card elevation, card radius, header divider. | `--card-padding`, `--card-header-gap`. | Maintain visual containment; hover elevation shifts must not cause cumulative layout shift (CLS). |
| **8. Navigation & Sidebars** | Sidebar background, nav item active background, nav item hover, active indicator. | `--sidebar-width`, `--sidebar-collapsed-width`, `--nav-item-height`. | Visible active state; collapsed mode retains recognizable icons; expand/collapse fluidly without breaking grids. |
| **9. Tabs** | Tab active text color, tab inactive text color, indicator bar color, focus ring. | `--tab-gap`, `--tab-padding-y`, `--tab-indicator-thickness`. | APG Tab pattern: Arrow key navigation; `aria-selected` and `aria-controls` bindings required. |
| **10. Modals & Dialogs** | Modal surface, backdrop color/opacity, modal radius, modal elevation, title font. | `--modal-max-width`, `--modal-padding`, `--modal-backdrop-blur`. | Focus trapped within modal; Escape key dismisses; focus restored to trigger upon close; `role="dialog"`. |
| **11. Alerts & Notifications** | Intent surfaces (success, warning, danger, info), intent text, intent border, radius. | `--alert-padding`, `--alert-icon-size`. | High-contrast status icons accompany colored backgrounds; screen reader live region (`aria-live="polite"`). |
| **12. Badges & Status Indicators** | Intent background, intent text color, border radius (usually full), font size (`font-caption`). | `--badge-padding-x`, `--badge-padding-y`, `--badge-dot-size`. | Status conveyed through text/icon, not color alone; text meets $4.5:1$ contrast ratio. |
| **13. Tooltips** | Tooltip surface, tooltip text color, tooltip radius, tooltip elevation. | `--tooltip-max-width`, `--tooltip-padding-x`, `--tooltip-padding-y`. | `role="tooltip"`; triggered by both hover and focus; does not obscure active element; dismissed on Escape. |
| **14. Pagination** | Page button background, active page background, active page text, border. | `--pagination-button-size`, `--pagination-gap`. | Previous/Next directional buttons flip correctly in RTL; active page indicates `aria-current="page"`. |
| **15. Menus & Flyouts** | Flyout surface, flyout elevation, item hover background, divider color. | `--menu-min-width`, `--menu-item-height`, `--menu-padding`. | `role="menu"` with `role="menuitem"`; arrow key navigation; closes on outside click and Escape. |
| **16. Form Layouts** | Form gutter spacing, fieldset border, section title font, error summary surface. | `--form-gap-y`, `--form-gap-x`, `--form-label-margin-bottom`. | Single-column preference; logical grouping; field errors inline directly below invalid inputs. |
| **17. Empty States** | Graphic color/tint, title font, description color, CTA button variant. | `--empty-state-padding-y`, `--empty-state-max-width`. | Clear explanation; non-distracting illustration; explicit actionable next step button. |
| **18. Loading States** | Skeleton background, skeleton shimmer gradient, spinner color. | `--skeleton-border-radius`, `--spinner-size`. | Skeletons match final content geometry; spinner maintains container dimensions; `aria-busy="true"`. |

---

### 12.7 Live Preview and Safe Application Workflow

Whenever a project requires an interactive visual customization interface, it must follow an operational, non-destructive preview and publication workflow.

#### 12.7.1 The 7-Step Preview and Publication Workflow
1. **Access Interface:** Authorized administrator opens the visual customization workspace.
2. **Draft Modification:** Administrator adjusts palettes, typography, themes, presets, or component tokens.
3. **Sandbox Application:** Changes are dynamically applied exclusively to an isolated preview container (e.g., `.theme-preview-sandbox` or an isolated `<iframe>`).
4. **Full Viewport & Bidirectional Reflection:** The preview renders a representative design showcase across:
   - Multiple responsive breakpoints (Mobile 360px, Tablet 768px, Desktop 1280px).
   - Both text directions (LTR English and RTL Arabic).
   - Core interactive states (Hover, Focus, Disabled, Loading, Error).
5. **Real-Time Automated Validation:** Automated validation engines evaluate the draft tokens:
   - Evaluates text-to-surface contrast against WCAG 2.2 AA ($4.5:1$).
   - Flags missing shades or broken token references.
   - Blocks invalid CSS property injections.
6. **User Review & Rollback:** The administrator can test interaction states, compare with the current production baseline, or click **"Discard Changes"** to restore previous settings.
7. **Safe Atomic Activation:** Upon clicking **"Publish Theme"**, the system persists the configuration, invalidates asset caches, and applies the approved tokens atomically.

#### 12.7.2 Technical Safeguards in Live Preview
- **Production Session Isolation:** Draft customizations must be scoped strictly to the current administrator session (via local draft state or temporary session tokens). Draft changes must **never** leak to end users or active production sessions.
- **Unsaved Changes Guard:** If the administrator attempts to navigate away with uncommitted changes, trigger an unsaved changes confirmation dialog.
- **Rollback Snapshotting:** Prior to publishing any theme change, the system automatically takes an immutable snapshot of the active configuration, enabling instant one-click rollback if regressions emerge.
- **Atomic Cache Invalidation:** Published token bundles must include content hashes (e.g., `theme-corporate.v4a2b9.css`) to prevent browsers from caching stale styling rules.

---

### 12.8 Customization Governance, Security, and Multi-Tenancy

Enterprise visual customization interfaces are high-value administrative surfaces requiring strict security boundaries.

#### 12.8.1 Role-Based Access Control (RBAC)
- **Super Administrator (System-Wide):** Full authority to create, edit, publish, and delete global themes, design presets, and core primitive mappings.
- **Tenant Administrator (Organization Scope):** Authority to customize tenant-scoped themes within organizational boundaries. Cannot mutate global system defaults.
- **End User (Personal Ergonomics):** Permitted only to toggle user preferences: Light/Dark mode, Compact/Comfortable density, and OS Reduced Motion synchronization.
- **Independent Auditor (Read-Only):** Authority to inspect theme configurations, audit logs, and contrast reports without modification rights.

#### 12.8.2 Security Rules and Injection Prevention
> [!CAUTION]
> **Zero Untrusted CSS / Markup Injection Invariant:**
> Untrusted customization inputs must never be written directly into raw CSS stylesheets or rendered unescaped in HTML. Customization fields must be strictly typed data values (e.g., validated hex colors, numeric pixel/rem dimensions, enumerated font family keys).

1. **Strict Sanitization:**
   - Prohibit raw CSS expressions, `@import` rules, `behavior:`, and `expression()`.
   - Disallow external `url(...)` declarations to prevent data exfiltration and tracking pixels.
   - Reject any input containing HTML tags (`<script>`, `<iframe>`), quotes, or unbalanced curly braces (`{`, `}`).
2. **Font Asset Upload Security:**
   - Uploaded font files must be validated for MIME type (`font/woff2`, `font/woff`).
   - Validate font file magic bytes (WOFF2 signature: `0x774F4632`).
   - Enforce a strict file size ceiling (maximum 2MB per font asset).
   - Serve uploaded fonts with restrictive HTTP response headers:
     `Content-Type: font/woff2; Access-Control-Allow-Origin: <project-origin>; X-Content-Type-Options: nosniff`.
3. **Safe Import / Export:**
   - Imported JSON theme packages must be validated against the formal theme JSON schema.
   - Unrecognized properties must be stripped automatically.
   - Maximum import payload size: 512KB.
4. **Configuration Integrity and Auditability:**
   - Every published theme version must generate a SHA-256 cryptographic checksum.
   - Audit logging must record: Timestamp, User ID, Theme ID, Modified Token Keys, Old Values, and New Values.

#### 12.8.3 Multi-Tenant Theme Isolation
In multi-tenant SaaS environments:
- Tenant themes must be scoped using tenant identifiers (e.g., `<body data-tenant="tenant_894" data-theme="brand">`).
- Tenant CSS variables must reside in isolated CSS scopes or dynamically injected stylesheets scoped strictly to the tenant's authenticated session.
- Under no circumstances may Tenant A view, modify, or inherit unapproved customizations from Tenant B.

---

### 12.9 Extensibility Contracts and Framework Adapters

The design customization architecture is open for extension and closed for core modification. Projects can introduce new visual assets without redesigning the core token system.

#### 12.9.1 Pluggable Token Transformers
The centralized token source of truth (DTCG JSON) compiles into platform-specific artifacts via automated build transformers:
1. **CSS Custom Properties:** Generates `:root` and `[data-theme="..."]` stylesheets.
2. **Tailwind CSS Plugin:** Generates dynamic utilities (`bg-surface-card`, `text-text-primary`, `rounded-radius-md`).
3. **TypeScript Definitions:** Generates strict string literal union types (`type ThemeColor = 'primary' | 'surface-card' | ...;`).
4. **SCSS / PostCSS Maps:** Generates preprocessor variables where legacy builds require them.

#### 12.9.2 Framework Adapter Specifications

##### A. Laravel Blade and Tailwind CSS Adapter
In Laravel Blade, design tokens integrate with Tailwind CSS variables and Blade layout directives:
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="{{ $activeTheme->slug ?? 'default' }}"
      class="{{ $userModePreference ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Preload primary font assets --}}
    <link rel="preload" href="/fonts/cairo-variable.woff2" as="font" type="font/woff2" crossorigin>
    
    {{-- Dynamic Tenant/Project Token Overrides --}}
    @if(isset($themeCustomCss))
        <style id="custom-theme-tokens">{!! $themeCustomCss !!}</style>
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-canvas text-text-primary antialiased min-h-screen">
    {{ $slot }}
</body>
</html>
```

##### B. Filament 5.x and Livewire 4.x Adapter
In Filament panels, register custom themes and color palettes using the Panel Provider:
```php
<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->font('Cairo')
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
```

##### C. React 18+ and TypeScript Adapter
In React applications, implement design tokens using a typed Theme Context and Provider:
```tsx
// src/context/ThemeContext.tsx
import React, { createContext, useContext, useEffect, useState } from 'react';

export type ThemeMode = 'light' | 'dark' | 'system';

export interface ThemeContextValue {
  themeId: string;
  mode: ThemeMode;
  setThemeId: (id: string) => void;
  setMode: (mode: ThemeMode) => void;
}

const ThemeContext = createContext<ThemeContextValue | undefined>(undefined);

export const ThemeProvider: React.FC<{
  children: React.ReactNode;
  defaultTheme?: string;
}> = ({ children, defaultTheme = 'default' }) => {
  const [themeId, setThemeId] = useState<string>(defaultTheme);
  const [mode, setMode] = useState<ThemeMode>('system');

  useEffect(() => {
    const root = document.documentElement;
    root.setAttribute('data-theme', themeId);
    
    const isDark = mode === 'dark' || 
      (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    root.classList.toggle('dark', isDark);
  }, [themeId, mode]);

  return (
    <ThemeContext.Provider value={{ themeId, mode, setThemeId, setMode }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = (): ThemeContextValue => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
```

##### D. Vue 3 and Inertia.js Adapter
In Vue 3 with Inertia, maintain theme state in a reactive store synced with Inertia page props:
```typescript
// resources/js/stores/useThemeStore.ts
import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
  const activeTheme = ref<string>('default');
  const isDark = ref<boolean>(false);

  function applyTheme() {
    const root = document.documentElement;
    root.setAttribute('data-theme', activeTheme.value);
    root.classList.toggle('dark', isDark.value);
  }

  function setTheme(theme: string) {
    activeTheme.value = theme;
    applyTheme();
  }

  function toggleMode() {
    isDark.value = !isDark.value;
    applyTheme();
  }

  return { activeTheme, isDark, setTheme, toggleMode, applyTheme };
});
```

---

## 13. Frontend Technology Integration

Universal Core UI/UX standards apply universally across supported frontend runtimes. Engineers must integrate these standards without violating framework architectural patterns.

### 13.1 Laravel Blade & Tailwind CSS
Blade components encapsulate UI tokens, supporting native attributes and localization directives:
```blade
{{-- resources/views/components/ui/card.blade.php --}}
@props([
    'title' => null,
    'description' => null,
    'density' => 'comfortable', // compact | comfortable | spacious
])

@php
$densityClasses = [
    'compact' => 'p-3 gap-2',
    'comfortable' => 'p-5 gap-4',
    'spacious' => 'p-8 gap-6',
][$density] ?? 'p-5 gap-4';
@endphp

<section {{ $attributes->merge(['class' => "bg-surface-card border border-border-default rounded-xl shadow-sm flex flex-col {$densityClasses} transition-colors duration-150"]) }}>
    @if($title || $description)
        <header class="border-b border-border-subtle pb-3">
            @if($title)
                <h3 class="text-base font-semibold text-text-primary tracking-tight">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="text-xs text-text-secondary mt-0.5">{{ $description }}</p>
            @endif
        </header>
    @endif

    <div class="flex-1">
        {{ $slot }}
    </div>
</section>
```

### 13.2 Filament 5.x and Livewire 4.x
In Filament and Livewire, adhere to Filament's theme configuration and schema components rather than injecting inline CSS:
- Enforce the 4px/8px spacing grid using Filament's grid system (`->columns(['sm' => 1, 'md' => 2, 'xl' => 4])`).
- Respect density configurations in table schemas (`$table->striped()->defaultPaginationPageOption(25)`).
- Use Filament Action modals for destructive confirmations (`->requiresConfirmation()`, `->modalIcon('heroicon-o-trash')`, `->modalHeading('Delete Customer Record')`).
- Consult `SKILL-FILA-001` for detailed Livewire state snapshotting and Filament Unified Schema lifecycles.

### 13.3 React 18+ and TypeScript
In React applications, implement design tokens using strongly typed variant props with strict accessibility bindings:
```tsx
// src/components/ui/Button.tsx
import React, { forwardRef } from 'react';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(({
  variant = 'primary',
  size = 'md',
  isLoading = false,
  children,
  disabled,
  className = '',
  ...props
}, ref) => {
  const baseClasses = "inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none";
  
  const variantClasses = {
    primary: "bg-primary hover:bg-primary-hover text-white focus-visible:ring-border-focus",
    secondary: "bg-surface-hover hover:bg-surface-active text-text-primary focus-visible:ring-border-default",
    danger: "bg-danger hover:bg-red-700 text-white focus-visible:ring-danger",
    ghost: "bg-transparent hover:bg-surface-hover text-text-secondary focus-visible:ring-border-default",
  }[variant];

  const sizeClasses = {
    sm: "h-8 px-3 text-xs gap-1.5",
    md: "h-10 px-4 text-sm gap-2",
    lg: "h-12 px-6 text-base gap-2.5",
  }[size];

  return (
    <button
      ref={ref}
      disabled={disabled || isLoading}
      aria-busy={isLoading}
      className={`${baseClasses} ${variantClasses} ${sizeClasses} ${className}`}
      {...props}
    >
      {isLoading && (
        <svg className="animate-spin -ms-1 me-2 h-4 w-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      )}
      {children}
    </button>
  );
});
Button.displayName = 'Button';
```

### 13.4 Vue 3 and Inertia.js
In Vue 3 with Inertia, maintain design tokens in typed SFCs utilizing the Composition API (`<script setup lang="ts">`):
```vue
<!-- resources/js/Components/UI/MetricCard.vue -->
<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  label: string;
  value: string | number;
  trend?: {
    percentage: number;
    direction: 'up' | 'down' | 'neutral';
  };
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
});

const trendColor = computed(() => {
  if (!props.trend) return '';
  return props.trend.direction === 'up'
    ? 'text-emerald-600 dark:text-emerald-400'
    : props.trend.direction === 'down'
    ? 'text-rose-600 dark:text-rose-400'
    : 'text-text-secondary';
});
</script>

<template>
  <div class="bg-surface-card border border-border-default p-5 rounded-xl shadow-sm">
    <div v-if="isLoading" class="animate-pulse space-y-3" aria-hidden="true">
      <div class="h-3 bg-surface-hover rounded w-1/3"></div>
      <div class="h-7 bg-surface-hover rounded w-2/3"></div>
    </div>
    <div v-else>
      <dt class="text-xs font-medium text-text-secondary uppercase tracking-wider">{{ label }}</dt>
      <dd class="mt-2 flex items-baseline justify-between">
        <span class="text-2xl font-bold tracking-tight text-text-primary tabular-nums">{{ value }}</span>
        <span v-if="trend" :class="['text-xs font-semibold flex items-center gap-0.5', trendColor]">
          <span v-if="trend.direction === 'up'">↑</span>
          <span v-else-if="trend.direction === 'down'">↓</span>
          {{ Math.abs(trend.percentage) }}%
        </span>
      </dd>
    </div>
  </div>
</template>
```

---

## 14. UI Performance and Perceived Performance

### 14.1 Perceived Performance Optimization
1. **Layout Stability (Zero Cumulative Layout Shift - CLS):**
   - Explicitly define `width` and `height` (or `aspect-ratio`) on all `<img>`, `<video>`, and canvas elements.
   - Reserve geometric space for asynchronous data cards and advertisements using skeleton placeholders. Content loading must never push already visible content downwards.
2. **Skeleton Screens vs. Spinners:**
   - Use **Skeleton Screens** for content loading (cards, lists, tables). Skeletons mimic the structural layout of upcoming content and convey rapid progress.
   - Use **Inline Spinners** only for short-lived submission actions (e.g., inside a button during form submission).
3. **Web Font Loading Strategy:**
   - Apply `font-display: swap` on all custom web fonts (`Cairo`, `Inter`, `Alexandria`) to prevent invisible text during font download (FOIT).
   - Preload critical primary fonts via `<link rel="preload" as="font" type="font/woff2" crossorigin>`.
   - Prevent Cumulative Layout Shift using size-adjusted fallback font descriptors.

---

## 15. Design Quality Review, UX Acceptance Criteria, and Defect Management

### 15.1 Review Methodology and Gating
Prior to declaring an interface or design customization deliverable complete, engineers and reviewing agents must evaluate it against the **UI/UX Acceptance Matrix**:

| Category | Verification Item | Pass Criteria |
| :--- | :--- | :--- |
| **Tokens & Consistency** | Semantic Token Adherence | Zero hardcoded arbitrary hex colors or rogue pixel spacing in templates or CSS. |
| **Color Management** | Palette & Contrast Compliance | All color families define verified stops; text-to-surface contrast $\ge 4.5:1$ (WCAG AA). |
| **Typography** | Scale, Assets & Line-Height | Font assets verified on disk; Arabic line-height $\ge 1.5 - 1.7$; `font-synthesis: none` enforced. |
| **Bidirectionality** | RTL / LTR Rendering | Flawless layout switch with `dir="rtl"`; logical CSS properties used exclusively; icons mirrored correctly. |
| **Theme System** | Multi-Theme & Runtime Switch | Themes switch dynamically without page reload; light and dark modes fully decoupled. |
| **Design Presets** | Preset Integrity & Fallbacks | Presets customize cleanly without breaking core token dependencies; fallbacks verified. |
| **Component Contracts** | Component Token Boundaries | Components consume approved tokens only; touch targets $\ge 44\times 44$px; full state coverage. |
| **Live Preview** | Preview Isolation & Safety | Preview changes isolated from production sessions; discard/rollback tested and operational. |
| **Governance & Security** | Sanitization & RBAC | Zero unescaped CSS/markup injection; upload boundaries enforced; integrity checksums recorded. |
| **Interactive States** | State Completeness | Complete 12-state interactive journey implemented where applicable; all controls have hover, focus, active, disabled. |
| **Error Handling** | Form Error Display | Clear inline error messages; first invalid field focused; inputs preserved upon error. |
| **Responsive Adaptation**| Mobile Breakpoints | Zero horizontal document overflow (`overflow-x`) down to 360px viewport. |
| **Accessibility Gate** | Baseline Compliance | Zero critical or serious accessibility barriers per `SKILL-A11Y-001`. |

### 15.2 Defect Severity Classification
- **Blocker (Severity 1):** User cannot complete a core workflow; customization allows arbitrary CSS/XSS injection; total layout destruction in RTL; text contrast $< 3:1$ on primary interactive surfaces.
- **Major (Severity 2):** Workflow is possible but confusing or broken across specific breakpoints; theme switching causes FOUC or requires manual page reload; table columns overflowing without scroll; missing focus-visible rings.
- **Minor (Severity 3):** Visual inconsistency that does not halt the workflow; subtle spacing misalignment ($< 4$px); missing hover transition; font weight falling back to standard bold without layout shift.
- **Cosmetic (Severity 4):** Minor visual polish, subjective aesthetic enhancement, or slight badge color tone variation.

---

## 16. Verification Checklist

Before releasing or closing any UI/UX or visual customization deliverable, verify compliance against every mandatory check:

- [ ] All styling uses semantic design tokens; zero uncontrolled inline hex codes or arbitrary pixel margins?
- [ ] 300-line modularity rule strictly respected; large views decomposed into atomic sub-components?
- [ ] Centralized token taxonomy adheres to DTCG naming conventions and acyclic dependency graphs?
- [ ] Color management supports full tonal scales; text-to-surface contrast meets WCAG 2.2 AA ($4.5:1$)?
- [ ] All registered font families and weights backed by verified physical font assets; `font-synthesis: none` enforced?
- [ ] Full 12-state interactive lifecycle accounted for (Initial, Loading, Empty, Populated, Error, Unsaved Changes, etc.)?
- [ ] No non-functional placeholder buttons or dead-end controls rendered?
- [ ] Form labels permanently visible above inputs; placeholder anti-pattern avoided?
- [ ] Validation occurs on blur and submit; keystroke spamming avoided?
- [ ] CSS Logical Properties used exclusively (`margin-inline-start`, `padding-inline-end`, `inset-inline-start`)?
- [ ] Arabic typography line-heights expanded ($\ge 1.5 - 1.7$); dual-script font tokens configured?
- [ ] Directional icons flipped in RTL; symmetrical, media, clock, and brand icons preserved?
- [ ] Dark mode designed with independent surface luminance; no raw `invert()` filters?
- [ ] Multi-theme architecture supports runtime switching without full page reload; FOUC prevented via cookie/SSR hydration?
- [ ] Component customization contracts strictly respected across all 18 standard component categories?
- [ ] Live preview workflow isolates draft changes from production; unsaved changes guard and rollback operational?
- [ ] Customization governance enforces RBAC, JSON schema validation, input sanitization, and SHA-256 integrity checksums?
- [ ] Mobile breakpoints verified down to 360px viewport with zero horizontal overflow?
- [ ] `prefers-reduced-motion` media query respected across all animations and transitions?
- [ ] Design acceptance verified against `SKILL-A11Y-001`; zero unresolved critical accessibility barriers?

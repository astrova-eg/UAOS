# Shared Engineering Skill: Accessibility Engineering Standards and WCAG 2.2 AA

**Skill ID:** SKILL-A11Y-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  
**Approved Baseline:** W3C WCAG 2.2 Level AA (Primary Target), WAI-ARIA 1.2/1.3, HTML5 Accessibility API (AAM), ARIA Authoring Practices Guide (APG), axe-core v4.x, Playwright Accessibility Automation, Screen Readers (NVDA, VoiceOver, JAWS)  

---

## 1. Skill Identity, Scope, and Enterprise Baseline

The **Accessibility Engineering Standards and WCAG 2.2 AA** skill establishes the authoritative engineering standard for architecting, constructing, testing, auditing, and maintaining universally accessible digital applications across Universal Core.

### 1.1 Mission and Purpose
This skill equips Accessibility Specialists, Frontend Engineers, and autonomous engineering agents to build digital interfaces that guarantee equal access and unhindered operability for all users, including individuals with visual, motor, auditory, cognitive, or neurological disabilities. It enforces:
- **WCAG 2.2 Level AA Normative Compliance:** Strict adherence to all applicable Level A and Level AA success criteria of the Web Content Accessibility Guidelines (WCAG) 2.2, with particular emphasis on newly introduced 2.2 criteria.
- **Native Semantic Foundation ("First Rule of ARIA"):** Maximizing native HTML5 semantics (`<button>`, `<dialog>`, `<nav>`, `<input>`, `<table>`) before considering custom ARIA widgets.
- **Flawless Keyboard Ergonomics:** Full programmatic and visual keyboard operability for 100% of interactive controls, featuring non-obscured visible focus indicators, logical tab sequences, and standard keyboard interaction models.
- **Screen Reader Parity:** Exact equivalence of information and state announcements across leading screen readers (NVDA, VoiceOver, JAWS, TalkBack), ensuring that visual changes, dynamic content, and error states are accurately communicated through accessible names, descriptions, and live regions.
- **Bidirectional Multilingual Inclusivity:** Flawless accessibility across both Arabic (RTL) and English (LTR) locales, accommodating Arabic screen reader pronunciations, inverted directional keyboard models, and localized accessible metadata.
- **Layered Empirical Verification:** Establishing an eight-layer accessibility verification lifecycle combining static linting, automated DOM scanning (`axe-core`), keyboard traversal audits, screen reader validation, and manual WCAG compliance reviews.

> [!WARNING]
> **Automated Scanners Are Necessary But Insufficient:**  
> Passing automated tests (such as `axe-core`, Lighthouse, or ESLint plugins) does **NOT** constitute WCAG conformance or legal certification. Automated tools detect only approximately 30% to 40% of accessibility defects. The remaining 60% to 70% (including focus logical order, meaningful alternative text, keyboard traps, screen reader comprehensibility, and cognitive flows) require mandatory manual and assistive technology verification.

### 1.2 Relationship to UI/UX Engineering (`SKILL-UIUX-001`)
Accessibility Engineering and UI/UX Engineering function as independent, peer disciplines with clear boundaries:

| Dimension | UI/UX Engineering (`SKILL-UIUX-001`) | Accessibility Engineering (`SKILL-A11Y-001`) |
| :--- | :--- | :--- |
| **Primary Focus** | User experience, visual harmony, ergonomics, design tokens, responsive layout, information architecture. | WCAG 2.2 normative criteria, assistive tech compatibility, semantic DOM trees, keyboard models, focus management. |
| **Color & Contrast** | Defines color palettes, semantic roles, dark/light themes, and visual balance. | Validates luminance contrast mathematically ($4.5:1$ text, $3:1$ UI components), non-color cues, and reflow. |
| **Component States**| Designs visual representations for hover, focus, active, disabled, loading, empty, and error. | Ensures programmatic state exposure (`aria-expanded`, `aria-busy`, `aria-disabled`), keyboard operability, and focus visibility. |
| **Navigation** | Determines hierarchical wayfinding, breadcrumb structures, and responsive menu patterns. | Mandates landmark regions (`<nav>`, `<main>`), skip links, logical tab orders, and focus restoration upon closure. |
| **Review Authority** | Owns design acceptance and visual fidelity. Cannot approve an interface with known A11Y blockers. | Identifies accessibility violations and mandates technical remediation; does not arbitrarily alter visual branding. |

---

## 2. Accessibility Architecture, Governance, and Lifecycle

Accessibility is an engineering invariant embedded across every stage of the software development lifecycle:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        Accessibility Lifecycle                         │
├─────────────────┬──────────────────┬─────────────────┬─────────────────┤
│ 1. Architecture │ 2. Build Layer   │ 3. Automated CI │ 4. Manual Audit │
│ - Semantic DOM  │ - Native HTML5   │ - axe-core scan │ - Keyboard nav  │
│ - Focus logic   │ - WAI-ARIA APG   │ - ESLint a11y   │ - Screen reader │
│ - Token contrast│ - RTL/LTR logic  │ - Playwright E2E│ - Zoom & reflow │
└─────────────────┴──────────────────┴─────────────────┴─────────────────┘
```

### 2.1 Governance and Role Ownership
1. **Design / UI/UX Authority:** Ensures design tokens satisfy $4.5:1$ normal text and $3:1$ non-text contrast ratios; specifies visible focus rings; provides permanent form labels.
2. **Frontend Engineering Authority:** Implements semantic HTML elements; manages DOM focus programmatically; binds `aria-*` attributes dynamically; implements keyboard listeners according to WAI-ARIA Authoring Practices Guide (APG).
3. **Backend / API Authority:** Delivers actionable, structured validation error payloads; ensures localized accessible error messages; supports accessible authentication mechanisms.
4. **QA & Accessibility Specialist:** Executes automated scanning pipelines; conducts manual keyboard sweeps; tests with assistive screen readers; logs reproducible WCAG defects with severity rankings.

---

## 3. Comprehensive WCAG 2.2 Level A and AA Requirements

Universal Core requires compliance with all applicable Level A and Level AA success criteria under WCAG 2.2, structured across the four foundational principles: **Perceivable**, **Operable**, **Understandable**, and **Robust**.

### 3.1 Principle 1: Perceivable
Information and user interface components must be presentable to users in ways they can perceive.

#### 3.1.1 Guideline 1.1 Text Alternatives
- **1.1.1 Non-text Content (Level A):**
  - All non-text content (`<img>`, `<svg>`, `<canvas>`, icon buttons) must have a text alternative that serves an equivalent purpose.
  - Informative images must have concise, descriptive `alt` text.
  - Decorative images, background graphics, or redundant illustrations must have `alt=""` or `aria-hidden="true"` to prevent screen reader noise.
  - Complex charts and diagrams must provide a detailed text description or data table accessible in the immediate context.

#### 3.1.2 Guideline 1.2 Time-based Media
- **1.2.1 Audio-only and Video-only (Prerecorded) (Level A):** Provide text transcripts for prerecorded audio and video.
- **1.2.2 Captions (Prerecorded) (Level A):** Synchronized captions must be provided for all prerecorded video with audio.
- **1.2.3 Audio Description or Media Alternative (Level A):** Provide audio descriptions or text alternatives for video content.
- **1.2.4 Captions (Live) (Level AA):** Provide synchronized captions for live audio streams.
- **1.2.5 Audio Description (Prerecorded) (Level AA):** Provide synchronized audio descriptions for prerecorded video.

#### 3.1.3 Guideline 1.3 Adaptable
- **1.3.1 Info and Relationships (Level A):**
  - Information, structure, and relationships conveyed visually must be programmatically determinable through semantic markup.
  - Headings must use `<h1>` through `<h6>` in a strict logical hierarchy without skipping levels (e.g., never jump from `<h2>` directly to `<h4>`).
  - Lists must use `<ul>`, `<ol>`, or `<dl>`.
  - Tabular data must use `<table>`, `<caption>`, `<thead>`, `<tbody>`, and `<th>` with explicit `scope="col"` or `scope="row"`.
- **1.3.2 Meaningful Sequence (Level A):** The programmatic reading order (DOM sequence) must match the visual and logical reading sequence. Never rearrange DOM nodes with CSS flexbox/grid `order` in a way that disrupts tab or reading order.
- **1.3.3 Sensory Characteristics (Level A):** Instructions must not rely solely on shape, color, size, visual location, or sound (e.g., never instruct: "Click the circular red button on the right").
- **1.3.4 Orientation (Level AA):** Content must not restrict its view and operation to a single display orientation (portrait or landscape) unless essential.
- **1.3.5 Identify Input Purpose (Level AA):** Common input fields collecting personal information must provide standard `autocomplete` tokens (e.g., `autocomplete="name"`, `autocomplete="email"`, `autocomplete="tel"`).

#### 3.1.4 Guideline 1.4 Distinguishable
- **1.4.1 Use of Color (Level A):** Color must not be used as the sole visual means of conveying information, indicating an action, prompting a response, or distinguishing a visual element. Always pair color with text, icons, borders, or patterns (e.g., error fields must feature an error icon and text message, not merely a red border).
- **1.4.2 Audio Control (Level A):** If audio plays automatically for more than 3 seconds, provide a mechanism to pause, stop, or control volume independently from the system volume.
- **1.4.3 Contrast (Minimum) (Level AA):**
  - **Normal Text (< 18pt / 24px or < 14pt / 18.5px bold):** Minimum contrast ratio of **4.5:1** against its background.
  - **Large Text ($\ge$ 18pt / 24px or $\ge$ 14pt / 18.5px bold):** Minimum contrast ratio of **3.0:1** against its background.
- **1.4.4 Resize Text (Level AA):** Text must be resizable up to **200%** using browser zoom without loss of content or functionality, and without clipping in fixed-height containers.
- **1.4.5 Images of Text (Level AA):** Use actual text styled with CSS rather than embedding text inside raster images, except for brand logos.
- **1.4.10 Reflow (Level AA):** Content must reflow without loss of information or functionality, and without requiring two-dimensional scrolling (horizontal scroll), at a width of **320 CSS pixels** (equivalent to 400% zoom at 1280px).
- **1.4.11 Non-text Contrast (Level AA):** Active user interface components (input borders, buttons, checkboxes, focus indicators) and meaningful graphical objects must have a minimum contrast ratio of **3.0:1** against adjacent background colors.
- **1.4.12 Text Spacing (Level AA):** Content must not be truncated or broken when users adjust text spacing to: line height 1.5x font size, paragraph spacing 2x font size, letter spacing 0.12x font size, and word spacing 0.16x font size.
- **1.4.13 Content on Hover or Focus (Level AA):** Tooltips and popovers triggered by hover or focus must be:
  - *Dismissible:* Can be closed via the `Escape` key without moving the pointer or focus.
  - *Hoverable:* Pointer can move over the tooltip content without it disappearing.
  - *Persistent:* Remains visible until the trigger loses hover/focus or the user dismisses it.

---

### 3.2 Principle 2: Operable
User interface components and navigation must be operable.

#### 3.2.1 Guideline 2.1 Keyboard Accessible
- **2.1.1 Keyboard (Level A):** All functionality of the interface must be operable through a keyboard interface without requiring specific timings for individual keystrokes.
- **2.1.2 No Keyboard Trap (Level A):** Keyboard focus must never become trapped inside a component or modal. If focus is moved into a dialog, the user must be able to navigate out using standard keys (`Tab`, `Shift+Tab`, `Escape`).
- **2.1.4 Character Key Shortcuts (Level A):** If single-character keyboard shortcuts are implemented (e.g., `?` or `s`), provide a mechanism to turn them off, remap them, or restrict them to when the component has focus.

#### 3.2.2 Guideline 2.2 Enough Time
- **2.2.1 Timing Adjustable (Level A):** If a session timeout exists, provide a mechanism to turn off, adjust, or extend the limit by at least 10 times with an accessible warning dialog.
- **2.2.2 Pause, Stop, Hide (Level A):** For any moving, blinking, scrolling, or auto-updating information that starts automatically and lasts more than 5 seconds, provide a user control to pause, stop, or hide it.

#### 3.2.3 Guideline 2.3 Seizures and Physical Reactions
- **2.3.1 Three Flashes or Below Threshold (Level A):** Web pages must not contain anything that flashes more than three times in any one-second period, preventing epileptic seizures.

#### 3.2.4 Guideline 2.4 Navigable
- **2.4.1 Bypass Blocks (Level A):** A mechanism (e.g., a "Skip to Main Content" link) must be available to bypass repeated blocks of content (headers, navigation bars).
- **2.4.2 Page Titled (Level A):** Each web page must have an informative, descriptive `<title>` that identifies the specific page context followed by the application name (e.g., `<title>Edit Invoice INV-2026-001 | Universal Core</title>`).
- **2.4.3 Focus Order (Level A):** If a web page can be navigated sequentially, focusable components must receive focus in an order that preserves meaning and operability.
- **2.4.4 Link Purpose (In Context) (Level A):** The purpose of each link must be determinable from the link text alone or from the link text together with its programmatically determined context. Avoid vague link text such as "Click here" or "Read more"; use "View Invoice INV-2026-001 details".
- **2.4.6 Headings and Labels (Level AA):** Headings and labels must be clear, descriptive, and accurately represent the content or function they introduce.
- **2.4.7 Focus Visible (Level AA):** Any keyboard operable user interface must have a clearly visible focus indicator when receiving keyboard focus.
- **2.4.11 Focus Not Obscured (Minimum) (Level AA - WCAG 2.2 New):**
  - When an item receives keyboard focus, it must **not be entirely obscured** by author-created content (such as sticky headers, floating footers, cookie banners, or modal overlays).
  - *Engineering Rule:* Sticky headers must use scroll-padding (`scroll-padding-top: 5rem`) or dynamically adjust scroll offsets so focused elements remain visible in the viewport.
- **2.4.12 Focus Not Obscured (Enhanced) (Level AAA Context):** No part of the focused item may be obscured by author-created content. While Level AAA, Universal Core strives to keep the entire focused element visible.

#### 3.2.5 Guideline 2.5 Input Modalities
- **2.5.1 Pointer Gestures (Level A):** All functionality that uses multipoint or path-based gestures (pinch zoom, swipe) must be operable with a single pointer without a path-based gesture (e.g., provide `+` and `-` zoom buttons alongside pinch zoom).
- **2.5.2 Pointer Cancellation (Level A):** For single-pointer clicks, activation must occur on the `up` event (mouse up / touch end), allowing users to cancel the action by moving the pointer away before releasing.
- **2.5.3 Label in Name (Level A):** For user interface components with text labels, the programmatic accessible name must contain the visual text presented to the user.
- **2.5.7 Dragging Movements (Level AA - WCAG 2.2 New):**
  - Any functionality that requires a dragging movement (e.g., drag-and-drop kanban boards, reorderable table rows, slider thumbs) must also be operable via a **simple pointer interaction** (e.g., "Move Up" / "Move Down" buttons, keyboard arrow keys, or a click-to-select destination menu).
  - *Exception:* When dragging is essential (e.g., freehand drawing canvas).
- **2.5.8 Target Size (Minimum) (Level AA - WCAG 2.2 New):**
  - The size of the target for pointer inputs must be at least **24 by 24 CSS pixels**, OR the target must have an offset/spacing such that a 24px diameter circle centered on the target does not intersect another target or another target's spacing area.
  - *Universal Core Standard:* Universal Core mandates a standard target size of **44 by 44 CSS pixels** for primary controls, with a strict minimum of 24×24px for dense desktop controls with at least 8px margin separation.
  - *Exceptions:* Inline text links, native browser select dropdown arrows, or essential controls.

---

### 3.3 Principle 3: Understandable
Information and the operation of user interface must be understandable.

#### 3.3.1 Guideline 3.1 Readable
- **3.1.1 Language of Page (Level A):** The default natural language of the document must be declared on the root element (e.g., `<html lang="ar" dir="rtl">` or `<html lang="en" dir="ltr">`).
- **3.1.2 Language of Parts (Level AA):** Any phrase, passage, or component written in a language different from the document default must declare its language explicitly (e.g., `<span lang="en">Terms of Service</span>` within an Arabic page).

#### 3.3.2 Guideline 3.2 Predictable
- **3.2.1 On Focus (Level A):** Merely receiving focus must not initiate an unexpected change of context (e.g., focusing an input field must not automatically submit the form or navigate to a new URL).
- **3.2.2 On Input (Level A):** Changing the setting of any user interface component (e.g., selecting a checkbox or dropdown option) must not automatically cause a change of context unless the user has been explicitly advised of the behavior in advance.
- **3.2.3 Consistent Navigation (Level AA):** Navigational mechanisms that are repeated across multiple pages must occur in the same relative order.
- **3.2.4 Consistent Identification (Level AA):** Components that have the same functionality across an application must be identified consistently (e.g., do not label a search input "Find" on one page and "Search" on another).
- **3.2.6 Consistent Help (Level A - WCAG 2.2 New):**
  - If a website provides help mechanisms (such as contact information, human support messaging, automated chat agent, or self-help documentation), they must appear in a **consistent relative order** across all pages where they occur.

#### 3.3.3 Guideline 3.3 Input Assistance
- **3.3.1 Error Identification (Level A):** If an input error is detected, the item that is in error must be identified and the error described to the user in clear, accessible text.
- **3.3.2 Labels or Instructions (Level A):** Labels or instructions must be provided when content requires user input.
- **3.3.3 Error Suggestion (Level AA):** If an input error is detected and suggestions for correction are known, the suggestions must be provided to the user, unless doing so would jeopardize security.
- **3.3.4 Error Prevention (Legal, Financial, Data) (Level AA):** For pages that cause legal commitments, financial transactions, or data modifications/deletions, submissions must be **reversible**, **checked for input errors**, OR **confirmed** through an explicit confirmation step before final submission.
- **3.3.7 Redundant Entry (Level A - WCAG 2.2 New):**
  - Information previously entered by or provided to the user that is required to be entered again in the same process must be **either auto-populated** OR **available for the user to select** (e.g., "Billing address same as shipping address" checkbox).
  - *Exceptions:* Re-entering is essential (e.g., confirming a new password during reset), or the previous information is no longer valid.
- **3.3.8 Accessible Authentication (Minimum) (Level AA - WCAG 2.2 New):**
  - A cognitive function test (such as remembering a complex password, solving a math equation, or memorizing an arbitrary puzzle) must **not be required** for any step in an authentication process, UNLESS at least one of the following is provided:
    1. *Alternative:* Another authentication method that does not rely on a cognitive test (e.g., email magic link, biometric WebAuthn / Passkeys, SMS OTP).
    2. *Mechanism:* A mechanism to assist the user (e.g., **allowing copy-and-paste into password fields** and full compatibility with password managers).
    3. *Object Recognition:* Recognizing familiar objects or personal images.
    4. *Personal Content:* Identifying user-provided content.
  - *Engineering Rule:* Never disable pasting on password or OTP fields (`onpaste="return false"` is strictly prohibited).

---

### 3.4 Principle 4: Robust
Content must be robust enough that it can be reliably interpreted by a wide variety of user agents, including assistive technologies.

#### 3.4.1 Guideline 4.1 Compatible
- **4.1.2 Name, Role, Value (Level A):**
  - For all user interface components (including custom form elements, script-generated links, tabs, and modals), the **Name** and **Role** can be programmatically determined; states, properties, and values that can be set by the user can be programmatically set; and notification of changes to these items is available to user agents, including assistive technologies.
- **4.1.3 Status Messages (Level AA):**
  - In content implemented using markup languages, status messages (e.g., "3 items added to cart", "Search found 12 results", "Saving changes...") must be programmatically determined through role or properties (`aria-live="polite"` or `role="status"`) so they can be presented to the user by assistive technologies without receiving focus.

---

## 4. Semantic HTML, Landmarks, and Document Structure

### 4.1 Native HTML5 First Invariant
Custom interactive components built out of `<div>` and `<span>` elements with synthetic event listeners are the primary cause of accessibility failures. Follow the **W3C First Rule of ARIA**:
> *"If you can use a native HTML element or attribute with the semantics and behavior you require already built-in, then do so instead of re-purposing an element and adding ARIA roles, states, or properties to make it accessible."*

| Anti-Pattern (Prohibited) | Standard Semantic Implementation (Mandatory) | Rationale |
| :--- | :--- | :--- |
| `<div class="btn" onclick="save()">Save</div>` | `<button type="button" class="btn" onclick="save()">Save</button>` | Native keyboard focus, Enter/Space activation, native disabled state. |
| `<div class="link" onclick="visit()">About</div>` | `<a href="/about" class="link">About</a>` | Native browser routing, open in new tab, context menu, screen reader link role. |
| `<div role="heading" aria-level="2">Title</div>` | `<h2>Title</h2>` | Native heading navigation in screen readers, zero ARIA redundancy. |
| `<div class="checkbox" onclick="toggle()">` | `<input type="checkbox" id="terms"><label for="terms">` | Built-in checked state, spacebar toggling, accessibility tree synchronization. |

### 4.2 Landmark Regions
Structure every application layout using HTML5 landmark elements:
- `<header>`: Site banner, global application brand, top navigation.
- `<nav aria-label="...">`: Primary, secondary, and breadcrumb navigation blocks. Every `<nav>` must have an accessible label distinguishing its purpose (e.g., `<nav aria-label="Main Navigation">`, `<nav aria-label="Breadcrumb">`).
- `<main id="main-content">`: The unique, primary content of the document. There must be exactly one visible `<main>` element per view.
- `<aside aria-label="...">`: Contextual sidebars, complementary information, or secondary widgets.
- `<footer>`: Application footer, copyright, legal links.
- `<search role="search">`: Search containers, enclosing search inputs and action buttons.

### 4.3 Accessible Skip Link Implementation
Every application layout must provide a skip link as the very first focusable element in the DOM:
```html
<!-- Native Accessible Skip Link -->
<a href="#main-content" 
   class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
  Skip to main content
</a>
```

### 4.4 Accessible Name Computation (AccName 1.2)
Every interactive element must have a non-empty Accessible Name computed according to the W3C Accessible Name and Description Computation specification:
1. `aria-labelledby`: References IDs of elements providing the name.
2. `aria-label`: Explicit string defining the name.
3. Native subtree text: Text content inside the element (e.g., `<button>Save</button>`).
4. Native attribute: `alt` on `<img>`, `title` on `<input>` (last resort).

```html
<!-- Correct Accessible Name on Icon Button -->
<button type="button" 
        aria-label="Delete Invoice INV-2026-001" 
        class="text-red-600 hover:text-red-700 p-2">
  <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
    <path d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" />
  </svg>
</button>
```

---

## 5. Keyboard Navigation, Visible Focus, and Focus Management

### 5.1 Keyboard Operation Standards
Every workflow achievable with a mouse or touch must be 100% operable with a standard keyboard:
- **`Tab` / `Shift+Tab`:** Moves forward and backward between interactive components in natural reading order.
- **`Enter`:** Activates links, primary buttons, and submits active forms.
- **`Spacebar`:** Activates buttons, toggles checkboxes, switches radio buttons, opens dropdown menus.
- **Arrow Keys (`Up`/`Down`/`Left`/`Right`):** Navigates within composite widgets (tabs, radio groups, menu bars, slider tracks).
- **`Escape`:** Closes open modal dialogs, popovers, dropdown menus, and tooltips, returning focus to the trigger.
- **`Home` / `End`:** Moves to the first or last item in a composite widget (e.g., first/last tab or menu item).

### 5.2 Visible Focus Indicators (WCAG 2.4.7 & 2.4.13)
Focus indicators must **never** be removed via `outline: none` without providing an equivalent or superior visible indicator:
- **Minimum Contrast:** The focus ring must have at least **3.0:1 contrast** against both the component surface and the adjacent background.
- **Minimum Dimensions:** At least **2 CSS pixels thick** with an offset (`outline-offset: 2px`) to ensure separation from element borders.
- **Focus-Visible Discipline:** Use `:focus-visible` to present focus rings exclusively on keyboard navigation while preventing distracting rings on mouse clicks:
  ```css
  /* Enterprise Focus-Visible Standard */
  :focus:not(:focus-visible) {
    outline: none;
  }

  :focus-visible {
    outline: 2px solid var(--color-primary, #2563eb);
    outline-offset: 2px;
  }
  ```

### 5.3 Focus Trapping and Restoration in Dialogs
When a modal dialog or slide-out drawer opens:
1. Focus must immediately move to the dialog container, the first focusable element inside the dialog, or the modal heading.
2. Focus must be **trapped** inside the dialog. Pressing `Tab` on the last focusable element must cycle to the first element; pressing `Shift+Tab` on the first must cycle to the last. Background elements must be marked `inert` or hidden via `aria-hidden="true"`.
3. When the user dismisses the dialog (via `Escape` or the close button), focus must be **restored to the trigger element** that opened it.

---

## 6. Screen Readers, ARIA Semantics, and Live Regions

### 6.1 Dynamic Content Announcements (`aria-live`)
Screen reader users do not perceive visual updates that occur without a full page reload. Dynamic status updates must be announced via ARIA live regions:
- **`role="status"` / `aria-live="polite"`:** For non-urgent updates (e.g., "5 search results found", "Saved draft", "Page 2 loaded"). The screen reader waits until the user finishes reading their current item before announcing.
- **`role="alert"` / `aria-live="assertive"`:** For critical, immediate warnings (e.g., "Connection lost", "Credit card expired"). The screen reader interrupts current speech immediately. Use sparingly to avoid disorienting the user.

```html
<!-- Live Status Region -->
<div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
  {{ $liveStatusMessage }}
</div>
```

---

## 7. Forms, Validation, and Accessible Error Handling

### 7.1 Form Field Accessibility Architecture
Every form field must establish unambiguous programmatic relationships between the label, input, helper description, and error message:

```
[ Label (<label for="email">) ]
       │
       ▼
[ Input (<input id="email" aria-describedby="email-desc email-err" aria-invalid="true">) ]
       ├──> [ Helper Text (<p id="email-desc">) ]
       └──> [ Error Message (<p id="email-err" role="alert">) ]
```

### 7.2 Complete Accessible Blade Form Field Implementation
```blade
{{-- resources/views/components/form/input.blade.php --}}
@props([
    'name',
    'id' => null,
    'label',
    'type' => 'text',
    'required' => false,
    'helper' => null,
    'autocomplete' => null,
])

@php
$inputId = $id ?? 'input-' . $name;
$helperId = $inputId . '-helper';
$errorId = $inputId . '-error';
$hasError = $errors->has($name);
$describedBy = trim(($helper ? $helperId : '') . ' ' . ($hasError ? $errorId : ''));
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $inputId }}" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
        {{ $label }}
        @if($required)
            <span class="text-red-600 dark:text-red-400 font-bold" aria-hidden="true">*</span>
            <span class="sr-only">(Required field)</span>
        @else
            <span class="text-xs font-normal text-slate-500 dark:text-slate-400 ms-1">(Optional)</span>
        @endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $attributes->get('value')) }}"
        @if($required) required aria-required="true" @endif
        @if($hasError) aria-invalid="true" aria-errormessage="{{ $errorId }}" @endif
        @if(!empty($describedBy)) aria-describedby="{{ $describedBy }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'h-10 px-3 text-sm rounded-lg border ' . ($hasError ? 'border-red-500 ring-1 ring-red-500 bg-red-50/20' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900') . ' focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-colors']) }}
    />

    @if($helper && !$hasError)
        <p id="{{ $helperId }}" class="text-xs text-slate-500 dark:text-slate-400">
            {{ $helper }}
        </p>
    @endif

    @error($name)
        <p id="{{ $errorId }}" role="alert" class="text-xs font-medium text-red-600 dark:text-red-400 flex items-center gap-1 mt-0.5">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
```

### 7.3 Form Error Recovery and Submission Focus
When a form submission fails backend validation:
1. Render an **Accessible Error Summary** at the top of the form, configured with `role="alert"`, `tabindex="-1"`, and an internal list of links pointing to each invalid input.
2. Programmatically transfer keyboard focus to the Error Summary container or directly to the first invalid input element so screen readers immediately inform the user of the failure.

---

## 8. Accessible Interactive Components (WAI-ARIA APG Patterns)

Engineers implementing custom interactive widgets must strictly replicate the W3C WAI-ARIA Authoring Practices Guide (APG) design patterns.

### 8.1 Accessible Modal Dialog Pattern
A modal dialog must satisfy:
- Container: `role="dialog"`, `aria-modal="true"`, `aria-labelledby="dialog-title-id"`, optional `aria-describedby="dialog-desc-id"`.
- Keyboard: `Escape` key closes dialog; `Tab` / `Shift+Tab` traps focus inside.
- Focus: Sets focus to first interactive element upon open; restores focus to trigger upon close.

```html
<!-- Accessible Modal Structure -->
<div role="dialog" 
     id="confirmation-modal" 
     aria-modal="true" 
     aria-labelledby="modal-title" 
     aria-describedby="modal-desc" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
  
  <div class="bg-white dark:bg-slate-800 rounded-xl p-6 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700">
    <h2 id="modal-title" class="text-lg font-bold text-slate-900 dark:text-slate-100">
      Confirm Account Deletion
    </h2>
    <p id="modal-desc" class="text-sm text-slate-600 dark:text-slate-400 mt-2">
      This action is permanent and cannot be reversed. All tenant data will be permanently purged.
    </p>

    <div class="mt-6 flex justify-end gap-3">
      <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
      <button type="button" class="btn-danger" onclick="confirmDelete()">Permanently Delete</button>
    </div>
  </div>
</div>
```

### 8.2 Accessible Tab Widget Pattern
Tabs organize content into panels where only one panel is visible at a time:
- Container: `role="tablist"` with `aria-label="Section Options"`.
- Tab Buttons: `role="tab"`, `aria-selected="true|false"`, `aria-controls="panel-id"`, `id="tab-id"`, `tabindex="0"` on the active tab and `tabindex="-1"` on inactive tabs.
- Tab Panels: `role="tabpanel"`, `id="panel-id"`, `aria-labelledby="tab-id"`, `tabindex="0"` so the panel itself can be scrolled via keyboard.
- Keyboard: `ArrowRight` / `ArrowLeft` cycles between tabs (inverted in RTL); `Home` / `End` jumps to first/last tab.

---

## 9. Arabic, RTL, and Multilingual Accessibility

### 9.1 Root Direction and Language Declarations
The document root must dynamically reflect the active locale:
```html
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>لوحة التحكم | Universal Core</title>
</head>
```

### 9.2 Directional Keyboard Inversion
In horizontal tablists, slider thumbs, and carousels, the directional semantics invert in RTL:
- In **LTR**: `ArrowRight` moves to the *next* tab; `ArrowLeft` moves to the *previous* tab.
- In **RTL**: `ArrowLeft` moves to the *next* tab; `ArrowRight` moves to the *previous* tab.
Component keyboard listeners must inspect the active text direction (`document.documentElement.dir === 'rtl'`) and adjust arrow navigation accordingly.

### 9.3 Screen Reader Compatibility in Arabic
1. **Pronunciation & Numbers:** Ensure phone numbers, dates, and serial IDs are wrapped in `<bdi>` or styled with `font-variant-numeric: tabular-nums` to prevent Arabic screen readers (NVDA Eloquence or VoiceOver Arabic) from garbling mixed alphanumeric identifiers.
2. **Accessible Labels:** Never leave English `aria-label` values in an Arabic interface (e.g., an Arabic button must have `aria-label="إغلاق النافذة"`, never `aria-label="Close modal"`).

---

## 10. Comprehensive Layered Accessibility Testing Strategy

Universal Core establishes an eight-layer verification pipeline. Deliverables must pass every applicable layer before acceptance:

| Layer | Testing Scope | Primary Tooling | Pass Criteria |
| :--- | :--- | :--- | :--- |
| **1. Static Analysis** | Markup AST & JSX linting | ESLint `jsx-a11y`, `vuejs-accessibility` | 0 lint errors or warnings. |
| **2. Unit / Component** | Isolated component DOM checks | `vitest-axe`, `@testing-library/react`, Vue Test Utils | 0 automated `axe` violations on rendered states. |
| **3. Browser Automation** | Automated full-page audit | `@axe-core/playwright`, Lighthouse CI | 100% axe-core automated rule pass rate. |
| **4. Keyboard Sweep** | 100% keyboard operability | Manual keyboard (`Tab`, `Shift+Tab`, `Space`, `Enter`, `Esc`) | All controls reachable, visible focus rings, zero traps. |
| **5. Focus Obscuration** | WCAG 2.2 2.4.11 verification | Visual scroll testing with sticky headers | Focused elements never obscured by fixed/floating UI. |
| **6. Zoom & Reflow** | 200% text zoom & 400% reflow | Browser zoom at 320px viewport width | No horizontal scroll, zero clipped text, full functionality. |
| **7. Assistive Tech** | Screen reader interaction | NVDA (Windows), VoiceOver (macOS/iOS) | Names, roles, states, and errors accurately spoken. |
| **8. Manual Review** | Subjective WCAG verification | Manual audit against WCAG 2.2 AA criteria | 100% criteria validated with documented evidence. |

### 10.1 Automated Playwright Accessibility Test Example
```typescript
// tests/e2e/accessibility.spec.ts
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Accessibility Compliance Audit (WCAG 2.2 AA)', () => {
  test('User Dashboard must have zero automated axe-core violations', async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze();

    expect(accessibilityScanResults.violations).toEqual([]);
  });

  test('Modal dialog must trap focus and close on Escape key', async ({ page }) => {
    await page.goto('/invoices');
    await page.click('button#btn-create-invoice');
    
    // Verify dialog is visible and has correct accessible attributes
    const modal = page.locator('div[role="dialog"]');
    await expect(modal).toBeVisible();
    await expect(modal).toHaveAttribute('aria-modal', 'true');

    // Press Escape and verify focus returns to trigger button
    await page.keyboard.press('Escape');
    await expect(modal).toBeHidden();
    await expect(page.locator('button#btn-create-invoice')).toBeFocused();
  });
});
```

---

## 11. Accessibility Defect Management and Classification

### 11.1 Defect Severity Matrix
Accessibility defects are prioritized according to user impact:

| Severity | Definition & User Impact | Examples | Remediation SLA |
| :--- | :--- | :--- | :--- |
| **Critical (Blocker)** | Completely blocks users with disabilities from completing a core business task. No workaround exists. | Keyboard trap in a modal; missing accessible label on submit button; form errors invisible to screen readers; password field blocks paste. | Immediate blocker. Must resolve prior to release. |
| **Serious (Major)** | Severe friction or obstacle. A workaround exists but requires extraordinary effort. | Contrast ratio below 3:1 on body text; missing skip link; focus indicator completely obscured by sticky header; dragging action has no click alternative. | Resolve within current sprint. |
| **Moderate (Normal)** | Noticeable usability hindrance for assistive users. | Heading hierarchy skips a level (h2 to h4); table missing caption; missing `autocomplete` attribute on email field. | Scheduled regular maintenance. |
| **Minor (Low)** | Technical non-conformance with minimal user impact. | Redundant `aria-label` identical to subtree text; minor spacing token deviation on non-interactive badge. | Backlog refinement. |

### 11.2 Defect Documentation Template
Every accessibility issue logged must contain:
1. **WCAG Criterion:** Explicit identifier (e.g., `WCAG 2.2 SC 2.4.11 Focus Not Obscured`).
2. **User Impact:** Specific disability group affected (keyboard-only, screen reader, low-vision).
3. **Assistive Tech Tested:** Specific tool and version (e.g., NVDA 2024.1 on Firefox 128).
4. **Steps to Reproduce:** Exact keyboard or screen reader navigation sequence.
5. **Expected vs. Actual:** Programmatic state observed in accessibility tree.
6. **Remediation Code:** Concrete HTML/CSS/JS patch resolving the violation.

---

## 12. Verification Checklist

Before certifying any application view or component as WCAG 2.2 Level AA compliant:

- [ ] All images and icons have descriptive `alt` text or `aria-hidden="true"` if decorative?
- [ ] Heading hierarchy follows strict sequential levels (`<h1>` through `<h6>`) with zero skipping?
- [ ] Document landmarks (`<header>`, `<nav>`, `<main>`, `<aside>`, `<footer>`, `<search>`) present and labeled?
- [ ] Native skip link implemented as first focusable element pointing to `#main-content`?
- [ ] 100% of interactive controls reachable and operable via keyboard alone (`Tab`, `Enter`, `Space`, `Esc`)?
- [ ] Visible focus indicators provide at least 3.0:1 contrast and 2px thickness; no hidden outlines?
- [ ] Focused elements are never completely obscured by sticky headers or floating elements (2.4.11)?
- [ ] Text contrast meets minimum 4.5:1 (normal text) and 3.0:1 (large text) across light and dark modes?
- [ ] UI components and borders meet 3.0:1 non-text contrast against adjacent surfaces (1.4.11)?
- [ ] Content reflows cleanly without horizontal scrolling at 320px viewport width (400% zoom) (1.4.10)?
- [ ] Drag-and-drop or dragging interactions provide single-pointer button alternatives (2.5.7)?
- [ ] Touch/pointer targets meet minimum 24×24px with spacing, or standard 44×44px (2.5.8)?
- [ ] Password and OTP inputs permit pasting without obstruction (3.3.8)?
- [ ] Previously entered data in multi-step workflows is auto-populated or selectable (3.3.7)?
- [ ] Help mechanisms (support, docs) appear in consistent relative order across views (3.2.6)?
- [ ] Live regions (`aria-live="polite"`) announce asynchronous updates and search results?
- [ ] Form inputs programmatically linked to labels, helper text, and inline error messages?
- [ ] Dialogs trap focus, close on `Escape`, and restore focus to trigger element upon exit?
- [ ] Bidirectional attributes (`lang` and `dir`) configured correctly; Arabic translations complete?
- [ ] Automated scan with `axe-core` yields zero violations across Level A and AA rules?

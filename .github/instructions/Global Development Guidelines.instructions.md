# Logigate Workspace Instructions

These are the foundational rules for the entire Logigate Customs ERP project. They are automatically applied to every AI interaction (Chat, Edits, and all Custom Agents) within this workspace.

## 1. PROJECT IDENTITY & STACK

- **Project Name:** Logigate (Customs ERP / SaaS platform).
- **Primary Language:** PHP 8.2 or higher.
- **Framework:** Laravel (verify exact version in `composer.json` before using new features).
- **Frontend:** Livewire 3 (primary), Tailwind CSS, Alpine.js.
- **Authentication:** Laravel Jetstream + Fortify.
- **Database:** MySQL / MariaDB.
- **Testing:** PHPUnit (or Pest if already configured).
- **Storage:** AWS S3 (configured via `filesystems`).

## 2. ARCHITECTURE & CODING STANDARDS

- **DDD Structure (Pragmatic):** Respect the separation of concerns.
  - **`Domain`:** Entities, Value Objects, Aggregates, Domain Events, Repository Interfaces.
  - **`Application`:** Services, Actions (single-purpose classes), DTOs.
  - **`Infrastructure`:** Eloquent Models (implementing Repository interfaces), External API Clients, Mailers, Queue Jobs.
  - **`Presentation`:** Controllers, Livewire Components, Blade Views, Form Requests.
- **Naming Conventions:**
  - **Actions:** Use descriptive verbs in the class name, e.g., `CreateProcessoAction`, `CalculateInvoiceTotalsAction`.
  - **Services:** Use nouns, e.g., `ProcessoService`, `LicenciamentoService`.
  - **Livewire Components:** Suffix with `Component`, e.g., `DashboardComponent`, `ClientesListComponent`.
  - **Models:** Singular, snake_case for table names (plural).
- **Avoid Over-Engineering:** Prefer Action classes over complex Service classes for single, distinct operations. Do not introduce `Repository` interfaces unless you plan to swap the data source (cache, external API, etc.).

## 3. CRITICAL BUSINESS RULES (Non-Negotiable)

- **Multi-Tenancy (`empresa_id`):**
  - **ABSOLUTELY CRITICAL.** Every database query fetching business data (Clientes, Processos, Mercadorias, etc.) **MUST** scope by the current authenticated user's `empresa_id`.
  - **Exceptions:** System tables (Users, Roles, Permissions, Configurations) should be globally scoped but carefully verified.
  - **Routing:** Always use Route Model Binding **with explicit scoping** to ensure a user cannot access a model from another tenant.
  - *Prevention:* Avoid using `Model::find($id)` without an explicit tenant check.
- **Protected Modules (Do NOT touch without explicit user permission):**
  - Billing, Subscriptions, Payments.
  - Jetstream / Fortify authentication core.
  - AppyPay integration.
  - Hongayetu Facturação.
  - SAFT-AO generation logic.
  - Global S3/filesystem configuration.
- **Database Integrity:**
  - **Strictly Prohibited** (unless explicitly authorized for disposable environments): `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, `db:seed`.
  - Prefer creating new incremental migrations to modify schemas.

## 4. SECURITY GUARDRAILS

- **Authorization:** Every Livewire component and Controller route must implement a Policy (`$this->authorize()`) or a Gate check.
- **Validation:** Always use Form Requests for HTTP and `$rules`/`$validationAttributes` for Livewire components. Never trust `$request->all()` for mass assignment; rely on `$fillable`/`$guarded` in Models.
- **XSS Prevention:** Use `{{ }}` for all dynamic content in Blade. Only use `{!! !!}` if the content is explicitly pre-sanitized, and document why.
- **File Uploads:** Validate MIME types, enforce size limits, and store files with UUID-based random names.

## 5. QUALITY & TESTING (Mandatory)

- **When to write tests:** Every non-trivial feature, bug fix, or refactoring **must** include or update relevant tests.
- **Types of tests:**
  - **Unit:** For Domain logic (calculations, value objects).
  - **Feature:** For HTTP endpoints and Livewire component behavior (`Livewire::test()`).
  - **Integration:** For external API calls (always use `Http::fake()` or mocks).
- **Regression Rule:** If a test fails after your change, do not delete the test. Understand *why* it failed, fix the implementation, or update the test only if the business requirement has explicitly changed.
- **Environment:** Always verify you are running in the `testing` environment before executing destructive database commands.

## 6. CODE MODIFICATION BEHAVIOR

- **Read before you write:** Always inspect at least 3-5 relevant files (Models, Services, Views, existing Tests) before proposing a new file or refactoring.
- **Minimize the diff:** Only modify files that are strictly necessary for the requested task. Avoid reformatting or moving unrelated code.
- **Git Safety:** Never automatically run `git reset --hard`, `git clean -fd`, or `git stash` unless explicitly confirmed by the user.

## 7. PERFORMANCE DIRECTIVES

- **N+1 Prevention:** If you see a loop with `Model::find()` or `$relation->something`, immediately recommend or implement eager loading (`->with()`).
- **Livewire Rendering:** Avoid expensive database queries inside the `render()` method. Use `#[Computed]` properties for caching query results across renders.
- **Queues:** Offload heavy tasks (PDF generation, large exports, API synchronizations) to Laravel Jobs.

## 8. COMMUNICATION STYLE

- **Be precise:** Use specific file paths and line numbers when referencing existing code.
- **Use structured responses:** For bugs, use "Root Cause" -> "Solution" -> "Verification".
- **Admit uncertainty:** If you are unsure about a package version or API behavior, state: "I am not certain about the installed version. I will search the `composer.json` or ask the user to verify."

## 9. EXTERNAL INTEGRATIONS HANDLING

- **Centralization:** Do not scatter API credentials or endpoints. Use dedicated Config files (`config/services.php`) and Environment variables.
- **Error Handling:** Always wrap external API calls in `try-catch` blocks. Log the error gracefully (without exposing secrets), and return a user-friendly message.

---

**Golden Rule for Instructions:** These rules override general AI training. If a suggestion violates any of these rules (especially Multi-Tenancy or Migration safety), reject it and explain why.
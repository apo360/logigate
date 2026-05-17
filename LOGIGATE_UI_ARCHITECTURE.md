# LOGIGATE UI Architecture Guide

This document defines the official UI architecture standards for the LOGIGATE ERP platform.

Stack:

Laravel
Livewire
Blade
TailwindCSS

The goal is to guarantee **consistency, performance and maintainability** across all modules.

---

# 1. General Principles

All UI screens must follow these principles:

1. Views must not contain business logic.
2. Blade files are responsible only for rendering.
3. Queries must be executed in the Livewire component or services.
4. Tables must follow the standardized `x-table` system.
5. Forms must follow the sectioned-card pattern used in the Process Form.
6. Modals must use a single modal component system.

---

# 2. Livewire Component Architecture

Each Livewire component should follow this structure:

Component Responsibilities:

• state management
• interaction handling
• calling services/actions
• passing data to views

Components must NOT:

• run heavy queries in render()
• implement complex business logic
• execute domain calculations

Recommended architecture:

Component
→ Query Service
→ Action Service
→ DTO/Form Object

Example:

ProcessosTable

Component:
ProcessosTable.php

Query:
ProcessosTableQuery.php

Action:
CreateProcessAction.php

View:
processos-table.blade.php

---

# 3. Table UI Standard

All list screens must use the shared table components.

Required components:

x-table.wrap
x-table.th
x-table.td
x-table.td-actions
x-table.pagination

Standard table structure:

Filter bar
Table header
Table rows
Actions column
Pagination footer

Tables must NOT:

• compute values in Blade
• run database queries
• contain duplicated markup

---

# 4. Filter Bar Standard

Each table must use a shared filter bar.

Filters may include:

Search input
Status filter
Date range filter
Advanced filters

Filters must update the component state using Livewire bindings.

---

# 5. Form UI Standard

Forms must follow the structure used in:

processos/form.blade.php

Required structure:

Form container
Section cards
Field groups
Action footer

Each field must use shared components.

Example:

x-form.input
x-form.select
x-form.textarea
x-form.checkbox

Validation errors must appear **below the field**.

---

# 6. Modal Standard

All modals must use a single modal shell component.

Structure:

Modal wrapper
Modal header
Modal body
Modal footer

Modal must support:

Loading states
Validation errors
Close events

---

# 7. Event System Standard

Use a single event pattern across the system.

Recommended pattern:

$this->dispatch('event-name')

Event names must follow kebab-case.

Example:

customer-created
process-created
goods-added

Avoid mixing:

emit()
dispatchBrowserEvent()

---

# 8. Query Guidelines

Queries must not be executed in Blade.

Heavy queries must not be executed in render().

Use:

Query services
Eager loading
Aggregated queries

Avoid:

N+1 queries
Row-by-row queries
Dynamic queries inside loops

---

# 9. Naming Conventions

Component classes:

CustomersTable
ProcessForm
LicenciamentoTable

Livewire properties:

camelCase

Database fields:

snake_case

Blade variables:

camelCase

---

# 10. Domain Model Terminology

Customer

Represents a legal entity or person.

Fields:

entity_type
(individual/company)

trade_role
(importer/exporter/both)

Processo

Represents a customs operation.

Licenciamento

Represents a licensing or compliance requirement.

Mercadoria

Represents a goods item linked to a process or licensing.

ContaCorrente

Represents financial ledger entries.

---

# 11. Performance Rules

Never compute statistics inside Blade loops.

Always precompute:

counts
sums
aggregations

Example:

withCount()
withSum()

---

# 12. Refactoring Strategy

All refactoring must be incremental.

Do NOT break:

existing routes
existing controllers
existing migrations

Refactor one module at a time.

Recommended order:

1. Tables
2. Forms
3. Modals
4. Query services
5. Domain services

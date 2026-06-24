# TODO

## Phase 3 (Refined) — Implementation plan

### Step 0 — Repo discovery
- [ ] Re-run repository reading: locate existing document upload/storage models, controllers, routes, S3 config, and any current IDs/keys usage.
- [ ] Identify existing DB schema related to documents and the current access gates for internal vs customer portal users.

### Step 1 — S3 architecture implementation
- [ ] Define S3 prefix strategy in configuration (Customer/Processos, Customer/Licenciamentos, Customer/Facturas, Customer/Comprovativos).
- [ ] Create/adjust storage service to write to the target prefixes.

### Step 2 — `documentos_arquivos` as single source of truth
- [ ] Create/adjust migration + model `documentos_arquivos`.
- [ ] Ensure all document access and metadata are read from `documentos_arquivos`.

### Step 3 — UUID-based document access + remove raw S3 keys
- [ ] Implement download/preview endpoints that accept `uuid` and use policies.
- [ ] Remove/flag any endpoints that leak raw S3 keys.

### Step 4 — Authorization rules
- [ ] Implement `DocumentoArquivoPolicy` (internal vs customer portal).
- [ ] Implement gates/services where needed.

### Step 5 — Document lifecycle
- [ ] Implement lifecycle transitions and states: created, available, visible_to_customer, archived, deleted.
- [ ] Ensure query scopes for portal visibility.

### Step 6 — Routes policy compatibility
- [ ] Keep and adapt current portal routes (no UI change).
- [ ] Deprecate legacy routes (id-based / s3-key based) behind feature flags.

### Step 7 — Migration/backfill strategy
- [ ] Implement backfill command for existing S3 objects without `documentos_arquivos`.
- [ ] Validate mapping rules (customer/entity_type/entity_uuid) and generate lifecycle defaults.

### Step 8 — Tests
- [ ] Authorization tests matrix.
- [ ] S3/storage tests using fakes.
- [ ] Customer portal access tests (list/download/preview).

### Step 9 — Deliverables
- [ ] Produce/commit:
  - [ ] S3 architecture report
  - [ ] Security report
  - [ ] Portal readiness report


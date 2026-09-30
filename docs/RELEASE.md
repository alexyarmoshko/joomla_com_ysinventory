# Release Notes

## 1.0.1

### Packaging and updates

- Update feed download URL fixed: it pointed at `releases/download/v1.0.0/`, which does not exist, so Joomla could not download 1.0.0 as an update
- Update server moved to `joomla_update_system`; this repository's `com_ysinventory.update.xml` stays as a feed for sites still on 1.0.0
- Reproducible release packages: built from the tag with a vendored deterministic packager (`tools/jzip.php`), so a rebuild from the tag matches the published `sha256`
- `make release`, `make dist_release` and `make dist_dev` replace `make dist`; dev packages drop `<updateservers>`

## 1.0.0

Initial release.

### Inventory management

- Inventories with name, alias, description, and owner (Joomla user or contact XOR)
- Component-local categories with nested-set hierarchy and per-category ACL
- Brands with name, alias, description, and image
- Tag groups and tags with group-tag relationships

### Item catalog

- Items with name, description, image, model, serial number, SKU, quantity, and references to inventory, category, brand, and location
- Many-to-many item-tag associations
- Admin CRUD with search, filters, ordering, and pagination
- Frontend catalog with filtering (category, inventory, tag, location, brand), search, and pagination
- Access-level filtering on all frontend queries

### Lending workflow

- Loan records with item reference, loanee, date range, note, and status tracking
- Status lifecycle: Requested, On Loan, Returned, Lost, Returned Damaged, Returned Overdue, Cancelled, Denied
- Allowed transitions: Requested -> On Loan|Cancelled|Denied; On Loan -> Returned|Lost|Returned Damaged|Returned Overdue
- Configurable request and moderation groups (global and per-category)
- Transactional stock guard on loan approval
- Admin and frontend loan management with status badges
- Append-only audit journal (`#__ysi_lends_log`) for loan updates and deletes

### Frontend views

- Menu item metadata for all 9 frontend views (brands, brand, categories, category, tags, tag, items, item, lends)
- Joomla breadcrumb support across all frontend views
- Borrowings tab on item detail with server-side pagination

### Infrastructure

- Joomla 5.4 MVC architecture (namespaced, DI-wired)
- PSR-12 coding standards
- Joomla ACL integration with component and per-category permissions
- Full localization via language files (admin and site)
- Centralized helper classes (`StatusHelper`, `ModeratorHelper`) eliminating cross-boundary dependencies and code duplication
- Makefile for build packaging

# Release Notes

## v1.0.6 (in progress)

### Phase 1 — Bootstrap and installability baseline

- Component scaffold with Joomla 5 MVC architecture (namespaced, DI-wired)
- Root manifest (`ysinventory.xml`)
- Service provider with MVCFactory and ComponentDispatcherFactory
- Extension class (`YsinventoryComponent`) extending `MVCComponent`
- Admin dashboard view with toolbar
- Admin and site `DisplayController`
- Language files (admin `.ini`, `.sys.ini`, site `.ini`)
- SQL install/uninstall scripts and schema version baseline
- ACL `access.xml` with standard Joomla component actions
- Makefile for repeatable build packaging

### Phase 2 — Inventory entity admin CRUD

- `#__ysi_inventories` table with name, alias, description, inventory owner reference, published state, ordering, and timestamps
- Full admin CRUD: Table class, AdminModel, ListModel, FormController, AdminController
- Admin list view with search, status filter, drag ordering, pagination, and empty state
- Admin edit form with tabbed layout (details: name/alias/owner/description, publishing: dates/created-by)
- Submenu entry for Inventories
- Inventory owner supports logical XOR: exactly one of Joomla `user` or Joomla `contact`

### Phase 3 — Component-local category subsystem

- `#__ysi_categories` table with nested-set hierarchy (parent_id, lft, rgt, level, path), asset-based Joomla ACL, access level, metadata, and audit fields
- Admin CRUD with hierarchy-aware form (parent selector, indented list view, drag ordering, rebuild action)
- Per-category ACL permissions (create, delete, edit, edit.state, edit.own) via `access.xml`
- CategoryTable extending `Joomla\CMS\Table\Nested` with asset management and descendant path rebuild
- Frontend category list with per-category item counts and category-to-item click-through navigation
- Breadcrumb support for category hierarchy on frontend views

### Phase 4 — Brands

- `#__ysi_brands` table with name, alias, description (editor), image (media picker), published state, ordering, and timestamps
- Full admin CRUD with brand list (image thumbnails) and edit form (tabbed layout)
- Frontend brand list with per-brand item counts and brand-to-item click-through navigation
- Single brand view with heading, image, description, and item listing

### Phase 5 — Tag groups and tags

- `#__ysi_tag_groups` and `#__ysi_tags` tables with group-tag relationship (each tag belongs to one group)
- Admin CRUD for both tag groups and tags with search, filters, and ordering
- Tag form fields: name, alias, description (Joomla editor)
- Frontend grouped tag list with per-tag item counts and tag-to-item click-through navigation
- Single tag view with tag heading and item listing

### Phase 6 — Items and catalog browsing

- `#__ysi_items` table with name, alias, description, image, model, serial number, SKU, quantity, and FK references to inventory, category, brand, and location (Joomla user)
- `#__ysi_item_tag_map` many-to-many join table for item-tag associations
- Full admin CRUD with item list (search, filters, ordering, pagination) and tabbed edit form (details, tags, publishing)
- Multi-select tag picker in admin item form (grouped by tag group)
- Frontend item list with pagination, items-per-page selector, and filters (category, inventory, tag, location, brand)
- Frontend item detail page with image, description, metadata table (model, serial number, SKU, quantity, brand, category, inventory, location), and associated tags
- Access-level filtering on all frontend item queries

### Phase 7 — Lending workflow

- `#__ysi_lends` table with item FK, user reference, from/to dates, note, status (Requested/Borrowed/Returned/Lost), and audit fields
- Full admin CRUD: LendTable with status transition enforcement (Requested->Borrowed->Returned|Lost) and stock guard on Borrowed transition
- Admin list view with colored status badges (warning/primary/success/danger), item name, requester, date range, and creation date
- Admin edit form with tabbed layout (details: item selector, user picker, note, status, dates; publishing: audit fields)
- Component configuration for lending: request groups and moderation groups (usergrouplist, multi-select)
- Lend ACL section in `access.xml` (core.create, core.delete, core.edit, core.edit.state)
- Site-side lend request controller with CSRF, login, group, item, date, and stock validation
- Frontend item detail page shows available stock and conditional lend request form for authorized users
- Stock guard prevents approving borrows when all units are lent out
- Borrowings (Loan History) tab now uses Joomla standard server-side pagination (`getListFooter`) with consistent "Per page" control
- Fixed frontend loan edit form loading by packaging `site/forms` and adding form-path fallback in site `LendModel`
- Fixed frontend loan new/edit form reliability and localization:
  - Corrected site lend edit submit routing/context (`view=lend`, `id`, `Itemid`) so edits persist to the intended record
  - Added missing site lend language keys for form descriptions/placeholders to remove raw key output
- Updated frontend loans management UX:
  - Added explicit edit action in site loans list
  - Removed asset detail-page link from site loans list asset column
- Updated loanee selection policy across site and admin lend forms:
  - Site loanee selector uses searchable fancy-select SQL list with person name only
  - Site loan edit locks Asset and Loanee fields (editable on New Loan only)
  - Loanee options are filtered to users in effective `ysi_lend_request_groups` (category override, global fallback) in both site and admin lend forms

### Cross-phase — Frontend menu integration

- Menu item metadata XML for all 8 frontend views (brands, brand, categories, category, tags, tag, items, item)
- Joomla Menu Manager integration with view-specific menu item types and ID selectors for single-entity views
- Site default view set to items catalog for proper fallback routing
- Menu type language keys for titles, options, and descriptions

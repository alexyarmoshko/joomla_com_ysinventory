# Yak Shaver Inventory (`com_ysinventory`)

Joomla 5.4 component for managing inventories, assets, and loans.

## Current Status

- Version: **1.0.0**
- Release state: **in progress**
- Package output: `installation/com_ysinventory-v1-0-0.zip`

## UI Terminology

The UI uses the following business terms consistently:

- **Asset** (entity/table name in code is still `item` in many places)
- **Loan** (historically referred to as lend/lending in some internal identifiers)
- **Loan Register** (loan list/management view)
- **Loan History** (asset detail tab with borrowing history)

## Implemented Features

### Admin

- Inventory CRUD
- Inventory owner is required as XOR: exactly one of Joomla User or Joomla Contact
- Component-local Categories with nested-set hierarchy and ACL-aware behavior
- Brands CRUD
- Tag Groups and Tags CRUD
- Assets CRUD with links to Inventory, Category, Brand, Location (Joomla User), and tags
- Loan Register CRUD and moderation workflow

### Frontend

- List/detail views for Brands, Categories, Tags, and Assets
- Asset detail tabs for Details, Loan, and Loan History
- Asset and loan list filtering, search, ordering, and pagination
- Group-based access control for loan request and moderation flows

### Loan Workflow

- Loan request submission from asset detail page
- Status lifecycle: Requested, On Loan, Returned, Lost, Returned Damaged, Returned Overdue, Cancelled, Denied
- Allowed transitions: Requested -> On Loan|Cancelled|Denied; On Loan -> Returned|Lost|Returned Damaged|Returned Overdue
- Category-level configuration with fallback to global component settings
- Transactional stock guard on loan approval (`On Loan` transition)
- Append-only loan journal table (`#__ysi_lends_log`) for update/delete snapshots

## Requirements

- Joomla **5.4.x**
- PHP **8.3+**
- MySQL **8.0+** (`utf8mb4`)

## Installation

1. Build or obtain the component zip (for example, `installation/com_ysinventory-v1-0-0.zip`).
2. In Joomla Administrator, open **System -> Install -> Extensions**.
3. Upload the zip and complete installation.

## Build From Source

```bash
make info
make dist
make clean
```

- `make info`: show package metadata (name/version/output path)
- `make dist`: build extension package in `installation/`
- `make clean`: remove the built package for the current version

## Documentation

- [Release Notes](docs/RELEASE.md)

## License

GNU General Public License v2 or later. See [LICENSE](LICENSE).

## Author

Yak Shaver - [kayakshaver.com](https://www.kayakshaver.com)

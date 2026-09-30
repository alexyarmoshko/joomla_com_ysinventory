# Yak Shaver Inventory (`com_ysinventory`)

Joomla 5.4 component for managing inventories, assets, and loans.

## Current Status

- Version: **1.0.1**
- Release package: `installation/release/com_ysinventory-v1-0-1.zip`

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

1. Download the zip from the GitHub release, or build it (see below).
2. In Joomla Administrator, open **System -> Install -> Extensions**.
3. Upload the zip and complete installation.

## Build From Source

Packages are reproducible: a release zip is built from its git tag with the vendored packager `tools/jzip.php`, and every entry is stamped with the tag's commit date. Rebuilding a tag gives the same bytes and the same `sha256` on any machine whose PHP uses stock zlib; a different deflate implementation such as zlib-ng produces different bytes.

Building needs git, GNU make, a POSIX shell with `tar` and `awk`, `sha256sum` or `shasum`, and PHP 8.3 with the zlib and SimpleXML extensions.

```bash
make info          # version, package file count, output paths, download URL
make lint          # php -l on every packaged PHP file, well-formedness of every XML file
make dist_dev      # test package from the working tree, without <updateservers>
make release       # checks, then tags the manifest version
make dist_release  # builds the release zip from the tag and generates its update descriptor
make clean
```

`PACKAGE_FILES` in the `Makefile` is the explicit list of what ships. Add a new source file there, or the package will not contain it.

### Cutting a release

1. Bump `<version>` in `ysinventory.xml` and in `tools/com_ysinventory.update.xml`, add a `## <version>` heading to `docs/RELEASE.md`, and commit.
2. `make release`, then `make dist_release` with the tag checked out.
3. Create the GitHub release for the tag and upload `installation/release/com_ysinventory-v<X-Y-Z>.zip`.
4. Copy `installation/release/com_ysinventory.update.xml` to `joomla_update_system/manifests/`, commit it there and push that repository to GitHub.
5. Copy the same file over `com_ysinventory.update.xml` in this repository's root and commit it. Sites still on 1.0.0 read that file from GitHub `main`, so it reaches them only after `sync_public.py` and a push of the public mirror. Joomla switches a site to the new update server when it installs 1.0.1, so this file can be retired once no 1.0.0 sites remain.

Do not publish the descriptor before its zip is uploaded, or sites will be offered a download that returns 404.

## Documentation

- [Release Notes](docs/RELEASE.md)

## License

GNU General Public License v2 or later. See [LICENSE](LICENSE).

## Author

Yak Shaver - [kayakshaver.com](https://www.kayakshaver.com)

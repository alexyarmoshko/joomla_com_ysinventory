# Release Notes

## v1.0.0 (in progress)

### Phase 1 — Bootstrap and installability baseline

- Component scaffold with Joomla 5 MVC architecture (namespaced, DI-wired)
- Root manifest (`ysinventory.xml`) v1.0.0
- Service provider with MVCFactory and ComponentDispatcherFactory
- Extension class (`YsinventoryComponent`) extending `MVCComponent`
- Admin dashboard view with toolbar
- Admin and site `DisplayController`
- Language files (admin `.ini`, `.sys.ini`, site `.ini`)
- SQL install/uninstall scripts and schema version baseline
- ACL `access.xml` with standard Joomla component actions
- Makefile for repeatable build packaging
- Installation artifact: `installation/com_ysinventory-v1-0-0.zip`

### Phase 2 - Inventory entity admin CRUD

- `#__ysi_inventories` table with name, alias, description, inventory owner reference, published state, ordering, and timestamps
- Full admin CRUD: Table class, AdminModel, ListModel, FormController, AdminController
- Admin list view with search, status filter, drag ordering, pagination, and empty state
- Admin edit form with tabbed layout (details: name/alias/owner/description, publishing: dates/created-by)
- Submenu entry for Inventories
- Inventory owner now supports logical XOR: exactly one of Joomla `user` or Joomla `contact`

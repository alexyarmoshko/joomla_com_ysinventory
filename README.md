# Yak Shaver Inventory (com_ysinventory)

A Joomla 5.4 component for inventory management and lending.

## Features

- Inventory management with categories, brands, locations, and tags
- Inventory owner selection supports exactly one of Joomla user or Joomla contact (XOR)
- Item catalog with filtering, search, and pagination
- Lending workflow with request/moderation and stock enforcement
- Configurable permissions via Joomla user groups

## Requirements

- Joomla 5.4.x
- PHP 8.3+
- MySQL 8.0+ (utf8mb4)

## Installation

1. Download the latest release zip from `installation/`
2. In Joomla Administrator, go to **System > Install > Extensions**
3. Upload the zip file and install

## Building from Source

```bash
make info    # Show package details
make dist    # Build the installation zip
make clean   # Remove the built zip
```

The build output is placed in `installation/com_ysinventory-v<version>.zip`.

## Development Status

This component is under active development in phased increments:

- **Phase 1**: Bootstrap and installability baseline
- **Phase 2**: Inventory entity admin CRUD
- **Phase 3**: Component-local categories
- **Phase 4**: Brands and locations
- **Phase 5**: Tag groups and tags
- **Phase 6**: Items and catalog browsing
- **Phase 7**: Lending workflow
- **Phase 8**: Release and documentation

See the design notes for full details.

## License

GNU General Public License version 2 or later. See [LICENSE](LICENSE).

## Author

Yak Shaver — [kayakshaver.com](https://www.kayakshaver.com)

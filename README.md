# Yak Shaver Inventory (com_ysinventory)

A Joomla 5.4 component for inventory management and lending.

## Features

- Inventory management with categories, brands, and tags
- Inventory owner selection supports exactly one of Joomla user or Joomla contact (XOR)
- Item catalog with filtering, search, and pagination
- Lending workflow with request/moderation and stock enforcement
- Loan History uses Joomla standard list pagination controls ("Per page" + page links)
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

## Version

Current release: **v1.0.0**

See [RELEASE.md](docs/RELEASE.md) for release notes and the design notes for design details.

## License

GNU General Public License version 2. See [LICENSE](LICENSE).

## Author

Yak Shaver — [kayakshaver.com](https://www.kayakshaver.com)

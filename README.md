# YOYAKU Shipping Icons

WordPress/WooCommerce plugin that automatically injects carrier logos in front of shipping method labels at checkout.

## Features

- Automatic logo injection for shipping methods
- Supports multiple carriers: FedEx, UPS, Chronopost, Colissimo, Spring GDS
- Automatic sorting by price (cheapest first, pickup last)
- Respects user selection (no re-sorting after user chooses)
- Compatible with all WooCommerce shipping plugins

## Supported Carriers

| Carrier | Logo File | Pattern Matching |
|---------|-----------|------------------|
| FedEx | `fedex-logo.png` | `fedex`, `fedex®`, `fedex priority`, `fedex first`, etc. |
| UPS | `ups-logo.png` | `ups`, `ups standard`, `ups express`, `ups worldwide saver` |
| UPS WWE | `ups-wwe.png` | `ups worldwide economy` |
| Chronopost | `chronopost-logo.png` | `chronopost` |
| Colissimo | `colissimo-logo.png` | `colissimo` |
| Spring GDS | `spring-gds.png` | `spring gds` |

## Installation

1. Upload the `yoyaku-shipping-icons` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Logos will automatically appear on checkout

## Adding New Carriers

1. Add logo PNG to `/assets/` folder (recommended: 100-150px width, transparent background)
2. Edit `yoyaku-shipping-icons.php` and add pattern to `$patterns` array:

```php
$patterns = array(
    "new carrier name" => "new-carrier-logo.png",
    // ... existing patterns
);
```

## Logo Specifications

- **Format:** PNG with transparent background
- **Size:** Height ~50px, width proportional (max ~150px)
- **Quality:** High resolution, no compression artifacts

## Changelog

### 1.7.0 (2025-11-25)
- Added FedEx carrier support with all service variants
- Added FedEx purple logo

### 1.6.3 (2025-09-18)
- Fixed sorting to respect user selection
- Improved pickup detection (supports "retrait", "pick up", "pickup")

### 1.6.2 (2025-08-18)
- Added Delivengo exclusion for checkout (logo only in simulator)

### 1.6.1 (2025-08-17)
- Fixed label normalization for special characters

### 1.0.0 (2025-05-07)
- Initial release with UPS, Chronopost, Colissimo, Spring GDS support

## Files Structure

```
yoyaku-shipping-icons/
├── assets/
│   ├── chronopost-logo.png
│   ├── colissimo-logo.png
│   ├── fedex-logo.png
│   ├── spring-gds.png
│   ├── ups-logo.png
│   └── ups-wwe.png
├── yoyaku-shipping-icons.php
└── README.md
```

## Author

**Benjamin Belaga**
- GitHub: [@benjaminbelaga](https://github.com/benjaminbelaga)
- Website: [yoyaku.io](https://yoyaku.io)

## License

GPL-2.0-or-later

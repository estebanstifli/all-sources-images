# New Admin UI - Documentation

## Overview

This folder contains the redesigned admin UI for All Sources Images plugin. The new UI is structured into three main menu items:

1. **New Settings** - Manual/block editor image sources configuration
2. **New Automatic** - Automatic generation settings (bulk, cron, integrations)
3. **New Bulk Generation** - Placeholder for bulk/cron features

## File Structure

```
admin/partials/new-ui/
├── index.php                  # Security file
├── new-ui-loader.php          # Main entry point - include this to activate
├── new-ui-menus.php           # WordPress menu registration
├── new-ui-assets.php          # CSS/JS enqueuing
├── new-ui-styles.css          # Custom styles
├── new-settings.php           # New Settings main container
├── new-automatic.php          # New Automatic main container
├── new-bulk-generation.php    # Bulk Generation placeholder
└── tabs/
    ├── index.php
    ├── new-settings-source.php           # Manual sources (Gutenberg/Elementor)
    ├── new-settings-proxy.php            # Proxy configuration
    ├── new-settings-others.php           # Block settings + Logs
    ├── new-automatic-sources.php         # Automatic sources selection
    ├── new-automatic-plugins.php         # Plugin compatibility
    ├── new-automatic-image-placement.php # Featured/Inline placement
    ├── new-automatic-post-processing.php # Image modifications, ALT tags
    └── new-automatic-pre-processing.php  # Hooks, intervals, search mode
```

## How to Activate

### Option 1: Include in plugin main file

Add this line in `all-sources-images.php` after plugin initialization:

```php
// Load new admin UI
require_once plugin_dir_path( __FILE__ ) . 'admin/partials/new-ui/new-ui-loader.php';
```

### Option 2: Include in admin class

Add this line in `class-all-sources-images-admin.php` constructor:

```php
// Load new admin UI
require_once plugin_dir_path( __FILE__ ) . 'partials/new-ui/new-ui-loader.php';
asi_init_new_ui( $this );
```

### Option 3: Via functions.php or mu-plugin

```php
add_action( 'plugins_loaded', function() {
    if ( defined( 'ALL_SOURCES_IMAGES_VERSION' ) ) {
        require_once WP_PLUGIN_DIR . '/all-sources-images/admin/partials/new-ui/new-ui-loader.php';
    }
});
```

## Menu Structure

### New Settings (`asi-new-settings`)
- **Source Tab**: Select manual image sources for Gutenberg block and Elementor
- **Proxy Tab**: Configure proxy settings
- **Others Tab**: Block display settings, ALT tags, Logs enable

### New Automatic (`asi-new-automatic`)
- **Sources Tab**: Select automatic image sources for bulk/cron/integrations
- **Plugins Tab**: Compatibility with WPeMatico, FeedWordPress, WP All Import, etc.
- **Image Placement Tab**: Featured image vs inline content configuration
- **Post-Processing Tab**: Naming, flip, crop, ALT, caption settings
- **Pre-Processing Tab**: Save post hook, WP insert post hook, interval settings

### New Bulk Generation (`asi-new-bulk-generation`)
- Placeholder page for future bulk generation interface
- Links to existing posts list bulk actions

## Notes

- All files are NEW - no existing PHP files were modified
- Uses existing WordPress options (`ASI_plugin_*_settings`)
- Compatible with existing admin class methods
- Responsive design with custom CSS
- Arrow prefix (→) on menu items to distinguish from existing menus

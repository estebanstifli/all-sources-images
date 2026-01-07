# All Sources Images - AI Coding Agent Guide

## Project Overview

WordPress plugin for automatic image generation/retrieval from multiple sources (DALL·E, Stable Diffusion, Gemini, Pexels, Unsplash, Pixabay, etc.). Sets featured images or inserts into post content via Gutenberg blocks and bulk operations.

**Version**: 1.0.0 | **PHP**: 7.3+ | **WordPress**: 6.0+  
**Entry Point**: `all-sources-images.php`

## Architecture

### Core Class Hierarchy

```
All_Sources_Images (includes/class-all-sources-images.php)     ? Main orchestrator
+-- All_Sources_Images_Admin (admin/class-all-sources-images-admin.php) ? Settings, UI
¦   +-- All_Sources_Images_Generation (admin/class-all-sources-images-generation.php) ? Extends Admin
+-- ALLSI_Source_Manager (admin/sources/class-allsi-source-manager.php) ? Modular source registry
+-- ALLSI_Bulk_Generation_DB/Ajax/Cron (admin/includes/)        ? Database-backed bulk system
+-- ALLSI_Plugin_Integrations (admin/class-allsi-plugin-integrations.php) ? Third-party hooks
```

### Image Source Pattern (Strategy Pattern)

Each image bank is a class extending `ALLSI_Image_Source` (`admin/sources/`):

```php
// Example: admin/sources/class-allsi-source-pixabay.php
class ALLSI_Source_Pixabay extends ALLSI_Image_Source {
    public function get_slug() { return 'pixabay'; }
    public function generate( array $context ) { /* API call logic */ }
    public function is_available() { return true; }
}
```

Sources registered via `ALLSI_Source_Manager::register_source()`. The generation class lazily loads sources in `ALLSI_get_source_manager_instance()`.

**Available Sources**: `gemini`, `workers_ai`, `pexels`, `pixabay`, `flickr`, `giphy`, `stability`, `replicate`, `google_image`, `youtube`, `dallev1`, `unsplash`, `openverse`

### Settings Structure

All options use `wp_parse_args()` with defaults from `ALLSI_default_options_*_settings()`:

| Option Key | Purpose |
|------------|---------|
| `ALLSI_plugin_main_settings` | Core config, `image_block` array for multiple images/post |
| `ALLSI_plugin_banks_settings` | API credentials per source |
| `ALLSI_plugin_cron_settings` | Scheduled generation |
| `ALLSI_plugin_compatibility_settings` | Third-party integrations |
| `ALLSI_plugin_rights_settings` | User role permissions |

### Image Generation Flow

1. **Entry**: `ALLSI_ajax_call()` or `save_post` hook ? `ALLSI_check_post_type()`
2. **Core**: `ALLSI_create_thumb()` - 12+ parameters, iterates image blocks
3. **Source Resolution**: `ALLSI_get_source_manager_instance()` ? `$source->generate($context)`
4. **Download**: Source returns `['url_results', 'file_media', 'alt_img', 'caption_img']`
5. **Attach**: WordPress media library integration, set featured or insert in content

### Bulk Generation System

Database-backed (`wp_ALLSI_bulk_jobs`, `wp_ALLSI_bulk_posts` tables):
- `ALLSI_Bulk_Generation_DB` - CRUD operations, table schema
- `ALLSI_Bulk_Generation_Ajax` - AJAX handlers (`ALLSI_bulk_*` actions)
- `ALLSI_Bulk_Generation_Cron` - Background processing

## Development Workflows

### Adding New Image Source

1. Create `admin/sources/class-allsi-source-{slug}.php` extending `ALLSI_Image_Source`
2. Implement `get_slug()`, `generate(array $context)`, optionally `is_available()`
3. Register in `All_Sources_Images_Generation::ALLSI_register_builtin_sources()`
4. Add settings UI in `admin/partials/tabs/banks/{slug}.php`
5. Update `ALLSI_default_options_banks_settings()` with defaults

### Debugging

Use built-in helpers from `includes/allsi-helpers.php`:
```php
ALLSI_log( 'Message', 'CONTEXT' );           // To debug.log when ALLSI_DEBUG=true
ALLSI_log_entry( 'function_name', $args );   // Trace function entry
ALLSI_log_error( 'Error message', $exception ); // With stack trace
```

Monolog for production: `$this->ALLSI_monolog_call()->info()` ? `wp-content/uploads/all-sources-images/logs/`

### Testing API Sources

Each source has test button in admin settings. Test flow:
1. User clicks "Test API" in `admin/partials/tabs/banks/{source}.php`
2. JS in `admin/js/source.js` sends AJAX request
3. Source's `generate()` called with test context

## Naming Conventions

| Element | Pattern | Example |
|---------|---------|---------|
| Methods | `ALLSI_` prefix | `ALLSI_create_thumb()`, `ALLSI_ajax_call()` |
| Options | `ALLSI_plugin_{category}_settings` | `ALLSI_plugin_banks_settings` |
| Sources | `ALLSI_Source_{Name}` | `ALLSI_Source_Pixabay` |
| AJAX actions | `ALLSI_bulk_*`, `ALLSI_generate_image` | `wp_ajax_ALLSI_bulk_create_job` |
| Nonces | `ALLSI_bulk_nonce`, `ajax_nonce_All_Sources_Images` | |

## Key Files

| File | Purpose |
|------|---------|
| `admin/class-all-sources-images-generation.php` | Core generation logic (~2460 lines) |
| `admin/sources/class-allsi-image-source.php` | Abstract source base class |
| `admin/sources/class-allsi-source-manager.php` | Singleton source registry |
| `admin/includes/class-allsi-bulk-generation-*.php` | Bulk system (DB, Ajax, Cron) |
| `admin/js/bulk-generation.js` | Frontend bulk UI |
| `admin/partials/new-ui/` | Redesigned admin UI (opt-in) |

## Common Tasks

**Update Version**: `all-sources-images.php` ? `ALL_SOURCES_IMAGES_VERSION` constant + PHPDoc header + `README.txt`

**Add Third-Party Integration**: 
1. Add setting to `ALLSI_plugin_compatibility_settings`
2. Hook in `ALLSI_Plugin_Integrations::init_hooks()`
3. Implement handler method calling `$generation->ALLSI_create_thumb()`

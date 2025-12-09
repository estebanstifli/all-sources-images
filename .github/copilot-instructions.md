# All Sources Images - AI Coding Agent Guide

## Project Overview

WordPress plugin for automatic image generation/retrieval from multiple sources (DALL·E, Stable Diffusion, Gemini, Pexels, Unsplash, Pixabay, etc.). Sets featured images or inserts into post content via Gutenberg blocks and bulk operations.

**Version**: 1.0.0 | **PHP**: 7.3+ | **WordPress**: 6.0+  
**Entry Point**: `all-sources-images.php`

## Architecture

### Core Class Hierarchy

```
All_Sources_Images (includes/class-all-sources-images.php)     → Main orchestrator
├── All_Sources_Images_Admin (admin/class-all-sources-images-admin.php) → Settings, UI
│   └── All_Sources_Images_Generation (admin/class-all-sources-images-generation.php) → Extends Admin
├── ASI_Source_Manager (admin/sources/class-asi-source-manager.php) → Modular source registry
├── ASI_Bulk_Generation_DB/Ajax/Cron (admin/includes/)        → Database-backed bulk system
└── ASI_Plugin_Integrations (admin/class-asi-plugin-integrations.php) → Third-party hooks
```

### Image Source Pattern (Strategy Pattern)

Each image bank is a class extending `ASI_Image_Source` (`admin/sources/`):

```php
// Example: admin/sources/class-asi-source-pixabay.php
class ASI_Source_Pixabay extends ASI_Image_Source {
    public function get_slug() { return 'pixabay'; }
    public function generate( array $context ) { /* API call logic */ }
    public function is_available() { return true; }
}
```

Sources registered via `ASI_Source_Manager::register_source()`. The generation class lazily loads sources in `ASI_get_source_manager_instance()`.

**Available Sources**: `gemini`, `workers_ai`, `pexels`, `pixabay`, `flickr`, `giphy`, `stability`, `replicate`, `google_image`, `youtube`, `dallev1`, `unsplash`, `openverse`

### Settings Structure

All options use `wp_parse_args()` with defaults from `ASI_default_options_*_settings()`:

| Option Key | Purpose |
|------------|---------|
| `ASI_plugin_main_settings` | Core config, `image_block` array for multiple images/post |
| `ASI_plugin_banks_settings` | API credentials per source |
| `ASI_plugin_cron_settings` | Scheduled generation |
| `ASI_plugin_compatibility_settings` | Third-party integrations |
| `ASI_plugin_rights_settings` | User role permissions |

### Image Generation Flow

1. **Entry**: `ASI_ajax_call()` or `save_post` hook → `ASI_check_post_type()`
2. **Core**: `ASI_create_thumb()` - 12+ parameters, iterates image blocks
3. **Source Resolution**: `ASI_get_source_manager_instance()` → `$source->generate($context)`
4. **Download**: Source returns `['url_results', 'file_media', 'alt_img', 'caption_img']`
5. **Attach**: WordPress media library integration, set featured or insert in content

### Bulk Generation System

Database-backed (`wp_asi_bulk_jobs`, `wp_asi_bulk_posts` tables):
- `ASI_Bulk_Generation_DB` - CRUD operations, table schema
- `ASI_Bulk_Generation_Ajax` - AJAX handlers (`asi_bulk_*` actions)
- `ASI_Bulk_Generation_Cron` - Background processing

## Development Workflows

### Adding New Image Source

1. Create `admin/sources/class-asi-source-{slug}.php` extending `ASI_Image_Source`
2. Implement `get_slug()`, `generate(array $context)`, optionally `is_available()`
3. Register in `All_Sources_Images_Generation::ASI_register_builtin_sources()`
4. Add settings UI in `admin/partials/tabs/banks/{slug}.php`
5. Update `ASI_default_options_banks_settings()` with defaults

### Debugging

Use built-in helpers from `includes/asi-helpers.php`:
```php
ASI_log( 'Message', 'CONTEXT' );           // To debug.log when ASI_DEBUG=true
ASI_log_entry( 'function_name', $args );   // Trace function entry
ASI_log_error( 'Error message', $exception ); // With stack trace
```

Monolog for production: `$this->ASI_monolog_call()->info()` → `wp-content/uploads/all-sources-images/logs/`

### Testing API Sources

Each source has test button in admin settings. Test flow:
1. User clicks "Test API" in `admin/partials/tabs/banks/{source}.php`
2. JS in `admin/js/source.js` sends AJAX request
3. Source's `generate()` called with test context

## Naming Conventions

| Element | Pattern | Example |
|---------|---------|---------|
| Methods | `ASI_` prefix | `ASI_create_thumb()`, `ASI_ajax_call()` |
| Options | `ASI_plugin_{category}_settings` | `ASI_plugin_banks_settings` |
| Sources | `ASI_Source_{Name}` | `ASI_Source_Pixabay` |
| AJAX actions | `asi_bulk_*`, `asi_generate_image` | `wp_ajax_asi_bulk_create_job` |
| Nonces | `asi_bulk_nonce`, `ajax_nonce_All_Sources_Images` | |

## Key Files

| File | Purpose |
|------|---------|
| `admin/class-all-sources-images-generation.php` | Core generation logic (~2460 lines) |
| `admin/sources/class-asi-image-source.php` | Abstract source base class |
| `admin/sources/class-asi-source-manager.php` | Singleton source registry |
| `admin/includes/class-asi-bulk-generation-*.php` | Bulk system (DB, Ajax, Cron) |
| `admin/js/bulk-generation.js` | Frontend bulk UI |
| `admin/partials/new-ui/` | Redesigned admin UI (opt-in) |

## Common Tasks

**Update Version**: `all-sources-images.php` → `ALL_SOURCES_IMAGES_VERSION` constant + PHPDoc header + `README.txt`

**Add Third-Party Integration**: 
1. Add setting to `ASI_plugin_compatibility_settings`
2. Hook in `ASI_Plugin_Integrations::init_hooks()`
3. Implement handler method calling `$generation->ASI_create_thumb()`

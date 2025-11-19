# Magic Post Thumbnail - AI Coding Agent Guide

## Project Overview

Magic Post Thumbnail is a WordPress plugin that automatically generates and retrieves images for posts from multiple sources (Google Images, DALL·E, Stable Diffusion, Pexels, Unsplash, Pixabay, etc.). It can set featured images or insert images into post content via Gutenberg blocks and bulk operations.

**Version**: 6.1.6  
**Type**: WordPress Plugin (Free + Pro with Freemius SDK)  
**PHP Version**: 7.3+  
**WordPress**: 6.0+

## Architecture

### Core Class Hierarchy

The plugin follows WordPress plugin boilerplate architecture:

- **`Magic_Post_Thumbnail`** (`includes/class-magic-post-thumbnail.php`) - Main orchestrator, loads dependencies and initializes hooks
- **`Magic_Post_Thumbnail_Loader`** (`includes/class-magic-post-thumbnail-loader.php`) - Hook registration system
- **`Magic_Post_Thumbnail_Admin`** (`admin/class-magic-post-thumbnail-admin.php`) - Admin functionality, settings, UI
- **`Magic_Post_Thumbnail_Generation`** (`admin/class-magic-post-thumbnail-generation.php`) - **Extends** `Magic_Post_Thumbnail_Admin`, handles all image generation logic
- **`Magic_Post_Thumbnail_Public`** (`public/class-magic-post-thumbnail-public.php`) - Public-facing functionality (minimal)

**Key Pattern**: `Magic_Post_Thumbnail_Generation` extends `Magic_Post_Thumbnail_Admin` to access admin methods while providing specialized generation features.

### Critical Settings Structure

Plugin settings are stored in WordPress options with `MPT_plugin_*` prefix:

- `MPT_plugin_main_settings` - Core config including `image_block` array (multiple image generation blocks per post)
- `MPT_plugin_banks_settings` - API credentials and selected image banks (`api_chosen_auto`, `api_chosen_manual`)
- `MPT_plugin_compatibility_settings` - Integrations (REST API, WPeMatico, FeedWordPress, FIFU, CMB2, ACF)
- `MPT_plugin_rights_settings` - User role permissions (administrator, editor, author, etc.)
- `MPT_plugin_cron_settings` - Scheduled generation config (Pro feature)
- `MPT_plugin_proxy_settings` - Proxy configuration for API calls
- `MPT_plugin_logs_settings` - Monolog configuration

**Pattern**: All options use `wp_parse_args()` with defaults from `MPT_default_options_*_settings()` methods in admin class.

### Image Generation Flow

1. **Trigger Points**:
   - Manual: Meta box button in post editor → AJAX call to `MPT_ajax_call()`
   - Automatic: `save_post` hook → `wp_schedule_single_event()` → `mpt_generate_scheduled_image`
   - Bulk: Admin list table bulk action → Sequential AJAX calls
   - Integrations: REST API, WPeMatico, FeedWordPress hooks

2. **Core Method**: `MPT_create_thumb()` in `Magic_Post_Thumbnail_Generation`
   - Takes 12 parameters including `$post_id`, `$key_img_block` (image block index), `$button_autogenerate`
   - Checks permissions, post type, categories
   - Extracts search terms (title, text analysis, categories, tags, custom fields, OpenAI)
   - Iterates through selected image banks in order
   - Downloads and attaches image to WordPress media library
   - Sets featured image OR inserts into post content based on `image_location` setting

3. **Multi-Block System**: `image_block` array allows multiple images per post with different:
   - Image banks
   - Search sources (title, tags, categories, text analysis)
   - Locations (featured, content at specific positions, FIFU, CMB2, ACF)
   - Post-processing (flip, crop)

### AJAX Architecture

**Bulk Generation** (`admin/js/generation.js` + `MPT_ajax_call()`):
- Client sends `ids_mpt_generation` (array of post IDs), `currentPostIndex`
- Server processes ONE post at a time, returns `nextPost: true/false`
- Client loops with interval delays (configurable speed)
- Progress bar updates: `percent = 100*(imageCounter/(count*totalBlocks))`

**Gutenberg Block** (`admin/blocks/mpt-images/index.js`):
- `block_searching_images` - Search images from APIs
- `block_downloading_image` - Download selected image to media library
- Supports setting featured image OR inserting image block
- Uses `getBankPaths.js` for API-specific JSON path mappings (Pixabay vs Unsplash vs Pexels structures differ)

### Logging System

Uses **Monolog** (`admin/partials/monolog/vendor/monolog/monolog`):
- Called via `$this->MPT_monolog_call()` in admin classes
- Log levels: `$log->info()`, `$log->error()`
- Logs stored in `admin/partials/monolog/logs/`
- Viewable in admin dashboard "Logs" tab

**Example**:
```php
$log = $this->MPT_monolog_call();
$log->info('Search term', array('keyword' => $keyword, 'bank' => $api_chosen));
```

## Development Workflows

### Testing API Integrations

1. Each image bank has config file in `admin/partials/tabs/banks/*.php` (pixabay.php, dallev1.php, etc.)
2. Admin has "Test API" button → AJAX call to `MPT_test_apis()` method
3. Returns success/error for each configured bank

### Adding New Image Bank

1. Add bank config in `admin/partials/tabs/banks/newbank.php`
2. Update `MPT_default_options_banks_settings()` to include bank in arrays
3. Implement API call logic in `MPT_create_thumb()` switch/case for bank
4. Add JSON path mappings in `admin/blocks/mpt-images/getBankPaths.js` for Gutenberg block
5. Update translations in `languages/magic-post-thumbnail.pot`

### Debugging Generation Issues

1. Enable logs in admin settings
2. Check `admin/partials/monolog/logs/*.log` for detailed generation flow
3. Common issues logged:
   - "Featured image already exists" - check `rewrite_featured` setting
   - "Post is not in selected post types" - verify `MPT_plugin_main_settings['image_custom_post_type']`
   - "API URL not provided" - check bank credentials in `MPT_plugin_banks_settings`

## Project-Specific Conventions

### Naming Patterns

- **Methods**: `MPT_` prefix for all plugin methods (`MPT_create_thumb`, `MPT_monolog_call`)
- **Options**: `MPT_plugin_{category}_settings` format
- **Capabilities**: `mpt_manage` for admin access checks
- **Nonces**: `ajax_nonce_magic_post_thumbnail` for AJAX security

### WordPress Integration Points

**Hooks Used**:
- `save_post` - Automatic generation trigger
- `admin_notices` - Review request notice
- `enqueue_block_editor_assets` - Gutenberg block scripts
- `wp_ajax_generate_image` / `wp_ajax_nopriv_generate_image` - AJAX endpoints
- Custom: `mpt_generate_scheduled_image` - Scheduled event for deferred generation

**Custom Post Meta**:
- Featured images use standard `_thumbnail_id`
- No custom meta keys for tracking (uses WP core)

### Freemius Integration

- Free vs Pro feature gating via `mpt_freemius()->is_premium()`
- License check: `$licensing = mptAjax.licensing_data ? mptAjax.licensing_data : false;`
- Pro features: Stable Diffusion, Replicate, Crons, Advanced search sources, Compatibility plugins

### External Dependencies

- **Monolog 2.0** - Logging (`admin/partials/monolog/composer.json`)
- **php-ml** - Text analysis for keyword extraction (`includes/php-ml/vendor/php-ai/php-ml/`)
- **Stop words** - Multi-language in `includes/php-ml/stop-words/*.txt`

## Key Files Reference

- **Entry Point**: `magic-post-thumbnail.php` - Plugin initialization, Freemius SDK setup
- **Generation Engine**: `admin/class-magic-post-thumbnail-generation.php` (1843 lines) - Core image generation logic
- **Admin UI**: `admin/partials/magic-post-thumbnail-admin-display.php` - Tab-based settings interface
- **Gutenberg Block**: `admin/blocks/mpt-images/index.js` - React component for image search/insert
- **Bulk JS**: `admin/js/generation.js` - Sequential AJAX bulk generation with progress tracking

## Common Tasks

**Update Plugin Version**: Change in 3 places:
1. `magic-post-thumbnail.php` - `define('MAGIC_POST_THUMBNAIL_VERSION', 'X.X.X')`
2. `magic-post-thumbnail.php` - PHPDoc header `@version`
3. `README.txt` - Stable tag

**Add New Compatibility Plugin**:
1. Add option to `MPT_plugin_compatibility_settings`
2. Hook into plugin's action/filter in `Magic_Post_Thumbnail_Admin::__construct()`
3. Add UI in `admin/partials/tabs/compatibility.php`

**Modify Search Term Extraction**:
- Edit `MPT_create_thumb()` search source logic (title, tags, text analysis sections)
- Uses php-ml `StopWords` for text cleaning before keyword extraction

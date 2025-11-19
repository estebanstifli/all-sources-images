# Plan de Renombrado: Magic Post Thumbnail → All Sources Images

## 📋 Resumen de Cambios

**Nombre Antiguo**: Magic Post Thumbnail  
**Nombre Nuevo**: All Sources Images  
**Prefijo Antiguo**: `MPT` / `mpt`  
**Prefijo Nuevo**: `ASI` / `asi`

---

## 🎯 Cambios Necesarios

### 1. **Identificadores del Plugin**

#### Archivo Principal
- [ ] `magic-post-thumbnail.php` → `all-sources-images.php`

#### Constantes PHP
- [ ] `ALL_SOURCES_IMAGES_VERSION` → `ALL_SOURCES_IMAGES_VERSION`
- [ ] `ASI_FREEMIUS_UNINSTALL` → `ASI_FREEMIUS_UNINSTALL`

#### Plugin Headers
- [ ] Plugin Name: `All Sources Images`
- [ ] Plugin URI: `https://tu-dominio.com/all-sources-images/`
- [ ] Description: Actualizar
- [ ] Text Domain: `magic-post-thumbnail` → `all-sources-images`
- [ ] Author: Tu nombre
- [ ] Author URI: Tu sitio

---

### 2. **Prefijos de Clases PHP**

#### Clases Principales (includes/)
- [ ] `All_Sources_Images` → `All_Sources_Images`
- [ ] `All_Sources_Images_Loader` → `All_Sources_Images_Loader`
- [ ] `All_Sources_Images_i18n` → `All_Sources_Images_i18n`
- [ ] `All_Sources_Images_Activator` → `All_Sources_Images_Activator`
- [ ] `All_Sources_Images_Deactivator` → `All_Sources_Images_Deactivator`

#### Clases Admin (admin/)
- [ ] `All_Sources_Images_Admin` → `All_Sources_Images_Admin`
- [ ] `All_Sources_Images_Generation` → `All_Sources_Images_Generation`

#### Clases Public (public/)
- [ ] `All_Sources_Images_Public` → `All_Sources_Images_Public`

---

### 3. **Prefijos de Funciones PHP**

#### Funciones Globales
- [ ] `asi_freemius()` → `asi_freemius()`
- [ ] `activate_All_Sources_Images()` → `activate_all_sources_images()`
- [ ] `deactivate_All_Sources_Images()` → `deactivate_all_sources_images()`
- [ ] `ASI_FREEMIUS_UNINSTALL_cleanup()` → `asi_freemius_uninstall_cleanup()`

#### Métodos de Clase (Prefijo ASI_)
- [ ] `ASI_create_thumb()` → `ASI_create_thumb()`
- [ ] `ASI_ajax_call()` → `ASI_ajax_call()`
- [ ] `ASI_monolog_call()` → `ASI_monolog_call()`
- [ ] `ASI_test_apis()` → `ASI_test_apis()`
- [ ] `ASI_block_searching_images()` → `ASI_block_searching_images()`
- [ ] `ASI_block_downloading_image()` → `ASI_block_downloading_image()`
- [ ] `ASI_Generate()` → `ASI_Generate()`
- [ ] `ASI_Get_Parameters()` → `ASI_Get_Parameters()`
- [ ] Todos los métodos con prefijo `ASI_*`

---

### 4. **WordPress Options (Base de Datos)**

#### Settings Options
- [ ] `ASI_plugin_main_settings` → `ASI_plugin_main_settings`
- [ ] `ASI_plugin_banks_settings` → `ASI_plugin_banks_settings`
- [ ] `ASI_plugin_compatibility_settings` → `ASI_plugin_compatibility_settings`
- [ ] `ASI_plugin_rights_settings` → `ASI_plugin_rights_settings`
- [ ] `ASI_plugin_cron_settings` → `ASI_plugin_cron_settings`
- [ ] `ASI_plugin_proxy_settings` → `ASI_plugin_proxy_settings`
- [ ] `ASI_plugin_logs_settings` → `ASI_plugin_logs_settings`
- [ ] `ASI_plugin_block_settings` → `ASI_plugin_block_settings`
- [ ] `ASI_plugin_posts_settings` → `ASI_plugin_posts_settings`
- [ ] `ASI_plugin_interval_settings` → `ASI_plugin_interval_settings`
- [ ] `ASI_plugin_activation_date` → `ASI_plugin_activation_date`

#### Métodos Default Options
- [ ] `ASI_default_options_main_settings()` → `ASI_default_options_main_settings()`
- [ ] `ASI_default_options_banks_settings()` → `ASI_default_options_banks_settings()`
- [ ] `ASI_default_options_compatibility_settings()` → `ASI_default_options_compatibility_settings()`
- [ ] `ASI_default_options_rights_settings()` → `ASI_default_options_rights_settings()`
- [ ] `ASI_default_options_cron_settings()` → `ASI_default_options_cron_settings()`
- [ ] `ASI_default_options_proxy_settings()` → `ASI_default_options_proxy_settings()`
- [ ] `ASI_default_options_logs_settings()` → `ASI_default_options_logs_settings()`
- [ ] `ASI_default_posts_types()` → `ASI_default_posts_types()`

---

### 5. **WordPress Hooks & Actions**

#### Action Hooks
- [ ] `ASI_freemius_loaded` → `asi_freemius_loaded`
- [ ] `ASI_generate_scheduled_image` → `asi_generate_scheduled_image`

#### AJAX Actions
- [ ] `wp_ajax_generate_image` → `wp_ajax_asi_generate_image`
- [ ] `wp_ajax_nopriv_generate_image` → `wp_ajax_nopriv_asi_generate_image`
- [ ] `wp_ajax_test_apis` → `wp_ajax_asi_test_apis`
- [ ] `wp_ajax_block_searching_images` → `wp_ajax_asi_block_searching_images`
- [ ] `wp_ajax_block_downloading_image` → `wp_ajax_asi_block_downloading_image`
- [ ] `wp_ajax_asi_hide_notice` → `wp_ajax_asi_hide_notice`
- [ ] `wp_ajax_asi_remind_later` → `wp_ajax_asi_remind_later`

#### Settings Groups
- [ ] `ASI-plugin-main-settings` → `ASI-plugin-main-settings`
- [ ] `ASI-plugin-banks-settings` → `ASI-plugin-banks-settings`
- [ ] `ASI-plugin-compatibility-settings` → `ASI-plugin-compatibility-settings`
- [ ] `ASI-plugin-rights-settings` → `ASI-plugin-rights-settings`
- [ ] `ASI-plugin-cron-settings` → `ASI-plugin-cron-settings`
- [ ] `ASI-plugin-proxy-settings` → `ASI-plugin-proxy-settings`
- [ ] `ASI-plugin-logs-settings` → `ASI-plugin-logs-settings`

---

### 6. **Capabilities (Permisos WordPress)**

- [ ] `ASI_manage` → `asi_manage`

---

### 7. **JavaScript Variables & AJAX**

#### Variables Globales JS
- [ ] `asiAjax` → `asiAjax`
- [ ] `asiGenerationVars` → `asiGenerationVars` (opcional)

#### Archivos JavaScript
- [ ] `admin/js/magic-post-thumbnail-admin.js` → `admin/js/all-sources-images-admin.js`
- [ ] `admin/js/generation.js` - Actualizar referencias
- [ ] `admin/js/manual_search.js` - Actualizar referencias
- [ ] `admin/js/source.js` - Actualizar referencias
- [ ] `admin/js/common.js` - Actualizar referencias
- [ ] `public/js/magic-post-thumbnail-public.js` → `public/js/all-sources-images-public.js`

#### AJAX Endpoints en JS
- [ ] `action: 'asi_generate_image'` → `action: 'asi_generate_image'`
- [ ] `action: 'asi_test_apis'` → `action: 'asi_test_apis'`
- [ ] `action: 'asi_block_searching_images'` → `action: 'asi_block_searching_images'`
- [ ] `action: 'asi_block_downloading_image'` → `action: 'asi_block_downloading_image'`

---

### 8. **CSS Files & Classes**

#### Archivos CSS
- [ ] `admin/css/magic-post-thumbnail-admin.css` → `admin/css/all-sources-images-admin.css`
- [ ] `admin/css/magic-post-thumbnail-post.css` → `admin/css/all-sources-images-post.css`
- [ ] `public/css/magic-post-thumbnail-public.css` → `public/css/all-sources-images-public.css`

#### Clases CSS (opcional, solo si quieres)
- [ ] `.mpt-*` → `.asi-*`

---

### 9. **Admin Menu & Pages**

#### Menu Slugs
- [ ] `all-sources-images-admin-display` → `all-sources-images-admin-display`
- [ ] `all-sources-images-admin-display-pricing` → `all-sources-images-admin-display-pricing`

#### Page Titles
- [ ] "Magic Post Thumbnail" → "All Sources Images"

---

### 10. **Archivos de Plantillas**

#### Admin Partials
- [ ] `admin/partials/all-sources-images-admin-display.php` → `admin/partials/all-sources-images-admin-display.php`

#### Public Partials
- [ ] `public/partials/magic-post-thumbnail-public-display.php` → `public/partials/all-sources-images-public-display.php`

---

### 11. **Gutenberg Block**

#### Block Registration
- [ ] `registerBlockType('asi/asi-images')` → `registerBlockType('asi/asi-images')`
- [ ] Block title: "ASI Images" → "ASI Images"

#### Block Directory
- [ ] `admin/blocks/mpt-images/` → `admin/blocks/asi-images/`

---

### 12. **Translation/i18n**

#### Text Domain
- [ ] `'all-sources-images'` → `'all-sources-images'`
- [ ] `'all-sources-images'` → `'all-sources-images'`

#### Language Files
- [ ] `languages/magic-post-thumbnail.pot` → `languages/all-sources-images.pot`
- [ ] `languages/mpt-*.po` → `languages/asi-*.po`
- [ ] `languages/mpt-*.mo` → `languages/asi-*.mo`
- [ ] `languages/mpt-*-script.json` → `languages/asi-*-script.json`

---

### 13. **Directorio del Plugin**

#### Renombrar Carpeta
- [ ] `magic-post-thumbnail/` → `all-sources-images/`

---

### 14. **README & Documentación**

- [ ] `README.txt` - Actualizar todos los textos
- [ ] Plugin Name
- [ ] Description
- [ ] Author
- [ ] URLs
- [ ] Screenshots descriptions
- [ ] Changelog (opcional mantener histórico)

---

### 15. **Freemius SDK (Si lo mantienes)**

#### Config Freemius
- [ ] `id` - Necesitarás un nuevo ID si registras en Freemius
- [ ] `slug`: `'all-sources-images'` → `'all-sources-images'`
- [ ] `public_key` - Nuevo key si lo usas
- [ ] Menu slugs

**NOTA**: Si no vas a usar Freemius, puedes eliminarlo completamente.

---

### 16. **Logs & Monolog**

#### Directorio de Logs
- [ ] `admin/partials/monolog/logs/` - Sin cambios necesarios
- [ ] Nombres de archivos log pueden mantener formato existente

---

### 17. **Imágenes & Assets**

#### Logos & Screenshots
- [ ] `admin/img/logo.png` - Cambiar por tu logo
- [ ] Screenshots del plugin
- [ ] Iconos si los hay

---

## 🔧 Orden de Ejecución Recomendado

### **Fase 1: Preparación**
1. Backup del código actual
2. Crear nueva rama en Git
3. Renombrar directorio principal del plugin

### **Fase 2: PHP Core**
4. Renombrar archivo principal `.php`
5. Actualizar constantes
6. Renombrar todas las clases
7. Renombrar todas las funciones globales
8. Actualizar métodos de clases

### **Fase 3: WordPress Integration**
9. Actualizar options keys
10. Actualizar hooks y actions
11. Actualizar AJAX actions
12. Actualizar capabilities

### **Fase 4: Frontend**
13. Renombrar y actualizar archivos JS
14. Renombrar y actualizar archivos CSS
15. Actualizar Gutenberg block

### **Fase 5: Admin & UI**
16. Actualizar admin menu slugs
17. Actualizar archivos de plantillas
18. Actualizar text domain y traducciones

### **Fase 6: Assets & Docs**
19. Actualizar README.txt
20. Cambiar logos e imágenes
21. Actualizar Freemius o eliminarlo

### **Fase 7: Testing**
22. Probar activación del plugin
23. Probar generación de imágenes
24. Probar todas las configuraciones
25. Verificar que no haya errores en logs

---

## 📝 Script de Búsqueda y Reemplazo

### Búsquedas Globales Necesarias:

```bash
# Clases
All_Sources_Images → All_Sources_Images

# Funciones
asi_freemius → asi_freemius
ASI_ → ASI_

# Options
ASI_plugin_ → ASI_plugin_
ASI_default_ → ASI_default_

# Text Domain
'all-sources-images' → 'all-sources-images'
'all-sources-images' → 'all-sources-images'

# Slugs
magic-post-thumbnail → all-sources-images
mpt- → asi-

# Variables JS
asiAjax → asiAjax

# Actions
'asi_generate_image' → 'asi_generate_image'
'asi_test_apis' → 'asi_test_apis'

# Capabilities
ASI_manage → asi_manage
```

---

## ⚠️ PRECAUCIONES

1. **NO reemplazar** en carpetas vendor: `admin/partials/freemius/`, `admin/partials/monolog/vendor/`, `includes/php-ml/vendor/`
2. **Mantener** nombres de archivos de configuración de APIs en `admin/partials/tabs/banks/*.php`
3. **Verificar** que los nonces sigan funcionando después del cambio
4. **Actualizar** `.github/copilot-instructions.md` con los nuevos nombres

---

## ✅ Checklist Final

- [ ] Plugin se activa sin errores
- [ ] No hay errores en debug.log
- [ ] Opciones se guardan correctamente
- [ ] AJAX funciona en admin
- [ ] Generación de imágenes funciona
- [ ] Bulk generation funciona
- [ ] Gutenberg block funciona
- [ ] Traducciones funcionan
- [ ] No hay referencias a "Magic Post Thumbnail" en el código visible
- [ ] No conflictos con el plugin original si se instalan ambos

---

**NOTA IMPORTANTE**: Este es un proceso que puede tomar 2-4 horas. ¿Quieres que empecemos por fases?

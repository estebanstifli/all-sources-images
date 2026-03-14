# 📋 INFORME: Integración de WordPress Abilities API en All Sources Images

## 1. Resumen Ejecutivo

WordPress 6.9 incluye la **Abilities API** en el core, que proporciona un registro centralizado de capacidades (abilities) en formato legible por máquinas y humanos. Combinado con el **MCP Adapter**, permite que agentes de IA interactúen con WordPress de forma estandarizada.

**Oportunidad para All Sources Images**: Exponer las funcionalidades del plugin como "abilities" permitirá que agentes de IA (Claude, GPT, Gemini, etc.) puedan buscar imágenes, generarlas con IA, e insertarlas en posts automáticamente mediante comandos en lenguaje natural.

---

## 2. Cómo Funciona el Sistema

### Flujo de Comunicación:
```
Usuario → Agente IA → MCP Client → WordPress MCP Adapter → Abilities API → All Sources Images
                                                    ↓
                                              Plugin ejecuta la acción
                                                    ↓
                                              Resultado devuelto al agente
```

### Componentes Clave:
- **Abilities API**: Registro de capacidades con schemas de entrada/salida
- **MCP Adapter**: Traduce abilities a herramientas MCP que los agentes IA pueden usar
- **Tools**: Acciones ejecutables (buscar imagen, generar, insertar)
- **Resources**: Datos expuestos (lista de fuentes disponibles, configuración)

---

## 3. Abilities Propuestas para All Sources Images

### 🔍 **Ability 1: `allsi/search-image`**
**Descripción**: Buscar imágenes en bancos de imágenes o generar con IA

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Busca una imagen de un gato jugando" |
| **Input Schema** | `search_term` (string), `source` (string: pexels/pixabay/unsplash/dalle/etc), `count` (int), `orientation` (string: landscape/portrait/square) |
| **Output Schema** | Array de `{url, thumbnail, alt, caption, source, id}` |
| **Callback** | Llama a `ALLSI_Source_Manager->get_source($source)->generate()` |
| **Permisos** | `edit_posts` |

**Ejemplo de uso por agente IA:**
```
"Busca 5 imágenes de atardeceres en la playa usando Pexels"
```

---

### 📥 **Ability 2: `allsi/download-image`**
**Descripción**: Descarga una imagen a la biblioteca de medios de WordPress

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Descarga esta imagen a mi biblioteca" |
| **Input Schema** | `image_url` (string), `alt_text` (string), `caption` (string), `filename` (string) |
| **Output Schema** | `{attachment_id, url, title, success}` |
| **Callback** | Función de descarga existente en el plugin |
| **Permisos** | `upload_files` |

---

### 🖼️ **Ability 3: `allsi/set-featured-image`**
**Descripción**: Establece una imagen como imagen destacada de un post

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Pon esta imagen como destacada del post 123" |
| **Input Schema** | `post_id` (int), `image_url` (string) O `attachment_id` (int) |
| **Output Schema** | `{success, post_id, attachment_id, thumbnail_url}` |
| **Callback** | `set_post_thumbnail()` + descarga si es URL externa |
| **Permisos** | `edit_post` |

---

### 📝 **Ability 4: `allsi/insert-image-in-content`**
**Descripción**: Inserta una imagen en el contenido de un post

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Inserta esta imagen después del párrafo 3 en el post 45" |
| **Input Schema** | `post_id` (int), `image_url` (string), `position` (string: after_paragraph_1/after_paragraph_2/etc), `alignment` (string: left/center/right) |
| **Output Schema** | `{success, post_id, image_position}` |
| **Callback** | Lógica existente de inserción en contenido |
| **Permisos** | `edit_post` |

---

### 🤖 **Ability 5: `allsi/generate-ai-image`**
**Descripción**: Genera una imagen usando IA (DALL-E, Stable Diffusion, Gemini, etc.)

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Genera una imagen de un dragón volando sobre montañas con DALL-E" |
| **Input Schema** | `prompt` (string), `source` (string: dalle/stability/gemini/replicate), `style` (string), `size` (string: 1024x1024/etc) |
| **Output Schema** | `{url, prompt_used, source, generation_id}` |
| **Callback** | Sources de IA (`ALLSI_Source_Dallev1`, `ALLSI_Source_Stability`, etc.) |
| **Permisos** | `edit_posts` |

---

### 🔄 **Ability 6: `allsi/auto-generate-for-post`**
**Descripción**: Genera automáticamente imagen(es) para un post basándose en su título/contenido

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Genera imagen destacada automática para el post 99" |
| **Input Schema** | `post_id` (int), `source` (string, opcional), `set_as_featured` (bool), `insert_in_content` (bool), `position` (string) |
| **Output Schema** | `{success, search_term_used, image_url, attachment_id}` |
| **Callback** | `ALLSI_create_thumb()` |
| **Permisos** | `edit_post` |

---

### 📋 **Ability 7: `allsi/list-sources`**
**Descripción**: Lista las fuentes de imágenes disponibles y su estado

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "¿Qué bancos de imágenes tengo disponibles?" |
| **Input Schema** | (ninguno) |
| **Output Schema** | Array de `{slug, name, type: stock/ai, is_available, requires_api_key}` |
| **Callback** | `ALLSI_Source_Manager::instance()->all()` |
| **Permisos** | `manage_options` |

---

### 🌍 **Ability 8: `allsi/translate-search-term`**
**Descripción**: Traduce un término de búsqueda a inglés para mejores resultados

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Traduce 'gato jugando' a inglés" |
| **Input Schema** | `text` (string), `source_language` (string, opcional) |
| **Output Schema** | `{original, translated, source_lang, target_lang}` |
| **Callback** | `ALLSI_translate_text()` |
| **Permisos** | `edit_posts` |

---

### 📦 **Ability 9: `allsi/bulk-generate`** (Avanzada)
**Descripción**: Crea un trabajo de generación masiva de imágenes

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "Genera imágenes para todos los posts sin imagen destacada" |
| **Input Schema** | `post_type` (string), `filter` (string: all/no_featured/custom), `post_ids` (array, opcional), `source` (string) |
| **Output Schema** | `{job_id, posts_count, status}` |
| **Callback** | `ALLSI_Bulk_Generation_DB::create_job()` |
| **Permisos** | `manage_options` |

---

### 📊 **Ability 10: `allsi/get-bulk-job-status`**
**Descripción**: Consulta el estado de un trabajo de generación masiva

| Campo | Detalle |
|-------|---------|
| **Caso de uso** | "¿Cómo va el trabajo de generación #15?" |
| **Input Schema** | `job_id` (int) |
| **Output Schema** | `{job_id, status, total_posts, completed, failed, progress_percent}` |
| **Callback** | `ALLSI_Bulk_Generation_DB::get_job()` |
| **Permisos** | `manage_options` |

---

## 4. Ejemplos de Comandos de Agentes IA

Con estas abilities implementadas, un agente IA podría ejecutar:

| Comando del Usuario | Abilities Usadas |
|---------------------|------------------|
| "Busca una imagen de un gato jugando y ponla en el post 10" | `allsi/search-image` → `allsi/set-featured-image` |
| "Genera con DALL-E una imagen de montañas nevadas y añádela después del primer párrafo del post 25" | `allsi/search-image` (source=dallev1) → `allsi/insert-image-in-content` |
| "Pon imágenes destacadas a todos mis posts que no tienen" | `allsi/bulk-generate` (pendiente) |
| "¿Qué bancos de imágenes puedo usar?" | `allsi/list-sources` (pendiente) |
| "Busca 3 fotos de perros en Pixabay" | `allsi/search-image` ✅ |
| "Genera automáticamente la imagen para el borrador que acabo de crear" | `allsi/auto-generate-for-post` ✅ |
| "Inserta una imagen de playa después del segundo párrafo" | `allsi/search-image` → `allsi/insert-image-in-content` ✅ |

---

## 5. Arquitectura Propuesta

### Nuevo Archivo: `includes/class-allsi-abilities.php`

```php
class ALLSI_Abilities {
    
    public function __construct() {
        add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
    }
    
    public function register_abilities() {
        // Registrar cada ability con wp_register_ability()
        $this->register_search_image_ability();
        $this->register_set_featured_ability();
        $this->register_generate_ai_ability();
        // ... etc
    }
}
```

### Modificaciones Necesarias:
1. Crear clase `ALLSI_Abilities` para registrar todas las abilities
2. Añadir como dependencia `wordpress/abilities-api` en composer.json (o verificar WP 6.9+)
3. Refactorizar funciones existentes para que sean llamables como callbacks
4. Añadir validación de schemas de entrada/salida

---

## 6. Prioridad de Implementación

| Prioridad | Ability | Justificación |
|-----------|---------|---------------|
| 🔴 Alta | `allsi/search-image` | Core del plugin, más usada |
| 🔴 Alta | `allsi/set-featured-image` | Caso de uso principal |
| 🔴 Alta | `allsi/auto-generate-for-post` | Funcionalidad completa en una ability |
| 🟡 Media | `allsi/generate-ai-image` | Para usuarios con APIs de IA |
| 🟡 Media | `allsi/list-sources` | Útil para descubrimiento |
| 🟡 Media | `allsi/insert-image-in-content` | Inserción en contenido |
| 🟢 Baja | `allsi/download-image` | Helper, usualmente encadenada |
| 🟢 Baja | `allsi/translate-search-term` | Utility |
| 🟢 Baja | `allsi/bulk-generate` | Avanzada |
| 🟢 Baja | `allsi/get-bulk-job-status` | Complementaria |

---

## 7. Requisitos Técnicos

1. **WordPress 6.9+** (Abilities API en core)
2. **MCP Adapter plugin** instalado para comunicación con agentes IA
3. **Autenticación**: Los agentes necesitan autenticarse via Application Passwords u otro método
4. **Permisos**: Cada ability define su `permission_callback`

---

## 8. Beneficios de la Implementación

✅ **Automatización**: Agentes IA pueden generar imágenes sin intervención humana  
✅ **Interoperabilidad**: Funciona con cualquier cliente MCP (Claude, GPT, etc.)  
✅ **Descubribilidad**: Las abilities son auto-documentadas con schemas  
✅ **Seguridad**: Control granular de permisos por ability  
✅ **Futuro-proof**: Alineado con la dirección de WordPress core  
✅ **Diferenciación**: Pocos plugins de imágenes tienen soporte MCP/Abilities  

---

## 9. Próximos Pasos Recomendados

1. **Decidir qué abilities implementar primero** (recomiendo las 3 de prioridad alta)
2. **Crear estructura base** (`class-allsi-abilities.php`)
3. **Implementar `allsi/search-image`** como prueba de concepto
4. **Testear con MCP Adapter** y un cliente como Claude Desktop
5. **Iterar y añadir más abilities**

---

## 10. Estado de Implementación

### ✅ Implementadas y Testeadas (v1.0.7):

| Ability | Estado | Testeado en |
|---------|--------|-------------|
| `allsi/search-image` | ✅ Funcionando | https://100blogs.ovh/36/ |
| `allsi/set-featured-image` | ✅ Funcionando | Post ID 1350 |
| `allsi/auto-generate-for-post` | ✅ Funcionando | Posts 1349, 1345 |
| `allsi/insert-image-in-content` | ✅ Funcionando | Post ID 1348 |
| `allsi/generate-ai-image` | ✅ Funcionando | DALL-E testeado |

### Resultados de Testing:

```bash
# Búsqueda de imágenes - Pixabay
POST /wp-abilities/v1/abilities/allsi/search-image/run
{"input": {"search_term": "cat", "source": "pixabay", "count": 3}}
→ SUCCESS: 3 imágenes con URLs, thumbnails, alt, caption, width, height

# Búsqueda de imágenes - Pexels
POST /wp-abilities/v1/abilities/allsi/search-image/run
{"input": {"search_term": "mountain", "source": "pexels", "count": 2}}
→ SUCCESS: 2 imágenes de Pexels

# Establecer featured image
POST /wp-abilities/v1/abilities/allsi/set-featured-image/run
{"input": {"post_id": 1350, "image_url": "https://...", "alt_text": "Test"}}
→ SUCCESS: attachment_id: 1359

# Auto-generar imagen para post
POST /wp-abilities/v1/abilities/allsi/auto-generate-for-post/run
{"input": {"post_id": 1349, "source": "pixabay", "overwrite": true}}
→ SUCCESS: Extrajo "sistema solar snapshot small" del título largo
→ Encontró imagen y estableció como featured

# Insertar imagen en contenido
POST /wp-abilities/v1/abilities/allsi/insert-image-in-content/run
{"input": {"post_id": 1348, "image_url": "https://...", "position": 1, "placement": "after", "element": "p"}}
→ SUCCESS: Imagen insertada después del párrafo 1
→ attachment_id: 1363, position_description: "after paragraph 1"
```

### Características Implementadas:

1. **Extracción inteligente de términos de búsqueda**
   - Elimina stop words (the, a, and, is, etc.)
   - Limita a 4 palabras principales
   - Fallback para títulos muy procesados

2. **Soporte para múltiples fuentes**
   - Stock photos: Pixabay, Pexels, Unsplash, Flickr, Openverse, Giphy
   - IA generativa: DALL-E, Stability, Gemini, Replicate, Workers AI

3. **Reutilización de código existente**
   - Usa `ALLSI_create_thumb()` con `get_only_thumb=true`
   - Usa `ALLSI_insert_content_image()` para inserción inline
   - Aprovecha proxy, API keys, y toda la lógica existente

4. **Parámetros con valores por defecto**
   - `source`: "pixabay" (no requiere API key)
   - `count`: 5 imágenes
   - `selection`: "random_result" para variedad
   - `overwrite`: false (no sobreescribir)
   - `position`: 1 (primer párrafo)
   - `placement`: "after" (después del elemento)
   - `element`: "p" (párrafo)
   - `image_size`: "large"

5. **Descripciones en lenguaje natural para IA**
   - Cada ability tiene descripción detallada
   - Cada parámetro explica su uso y valores válidos
   - Enums para valores restringidos
   - Ejemplos de uso en las descripciones

### 🔜 Pendientes:
- `allsi/list-sources`
- `allsi/download-image`
- `allsi/translate-search-term`
- `allsi/bulk-generate`
- `allsi/get-bulk-job-status`

---

## 11. Ability: allsi/generate-ai-image (Detalles)

### Descripción
Genera una imagen usando inteligencia artificial. Soporta múltiples proveedores:
- **DALL-E 3** (OpenAI): Alta calidad, fotorealista, mejor para uso general
- **Stability AI**: Estilos artísticos, bueno para imágenes creativas  
- **Gemini** (Google): IA multimodal, bueno para prompts complejos
- **Replicate**: Múltiples modelos de IA disponibles
- **Workers AI** (Cloudflare): Generación rápida basada en edge

### Input Schema
```json
{
  "prompt": "string (requerido) - Descripción detallada de la imagen a generar",
  "source": "string - dallev1|stability|gemini|replicate|workers_ai (default: dallev1)",
  "size": "string - 1024x1024|1024x1792|1792x1024 (default: 1024x1024)",
  "style": "string - vivid|natural (default: vivid, solo DALL-E)",
  "quality": "string - standard|hd (default: hd, solo DALL-E)"
}
```

### Output Schema
```json
{
  "success": "boolean",
  "url": "string - URL temporal de la imagen generada",
  "prompt_used": "string - Prompt enviado a la IA",
  "revised_prompt": "string - Prompt revisado por DALL-E (si aplica)",
  "source": "string - Proveedor usado",
  "size": "string - Tamaño de la imagen",
  "error": "string - Mensaje de error si falló"
}
```

### Ejemplo de Uso
```bash
# Generar imagen con DALL-E
POST /wp-abilities/v1/abilities/allsi/generate-ai-image/run
{
  "input": {
    "prompt": "A majestic golden dragon flying over snow-capped mountains at sunset",
    "source": "dallev1",
    "size": "1792x1024",
    "style": "vivid",
    "quality": "hd"
  }
}
```

### Notas Importantes
1. **URL Temporal**: La URL devuelta es temporal (expira en ~1 hora para DALL-E). Usa `allsi/set-featured-image` o `allsi/insert-image-in-content` para guardarla en WordPress.
2. **API Keys**: Cada proveedor requiere su propia API key configurada en los ajustes del plugin.
3. **Costos**: La generación de imágenes con IA tiene costo por imagen según el proveedor.
4. **Gemini Base64**: Gemini devuelve imágenes en base64. Si necesitas usarlo, usa `allsi/search-image` con `source=gemini` que maneja la descarga automáticamente.

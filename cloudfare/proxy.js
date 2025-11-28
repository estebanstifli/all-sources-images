// Worker Proxy Genérico de Capa Intermedia
// Este Worker maneja la autenticación y reenvío de solicitudes
// basándose en el parámetro 'servicio'.

// =======================================================================
// === CONFIGURACIÓN DE SERVICIOS Y AUTENTICACIÓN ===
// Define dónde debe inyectarse la API Key para cada servicio
// 'key_location': 'query' (URL), 'header' o 'none'
// 'key_name_in_api': Nombre del parámetro/cabecera
// 'auth_prefix': Prefijo opcional (p.ej. "Bearer ")
// 'env_key_name': Nombre del Secret configurado en Cloudflare
// =======================================================================
const SERVICE_CONFIGS = {
    // Bancos clásicos
    pixabay:      { key_location: 'query',  key_name_in_api: 'key',        env_key_name: 'PIXABAY_API_KEY' },
    pexels:       { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: '',            env_key_name: 'PEXELS_API_KEY' },
    unsplash:     { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Client-ID ',  env_key_name: 'UNSPLASH_API_KEY' },
    giphy:        { key_location: 'query',  key_name_in_api: 'api_key',    env_key_name: 'GIPHY_API_KEY' },
    flickr:       { key_location: 'query',  key_name_in_api: 'api_key',    env_key_name: 'FLICKR_API_KEY' },
    openverse:    { key_location: 'none' },
    cc_search:    { key_location: 'none' },
    youtube:      { key_location: 'query',  key_name_in_api: 'key',        env_key_name: 'YOUTUBE_API_KEY' },
    google_image: { key_location: 'query',  key_name_in_api: 'key',        env_key_name: 'GOOGLE_IMAGE_API_KEY' },
    pixabay_video:{ key_location: 'query',  key_name_in_api: 'key',        env_key_name: 'PIXABAY_API_KEY' },

    // IA / modelos
    dallev1:      { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Bearer ',     env_key_name: 'DALLE_API_KEY' },
    stability:    { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Bearer ',     env_key_name: 'STABILITY_API_KEY' },
    replicate:    { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Token ',      env_key_name: 'REPLICATE_API_KEY' },
    gemini:       { key_location: 'query',  key_name_in_api: 'key',        env_key_name: 'GEMINI_API_KEY' },
    workers_ai:   { key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Bearer ',     env_key_name: 'WORKERS_AI_API_TOKEN' },
    google_translate: { key_location: 'query', key_name_in_api: 'key',     env_key_name: 'GOOGLE_TRANSLATE_API_KEY' },
    cloudflare_ai:{ key_location: 'header', key_name_in_api: 'Authorization', auth_prefix: 'Bearer ',     env_key_name: 'WORKERS_AI_API_TOKEN' },
};

const DEFAULT_ALLOWED_HEADERS = 'Content-Type, Token, X-Requested-With';

export default {
    async fetch(request, env) {
        // Solo permitir solicitudes GET y POST (o OPTIONS para CORS)
        if (request.method !== 'GET' && request.method !== 'POST' && request.method !== 'OPTIONS') {
            return new Response('Método no permitido.', { status: 405 });
        }
        
        // Manejo de CORS (si el plugin está en otro dominio)
        if (request.method === 'OPTIONS') {
            return new Response(null, {
                status: 204,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
                    'Access-Control-Allow-Headers': DEFAULT_ALLOWED_HEADERS,
                }
            });
        }

        const url = new URL(request.url);
        
        // 1. Obtener parámetros Fijos para la Autenticación y Reenvío
        
        // Se puede enviar en la URL o en la cabecera X-Auth-Token
        const incomingToken = url.searchParams.get('token') || request.headers.get('Token'); 
        const rawService = url.searchParams.get('servicio');
        let destinationUrl = url.searchParams.get('url');
        const destinationUrlBase64 = url.searchParams.get('url_b64');
        if (destinationUrlBase64 && destinationUrlBase64 !== 'null' && destinationUrlBase64 !== 'undefined') {
            try {
                const normalizedB64 = destinationUrlBase64.replace(/ /g, '+');
                destinationUrl = atob(normalizedB64);
            } catch (error) {
                // fall back to plain URL if provided
                if (!destinationUrl) {
                    return new Response('URL base64 decode failed.', { status: 400 });
                }
            }
        }
        
        // 2. Validación de Parámetros Fijos
        
        if (!env.PLUGIN_AUTH_TOKEN) {
            return new Response('Token del plugin no configurado en Cloudflare.', { status: 500 });
        }

        if (incomingToken !== env.PLUGIN_AUTH_TOKEN) {
            return new Response('Token de autenticación inválido.', { status: 403 });
        }

        if (!rawService || !destinationUrl) {
            return new Response('Parámetros "servicio" o "url" faltantes.', { status: 400 });
        }

        const serviceName = rawService.toLowerCase();
        const config = SERVICE_CONFIGS[serviceName];
        if (!config) {
            return new Response(`Servicio "${serviceName}" no reconocido.`, { status: 404 });
        }

        // 3. Preparar la URL y las Cabeceras para el Servicio Destino
        
        const finalUrl = new URL(destinationUrl);
        let headers = new Headers(request.headers);
        
        // Eliminar cabeceras de autenticación propias (si se enviaron en las cabeceras)
        headers.delete('Token'); 
        headers.delete('token');
        headers.delete('host');
        headers.delete('content-length');
        headers.delete('accept-encoding');

        // 4. INYECCIÓN DINÁMICA DE LA API KEY (Autenticación)
        const apiKey = config.env_key_name ? env[config.env_key_name] : null;

        if (config.key_location === 'query' && apiKey) {
            // Inyectar la clave en los parámetros de la URL
            finalUrl.searchParams.set(config.key_name_in_api, apiKey);

        } else if (config.key_location === 'header' && apiKey) {
            // Inyectar la clave en la cabecera HTTP
            const authValue = (config.auth_prefix || '') + apiKey;
            headers.set(config.key_name_in_api, authValue);

        } else if (config.key_location !== 'none' && !apiKey) {
            // Error si se requiere una clave pero no se encontró en 'env'
            return new Response(`La clave API para ${serviceName} no está configurada.`, { status: 500 });
        }
        
        // 5. Reenvío de la Solicitud (Proxy)
        
        // Crear una nueva solicitud con el método, cuerpo y cabeceras originales
        const requestOptions = new Request(finalUrl.toString(), {
            method: request.method,
            headers,
            body: (request.method === 'GET' || request.method === 'HEAD') ? undefined : request.body,
            redirect: 'follow',
        });

        // Reenviar la solicitud a la URL final
        const response = await fetch(requestOptions);
        
        // 6. Devolver la Respuesta TAL CUAL
        
        // Devolver la respuesta del servicio destino con el mismo estado, cabeceras y cuerpo.
        // Las cabeceras CORS deben añadirse para que el plugin pueda leer la respuesta.
        const responseHeaders = new Headers(response.headers);
        responseHeaders.set('Access-Control-Allow-Origin', '*');
        responseHeaders.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        responseHeaders.set('Access-Control-Allow-Headers', DEFAULT_ALLOWED_HEADERS);

        return new Response(response.body, {
            status: response.status,
            statusText: response.statusText,
            headers: responseHeaders,
        });

    }
}
// Worker Proxy Genérico de Capa Intermedia
// Este Worker maneja la autenticación y reenvío de solicitudes
// basándose en el parámetro 'servicio'.

// ⚠️ Define tu token de autenticación para el plugin (puedes moverlo a un Secret si es necesario)
const AUTH_TOKEN_SECRETO = "MI_TOKEN_SECRETO_PARA_PLUGINS"; 

// =======================================================================
// === CONFIGURACIÓN DE SERVICIOS Y AUTENTICACIÓN ===
// Define dónde debe inyectarse la API Key para cada servicio
// 'key_location': 'query' (URL) o 'header' (Authorization)
// 'key_name_in_api': Nombre del parámetro en la URL o la cabecera
// 'env_key_name': Nombre del Secret configurado en el panel de Cloudflare
// =======================================================================
const SERVICE_CONFIGS = {
    // Bancos de Imágenes / APIs conocidas
    "Pixabay": { 
        key_location: 'query', 
        key_name_in_api: 'key', 
        env_key_name: 'PIXABAY_API_KEY' 
    },
    "Pexels": { 
        key_location: 'header', 
        key_name_in_api: 'Authorization', 
        auth_prefix: '', // Pexels usa la clave directamente como encabezado
        env_key_name: 'PEXELS_API_KEY' 
    },
    "Unsplash": { 
        key_location: 'header', 
        key_name_in_api: 'Authorization', 
        auth_prefix: 'Client-ID ', 
        env_key_name: 'UNSPLASH_API_KEY' 
    },
    "GIPHY": { 
        key_location: 'query', 
        key_name_in_api: 'api_key', 
        env_key_name: 'GIPHY_API_KEY' 
    },
    "Flickr": { 
        key_location: 'query', 
        key_name_in_api: 'api_key', 
        env_key_name: 'FLICKR_API_KEY' 
    },

    // Servicios de IA / Google
    "Gemini": { 
        key_location: 'query', 
        key_name_in_api: 'key', 
        env_key_name: 'GEMINI_API_KEY' 
    },
    "ReplicateAI": {
        key_location: 'header',
        key_name_in_api: 'Authorization',
        auth_prefix: 'Token ',
        env_key_name: 'REPLICATE_API_KEY'
    },
    
    // Servicios que no requieren API Key (ej. scraping o APIs públicas)
    "Openverse": { 
        key_location: 'none' 
    },
    "Youtube": { 
        key_location: 'none' 
    },
    // Nota: Para Google Image (API), Stable Diffusion, DALL·E, Cloudflare Workers AI,
    // debes añadir sus configuraciones específicas (header o query) aquí.

};


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
                    'Access-Control-Allow-Headers': 'Content-Type, Token', // Permitir cabecera 'Token'
                }
            });
        }

        const url = new URL(request.url);
        
        // 1. Obtener parámetros Fijos para la Autenticación y Reenvío
        
        // Se puede enviar en la URL o en la cabecera X-Auth-Token
        const incomingToken = url.searchParams.get('token') || request.headers.get('Token'); 
        const serviceName = url.searchParams.get('servicio');
        const destinationUrl = url.searchParams.get('url');
        
        // 2. Validación de Parámetros Fijos
        
        if (incomingToken !== AUTH_TOKEN_SECRETO) {
            return new Response('Token de autenticación inválido.', { status: 403 });
        }
        
        if (!serviceName || !destinationUrl) {
            return new Response('Parámetros "servicio" o "url" faltantes.', { status: 400 });
        }

        const config = SERVICE_CONFIGS[serviceName];
        if (!config) {
            return new Response(`Servicio "${serviceName}" no reconocido.`, { status: 404 });
        }

        // 3. Preparar la URL y las Cabeceras para el Servicio Destino
        
        const finalUrl = new URL(destinationUrl);
        let headers = new Headers(request.headers);
        
        // Eliminar cabeceras de autenticación propias (si se enviaron en las cabeceras)
        headers.delete('Token'); 

        // 4. INYECCIÓN DINÁMICA DE LA API KEY (Autenticación)
        const apiKey = config.env_key_name ? env[config.env_key_name] : null;

        if (config.key_location === 'query' && apiKey) {
            // Inyectar la clave en los parámetros de la URL
            finalUrl.searchParams.append(config.key_name_in_api, apiKey);

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
        const requestOptions = {
            method: request.method,
            headers: headers,
        };

        // Incluir el cuerpo de la solicitud solo para POST o PUT
        if (request.method === 'POST' || request.method === 'PUT') {
            requestOptions.body = request.body;
        }

        // Reenviar la solicitud a la URL final
        const response = await fetch(finalUrl.toString(), requestOptions);
        
        // 6. Devolver la Respuesta TAL CUAL
        
        // Devolver la respuesta del servicio destino con el mismo estado, cabeceras y cuerpo.
        // Las cabeceras CORS deben añadirse para que el plugin pueda leer la respuesta.
        const responseHeaders = new Headers(response.headers);
        responseHeaders.set('Access-Control-Allow-Origin', '*');
        responseHeaders.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');

        return new Response(response.body, {
            status: response.status,
            statusText: response.statusText,
            headers: responseHeaders,
        });

    }
}
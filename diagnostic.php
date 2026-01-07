<?php
/**
 * Automated diagnostics for All Sources Images.
 *
 * Quick usage examples:
 *  - Web:  https://example.com/wp-content/plugins/all-sources-images/diagnostic.php?busqueda=perros&sources_no_ia=auto&notify=errors
 *  - Cron: php diagnostic.php busqueda="tech news" sources_no_ia=all sources_ia=auto notify=always email=you@site.com format=text
 *
 * Query/CLI parameters:
 *  - busqueda        : Keyword to test (default: "nature landscape").
 *  - sources_no_ia   : non-AI sources (all | auto | comma list | none).
 *  - sources_ia      : AI sources (same accepted values as above).
 *  - email           : Comma/semicolon separated recipient list (fallback: admin_email).
 *  - notify          : always | errors | never (default: errors -> notify only on failures).
 *  - format          : html | text | json (default html for web, text for CLI).
 *  - token           : Optional shared secret. If ALLSI_DIAGNOSTIC_TOKEN constant or ALLSI_diagnostic_token option is set, requests must provide it.
 */

define( 'ALLSI_DIAGNOSTIC_START', microtime( true ) );

// Bootstrap WordPress when the script is executed directly.
if ( ! defined( 'ABSPATH' ) ) {
    $wp_load_path = dirname( __DIR__, 3 ) . '/wp-load.php';
    if ( file_exists( $wp_load_path ) ) {
        require_once $wp_load_path;
    } else {
        http_response_code( 500 );
        echo 'Unable to locate wp-load.php. Please run this script from inside a WordPress installation.';
        exit;
    }
}

if ( ! function_exists( 'wp_mail' ) ) {
    require_once ABSPATH . WPINC . '/pluggable.php';
}

// Ensure core plugin classes are available even if the plugin is inactive.
if ( ! class_exists( 'All_Sources_Images_Admin' ) ) {
    require_once __DIR__ . '/admin/class-all-sources-images-admin.php';
}
if ( ! class_exists( 'All_Sources_Images_Generation' ) ) {
    require_once __DIR__ . '/admin/class-all-sources-images-generation.php';
}

$plugin_name    = 'all-sources-images';
$plugin_version = defined( 'ALL_SOURCES_IMAGES_VERSION' ) ? ALL_SOURCES_IMAGES_VERSION : '1.0.0';
$generation     = new All_Sources_Images_Generation( $plugin_name, $plugin_version );
$params         = ALLSI_diag_collect_params();

ALLSI_diag_guard_token( $params );

$search_term = sanitize_text_field( $params['busqueda'] );
if ( '' === $search_term ) {
    $search_term = 'nature landscape';
}

$notify_mode = ALLSI_diag_resolve_notify_mode( $params['notify'] );
$format      = ALLSI_diag_resolve_format( $params['format'] );
$recipients  = ALLSI_diag_resolve_recipients( $params['email'] );
if ( empty( $recipients ) ) {
    $admin_email = get_option( 'admin_email' );
    if ( $admin_email && is_email( $admin_email ) ) {
        $recipients = array( $admin_email );
    }
}

$options_main  = wp_parse_args( get_option( 'ALLSI_plugin_main_settings' ), $generation->ALLSI_default_options_main_settings( true ) );
$options_banks = wp_parse_args( get_option( 'ALLSI_plugin_banks_settings' ), $generation->ALLSI_default_options_banks_settings( true ) );
$options_cron  = wp_parse_args( get_option( 'ALLSI_plugin_cron_settings' ), $generation->ALLSI_default_options_cron_settings( true ) );
$merged_options = array_merge( $options_main, $options_banks, $options_cron );
$proxy_args      = $generation->ALLSI_get_proxy_args();

$available_codes = ALLSI_diag_extract_codes( $generation->ALLSI_banks_name_auto() );
$ai_codes        = array_values( array_unique( array_map( 'sanitize_key', $generation->ALLSI_ai_source_codes() ) ) );
$non_ai_codes    = array_values( array_diff( $available_codes, $ai_codes ) );

$selected_non_ai = ALLSI_diag_resolve_sources( $params['sources_no_ia'], 'non_ai', $options_banks, $non_ai_codes, $ai_codes );
$selected_ai     = ALLSI_diag_resolve_sources( $params['sources_ia'], 'ai', $options_banks, $non_ai_codes, $ai_codes );
$targets         = array_values( array_unique( array_merge( $selected_non_ai, $selected_ai ) ) );

if ( empty( $targets ) ) {
    ALLSI_diag_render_and_exit( array(
        'generated_at' => current_time( 'mysql' ),
        'search_term'  => $search_term,
        'results'      => array(),
        'error_count'  => 0,
        'warning_count'=> 0,
        'duration_ms'  => ALLSI_diag_duration_ms(),
        'message'      => 'No sources selected. Provide sources_no_ia or sources_ia parameters.',
    ), $format );
}

$source_manager = ALLSI_diag_get_source_manager( $generation );
if ( ! $source_manager ) {
    ALLSI_diag_render_and_exit( array(
        'generated_at' => current_time( 'mysql' ),
        'search_term'  => $search_term,
        'results'      => array(),
        'error_count'  => 1,
        'warning_count'=> 0,
        'duration_ms'  => ALLSI_diag_duration_ms(),
        'message'      => 'Unable to initialize source manager.',
    ), $format, 500 );
}

$results = array();
foreach ( $targets as $bank ) {
    $category      = in_array( $bank, $ai_codes, true ) ? 'ai' : 'non_ai';
    $results[]     = ALLSI_diag_run_check( $generation, $source_manager, $bank, $search_term, $category, $merged_options, $proxy_args );
}

$error_count   = count( array_filter( $results, function( $item ) {
    return isset( $item['status'] ) && 'error' === $item['status'];
} ) );
$warning_count = count( array_filter( $results, function( $item ) {
    return isset( $item['status'] ) && 'warning' === $item['status'];
} ) );
$status_label  = $error_count > 0 ? 'Errors detected' : ( $warning_count > 0 ? 'Warnings detected' : 'All good' );

$summary = array(
    'generated_at'  => current_time( 'mysql' ),
    'site'          => get_home_url(),
    'search_term'   => $search_term,
    'results'       => $results,
    'error_count'   => $error_count,
    'warning_count' => $warning_count,
    'ok_count'      => count( $results ) - $error_count - $warning_count,
    'duration_ms'   => ALLSI_diag_duration_ms(),
    'status_label'  => $status_label,
    'notify_mode'   => $notify_mode,
);

$should_email = false;
if ( 'always' === $notify_mode ) {
    $should_email = true;
} elseif ( 'errors' === $notify_mode && ( $error_count > 0 || $warning_count > 0 ) ) {
    $should_email = true;
}

if ( $should_email && ! empty( $recipients ) ) {
    ALLSI_diag_send_email( $recipients, $summary );
}

ALLSI_diag_render_and_exit( $summary, $format );

// -----------------------------------------------------------------------------
// Helper functions
// -----------------------------------------------------------------------------

function ALLSI_diag_collect_params() {
    $defaults = array(
        'busqueda'      => '',
        'sources_no_ia' => 'auto',
        'sources_ia'    => 'auto',
        'email'         => '',
        'notify'        => 'errors',
        'format'        => '',
        'token'         => '',
    );
    $params = $defaults;
    $request = $_REQUEST ?? array();
    foreach ( $request as $key => $value ) {
        if ( ! is_scalar( $value ) ) {
            continue;
        }
        $params[ $key ] = trim( (string) $value );
    }
    if ( PHP_SAPI === 'cli' && ! empty( $GLOBALS['argv'] ) ) {
        foreach ( $GLOBALS['argv'] as $index => $argument ) {
            if ( 0 === $index ) {
                continue;
            }
            if ( false !== strpos( $argument, '=' ) ) {
                list( $key, $value ) = explode( '=', $argument, 2 );
                $params[ trim( $key ) ] = trim( $value );
            }
        }
    }
    return $params;
}

function ALLSI_diag_guard_token( array $params ) {
    $expected = '';
    if ( defined( 'ALLSI_DIAGNOSTIC_TOKEN' ) && ALLSI_DIAGNOSTIC_TOKEN ) {
        $expected = ALLSI_DIAGNOSTIC_TOKEN;
    } else {
        $stored = get_option( 'ALLSI_diagnostic_token' );
        if ( is_string( $stored ) ) {
            $expected = $stored;
        }
    }
    if ( '' === $expected ) {
        return;
    }
    $provided = isset( $params['token'] ) ? (string) $params['token'] : '';
    if ( $provided === '' || ! hash_equals( $expected, $provided ) ) {
        http_response_code( 403 );
        exit( 'Invalid diagnostic token.' );
    }
}

function ALLSI_diag_resolve_notify_mode( $value ) {
    $value = strtolower( trim( (string) $value ) );
    if ( in_array( $value, array( 'always', 'siempre', 'all', '1', 'true' ), true ) ) {
        return 'always';
    }
    if ( in_array( $value, array( 'never', 'nunca', '0', 'false', 'off' ), true ) ) {
        return 'never';
    }
    if ( in_array( $value, array( 'errors', 'errores', 'only_errors' ), true ) ) {
        return 'errors';
    }
    return 'errors';
}

function ALLSI_diag_resolve_format( $value ) {
    $value = strtolower( trim( (string) $value ) );
    if ( in_array( $value, array( 'json', 'text', 'html' ), true ) ) {
        return $value;
    }
    return PHP_SAPI === 'cli' ? 'text' : 'html';
}

function ALLSI_diag_resolve_recipients( $value ) {
    if ( empty( $value ) ) {
        return array();
    }
    $parts = preg_split( '/[,;\s]+/', $value );
    $valid = array();
    foreach ( $parts as $email ) {
        $email = trim( $email );
        if ( $email && is_email( $email ) ) {
            $valid[] = $email;
        }
    }
    return array_values( array_unique( $valid ) );
}

function ALLSI_diag_extract_codes( array $banks_map ) {
    $codes = array();
    foreach ( $banks_map as $data ) {
        if ( is_array( $data ) && ! empty( $data[0] ) ) {
            $codes[] = sanitize_key( $data[0] );
        }
    }
    return array_values( array_unique( $codes ) );
}

function ALLSI_diag_resolve_sources( $raw, $category, $bank_options, $non_ai_codes, $ai_codes ) {
    $allowed = ( 'ai' === $category ) ? $ai_codes : $non_ai_codes;
    if ( empty( $allowed ) ) {
        return array();
    }
    $raw = strtolower( trim( (string) $raw ) );
    if ( '' === $raw || in_array( $raw, array( 'none', 'ninguna', '0', 'false' ), true ) ) {
        return array();
    }
    if ( in_array( $raw, array( 'all', 'todos', '*', 'todo' ), true ) ) {
        return $allowed;
    }
    $selected = array();
    if ( in_array( $raw, array( 'auto', 'selected', 'seleccionados', 'default' ), true ) ) {
        $auto = isset( $bank_options['api_chosen_auto'] ) ? (array) $bank_options['api_chosen_auto'] : array();
        $manual = isset( $bank_options['api_chosen_manual'] ) ? (array) $bank_options['api_chosen_manual'] : array();
        $selected = array_merge( $auto, $manual );
    } else {
        $selected = preg_split( '/[\s,;|]+/', $raw );
    }
    $selected = array_map( 'sanitize_key', array_filter( array_map( 'trim', $selected ) ) );
    $selected = array_values( array_intersect( $selected, $allowed ) );
    return $selected;
}

function ALLSI_diag_get_source_manager( $generation ) {
    try {
        $reflection = new ReflectionClass( $generation );
        $method     = $reflection->getMethod( 'ALLSI_get_source_manager_instance' );
        $method->setAccessible( true );
        return $method->invoke( $generation );
    } catch ( Exception $e ) {
        error_log( '[All Sources Images][Diagnostics] Unable to access source manager: ' . $e->getMessage() );
        return null;
    }
}

function ALLSI_diag_run_check( $generation, $source_manager, $bank, $search_term, $category, $options, $proxy_args ) {
    $result = array(
        'bank'        => $bank,
        'category'    => $category,
        'status'      => 'skipped',
        'message'     => '',
        'duration_ms' => 0,
    );

    if ( ! $source_manager || ! $source_manager->has_source( $bank ) ) {
        $result['status']  = 'skipped';
        $result['message'] = 'Source not registered.';
        return $result;
    }

    $source = $source_manager->get_source( $bank );
    if ( ! $source ) {
        $result['status']  = 'skipped';
        $result['message'] = 'Unable to load source handler.';
        return $result;
    }

    $context = array(
        'img_block'      => array(
            'api_chosen'    => $bank,
            'based_on'      => 'title',
            'selected_image'=> 'random_result',
        ),
        'options'        => $options,
        'search'         => $search_term,
        'log'            => null,
        'post_id'        => 0,
        'get_only_thumb' => true,
        'selected_image' => 'random_result',
        'proxy_args'     => $proxy_args,
        'generation'     => $generation,
        'page'           => 1,
    );

    $start    = microtime( true );
    $response = $source->generate( $context );
    $result['duration_ms'] = round( ( microtime( true ) - $start ) * 1000, 2 );

    if ( is_wp_error( $response ) ) {
        $result['status']  = 'error';
        $result['message'] = $response->get_error_message();
        $data = $response->get_error_data();
        if ( $data ) {
            $result['details'] = $data;
        }
        return $result;
    }

    if ( empty( $response ) ) {
        $result['status']  = 'warning';
        $result['message'] = 'Empty response.';
        return $result;
    }

    $items = ALLSI_diag_estimate_items( $response );
    if ( null === $items ) {
        $result['status']  = 'ok';
        $result['message'] = 'Response received.';
    } elseif ( $items > 0 ) {
        $result['status']  = 'ok';
        $result['message'] = sprintf( '%d items returned.', $items );
        $result['items']   = $items;
    } else {
        $result['status']  = 'warning';
        $result['message'] = 'No results returned.';
        $result['items']   = 0;
    }

    $result['preview'] = ALLSI_diag_preview_payload( $response );
    return $result;
}

function ALLSI_diag_estimate_items( $payload ) {
    if ( ! is_array( $payload ) ) {
        return null;
    }
    foreach ( array( 'images', 'items', 'photos', 'data', 'results' ) as $key ) {
        if ( isset( $payload[ $key ] ) && is_array( $payload[ $key ] ) ) {
            return count( $payload[ $key ] );
        }
    }
    return null;
}

function ALLSI_diag_preview_payload( $payload ) {
    if ( is_array( $payload ) ) {
        $encoded = wp_json_encode( $payload );
        if ( is_string( $encoded ) ) {
            return ALLSI_diag_truncate( $encoded, 600 );
        }
    }
    if ( is_string( $payload ) ) {
        return ALLSI_diag_truncate( $payload, 300 );
    }
    return '';
}

function ALLSI_diag_send_email( array $recipients, array $summary ) {
    $subject = sprintf(
        '[All Sources Images] %s (%s errors, %s warnings)',
        $summary['status_label'],
        $summary['error_count'],
        $summary['warning_count']
    );
    $body    = ALLSI_diag_render_html( $summary );
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    wp_mail( $recipients, $subject, $body, $headers );
}

function ALLSI_diag_render_and_exit( array $summary, $format, $status_code = 200 ) {
    if ( ! headers_sent() ) {
        http_response_code( $status_code );
    }
    if ( 'json' === $format ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        echo wp_json_encode( $summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    } elseif ( 'text' === $format ) {
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo ALLSI_diag_render_text( $summary );
    } else {
        header( 'Content-Type: text/html; charset=utf-8' );
        echo ALLSI_diag_render_html( $summary );
    }
    exit;
}

/**
 * Render diagnostic results as HTML.
 *
 * Note: Inline styles are used intentionally here because this function generates
 * a standalone HTML document (for email reports and CLI output) outside the WordPress
 * admin context where wp_add_inline_style() would be available.
 *
 * @param array $summary Diagnostic summary data.
 * @return string HTML document.
 */
function ALLSI_diag_render_html( array $summary ) {
    $results = $summary['results'] ?? array();
    ob_start();
    // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Standalone HTML document for email/CLI output.
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>All Sources Images · Diagnostics</title>
        <style>
            body { font-family: Arial, sans-serif; background:#f7f7f9; color:#1b1b1f; margin:0; padding:32px; }
            h1 { margin-top:0; }
            table { width:100%; border-collapse:collapse; margin-top:24px; background:#fff; }
            th, td { padding:10px 12px; border-bottom:1px solid #e2e4ea; text-align:left; }
            th { background:#f0f2f7; font-size:13px; text-transform:uppercase; letter-spacing:0.04em; }
            .status-ok { color:#1b873f; font-weight:600; }
            .status-warning { color:#b78103; font-weight:600; }
            .status-error { color:#c53d3d; font-weight:600; }
            .status-skipped { color:#687078; }
            small { color:#555; }
        </style>
        <?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
    </head>
    <body>
        <h1>All Sources Images · Diagnostics</h1>
        <p><strong>Site:</strong> <?php echo esc_html( $summary['site'] ?? get_home_url() ); ?> ·
           <strong>Generated:</strong> <?php echo esc_html( $summary['generated_at'] ?? current_time( 'mysql' ) ); ?> ·
           <strong>Search:</strong> "<?php echo esc_html( $summary['search_term'] ?? '' ); ?>" ·
           <strong>Duration:</strong> <?php echo esc_html( $summary['duration_ms'] ?? 0 ); ?> ms</p>
        <p><strong>Status:</strong> <?php echo esc_html( $summary['status_label'] ?? 'n/a' ); ?> ·
           <strong>Totals:</strong> OK <?php echo intval( $summary['ok_count'] ?? 0 ); ?> ·
           Warnings <?php echo intval( $summary['warning_count'] ?? 0 ); ?> ·
           Errors <?php echo intval( $summary['error_count'] ?? 0 ); ?></p>
        <?php if ( empty( $results ) ) : ?>
            <p>No sources were tested. Review parameters "sources_no_ia" / "sources_ia".</p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Items</th>
                        <th>Time (ms)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $results as $row ) :
                    $status = strtolower( $row['status'] );
                ?>
                    <tr>
                        <td><?php echo esc_html( $row['bank'] ); ?></td>
                        <td><?php echo esc_html( strtoupper( $row['category'] ) ); ?></td>
                        <td class="status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( strtoupper( $row['status'] ) ); ?></td>
                        <td><?php echo esc_html( $row['message'] ); ?></td>
                        <td><?php echo isset( $row['items'] ) ? intval( $row['items'] ) : '—'; ?></td>
                        <td><?php echo esc_html( $row['duration_ms'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p><small>Notification mode: <?php echo esc_html( $summary['notify_mode'] ?? 'errors' ); ?></small></p>
    </body>
    </html>
    <?php
    return trim( ob_get_clean() );
}

function ALLSI_diag_render_text( array $summary ) {
    $lines   = array();
    $lines[] = 'All Sources Images :: Diagnostics';
    $lines[] = 'Site: ' . ( $summary['site'] ?? get_home_url() );
    $lines[] = 'Generated: ' . ( $summary['generated_at'] ?? current_time( 'mysql' ) );
    $lines[] = 'Search: "' . ( $summary['search_term'] ?? '' ) . '"';
    $lines[] = 'Status: ' . ( $summary['status_label'] ?? 'n/a' );
    $lines[] = sprintf( 'Totals -> OK:%d  Warnings:%d  Errors:%d', intval( $summary['ok_count'] ?? 0 ), intval( $summary['warning_count'] ?? 0 ), intval( $summary['error_count'] ?? 0 ) );
    $lines[] = str_repeat( '-', 48 );
    foreach ( $summary['results'] ?? array() as $row ) {
        $lines[] = sprintf( '[%s] %s (%s) -> %s', strtoupper( $row['status'] ), $row['bank'], $row['category'], $row['message'] );
    }
    return implode( PHP_EOL, $lines ) . PHP_EOL;
}

function ALLSI_diag_duration_ms() {
    return round( ( microtime( true ) - ALLSI_DIAGNOSTIC_START ) * 1000, 2 );
}

function ALLSI_diag_truncate( $text, $length ) {
    if ( ! is_string( $text ) ) {
        return '';
    }
    if ( function_exists( 'mb_substr' ) ) {
        return mb_substr( $text, 0, $length );
    }
    return substr( $text, 0, $length );
}

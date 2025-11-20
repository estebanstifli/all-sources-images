<?php
/**
 * Diagnostic script for All Sources Images plugin
 * Upload to wp-content/plugins/all-sources-images/ and access via browser
 * URL: https://casaydinero.es/wp-content/plugins/all-sources-images/diagnostic.php
 */

// Prevent direct access without WordPress
if (!defined('ABSPATH')) {
    define('DIAGNOSTICS_STANDALONE', true);
}

echo "<h1>All Sources Images - Diagnostic Report</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .pass{color:green;} .fail{color:red;} .warn{color:orange;}</style>";

// Check 1: File encoding (BOM detection)
echo "<h2>1. File Encoding Check (BOM Detection)</h2>";
$files_to_check = array(
    'all-sources-images.php',
    'includes/class-all-sources-images.php',
    'includes/class-all-sources-images-activator.php',
    'includes/class-all-sources-images-loader.php',
    'includes/asi-helpers.php',
    'admin/class-all-sources-images-admin.php',
    'admin/class-all-sources-images-generation.php',
);

$bom_found = false;
foreach ($files_to_check as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $contents = file_get_contents($filepath);
        $first_bytes = substr($contents, 0, 3);
        $has_bom = ($first_bytes === "\xEF\xBB\xBF");
        
        if ($has_bom) {
            echo "<p class='fail'>✗ FAIL: <strong>$file</strong> has UTF-8 BOM</p>";
            $bom_found = true;
        } else {
            echo "<p class='pass'>✓ PASS: <strong>$file</strong> is clean</p>";
        }
        
        // Check for whitespace before <?php
        if (preg_match('/^[\s\n\r]+<\?php/', $contents)) {
            echo "<p class='fail'>✗ FAIL: <strong>$file</strong> has whitespace before &lt;?php tag</p>";
        }
    } else {
        echo "<p class='warn'>⚠ WARNING: <strong>$file</strong> not found</p>";
    }
}

if (!$bom_found) {
    echo "<p class='pass'><strong>✓ All files are clean (no BOM detected)</strong></p>";
}

// Check 2: PHP syntax errors
echo "<h2>2. PHP Syntax Check</h2>";
foreach ($files_to_check as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $output = array();
        $return_var = 0;
        exec("php -l " . escapeshellarg($filepath) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<p class='pass'>✓ PASS: <strong>$file</strong> - No syntax errors</p>";
        } else {
            echo "<p class='fail'>✗ FAIL: <strong>$file</strong> - " . implode("<br>", $output) . "</p>";
        }
    }
}

// Check 3: WordPress environment (if available)
echo "<h2>3. WordPress Environment Check</h2>";
if (defined('DIAGNOSTICS_STANDALONE')) {
    echo "<p class='warn'>⚠ WordPress not loaded (standalone mode)</p>";
    echo "<p>To run full diagnostics, add this to your wp-config.php temporarily:</p>";
    echo "<pre>define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);</pre>";
} else {
    // WordPress is loaded
    echo "<p class='pass'>✓ WordPress is loaded</p>";
    
    // Check plugin activation status
    if (is_plugin_active('all-sources-images/all-sources-images.php')) {
        echo "<p class='pass'>✓ Plugin is ACTIVE</p>";
    } else {
        echo "<p class='fail'>✗ Plugin is NOT ACTIVE</p>";
    }
    
    // Check capabilities
    global $current_user;
    wp_get_current_user();
    echo "<p>Current user: <strong>" . $current_user->user_login . "</strong></p>";
    echo "<p>Has 'asi_manage' capability: " . (current_user_can('asi_manage') ? '<span class="pass">YES</span>' : '<span class="fail">NO</span>') . "</p>";
    echo "<p>Has 'manage_options' capability: " . (current_user_can('manage_options') ? '<span class="pass">YES</span>' : '<span class="fail">NO</span>') . "</p>";
    
    // Check plugin options
    $main_settings = get_option('ASI_plugin_main_settings');
    echo "<p>ASI_plugin_main_settings exists: " . ($main_settings ? '<span class="pass">YES</span>' : '<span class="warn">NO (not initialized yet)</span>') . "</p>";
}

// Check 4: File permissions
echo "<h2>4. File Permissions Check</h2>";
foreach ($files_to_check as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $perms = fileperms($filepath);
        $perms_string = substr(sprintf('%o', $perms), -4);
        $readable = is_readable($filepath);
        
        if ($readable) {
            echo "<p class='pass'>✓ <strong>$file</strong> - Permissions: $perms_string (readable)</p>";
        } else {
            echo "<p class='fail'>✗ <strong>$file</strong> - Permissions: $perms_string (NOT readable)</p>";
        }
    }
}

// Check 5: PHP version and extensions
echo "<h2>5. Server Environment</h2>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong> " . (version_compare(phpversion(), '7.3.0', '>=') ? '<span class="pass">(✓ OK)</span>' : '<span class="fail">(✗ Requires 7.3+)</span>') . "</p>";
echo "<p>WordPress Version: <strong>" . (defined('DIAGNOSTICS_STANDALONE') ? 'N/A (standalone)' : get_bloginfo('version')) . "</strong></p>";

$required_extensions = array('curl', 'json', 'mbstring', 'gd');
echo "<p>Required PHP Extensions:</p><ul>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<li class='" . ($loaded ? 'pass' : 'fail') . "'>" . ($loaded ? '✓' : '✗') . " $ext</li>";
}
echo "</ul>";

// Check 6: Recent errors in debug.log (if accessible)
echo "<h2>6. Recent Debug Log Entries</h2>";
$debug_log_path = dirname(dirname(dirname(__DIR__))) . '/debug.log';
if (file_exists($debug_log_path)) {
    $log_contents = file_get_contents($debug_log_path);
    $log_lines = explode("\n", $log_contents);
    $recent_asi_errors = array();
    
    // Get last 50 lines
    $recent_lines = array_slice($log_lines, -50);
    
    foreach ($recent_lines as $line) {
        if (stripos($line, 'all-sources') !== false || stripos($line, 'all_sources') !== false || stripos($line, 'ASI_') !== false) {
            $recent_asi_errors[] = $line;
        }
    }
    
    if (count($recent_asi_errors) > 0) {
        echo "<p class='warn'>Found " . count($recent_asi_errors) . " recent entries related to this plugin:</p>";
        echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;'>";
        foreach ($recent_asi_errors as $error) {
            echo htmlspecialchars($error) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='pass'>No errors found in debug.log related to this plugin</p>";
    }
} else {
    echo "<p class='warn'>Debug log not found at: $debug_log_path</p>";
    echo "<p>Enable debug logging in wp-config.php</p>";
}

echo "<hr><p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";

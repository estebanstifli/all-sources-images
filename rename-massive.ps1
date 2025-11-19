# Script de renombrado masivo
$ErrorActionPreference = "Continue"

Write-Host "Iniciando renombrado masivo..." -ForegroundColor Green

$excludeDirs = @(
    "*\admin\partials\freemius\*",
    "*\admin\partials\monolog\vendor\*",
    "*\includes\php-ml\vendor\*",
    "*\.git\*"
)

$files = Get-ChildItem -Path "." -Include *.php,*.js,*.css,*.txt,*.md -Recurse | Where-Object {
    $file = $_
    $exclude = $false
    foreach ($dir in $excludeDirs) {
        if ($file.FullName -like $dir) {
            $exclude = $true
            break
        }
    }
    -not $exclude
}

Write-Host "Procesando $($files.Count) archivos..." -ForegroundColor Yellow

$totalChanges = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    # CLASES PHP
    $content = $content -replace 'class Magic_Post_Thumbnail_Loader', 'class All_Sources_Images_Loader'
    $content = $content -replace 'class Magic_Post_Thumbnail_i18n', 'class All_Sources_Images_i18n'
    $content = $content -replace 'class Magic_Post_Thumbnail_Activator', 'class All_Sources_Images_Activator'
    $content = $content -replace 'class Magic_Post_Thumbnail_Deactivator', 'class All_Sources_Images_Deactivator'
    $content = $content -replace 'class Magic_Post_Thumbnail_Admin', 'class All_Sources_Images_Admin'
    $content = $content -replace 'class Magic_Post_Thumbnail_Generation extends Magic_Post_Thumbnail_Admin', 'class All_Sources_Images_Generation extends All_Sources_Images_Admin'
    $content = $content -replace 'class Magic_Post_Thumbnail_Public', 'class All_Sources_Images_Public'
    $content = $content -replace 'class Magic_Post_Thumbnail([^_]|\s|$)', 'class All_Sources_Images$1'
    
    # Referencias a clases
    $content = $content -replace 'Magic_Post_Thumbnail_Loader', 'All_Sources_Images_Loader'
    $content = $content -replace 'Magic_Post_Thumbnail_i18n', 'All_Sources_Images_i18n'
    $content = $content -replace 'Magic_Post_Thumbnail_Activator', 'All_Sources_Images_Activator'
    $content = $content -replace 'Magic_Post_Thumbnail_Deactivator', 'All_Sources_Images_Deactivator'
    $content = $content -replace 'Magic_Post_Thumbnail_Admin', 'All_Sources_Images_Admin'
    $content = $content -replace 'Magic_Post_Thumbnail_Generation', 'All_Sources_Images_Generation'
    $content = $content -replace 'Magic_Post_Thumbnail_Public', 'All_Sources_Images_Public'
    $content = $content -replace 'Magic_Post_Thumbnail([^_]|\s|$)', 'All_Sources_Images$1'
    
    # CONSTANTES
    $content = $content -replace 'MAGIC_POST_THUMBNAIL_VERSION', 'ALL_SOURCES_IMAGES_VERSION'
    $content = $content -replace 'MPT_FREEMIUS_UNINSTALL', 'ASI_FREEMIUS_UNINSTALL'
    
    # FUNCIONES GLOBALES
    $content = $content -replace '\bmpt_freemius\b', 'asi_freemius'
    $content = $content -replace 'activate_magic_post_thumbnail', 'activate_all_sources_images'
    $content = $content -replace 'deactivate_magic_post_thumbnail', 'deactivate_all_sources_images'
    $content = $content -replace 'run_magic_post_thumbnail', 'run_all_sources_images'
    $content = $content -replace 'mpt_freemius_uninstall_cleanup', 'asi_freemius_uninstall_cleanup'
    
    # MÉTODOS DE CLASE
    $content = $content -replace '\bMPT_', 'ASI_'
    
    # WORDPRESS OPTIONS
    $content = $content -replace 'MPT_plugin_', 'ASI_plugin_'
    $content = $content -replace 'MPT-plugin-', 'ASI-plugin-'
    
    # HOOKS
    $content = $content -replace 'mpt_freemius_loaded', 'asi_freemius_loaded'
    $content = $content -replace 'mpt_generate_scheduled_image', 'asi_generate_scheduled_image'
    $content = $content -replace 'mpt_hide_notice', 'asi_hide_notice'
    $content = $content -replace 'mpt_remind_later', 'asi_remind_later'
    
    # AJAX ACTIONS
    $content = $content -replace "'generate_image'", "'asi_generate_image'"
    $content = $content -replace '"generate_image"', '"asi_generate_image"'
    $content = $content -replace "'test_apis'", "'asi_test_apis'"
    $content = $content -replace '"test_apis"', '"asi_test_apis"'
    $content = $content -replace "'block_searching_images'", "'asi_block_searching_images'"
    $content = $content -replace '"block_searching_images"', '"asi_block_searching_images"'
    $content = $content -replace "'block_downloading_image'", "'asi_block_downloading_image'"
    $content = $content -replace '"block_downloading_image"', '"asi_block_downloading_image"'
    
    # CAPABILITIES
    $content = $content -replace 'mpt_manage', 'asi_manage'
    
    # JAVASCRIPT
    $content = $content -replace '\bmptAjax\b', 'asiAjax'
    $content = $content -replace 'generationJsVars', 'asiGenerationVars'
    
    # ADMIN MENU
    $content = $content -replace 'magic-post-thumbnail-admin-display', 'all-sources-images-admin-display'
    
    # TEXT DOMAIN
    $content = $content -replace "'mpt'", "'all-sources-images'"
    $content = $content -replace '"mpt"', '"all-sources-images"'
    $content = $content -replace "'magic-post-thumbnail'", "'all-sources-images'"
    $content = $content -replace '"magic-post-thumbnail"', '"all-sources-images"'
    
    # GUTENBERG BLOCK
    $content = $content -replace "'mpt/mpt-images'", "'asi/asi-images'"
    $content = $content -replace '"mpt/mpt-images"', '"asi/asi-images"'
    $content = $content -replace 'MPT Images', 'ASI Images'
    
    # PACKAGE NAMES
    $content = $content -replace '@package\s+Magic_Post_Thumbnail', '@package    All_Sources_Images'
    $content = $content -replace '@subpackage\s+Magic_Post_Thumbnail/', '@subpackage All_Sources_Images/'
    
    # REQUIRES
    $content = $content -replace 'class-magic-post-thumbnail', 'class-all-sources-images'
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $totalChanges++
        Write-Host "Modificado: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "`n=== COMPLETADO ===" -ForegroundColor Green
Write-Host "Archivos modificados: $totalChanges" -ForegroundColor Cyan

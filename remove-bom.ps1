# Script to remove BOM (Byte Order Mark) from PHP files
# BOM causes "headers already sent" errors in WordPress

$pluginPath = "c:\prueba\magic-post-thumbnail"
$filesFixed = 0
$filesChecked = 0

Write-Host "Scanning for PHP files with BOM..." -ForegroundColor Cyan

Get-ChildItem -Path $pluginPath -Filter "*.php" -Recurse | ForEach-Object {
    $filesChecked++
    $filePath = $_.FullName
    
    # Read file as bytes
    $bytes = [System.IO.File]::ReadAllBytes($filePath)
    
    # Check for UTF-8 BOM (EF BB BF)
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        Write-Host "  [BOM FOUND] $($_.Name)" -ForegroundColor Yellow
        
        # Remove BOM (skip first 3 bytes)
        $newBytes = $bytes[3..($bytes.Length - 1)]
        
        # Write back without BOM
        [System.IO.File]::WriteAllBytes($filePath, $newBytes)
        
        $filesFixed++
        Write-Host "    -> Fixed!" -ForegroundColor Green
    }
}

Write-Host "`nScan complete:" -ForegroundColor Cyan
Write-Host "  Files checked: $filesChecked" -ForegroundColor White
Write-Host "  Files fixed: $filesFixed" -ForegroundColor Green

if ($filesFixed -eq 0) {
    Write-Host "`nNo BOM found. Checking for extra whitespace..." -ForegroundColor Cyan
    
    # Check main plugin file for whitespace before <?php
    $mainFile = "$pluginPath\all-sources-images.php"
    $content = Get-Content $mainFile -Raw
    
    if ($content -match '^\s+<\?php') {
        Write-Host "  [WHITESPACE] Found spaces/newlines before <?php in all-sources-images.php" -ForegroundColor Yellow
        $content = $content -replace '^\s+(<\?php)', '$1'
        Set-Content -Path $mainFile -Value $content -NoNewline -Encoding UTF8
        Write-Host "    -> Fixed!" -ForegroundColor Green
    }
}

Write-Host "`nDone! Upload the plugin again and try activating." -ForegroundColor Cyan

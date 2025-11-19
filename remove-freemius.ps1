# Remove all Freemius checks and make everything free

$files = Get-ChildItem -Path "admin\partials\tabs" -Include *.php -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $original = $content
    
    # Remove premium checks with regex - simple approach
    $content = $content -replace 'if\s*\(\s*!\$this->asi_freemius\(\)->is__premium_only\(\)\s*&&[^{]*\)\s*\{[^}]*\}', ''
    $content = $content -replace 'if\s*\(\s*true\s*===\s*\$this->asi_freemius\(\)->is__premium_only\(\)\s*\)\s*\{', '// Premium features now available for free'
    $content = $content -replace 'if\s*\(\s*\$this->asi_freemius\(\)->can_use_premium_code\(\)\s*\)\s*\{', ''
    $content = $content -replace '\}\s*else\s*\{[^}]*ONLY\s+FOR\s+PRO[^}]*\}', ''
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "Procesado: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "`nCompletado" -ForegroundColor Cyan

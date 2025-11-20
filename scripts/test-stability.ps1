Param(
    [Parameter(Mandatory=$false)]
    [string]$ApiKey = $env:STABILITY_API_KEY,

    [Parameter(Mandatory=$false)]
    [string]$Prompt = "cat playing",

    [Parameter(Mandatory=$false)]
    [string]$Model = "sd3-large",

    [Parameter(Mandatory=$false)]
    [string]$AspectRatio = "1:1",

    [Parameter(Mandatory=$false)]
    [string]$OutputFormat = "jpeg",

    [Parameter(Mandatory=$false)]
    [string]$OutputPath
)

if (-not $ApiKey) {
    Write-Error "Provide an API key via -ApiKey or STABILITY_API_KEY env var."
    exit 1
}

if (-not $OutputPath) {
    $scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
    if (-not $scriptRoot) {
        $scriptRoot = (Get-Location).Path
    }
    $OutputPath = Join-Path $scriptRoot "stability_result.$OutputFormat"
}

$endpointMap = @{
    "sd3-large"         = "sd3"
    "sd3-large-turbo"   = "sd3-turbo"
    "sd3-medium"        = "sd3"
    "sd3.5-large"       = "sd3"
    "sd3.5-large-turbo" = "sd3"
    "core"              = "core"
    "ultra"             = "ultra"
}

if (-not $endpointMap.ContainsKey($Model)) {
    Write-Error "Unknown model '$Model'. Available: $($endpointMap.Keys -join ', ')."
    exit 1
}

$uri = "https://api.stability.ai/v2beta/stable-image/generate/$($endpointMap[$Model])"
$bodyObject = [ordered]@{
    prompt        = $Prompt
    model         = $Model
    aspect_ratio  = $AspectRatio
    output_format = $OutputFormat
}

$formData = [ordered]@{
    prompt        = $Prompt
    model         = $Model
    aspect_ratio  = $AspectRatio
    output_format = $OutputFormat
}

$supportsDimensions = $endpointMap[$Model] -in @("core","ultra")
if ($supportsDimensions) {
    $aspectMap = @{
        "21:9" = @{ width = 2560; height = 1097 }
        "16:9" = @{ width = 2560; height = 1440 }
        "3:2"  = @{ width = 2304; height = 1536 }
        "5:4"  = @{ width = 2048; height = 1638 }
        "1:1"  = @{ width = 2048; height = 2048 }
        "4:5"  = @{ width = 1638; height = 2048 }
        "2:3"  = @{ width = 1536; height = 2304 }
        "9:16" = @{ width = 1440; height = 2560 }
        "9:21" = @{ width = 1097; height = 2560 }
    }
    $dimensions = $aspectMap[$AspectRatio]
    if (-not $dimensions) {
        $dimensions = $aspectMap["16:9"]
    }
    $formData["width"] = $dimensions.width
    $formData["height"] = $dimensions.height
}

$headers = @{
    Authorization = "Bearer $ApiKey"
    Accept        = "image/*"
}

$boundary = "----ASI" + ([System.Guid]::NewGuid().ToString("N"))
$headers['Content-Type'] = "multipart/form-data; boundary=$boundary"
$builder = New-Object System.Text.StringBuilder
foreach ($entry in $formData.GetEnumerator()) {
    [void]$builder.Append("--$boundary`r`n")
    [void]$builder.AppendFormat("Content-Disposition: form-data; name=""{0}""`r`n`r`n", $entry.Key)
    [void]$builder.Append("$($entry.Value)`r`n")
}
[void]$builder.Append("--$boundary--`r`n")
$bodyBytes = [System.Text.Encoding]::UTF8.GetBytes($builder.ToString())

Write-Host "Requesting: $uri" -ForegroundColor Cyan

try {
    Invoke-WebRequest -Uri $uri -Method Post -Headers $headers -Body $bodyBytes -TimeoutSec 60 -OutFile $OutputPath -ErrorAction Stop | Out-Null
    Write-Host "Status: 200" -ForegroundColor Green
    Write-Host "Image saved to $OutputPath" -ForegroundColor Green
}
catch {
    Write-Host "Request failed" -ForegroundColor Red
    if ($_.Exception.Response) {
        $status = $_.Exception.Response.StatusCode.value__
        $desc = $_.Exception.Response.StatusDescription
        Write-Host "HTTP Status: $status $desc" -ForegroundColor Yellow
        $stream = $_.Exception.Response.GetResponseStream()
        if ($stream) {
            $reader = New-Object System.IO.StreamReader($stream)
            $body = $reader.ReadToEnd()
            if ($body) {
                Write-Host "Response body:" -ForegroundColor Yellow
                Write-Output $body
            }
        }
    } else {
        Write-Error $_
    }
    if ($_.ErrorDetails -and $_.ErrorDetails.Message) {
        Write-Host "Error details:" -ForegroundColor Yellow
        Write-Output $_.ErrorDetails.Message
    }
    exit 1
}

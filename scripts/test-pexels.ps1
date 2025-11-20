Param(
    [Parameter(Mandatory=$false)]
    [string]$ApiKey = $env:PEXELS_API_KEY,

    [Parameter(Mandatory=$false)]
    [string]$Query = "cat",

    [Parameter(Mandatory=$false)]
    [int]$PerPage = 5,

    [Parameter(Mandatory=$false)]
    [string]$Locale = "en-US"
)

if (-not $ApiKey) {
    Write-Error "Provide an API key via -ApiKey or PEXELS_API_KEY env var."
    exit 1
}

$baseUri = New-Object System.UriBuilder "https://api.pexels.com/v1/search"
$encodedQuery = [System.Uri]::EscapeDataString($Query)
$baseUri.Query = "query=$encodedQuery&per_page=$PerPage&locale=$Locale"
$uri = $baseUri.Uri.AbsoluteUri

$headers = @{ Authorization = $ApiKey }

Write-Host "Requesting: $uri" -ForegroundColor Cyan

try {
    $response = Invoke-RestMethod -Uri $uri -Headers $headers -Method Get -ErrorAction Stop
    Write-Host "Status: 200" -ForegroundColor Green
    if ($response.photos) {
        $preview = $response.photos | Select-Object -First 3 -Property id, photographer, url
        Write-Output ($preview | Format-Table -AutoSize | Out-String)
    } else {
        Write-Output ($response | ConvertTo-Json -Depth 4)
    }
}
catch {
    Write-Host "Request failed" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        $statusDesc = $_.Exception.Response.StatusDescription
        Write-Host "HTTP Status: $statusCode $statusDesc" -ForegroundColor Yellow
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $body = $reader.ReadToEnd()
        if ($body) {
            Write-Host "Response body:" -ForegroundColor Yellow
            Write-Output $body
        }
    } else {
        Write-Error $_
    }
    exit 1
}

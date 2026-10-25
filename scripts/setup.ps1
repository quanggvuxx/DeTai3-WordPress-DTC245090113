# Requires only Windows PowerShell 5.1+ (no Python required).
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$target = Join-Path $root '.env'
if (Test-Path $target) {
    Write-Host '.env already exists. Its passwords were NOT overwritten.' -ForegroundColor Yellow
    exit 0
}
function New-RandomHexPassword {
    $data = New-Object byte[] 24
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($data) } finally { $rng.Dispose() }
    return [BitConverter]::ToString($data).Replace('-', '')
}
$rootPass = New-RandomHexPassword
$appPass = New-RandomHexPassword
$metricsPass = New-RandomHexPassword
$grafanaPass = New-RandomHexPassword
@"
MYSQL_ROOT_PASSWORD=$rootPass
MYSQL_PASSWORD=$appPass
MYSQL_EXPORTER_PASSWORD=$metricsPass
GF_SECURITY_ADMIN_PASSWORD=$grafanaPass
SITE_URL=http://localhost:8080
"@ | Set-Content -Path $target -Encoding ascii
Write-Host 'Created .env with 4 random passwords (not committed to Git).' -ForegroundColor Green
Write-Host 'Keep .env private. For Grafana: username admin; find password in .env.'

# Descarga y configura PHP (TS) + Apache portables en runtime/ del proyecto.
# Uso: powershell -ExecutionPolicy Bypass -File tools\provision_runtime.ps1 [-Force]

param(
    [switch]$Force
)

$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$ManifestPath = Join-Path $PSScriptRoot 'runtime-manifest.json'
if (-not (Test-Path $ManifestPath)) {
    Write-Error "No se encontro tools/runtime-manifest.json"
}

$manifest = Get-Content $ManifestPath -Raw | ConvertFrom-Json
$RuntimeRoot = Join-Path $ProjectRoot 'runtime'
$CacheDir = Join-Path $RuntimeRoot 'cache'
$PhpDir = Join-Path $RuntimeRoot 'php'
$ApacheDir = Join-Path $RuntimeRoot 'apache'
$PhpExe = Join-Path $PhpDir 'php.exe'
$HttpdExe = Join-Path $ApacheDir 'bin\httpd.exe'
$HttpdConf = Join-Path $ApacheDir 'conf\httpd-portable.conf'
$PortFile = Join-Path $RuntimeRoot 'apache-port.txt'

function Test-PortInUse([int]$Port) {
    if (Get-Command Get-NetTCPConnection -ErrorAction SilentlyContinue) {
        $listen = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
        return ($null -ne $listen -and @($listen).Count -gt 0)
    }

    $line = netstat -ano | Select-String -Pattern ":$Port\s" | Select-Object -First 1
    return ($null -ne $line)
}

function Get-AvailableApachePort {
    param([int[]]$Candidates)
    foreach ($candidate in $Candidates) {
        if (-not (Test-PortInUse $candidate)) {
            return $candidate
        }
        Write-Host "   Puerto $candidate ocupado, probando siguiente..."
    }
    throw "Ningun puerto libre entre: $($Candidates -join ', ')"
}

$candidates = @($manifest.apachePortCandidates)
if ($candidates.Count -eq 0) {
    $candidates = @(80, 8080, 8081, 8082)
}
$Port = Get-AvailableApachePort -Candidates $candidates

function Write-Step([string]$Message) {
    Write-Host ""
    Write-Host ">> $Message"
}

function Ensure-Dir([string]$Path) {
    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
    }
}

function Resolve-LatestPhpUrl {
    param([string]$FallbackUrl)
    try {
        $index = (Invoke-WebRequest -Uri 'https://downloads.php.net/~windows/releases/' -UseBasicParsing -Headers @{ 'User-Agent' = 'proyectoWebTicket-provision/1.0' }).Content
        $found = [regex]::Matches($index, 'href="(php-8\.2\.(\d+)-Win32-vs16-x64\.zip)"')
        if ($found.Count -eq 0) { return $FallbackUrl }
        $best = $found | ForEach-Object {
            [pscustomobject]@{ File = $_.Groups[1].Value; Patch = [int]$_.Groups[2].Value }
        } | Sort-Object Patch -Descending | Select-Object -First 1
        return 'https://downloads.php.net/~windows/releases/' + $best.File
    } catch {
        return $FallbackUrl
    }
}

function Resolve-LatestApacheUrl {
    param([string]$FallbackUrl)
    try {
        $index = (Invoke-WebRequest -Uri 'https://www.apachelounge.com/download/' -UseBasicParsing -Headers @{ 'User-Agent' = 'proyectoWebTicket-provision/1.0' }).Content
        $match = [regex]::Match($index, 'href="(/download/VS18/binaries/httpd-2\.4\.[^"]+-Win64-VS18\.zip)"')
        if (-not $match.Success) { return $FallbackUrl }
        return 'https://www.apachelounge.com' + $match.Groups[1].Value
    } catch {
        return $FallbackUrl
    }
}

function Download-File([string]$Url, [string]$Destination) {
    Write-Host "   Descargando: $Url"
    $headers = @{ 'User-Agent' = 'proyectoWebTicket-provision/1.0' }
    if (Get-Command curl.exe -ErrorAction SilentlyContinue) {
        & curl.exe -fsSL -A 'proyectoWebTicket-provision/1.0' -o $Destination $Url
        if ($LASTEXITCODE -ne 0) { throw "curl fallo al descargar $Url" }
        return
    }

    try {
        Invoke-WebRequest -Uri $Url -OutFile $Destination -Headers $headers -UseBasicParsing -MaximumRedirection 5
    } catch {
        throw $_
    }
}

function Expand-Zip([string]$ZipPath, [string]$DestDir) {
    Ensure-Dir $DestDir
    if (Get-Command Expand-Archive -ErrorAction SilentlyContinue) {
        Expand-Archive -Path $ZipPath -DestinationPath $DestDir -Force
    } else {
        Add-Type -AssemblyName System.IO.Compression.FileSystem
        [System.IO.Compression.ZipFile]::ExtractToDirectory($ZipPath, $DestDir)
    }
}

function Configure-PhpIni([string]$PhpRoot) {
    $iniDev = Join-Path $PhpRoot 'php.ini-development'
    $iniProd = Join-Path $PhpRoot 'php.ini'
    if (-not (Test-Path $iniDev)) {
        throw "No se encontro php.ini-development en $PhpRoot"
    }
    Copy-Item $iniDev $iniProd -Force
    $ini = Get-Content $iniProd -Raw
    $extDir = (Join-Path $PhpRoot 'ext') -replace '\\', '/'

    $replacements = @{
        ';extension_dir = "ext"' = "extension_dir = `"$extDir`""
        '; extension_dir = "ext"' = "extension_dir = `"$extDir`""
        ';extension=curl' = 'extension=curl'
        ';extension=fileinfo' = 'extension=fileinfo'
        ';extension=mbstring' = 'extension=mbstring'
        ';extension=mysqli' = 'extension=mysqli'
        ';extension=openssl' = 'extension=openssl'
        ';extension=pdo_mysql' = 'extension=pdo_mysql'
    }

    foreach ($key in $replacements.Keys) {
        if ($ini.Contains($key)) {
            $ini = $ini.Replace($key, $replacements[$key])
        }
    }

    if ($ini -notmatch 'extension_dir\s*=') {
        $ini += "`nextension_dir = `"$extDir`"`n"
    }
    foreach ($ext in @('curl', 'fileinfo', 'mbstring', 'mysqli', 'openssl', 'pdo_mysql')) {
        if ($ini -notmatch "extension=$ext") {
            $ini += "`nextension=$ext`n"
        }
    }

    Set-Content -Path $iniProd -Value $ini -Encoding UTF8
}

function Write-ApacheConf {
    param(
        [string]$ApacheRoot,
        [string]$PhpRoot,
        [string]$AppRoot,
        [int]$ListenPort,
        [string]$AliasPath
    )

    $srv = ($ApacheRoot -replace '\\', '/')
    $php = ($PhpRoot -replace '\\', '/')
    $app = ($AppRoot -replace '\\', '/')
    $logs = ($ApacheRoot + '/logs' -replace '\\', '/')
    $phpModule = Join-Path $PhpRoot 'php8apache2_4.dll'
    if (-not (Test-Path $phpModule)) {
        throw "No se encontro php8apache2_4.dll en $PhpRoot (use PHP Thread Safe x64, no NTS)."
    }
    $phpModule = ($phpModule -replace '\\', '/')

    $conf = @"
# Generado por tools/provision_runtime.ps1 — no editar a mano salvo puerto/ruta
Define SRVROOT "$srv"
ServerRoot "`${SRVROOT}"
Listen $ListenPort

LoadModule access_compat_module modules/mod_access_compat.so
LoadModule alias_module modules/mod_alias.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule dir_module modules/mod_dir.so
LoadModule env_module modules/mod_env.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule mime_module modules/mod_mime.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule setenvif_module modules/mod_setenvif.so

LoadModule php_module "$phpModule"
PHPIniDir "$php"
AddHandler application/x-httpd-php .php

ServerName localhost
DocumentRoot "`${SRVROOT}/htdocs"

ErrorLog "$logs/error.log"
CustomLog "$logs/access.log" common

<Directory "`${SRVROOT}/htdocs">
    AllowOverride None
    Require all granted
</Directory>

Alias $AliasPath "$app"
<Directory "$app">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

DirectoryIndex index.php index.html
"@

    $confPath = Join-Path $ApacheRoot 'conf\httpd-portable.conf'
    Set-Content -Path $confPath -Value $conf -Encoding ASCII
}

Ensure-Dir $RuntimeRoot
Ensure-Dir $CacheDir

$needPhp = $Force -or -not (Test-Path $PhpExe)
$needApache = $Force -or -not (Test-Path $HttpdExe)

if (-not $needPhp -and -not $needApache) {
    Write-Host "Runtime local listo (runtime\php y runtime\apache)."
} else {
    if ($needPhp) {
        Write-Step "Instalando PHP $($manifest.phpVersion) (Thread Safe, x64)"
        $phpUrl = Resolve-LatestPhpUrl -FallbackUrl $manifest.phpUrl
        $phpZipName = [IO.Path]::GetFileName(($phpUrl -split '\?')[0])
        $phpZipPath = Join-Path $CacheDir $phpZipName
        if (-not (Test-Path $phpZipPath)) {
            Download-File $phpUrl $phpZipPath
        }
        $phpExtract = Join-Path $CacheDir 'php-extract'
        if (Test-Path $phpExtract) { Remove-Item $phpExtract -Recurse -Force }
        Expand-Zip $phpZipPath $phpExtract
        if (Test-Path $PhpDir) { Remove-Item $PhpDir -Recurse -Force }
        Ensure-Dir $PhpDir
        Get-ChildItem -Path $phpExtract -Force | Move-Item -Destination $PhpDir
        Remove-Item $phpExtract -Recurse -Force -ErrorAction SilentlyContinue
        Configure-PhpIni $PhpDir
        Write-Host "   PHP en: $PhpDir"
    }

    if ($needApache) {
        Write-Step 'Instalando Apache 2.4 (VS18, Win64; compatible con modulo PHP VS16)'
        $apacheUrl = Resolve-LatestApacheUrl -FallbackUrl $manifest.apacheUrl
        $apacheZipName = [IO.Path]::GetFileName(($apacheUrl -split '\?')[0])
        $apacheZipPath = Join-Path $CacheDir $apacheZipName
        if (-not (Test-Path $apacheZipPath)) {
            Download-File $apacheUrl $apacheZipPath
        }
        $apacheExtract = Join-Path $CacheDir 'apache-extract'
        if (Test-Path $apacheExtract) { Remove-Item $apacheExtract -Recurse -Force }
        Expand-Zip $apacheZipPath $apacheExtract
        $apacheInner = Get-ChildItem $apacheExtract -Directory | Select-Object -First 1
        if (-not $apacheInner) { throw 'El ZIP de Apache no tiene una carpeta raiz esperada.' }
        if (Test-Path $ApacheDir) { Remove-Item $ApacheDir -Recurse -Force }
        Move-Item $apacheInner.FullName $ApacheDir
        Remove-Item $apacheExtract -Recurse -Force -ErrorAction SilentlyContinue
        Ensure-Dir (Join-Path $ApacheDir 'logs')
        Write-Host "   Apache en: $ApacheDir"
    }
}

Write-Step 'Generando configuracion de Apache para este proyecto'
Write-ApacheConf -ApacheRoot $ApacheDir -PhpRoot $PhpDir -AppRoot $ProjectRoot -ListenPort $Port -AliasPath $manifest.urlAlias
Set-Content -Path $PortFile -Value $Port -Encoding ASCII

Write-Step 'Composer phar (opcional, para composer install sin instalador global)'
$composerPhar = Join-Path $ProjectRoot 'tools\composer.phar'
if ($Force -or -not (Test-Path $composerPhar)) {
    Download-File $manifest.composerUrl $composerPhar
}

Write-Step 'Comprobando PHP local'
& $PhpExe -r "if (PHP_VERSION_ID < 80200) { fwrite(STDERR, 'Se requiere PHP 8.2+. Actual: '.PHP_VERSION.PHP_EOL); exit(1); } foreach (['pdo_mysql','mbstring','fileinfo'] as `$e) { if (!extension_loaded(`$e)) { fwrite(STDERR, 'Falta extension '. `$e . PHP_EOL); exit(1); } } echo 'PHP '.PHP_VERSION.' OK'.PHP_EOL;"
if ($LASTEXITCODE -ne 0) {
    throw 'PHP local no cumple requisitos.'
}

Write-Step 'Comprobando Apache (modulo PHP)'
& $HttpdExe -t -f $HttpdConf
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Apache no paso la prueba de configuracion." -ForegroundColor Yellow
    Write-Host "Instale Microsoft Visual C++ Redistributable 2015-2022 (x64):" -ForegroundColor Yellow
    Write-Host "https://aka.ms/vs/17/release/vc_redist.x64.exe" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "Runtime preparado. Puerto HTTP: $Port"
Write-Host "URL: http://127.0.0.1:$Port$($manifest.urlAlias)/login"
exit 0

# Copia el proyecto a una carpeta independiente y ejecuta montar.bat (instancia desde cero).
# Uso:
#   powershell -ExecutionPolicy Bypass -File tools\instalar_instancia.ps1
#   powershell -ExecutionPolicy Bypass -File tools\instalar_instancia.ps1 -TargetDir "D:\IntranetTambo"

param(
    [string]$TargetDir = (Join-Path $env:USERPROFILE 'IntranetTambo-instancia')
)

$ErrorActionPreference = 'Stop'
$SourceRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$TargetRoot = [IO.Path]::GetFullPath($TargetDir)

Write-Host ""
Write-Host "=== Instalador de instancia independiente ==="
Write-Host "Origen:  $SourceRoot"
Write-Host "Destino: $TargetRoot"
Write-Host ""

if ($SourceRoot.TrimEnd('\') -eq $TargetRoot.TrimEnd('\')) {
    Write-Error "El destino no puede ser la misma carpeta del repositorio de desarrollo."
}

if (Test-Path $TargetRoot) {
    Write-Host "La carpeta destino ya existe. Se actualizara el codigo (sin borrar runtime completo si ya estaba descargado)."
} else {
    New-Item -ItemType Directory -Path $TargetRoot -Force | Out-Null
}

$robocopy = Get-Command robocopy.exe -ErrorAction SilentlyContinue
if (-not $robocopy) {
    Write-Error "robocopy.exe no esta disponible."
}

$excludeDirs = @('runtime', 'vendor', '.git', '.phpunit.cache', 'node_modules')
$excludeArgs = $excludeDirs | ForEach-Object { '/XD', $_ }

Write-Host "Copiando archivos del proyecto..."
& robocopy.exe $SourceRoot $TargetRoot /E /NFL /NDL /NJH /NJS /NC /NS /NP @excludeArgs /XF *.log | Out-Null
if ($LASTEXITCODE -ge 8) {
    Write-Error "robocopy fallo con codigo $LASTEXITCODE"
}

function Ensure-Dir([string]$Path) {
    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
    }
}

Ensure-Dir (Join-Path $TargetRoot 'runtime')
Ensure-Dir (Join-Path $TargetRoot 'public\uploads')

Set-Content -Path (Join-Path $TargetRoot 'runtime\standalone.flag') -Value (Get-Date -Format o) -Encoding ASCII

Write-Host ""
Write-Host "Ejecutando montar.bat en la instancia..."
$montar = Join-Path $TargetRoot 'montar.bat'
if (-not (Test-Path $montar)) {
    Write-Error "No se encontro montar.bat en el destino."
}

$p = Start-Process -FilePath 'cmd.exe' -ArgumentList '/c', 'set MONTAR_AUTO=1&& call montar.bat' -WorkingDirectory $TargetRoot -Wait -PassThru -NoNewWindow
if ($p.ExitCode -ne 0) {
    exit $p.ExitCode
}

$portFile = Join-Path $TargetRoot 'runtime\apache-port.txt'
$port = '8080'
if (Test-Path $portFile) {
    $port = (Get-Content $portFile -Raw).Trim()
}

Write-Host ""
Write-Host "Instancia lista en: $TargetRoot"
Write-Host "URL: http://127.0.0.1:$port/proyectoWebTicket/login"
exit 0

@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"
title Instalar instancia independiente

echo.
echo Copia el proyecto a una carpeta aparte e instala PHP/Apache desde cero.
echo BD remota: la de config\config.php del repositorio.
echo.

set "TARGET=%USERPROFILE%\IntranetTambo-instancia"
if not "%~1"=="" set "TARGET=%~1"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\instalar_instancia.ps1" -TargetDir "%TARGET%"
exit /b %ERRORLEVEL%

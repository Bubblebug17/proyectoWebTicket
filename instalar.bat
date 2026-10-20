@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"
title Instalar Intranet Tambo

echo.
echo === Instalador (ejecutar desde la carpeta clonada del repo) ===
echo.
echo Pasos que ejecuta montar.bat:
echo   1. Descarga PHP 8.2 y Apache en runtime\
echo   2. Dependencias, uploads y base remota (config\config.php)
echo   3. Servidor HTTP en el primer puerto libre: 80, 8080, 8081 o 8082
echo.

if not exist "montar.bat" (
    echo Error: montar.bat no encontrado. Ejecute este archivo desde la raiz del proyecto clonado.
    pause
    exit /b 1
)

if not exist "runtime" mkdir "runtime"
echo Instalacion standalone (Apache portable, sin depender de XAMPP).> "runtime\standalone.flag"

call "%~dp0montar.bat"
exit /b %ERRORLEVEL%

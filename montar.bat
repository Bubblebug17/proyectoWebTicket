@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"
title Intranet Tambo

echo.
echo === Intranet Tambo ===
echo Descarga PHP y Apache locales si faltan, prepara la app y abre el login.
echo.

echo [1/5] Preparando runtime local (PHP + Apache)...
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\provision_runtime.ps1"
if errorlevel 1 goto fallo

set "PHP_EXE=%~dp0runtime\php\php.exe"
set "HTTPD=%~dp0runtime\apache\bin\httpd.exe"
set "HTTPD_CONF=%~dp0runtime\apache\conf\httpd-portable.conf"
set "PORT=8080"
if exist "%~dp0runtime\apache-port.txt" set /p PORT=<"%~dp0runtime\apache-port.txt"

if not exist "%PHP_EXE%" (
    echo No se encontro runtime\php\php.exe tras la provision.
    goto fallo
)

echo.
echo PHP local: %PHP_EXE%
"%PHP_EXE%" -r "if (PHP_VERSION_ID < 80200) { fwrite(STDERR, 'Se requiere PHP 8.2 o superior. Actual: '.PHP_VERSION.PHP_EOL); exit(1); } foreach (['pdo_mysql','mbstring','fileinfo'] as $ext) { if (!extension_loaded($ext)) { fwrite(STDERR, 'Falta la extension '. $ext . PHP_EOL); exit(1); } }"
if errorlevel 1 goto fallo

echo.
echo [2/5] Carpetas y dependencias...
if not exist "public\uploads" (
    mkdir "public\uploads"
    echo Carpeta creada: public\uploads
) else (
    echo Carpeta lista: public\uploads
)

if exist "tools\composer.phar" (
    echo Instalando dependencias con Composer phar...
    "%PHP_EXE%" "%~dp0tools\composer.phar" install --no-interaction --working-dir="%~dp0"
    if errorlevel 1 goto fallo
) else if exist "vendor\autoload.php" (
    echo Se reutiliza vendor\autoload.php
) else (
    echo Generando autoload minimo...
    "%PHP_EXE%" "%~dp0tools\escribir_autoload.php"
    if errorlevel 1 goto fallo
)

echo.
echo [3/5] Base de datos remota (config\config.php)...
"%PHP_EXE%" "%~dp0tools\montar.php"
if errorlevel 1 (
    if exist "%~dp0runtime\standalone.flag" (
        echo [AVISO] No se pudo conectar o preparar la BD remota. Se continua con el servidor web.
        goto web_setup
    )
    goto fallo
)

:web_setup
echo.
echo [4/5] Servidor web local...
call "%~dp0tools\detectar_url_web.bat"
if not errorlevel 1 goto web_ok

echo No hay servidor respondiendo aun. Intentando Apache portable (puerto %PORT%)...
call "%~dp0tools\iniciar_apache.bat"
if errorlevel 1 goto fallo
timeout /t 2 /nobreak >nul
call "%~dp0tools\detectar_url_web.bat"
if errorlevel 1 goto web_fallo

:web_ok
if "%WEB_MODE%"=="xampp" (
    echo Usando XAMPP / Apache en puerto 80 ^(misma carpeta del proyecto^).
) else (
    echo Usando Apache portable en puerto %PORT% ^(runtime\apache^).
)
set "APP_URL=%WEB_URL%"
goto abrir

:web_fallo
echo.
echo Ningun servidor respondio en:
echo   http://localhost/proyectoWebTicket/login  ^(XAMPP, puerto 80^)
echo   http://127.0.0.1:%PORT%/proyectoWebTicket/login  ^(portable^)
echo.
echo Si usa solo el runtime portable, instale Visual C++ Redistributable x64:
echo https://aka.ms/vs/17/release/vc_redist.x64.exe
echo.
echo Inicio manual del Apache portable:
echo "%HTTPD%" -f "%HTTPD_CONF%"
goto fin

:abrir
echo.
echo [5/5] Listo. Abriendo %APP_URL%
start "" "%APP_URL%"
goto fin

:fallo
echo.
echo El montaje se detuvo. Corrija el mensaje anterior y vuelva a ejecutar montar.bat.
echo Para forzar nueva descarga de PHP/Apache:
echo   powershell -ExecutionPolicy Bypass -File tools\provision_runtime.ps1 -Force
echo.
if not "%MONTAR_AUTO%"=="1" pause
exit /b 1

:fin
echo.
if not "%MONTAR_AUTO%"=="1" pause
exit /b 0

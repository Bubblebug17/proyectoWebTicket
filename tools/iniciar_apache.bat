@echo off
setlocal EnableExtensions
cd /d "%~dp0.."

set "HTTPD=%CD%\runtime\apache\bin\httpd.exe"
set "CONF=%CD%\runtime\apache\conf\httpd-portable.conf"
set "PORT_FILE=%CD%\runtime\apache-port.txt"

if not exist "%HTTPD%" (
    echo Falta runtime\apache. Ejecute montar.bat primero.
    exit /b 1
)

set "PORT=8080"
if exist "%PORT_FILE%" set /p PORT=<"%PORT_FILE%"

if exist "%CD%\runtime\apache\logs\httpd.pid" (
    "%HTTPD%" -f "%CONF%" -k stop >nul 2>&1
    timeout /t 1 /nobreak >nul
)

curl.exe -s -o NUL -m 2 -w "%%{http_code}" "http://127.0.0.1:%PORT%/proyectoWebTicket/login" > "%TEMP%\tambo-http.txt" 2>nul
set /p HTTP_CODE=<"%TEMP%\tambo-http.txt"
if "%HTTP_CODE%"=="200" exit /b 0
if "%HTTP_CODE%"=="302" exit /b 0

start "Apache portable" /MIN "%HTTPD%" -f "%CONF%"
timeout /t 2 /nobreak >nul
exit /b 0

@echo off
cd /d "%~dp0.."

set "PORT=8080"
if exist "runtime\apache-port.txt" set /p PORT=<"runtime\apache-port.txt"

set "URL_PORTABLE=http://127.0.0.1:%PORT%/proyectoWebTicket/login"
set "URL_XAMPP=http://localhost/proyectoWebTicket/login"

if exist "runtime\standalone.flag" goto solo_portable

curl.exe -s -o NUL -m 3 -w "%%{http_code}" "%URL_XAMPP%" > "%TEMP%\tambo-detect-xampp.txt" 2>nul
set /p CODE_XAMPP=<"%TEMP%\tambo-detect-xampp.txt"
if "%CODE_XAMPP%"=="200" goto usar_xampp
if "%CODE_XAMPP%"=="302" goto usar_xampp

:solo_portable
curl.exe -s -o NUL -m 3 -w "%%{http_code}" "%URL_PORTABLE%" > "%TEMP%\tambo-detect-portable.txt" 2>nul
set /p CODE_PORTABLE=<"%TEMP%\tambo-detect-portable.txt"
if "%CODE_PORTABLE%"=="200" goto usar_portable
if "%CODE_PORTABLE%"=="302" goto usar_portable

set "WEB_URL="
set "WEB_MODE=none"
exit /b 1

:usar_xampp
set "WEB_URL=%URL_XAMPP%"
set "WEB_MODE=xampp"
exit /b 0

:usar_portable
set "WEB_URL=%URL_PORTABLE%"
set "WEB_MODE=portable"
exit /b 0

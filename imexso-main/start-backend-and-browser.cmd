@echo off
cd /d "%~dp0imexso-main"
if not exist "artisan" (
  echo Nije nadjen Laravel. Ocekujem: %~dp0imexso-main\artisan
  pause
  exit /b 1
)
echo Otvara se novi prozor sa "php artisan serve" - ostavi ga OTVORENIM.
echo Zatim se otvara preglednik na http://127.0.0.1:8000
rem Slusaj samo na IPv4 127.0.0.1 — "localhost" na Windowsu cesto ide na ::1 pa veza padne ako nema IPv6 listenera
start "Laravel-8000" cmd /k "cd /d %~dp0imexso-main && php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:8000/"

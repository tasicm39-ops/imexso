@echo off
cd /d "%~dp0imexso-main"
if not exist "artisan" (
  echo Nije Laravel folder. Ocekujem: %~dp0imexso-main\artisan
  pause
  exit /b 1
)
echo Otvara se NOVI prozor sa serverom - ostavi ga OTVORENIM.
echo Ovaj prozor mozes koristiti za curl / ostalo.
start "Laravel-127.0.0.1-8000" cmd /k "cd /d %~dp0imexso-main && php artisan serve --host=127.0.0.1 --port=8000"

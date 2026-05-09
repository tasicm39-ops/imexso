@echo off
cd /d "%~dp0imexso-main"
if not exist "artisan" (
  echo Expected: %~dp0imexso-main\artisan ^(Laravel root^)
  pause
  exit /b 1
)
echo.
echo === Laravel samo (preporuceno) ===
echo Otvara se NOVI prozor. OSTAVI GA OTVORENIM - dok je otvoren, http://127.0.0.1:8000 radi.
echo Admin (Inertia): http://127.0.0.1:8000/admin  (nalog mora imati is_admin=1 i verified)
echo Za Vite+red istovremeno pokreni: dev-backend-full.cmd
echo.
start "Laravel-8000" cmd /k "cd /d %~dp0imexso-main && php artisan serve --host=127.0.0.1 --port=8000"

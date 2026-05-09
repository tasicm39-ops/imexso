@echo off
echo === Sta slusa na portu 8000? ===
netstat -ano | findstr ":8000"
if errorlevel 1 (
  echo NIC - nema LISTENING na 8000. Pokreni prvo: serve-in-new-window.cmd ili php artisan serve
  pause
  exit /b 1
)
echo.
echo === HTTP odgovor (treba 200 ili 302) ===
curl.exe -s -I --max-time 5 http://127.0.0.1:8000/
echo.
pause

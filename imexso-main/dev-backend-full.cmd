@echo off
cd /d "%~dp0imexso-main"
if not exist "artisan" (
  echo Expected: %~dp0imexso-main\artisan
  pause
  exit /b 1
)
echo Pun stack: Laravel + Vite + red + logovi. Treba npm install u ovom folderu.
echo Ako jedan proces padne, concurrently gasi sve - za samo API koristi dev-backend.cmd
call composer run dev

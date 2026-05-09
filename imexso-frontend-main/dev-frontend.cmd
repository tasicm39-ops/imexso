@echo off
cd /d "%~dp0imexso-frontend-main"
if not exist "package.json" (
  echo Expected: %~dp0imexso-frontend-main\package.json
  pause
  exit /b 1
)
call npm run dev

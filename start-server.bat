@echo off
setlocal
cd /d "%~dp0"

if exist "C:\xampp\php\php.exe" (
    set "PHP=C:\xampp\php\php.exe"
) else (
    set "PHP=php"
)

"%PHP%" -r "exit(extension_loaded('pdo_mysql') ? 0 : 1);" >nul 2>&1
if errorlevel 1 (
    echo PHP with the pdo_mysql extension was not found. Check your PHP installation.
    pause
    exit /b 1
)

"%PHP%" -r "$s = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 1); if ($s === false) { exit(1); } fclose($s);" >nul 2>&1
if errorlevel 1 (
    echo MySQL is not reachable on 127.0.0.1:3306.
    echo Start MySQL in the XAMPP Control Panel, then run this launcher again.
    pause
    exit /b 1
)

"%PHP%" artisan serve --host=127.0.0.1 --port=8000
pause

@echo off
title YonoLootZone Local Server
echo ===================================================
echo   YonoLootZone - Starting Local Development Server
echo ===================================================

:: Detect PHP
set PHP_BIN=php
where php >nul 2>&1
if %ERRORLEVEL% equ 0 goto found_php

if exist "M:\Xampp\php\php.exe" (
    set PHP_BIN=M:\Xampp\php\php.exe
    goto found_php
)

if exist "C:\xampp\php\php.exe" (
    set PHP_BIN=C:\xampp\php\php.exe
    goto found_php
)

echo [ERROR] PHP executable not found in PATH or standard XAMPP paths.
echo Please start Apache from XAMPP Control Panel or install PHP.
pause
exit /b 1

:found_php
echo Found PHP: %PHP_BIN%
echo.
echo Server URL:   http://localhost:8000
echo Admin Panel:  http://localhost:8000/admin/
echo CMS:          http://localhost:8000/cms/
echo.
echo Press Ctrl+C to stop the server.
echo ===================================================

:: Open default browser
start http://localhost:8000

:: Run PHP built-in server
"%PHP_BIN%" -S localhost:8000


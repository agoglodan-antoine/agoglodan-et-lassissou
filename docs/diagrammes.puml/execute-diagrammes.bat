@echo off
setlocal
set "SCRIPT_DIR=%~dp0"
if not exist "%SCRIPT_DIR%output" mkdir "%SCRIPT_DIR%output"

REM Génère des SVG vectoriels et des PNG haute définition avec PlantUML.
plantuml -charset UTF-8 -tsvg -o "%SCRIPT_DIR%output" "%SCRIPT_DIR%*.puml"
plantuml -charset UTF-8 -tpng -o "%SCRIPT_DIR%output" "%SCRIPT_DIR%*.puml"

echo Generation terminee : %SCRIPT_DIR%output
endlocal
pause

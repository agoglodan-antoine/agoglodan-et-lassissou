@echo off
plantuml -tsvg -r -o "%~dp0\output" "%~dp0\*.puml"
echo Génération terminée.
pause
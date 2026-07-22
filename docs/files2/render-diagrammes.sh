#!/usr/bin/env bash
# Regénère les diagrammes SVG (vectoriels) et PNG (haute définition).
set -euo pipefail

cd "$(dirname "$0")"
mkdir -p output

plantuml -charset UTF-8 -tsvg -o output ./*.puml
plantuml -charset UTF-8 -tpng -o output ./*.puml

echo "Diagrammes générés dans $(pwd)/output"

# Diagrammes ElevConnect

Les fichiers PlantUML de ce dossier ont été simplifiés pour privilégier la
lecture dans le mémoire : ils montrent les flux et les données essentiels sans
détailler chaque champ ou chaque appel technique.

## Images livrées

Le dossier [`output/`](output/) contient pour chaque diagramme :

- **SVG** — format vectoriel à privilégier : parfaitement net à l'impression et
  lors d'un agrandissement dans Word ou LibreOffice ;
- **PNG** — export haute définition, pratique si l'éditeur de texte ne gère pas
  correctement les SVG.

## Régénérer les images

1. Installer [PlantUML](https://plantuml.com/fr/download) et Graphviz.
2. Sous Windows, lancer `execute-diagrammes.bat`.
3. Sous macOS/Linux, exécuter :

   ```bash
   ./render-diagrammes.sh
   ```

Les deux scripts exportent les fichiers `.svg` et `.png` dans `output/` avec
l'encodage UTF-8. Chaque fichier `.puml` est autonome : il peut donc être
prévisualisé ou exporté seul, sans fichier de style externe à inclure.

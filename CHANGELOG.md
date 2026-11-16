# Änderungen

## 1.0.0 – Final · 16.11.2026

Erster finaler Release unter dem Namen **Mitteilungszentrale**. Er fasst die zuvor als Visu-Messages entwickelten Funktionen zusammen.

### Meldungen und Regeln

- Konfigurierbare Variablenbedingungen mit eigenen Texten und Platzhaltern.
- Übergeordnete Instanz im Standardtext.
- Gemeinsame, dauerhaft gespeicherte Liste mit Anpinnen, Lösen, Einzel- und Sammellöschen.
- Priorität 1/2/3 mit rotem, gelbem oder normalem Symbol; Sortierung nach Priorität oder Datum.
- Begrenzung normaler Meldungen, angepinnte Einträge bleiben geschützt.

### Visualisierung

- HTML-Darstellung für Kachelvisu, WebFront und IPSView.
- Gemeinsame Vorgaben und getrennte Darstellungsprofile in der Reihenfolge Kachelvisu, WebFront, IPSView.
- Standardhöhe 60 px, anpassbare Schriftgrößen und verbesserte Miniansicht.
- Variableigene Symbole mit Brief als Standard, lokale Einbettung moderner Symbole.
- Konfigurierbare Überschrift inklusive Ausblenden, bündiger Kopfbereich und Fußzeile „Letzte Aktualisierung“.
- Farben, Rahmen, Rundung, Schatten und eigenes CSS je Visu.
- Transparenzauswahl vorerst entfernt; feste Farben für verlässliche native IPSView-Darstellung.

### Optionale Ausgaben

- Durchsagen mit Zielinstanz, zusätzlichen Wiederholungen, Intervall und eigener Lautstärke.
- Direkter Push an Kachelvisu/WebFront mit Titel, Sprungziel und unabhängigen Wiederholungen.
- Sichtbare Lautsprecher-, Wiederholungs- und Push-Symbole an entsprechend erstellten Meldungen.
- Fehlende optionale Funktionen verhindern den normalen Modulbetrieb nicht; Hinweise und ausgegraute Felder direkt in der Melderegel.

### Konfiguration, Daten und Dokumentation

- Größere Melderegeltabelle und kompaktere, gegliederte Einstellungen.
- Sichtbare Modulvariablen auf Wunsch, JSON-Ausgabe und eigene Zeitstempelvariable.
- Zuschaltbare Debugausgaben.
- Ausführliche Dokumentation für Installation, Bedienung, Update und Fehlerbehebung.
- Sichtbare Umbenennung zu Mitteilungszentrale mit Erhalt der technischen Modulidentität und bestehender Daten.

### Updatehinweis

Bestehende Bibliothek aktualisieren und Instanz weiterverwenden. Keine zweite Kopie installieren. Speichern der Konfiguration und Neustarts verwerfen wartende Audio- und Push-Wiederholungen. Details: [Update-Anleitung](docs/UPGRADE.md).

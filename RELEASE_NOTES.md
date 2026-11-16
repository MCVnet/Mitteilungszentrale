# Mitteilungszentrale 1.0.0 – Final

Die bisherige **Visu-Messages** heißt jetzt **Mitteilungszentrale**. Dieser erste finale Release bündelt die gemeinsame Meldungsanzeige für **IP-Symcon ab 9.0** in Kachelvisu, WebFront und IPSView.

## Enthalten

- Frei konfigurierbare Melderegeln und Nachrichtentexte mit Platzhaltern.
- Anpinnen, Lösen und Löschen in einer gemeinsamen dauerhaften Liste.
- Prioritäten 1/2/3, farbige Variablensymbole und Sortierung nach Priorität oder Datum.
- Getrennte Darstellungsprofile mit Größen, Schrift, Farben, Überschrift, Rahmen, Schatten und CSS.
- Kompakte 60-px-Karten und responsive Miniansicht mit aufklappbarem Text.
- Optionale Durchsagen mit Lautstärke und Wiederholungen.
- Optionaler direkter Push an Kachelvisu/WebFront mit eigenen Wiederholungen.
- Modulvariablen, JSON-Ausgabe, letzte Aktualisierung und Debugoption.
- Ausführliche deutsche Installations-, Bedienungs- und Updatedokumentation.

## Installation und Update

Repository für die Symcon-Modulverwaltung:

```text
https://github.com/MCVnet/Mitteilungszentrale
```

**Bestehende Installationen aktualisieren und ihre Instanz weiterverwenden.** Modul-IDs, Einstellungen, Meldungen und technische Funktionsnamen bleiben erhalten. Keine zweite Bibliothekskopie installieren.

- [Installation](https://github.com/MCVnet/Mitteilungszentrale/blob/main/docs/INSTALLATION.md)
- [Handbuch](https://github.com/MCVnet/Mitteilungszentrale/blob/main/docs/HANDBUCH.md)
- [Update von Visu-Messages](https://github.com/MCVnet/Mitteilungszentrale/blob/main/docs/UPGRADE.md)
- [Fehlerbehebung](https://github.com/MCVnet/Mitteilungszentrale/blob/main/docs/FEHLERBEHEBUNG.md)

Durchsage und Push sind optional. Das separate Benachrichtigungsmodul ist für direkten Push nicht erforderlich. Transparenz wird in diesem Release nicht angeboten; IPSView verwendet feste Hintergrundfarben. Speichern der Konfiguration oder Neustarts verwerfen ausstehende Wiederholungen, nicht die gespeicherten Meldungen.

Die automatisierten Prüfungen umfassen PHP-Modulabläufe mit simulierter Symcon-Umgebung und die HTML-Oberfläche in Chrome. Geräte- und Push-Zustellung sind abhängig von der jeweiligen Zielkonfiguration.

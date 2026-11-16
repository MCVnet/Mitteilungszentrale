# Mitteilungszentrale

**Zentrale Meldungen für IP-Symcon 9.0 und neuer – in Kachelvisu, WebFront und IPSView.**

Die Mitteilungszentrale überwacht ausgewählte Variablen und erstellt bei konfigurierbaren Bedingungen verständliche Meldungen. Alle Visualisierungen greifen auf dieselbe Meldungsliste zu: Eine Meldung lässt sich anpinnen, wieder lösen oder löschen. Wichtige Einträge bleiben sichtbar, während normale Meldungen gemeinsam entfernt werden können.

**Version 1.0.0 · Final · ehemals Visu-Messages**

## Was das Modul bietet

- **Konfigurierbare Melderegeln:** Variable, Bedingung, Vergleichswert und eigener Nachrichtentext mit Platzhaltern.
- **Prioritäten:** Prio 1 mit rotem Symbol, Prio 2 mit gelbem Symbol und Prio 3 mit der eingestellten Standardfarbe. Angepinnte Meldungen stehen immer oben.
- **Sortierung in der Visu:** Wahl zwischen Priorität und Datum; Standard ist Priorität.
- **Gemeinsame Meldungsliste:** Anpinnen und Löschen wirken in allen verbundenen Ansichten derselben Instanz.
- **Symbole:** Übernahme des Variablensymbols; ohne hinterlegtes Symbol erscheint ein Briefsymbol.
- **Darstellung je Visualisierung:** Eigene Größen, Schriftgrößen, Überschriften, Farben, Rahmen, Rundungen und Schatten für Kachelvisu, WebFront und IPSView. Zusätzliche Anpassungen über CSS.
- **Kompakte Meldungskarten:** Standardhöhe 60 px, anpassbare Breite und responsive Miniansicht. Langer Text lässt sich aufklappen.
- **Optionale Durchsagen:** Wiedergabe über eine vorhandene Durchsage-Instanz, mit eigener Lautstärke und zeitlich gesteuerten Wiederholungen.
- **Optionale Push-Nachrichten:** Direkter Versand an eine gewählte Kachelvisu oder einen WebFront-Konfigurator, ebenfalls mit eigenen Wiederholungen.
- **Modulvariablen und Daten:** Meldungszähler, Meldungsdaten als JSON, WebView-Adresse und „Letzte Aktualisierung“ können im Objektbaum sichtbar gemacht werden.
- **Debug:** Bei Bedarf zuschaltbare Diagnoseausgaben.

Die Grundfunktion benötigt weder das Durchsage- noch das Benachrichtigungsmodul. Fehlt die Durchsage-Erweiterung oder eine passende Instanz, steht der Hinweis direkt in der Melderegel; die entsprechenden Einstellungen sind deaktiviert. Push wird direkt über die unterstützte Symcon-Visualisierung versendet und benötigt keine Benachrichtigungskette.

## Schnellstart

1. In IP-Symcon die **Modulverwaltung / Module Control** öffnen.
2. Dieses Repository hinzufügen:

   ```text
   https://github.com/MCVnet/Mitteilungszentrale
   ```

3. Eine Instanz **Mitteilungszentrale** hinzufügen.
4. Unter **Melderegeln** eine Variable auswählen, Bedingung und Nachricht eintragen und die Änderungen übernehmen.
5. Die Instanz als HTML-Kachel einbinden oder die erzeugte HTML-Box im WebFront verwenden. In IPSView bevorzugt **TileHtml** mit der Modulinstanz verknüpfen.
6. Mit einer Testmeldung die Darstellung prüfen. Zum Prüfen einer Regel muss ihre Bedingung durch eine spätere Variablenänderung neu erfüllt werden.

Das Modul ist über die Repository-URL installierbar. Eine Veröffentlichung im Symcon Module Store ist damit nicht verbunden.

## Dokumentation

| Thema | Inhalt |
| --- | --- |
| [Installation](docs/INSTALLATION.md) | Voraussetzungen, Installation und Einbindung in alle drei Visualisierungen |
| [Handbuch](docs/HANDBUCH.md) | Melderegeln, Prioritäten, Darstellung, CSS, Durchsagen, Push und Daten |
| [Update von Visu-Messages](docs/UPGRADE.md) | Namenswechsel, Datenerhalt und vorhandene Installationen |
| [Fehlerbehebung](docs/FEHLERBEHEBUNG.md) | Häufige Ursachen, Diagnose und bekannte Grenzen |
| [Technik und Prüfung](docs/ENTWICKLUNG.md) | Dateiaufbau, technische Kompatibilität und Testumfang |
| [Änderungen](CHANGELOG.md) | Inhalt des finalen Releases |

## Ein Beispiel

Eine Fenster-Variable wird von `false` auf `true` geändert. Eine Regel mit „gleich“, Vergleichswert `true` und dem Text

```text
{parent} · {name}: {value}
```

erzeugt beispielsweise:

```text
EG | Büro links | Fenster-offen · Value: true
```

Alternativ kann der Nachrichtentext frei formuliert werden, etwa „Bitte das Fenster im Büro schließen.“ Bei Prio 1 erscheint das Variablensymbol rot. Mit aktivierter Durchsage und Push-Ausgabe wird derselbe aufgelöste Meldungstext zusätzlich ausgegeben.

## Vor dem Einsatz beachten

- Meldungen entstehen beim **Wechsel von einer nicht erfüllten zu einer erfüllten Bedingung**. Bereits beim Speichern erfüllte Bedingungen erzeugen keine nachträglichen Meldungen.
- Standardmäßig werden höchstens **200 nicht angepinnte Meldungen** gespeichert. Angepinnte Meldungen sind davon ausgenommen.
- Wiederholungen enden bei Löschen der Meldung, beim Speichern der Instanzkonfiguration und beim Neustart. Eine wieder normale Variable beendet sie nicht automatisch.
- Transparente Ansichten werden in diesem Release nicht angeboten. Für IPSView stehen feste Hintergrundfarben zur Verfügung, da die native Einbettung sonst eine weiße Fläche zeigen kann.
- Die WebView-Adresse enthält einen Zugriffsschlüssel. Wer die Adresse kennt, kann die Meldungsliste lesen und bedienen. Sie gehört nicht in öffentliche Screenshots, Logs oder Repository-Dateien.

## Bestehende Installation

**Die vorhandene Bibliothek aktualisieren und die bestehende Instanz weiterverwenden.** Modul-IDs, gespeicherte Meldungen, Einstellungen und technische Funktionsnamen bleiben erhalten. Nicht parallel eine zweite Kopie derselben Bibliothek installieren. Details stehen in der [Update-Anleitung](docs/UPGRADE.md).

Autor: Matthias Vogel. Fehlerberichte bitte über die [GitHub Issues](https://github.com/MCVnet/Mitteilungszentrale/issues), mit Versionsnummer und bereinigten Diagnoseangaben.

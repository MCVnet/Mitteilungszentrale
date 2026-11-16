# Technik und Prüfung

[Übersicht](../README.md)

## Dateiaufbau

```text
Mitteilungszentrale/
├── library.json
├── README.md
├── CHANGELOG.md
├── RELEASE_NOTES.md
├── docs/
├── tests/
│   ├── rules.php
│   └── announcements.php
└── VisuMessages/
    ├── module.json
    ├── module.php
    ├── Rules.php
    ├── form.json
    └── view.html
```

`module.php` verwaltet Regeln, Speicher, Datenvariablen und Ausgaben. `Rules.php` enthält Vergleichs- und Sortierlogik. `view.html` stellt die gemeinsame HTML-Oberfläche für WebHook- und SDK-Einbindungen bereit. Das Formular wird anhand der ausgewählten Darstellung und der optional vorhandenen Funktionen ergänzt.

## Technische Identität

| Merkmal | Wert |
| --- | --- |
| Bibliotheks-ID | `{726A8C3E-D761-46C3-BE6C-C9306A8C1114}` |
| Modul-ID | `{C13DA055-AF58-4504-A084-C5355DB865A0}` |
| PHP-Klasse / interner Modulname | `VisuMessages` |
| Funktionspräfix | `VMMSG` |
| WebHook | `/hook/visu-messages-<Instanz-ID>` |
| Sichtbarer Produktname | Mitteilungszentrale |
| Mindestversion | IP-Symcon 9.0 |

Interne Namen sind ausdrücklich Teil der Update-Kompatibilität. Sie werden nicht allein wegen des neuen Produktnamens umbenannt.

## Speicherung und Schnittstellen

Der interne Meldungsspeicher liegt als Instanzattribut vor. Datenvariablen sind daraus erzeugte Ausgaben; ihr manuelles Überschreiben ist kein unterstützter Weg zum Bearbeiten der Meldungen.

`VMMSG_AddTestMessage(InstanzID)` erzeugt eine visuelle Testmeldung. `VMMSG_RotateAccessKey(InstanzID)` erneuert den WebHook-Schlüssel. Timer- und Formularfunktionen wie `VMMSG_ProcessAnnouncements` und `VMMSG_UpdateAppearanceEditor` sind interne Abläufe, keine allgemeine Benachrichtigungs-API.

Durchsagen werden über `DS_Play` an eine bestehende Durchsage-Instanz übergeben. Push nutzt `VISU_PostNotification` beziehungsweise `WFC_PushNotification`. Die Ausgaben laufen in einer zeitgesteuerten Warteschlange; Audioaufrufe werden außerhalb der Sperre des Meldungsspeichers ausgeführt.

## Mitgelieferte Tests

Die PHP-Tests benötigen eine moderne PHP-CLI; die Zielinstallation des Moduls selbst läuft in IP-Symcon. Vom Bibliotheksverzeichnis aus:

```sh
php tests/rules.php
php tests/announcements.php
```

`rules.php` prüft Datentypen, Vergleiche und Sortierung. `announcements.php` verwendet eine simulierte Symcon-Umgebung für die Modulabläufe. Es verschickt keine echten Push-Nachrichten und spielt keine echten Durchsagen ab.

Abgedeckt werden unter anderem Warteschlangen, Wiederholungen, Lautstärkerücksetzung, Abbruch beim Löschen, fehlende optionale Module, Prioritäten, Datenvariablen, unabhängige Darstellungsprofile und die Namensmigration. Diese Tests ersetzen keinen Integrationstest mit einem konkreten Lautsprecher, Mobilgerät oder IPSView-Client.

Für den finalen Release wurden zusätzlich HTML-Ansichten in Chrome automatisiert auf Prioritätssortierung, kleine Breiten, Textaufklappen, Ausgabesymbole, Überschriften und getrennte Darstellungsprofile geprüft. Die PHP-Prüfungen wurden in einer PHP-8.4-WebAssembly-Laufzeit mit Symcon-Doubles ausgeführt.

## Manuelle Abnahme nach Installation

1. Regel mit eigener Testvariable auslösen.
2. Liste in allen eingesetzten Visualisierungen prüfen.
3. Anpinnen, Lösen, Einzellöschen und Sammellöschen prüfen.
4. Priorität und Datum umschalten; angepinnte Einträge bleiben oben.
5. Eigenes Profil je Visu sowie Miniansicht und langen Text prüfen.
6. Optional Durchsage und Push mit einem geeigneten Testziel prüfen.
7. Regulären Neustart prüfen: Meldungen bleiben, wartende Wiederholungen werden nicht nachgesendet.

Der Repository-Code enthält keine private Symcon-Konfiguration, keine View-Backups und keine Zugangsdaten. Für Fehlerberichte nur bereinigte Beispiele verwenden.

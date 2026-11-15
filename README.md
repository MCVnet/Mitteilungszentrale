# Visu-Messages · 0.1 – erste Testversion

Ein IP-Symcon-Modul für Symcon ab 9.0. Es sammelt frei formulierte Meldungen bei konfigurierbaren Variablenbedingungen und stellt eine gemeinsame Liste für Kachelvisualisierung, WebFront und IPSView bereit.

## Verhalten

- Pro Regel eine Variable, ein Vergleich und ein Nachrichtentext; beliebig viele Regeln.
- Zahlen: gleich, ungleich, kleiner, kleiner/gleich, größer, größer/gleich.
- Boolean: gleich/ungleich, Vergleichswert `true` oder `false` (auch `1`/`0`).
- Text: gleich, ungleich, enthält; Groß-/Kleinschreibung wird berücksichtigt.
- Eine Meldung entsteht beim Eintritt in die Bedingung. Beispiel `Temperatur < 5`: 6 → 4 erzeugt eine Meldung; 4 → 3 erzeugt keine weitere; nach 3 → 6 → 4 entsteht eine neue.
- Beim Speichern oder Neustarten werden bereits erfüllte Bedingungen nicht nachträglich gemeldet. Während eines ausgeschalteten Symcon werden keine Ereignisse erfasst.
- Meldungen mit Zeitstempel, Quellname und Text bleiben als Instanzattribut über reguläre Neustarts erhalten. Löschen der Instanz entfernt auch ihre Meldungen; Symcon-Sicherungen sind weiterhin sinnvoll.
- Angepinnte Einträge stehen oben. Sammellöschen und Speicherlimit betreffen nur nicht angepinnte Einträge. Zum einzelnen Löschen angepinnter Meldungen zuerst „Lösen“ wählen.
- Standardlimit: 200 normale Meldungen; älteste normale Einträge werden zuerst entfernt. Angepinnte Meldungen haben kein automatisches Limit.
- Änderungen in einer Ansicht gelten für alle. Kacheln erhalten Updates über das HTML-SDK; Webansichten fragen alle drei Sekunden ab.
- Version 0.1 unterstützt einzelne Variablenbedingungen, keine UND/ODER-Gruppen, Zeitverzögerung oder Hysterese. Es werden Meldungen in der Visu erstellt; kein E-Mail- oder Push-Versand.

## Installation

Die ZIP-Datei ist ein Quellcodepaket, kein Import für den Module Store.

**Lokale Installation:** Auf dem Symcon-Server den vollständigen Ordner `Visu-Messages` unterhalb des Symcon-Verzeichnisses `modules` ablegen. Die Struktur muss `modules/Visu-Messages/library.json` und `modules/Visu-Messages/VisuMessages/module.php` ergeben. Der genaue Datenpfad hängt von deinem Symcon-System ab. Danach den Symcon-Dienst neu starten und eine Instanz „Visu-Messages“ hinzufügen.

**Alternativ über Module Control:** In Module Control die Repository-URL `https://github.com/MCVnet/Symcon-Visu-Messages` hinzufügen. Anschließend eine Instanz „Visu-Messages“ anlegen.

Vor der Nutzung in der produktiven Visu zunächst mit einer Testvariable und dem Button „Testmeldung erzeugen“ prüfen.

## Regeln einrichten

1. Instanz öffnen und unter „Melderegeln“ eine Zeile hinzufügen.
2. Variable auswählen, Vergleich und Vergleichswert einstellen.
3. Nachricht eintragen, etwa `Das Fenster im Schlafzimmer wurde geöffnet.`
4. Änderungen übernehmen. Bei fehlerhafter Konfiguration sind die Melderegeln deaktiviert; Formular erneut öffnen, um den konkreten Fehlerhinweis zu sehen. Bestehende Meldungen bleiben erhalten.

Beispiele:

| Variable | Bedingung | Vergleichswert | Nachricht |
| --- | --- | --- | --- |
| Fenster geöffnet (Boolean) | gleich | true | Das Schlafzimmerfenster wurde geöffnet. |
| Batteriestand (Zahl) | kleiner | 20 | Batterie bei {name} schwach: {value} %. |
| Gerätestatus (Text) | gleich | Fertig | Die Waschmaschine ist fertig. |

Platzhalter: `{name}` Variablenname, `{value}` neuer Rohwert, `{old}` alter Rohwert, `{id}` Variablen-ID. Werte sind keine formatierten Profiltexte. Für „Waschmaschine fertig“ ist ein tatsächlicher Fertigstatus erforderlich; ein niedriger Leistungswert allein kann auch eine Programmpause bedeuten.

## Die drei Visualisierungen

**Kachelvisualisierung:** Die Instanz „Visu-Messages“ als eigene Kachel hinzufügen. Die individuelle HTML-Darstellung nutzt die Anmeldung der Visualisierung. Ein eigener WebView-Schlüssel ist dafür nicht erforderlich.

**WebFront:** Die unter der Instanz erzeugte Variable „Meldungen (WebFront / IPSView)“ einbinden oder verlinken. Sie enthält eine HTML-Box mit eingebetteter Webansicht. Standardhöhe 480 Pixel, im Formular konfigurierbar. Bei leerer Basisadresse wird dieselbe Serveradresse wie beim WebFront genutzt.

**IPSView:** Im Instanzformular eine vom IPSView-Gerät erreichbare Symcon-Basisadresse eintragen, beispielsweise `http://192.168.1.20:3777` im Heimnetz, dann übernehmen. Im Formular erscheint die vollständige „WebView-Adresse“. Diese in ein IPSView-WebView-Element übernehmen. Alternativ die erzeugte HTML-Box testen. Die WebView-Adresse ist zusätzlich in der versteckten Instanzvariable „WebView-Adresse (mit Zugriffsschlüssel)“ hinterlegt.

Bei abweichenden Hostnamen, HTTPS und Fernzugriff muss die Adresse aus dem jeweiligen Client erreichbar sein. HTTPS-Visualisierungen können eingebettete HTTP-Inhalte blockieren. Ein beliebiger Symcon-Connect-/Proxy-Zugang garantiert nicht automatisch die Erreichbarkeit benutzerdefinierter Hooks. Daher die vollständige Adresse zuerst direkt im Client-Browser testen.

## Zugriff auf die Webansicht

WebFront und IPSView nutzen einen instanzbezogenen zufälligen Zugriffsschlüssel. Wer die WebView-Adresse kennt, kann die Liste lesen, anpinnen und löschen. Die Berechtigung gilt für die gesamte Liste, nicht pro Benutzer. Die Adresse vertraulich behandeln und bei Fernzugriff HTTPS oder VPN verwenden.

„WebView-Zugriffsschlüssel erneuern“ macht alte Adressen ungültig. Bereits offene Webansichten müssen anschließend mit der neuen Adresse geladen werden; externe IPSView-Adressen manuell aktualisieren. Die Kacheldarstellung ist davon unabhängig. Die Webansicht entfernt den Schlüssel nach dem Laden aus der sichtbaren Browseradresse, daher nach einem manuellen Neuladen wieder den vollständigen Link verwenden.

## Prüfung und Grenzen dieser Lieferung

Dies ist eine erste Testversion, keine auf einem laufenden Symcon geprüfte Veröffentlichung. In der Erstellungsumgebung steht weder ein Symcon-Server noch eine PHP-Kommandozeile zur Verfügung. Die beigelegten PHP-Logiktests können mit `php tests/rules.php` ausgeführt werden. Ein erfolgreicher Test dieser reinen Logik ersetzt keinen Integrationstest auf Symcon 9.0.

Auf deinem System prüfen: Instanz anlegen; Testmeldung; Regelübergang falsch → wahr; keine Wiederholung bei weiterhin erfüllter Bedingung; anpinnen; zweite normale Meldung erzeugen; Sammellöschen erhält angepinnte Meldung; lösen und löschen; mehrere Clients öffnen; regulärer Neustart erhält gespeicherte Meldungen; WebView ohne Schlüssel wird abgewiesen.

## Technische Grundlagen

- [Symcon PHP-SDK und Installation](https://www.symcon.de/de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
- [HTML-SDK für Kacheln](https://www.symcon.de/de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/html-sdk/)
- [Native Hook-Registrierung](https://www.symcon.de/de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerhook/)
- [HTML-Box als Webinhalt](https://www.symcon.de/en/service/documentation/components/object-presentation/web-content/)

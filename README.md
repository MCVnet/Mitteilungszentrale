# Visu-Messages · 0.18

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

## Darstellung in der Instanz

Unter **Darstellung: Symbole, Farben und Breite** sind alle Optionen hinterlegt:

- **Symbole der Variablen übernehmen:** links vor jeder Meldung, vertikal zentriert. Vorrang hat das eigene Objektsymbol; andernfalls wird das Symbol der Variablendarstellung bzw. des Profils verwendet. Ohne Symbol erscheint ein Brief. Auch vorhandene Meldungen erhalten das aktuelle Quellsymbol. Bei deaktivierter Option wird immer der Brief angezeigt.
- **Symbole direkt einbetten:** standardmäßig aktiv. Der auf deinem Symcon-Server vorhandene Symbolsatz wird für die SDK-Darstellung direkt eingebettet und pro Instanz im Speicher zwischengespeichert. Damit benötigt IPSView keinen zusätzlichen externen Skriptabruf. Der lokale Webserverport ist normalerweise 3777 und nur bei abweichender Serverkonfiguration anzupassen. Der erste Abruf kann kurz länger dauern; die HTML-Antwort ist mit eingebetteten Symbolen größer.
- **Symbolgröße:** standardmäßig 48 Pixel, einstellbar von 24 bis 96 Pixel. Auf sehr schmalen Displays wird die Größe auf höchstens 48 Pixel begrenzt. Ein hinterlegtes, aber nicht ladbares Symbol wird mit „?“ gekennzeichnet und nicht durch einen Brief ersetzt.
- **Eigene Farben verwenden:** aktiviert die vier Farbfelder für Ansichtshintergrund, Meldungshintergrund, Schrift und Akzent. Die Akzentfarbe wird für Symbole und „Angepinnt“ verwendet. Ohne eigene Farben folgt die Ansicht dem hellen/dunklen Modus des Clients.
- **Breite:** Mini (max. 320 px), Kompakt (480 px), Normal (760 px), Groß (1100 px) oder Benutzerdefiniert mit Mindest-/Maximalbreite. Auf schmaleren Displays bleibt die Darstellung innerhalb der verfügbaren Breite. Die Werte beziehen sich auf CSS-Pixel der jeweiligen Webansicht, nicht auf physische Bildschirm-Pixel.

Änderungen übernehmen. Farben und Breite werden an offene Ansichten übertragen; nach dem Modulupdate die Ansichten einmal neu öffnen, damit die neue HTML-Datei geladen wird. Einstellungen gelten pro Instanz. Werden mehrere Visu-Messages-Instanzen verwendet, haben sie jeweils eigene Meldungen und Darstellungseinstellungen.

Die Darstellung wurde mit den tatsächlich installierten Symcon-Symbolen, dem Brief-Fallback, älteren SVG-Symbolen, allen Breiten und einem 320-Pixel-Display geprüft. PHP-Syntax und die Registrierung der Formularfelder wurden geprüft; ein vollständiger PHP-Laufzeittest außerhalb Symcon steht nicht zur Verfügung.

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

## Farben je Visualisierung (0.17)

Die Instanz zeigt vier getrennte Farbbereiche: Gemeinsame Vorgabe, WebFront, IPSView und Kachelvisu. Pro Bereich stehen Ansicht/Meldung sowie Schrift/Akzent in zwei Reihen. Die einzelnen Visus verwenden die gemeinsame Vorgabe, bis „Eigene Farben verwenden“ aktiviert wird. Bestehende gesetzte Farben bleiben erhalten.

Transparenz und der Diagnosemodus sind vorerst entfernt. Zuvor transparente Farbfelder werden beim Aktualisieren auf die ursprünglichen festen Standardfarben gesetzt. Der HTML-Meldungsbereich wird wieder regulär angezeigt. Eigene CSS-Regeln bleiben erhalten und können weiterhin Farben überschreiben; bestehendes `background: transparent` im eigenen CSS bei Bedarf selbst entfernen.

CSS liegt eingeklappt unter „Erweitert · CSS“ im jeweiligen Visu-Bereich. Beispiel: `article { border-radius: 16px; }`. Die Farbfelder setzen CSS-Variablen inline und haben Vorrang vor gleichnamigen Variablen in einem normalen Stylesheet.

Unter „Erweitert · Einbindung“ liegen Symbol-Einbettung, lokaler Webserverport und SDK-Profil. Die automatische SDK-Erkennung unterscheidet eigenständige Dokumente (IPSView) von Frames (Kachelvisu); die manuelle Übersteuerung gilt für alle SDK-Clients dieser Instanz.

## Instanzaufbau (0.18)

Die Melderegeln stehen zuerst, über die volle Formularbreite und mit 16 sichtbaren Zeilen. Die Variablenspalte ist 420 Pixel breit. Hinweise und Einstellungen sind eingeklappt. Unter Einstellungen liegen Speicher und Begrenzung, Darstellung, die vier Farbbereiche sowie die technische Einbindung getrennt. HTML-Höhe gehört zur Darstellung; das Meldungslimit zu Speicher und Begrenzung.

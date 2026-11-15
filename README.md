# Visu-Messages · 0.32

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

## Modulvariablen und Daten (0.19)

Unter Einstellungen → Modulvariablen und Daten aktiviert „Modulvariablen und Daten im Objektbaum anzeigen“ die Sichtbarkeit der zusätzlichen Ausgabevariablen unter der Instanz. Nach Änderungen übernehmen werden sie ein- oder ausgeblendet. Standardmäßig sind sie verborgen. Die normale HTML-Meldungsvariable bleibt sichtbar.

| Ident | Inhalt |
| --- | --- |
| `URL` | WebView-Adresse inklusive Zugriffsschlüssel |
| `MessageCount` | Gesamtzahl aller gespeicherten Meldungen |
| `PinnedCount` | Anzahl angepinnter Meldungen |
| `UnpinnedCount` | Anzahl nicht angepinnter Meldungen |
| `MessageData` | JSON mit `version`, `total`, `pinned`, `unpinned`, `messages` |

Die JSON-Liste enthält dieselben sortierten Meldungen wie die Visualisierung, inklusive Symbol. Die Ausgabe wird beim Speichern der Konfiguration und nach Meldungsänderungen aktualisiert, auch wenn sie ausgeblendet ist. Sie ist kein Eingabekanal: Änderungen an den Ausgabevariablen verändern den internen Meldungsspeicher nicht. Der Sichtbarkeitsschalter blendet Daten nur aus und stellt keine Zugriffssperre dar.

## Höhe einzelner Meldungen (0.21)

Unter Einstellungen → Darstellung · Symbole und Größe legt „Höhe einer Meldung“ die feste Gesamthöhe der Karte inklusive Symbol, Text, Innenabständen und Schaltflächen fest (0–2000 Pixel). 0 = automatisch. Überlanger Inhalt ist innerhalb der Karte scrollbar. Die Einstellung gilt für alle drei Visualisierungen, unabhängig von der gesamten HTML-Box. Der bisherige Mindesthöhenwert wird als feste Höhe weiterverwendet.

## Kartenlayout (0.22)

„Inhalt an die eingestellte Kartenhöhe anpassen“ skaliert Symbol, Texte, Innenabstände und Schaltflächen proportional zur Höhe (Referenz 132 Pixel, Skalierung begrenzt auf 60–200 Prozent). Bei automatischer Höhe gilt 100 Prozent. Sehr kleine Karten, lange Texte oder schmale Ansichten bleiben scrollbar, damit Bedienelemente erreichbar sind.

Unter Darstellung → Meldungsbox · Rahmen und Schatten liegen Rahmenfarbe, Rahmenstärke (0 = ohne), Eckenradius und Schatten. Diese Vorgaben gelten für alle Ansichten. Der farbige linke Pin-Streifen bleibt eine Zustandsmarkierung.

Eigenes CSS je Visu kann diese Vorgaben überschreiben, beispielsweise:

```css
:root {
  --message-border-color: #3899dd;
  --message-border-width: 2px;
  --message-radius: 16px;
  --message-shadow: 0 6px 18px rgba(0,0,0,.3);
  --message-gap: 12px;
}
article .message { font-weight: 600; }
```

`--message-scale` kann im CSS manuell überschrieben werden. Die automatische Skalierung reagiert auf die Höhen-Einstellung der Instanz; direkte CSS-Regeln für `height` lösen keine zusätzliche automatische Skalierung aus.

## Kompakte Meldungen und mehr Regelplatz (0.23)

Neue Instanzen verwenden 60 Pixel Kartenhöhe. Bereits gespeicherte Höhen bleiben erhalten; für bestehende Instanzen die Höhe auf 60 setzen. Bis einschließlich 90 Pixel gilt das kompakte Layout: Symbol links, Metadaten/Text in der Mitte, Bedienelemente rechts. Auf schmalen Displays werden die Schaltflächen als Symbole mit zugänglichen Beschriftungen dargestellt. Lange Inhalte bleiben scrollbar.

Die Skalierung verwendet im kompakten Layout 60 Pixel als Referenz, darüber das bisherige 132-Pixel-Layout. Der Regelbereich zeigt jetzt 24 Zeilen, die Variablenspalte ist 480 Pixel breit.

## Lesbarkeit und Aktualisierung (0.24)

Kompakte Karten verwenden größere Schrift: Meldung 15 px, Metadaten 11 px und Schaltflächen 13 px bei 60 Pixel Kartenhöhe. Kopfzeile und Karten haben dieselbe maximale Breite. Unter der Liste steht nur noch der Zeitpunkt der letzten Datenaktualisierung; der permanente Pin-Hinweis entfällt.

Die Integer-Variable `LastUpdate` („Letzte Aktualisierung“, Profil `~UnixTimestamp`) enthält den gespeicherten Symcon-Zeitpunkt. Sie wird zusammen mit den Modulvariablen ein-/ausgeblendet. Meldungsänderungen und das Übernehmen der Konfiguration setzen den Zeitpunkt; reine Refresh-/Web-Abfragen ändern ihn nicht. SDK, Web-API und JSON-Ausgabe enthalten denselben Wert als `updatedAt`.

## Übergeordnete Instanz im Nachrichtentext (0.25)

Der Platzhalter `{parent}` liefert den Namen des direkten übergeordneten Objekts der überwachten Variable (bei Gerätevariablen üblicherweise die Instanz). Neue Regeln verwenden `{parent} · {name}: {value}`. Beispiel: `EG | Büro links | Fenster-offen · Value: true`. Bei Variablen direkt unter der Wurzel lautet der übergeordnete Name `IP-Symcon`.

Bestehende Regeln mit exakt dem bisherigen Standardtext `{name}: {value}` werden beim Aktualisieren auf den neuen Standard erweitert. Individuelle Texte sowie bereits gespeicherte Meldungen bleiben unverändert.

## Durchsagen pro Melderegel (0.26)

Im Bearbeitungsdialog einer Regel kann unter „Durchsage und Wiederholung“ die Ausgabe aktiviert werden. Ausgewählt wird eine Instanz des offiziellen Durchsage-Moduls (`symcon/Durchsage`). Dort werden TTS, Lautsprecher und Lautstärke konfiguriert. Visu-Messages übergibt den fertig aufgelösten Nachrichtentext an `DS_Play`.

Zusätzliche Wiederholungen: 0–100; 0 = einmalige Durchsage, 2 = insgesamt drei Ausgaben. Abstand: 1–86400 Sekunden ab Rückkehr des vorherigen Aufrufs. Die erste Ausgabe wird per Timer frühestens etwa eine Sekunde nach dem Ereignis gestartet. Es wird eine Ausgabe je Timerdurchlauf verarbeitet, aufeinanderfolgende Aufrufe innerhalb dieser Visu-Messages-Instanz werden serialisiert. Das Durchsage-Modul steuert die eigentliche Wiedergabe und deren Ende.

Löschen oder Entfernen durch das Meldungslimit verwirft offene Ausgaben dieser Meldung. Anpinnen ändert die Wiederholung nicht. Ein Rückwechsel der Quellvariable beendet die feste Wiederholungsanzahl nicht. Speichern der Konfiguration und Neustarten verwerfen die Warteschlange; alte Meldungen werden nicht nachträglich abgespielt. Laufende Audiodateien werden durch Löschen nicht gestoppt. Bei nicht verfügbarer Durchsage-Instanz oder Fehler wird die verbleibende Ausgabe dieser Meldung abgebrochen und eine Warnung protokolliert. Die visuelle Meldung bleibt erhalten.

Bestehende Regeln sind standardmäßig ohne Durchsage. Tests und Testmeldungen starten keine Durchsage.

## Durchsage-Lautstärke und Symbole (0.27)

Pro Melderegel gibt es „Eigene Lautstärke“ mit 1–100 Prozent. Ohne Aktivierung bleibt die Vorgabe der Durchsage-Instanz maßgeblich. Da `DS_Play` keinen Lautstärkeparameter anbietet, wird `SonosVolume` bzw. `MediaPlayerVolume` für den Aufruf temporär gesetzt, angewendet und im finally-Block auf den vorherigen Wert zurückgesetzt. Ungespeicherte Änderungen der Zielinstanz verhindern diesen temporären Eingriff. Visu-Messages-Aufrufe auf dieselbe Durchsage-Instanz werden durch eine gemeinsame Sperre serialisiert; externe Aufrufe anderer Module nutzen diese Sperre nicht. Das Wiederherstellen der Modulkonfiguration ist keine Zusage, die tatsächliche MediaPlayer-Lautstärke nach dem Audio zurückzustellen (dies steuert das Durchsage-Modul).

Neue Meldungen speichern die konfigurierte Durchsage mit Wiederholungsanzahl und Lautstärke im Feld `announcement`. Am Textende erscheint ein kleines Lautsprechersymbol; bei zusätzlichen Wiederholungen ein weiteres Wiederholungssymbol. Tooltips enthalten Lautstärke und Wiederholungsdetails. Die Symbole zeigen die beim Erstellen hinterlegte Konfiguration, keine Bestätigung erfolgreicher Wiedergabe oder noch ausstehender Wiederholungen. Ältere Meldungen ohne diese Angaben bleiben unverändert.

## Direkte Push-Benachrichtigung (0.28)

Im Bearbeitungsdialog jeder Regel gibt es „Push-Benachrichtigung“. Aktivieren, eine Kachel-Visualisierung oder einen WebFront-Konfigurator auswählen und optional Titel und Sprungziel hinterlegen. Ohne eigenes Sprungziel wird die Visu-Messages-Instanz geöffnet; sie muss in der ausgewählten Visu erreichbar sein. Gesendet wird der fertig aufgelöste Meldungstext; standardmäßig einmal, optional mit eigenen Push-Wiederholungen. Durchsage und Push sind unabhängig einschaltbar.

Der Versand verwendet direkt `VISU_PostNotification` bzw. `WFC_PushNotification`, ohne das mehrstufige Benachrichtigungsmodul zu verändern oder eine Kette zu starten. Titel werden auf 32, Nachrichtentexte auf 256 Unicode-Zeichen begrenzt. Die Liste speichert weiterhin den vollständigen Text. Die Ziel-Visu muss registrierte Mobilgeräte besitzen; die üblichen Symcon-Push-Voraussetzungen gelten. IPSView ist über diese beiden APIs kein eigenes Push-Ziel.

Die Zustellung erfolgt über dieselbe interne Warteschlange wie Durchsagen; bei Kombination wird Push zuerst eingeplant. Pro geplanter Ausgabe wird genau ein Versand versucht. Fehler werden protokolliert, ohne die Meldung oder deren Durchsage zu entfernen. Löschen der Meldung vor dem Versand, Speichern der Konfiguration oder Neustart verwirft offene Ausgaben. Keine Push-Nachricht durch reine Testmeldungen.

Durchsage- und Wiederholungssymbole sind auf 1,35 em vergrößert und skalieren mit der Meldungsschrift.

API-Dokumentation: https://www.symcon.de/de/service/dokumentation/modulreferenz/visualisierungen/kachel-visualisierung/visu-postnotification/

## Push-Wiederholungen und Regeleditor (0.29)

Durchsage und Push sind im Regeleditor standardmäßig eingeklappt. Der Nachrichtentext hat einen eigenen offenen Bereich mit mehrzeiligem Eingabefeld und Platzhalterhilfe.

Push besitzt nun eine unabhängige Zahl zusätzlicher Wiederholungen (0–100) und einen Abstand (1–86400 Sekunden). 0 = einmalig; 2 = drei Ausgaben insgesamt. Bestehende Regeln erhalten 0 Wiederholungen und 60 Sekunden Abstand. Nach einem Versandfehler werden die weiteren Push-Ausgaben dieser Meldung abgebrochen; Durchsagen bleiben davon unabhängig. Die tatsächliche Zustellzeit kann durch den Push-Dienst verzögert sein.

## Aufgeräumte Darstellung und Mini (0.30)

Darstellung ist in Kartenformat, Schrift und Symbole, Rahmen/Schatten sowie HTML-Box getrennt. Benutzerdefinierte Breiten liegen eingeklappt beim Kartenformat. Meldungstext (10–30 px) und Zeit/Quelle (9–20 px) sind getrennt konfigurierbar. Mit aktivierter Inhaltsskalierung werden die eingestellten Größen zusätzlich mit der Kartenhöhe skaliert.

Die Mini-Darstellung richtet sich nach der tatsächlichen Kartenbreite (bis 420 px), nicht mehr nur nach dem Browserfenster. Kleine Karten verwenden Symbol-Schaltflächen, eine kurze Zeitzeile mit Quelle und bis zu zwei Textzeilen. Vollständiges Datum/Quelle stehen im Tooltip. Antippen des Nachrichtentexts oder Enter/Leertaste klappt die Meldung vollständig auf und wieder zu. Bei großer Schrift dürfen schmale Karten über die eingestellte Höhe hinauswachsen, damit Inhalte und Bedienung lesbar bleiben.

## Überschrift, Debug und optionale Funktionen (0.31)

Unter Darstellung → Überschrift lässt sich „Meldungen“ durch einen eigenen Text ersetzen oder nur die Überschrift ausblenden. Anzahl und Löschbutton bleiben erhalten.

Unter Modulvariablen und Daten kann erweitertes Debug eingeschaltet werden. Die Ausgabe erfolgt im Symcon-Debug-Fenster der Instanz; der Schalter öffnet dieses Fenster nicht automatisch. Er protokolliert Meldungs-IDs, Quellvariablen und Ausgabeschritte, nicht Nachrichtentexte oder Zugriffsschlüssel. Warnungen bei Ausgabefehlern bleiben unabhängig davon aktiv.

Unter Optionale Funktionen · Verfügbarkeit wird angezeigt, ob Durchsage-Instanzen und Push-Zielvisualisierungen vorhanden sind. Fehlt ein gewähltes Ziel, wird nur die betreffende optionale Ausgabe übersprungen; gültige Melderegeln erzeugen weiterhin visuelle Meldungen. Nach Einrichtung/Änderung eines Zieles Konfiguration erneut übernehmen. Das separate mehrstufige Modul Benachrichtigung ist nicht erforderlich, da Push direkt über die Visu-API gesendet wird.

## Push-Symbol und Einstellungsreihenfolge (0.32)

Die Farbbereiche stehen in der Reihenfolge Kachelvisu, WebFront, IPSView. Neue Meldungen speichern die Push-Konfiguration im Feld `push` und zeigen bei aktivierter Push-Option ein Glockensymbol am Textende. Der Tooltip enthält Wiederholungsanzahl und Abstand. Wie das Durchsagesymbol zeigt die Glocke eine hinterlegte Konfiguration, keine Zustellbestätigung. Ältere Meldungen ohne Push-Metadaten bleiben unverändert.

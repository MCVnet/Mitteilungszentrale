# Fehlerbehebung und bekannte Grenzen

[Übersicht](../README.md) · [Installation](INSTALLATION.md) · [Handbuch](HANDBUCH.md)

## Instanz ist nicht auffindbar

Prüfen, ob die Bibliothek in der Modulverwaltung vollständig geladen wurde und IP-Symcon mindestens Version 9.0 verwendet. Nach **Mitteilungszentrale** suchen. Der technische interne Name `VisuMessages` und der alte Alias sind aus Kompatibilitätsgründen weiterhin vorhanden.

Bei einem Update die vorhandene Bibliothek aktualisieren. Keine zweite Installation unter anderem Ordnernamen hinzufügen. Die Modul-IDs sind identisch.

## Keine neue Meldung

- Ist die Instanz aktiv und die Konfiguration fehlerfrei?
- Ist die Regel aktiv und die richtige Variable gewählt?
- Passt der Vergleichswert zum Datentyp?
- Wurde die Bedingung durch eine spätere Änderung neu erfüllt?

Eine bereits erfüllte Bedingung wird beim Speichern nicht nachträglich gemeldet. Ein weiterhin wahrer Zustand erzeugt auch bei weiteren Änderungen innerhalb dieses Zustands keine zusätzliche Meldung. Zum Prüfen eine eigene Testvariable erst in einen nicht erfüllten Zustand und anschließend in den Zielzustand bringen.

Die Testmeldung prüft nur die Darstellung und löst keine regelbezogene Audio- oder Push-Ausgabe aus.

## Modulstatus meldet fehlerhafte Konfiguration

Variablen-ID, Operator, Vergleichswert, Wiederholungszahl und Wiederholungsabstand prüfen. Ungültige Hauptregeln können die Überwachung verhindern. Wiederholungen müssen zwischen 0 und 100 und der Abstand zwischen 1 und 86400 Sekunden liegen.

Eine fehlende optionale Ausgabeinstanz sollte die sichtbare Meldung nicht verhindern. Sie wird gesondert behandelt. Nach einer Korrektur die Instanzänderungen übernehmen.

## Anpinnen oder Löschen wirkt nicht

Angepinnte Meldungen sind gegen direktes Löschen geschützt. Zuerst **Lösen**, dann **Löschen** verwenden. Beim Sammellöschen die Bestätigung **Jetzt löschen** ausführen. Angepinnte Einträge bleiben absichtlich erhalten.

Falls die Ansicht nicht reagiert, vollständig neu laden und prüfen, ob derselbe Instanzbestand angezeigt wird. Bei direktem WebView-Aufruf muss ein gültiger Zugriffsschlüssel vorhanden sein. Eine geänderte oder nur teilweise kopierte Adresse kann die Bedienung verhindern. Nach Schlüsselwechsel außerhalb gespeicherte Links aktualisieren.

## IPSView zeigt eine weiße oder leere Fläche

Eine weiße Fläche mit sichtbaren Meldungen ist von einer vollständig leeren Darstellung zu unterscheiden.

**Weiße Fläche mit Inhalt:** Eine transparente HTML-Seite macht in manchen nativen Clients die weiße WebView-Fläche sichtbar. Version 1.0.0 verwendet deshalb feste Farben. Unter dem Profil **IPSView** eigene Farben aktivieren und einen passenden Hintergrund wählen. CSS kann die native Zeichenfläche außerhalb des HTML-Dokuments nicht zuverlässig transparent machen.

**Kein Inhalt:** Die TileHtml-Zuordnung prüfen: Sie muss auf die Modulinstanz zeigen, nicht auf die HTML-Stringvariable. View speichern und im Client neu laden. Bei Iframe-/WebView-Adressen zusätzlich Erreichbarkeit, Protokoll und Zugriffsschlüssel prüfen. Eine im Desktopbrowser funktionierende Adresse muss nicht aus jedem mobilen Client gleich erreichbar sein.

## Variablensymbol fehlt oder bleibt bei drei Punkten

1. Prüfen, ob tatsächlich an der Variable ein Symbol hinterlegt ist.
2. Im verwendeten Darstellungsprofil die Symbolübernahme aktivieren.
3. Bei nativer SDK-Einbindung das Einbetten der Symbole aktiviert lassen und den lokalen Webserver-Port prüfen.
4. Ansicht vollständig neu laden.

Ohne hinterlegtes Symbol wird ein Brief angezeigt. Ein vorhandenes, aber nicht ladbares Symbol ist ein anderes Problem; besonders ältere externe Symbolquellen können in nativen Clients unerreichbar sein. Das Modul hält moderne Symboldaten in lokalen Dateien vor, statt sehr große Bibliotheken in Symcon-Buffern zu speichern.

Tauchen erneut Meldungen wie „Buffer … over …“ auf, zuerst prüfen, ob tatsächlich die aktuelle Bibliotheksversion geladen ist und keine zweite alte Kopie existiert. Anschließend mit Versionsangabe und bereinigtem Protokoll berichten.

## Miniansicht schneidet Inhalte ab

Die echte verfügbare Breite prüfen, nicht nur die Breitenvorgabe der Karte. Für schmale Karten die aktuelle Version laden: Sie schaltet auf Symbolschaltflächen und begrenzte Textzeilen um. Durch Klick auf den Nachrichtentext kann die Meldung vollständig aufgeklappt werden.

Extrem große Schrift, eine feste kleine Höhe und viel Text lassen sich nicht gleichzeitig vollständig in 60 px darstellen. Die Karte kann bei schmalen Ansichten zugunsten der Lesbarkeit wachsen. Eigene CSS-Höhen, `overflow`-Regeln oder feste Positionierungen vorübergehend entfernen.

## Änderungen wirken in der falschen Visu oder gar nicht

Im Konfigurationseditor zuerst das richtige Profil auswählen. **Eigene Darstellung** betrifft Größen und Layout; **Eigene Farben** ist ein eigener Schalter. Ohne diese Schalter gelten die gemeinsamen Vorgaben. Eigenes CSS kann normale Einstellungen übersteuern.

Die Auswahl des bearbeiteten Profils legt nicht den Modus eines laufenden Clients fest. Falls ein SDK-Client falsch erkannt wird, die erweiterte SDK-Profilvorgabe prüfen. Eine feste Vorgabe gilt für alle SDK-Clients dieser Instanz; sie kann bei paralleler Kachel- und IPSView-Nutzung daher das andere Profil ebenfalls beeinflussen.

## Durchsage nicht verfügbar

Der Hinweis in der Melderegel unterscheidet zwischen fehlendem Modul und fehlender Instanz. Zuerst das Modul **Durchsage** und dort ein funktionierendes Ziel einrichten. Die Mitteilungszentrale bleibt auch ohne diese Erweiterung nutzbar.

Bei ausgewähltem Ziel prüfen: Ist die Durchsage-Instanz vorhanden und funktionsfähig? Funktioniert eine direkte Testausgabe dort? Sind für eine eigene Lautstärke noch ungespeicherte Änderungen vorhanden? Fehler im Symcon-Protokoll prüfen.

## Push kommt nicht an

- Eine unterstützte Kachelvisu oder einen WebFront-Konfigurator auswählen.
- Mobilgerät in dieser Visualisierung registrieren und deren Push-Einrichtung prüfen.
- Erforderliche Symcon-Freischaltungen beziehungsweise Voraussetzungen prüfen.
- Prüfen, ob das gewünschte Sprungziel in der Zielvisu vorhanden ist.
- Mit einer echten neu ausgelösten Melderegel testen; die allgemeine Testmeldung versendet keinen Push.

Das separate mehrstufige Benachrichtigungsmodul ist für diesen direkten Versand nicht erforderlich. IPSView ist kein eigener direkter Push-Empfänger dieser Funktion. Das Glockensymbol zeigt die hinterlegte Ausgabe, keinen Zustellnachweis.

## Wiederholungen stoppen unerwartet oder laufen weiter

Speichern der Instanzkonfiguration und Neustarts verwerfen wartende Wiederholungen. Löschen und Speicherbegrenzung ebenfalls. Ein Ausgabefehler verwirft den verbleibenden Auftrag der betroffenen Ausgabeart und wird protokolliert.

Die Rückkehr einer Variable in den normalen Zustand stoppt die Wiederholungen hingegen nicht. Sie laufen gemäß der ursprünglich eingestellten festen Anzahl weiter. Eine bereits laufende Audiodatei wird durch Löschen nicht abgebrochen.

## Letzte Aktualisierung bleibt gleich

Der Zeitpunkt beschreibt die letzte gespeicherte Aktualisierung, nicht den letzten Bildschirmaufruf. Regelmäßige Abfragen oder reines Lesen ändern ihn nicht. Unter **Modulvariablen und Daten** lässt sich die eigene Zeitstempelvariable sichtbar machen.

## Diagnose für einen Fehlerbericht

Debug in der Instanz aktivieren, deren Debugfenster öffnen und den Fehler einmal gezielt nachstellen. Anschließend Debug wieder deaktivieren.

Für einen hilfreichen Bericht angeben:

- Modulversion und IP-Symcon-Version.
- Betroffene Ansicht und Clientversion.
- Konkreter Auslöser und erwartetes Verhalten.
- Bereinigte Regelwerte und gegebenenfalls die Fehlermeldung.
- Ob der Fehler auch ohne eigenes CSS auftritt.

Zugriffsschlüssel, private Serveradressen, vollständige View-Dateien, Zugangsdaten und persönliche Meldungstexte vor der Veröffentlichung entfernen.

## Grenzen dieses Releases

Keine transparente native IPSView-Fläche, kein allgemeines Archiv, keine UND/ODER-Regelketten, kein automatisches Aufheben einer Meldung beim Normalzustand, keine unendlichen Wiederholungen und keine Empfangsbestätigung für Push oder Audio. Die optionalen Symbole kennzeichnen die konfigurierte Funktion.

# Update von Visu-Messages auf Mitteilungszentrale

[Übersicht](../README.md) · [Installation](INSTALLATION.md)

## Vor dem Update

Eine reguläre Symcon-Sicherung erstellen. Den Stand eigener CSS-Anpassungen und wichtiger Instanzeinstellungen bei Bedarf zusätzlich dokumentieren. Die vorhandene Instanz nicht löschen.

## Update durchführen

1. In der Modulverwaltung die bereits installierte Bibliothek **Visu-Messages** auswählen.
2. Diese Bibliothek aktualisieren. Der neue Repository-Name lautet **MCVnet/Mitteilungszentrale**.
3. GitHub leitet die bisherige Repository-Adresse normalerweise weiter. Sollte die Modulverwaltung damit nicht aktualisieren, die hinterlegte Adresse der vorhandenen Bibliothek auf folgende URL ändern:

   ```text
   https://github.com/MCVnet/Mitteilungszentrale
   ```

4. **Keine zweite Kopie der Bibliothek hinzufügen.** Beide Namen bezeichnen dieselben technischen Modul-IDs.
5. Die vorhandene Instanz öffnen und die Änderungen übernehmen.
6. Prüfen, dass die Bibliothek Version **1.0.0**, Build **100** meldet.
7. WebFront, Kachelvisu und IPSView aktualisieren beziehungsweise neu laden.
8. Meldungen, Regeln, Darstellungsprofile sowie optionale Ausgaben prüfen.

## Was bleibt erhalten?

- Bibliotheks-ID und Modul-ID.
- Vorhandene Instanz-ID und untergeordnete Variablen-IDs.
- Gespeicherte Meldungen, angepinnte Einträge und Einstellungen.
- Interner Modulordner `VisuMessages`, PHP-Klasse und Funktionspräfix `VMMSG`.
- Bestehende WebHook-Pfade `visu-messages-<Instanz-ID>` und Zugriffsschlüssel.

Die technischen Namen bleiben bewusst erhalten, damit ein Update nicht als anderes Modul behandelt wird. Im sichtbaren Produktnamen, im Paket und in der Dokumentation wird **Mitteilungszentrale** verwendet. Der bisherige Name bleibt als Suchalias verfügbar.

## Was wird umbenannt oder ergänzt?

- Die Bibliothek und die auswählbare neue Instanz heißen Mitteilungszentrale.
- Instanzen mit dem bisherigen unveränderten Standardnamen `Visu-Messages` oder `VisuMessages` werden beim Übernehmen entsprechend umbenannt. Eigene Instanznamen bleiben bestehen.
- Der bisherige Standard-Push-Titel `Visu-Messages` wird zu `Mitteilungszentrale`. Eigene Titel bleiben bestehen.
- Die Testmeldung und der HTML-Seitentitel verwenden den neuen Namen.
- Ältere Regeln erhalten fehlende optionale Felder mit verträglichen Standardwerten. Durchsage und Push werden dadurch nicht ungefragt eingeschaltet.
- Der alte Standardtext `{name}: {value}` wird um die übergeordnete Instanz ergänzt: `{parent} · {name}: {value}`. Andere eigene Texte bleiben bestehen.
- Ältere Meldungen ohne Priorität werden als Prio 3 behandelt.
- Frühere leere/transparente reguläre Farbangaben werden auf feste Standardfarben zurückgeführt. Eine transparente native IPSView-Fläche wird in diesem Release nicht angeboten.

Eine spätere Änderung von Regeltext, Priorität oder Ausgabeoptionen verändert bereits gespeicherte Meldungstexte und ihre Ausgabe-Metadaten nicht rückwirkend. Bei bisherigen Meldungen können deshalb neue Ausgabesymbole fehlen.

## Wiederholungen nach dem Update

Beim Übernehmen der Konfiguration und beim Neustart werden noch wartende Durchsagen und Push-Wiederholungen verworfen. Sie werden nicht nachgesendet. Die gespeicherten sichtbaren Meldungen bleiben erhalten.

## Manuelle ZIP-Installation

Das ZIP enthält den Ordner `Mitteilungszentrale` mit `library.json` auf dessen oberster Ebene. In einer manuell verwalteten Installation gehört diese Bibliothek in das dafür vorgesehene Symcon-Modulverzeichnis. Der Unterordner `VisuMessages` muss erhalten bleiben.

Keine zusätzliche Verzeichnisebene zwischen Bibliothekswurzel und `library.json` einfügen. Keine manuelle zweite Kopie neben einer bereits über Module Control verwalteten Version ablegen. Das erzeugt doppelte Modul-IDs. Ein manuelles Update sollte entsprechend dem bisherigen Installationsverfahren erfolgen; für normale Installationen ist die Modulverwaltung vorzuziehen.

## Falls etwas nicht stimmt

Zuerst die Bibliotheksversion und die Instanzkonfiguration prüfen, dann die Ansicht neu laden. Details stehen in der [Fehlerbehebung](FEHLERBEHEBUNG.md). Eine Rückkehr auf einen früheren Stand nur mit passender Sicherung und unter Berücksichtigung inzwischen veränderter Einstellungen durchführen.

# Installation und erste Einrichtung

[Zur Übersicht](../README.md) · [Handbuch](HANDBUCH.md) · [Fehlerbehebung](FEHLERBEHEBUNG.md)

## 1. Voraussetzungen

- IP-Symcon **ab Version 9.0**.
- Zugriff auf die Verwaltungskonsole und die Modulverwaltung.
- Eine erreichbare Symcon-Visualisierung und ein aktueller Browser beziehungsweise IPSView-Client.
- Schreibbare normale Symcon-Moduldatenverzeichnisse für lokale Symbol-Zwischenspeicherung.

Für reine Meldungen sind keine zusätzlichen Durchsage- oder Benachrichtigungsmodule erforderlich. Für Sprachausgabe wird eine eingerichtete Instanz des Moduls **Durchsage** benötigt. Für Push werden eine unterstützte Ziel-Visualisierung und dort registrierte Mobilgeräte benötigt; deren Symcon-seitige Voraussetzungen müssen erfüllt sein.

## 2. Installation über die Modulverwaltung

Die Bezeichnungen unterscheiden sich je nach Konsolendarstellung etwas; gemeint ist die Bibliotheksverwaltung „Modules“, „Module Control“ beziehungsweise „Modulverwaltung“ unter den Kerninstanzen.

1. Vor Änderungen eine aktuelle Symcon-Sicherung erstellen.
2. Die Modulverwaltung öffnen und **Hinzufügen** wählen.
3. Als Repository-Adresse eintragen:

   ```text
   https://github.com/MCVnet/Mitteilungszentrale
   ```

4. Das Laden der Bibliothek abwarten. Wenn eine Zweigauswahl angeboten wird, den Standardzweig **main** verwenden.
5. Im Objektbaum am gewünschten Ort **Instanz hinzufügen** öffnen.
6. Nach **Mitteilungszentrale** suchen und die Instanz erstellen.
7. Die Instanzkonfiguration öffnen und die gewünschten Melderegeln anlegen.

Eine Instanz kann alle drei Visualisierungen gleichzeitig versorgen. Zusätzliche Instanzen sind nur für tatsächlich getrennte Meldungslisten nötig. Die Versionsnummer des finalen Pakets ist **1.0.0**, Build **100**.

**Bei einem Update von Visu-Messages diesen Ablauf nicht als Neuinstallation wiederholen.** Stattdessen die vorhandene Bibliothek aktualisieren; siehe [Update-Anleitung](UPGRADE.md).

## 3. Erste Melderegel

1. In **Melderegeln** einen neuen Eintrag hinzufügen.
2. **Aktiv** einschalten und eine Variable auswählen.
3. Beispielsweise eine Boolean-Variable, Bedingung **gleich** und Vergleichswert `true` wählen.
4. Den Nachrichtentext eintragen, zum Beispiel:

   ```text
   {parent} · {name}: {value}
   ```

5. Priorität wählen; Standard ist **Prio 3**.
6. Durchsage und Push zunächst deaktiviert lassen, wenn nur die Anzeige getestet werden soll.
7. Den Regeleditor schließen und die Änderungen der Instanz übernehmen.

Die Regel reagiert anschließend auf eine tatsächliche Variablenänderung, durch die ihre Bedingung neu erfüllt wird. Für einen ungefährlichen Funktionstest eine eigene Testvariable verwenden: erst `false`, dann `true`. Reale Geräte müssen dafür nicht geschaltet werden.

Die Schaltfläche für eine **Testmeldung** prüft die Liste und deren Darstellung. Sie löst keine regelbezogenen Durchsagen oder Push-Nachrichten aus.

## 4. Kachelvisu

1. Die Instanz **Mitteilungszentrale** in der gewünschten Kategorie beziehungsweise an der gewünschten Stelle der Kachelvisualisierung verfügbar machen.
2. Die Instanz als HTML-Visualisierung/Kachel anzeigen lassen. Das Modul stellt seine Darstellung über das Symcon HTML-SDK bereit.
3. Die äußere Größe der Kachel in der Visualisierung einstellen.
4. In der Modulinstanz unter **Darstellung je Visualisierung → Kachelvisu** bei Bedarf eine eigene Darstellung aktivieren.
5. Breite, Meldungshöhe, Schriftgröße und Farben einstellen und übernehmen.

Die Moduloption „Höhe einer Meldung“ bestimmt die einzelne Karte. Sie bestimmt nicht die äußere Höhe der Kachel. Diese wird in der Visualisierung festgelegt.

## 5. WebFront

Unter der Modulinstanz wird die Stringvariable **Meldungen (WebFront / IPSView)** mit HTML-Darstellung angelegt. Ihr Inhalt bettet die aktuelle Meldungsliste ein.

1. Diese Variable in einer im WebFront sichtbaren Kategorie platzieren oder dorthin verlinken.
2. Im WebFront die Kategorie öffnen und die HTML-Box prüfen.
3. Unter **Darstellung je Visualisierung → WebFront** die gewünschte Gestaltung einstellen.
4. Die gemeinsame **HTML-Box: Höhe** steuert die Höhe dieses eingebetteten Rahmens; Standard ist 480 px.

### Serveradresse und Erreichbarkeit

Bei einer normalen Einbindung am selben Symcon-Server kann die Basisadresse leer bleiben; dann werden relative Adressen verwendet. Ist eine feste Adresse erforderlich, muss sie vom jeweiligen Client erreichbar sein, zum Beispiel:

```text
http://192.168.1.20:3777
```

Dies ist nur eine Beispieladresse. Keine Loopback-Adresse wie `localhost` verwenden, wenn die Ansicht auf einem anderen Gerät läuft. Wird die Visu über HTTPS geöffnet, muss auch der eingebettete Inhalt passend per HTTPS erreichbar sein; ein Browser kann HTTP-Inhalte in einer HTTPS-Seite blockieren.

## 6. IPSView

Für IPSView wird die HTML-Darstellung beibehalten. Bevorzugt wird das native Steuerelement **TileHtml**, das direkt mit der Modulinstanz arbeitet.

1. Im IPSView-Designer die gewünschte Seite öffnen.
2. Ein Steuerelement **TileHtml** einsetzen.
3. Als zugehörige Instanz die Instanz **Mitteilungszentrale** auswählen. Hier wird die **Instanz-ID** benötigt, nicht die ID der HTML-Stringvariable.
4. Position und äußere Größe des Steuerelements im Designer einstellen.
5. Die View speichern und im Client aktualisieren beziehungsweise neu laden.
6. In der Modulinstanz unter **Darstellung je Visualisierung → IPSView** eigene Darstellung und Farben einstellen.
7. Bei nativer SDK-Einbindung die eingebetteten Symbole aktiviert lassen. Der lokale Webserver-Port muss dem Symcon-Webserver entsprechen; Standard ist 3777.

Eine HTML-Box, die lediglich die WebFront-Stringvariable als Iframe lädt, verhält sich je nach IPSView-Client anders. Insbesondere Transparenz und Symbolabrufe sind dort nicht mit der nativen TileHtml-Einbindung gleichzusetzen.

### Weißer Hintergrund

Dieser Release bietet feste Hintergrundfarben. Eine transparente HTML-Seite kann den weißen Zeichenbereich der nativen WebView sichtbar machen, statt den IPSView-Seitenhintergrund durchzulassen. Deshalb unter den IPSView-Farben eine passende feste Farbe einstellen. Für durchgehendes Dunkelblau beispielsweise dieselbe Farbe wie im Seitenhintergrund verwenden.

### Automatische Profilwahl

Die SDK-Darstellung unterscheidet zwischen Kachelvisu und nativer IPSView-Einbettung. Falls ein Client anders einbettet und dadurch das falsche Profil erhält, kann das SDK-Profil in den erweiterten Einstellungen ausdrücklich vorgegeben werden. Diese Vorgabe gilt für die SDK-Aufrufe dieser Instanz insgesamt; bei gleichzeitiger Nutzung beider Clients zuerst die automatische Auswahl verwenden.

## 7. Direkter WebView-Aufruf

Mit aktivierter Option **Modulvariablen und Daten im Objektbaum anzeigen** wird auch die vollständige WebView-Adresse sichtbar. Sie lässt sich in einem erreichbaren Browser öffnen oder für eine passende WebView-Einbindung verwenden.

Die Adresse enthält einen Zugriffsschlüssel. Sie ist vertraulich und erlaubt die Bedienung der gesamten Liste. Eine beliebige Einbettung über Symcon Connect oder andere Proxys ist nicht für jeden Client zugesichert; die konkrete Route muss den Modul-WebHook erreichen.

## 8. Abschlussprüfung

- Eine Testmeldung erscheint in allen vorgesehenen Ansichten.
- Anpinnen setzt die Meldung nach oben.
- Eine angepinnte Meldung lässt sich erst nach dem Lösen einzeln löschen.
- „Nicht angepinnte löschen“ und die anschließende Bestätigung entfernen nur normale Meldungen.
- Eine eigene Testregel erzeugt beim Wechsel von `false` auf `true` eine neue Meldung.
- Die drei Darstellungsprofile wirken jeweils wie vorgesehen.
- Optional Durchsage und Push getrennt mit einem geeigneten Testziel prüfen.

Weitere Einstellungen und die genaue Auslöse- und Wiederholungslogik beschreibt das [Handbuch](HANDBUCH.md).

# Handbuch zur Mitteilungszentrale 1.0.0

[Übersicht](../README.md) · [Installation](INSTALLATION.md) · [Fehlerbehebung](FEHLERBEHEBUNG.md)

## 1. Grundprinzip

Eine Instanz enthält die Melderegeln, einen gemeinsamen Meldungsspeicher und die Darstellungsprofile. Die Liste wird in Kachelvisu, WebFront und IPSView angezeigt. Aktionen an derselben Instanz verändern denselben Speicher – unabhängig davon, in welcher Visu sie ausgeführt wurden.

Meldungen bleiben auch nach einem regulären Neustart gespeichert. Wartende Durchsagen und Push-Wiederholungen werden dagegen beim Neustart oder beim Übernehmen der Instanzkonfiguration verworfen.

## 2. Melderegeln

Der obere Bereich der Instanz ist für die Melderegeln vorgesehen. Die Tabelle bietet Platz für 24 sichtbare Zeilen; weitere Regeln sind über die Liste erreichbar. Jede Regel wird unabhängig geprüft.

| Einstellung | Wirkung |
| --- | --- |
| Aktiv | Aktiviert oder deaktiviert diese Regel |
| Variable | Zu überwachende Symcon-Variable |
| Bedingung | Vergleichsoperator |
| Vergleichswert | Wert, gegen den geprüft wird |
| Nachricht | Eigener Text mit optionalen Platzhaltern |
| Priorität | 1 = hoch, 2 = mittel, 3 = normal |
| Durchsage und Wiederholung | Optionale Audioausgabe mit Ziel und Wiederholungen |
| Push-Benachrichtigung | Optionaler direkter Push-Versand mit eigenem Ziel und Wiederholungen |

Die beiden optionalen Ausgabebereiche sind standardmäßig eingeklappt. Der Nachrichtentext steht in einem eigenen mehrzeiligen Bereich.

### Bedingungen und Datentypen

| Datentyp | Geeignete Vergleiche | Beispiel |
| --- | --- | --- |
| Boolean | gleich, ungleich | `true`, `false`, `1`, `0`, `ja`, `nein`, `an`, `aus` |
| Integer / Float | gleich, ungleich, kleiner, kleiner/gleich, größer, größer/gleich | Temperatur größer als `25` oder `25,5` |
| String | gleich, ungleich, enthält | Status enthält `Störung` |

Textvergleiche beachten Groß- und Kleinschreibung. Zahlen können mit Dezimalkomma eingegeben werden. Unpassende Vergleichswerte werden als Konfigurationsfehler gemeldet. Es gibt keine Verknüpfung mehrerer Variablen durch UND/ODER, keine Hysterese und keine Einschaltverzögerung.

### Wann entsteht eine Meldung?

Eine Meldung entsteht, wenn eine Variablenänderung die Regel von **nicht erfüllt** auf **erfüllt** wechselt. Beispiel für „Temperatur größer als 25“:

- 24 → 26: neue Meldung.
- 26 → 27: keine weitere Meldung.
- 27 → 23: keine Meldung, Bedingung wieder nicht erfüllt.
- 23 → 26: erneut eine Meldung.

Das Speichern einer Regel liest bestehende Zustände nicht als neue Ereignisse nach. Eine zum Zeitpunkt des Speicherns bereits erfüllte Bedingung erzeugt deshalb keine sofortige Meldung. Unveränderte Variablenwerte lösen ebenfalls keine neue Meldung aus.

### Textplatzhalter

| Platzhalter | Inhalt |
| --- | --- |
| `{parent}` | Name des direkt übergeordneten Objekts, häufig die Geräteinstanz |
| `{name}` | Name der Variable |
| `{value}` | Neuer Rohwert der Variable |
| `{old}` | Vorheriger Rohwert |
| `{id}` | Variablen-ID |

Standard:

```text
{parent} · {name}: {value}
```

Beispiel:

```text
EG | Büro links | Fenster-offen · Value: true
```

Die Werte stammen aus dem Variableninhalt, nicht aus der formatierten Profilanzeige. Ein Boolean erscheint daher als `true` oder `false`. `{parent}` gibt genau eine übergeordnete Ebene aus und keinen vollständigen Kategorienpfad.

## 3. Meldungen bedienen

**Anpinnen:** Die Meldung wird im oberen Bereich festgehalten. Sie bleibt beim Sammellöschen erhalten und wird nicht durch die Speichergrenze entfernt.

**Lösen:** Hebt das Anpinnen auf. Anschließend ist die Meldung wieder normal löschbar.

**Löschen:** Entfernt eine nicht angepinnte Meldung. Bei angepinnten Meldungen ist die Schaltfläche deaktiviert.

**Nicht angepinnte löschen:** Entfernt nach der Bestätigung „Jetzt löschen“ alle normalen Meldungen. Angepinnte bleiben erhalten.

**Langen Text lesen:** Auf den Nachrichtentext klicken beziehungsweise per Tastatur fokussieren und Enter oder Leertaste drücken. Die Karte klappt auf; erneute Betätigung klappt sie wieder zu. In schmalen Ansichten werden Texte zunächst begrenzt dargestellt.

WebHook-Ansichten aktualisieren sich regelmäßig, ungefähr alle drei Sekunden. SDK-Ansichten erhalten Aktualisierungen über die Symcon-Einbindung. Eine in einer anderen Ansicht ausgeführte Aktion kann deshalb mit kurzer Verzögerung erscheinen.

## 4. Priorität und Sortierung

| Priorität | Symbolfarbe | Position bei Standardsortierung |
| --- | --- | --- |
| 1 – hoch | Rot | Direkt nach den angepinnten Meldungen |
| 2 – mittel | Gelb | Nach Prio 1 |
| 3 – normal | Eingestellte Akzentfarbe | Nach Prio 2 |

Der Standard ist **Prio 3**. Die Priorität wird beim Erstellen der Meldung gespeichert. Eine spätere Änderung der Melderegel verändert vorhandene Meldungen nicht rückwirkend. Ältere Meldungen ohne Prioritätsfeld werden wie Prio 3 behandelt.

Oben lässt sich zwischen **Priorität** und **Datum** umschalten. Dies ist eine Sortierung der Liste, kein Ausblenden bestimmter Meldungen. Angepinnte Meldungen bleiben bei beiden Sortierungen oben und sind untereinander nach Datum absteigend geordnet. Bei Priorität folgen die Gruppen 1, 2 und 3, jeweils mit neuesten Meldungen zuerst. Bei Datum folgen alle normalen Meldungen ausschließlich nach ihrem Zeitstempel, neueste zuerst.

Die Auswahl wird, soweit der Client lokalen Browserspeicher erlaubt, je Instanz und Ansichtsmodus gespeichert. Rot und Gelb betreffen das führende Variablensymbol. Die kleinen Ausgabesymbole verwenden weiterhin die Akzentfarbe.

## 5. Speicher und maximale Anzahl

Standardmäßig werden **200 nicht angepinnte Meldungen** aufbewahrt. Der Wert ist von 1 bis 5000 einstellbar. Wird die Grenze überschritten, werden die ältesten normalen Meldungen entfernt. Die Priorität schützt dabei nicht vor der Speicherbegrenzung; dafür dient das Anpinnen.

Angepinnte Meldungen zählen nicht zu dieser Grenze. Die Gesamtzahl kann daher über der eingestellten Maximalzahl liegen. Eine separate zeitbasierte Archivierung oder ein externes Meldungsarchiv ist nicht enthalten.

Entfernt die Speicherbegrenzung eine Meldung, werden auch deren noch wartende Audio- und Push-Ausgaben verworfen.

## 6. Darstellung je Visualisierung

Im Bereich **Darstellung je Visualisierung** zuerst das zu bearbeitende Profil auswählen:

1. Gemeinsame Vorgabe
2. Kachelvisu
3. WebFront
4. IPSView

Die Profilwahl in der Konfiguration wählt nur den sichtbaren Einstellungsbereich. Sie schaltet nicht die laufende Visualisierung um.

Für jede Visu kann **Eigene Darstellung verwenden** aktiviert werden. Andernfalls gilt die gemeinsame Vorgabe für Größen, Schrift, Symbole, Überschrift und Kartenlayout. Eigene Farben haben einen getrennten Schalter; sie können unabhängig von einem eigenen Größenlayout verwendet werden. Zusätzlich gibt es eigenes CSS je Visu.

### Größen und Schrift

| Option | Bedeutung / Bereich |
| --- | --- |
| Breite | Mini, kompakt, normal, groß oder benutzerdefiniert |
| Mini | Bis 320 px; für schmale Bereiche optimiert |
| Kompakt | Bis 480 px |
| Normal | Bis 760 px |
| Groß | Bis 1100 px |
| Eigene Mindest-/Maximalbreite | Für die benutzerdefinierte Breite, 120–3000 px |
| Höhe einer Meldung | Standard 60 px; `0` = automatische Höhe |
| Nachrichtenschrift | 10–30 px; Standard 15 px |
| Metadaten-Schrift | 9–20 px; Standard 11 px |
| Symbolgröße | 24–96 px; Standard 48 px |
| Inhalt an Kartenhöhe anpassen | Skaliert Schrift, Abstände, Symbole und Bedienelemente mit |
| HTML-Box: Höhe | Äußerer WebFront-Iframe, 200–2000 px; Standard 480 px |

Die eingestellte Breite wird an den verfügbaren Platz angepasst. Bis 90 px Kartenhöhe verwendet das Layout eine kompakte Anordnung. Bei schmalen Karten bis etwa 420 px werden die Schaltflächen als Symbole dargestellt und Metadaten gekürzt. Die vollständigen Angaben sind über die Tooltips erreichbar.

Symbole werden in kompakten Karten auf eine passende Größe begrenzt, in der Miniansicht noch stärker. Bei großer Schrift kann eine sehr schmale Karte höher als der eingestellte Wert werden, damit Inhalte und Bedienelemente lesbar bleiben. Aufgeklappte Meldungen verwenden eine automatische Höhe.

Die HTML-Box-Höhe ist eine gemeinsame Rahmeneinstellung. Die äußere Höhe einer Kachel oder eines nativen IPSView-Steuerelements wird direkt in der jeweiligen Visualisierung festgelegt.

### Überschrift

Der Titel ist frei einstellbar; Standard ist **Meldungen**. Die Überschrift lässt sich ausblenden. Eintragsanzahl, Sortierung und Sammellöschen bleiben dabei bedienbar. Mit eigenem Darstellungsprofil kann jede Visu ihre eigene Überschrift erhalten.

### Variablensymbole

Bei aktivierter Symbolübernahme wird zuerst das Objekticon berücksichtigt, danach das Icon der Variablendarstellung beziehungsweise des Profils. Ist kein Symbol hinterlegt, erscheint ein Brief.

Das Symbol steht links und wird vertikal mittig ausgerichtet. Die aktuelle Symbolzuordnung kann auch für bereits vorhandene Meldungen übernommen werden. Die Farbe folgt der Priorität beziehungsweise der normalen Akzentfarbe.

In der nativen SDK-Darstellung werden moderne Symbole lokal bereitgestellt und eingebettet. Dafür gibt es in den erweiterten Einstellungen die Option zum Einbetten der Symbole und den lokalen Webserver-Port. Nicht jede ältere externe Symbolquelle ist in jeder nativen Einbettung erreichbar.

### Farben, Rahmen und Schatten

Je Profil stehen Ansichts-Hintergrund, Meldungs-Hintergrund, Schriftfarbe und Akzentfarbe zur Verfügung. Die Akzentfarbe wird unter anderem für normale Symbole und Markierungen genutzt.

Der Kartenrahmen ist über Farbe und Breite konfigurierbar. Hinzu kommen Eckenrundung und die Schattenstufen **kein**, **weich**, **mittel** und **stark**. Rahmenbreite: 0–12 px; Rundung: 0–100 px.

Transparenz ist in Version 1.0.0 nicht als reguläre Einstellung enthalten. In IPSView kann der Hintergrund der nativen WebView weiß bleiben, selbst wenn der HTML-Inhalt transparent ist. Eine feste, zum Seitenhintergrund passende Farbe ist dafür die vorgesehene Lösung.

## 7. Eigenes CSS

CSS wird zusätzlich zu den normalen Einstellungen auf die jeweilige Ansicht angewendet. Für eine nachvollziehbare Konfiguration zuerst die normalen Optionen verwenden und CSS für gezielte Ergänzungen einsetzen.

Beispiel für Abstände, Rundungen, Schatten und Prioritätsfarben:

```css
:root {
  --message-radius: 14px;
  --message-gap: 10px;
  --message-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  --priority-1-color: #ff5252;
  --priority-2-color: #ffd740;
}

article .message {
  font-weight: 600;
}
```

Ein eigener fester Seitenhintergrund kann direkt gesetzt werden:

```css
body {
  background: #071526;
}
```

Einige Farbvariablen werden aus den Instanzeinstellungen als Inline-Werte gesetzt. Ein normaler `:root`-Wert überschreibt diese nicht immer; dafür die betroffene CSS-Eigenschaft direkt setzen oder gezielt `!important` einsetzen. Ein per CSS erzwungener Höhenwert berechnet die automatische Inhaltsskalierung nicht neu. Für abgestimmte Größen daher die Kartenhöhe in der Instanz verwenden.

Ungeeignetes CSS kann Texte oder Bedienelemente verdecken. Zum Prüfen das eigene CSS vorübergehend leeren und die Änderungen übernehmen.

## 8. Durchsagen

Die Audiofunktion verwendet eine vorhandene Instanz des Symcon-Moduls **Durchsage**. Das Ausgabegerät sowie die eigentliche Audio- beziehungsweise Spracherzeugung werden dort eingerichtet.

In der Melderegel:

1. **Diese Meldung als Durchsage abspielen** aktivieren.
2. Die Durchsage-Instanz auswählen.
3. Anzahl zusätzlicher Wiederholungen und Abstand in Sekunden einstellen.
4. Bei Bedarf eine eigene Lautstärke aktivieren und einstellen.

| Einstellung | Bedeutung |
| --- | --- |
| Wiederholungen `0` | Einmalige Ausgabe |
| Wiederholungen `2` | Erste Ausgabe plus zwei weitere, also dreimal insgesamt |
| Anzahl | 0–100 zusätzliche Wiederholungen |
| Abstand | 1–86400 Sekunden zwischen Aufrufen |
| Eigene Lautstärke | 1–100 %; sonst Vorgabe der Durchsage-Instanz |

Die Ausgabe wird nach dem Erstellen der Meldung über einen Timer angestoßen. Sie erfolgt nicht synchron innerhalb der Variablenänderung. Der Wiederholungsabstand wird ab dem vorigen Aufruf berechnet und ist keine zugesicherte Pause ab dem Ende einer Audiodatei.

Eine eigene Lautstärke setzt die passende Einstellung der Durchsage-Instanz vorübergehend und stellt deren Konfiguration anschließend wieder her. Bei ungespeicherten Änderungen an dieser Instanz wird der Aufruf mit eigener Lautstärke ausgelassen. Das tatsächliche Lautstärkeverhalten des Geräts bestimmt weiterhin das Durchsage-Modul. Zeitgleiche Aufrufe anderer Automationen außerhalb der Mitteilungszentrale werden dadurch nicht koordiniert.

Fehlt das Modul, erscheint der Hinweis direkt im Durchsagebereich der Melderegel und die Einstellungen sind ausgegraut. Ist es installiert, aber keine Instanz vorhanden, erscheint ein entsprechender Einrichtungshinweis. Die visuelle Meldungsfunktion bleibt nutzbar.

Ein Lautsprechersymbol am Meldungstext kennzeichnet eine hinterlegte Durchsage. Bei Wiederholungen erscheint zusätzlich ein Wiederholungssymbol. Diese Angaben beschreiben die konfigurierte Ausgabe, nicht deren erfolgreichen Empfang.

## 9. Push-Benachrichtigungen

Push wird **direkt an die gewählte Symcon-Visualisierung** gesendet. Eine bestehende mehrstufige Benachrichtigungskette wird nicht gestartet. Das separate Benachrichtigungsmodul ist nicht erforderlich.

Unterstützte Ziele:

- Kachelvisualisierung über die Symcon-Push-Funktion.
- WebFront-Konfigurator mit registrierten Mobilgeräten.

IPSView dient hier zur Meldungsanzeige, ist jedoch kein eigener direkter Push-Empfänger dieser Funktion.

In der Melderegel:

1. **Meldung als Push an eine Visualisierung senden** aktivieren.
2. Ziel-Visualisierung auswählen.
3. Push-Titel festlegen; Standard ist **Mitteilungszentrale**.
4. Optional ein Objekt wählen, das beim Antippen geöffnet werden soll. `0` verwendet die Modulinstanz.
5. Zusätzliche Wiederholungen und Abstand einstellen.

Der Titel wird auf höchstens 32 Zeichen und der Push-Text auf höchstens 256 Zeichen begrenzt. In der Meldungsliste bleibt der vollständige Text erhalten. Das Sprungziel muss in der gewählten Visu erreichbar sein.

Push-Wiederholungen sind unabhängig von den Durchsage-Wiederholungen: `0` bedeutet einmalig, `2` bedeutet insgesamt drei Push-Nachrichten. Erlaubt sind 0–100 zusätzliche Wiederholungen und 1–86400 Sekunden Abstand.

Die Zielvisu muss für Push eingerichtet sein; Mobilgeräte müssen registriert sein und die Symcon-seitigen Voraussetzungen erfüllen. Eine auf dem Rechner geöffnete WebFront-Seite allein ist keine Garantie für eine mobile Push-Zustellung. Fehlen passende Ziele, zeigt die Melderegel einen Hinweis und deaktiviert die entsprechenden Einstellungen.

Ein Glockensymbol am Meldungstext kennzeichnet hinterlegten Push. Es ist kein Zustellnachweis.

## 10. Wiederholungen und Fehlerverhalten

Für beide Ausgabearten gilt:

- Die erste Ausgabe wird zeitnah nach dem Erstellen der Meldung eingeplant.
- Anpinnen oder Lösen beendet Wiederholungen nicht.
- Löschen, Sammellöschen oder Entfernen durch die Speichergrenze beendet noch wartende Ausgaben dieser Meldung.
- Eine Rückkehr der Variable in den normalen Zustand beendet Wiederholungen nicht automatisch.
- Änderungen der Instanzkonfiguration und Neustarts verwerfen ausstehende Ausgaben. Es gibt kein nachträgliches Abarbeiten eines Rückstaus.
- Eine bereits gestartete Durchsage wird durch Löschen nicht mitten in der Wiedergabe gestoppt. Bereits zugestellte Push-Nachrichten werden nicht zurückgerufen.
- Fehler werden protokolliert; die visuelle Meldung bleibt erhalten. Verbleibende Wiederholungen des betroffenen fehlerhaften Ausgabeauftrags werden verworfen. Es gibt keine zusätzliche automatische Zustellwiederholung bei Anbieterfehlern.

Die fehlende optionale Ausgabe darf nicht mit einer fehlerhaften Hauptregel verwechselt werden: Ungültige Variablen, Vergleichswerte oder Wiederholungsparameter müssen in der Instanzkonfiguration korrigiert werden.

## 11. Modulvariablen und Daten

Im Bereich **Modulvariablen und Daten** lässt sich die Sichtbarkeit zusätzlicher Variablen im Objektbaum einschalten. Die Werte werden auch bei ausgeblendeten Variablen gepflegt.

| Technischer Ident | Inhalt |
| --- | --- |
| `HTML` | HTML-Box für WebFront / entsprechende HTML-Einbindungen; bleibt sichtbar |
| `URL` | Vollständige WebView-Adresse mit Zugriffsschlüssel |
| `MessageCount` | Gesamtzahl der Meldungen |
| `PinnedCount` | Anzahl angepinnter Meldungen |
| `UnpinnedCount` | Anzahl normaler Meldungen |
| `MessageData` | JSON-Ausgabe der Meldungen und Zähler |
| `LastUpdate` | Unix-Zeitstempel der letzten gespeicherten Aktualisierung |

„Letzte Aktualisierung“ wird unter der Liste angezeigt und in einer eigenen Variable mit Zeitstempeldarstellung gespeichert. Reines Lesen oder regelmäßiges Abfragen der Ansicht ändert den Zeitpunkt nicht. Speichern von Meldungsänderungen und Übernehmen der Konfiguration können ihn aktualisieren.

Die JSON-Ausgabe enthält `version`, `updatedAt`, `total`, `pinned`, `unpinned` und `messages`. Meldungen enthalten unter anderem `id`, `time`, `text`, `source`, `variableID`, `pinned` und `priority`; bei entsprechenden Regeln kommen Ausgabe-Metadaten hinzu. Die Liste wird für diesen Export nach Priorität sortiert.

Diese Variablen sind Ausgaben zur Weiterverwendung. Ein manuelles Ändern des JSON-Werts bearbeitet nicht den internen Meldungsspeicher. Ausblenden ist keine Zugriffsberechtigung; insbesondere die URL enthält weiterhin ihren Zugriffsschlüssel.

## 12. Debug und Zugriff

**Debug aktivieren** schaltet zusätzliche Ausgaben für das native Symcon-Debugfenster frei. Den Debugbereich der Instanz anschließend in der Konsole öffnen. Die Diagnose enthält vor allem Ereignis- und Zielinformationen; die Option sollte nur während einer Fehlersuche aktiv sein. Warnungen zu fehlgeschlagenen Ausgaben können unabhängig davon im Symcon-Protokoll erscheinen.

Die WebView-Adresse enthält einen Zugriffsschlüssel. Dieser berechtigt zum Lesen, Anpinnen und Löschen der gesamten Instanzliste. Es gibt darüber keine Trennung nach Benutzer oder Meldungsquelle.

Bei Bedarf den **Zugriffsschlüssel erneuern**. Dadurch werden bisherige direkte Adressen ungültig. Die vom Modul erzeugte HTML-Box wird aktualisiert; außerhalb gespeicherte Adressen müssen ersetzt werden. Die SDK-Einbindung benötigt diesen WebHook-Schlüssel nicht.

Für externe Zugriffe HTTPS oder einen geeigneten geschützten Zugang verwenden. Keine privaten Adressen, Zugriffsschlüssel, vollständigen Symcon-Sicherungen oder personenbezogenen Meldungsinhalte in öffentliche Fehlerberichte aufnehmen.

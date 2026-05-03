# `block_coursectrl_dates` – Lastenheft, Pflichtenheft und Blueprint

## 1. Kurzbeschreibung

Das Block-Plugin `block_coursectrl_dates` zeigt Lehrenden direkt auf der Kursseite eine kompakte Kalender- und Terminübersicht für die nächsten Wochen. Aus dieser Übersicht heraus können Terminverschiebungen über bestehende Funktionen des Local-Plugins `local_coursectrl` angestoßen werden.

Der Block selbst führt keine Terminänderungen aus. Er dient als kursnahes Frontend für Sichtbarkeit, Orientierung und den Einstieg in die Terminbearbeitung.

---

## 2. Zielsetzung

Lehrende sollen auf der Kursseite schnell erkennen:

- welche Termine in den nächsten Wochen relevant sind,
- welche Aktivitäten oder Kursereignisse anstehen,
- ob eine Terminverschiebung sinnvoll ist,
- und wie sie direkt in die entsprechende Bearbeitung im `local_coursectrl` wechseln können.

Zusätzlich soll der Block bei bestimmten Neuanfangs-Ereignissen einen Splash-Screen anzeigen und auf die Timeline-Funktion des Local-Plugins verweisen.

---

# Teil A: Lastenheft

## 3. Fachliche Anforderungen

## A-1: Kalender- und Terminanzeige im Block

### A-1.1 Kalenderdarstellung

Der Block zeigt optional eine Kalenderdarstellung für die nächsten Wochen an.

Standardumfang:

- Anzeige der nächsten 4 Wochen
- konfigurierbar zwischen 1 und 6 Wochen
- Kalenderanzeige kann in den Block-Settings ein- oder ausgeschaltet werden

Die konkrete Darstellungsart wird nicht im Block neu definiert, sondern aus dem `local_coursectrl` bezogen.

Dazu gehören insbesondere:

- Kalenderlayout
- Hervorhebung von Terminen
- Feiertage
- Ferienangaben
- sonstige kalenderbezogene Darstellungsoptionen

### A-1.2 Terminliste unterhalb der Kalenderdarstellung

Unterhalb der Kalenderdarstellung zeigt der Block eine Liste der nächsten Termine.

Jeder Termin enthält mindestens:

- Tag
- Uhrzeit
- Aktivität
- Ereignis

Beispiele für Ereignisse:

- Aufgabe öffnet
- Aufgabe schließt
- Abgabefrist
- Quiz öffnet
- Quiz schließt
- Aktivität wird verfügbar
- Aktivität endet

### A-1.3 Sortierung der Termine

Die Termine werden chronologisch sortiert:

1. Tag
2. Uhrzeit
3. Aktivität
4. Ereignis

### A-1.4 Konfiguration der Terminliste

In den Block-Settings kann festgelegt werden, wie viele Termine angezeigt werden oder welcher Zeitraum berücksichtigt wird.

Mögliche Einstellungen:

- Anzahl der Termine
- Zeitintervall in Wochen
- Zeitintervall zwischen 1 und 6 Wochen

Es soll mindestens eine dieser beiden Varianten unterstützt werden:

- feste Anzahl der nächsten Termine
- alle Termine innerhalb eines konfigurierten Zeitraums

### A-1.5 Verschiebe-Buttons

Neben oder unter den Terminen werden Verschiebe-Buttons angezeigt, wie sie bereits aus dem Local-Plugin bekannt sind.

Der Block führt die Verschiebung nicht selbst aus. Die Buttons dienen als Einstieg in das `local_coursectrl`.

---

## A-2: Terminverschiebung über das Local-Plugin

### A-2.1 Button-Verhalten

Ein Klick auf einen Verschiebe-Button öffnet das Local-Plugin.

Die eigentliche Terminverschiebung erfolgt dort.

Der Block übergibt dabei den notwendigen Kontext, zum Beispiel:

- Kurs-ID
- Aktivitäts-ID
- Ereignistyp
- Ausgangstermin
- gewünschte Verschiebeaktion, sofern bereits bestimmt

### A-2.2 Ziel im Local-Plugin

Die Terminverschiebung wird im `local_coursectrl` durchgeführt.

Der Block verweist auf den passenden Workflow oder die passende Zielseite im Local-Plugin.

Für allgemeine Terminprüfung und Timeline-Verwendung ist `timeline.php` die zentrale Zielseite.

### A-2.3 Keine direkte Änderung im Block

Der Block darf keine Terminverschiebung unmittelbar durchführen.

Alle schreibenden Aktionen laufen über das Local-Plugin, damit dort weiterhin gelten:

- Vorschau
- Prüfung
- Bestätigung
- Änderungsausführung
- Protokollierung
- ggf. Rollback

---

## A-3: Splash-Screen bei Neuanfangs-Ereignissen

### A-3.1 Zweck

Der Splash-Screen erscheint im Block, wenn ein Kurs oder relevante Kursteile neu begonnen oder neu eingerichtet wurden.

Er soll Lehrende darauf hinweisen, dass Termine geprüft werden können.

### A-3.2 Auslösende Ereignisse

Der Splash-Screen kann angezeigt werden bei:

- Kurs neu angelegt
- Kurs zurückgesetzt
- Kursteile mit Zeitbegrenzungen importiert

### A-3.3 Inhalt des Splash-Screens

Der Splash-Screen enthält eine kurze Information und einen Verweis in das Local-Plugin.

Beispiel:

```text
Dieser Kurs wurde neu angelegt, zurückgesetzt oder es wurden Kursteile mit Zeitbegrenzungen importiert.

Möchten Sie die Kurstermine prüfen?

[Timeline öffnen]
```

Der Button verweist auf:

```text
/local/coursectrl/timeline.php
```

mit passender Kurs-ID.

### A-3.4 Konfiguration

Der Splash-Screen kann in den Block-Settings:

- aktiviert werden
- deaktiviert werden
- manuell erneut aufgerufen werden

---

# Teil B: Pflichtenheft

## 4. Systemverhalten

## 4.1 Block-Anzeige

Das Block-Plugin rendert im Kurskontext eine kompakte Terminansicht.

Die Anzeige besteht aus maximal drei Bereichen:

1. optionaler Splash-Screen
2. optionale Kalenderdarstellung
3. Terminliste mit Verschiebe-Buttons

Wenn der Splash-Screen aktiv ist, kann er oberhalb der Kalender- und Terminanzeige dargestellt werden.

---

## 4.2 Datenbezug

Der Block bezieht seine Daten aus `local_coursectrl`.

Insbesondere:

- Termine
- Terminarten
- Kalenderdarstellung
- Ferien- und Feiertagsinformationen
- Darstellungsoptionen
- URLs zu Workflows im Local-Plugin

Der Block soll keine eigene parallele Terminlogik entwickeln.

---

## 4.3 Kalenderbereich

Der Kalenderbereich wird nur angezeigt, wenn er in den Block-Settings aktiviert ist.

Parameter:

| Einstellung | Wertebereich | Standard |
|---|---:|---:|
| Kalender anzeigen | ja/nein | ja |
| Anzahl Wochen | 1–6 | 4 |

Der Kalender zeigt den Zeitraum ab dem aktuellen Datum.

Beispiel:

```text
Heute + 4 Wochen
```

---

## 4.4 Terminliste

Die Terminliste zeigt die nächsten relevanten Kurstermine.

Pflichtfelder je Termin:

| Feld | Beschreibung |
|---|---|
| Tag | Datum des Termins |
| Uhrzeit | Uhrzeit des Termins |
| Aktivität | Name der betroffenen Aktivität |
| Ereignis | Art des Ereignisses |
| Aktion | Verschiebe-Button oder Link |

Die Terminliste wird chronologisch sortiert.

---

## 4.5 Terminlisten-Konfiguration

In den Block-Settings kann konfiguriert werden, wie viele Termine angezeigt werden.

Empfohlene Einstellungen:

| Einstellung | Wertebereich | Standard |
|---|---:|---:|
| Anzeige nach | Anzahl / Zeitraum | Zeitraum |
| Anzahl Termine | 1–20 | 10 |
| Zeitraum in Wochen | 1–6 | 4 |

Wenn `Anzeige nach = Zeitraum` gewählt ist, werden alle Termine im gewählten Zeitraum angezeigt.

Wenn `Anzeige nach = Anzahl` gewählt ist, werden die nächsten n Termine angezeigt.

---

## 4.6 Verschiebe-Buttons

Verschiebe-Buttons werden analog zum Local-Plugin dargestellt.

Ein Klick auf den Button führt in das Local-Plugin.

Der Ziel-Link enthält mindestens:

- Kurs-ID
- optional Aktivitäts-ID
- optional Ereignis-ID oder Ereignistyp
- optional Rücksprung-URL zur Kursseite

Beispielhafte Ziel-URL:

```text
/local/coursectrl/timeline.php?id={courseid}&action=shift&cmid={cmid}&event={eventtype}&returnurl={returnurl}
```

Die genaue URL-Struktur wird vom Local-Plugin definiert.

---

## 4.7 Splash-Screen

Der Splash-Screen wird angezeigt, wenn ein Neuanfangs-Ereignis erkannt wurde und die Funktion in den Block-Settings aktiv ist.

Auslösende Ereignisse:

- Kurs wurde neu angelegt
- Kurs wurde zurückgesetzt
- Kursteile mit Zeitbegrenzungen wurden importiert

Der Splash-Screen enthält:

- kurze Erklärung
- Button zur Timeline im Local-Plugin
- Option zum Schließen

Beispiel:

```text
Kurstermine prüfen

Dieser Kurs wurde neu angelegt, zurückgesetzt oder enthält neu importierte zeitbegrenzte Kursteile.

[Timeline öffnen] [Ausblenden]
```

---

## 4.8 Splash-Screen-Einstellungen

In den Block-Settings stehen folgende Optionen zur Verfügung:

| Einstellung | Wertebereich | Standard |
|---|---:|---:|
| Splash-Screen aktivieren | ja/nein | ja |
| Splash-Screen erneut anzeigen | Aktion/Button | — |
| Splash nach Schließen erneut anzeigen | nie / bei neuem Ereignis | bei neuem Ereignis |

---

## 4.9 Rechte

Der Block berücksichtigt Moodle-Rollen und Capabilities.

Empfohlene Capabilities:

| Capability | Zweck |
|---|---|
| `block/coursectrl_dates:view` | Block anzeigen |
| `block/coursectrl_dates:configure` | Block konfigurieren |
| `local/coursectrl:viewtimeline` | Timeline im Local-Plugin öffnen |
| `local/coursectrl:managedates` | Terminworkflow verwenden |

Nur Nutzerinnen und Nutzer mit passenden Rechten sehen Verschiebe-Buttons.

Nutzerinnen und Nutzer ohne Bearbeitungsrechte können ggf. nur die Terminübersicht sehen oder den Block gar nicht sehen.

---

## 4.10 Nicht-Ziele

Das Block-Plugin soll ausdrücklich nicht:

- Termine selbst verändern,
- eine eigene vollständige Timeline implementieren,
- eine eigene Ferien- oder Feiertagslogik pflegen,
- Textprüfung selbst durchführen,
- Rollback-Funktionen anbieten,
- komplexe Massenbearbeitung im Block darstellen.

Diese Funktionen bleiben im `local_coursectrl`.

---

# Teil C: Blueprint

## 5. Plugin-Zuschnitt

## 5.1 Pluginname

Empfohlener technischer Name:

```text
block_coursectrldates
```

Empfehlung: `block_coursectrldates`, da die Zugehörigkeit zu `local_coursectrl` klar sichtbar bleibt.

---

## 5.2 Abhängigkeit

Das Block-Plugin hängt vom Local-Plugin ab.

```php
$plugin->dependencies = [
    'local_coursectrl' => ANY_VERSION,
];
```

Die konkrete Versionsnummer sollte später auf die erste Local-Plugin-Version gesetzt werden, die die benötigten Termin- und URL-Services bereitstellt.

---

## 5.3 Grobe Dateistruktur

```text
block_coursectrl_dates/
├── block_coursectrl_dates.php
├── version.php
├── edit_form.php
├── db/
│   └── access.php
├── lang/
│   ├── de/block_coursectrl_dates.php
│   └── en/block_coursectrl_dates.php
├── classes/
│   ├── output/
│   │   ├── block_renderer.php
│   │   ├── calendar_view.php
│   │   ├── event_list.php
│   │   └── splash_view.php
│   └── local/
│       ├── config_reader.php
│       ├── event_provider.php
│       └── splash_state.php
└── templates/
    ├── block.mustache
    ├── calendar.mustache
    ├── event_list.mustache
    ├── event_item.mustache
    └── splash.mustache
```

---

## 5.4 Block-Rendering

Das Rendering folgt diesem Ablauf:

```text
1. Kurskontext prüfen
2. Rechte prüfen
3. Blockkonfiguration lesen
4. Splash-Status prüfen
5. Termine aus local_coursectrl beziehen
6. Kalenderdaten aus local_coursectrl beziehen
7. Ausgabe rendern
```

---

## 5.5 Datenfluss

```text
Moodle-Kursseite
    ↓
block_coursectrl_dates
    ↓
local_coursectrl Termin-/Kalender-Service
    ↓
Termindaten + Kalenderdarstellung + Ziel-URLs
    ↓
Block-Ausgabe
    ↓
Klick auf Verschiebe-Button
    ↓
local_coursectrl/timeline.php
```

---

## 5.6 Moodle-typisches Wireframe

### Standardansicht

```text
┌────────────────────────────────────┐
│ Kurstermine                         │
├────────────────────────────────────┤
│ Nächste 4 Wochen                    │
│                                    │
│ Mo  Di  Mi  Do  Fr  Sa  So         │
│ 01  02  03  04  05  06  07         │
│     ●       ●                       │
│ 08  09  10  11  12  13  14         │
│         ●           ●               │
│                                    │
│ Nächste Termine                     │
│                                    │
│ Mo, 04.11., 10:00                  │
│ Aufgabe 1 – öffnet                  │
│ [Verschieben]                       │
│                                    │
│ Fr, 08.11., 23:59                  │
│ Quiz 2 – schließt                   │
│ [Verschieben]                       │
│                                    │
│ [Timeline öffnen]                   │
└────────────────────────────────────┘
```

### Ansicht ohne Kalender

```text
┌────────────────────────────────────┐
│ Kurstermine                         │
├────────────────────────────────────┤
│ Nächste Termine                     │
│                                    │
│ Mo, 04.11., 10:00                  │
│ Aufgabe 1 – öffnet                  │
│ [Verschieben]                       │
│                                    │
│ Fr, 08.11., 23:59                  │
│ Quiz 2 – schließt                   │
│ [Verschieben]                       │
│                                    │
│ [Timeline öffnen]                   │
└────────────────────────────────────┘
```

### Splash-Ansicht

```text
┌────────────────────────────────────┐
│ Kurstermine                         │
├────────────────────────────────────┤
│ Kurstermine prüfen                  │
│                                    │
│ Dieser Kurs wurde neu angelegt,     │
│ zurückgesetzt oder enthält neu      │
│ importierte zeitbegrenzte Inhalte.  │
│                                    │
│ [Timeline öffnen] [Ausblenden]      │
└────────────────────────────────────┘
```

---

## 5.7 Block-Settings Wireframe

```text
Block konfigurieren: Kurstermine

Allgemein
--------------------------------
Blocktitel
[ Kurstermine ]

Kalender
--------------------------------
[x] Kalender anzeigen
Anzahl Wochen
[ 4 v ]  Werte: 1–6

Terminliste
--------------------------------
Anzeige nach
[ Zeitraum v ]  Werte: Zeitraum / Anzahl

Zeitraum in Wochen
[ 4 v ]  Werte: 1–6

Anzahl Termine
[ 10 ]

Splash-Screen
--------------------------------
[x] Splash-Screen bei Neuanfangs-Ereignissen anzeigen

[ Splash-Screen beim nächsten Kursaufruf erneut anzeigen ]
```

---

## 5.8 Zustände des Blocks

| Zustand | Beschreibung |
|---|---|
| Standard | Kalender und Terminliste werden angezeigt |
| Ohne Kalender | Nur Terminliste wird angezeigt |
| Keine Termine | Hinweis, dass keine Termine im Zeitraum gefunden wurden |
| Splash aktiv | Hinweis auf Neuanfangs-Ereignis wird angezeigt |
| Keine Rechte | Block wird nicht angezeigt oder zeigt nur reduzierte Informationen |
| Local-Plugin fehlt | Hinweis für Administrator:innen oder Block nicht verfügbar |

---

## 5.9 Beispiel: Keine Termine

```text
┌────────────────────────────────────┐
│ Kurstermine                         │
├────────────────────────────────────┤
│ In den nächsten 4 Wochen wurden     │
│ keine Kurstermine gefunden.         │
│                                    │
│ [Timeline öffnen]                   │
└────────────────────────────────────┘
```

---

## 5.10 Beispiel: Mehr Termine als sichtbar

```text
┌────────────────────────────────────┐
│ Kurstermine                         │
├────────────────────────────────────┤
│ Nächste Termine                     │
│                                    │
│ 10 von 18 Terminen werden angezeigt │
│                                    │
│ ...                                │
│                                    │
│ [Alle in der Timeline anzeigen]     │
└────────────────────────────────────┘
```

---

## 6. Offene technische Punkte

Vor der Implementierung müssen folgende Punkte im `local_coursectrl` konkretisiert werden:

1. Welche Service-Funktion liefert die Termine für einen Kurs?
2. Welche Service-Funktion liefert die Kalenderdarstellung oder Kalenderdaten?
3. Wie werden Ferien- und Feiertagsangaben im Local-Plugin modelliert?
4. Welche URL-Parameter erwartet `timeline.php` für Verschiebeaktionen?
5. Wie werden importierte Kursteile mit Zeitbegrenzungen erkannt?
6. Wie wird der Splash-Zustand pro Kurs und Blockinstanz gespeichert?
7. Welche Capabilities aus `local_coursectrl` werden für Verschiebe-Buttons benötigt?

---

## 7. MVP-Abgrenzung

## MVP enthalten

- Block zeigt Terminliste
- optionaler Kalenderbereich
- konfigurierbare Wochenzahl 1–6
- konfigurierbare Terminanzahl oder Zeitraum
- Verschiebe-Buttons verlinken ins Local-Plugin
- Splash-Screen bei aktivierter Option
- manueller erneuter Splash-Aufruf über Block-Settings
- Verweis auf `timeline.php`

## MVP nicht enthalten

- direkte Terminverschiebung im Block
- eigene Kalenderlogik im Block
- eigene Feiertags-/Ferienlogik im Block
- Textprüfung im Block
- automatische Änderung von Textdaten
- komplexe Neuanfangs-Heuristik
- Lernstands- oder Sackgassenanalyse
- Constructive-Alignment-Prüfung

---

## 8. Kurzfassung

`block_coursectrl_dates` ist ein schlanker Kursblock für Termine.

Er zeigt:

- eine Kalenderdarstellung für die nächsten 1–6 Wochen,
- darunter die nächsten Termine nach Tag, Uhrzeit, Aktivität und Ereignis,
- Verschiebe-Buttons mit Weiterleitung ins `local_coursectrl`,
- und bei Neuanfangs-Ereignissen einen Splash-Screen mit Verweis auf `timeline.php`.

Die gesamte fachliche Terminbearbeitung bleibt im Local-Plugin.

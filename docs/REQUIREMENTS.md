# Requirements

What the application must **do**. Every requirement here is functional: a rule a user, a document
or another system depends on. How it is stored, rendered or delivered is not part of the contract
unless a requirement says so.

| | |
| --- | --- |
| Product | Vivalaraza — Tabellen und Strafen der internen kicker-Manager-Liga |
| Requirements | 49 |
| Last reviewed | 2026-09-25 (Mitspieler ändern PLY-02, Neuigkeiten NEWS-01–03, Verlauf LOG-01–03) |

Decisions and open questions live in [`DECISIONS.md`](DECISIONS.md); the domain vocabulary in
[`GLOSSARY.md`](GLOSSARY.md).

---

## How to read this

**These requirements are the contract.** Where a value, a label or an ordering appears below, it
was stated by the product owner. If a rule looks arbitrary, it may be — ask before improving it.

**Quoted UI text is part of the requirement.** Labels, statuses, messages and document strings in
*„quotes“* are verbatim in the language they are given in, and become that locale's translation.

**Ids are stable.** `ABC-01`: a 2–4 letter area prefix and a two-digit number. Ids are never
renumbered and never reused; a retired requirement stays in the ledger as `wont-do` with a reason.
Quote them in branch names, commit subjects, test descriptions and code comments.

## Format

The tooling (`php artisan spec:index`, `spec:coverage`) parses this file line by line. An area is a
numbered level-2 heading with its prefix in backticks; a requirement is a bullet starting with its
bold id and an em dash; continuation lines are indented two spaces:

    ## 3. Invoices (`INV`)

    *One sentence on what the area is for.*

    ### Constants

    | Constant | Value |
    | --- | --- |
    | Payment term | 14 days |

    - **INV-01** — Invoice numbers follow `RE-YYYY-NNNN`, **gapless per calendar year**,
      zero-padded to four digits.
    - **INV-02** — A finalised invoice is immutable; a correction is a cancellation plus a new
      invoice. The cancelled number stays taken.

(The example above is indented so that the parser ignores it.)

## Table of contents

1. Zugriff (`ACC`)
2. Mitspieler (`PLY`)
3. Saisons (`SEA`)
4. Spieltage (`MD`)
5. Strafen (`PEN`)
6. Gesamttabelle (`STD`)
7. Statistiken (`STAT`)
8. Besucher (`VIS`)
9. Neuigkeiten (`NEWS`)
10. Verlauf (`LOG`)

---

## 1. Zugriff (`ACC`)

*Wer was sehen und wer was eintragen darf.*

- **ACC-01** — Die Ergebnisse jeder Saison (Spieltage, Gesamttabelle, Strafen) sind **ohne
  Anmeldung** lesbar. Die öffentliche Seite zeigt die Gesamttabelle mit Punkte- und
  Strafensumme je Mitspieler, die Ergebnisse je Spieltag (Punkte, Platz, Strafe) und eine
  Auswahl früherer Saisons; vorausgewählt ist die zuletzt angelegte Saison.
- **ACC-02** — Punkte eintragen und ändern dürfen nur angemeldete Benutzer.
- **ACC-03** — Mitspieler und Saisons anlegen sowie die Mitspieler einer Saison festlegen dürfen
  nur Admins. Mitspieler werden angelegt und bearbeitet (PLY-02), nicht gelöscht. Eine Saison wird
  nicht bearbeitet – ausgenommen die Zwischenabrechnung (PEN-04) und die Strafenstaffel
  (SEA-05) –; ein Admin kann sie löschen und wiederherstellen (SEA-06).
- **ACC-04** — Ein Mitspieler braucht kein Benutzerkonto; Mitspieler und Benutzer sind getrennt.

## 2. Mitspieler (`PLY`)

*Die Teilnehmer der internen Liga im kicker Manager.*

- **PLY-01** — Ein Mitspieler hat einen Namen und seinen Alias aus dem kicker Manager.
- **PLY-02** — Ein Admin kann Name und Alias eines Mitspielers ändern. Die Änderung gilt überall,
  auch in früheren Saisons; Punkte, Platzierungen und Strafen bleiben unberührt. Ein Mitspieler
  wird nicht gelöscht.

## 3. Saisons (`SEA`)

*Eine Bundesliga-Saison der internen Liga.*

### Constants

| Constant | Value |
| --- | --- |
| Spieltage je Saison | 34 (Bundesliga) |

- **SEA-01** — Ein Admin legt eine Saison an und legt dabei ihre Bezeichnung (z. B. „2025/26“)
  sowie Startbetrag und Schrittweite der Strafenstaffel fest (PEN-01). Eine Saison hat keine
  weiteren Angaben. Startbetrag und Schrittweite lassen sich später ändern (SEA-05).
- **SEA-02** — Ein Admin legt fest, welche Mitspieler an einer Saison teilnehmen.
- **SEA-03** — Die Mitspieler einer Saison ändern sich während der Saison nicht: Die
  Mitspielerliste ist ab dem ersten eingetragenen Punkt gesperrt.
- **SEA-04** — Eine Saison hat 34 Spieltage.
- **SEA-05** — Startbetrag und Schrittweite einer Saison darf jeder angemeldete Benutzer jederzeit
  ändern. Die Strafen aller Spieltage der Saison gelten danach mit den neuen Werten.
- **SEA-06** — Ein Admin kann eine Saison nach einer Sicherheitsabfrage löschen. Eine gelöschte
  Saison verschwindet aus allen Ansichten und Statistiken; ihre Punkte bleiben erhalten, und ein
  Admin kann sie wiederherstellen.

## 4. Spieltage (`MD`)

*Die Punkte eines Bundesliga-Spieltags aus dem kicker Manager, von Hand eingetragen oder aus der
kopierten Liga-Seite übernommen.*

- **MD-01** — Ein Benutzer trägt je Spieltag für jeden Mitspieler der Saison dessen Punkte aus dem
  kicker Manager ein. Es werden immer für alle Mitspieler Punkte eingetragen. Punkte sind ganze
  Zahlen; negative Punkte sind erlaubt.
- **MD-02** — Ein Spieltag ist abgeschlossen, wenn für alle Mitspieler der Saison Punkte
  eingetragen sind. Erst dann zeigt die App Platzierungen und Strafen des Spieltags.
- **MD-03** — Die Platzierung am Spieltag berechnet die App aus den Punkten: mehr Punkte ergeben
  einen besseren Platz, gleiche Punktzahl ergibt den gleichen Platz. Plätze werden dicht gezählt:
  1, 2, 3, 3, 4.
- **MD-04** — Benutzer dürfen eingetragene Punkte ändern; Platzierung, Strafen und Gesamttabelle
  folgen der Änderung.
- **MD-05** — Ergänzend zur Eingabe von Hand (MD-01) kann ein Benutzer die Seite seiner Liga aus
  dem kicker Manager kopieren und einfügen. Die App liest daraus die Spieltagswertung, ordnet die
  Punkte über den Alias (PLY-01) den Mitspielern zu und trägt sie in die Eingabefelder ein;
  gespeichert wird erst mit „Punkte speichern“. Mitspieler ohne Punkte im Text und Namen im Text
  ohne passenden Mitspieler nennt die App; deren Felder bleiben unverändert.

## 5. Strafen (`PEN`)

*Nach jedem Spieltag zahlen die schwächeren Plätze in die Kasse. Ob eine Strafe bezahlt ist,
erfasst die App nicht.*

- **PEN-01** — Die Strafe je Mitspieler und abgeschlossenem Spieltag ergibt sich aus den
  **Plätzen, nicht aus den Mitspielern**: Die niedrigste Punktzahl des Spieltags zahlt den
  Startbetrag der Saison, jede nächsthöhere Punktzahl die Schrittweite weniger. Mitspieler mit
  gleicher Punktzahl zahlen denselben Betrag. Kein Betrag fällt unter 0 €.
- **PEN-02** — Beispiel mit Startbetrag 4,50 € und Schrittweite 0,50 € (vom Product Owner
  bestätigt):

  | Punkte | Platz von unten | Strafe |
  | --- | --- | --- |
  | 40 | 1. | 4,50 € |
  | 40 | 1. | 4,50 € |
  | 55 | 2. | 4,00 € |
  | 60 | 3. | 3,50 € |
  | 60 | 3. | 3,50 € |
  | 72 | 4. | 3,00 € |
  | 80 | 5. | 2,50 € |

- **PEN-03** — Die App zeigt je Mitspieler die Summe seiner Strafen in der Saison.
- **PEN-04** — Einmal je Saison wird die Strafenkasse zwischenabgerechnet. Nach welchem Spieltag
  (1–33), legt ein Admin beim Anlegen der Saison fest; er kann ihn später ändern oder entfernen
  (ACC-03). Ist er festgelegt,
  zeigt die Gesamttabelle je Mitspieler die Strafen der „Hinrunde“ (bis einschließlich diesem
  Spieltag), der „Rückrunde“ (danach) und die Summe der Saison. Punkte und Platzierung bleiben
  davon unberührt.

## 6. Gesamttabelle (`STD`)

*Die Rangliste einer Saison, gebildet wie im kicker Manager.*

- **STD-01** — Die Gesamttabelle ordnet die Mitspieler einer Saison nach der Summe ihrer Punkte,
  die meisten Punkte zuerst. Es zählen nur abgeschlossene Spieltage (MD-02). Gleiche Punktesumme
  ergibt den gleichen Platz; innerhalb eines Platzes steht der Mitspieler mit der geringeren
  Strafensumme zuerst.

## 7. Statistiken (`STAT`)

*Öffentliche Auswertungen je Mitspieler und für die ganze Liga.*

- **STAT-01** — Jeder Mitspieler hat eine eigene Seite, ohne Anmeldung lesbar; sein Name in
  Gesamttabelle und Spieltag führt dorthin. Sie zeigt eine Saison, wählbar unter den Saisons, an
  denen er teilnimmt; vorausgewählt ist die zuletzt angelegte davon.
- **STAT-02** — Alle Statistiken zählen nur abgeschlossene Spieltage (MD-02). Durchschnitte
  werden auf eine Nachkommastelle gerundet angezeigt.
- **STAT-03** — Kennzahlen je Saison: Gesamtpunkte, Ø Punkte je Spieltag, bester und
  schlechtester Spieltag (Punkte und Spieltag), aktueller Platz in der Gesamttabelle und die
  Strafen (Hinrunde, Rückrunde und Gesamt, wie PEN-04).
- **STAT-04** — Ein Graph zeigt die Punkte je Spieltag und als Vergleichslinie den
  Liga-Durchschnitt des Spieltags (Ø Punkte aller Mitspieler der Saison).
- **STAT-05** — Ein Graph zeigt den Platzierungsverlauf: den Platz in der Gesamttabelle nach
  jedem Spieltag.
- **STAT-06** — Ein Graph zeigt die Strafen je Spieltag und kumuliert über die Saison; der
  Spieltag der Zwischenabrechnung (PEN-04) ist markiert.
- **STAT-07** — Erfolge und Serien je Saison: Spieltagssiege (Platz 1), „Rote Laterne“ (letzter
  Platz) – bei Gleichstand zählt beides für jeden –, strafenfreie Spieltage, die längste Serie
  aufeinanderfolgender strafenfreier Spieltage und die Formkurve: der Tagesplatz der letzten fünf
  Spieltage, bewertet als gut, mittel oder schlecht nach oberem, mittlerem oder unterem Drittel der
  Plätze dieses Spieltags.
- **STAT-08** — Die ewige Bilanz eines Mitspielers über alle Saisons: gespielte Saisons, Ø Platz
  in der Gesamttabelle (je Saison der aktuelle bzw. letzte Platz), Punkte und Strafen insgesamt und
  Spieltagssiege insgesamt.
- **STAT-09** — Direktvergleich zweier Mitspieler einer Saison: beide Punkte-Verläufe
  übereinander und an wie vielen Spieltagen der eine mehr, gleich viele oder weniger Punkte hatte
  als der andere.
- **STAT-10** — Liga-Rekorde, über alle Saisons oder für eine gewählte Saison: höchste und
  niedrigste Punktzahl an einem Spieltag, meiste Spieltagssiege in einer Saison, höchste
  Strafensumme in einer Saison und der knappste Spieltag (kleinster Abstand zwischen höchster und
  niedrigster Punktzahl) – jeweils mit Mitspieler, Saison und Spieltag; bei Gleichstand alle.
- **STAT-11** — Spieltags-Highlights: zu jedem Spieltag der Tagessieger, die „Rote Laterne“ und
  der Liga-Durchschnitt.
- **STAT-12** — Die Strafenkasse einer Saison: die Summe aller Strafen (Hinrunde, Rückrunde,
  Gesamt) und die Mitspieler nach eingezahlter Summe, die höchste zuerst.
- **STAT-13** — Zu jedem abgeschlossenen Spieltag zeigt die App, wie viel Geld in die Kasse ging
  (die Summe der Strafen dieses Spieltags).
- **STAT-14** — Die Strafenkasse zeigt als Graph das Geld je Spieltag und den Kassenstand
  kumuliert über die Saison; der Spieltag der Zwischenabrechnung (PEN-04) ist markiert.
- **STAT-15** — Ein weiterer Liga-Rekord (wie STAT-10): der teuerste Spieltag, an dem das meiste
  Geld in die Kasse ging.

## 8. Besucher (`VIS`)

*Wann die öffentlichen Seiten aufgerufen werden – nur für Admins.*

- **VIS-01** — Die App zählt jeden Aufruf einer öffentlichen Seite: „Saisonansicht“ (`/` und jede
  Saison), „Mitspieler“ (jede Mitspielerseite), „Direktvergleich“ und „Liga-Rekorde“. Es zählen
  nur Besucher ohne Anmeldung; Bots und Crawler zählen nicht.
- **VIS-02** — Gespeichert werden je Aufruf nur die Seite, der Zeitpunkt und ein Kennzeichen, das
  Besucher desselben Tages unterscheidet und täglich wechselt. IP-Adresse und Browserkennung
  werden nicht gespeichert; es wird kein Cookie gesetzt.
- **VIS-03** — Aufrufe, die älter als 12 Monate sind, werden gelöscht.
- **VIS-04** — Die Besucherstatistik sehen nur Admins, unter „Besucher“ in der Navigation. Sie
  zeigt einen wählbaren Zeitraum – die letzten 7, 30, 90 oder 365 Tage, heute eingeschlossen;
  vorausgewählt sind 30 Tage. Alle Zeiten gelten in deutscher Zeit (Europe/Berlin).
- **VIS-05** — Für den Zeitraum zeigt sie die Summe der Aufrufe und der Besucher sowie einen
  Graphen der Aufrufe und Besucher je Tag. Besucher werden je Tag gezählt; die Summe des Zeitraums
  ist die Summe der Tage.
- **VIS-06** — Sie zeigt die Aufrufe des Zeitraums nach Wochentag (Montag bis Sonntag) und Stunde
  (0–23) als Heatmap und als Balken je Stunde, sowie je Seite (VIS-01), die meisten zuerst.

## 9. Neuigkeiten (`NEWS`)

*Mitteilungen an die Liga zu einer Saison, zum Beispiel über Termine oder Ereignisse.*

- **NEWS-01** — Jeder angemeldete Benutzer kann zu einer Saison Neuigkeiten schreiben: Text ohne
  Formatierung; Zeilenumbrüche bleiben erhalten, Links sind anklickbar.
- **NEWS-02** — Die Neuigkeiten stehen ohne Anmeldung lesbar in der Saisonansicht über der
  Gesamttabelle, die neueste zuerst, jede mit Datum und dem Namen des Verfassers. Die drei
  neuesten sind sichtbar, ältere hinter „Alle anzeigen“.
- **NEWS-03** — Eine Neuigkeit ändern oder löschen dürfen ihr Verfasser und jeder Admin.

## 10. Verlauf (`LOG`)

*Wer wann was an den Daten der Liga geändert hat – nur für Admins.*

- **LOG-01** — Die App hält jede Änderung an den Daten der Liga fest: Punkte eintragen und ändern
  (MD-01, MD-04), Mitspieler anlegen und ändern (PLY-02), Saison anlegen, löschen und
  wiederherstellen (SEA-01, SEA-06), die Mitspieler einer Saison (SEA-02), Startbetrag und
  Schrittweite (SEA-05), die Zwischenabrechnung (PEN-04) sowie Neuigkeiten schreiben, ändern und
  löschen (NEWS-01, NEWS-03). Benutzerkonten und Einstellungen gehören nicht dazu.
- **LOG-02** — Ein Eintrag nennt Benutzer, Zeitpunkt, Aktion und jeden geänderten Wert mit altem
  und neuem Wert (z. B. „BK, Spieltag 7: 62 → 65“).
- **LOG-03** — Den Verlauf sehen nur Admins, unter „Verlauf“ in der Navigation nach „Besucher“,
  der neueste Eintrag zuerst. Einträge werden nie gelöscht. Zeiten gelten in deutscher Zeit
  (Europe/Berlin).

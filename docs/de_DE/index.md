Plugin zum Erstellen einer Agenda und Auslösen von Aktionen
(Befehl oder Szenario).

Plugin Konfiguration
=======================

Die Konfiguration ist sehr einfach, nach dem Herunterladen des Plugins ist es
Sie aktivieren es einfach und das wars.

Gerätekonfiguration
=============================

Auf die Konfiguration von Kalendergeräten kann über das Menü zugegriffen werden
Plugins dann Organisation.

Sobald Sie darauf sind, finden Sie die Liste Ihrer Agenda.

Hier finden Sie die gesamte Konfiguration Ihrer Geräte :

-   **Name der Ausrüstung** : Name Ihres Kalenders.

-   **Übergeordnetes Objekt** : gibt das übergeordnete Objekt an, zu dem
    gehört Ausrüstung.

-   **Kategorie** : Gerätekategorien (es kann gehören
    mehrere Kategorien).

-   **Activer** : macht Ihre Ausrüstung aktiv.

-   **Visible** : macht es auf dem Dashboard sichtbar.

-   **Widget, Anzahl der Tage** : definiert die Anzahl der Tage
    Ereignisse, die im Widget angezeigt werden sollen.

-   **Maximale Anzahl von Ereignissen** : Stellen Sie die maximale Anzahl ein
    Ereignisse, die im Dashboard angezeigt werden sollen.

-   **Status und Bestellungen nicht anzeigen
    Aktivierung / Deaktivierung** : ermöglicht das Ausblenden des Status von
    die Agenda sowie die Befehle zum Aktivieren oder Nicht-Aktivieren.

-   **Liste der Kalenderereignisse** : Anzeige unter dem
    Liste aller Kalenderereignisse (ein Klick darauf ermöglicht
    Veranstaltung direkt bearbeiten).

-   **Ereignis hinzufügen** : Fügen Sie dem Kalender ein Ereignis hinzu.

-   **Agenda** : Anzeige einer Kalendertypansicht mit allen
    Ereignisse, in die Sie einziehen können, können angezeigt werden
    Verschieben Sie Ereignisse pro Woche oder Tag (Drag & Drop) und a
    Durch Klicken auf ein Ereignis wird das Bearbeitungsfenster geöffnet.

Ereignis bearbeiten
======================

Der wichtigste Teil des Plugins, hier können Sie
Konfigurieren Sie Ihre Veranstaltung.

Ereignis
---------

Hier finden Sie :

-   **Name der Veranstaltung** : Name Ihrer Veranstaltung.

-   **Symbol** : Mit dieser Option können Sie ein Symbol vor Ihrem Namen einfügen
    Ausrüstung (klicken Sie dazu auf "Symbol auswählen").

-   **Couleur** : Mit dieser Option können Sie die Farbe Ihrer Veranstaltung auswählen (a
    Häkchen ermöglicht es Ihnen auch, es transparent zu machen).

-   **Textfarbe** : Hier können Sie die Textfarbe von auswählen
    Ihre Veranstaltung.

-   **Nicht im Dashboard anzeigen** : erlaubt nicht anzuzeigen
    dieses Ereignis im Widget.

Aktion starten
---------------

Ermöglicht die Auswahl der Aktion (en), die beim Starten ausgeführt werden sollen
die Veranstaltung.

Um eine Aktion hinzuzufügen, klicken Sie einfach auf die Schaltfläche + am Ende von
In der Zeile haben Sie dann eine Schaltfläche, um nach einer Bestellung zu suchen. a
Sobald es gefunden ist, haben Sie die Wahl zwischen Optionen, falls vorhanden. Sie
kann so viel Aktion hinzufügen, wie Sie möchten.

> **Tip**
>
> Es ist möglich, die Reihenfolge der Aktionen durch Halten / Ziehen zu ändern
> celle-ci


> **Tip**
>
>Es ist möglich, die gleichen Aktionen wie in den Szenarien auszuführen (siehe [hier](https://jeedom.github.io/core/fr_FR/scenario))

Aktion beenden
-------------

Wie die Startaktion, diesmal jedoch die Aktion (en)
am Ende der Veranstaltung durchführen.

Programmation
-------------

Hier befindet sich das gesamte Zeitmanagement Ihrer Veranstaltung :

-   **Anfang** : Startdatum des Ereignisses.

-   **Fin** : Ereignisenddatum.

-   **Den ganzen Tag** : ermöglicht es, das Ereignis auf einem beliebigen zu definieren
    der Tag.

-   **In einen anderen Kalender aufnehmen** : Ermöglicht das Einschließen eines anderen
    Ereignis in Ihrem aktuellen Ereignis. Zum Beispiel, wenn Sie eine haben
    Ereignis jeden Montag wiederholt, und Sie schließen dies ein
    Ereignis A in Ihrem aktuellen Ereignis, dann wird dieses sein
    wird jeden Montag automatisch wiederholt.

-   **Inclure** : Mit dieser Option können Sie ein Ereignisdatum erzwingen
    Setzen Sie mehrere, indem Sie sie durch (Kommas) trennen
    Definieren Sie auch einen Bereich mit : (zwei Punkte).

-   **Wiederholt** : Nehmen wir an, Ihre Veranstaltung wird wiederholt (falls dies der Fall ist)
    Das Kontrollkästchen ist nicht aktiviert. Sie haben nicht die folgenden Optionen.).

-   **Wiederholungsmodus** : Hier können Sie den Wiederholungsmodus festlegen,
    sei einfach : jeden Tag, alle X Tage ... oder jede Wiederholung
    der 1., 2.… um jeden 3. Montag des
    Monate zum Beispiel (die folgenden Optionen können unterschiedlich sein
    abhängig von dieser Wahl).

-   **Wiederholen Sie alle** : \ [nur Einzelwiederholungsmodus \] erlaubt
    Definieren Sie die Häufigkeit der Wiederholung des Ereignisses (z. B. alle 3)
    Tage oder alle 2 Monate…).

-   **Le** : \ [Wiederholungsmodus der erste, der zweite ... nur \] :
    Mit dieser Option können Sie jeden 2. Montag im Monat eine Probe auswählen
    Zum Beispiel.

-   **Nur die** : ermöglicht es Ihnen, die Wiederholung auf bestimmte zu beschränken
    wochentags.

-   **Restriction** : Mit dieser Option können Sie nur das Ereignis einschränken
    Feiertage oder Feiertage ausschließen.

-   **Jusqu'à** : gibt das Enddatum des Auftretens des Ereignisses an.

-   **Durch einen anderen Kalender ausschließen** : erlaubt dies auszuschließen
    Ereignis nach einem anderen Kalender (um zum Beispiel das zu vermeiden
    2 widersprüchliche Ereignisse werden zusammen gefunden).

-   **Exclure** : wie "Einschließen", diesmal jedoch ausschließen
    Daten.

> **Note**
>
> Feiertage sind französisch und nur französisch nicht
> funktioniert nicht für andere Länder

> **Note**
>
> Oben rechts haben Sie 3 Schaltflächen, eine zum Löschen und eine zum Löschen
> speichern und eine zu duplizieren. Wenn Sie auf diese letzte Freiheit klicken
> Zeigt das aus der Duplizierung resultierende Ereignis an, damit Sie
> kann zum Beispiel den Namen ändern.Also vergiss es nicht
> Speichern Sie nach einem Klick auf die Schaltfläche Duplizieren

Tagebuch, Bestellungen und Szenario
=============================

Eine Agenda hat Kontrollen :

-   **In Bearbeitung** : listet die aktuellen Ereignisse getrennt durch
    Kommas zur Verwendung im einfachsten Szenario und
    zur Verwendung des Operators enthält (Übereinstimmungen) oder enthält nicht (nicht
    Übereinstimmungen), zum Beispiel * \ [Apartment \] \ [Test \] \ [In Bearbeitung \] * Übereinstimmungen
    "/ Anniv / ", ist wahr, wenn es in der Liste der aktuellen Ereignisse gibt
    ein "Anniv"

- **Fügen Sie ein Datum hinzu** : Ermöglicht es einem Szenario, einem Ereignis ein Datum hinzuzufügen (seien Sie vorsichtig, wenn Sie den Namen des Ereignisses ändern, müssen Sie ihn auch im Szenario korrigieren).. Sie können mehrere Ereignisse getrennt durch setzen ,

- **Entfernen Sie ein Datum** : Ermöglicht es einem Szenario, ein Datum von einem Ereignis auszuschließen (seien Sie vorsichtig, wenn Sie den Namen des Ereignisses ändern, müssen Sie ihn auch im Szenario korrigieren).. Sie können mehrere Ereignisse getrennt durch setzen ,

> **Note**
>
> Sie können den Befehl "In Bearbeitung" als Auslöser verwenden
> In einem Szenario wird jede Aktualisierung der Informationen ausgelöst
> die Ausführung des Szenarios. Es ist jedoch am besten, dies zu verwenden
> Befehl in einem programmierten Szenario mit einem Test auf den Wert.

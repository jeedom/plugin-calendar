Plugin to create an agenda and trigger actions
(command or scenario).

Plugin configuration
=======================

The configuration is very simple, after downloading the plugin, it
you just activate it and that&#39;s it.

Equipment configuration
=============================

The configuration of Calendar devices is accessible from the menu
Plugins then Organization.

Once on it you will find the list of your Agenda.

Here you find all the configuration of your equipment :

-   **Name of equipment** : name of your calendar.

-   **Parent object** : indicates the parent object to which
    belongs equipment.

-   **Category** : equipment categories (it may belong to
    multiple categories).

-   **Activer** : makes your equipment active.

-   **Visible** : makes it visible on the dashboard.

-   **Widget, number of days** : defines the number of days
    events to display on the widget.

-   **Maximum number of events** : set the maximum number
    events to display on the dashboard.

-   **Do not display status and orders
    activation / deactivation** : allows to hide the status of
    the agenda as well as the commands for activating it or not.

-   **List of calendar events** : display below the
    list of all calendar events (a click on it allows
    edit the event directly).

-   **Add event** : add an event to the calendar.

-   **Agenda** : Display of a calendar type view with all
    events you can move in, choose to display it
    per week or day, move events (drag and drop) and a
    clicking on an event will open its editing window.

Editing an event
======================

The most important part of the plugin, this is where you will be able to
configure your event.

Event
---------

Here you find :

-   **Name of the event** : Name of your event.

-   **Icon** : allows you to add an icon in front of your name
    equipment (to do this, click on "Choose an icon").

-   **Couleur** : allows you to choose the color of your event (a
    check mark also allows you to make it transparent).

-   **Text color** : allows you to choose the text color of
    your event.

-   **Do not show in the dashboard** : allows not to display
    this event on the widget.

Start action
---------------

Allows you to choose the action (s) to do when launching
the event.

To add an action, just click on the + button at the end of
the line then you're going to have a button to search for an order a
once it is found you will have the choice of options if it has any. You
can add as much action as you want.

> **Tip**
>
> It is possible to modify the order of actions by holding / dragging
> celle-ci


> **Tip**
>
>It is possible to do the same actions as in the scenarios (see [here](https://jeedom.github.io/core/fr_FR/scenario))

End action
-------------

Same as the start action but this time it is the action (s) to
perform at the end of the event.

Programmation
-------------

This is where all the time management of your event is located :

-   **Start** : Event start date.

-   **Fin** : Event end date.

-   **All day** : allows to define the event on any
    the day.

-   **Include by another calendar** : Allows to include another
    event in your current event. For example, if you have a
    event repeated every Monday, and you include this
    event A in your current event, then this one will be
    automatically repeated every Monday.

-   **Inclure** : allows you to force an occurrence date, you can
    put several by separating them with, (commas), you can
    also define a range with : (two points).

-   **Say again** : lets say that your event is repeated (if this
    box is not checked you will not have the following options).

-   **Repeat mode** : allows you to specify the repeat mode,
    be simple : every day, every X days… or repetition every
    the 1st, 2nd… to repeat an event every 3rd Monday of the
    months for example (the following options may be different
    depending on this choice).

-   **Repeat every** : \ [single repeat mode only \] allows
    define the frequency of repetition of the event (eg every 3
    days or every 2 months…).

-   **Le** : \ [repeat mode the first, the second ... only \] :
    allows you to choose a rehearsal every 2nd Monday of the month
    For example.

-   **Only the** : allows you to restrict repetition to certain
    days of the week.

-   **Restriction** : allows you to restrict the event only
    holidays or exclude holidays.

-   **Jusqu'à** : gives the end date of occurrence of the event.

-   **Exclude by another calendar** : allows to exclude this
    event according to another calendar (to avoid for example that
    2 contradictory events are found together).

-   **Exclure** : same as "Include" but this time to exclude
    dates.

> **Note**
>
> Public holidays are French and only French this does not
> does not work for other countries

> **Note**
>
> At the top right you have 3 buttons, one to delete, one to
> save and one to duplicate. When clicking on this last jeedom
> displays the event resulting from the duplication so that you
> can change the name for example.So don't forget to
> save following a click on the duplicate button

Diary, orders and scenario
=============================

An agenda has controls :

-   **Running** : lists the current events separated by
    commas, for use in the simplest scenario and
    to use the operator contains (matches) or does not contain (not
    matches), for example * \ [Apartment \] \ [test \] \ [In progress \] * matches
    "/ Anniv / ", will be true if in the list of current events there is
    an "Anniv"

- **Add a date** : allows from a scenario to add a date to an event (be careful if you change the name of the event you will have to correct it in the scenario too). You can put several events separated by ,

- **Remove a date** : allows from a scenario to exclude a date from an event (be careful if you change the name of the event you will have to correct it in the scenario too). You can put several events separated by ,

> **Note**
>
> You can use the "In progress" command as a trigger
> in a scenario, each update of the information will trigger
> the execution of the scenario. However, it is best to use this
> command in a programmed scenario with a test on the value.

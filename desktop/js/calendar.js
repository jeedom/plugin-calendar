
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
 calendar = null;
 $('#bt_healthcalendar').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé Agenda}}"});
    $('#md_modal').load('index.php?v=d&plugin=calendar&modal=health').dialog('open');
});
 $('#bt_addEvent').on('click', function () {
	$('#bt_calendartab').trigger('click');
    $('#md_modal').dialog({title: "{{Ajouter évènement}}"});
    $('#md_modal').load('index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});

 $('#div_eventList').delegate('.editEvent', 'click', function () {
	$('#bt_calendartab').trigger('click');
    $('#md_modal').dialog({title: "{{Ajouter évènement}}"});
    $('#md_modal').load('index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + '&id=' + $(this).attr('data-event_id')).dialog('open');
});

 $('#bt_calendartab').on('click',function(){
    setTimeout(function(){ $('#div_calendar').fullCalendar('render'); }, 100);
 });


 if (!isNaN(getUrlVars('event_id')) && getUrlVars('event_id') != '') {
    setTimeout(function(){
        $('#md_modal').dialog({title: "{{Ajouter évènement}}"});
        $('#md_modal').load('index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + '&id=' + getUrlVars('event_id')).dialog('open');
    }, 1000);
}

function printEqLogic() {
    if (calendar !== null) {
        calendar.fullCalendar('destroy');
    }
    calendar = $('#div_calendar').fullCalendar({
        lang: jeedom_langage.substr(0, 2),
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        height: 600,
        events: "plugins/calendar/core/ajax/calendar.ajax.php?action=getEvents&eqLogic_id=" + $('.eqLogicAttr[data-l1key=id]').value(),
        eventClick: function (calEvent) {
            $('#md_modal').dialog({title: "{{Ajouter évènement}}"});
            $('#md_modal').load('index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + '&id=' + calEvent.id + '&date=' + encodeURI(calEvent.start._i)).dialog('open');
        },
        editable: true,
        defaultView: 'month',
        allDayDefault: false,
        timeFormat: 'H:mm',
        eventDrop: function (event) {
            var eventSave = {
                id: event.id,
                startDate: event.start.format(),
                endDate: event.end.format()
            };
            updateCalendarEvent(eventSave);
        },
        eventRender: function (event, element) {
            element.find('.fc-title').html(event.title);
        },
        eventResize: function (event, revertFunc) {
            var eventSave = {
                id: event.id,
                startDate: event.start.format(),
                endDate: event.end.format()
            };
            updateCalendarEvent(eventSave);
        }
    });
    updateEventList();
     $('#div_calendar').fullCalendar('render');
}

function updateEventList() {
    $.ajax({
        type: 'POST',
        url: 'plugins/calendar/core/ajax/calendar.ajax.php',
        data: {
            action: 'getAllEvents',
            eqLogic_id: $('.eqLogicAttr[data-l1key=id]').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            var html = '';
            for (var i in data.result) {
                var color = init(data.result[i].cmd_param.color, '#2980b9');
                if(data.result[i].cmd_param.transparent == 1){
                   color = 'transparent';
               }

               html += '<span class="label editEvent cursor" data-event_id="' + data.result[i].id + '" style="background-color : ' + color + ';color : ' + init(data.result[i].cmd_param.text_color, 'black') + ';margin-top:5px;font-size:1em;display:inline-block;">';
               if (data.result[i].cmd_param.eventName != '') {
                html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.eventName;
            } else {
                html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.name;
            }
            html += '</span><br\>';
        }
        $('#div_eventList').empty().append(html);
    }
});
}

function updateCalendarEvent(_event) {
    $.ajax({
        type: 'POST',
        url: 'plugins/calendar/core/ajax/calendar.ajax.php',
        data: {
            action: 'saveEvent',
            event: json_encode(_event)
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Evènement modifié avec succès}}', level: 'success'});
            calendar.fullCalendar('refetchEvents');
        }
    });
}
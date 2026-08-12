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

var calendar

document.getElementById('div_mainContainer').addEventListener('click', function(event) {
  let _target = null

  if (_target = event.target.closest('#bt_healthcalendar')) {
    jeeDialog.dialog({
      id: 'jee_modal2',
      title: '{{Santé Agenda}}',
      contentUrl: 'index.php?v=d&plugin=calendar&modal=health'
    })
    return
  }

  if (_target = event.target.closest('#bt_addEvent')) {
    document.getElementById('bt_calendartab').triggerEvent('click')
    jeeDialog.dialog({
      id: 'eventEditModal',
      title: '{{Ajouter un évènement}}',
      contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue()
    })
    return
  }

  if (_target = event.target.closest('.editEvent')) {
    document.getElementById('bt_calendartab').triggerEvent('click')
    jeeDialog.dialog({
      id: 'eventEditModal',
      title: '{{Modifier un évènement}}',
      contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue() + '&id=' + _target.getAttribute('data-event_id')
    })
    return
  }

  if (_target = event.target.closest('#bt_calendartab')) {
    setTimeout(function() { calendar.render() }, 200)
    return
  }
})

if (parseInt(getUrlVars('event_id')) > 0) {
  setTimeout(function() {
    jeeDialog.dialog({
      id: 'eventEditModal',
      title: '{{Modifier un évènement}}',
      contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue() + '&id=' + getUrlVars('event_id')
    })
  }, 500)
}

function printEqLogic(_eqLogic) {
  if (calendar !== undefined) {
    calendar.destroy()
  }
  calendar = new FullCalendar.Calendar(document.getElementById('div_calendar'), {
    locale: jeeFrontEnd.language.substring(0, 2),
    height: "auto",
    nextDayThreshold: '12:00:00',
    stickyHeaderDates: false,
    allDaySlot: false,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    events: "plugins/calendar/core/ajax/calendar.ajax.php?action=getEvents&eqLogic_id=" + _eqLogic.id,
    eventClick: function(info) {
      jeeDialog.dialog({
        id: 'eventEditModal',
        title: '{{Modifier un évènement}}',
        contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + _eqLogic.id + '&id=' + info.event.id + '&date=' + encodeURI(info.event.start.toUTCString())
      })
    },
    eventTimeFormat: {
      hour: 'numeric',
      minute: '2-digit',
      meridiem: false
    },
    datesSet: function(dateInfo) {
      document.querySelector('.eqLogicAttr[data-l2key="defaultView"]').jeeValue(dateInfo.view.type)
    },
    initialView: _eqLogic.display.defaultView,
    eventDisplay: 'block',
    eventContent: function(info) {
      return { html: info.timeText + ' ' + info.event.title }
    }
  })

  updateEventList(_eqLogic.id)
}

function updateEventList(_eqLogicId) {
  domUtils.ajax({
    type: 'POST',
    url: 'plugins/calendar/core/ajax/calendar.ajax.php',
    data: {
      action: 'getAllEvents',
      eqLogic_id: _eqLogicId || document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue()
    },
    dataType: 'json',
    success: function(data) {
      if (data.state != 'ok') {
        jeedomUtils.showAlert({
          message: data.result,
          level: 'danger'
        })
        return
      }
      let html = ''
      for (const i in data.result) {
        let color = init(data.result[i].cmd_param.color, '#2980b9')
        if (data.result[i].cmd_param.transparent == 1) {
          color = 'transparent'
        }
        html += '<span class="label editEvent" data-event_id="' + data.result[i].id + '" style="cursor:pointer!important;background-color : ' + color + ';color : ' + init(data.result[i].cmd_param.text_color, 'black') + ';margin-top:5px;padding:8px;font-weight:bold;">'
        const icon = data.result[i].cmd_param.icon ? data.result[i].cmd_param.icon + ' ' : ''
        if (data.result[i].cmd_param.eventName != '') {
          html += icon + data.result[i].cmd_param.eventName
        }
        else {
          html += icon + data.result[i].cmd_param.name
        }
        html += '</span>'
        if (data.result[i].repeat.enable == 0) {
          html += ' {{Le}} ' + data.result[i].startDate.substring(0, 10)
        }
        else if (data.result[i].repeat.mode == 'simple') {
          html += ' {{Répétition simple}}'
        }
        else {
          html += ' {{Répétition avancée}}'
        }
        if (data.result[i].startDate.substr(11, 5) == '00:00' && data.result[i].endDate.substr(11, 5) == '23:59') {
          html += ' {{toute la journée}}<br><br>'
        }
        else {
          html += ' {{de}} ' + data.result[i].startDate.substr(11, 5) + ' {{à}} ' + data.result[i].endDate.substr(11, 5) + '<br><br>'
        }
      }
      document.getElementById('div_eventList').innerHTML = html
    }
  })
}

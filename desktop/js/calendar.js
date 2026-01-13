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

var calendar = undefined

document.getElementById('bt_healthcalendar').addEventListener('click', function() {
 jeeDialog.dialog({
   title: "{{Santé Agenda}}",
   contentUrl: 'index.php?v=d&plugin=calendar&modal=health'
 })
})

document.getElementById('bt_addEvent').addEventListener('click', function() {
  document.getElementById('bt_calendartab')?.triggerEvent('click')
  jeeDialog.dialog({
    title: "{{Ajouter un évènement}}",
    contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue()
  })
})

document.querySelector('#div_eventList').addEventListener('click', function(event) {
  var _target = null
  if (_target = event.target.closest('.editEvent')) {
    let eqLogic_id = document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue()
    let event_id = _target.getAttribute('data-event_id')
    document.getElementById('bt_calendartab')?.click()
    jeeDialog.dialog({
      title: "{{Modifier un évènement}}",
      contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + eqLogic_id + '&id=' + event_id
    })
  }
  return
})

document.getElementById('bt_calendartab').addEventListener('click', function() {
  setTimeout(function () { calendar.render() }, 600)
})

if (!isNaN(getUrlVars('event_id')) && getUrlVars('event_id') != '') {
  setTimeout(function () {
    let eqLogic_id = document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue()
    jeeDialog.dialog({
      title: "{{Ajouter/Modifier un évènement}}",
      contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + eqLogic_id + '&id=' + getUrlVars('event_id')
    })
  }, 1000)
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
    events: "plugins/calendar/core/ajax/calendar.ajax.php?action=getEvents&eqLogic_id=" + document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue(),
    eventClick: function (info) {
      jeeDialog.dialog({
        title: "{{Modifier un évènement}}",
        contentUrl: 'index.php?v=d&plugin=calendar&modal=event.edit&eqLogic_id=' + document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue() + '&id=' + info.event.id + '&date=' + encodeURI(info.event.start.toUTCString())
      })
    },
    eventTimeFormat: {
      hour: 'numeric',
      minute: '2-digit',
      meridiem: false
    },
    datesSet: function (dateInfo) {
      document.querySelector('.eqLogicAttr[data-l2key="defaultView"]').value = dateInfo.view.type
    },
    initialView: _eqLogic.display.defaultView,
    eventDisplay: 'block',
    eventContent: function (info) {
      return { html: info.timeText + ' ' + info.event.title }
    }
  })

  updateEventList()
}

function updateEventList() {
  domUtils.ajax({
    type: "POST",
    url: "plugins/calendar/core/ajax/calendar.ajax.php",
    data: {
      action: 'getAllEvents',
      eqLogic_id: document.querySelector('.eqLogicAttr[data-l1key=id]').jeeValue()
    },
    dataType: "json",
    error: function (request, status, error) {
     domUtils.handleAjaxError(request, status, error)
    },
    success: function (data) {
      if (data.state != 'ok') {
        jeedomUtils.showAlert({ message: data.result, level: "danger" })
        return
      }
      var html = ''
      for (var i in data.result) {
        var color = init(data.result[i].cmd_param.color, '#2980b9')
        if (data.result[i].cmd_param.transparent == 1) {
          color = 'transparent'
        }
        html += '<span class="label editEvent" data-event_id="' + data.result[i].id + '" style="cursor:pointer!important;background-color : ' + color + ';color : ' + init(data.result[i].cmd_param.text_color, 'black') + ';margin-top:5px;padding:8px;font-weight:bold;">'
        let icon = isset(data.result[i].cmd_param.icon) ? data.result[i].cmd_param.icon + ' ' : ''
        if (data.result[i].cmd_param.eventName != '') {
          html +=  icon + data.result[i].cmd_param.eventName
        }
        else {
          html += icon + data.result[i].cmd_param.name
        }
        html += '</a></span>'
        if (data.result[i].repeat.enable == 0) {
          html += ' {{Le}} ' + new Date(data.result[i].startDate).toLocaleString().substring(0, 10)
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

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

var eventEditModal = jeeDialog.get('#eventEditModal', 'dialog')
eventEditModal.style.maxWidth = '1500px'
eventEditModal.style.left = (document.body.getBoundingClientRect().width / 2) - (eventEditModal.getBoundingClientRect().width / 2) + 'px'
// Remove listeners from previous open (script re-runs in global scope on each modal open)
eventEditModal._ac?.abort()
eventEditModal._ac = new AbortController()

jeedomUtils.datePickerInit('Y-m-d H:i')

if (is_array(_calendarEvent)) {
  eventEditModal.querySelector('#div_eventEdit').setJeeValues(_calendarEvent, '.calendarAttr')
  displayRepeatOptions(_calendarEvent.repeat.enable == 1)

  for (const type of ['start', 'end']) {
    if (isset(_calendarEvent.cmd_param[type])) {
      for (const i in _calendarEvent.cmd_param[type]) {
        addAction(_calendarEvent.cmd_param[type][i], type)
      }
    }
  }
}

eventEditModal.addEventListener('click', function(event) {
  let _target = null

  if (_target = event.target.closest('#bt_chooseIcon')) {
    jeedomUtils.chooseIcon(function(_icon) {
      eventEditModal.querySelector('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').innerHTML = _icon
    })
    return
  }

  if (_target = event.target.closest('.bt_addAction')) {
    addAction({}, _target.dataset.type)
    return
  }

  if (_target = event.target.closest('.bt_removeAction')) {
    const type = _target.dataset.type
    _target.closest('.' + type).remove()
    eventEditModal.addClass('jeeDialogNoCloseBackdrop')
    return
  }

  if (_target = event.target.closest('.listAction')) {
    const type = _target.dataset.type
    const el = _target.closest('.' + type).querySelector('.expressionAttr[data-l1key="cmd"]')
    jeedom.getSelectActionModal({}, function(result) {
      el.jeeValue(result.human)
      jeedom.cmd.displayActionOption(el.jeeValue(), '', function(html) {
        el.closest('.' + type).querySelector('.actionOptions').html(html)
        jeedomUtils.taAutosize()
      })
    })
    return
  }

  if (_target = event.target.closest('.listEventCmdAction')) {
    const type = _target.dataset.type
    const el = _target.closest('.' + type).querySelector('.expressionAttr[data-l1key="cmd"]')
    jeedom.cmd.getSelectModal({
      cmd: {
        type: 'action'
      }
    }, function(result) {
      el.jeeValue(result.human)
      jeedom.cmd.displayActionOption(el.jeeValue(), '', function(html) {
        el.closest('.' + type).querySelector('.actionOptions').html(html)
        jeedomUtils.taAutosize()
      })
    })
    return
  }

  if (_target = event.target.closest('.calendarAction[data-action=allDay]')) {
    const eventStart = eventEditModal.querySelector('.calendarAttr[data-l1key=startDate]')
    let startDate = eventStart.jeeValue().substr(0, 10)
    if (startDate.trim() == '') {
      startDate = new Date()
      const y = startDate.getFullYear()
      let m = startDate.getMonth() + 1
      let d = startDate.getDate()
      m = (m < 10) ? '0' + m : m
      d = (d < 10) ? '0' + d : d
      startDate = y + '-' + m + '-' + d
    }
    eventStart.jeeValue(startDate + ' 00:00:00')
    eventEditModal.querySelector('.calendarAttr[data-l1key=endDate]').jeeValue(startDate + ' 23:59:00')
    return
  }

  if (_target = event.target.closest('#md_eventEditSave')) {
    _calendarEvent = eventEditModal.getJeeValues('.calendarAttr')[0]
    _calendarEvent.cmd_param.start = eventEditModal.querySelectorAll('#div_start .start').getJeeValues('.expressionAttr')
    _calendarEvent.cmd_param.end = eventEditModal.querySelectorAll('#div_end .end').getJeeValues('.expressionAttr')

    domUtils.ajax({
      type: 'POST',
      url: 'plugins/calendar/core/ajax/calendar.ajax.php',
      data: {
        action: 'saveEvent',
        event: JSON.stringify(_calendarEvent)
      },
      dataType: 'json',
      noDisplayError: true,
      error: function(error) {
        jeedomUtils.showAlert({
          message: error.message,
          level: 'danger',
          attachTo: eventEditModal
        })
      },
      success: function(data) {
        if (data.state != 'ok') {
          jeedomUtils.showAlert({
            message: data.result,
            level: 'danger',
            attachTo: eventEditModal
          })
          return
        }

        calendar.refetchEvents()
        updateEventList()
        eventEditModal._jeeDialog.close()
        const eventName = _calendarEvent.cmd_param.eventName
        jeedomUtils.showAlert({
          message: (_calendarEvent.id != '') ? `{{L'évènement ${eventName} a été modifié}}` : `{{L'évènement ${eventName} a été ajouté}}`,
          level: 'success'
        })
      }
    })
    return
  }

  if (_target = event.target.closest('#md_eventEditDuplicate')) {
    eventEditModal.querySelector('.calendarAttr[data-l1key=id]').jeeValue('')
    _target.unseen()
    eventEditModal.querySelector('#md_eventEditRemove').unseen()
    eventEditModal.querySelector('#md_eventEditSave').addClass('rounded')
    return
  }

  if (_target = event.target.closest('#md_eventEditRemove')) {
    if (is_array(_calendarEvent) && _calendarEvent.repeat.enable == 1 && _dateEvent != '') {
      jeeDialog.confirm({
        title: '{{Suppression}} ' + _calendarEvent.cmd_param.eventName,
        message: `{{Voulez-vous supprimer uniquement cette occurrence (${_calendarEvent.startDate} - ${_calendarEvent.endDate}) ou l'évènement complet?}}`,
        defaultButtons: {},
        buttons: {
          cancel: {
            label: '{{Annuler}}',
            className: 'btn-default',
            callback: {
              click: function(event) {
                event.target.closest('div.jeeDialog')._jeeDialog.close()
              }
            }
          },
          success: {
            label: '{{Supprimer occurrence}}',
            className: 'warning',
            callback: {
              click: function(event) {
                domUtils.ajax({
                  type: 'POST',
                  url: 'plugins/calendar/core/ajax/calendar.ajax.php',
                  data: {
                    action: 'removeOccurrence',
                    id: _calendarEvent.id,
                    date: _dateEvent
                  },
                  dataType: 'json',
                  noDisplayError: true,
                  error: function(error) {
                    jeedomUtils.showAlert({
                      message: error.message,
                      level: 'danger',
                      attachTo: eventEditModal
                    })
                  },
                  success: function(data) {
                    if (data.state != 'ok') {
                      jeedomUtils.showAlert({
                        message: data.result,
                        level: 'danger',
                        attachTo: eventEditModal
                      })
                      return
                    }

                    calendar.refetchEvents()
                    updateEventList()
                    event.target.closest('div.jeeDialog')._jeeDialog.close()
                    eventEditModal._jeeDialog.close()
                    jeedomUtils.showAlert({
                      message: '{{Occurrence supprimée avec succès}}',
                      level: 'success'
                    })
                  }
                })
              }
            }
          },
          danger: {
            label: '{{Supprimer évènement}}',
            className: 'danger',
            callback: {
              click: function(event) {
                domUtils.ajax({
                  type: 'POST',
                  url: 'plugins/calendar/core/ajax/calendar.ajax.php',
                  data: {
                    action: 'removeEvent',
                    id: _calendarEvent.id
                  },
                  dataType: 'json',
                  noDisplayError: true,
                  error: function(error) {
                    jeedomUtils.showAlert({
                      message: error.message,
                      level: 'danger',
                      attachTo: eventEditModal
                    })
                  },
                  success: function(data) {
                    if (data.state != 'ok') {
                      jeedomUtils.showAlert({
                        message: data.result,
                        level: 'danger',
                        attachTo: eventEditModal
                      })
                      return
                    }

                    calendar.refetchEvents()
                    updateEventList()
                    event.target.closest('div.jeeDialog')._jeeDialog.close()
                    eventEditModal._jeeDialog.close()
                    jeedomUtils.showAlert({
                      message: '{{Evènement supprimé avec succès}}',
                      level: 'success'
                    })
                  }
                })
              }
            }
          }
        }
      })
    } else {
      jeeDialog.confirm({
        title: '{{Suppression}} ' + _calendarEvent.cmd_param.eventName,
        message: '{{Etes-vous sûr de vouloir supprimer cet évènement?}}'
      }, function(result) {
        if (result) {
          domUtils.ajax({
            type: 'POST',
            url: 'plugins/calendar/core/ajax/calendar.ajax.php',
            data: {
              action: 'removeEvent',
              id: _calendarEvent.id
            },
            dataType: 'json',
            noDisplayError: true,
            error: function(error) {
              jeedomUtils.showAlert({
                message: error.message,
                level: 'danger',
                attachTo: eventEditModal
              })
            },
            success: function(data) {
              if (data.state != 'ok') {
                jeedomUtils.showAlert({
                  message: data.result,
                  level: 'danger',
                  attachTo: eventEditModal
                })
                return
              }

              calendar.refetchEvents()
              updateEventList()
              eventEditModal._jeeDialog.close()
              jeedomUtils.showAlert({
                message: '{{Evènement supprimé avec succès}}',
                level: 'success'
              })
            }
          })
        }
      })
    }
    return
  }

  if (_target = event.target.closest('button.btClose')) {
    if (!eventEditModal.hasClass('jeeDialogNoCloseBackdrop')) {
      return
    }

    event.stopImmediatePropagation()
    if (confirm("{{Quitter la fenêtre sans sauvegarder l'évènement?}}")) {
      eventEditModal.removeClass('jeeDialogNoCloseBackdrop')
      eventEditModal._jeeDialog.close()
    }
    return
  }
}, { capture: true, signal: eventEditModal._ac.signal })

eventEditModal.addEventListener('change', function(event) {
  let _target = null

  if (_target = event.target.closest('.calendarAttr, .expressionAttr')) {
    eventEditModal.addClass('jeeDialogNoCloseBackdrop')
  }

  if (_target = event.target.closest('.calendarAttr[data-l1key=repeat][data-l2key=enable]')) {
    displayRepeatOptions(_target.checked)
    return
  }

  if (_target = event.target.closest('.calendarAttr[data-l1key=repeat][data-l2key=mode]')) {
    const repeatMode = _target.jeeValue()
    // 4.5.4 mini:
    // eventEditModal.querySelector('.repeatMode:not(.' + repeatMode + ')').unseen()
    // eventEditModal.querySelector('.repeatMode.' + repeatMode).seen()
    eventEditModal.querySelector('.repeatMode:not(.' + repeatMode + ')').addClass('hidden')
    eventEditModal.querySelector('.repeatMode.' + repeatMode).removeClass('hidden')
    return
  }

  if (_target = event.target.closest('.calendarAttr[data-l1key=repeat][data-l2key=includeDateFromCalendar], .calendarAttr[data-l1key=repeat][data-l2key=excludeDateFromCalendar]')) {
    const formGroup = _target.parentNode.parentNode
    for (const calendarEvents of formGroup.querySelectorAll('div[data-calendar_id]')) {
      if (calendarEvents.isVisible()) {
        // 4.5.4 mini: calendarEvents.unseen()
        calendarEvents.addClass('hidden')

        const calendarEventsSelect = calendarEvents.querySelector('select')
        calendarEventsSelect.removeAttribute('data-l1key')
        calendarEventsSelect.removeAttribute('data-l2key')
        break
      }
    }

    if (_target.jeeValue() != '') {
      const calendarEvents = formGroup.querySelector('div[data-calendar_id="' + _target.jeeValue() + '"]')
      // 4.5.4 mini: calendarEvents.seen()
      calendarEvents.removeClass('hidden')

      const calendarEventsSelect = calendarEvents.querySelector('select')
      calendarEventsSelect.setAttribute('data-l1key', 'repeat')
      calendarEventsSelect.setAttribute('data-l2key', _target.dataset.l2key.replace('Calendar', 'Event'))
    }
    return
  }
}, { signal: eventEditModal._ac.signal })

eventEditModal.querySelector('#actiontab').addEventListener('focusout', function(event) {
  let _target = null

  if (_target = event.target.closest('.cmdAction.expressionAttr[data-l1key="cmd"]')) {
    const type = _target.getAttribute('data-type')
    const expression = _target.closest('.' + type).getJeeValues('.expressionAttr')
    jeedom.cmd.displayActionOption(_target.jeeValue(), init(expression[0].options), function(html) {
      _target.closest('.' + type).querySelector('.actionOptions').html(html)
      jeedomUtils.taAutosize()
    })
  }
})

eventEditModal.querySelector('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').addEventListener('dblclick', function() {
  this.innerHTML = ''
  this.triggerEvent('change')
})

for (const type of ['start', 'end']) {
  new Sortable(eventEditModal.querySelector('#div_' + type), {
    delay: 50,
    delayOnTouchOnly: true,
    draggable: '.' + type,
    filter: '.expressionAttr, .btn',
    preventOnFilter: false,
    direction: 'vertical',
    chosenClass: 'dragSelected',
    onUpdate: function(evt) {
      eventEditModal.addClass('jeeDialogNoCloseBackdrop')
    }
  })
}

function addAction(_action, _type) {
  if (!isset(_action)) {
    _action = {}
  }
  if (!isset(_action.options)) {
    _action.options = {}
  }
  let div = '<div class="' + _type + ' row" style="margin-bottom:5px">'
  div += '<div class="col-sm-1">'
  div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher la case pour désactiver l\'action}}">'
  div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher la case pour que la commande s\'exécute en parallèle des autres actions}}">'
  div += '</div>'
  div += '<div class="col-sm-4">'
  div += '<div class="input-group">'
  div += '<span class="input-group-btn">'
  div += '<a class="btn btn-default btn-sm bt_removeAction roundedLeft" data-type="' + _type + '" title="{{Supprimer l\'action}}"><i class="fas fa-minus-circle"></i></a>'
  div += '</span>'
  div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '">'
  div += '<span class="input-group-btn">'
  div += '<a class="btn btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>'
  div += '<a class="btn btn-default btn-sm listEventCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner la commande}}"><i class="fas fa-list-alt"></i></a>'
  div += '</span>'
  div += '</div>'
  div += '</div>'
  div += '<div class="col-sm-7 actionOptions"></div>'
  div += '</div>'

  eventEditModal.querySelector('#div_' + _type).insertAdjacentHTML('beforeend', div)
  const action = eventEditModal.querySelector('#div_' + _type + ' .' + _type + ':last-child')
  action.setJeeValues(_action, '.expressionAttr')
  action.querySelector('.expressionAttr[data-l1key="cmd"]').jeeComplete({
    source: jeedom.scenario.autoCompleteAction,
    forceSingle: true
  })
  jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options, function(html) {
    action.querySelector('.actionOptions').html(html)
    jeedomUtils.taAutosize()
  })
}

function displayRepeatOptions(_display = false) {
  if (_display) {
    // 4.5.4 mini: eventEditModal.querySelectorAll('.div_repeatOption').seen()
    eventEditModal.querySelectorAll('.div_repeatOption').removeClass('hidden')
  } else {
    // 4.5.4 mini: eventEditModal.querySelectorAll('.div_repeatOption').unseen()
    eventEditModal.querySelectorAll('.div_repeatOption').addClass('hidden')
  }
}

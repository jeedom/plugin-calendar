function initCalendarEvent(_options) {
  $.ajax({
    type: 'POST',
    url: 'plugins/calendar/core/ajax/calendar.ajax.php',
    data: {
      action: 'getAllCalendarAndEvents',
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
      $('#div_calendarEvent').empty();
      var html = '';
      for(var i in data.result){
        html += '<legend>'+data.result[i].name+'</legend>';
        html += '<ul data-role="listview" class="ui-listview">';
        for(var j in data.result[i].events){
          if (data.result[i].events[j].cmd_param.eventName != '') {
            html += '<li class="event"><a href="#" class="ui-btn ui-btn-icon-right ui-icon-carat-r" data-event_id="'+data.result[i].events[j].id+'">'+data.result[i].events[j].cmd_param.eventName+'</a></li>';
          }else{
            html += '<li class="event"><a href="#" class="ui-btn ui-btn-icon-right ui-icon-carat-r" data-event_id="'+data.result[i].events[j].id+'">'+data.result[i].events[j].cmd_param.name+'</a></li>';
          }
        }
        html += '</ul>';
      }
      $('#div_calendarEvent').html(html);

      $('#div_calendarEvent .event').on('click',function(){
        $('#div_calendarEvent').hide();
        $('#div_addIncludeDate').show();
        $('#div_addIncludeDate #in_event_id').value($(this).find('a').attr('data-event_id'));
      });
    }
  });

  $('#bt_cancelIncludeDate').on('click',function(){
    $('#div_calendarEvent').show();
    $('#div_addIncludeDate').hide();
    $('#div_addIncludeDate #in_event_id').value('');
  });

  $('#bt_validateIncludeDate').on('click',function(){
    $.ajax({
      type: 'POST',
      url: 'plugins/calendar/core/ajax/calendar.ajax.php',
      data: {
        action: 'addIncludeDateToEvent',
        id : $('#div_addIncludeDate #in_event_id').value(),
        startDate : $('#div_addIncludeDate #in_startdate').value(),
        endDate : $('#div_addIncludeDate #in_enddate').value()
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
        $('#div_calendarEvent').show();
        $('#div_addIncludeDate').hide();
        $('#div_addIncludeDate #in_event_id').value('');
      }
    });
  });

}

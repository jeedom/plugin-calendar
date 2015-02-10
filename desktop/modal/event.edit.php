<?php
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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
if (init('eqLogic_id') == '') {
	throw new Exception('{{L\'id de l\'équipement ne peut etre vide : }}' . init('eqLogic_id'));
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	throw new Exception('{{Aucun équipement associé à l\'id : }}' . init('eqLogic_id'));
}
$event = null;
if (init('id') != '') {
	$event = jeedom::toHumanReadable(calendar_event::byId(init('id')));
	if (!is_object($event)) {
		throw new Exception('{{Event id non trouvé : }}' . init('id'));
	}
	sendVarToJS('calendarEvent', utils::o2a($event));
	sendVarToJS('dateEvent', init('date'));
} else {
	sendVarToJS('calendarEvent', null);
	sendVarToJS('dateEvent', null);
}
?>
<div id='div_eventEditAlert' style="display: none;"></div>

<form class="form-horizontal" id="form_eventEdit">
    <fieldset>
        <legend>{{Evènement}}
            <a class="btn btn-xs btn-success pull-right" id="md_eventEditSave" style="color: white;"><i class="fa fa-check-circle"></i> {{Enregistrer}}</a>
            <?php
if (is_object($event)) {
	echo '<a class="btn btn-warning pull-right btn-xs" id="md_eventEditDuplicate" style="color: white;"><i class="fa fa-files-o"></i> {{Duplication}}</a>';
	echo '<a class="btn btn-danger pull-right btn-xs" id="md_eventEditRemove" style="color: white;"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>';
}
?>
        </legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de l'évenement}}</label>
            <div class="col-sm-3">
                <input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='eventName' />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Icône}}</label>
            <div class="col-sm-3">
                <input type="text" class="calendarAttr form-control" data-l1key="eqLogic_id" style="display: none;" value="<?php echo init('eqLogic_id')?>"/>
                <input type="text" class="calendarAttr form-control" data-l1key="id" style="display: none;" />
                <div class="calendarAttr" data-l1key="cmd_param" data-l2key="icon" ></div>
            </div>
            <div class="col-sm-2">
                <a class="btn btn-default btn-sm" id="bt_chooseIcon"><i class="fa fa-flag"></i> {{Choisir une icône}}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Couleur}}</label>
            <div class="col-sm-1">
                <input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='color' value='#2980b9' />
            </div>
            <label class="col-sm-1 control-label">{{Transparent}}</label>
            <div class="col-sm-3">
                <input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='transparent' />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Couleur du texte}}</label>
            <div class="col-sm-3">
                <input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='text_color' value='#FFFFFF' />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Ne pas à afficher dans le dashboard}}</label>
            <div class="col-sm-3">
                <input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='noDisplayOnDashboard' />
            </div>
        </div>
        <legend>Action de début</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Type}}</label>
            <div class="col-sm-3" >
                <select class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='start_type'>
                    <option value="none">Aucune</option>
                    <option value="cmd">Commande</option>
                    <option value="scenario">Scénario</option>
                </select>
            </div>
        </div>
        <div class="div_startType div_startcmd">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de la commande}}</label>
                <div class="col-sm-5">
                    <input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='start_name' />
                </div>
                <div class="col-sm-1">
                    <a class="btn btn-default btn-sm listCmdAction" data-target="start_name"><i class="fa fa-list-alt"></i></a>
                </div>
            </div>
            <div id="div_eventEditCmdStart" class="form-group">
                <label class="col-sm-3 control-label">{{Options}}</label>
                <div class="col-sm-6 options"></div>
            </div>
        </div>
        <div class="div_startType div_startscenario" style="display: none;">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom du scénario}}</label>
                <div class="col-sm-3">
                    <input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='start_scenarioName' />
                </div>
                <div class="col-sm-1">
                    <a class="btn btn-default btn-sm listScenario" data-target="start_scenarioName"><i class="fa fa-list-alt"></i></a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Action}}</label>
                <div class="col-sm-3">
                    <select class="calendarAttr form-control input-sm" data-l1key="cmd_param" data-l2key="start_action">
                        <option value="start">{{Start}}</option>
                        <option value="stop">{{Stop}}</option>
                        <option value="activate">{{Activer}}</option>
                        <option value="deactivate">{{Désactiver}}</option>
                    </select>
                </div>
            </div>
        </div>
        <legend>Action de fin</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Type}}</label>
            <div class="col-sm-3" >
                <select class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='end_type'>
                    <option value="none">Aucune</option>
                    <option value="cmd">Commande</option>
                    <option value="scenario">Scénario</option>
                </select>
            </div>
        </div>

        <div class="div_endType div_endcmd">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de la commande}}</label>
                <div class="col-sm-5">
                    <input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='end_name' />
                </div>
                <div class="col-sm-1">
                    <a class="btn btn-default btn-sm listCmdAction" data-target="end_name"><i class="fa fa-list-alt"></i></a>
                </div>
            </div>
            <div id="div_eventEditCmdEnd" class="form-group">
                <label class="col-sm-3 control-label">{{Options}}</label>
                <div class="col-sm-6 options"></div>
            </div>
        </div>
        <div class="div_endType div_endscenario" style="display: none;">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom du scénario}}</label>
                <div class="col-sm-3">
                    <input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='end_scenarioName' />
                </div>
                <div class="col-sm-1">
                    <a class="btn btn-default btn-sm listScenario" data-target="end_scenarioName"><i class="fa fa-list-alt"></i></a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Action}}</label>
                <div class="col-sm-3">
                    <select class="calendarAttr form-control input-sm" data-l1key="cmd_param" data-l2key="end_action">
                        <option value="start">{{Start}}</option>
                        <option value="stop">{{Stop}}</option>
                        <option value="activate">{{Activer}}</option>
                        <option value="deactivate">{{Désactiver}}</option>
                    </select>
                </div>
            </div>
        </div>
        <legend>Programmation</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Début}}</label>
            <div class="col-sm-2">
                <input type="text" class="calendarAttr form-control datetimepicker" data-l1key="startDate" />
            </div>
            <label class="col-sm-1 control-label">{{Fin}}</label>
            <div class="col-sm-2">
                <input type="text" class="calendarAttr form-control datetimepicker" data-l1key="endDate" />
            </div>
            <div class="col-sm-1">
                <a class="btn btn-default calendarAction" data-action="allDay"><i class="fa fa-history"></i> Toute la journée</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Répété}}</label>
            <div class="col-sm-3">
                <input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="enable" />
            </div>
        </div>

        <div class="div_repeatOption" style="display : none;">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Répéter tous les}}</label>
                <div class="col-sm-1">
                    <input class="calendarAttr form-control" data-l1key="repeat" data-l2key="freq" />
                </div>
                <div class="col-sm-2">
                    <select class="calendarAttr form-control" data-l1key="repeat" data-l2key="unite" >
                        <option value="minutes">{{Minutes(s)}}</option>
                        <option value="hours">{{Heure(s)}}</option>
                        <option value="days" selected>{{Jour(s)}}</option>
                        <option value="month">{{Mois}}</option>
                        <option value="years">{{Année(s)}}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Uniquement les}}</label>
                <div class="col-sm-9">
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='1' checked /> {{Lundis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='2' checked /> {{Mardis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='3' checked /> {{Mercredis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='4' checked /> {{Jeudis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='5' checked /> {{Vendredis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='6' checked /> {{Samedis}}
                    <input type='checkbox' class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='7' checked /> {{Dimanches}}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Restriction}}</label>
                <div class="col-sm-3">
                    <select class="calendarAttr form-control" data-l1key="repeat" data-l2key="nationalDay" >
                        <option value="all">{{Aucune}}</option>
                        <option value="exeptNationalDay">{{Tous sauf les jours fériés}}</option>
                        <option value="onlyNationalDay">{{Que les jours fériés}}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Jusqu'à}}</label>
                <div class="col-sm-3">
                    <input type="text" class="calendarAttr form-control datetimepicker" data-l1key="until" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Exclure (date sous forme 2014-04-08,2014-04-09...), vous pouvez spécifier une plage en séparant les 2 dates (les bornes) par des ":"}}</label>
                <div class="col-sm-3">
                    <input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDate" />
                </div>
            </div>
        </div>
    </fieldset>
</form>

<script>
    $('.calendarAttr[data-l1key=cmd_param][data-l2key=start_name]').on('change', function () {
        var html = jeedom.cmd.displayActionOption($(this).value());
        $('#div_eventEditCmdStart .options').empty().append(html);
    });
    $('.calendarAttr[data-l1key=cmd_param][data-l2key=start_type]').on('change', function () {
        $('.div_startType').hide();
        $('.div_start' + $(this).value()).show();
    });

    $('.calendarAttr[data-l1key=cmd_param][data-l2key=end_name]').on('change', function () {
        var html = jeedom.cmd.displayActionOption($(this).value());
        $('#div_eventEditCmdEnd .options').empty().append(html);
    });
    $('.calendarAttr[data-l1key=cmd_param][data-l2key=end_type]').on('change', function () {
        $('.div_endType').hide();
        $('.div_end' + $(this).value()).show();
    });

    $('.calendarAction[data-action=allDay]').on('click', function () {
        var startDate = $('.calendarAttr[data-l1key=startDate]').value().substr(0, 10);
        if (startDate == '') {
            var startDate = new Date();
            var y = startDate.getFullYear();
            var m = startDate.getMonth() + 1;
            var d = startDate.getDate();
            m = (m < 10) ? "0" + m : m;
            startDate = y + '-' + m + '-' + d;
        }
        $('.calendarAttr[data-l1key=startDate]').value(startDate + ' 00:00:00');
        var endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 1);
        var y = endDate.getFullYear();
        var m = endDate.getMonth() + 1;
        m = (m < 10) ? "0" + m : m;
        var d = endDate.getDate();
        $('.calendarAttr[data-l1key=endDate]').value(y + '-' + m + '-' + d + ' 00:00:00');
    });

    $('#bt_chooseIcon').on('click', function () {
        chooseIcon(function (_icon) {
            $('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').empty().append(_icon);
        });
    });

    $('.calendarAttr[data-l1key=repeat][data-l2key=enable]').on('change', function () {
        if ($(this).value() == 1) {
            $('#form_eventEdit .div_repeatOption').show();
        } else {
            $('#form_eventEdit .div_repeatOption').hide();
        }
    });

    if (calendarEvent != null && is_array(calendarEvent)) {
        $('#form_eventEdit').setValues(calendarEvent, '.calendarAttr');
        if (isset(calendarEvent.cmd_param) && isset(calendarEvent.cmd_param.start_name)) {
            jeedom.cmd.displayActionOption(calendarEvent.cmd_param.start_name, init(calendarEvent.cmd_param.start_options), function (html) {
                $('#div_eventEditCmdStart .options').empty().append(html);
            });
        }
        if (isset(calendarEvent.cmd_param) && isset(calendarEvent.cmd_param.end_name)) {
            jeedom.cmd.displayActionOption(calendarEvent.cmd_param.end_name, init(calendarEvent.cmd_param.end_options), function (html) {
                $('#div_eventEditCmdEnd .options').empty().append(html);
            });
        }
    }

    $("#form_eventEdit").delegate(".listCmdAction", 'click', function () {
        var el = $(this).closest('.form-group').find('.calendarAttr[data-l1key=cmd_param][data-l2key=' + $(this).attr('data-target') + ']');
        jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
            el.value(result.human);
        });
    });


    $("#form_eventEdit").delegate(".listScenario", 'click', function () {
        var el = $(this).closest('.form-group').find('.calendarAttr[data-l1key=cmd_param][data-l2key=' + $(this).attr('data-target') + ']');
        jeedom.scenario.getSelectModal({}, function (result) {
            el.value(result.human);
        });
    });

    $('.datetimepicker').datetimepicker({lang: 'fr',
        i18n: {
            fr: {
                months: [
                    'Janvier', 'Février', 'Mars', 'Avril',
                    'Mai', 'Juin', 'Juillet', 'Aout',
                    'Septembre', 'Octobre', 'Novembre', 'Décembre',
                ],
                dayOfWeek: [
                    "Di", "Lu", "Ma", "Me",
                    "Je", "Ve", "Sa",
                ]
            }
        },
        format: 'Y-m-d H:i:00',
        step: 15
    });
    $('#md_eventEditSave').on('click', function () {
        var calendarEvent = $('#form_eventEdit').getValues('.calendarAttr');
        calendarEvent = calendarEvent[0];
        var option = $('#div_eventEditCmdStart').getValues('.expressionAttr');
        if (isset(option[0]) && isset(option[0].options)) {
            calendarEvent.cmd_param.start_options = option[0].options;
        }
        var option = $('#div_eventEditCmdEnd').getValues('.expressionAttr');
        if (isset(option[0]) && isset(option[0].options)) {
            calendarEvent.cmd_param.end_options = option[0].options;
        }
        $.ajax({
            type: 'POST',
            url: 'plugins/calendar/core/ajax/calendar.ajax.php',
            data: {
                action: 'saveEvent',
                event: json_encode(calendarEvent)
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error, $('#div_eventEditAlert'));
            },
            success: function (data) {
                if (data.state != 'ok') {
                    $('#div_eventEditAlert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_eventEditAlert').showAlert({message: '{{Evènement ajouté avec succès}}', level: 'success'});
                calendar.fullCalendar('refetchEvents');
                updateEventList();
                $('#form_eventEdit').closest("div.ui-dialog-content").dialog("close");
            }
        });
    });

    $('#md_eventEditDuplicate').on('click', function () {
        $('.calendarAttr[data-l1key=id]').value('');
        $('#md_eventEditRemove').hide();
        $(this).hide();
    });

    $('#md_eventEditRemove').on('click', function () {
        if (calendarEvent != null && is_array(calendarEvent) && calendarEvent.repeat.enable == 1 && dateEvent != null && dateEvent != '') {
            bootbox.dialog({
                message: "{{Voulez vous supprimer cette occurence ou l\'evenement ?}}",
                title: "Suppression",
                buttons: {
                    cancel: {
                        label: "{{Annuler}}",
                        className: "btn-default",
                        callback: function () {

                        }
                    },
                    success: {
                        label: "{{Occurence}}",
                        className: "btn-success",
                        callback: function () {
                            $.ajax({
                                type: 'POST',
                                url: 'plugins/calendar/core/ajax/calendar.ajax.php',
                                data: {
                                    action: 'removeOccurence',
                                    id: $('.calendarAttr[data-l1key=id]').value(),
                                    date: dateEvent
                                },
                                dataType: 'json',
                                error: function (request, status, error) {
                                    handleAjaxError(request, status, error, $('#div_eventEditAlert'));
                                },
                                success: function (data) {
                                    if (data.state != 'ok') {
                                        $('#div_eventEditAlert').showAlert({message: data.result, level: 'danger'});
                                        return;
                                    }
                                    $('#div_eventEditAlert').showAlert({message: '{{Occurence supprimé avec success}}', level: 'success'});
                                    calendar.fullCalendar('refetchEvents');
                                    updateEventList();
                                    $('#form_eventEdit').closest("div.ui-dialog-content").dialog("close");
                                }
                            });
                        }
                    },
                    danger: {
                        label: "{{Evénement}}",
                        className: "btn-danger",
                        callback: function () {
                            $.ajax({
                                type: 'POST',
                                url: 'plugins/calendar/core/ajax/calendar.ajax.php',
                                data: {
                                    action: 'removeEvent',
                                    id: $('.calendarAttr[data-l1key=id]').value()
                                },
                                dataType: 'json',
                                error: function (request, status, error) {
                                    handleAjaxError(request, status, error, $('#div_eventEditAlert'));
                                },
                                success: function (data) {
                                    if (data.state != 'ok') {
                                        $('#div_eventEditAlert').showAlert({message: data.result, level: 'danger'});
                                        return;
                                    }
                                    $('#div_eventEditAlert').showAlert({message: '{{Evènement supprimé avec success}}', level: 'success'});
                                    calendar.fullCalendar('refetchEvents');
                                    updateEventList();
                                    $('#form_eventEdit').closest("div.ui-dialog-content").dialog("close");
                                }
                            });
                        }
                    },
                }
            });
        } else {
            bootbox.confirm('{{Etes-vous sûr de vouloir supprimer cette événement ?}}', function (result) {
                if (result) {
                    $.ajax({
                        type: 'POST',
                        url: 'plugins/calendar/core/ajax/calendar.ajax.php',
                        data: {
                            action: 'removeEvent',
                            id: $('.calendarAttr[data-l1key=id]').value()
                        },
                        dataType: 'json',
                        error: function (request, status, error) {
                            handleAjaxError(request, status, error, $('#div_eventEditAlert'));
                        },
                        success: function (data) {
                            if (data.state != 'ok') {
                                $('#div_eventEditAlert').showAlert({message: data.result, level: 'danger'});
                                return;
                            }
                            $('#div_eventEditAlert').showAlert({message: '{{Evènement supprimé avec success}}', level: 'success'});
                            calendar.fullCalendar('refetchEvents');
                            updateEventList();
                            $('#form_eventEdit').closest("div.ui-dialog-content").dialog("close");
                        }
                    });
                }
            });
        }
    });
</script>

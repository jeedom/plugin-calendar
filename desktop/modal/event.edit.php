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

<a class="btn btn-success pull-right" id="md_eventEditSave" style="color: white;"><i class="fas fa-check-circle"></i> {{Enregistrer}}</a>
<?php
if (is_object($event)) {
	echo '<a class="btn btn-warning pull-right" id="md_eventEditDuplicate" style="color: white;"><i class="far fa-clone"></i> {{Duplication}}</a>';
	echo '<a class="btn btn-danger pull-right" id="md_eventEditRemove" style="color: white;"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>';
}
?>

<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#eventtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-list"></i> {{Evènement}}</a></li>
	<li role="presentation"><a id="bt_calendartab" href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-calendar"></i> {{Action}}</a></li>
	<li role="presentation"><a id="bt_calendartab" href="#programmingtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-cogs"></i> {{Programmation}}</a></li>
</ul>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;" id="div_eventEdit">
	<div role="tabpanel" class="tab-pane active" id="eventtab">
		<br/>
		<form class="form-horizontal">
			<fieldset>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Nom de l'évènement}}</label>
					<div class="col-sm-3">
						<input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='eventName' />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Icône}}</label>
					<div class="col-sm-3">
						<input type="text" class="calendarAttr form-control" data-l1key="eqLogic_id" style="display: none;" value="<?php echo init('eqLogic_id') ?>"/>
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
						<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='transparent'/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Couleur du texte}}</label>
					<div class="col-sm-3">
						<input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='text_color' value='#FFFFFF' />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Masquer dans le dashboard}}</label>
					<div class="col-sm-3">
						<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='noDisplayOnDashboard'/>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="actiontab">
				<br/>
				<form class="form-horizontal" id="div_eventEdit">
					<fieldset>
						
						<legend><i class="fa fa-flag"></i>  {{Début}} <a class="btn btn-xs btn-success bt_addAction pull-right" data-type="start"><i class="fas fa-plus-circle"></i></a></legend>
						<div id="div_start"></div>
					</fieldset>
					<fieldset>
						<legend><i class="fa fa-flag-checkered"></i>  {{Fin}} <a class="btn btn-xs btn-success bt_addAction pull-right" data-type="end"><i class="fas fa-plus-circle"></i></a></legend>
						<div id="div_end"></div>
					</fieldset>
				</form>
			</div>
			
			
			<div role="tabpanel" class="tab-pane" id="programmingtab">
				<br/>
				<form class="form-horizontal" id="div_eventEdit">
					<fieldset>
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
								<a class="btn btn-default calendarAction" data-action="allDay"><i class="fa fa-history"></i> {{Toute la journée}}</a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Inclure par un autre agenda}}</label>
							<div class="col-sm-3">
								<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="includeDateFromCalendar">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (calendar::byType('calendar') as $calendar) {
										foreach ($calendar->getEvents() as $eventCalendar) {
											if (!is_object($event) || $event->getId() != $eventCalendar->getId()) {
												if ($eventCalendar->getCmd_param('eventName') != '') {
													echo '<option value="' . $eventCalendar->getId() . '">' . $calendar->getName() . ' - ' . $eventCalendar->getCmd_param('eventName') . '</option>';
												} else {
													echo '<option value="' . $eventCalendar->getId() . '">' . $calendar->getName() . ' - ' . $eventCalendar->getCmd_param('name') . '</option>';
												}
											}
										}
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Inclure (date sous forme AAAA-MM-JJ,AAAA-MM-JJ...)}}</label>
							<div class="col-sm-3">
								<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="includeDate" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Exclure (date sous forme AAAA-MM-JJ,AAAA-MM-JJ...)}}</label>
							<div class="col-sm-3">
								<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDate" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Répété}}</label>
							<div class="col-sm-3">
								<input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="enable"/>
							</div>
						</div>
						
						<div class="div_repeatOption" style="display : none;">
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Mode de répétition}}</label>
								<div class="col-sm-3">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="mode" >
										<option value="simple">{{Répétition simple}}</option>
										<option value="advance">{{Répétition le premier,deuxieme...}}</option>
									</select>
								</div>
							</div>
							<div class="repeatMode advance" style="display : none;">
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Le}}</label>
									<div class="col-sm-2">
										<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="positionAt" >
											<option value="first">{{Premier}}</option>
											<option value="second">{{Deuxième}}</option>
											<option value="third">{{Troisième}}</option>
											<option value="fourth">{{Quatrieme}}</option>
											<option value="last">{{Dernier}}</option>
										</select>
									</div>
									<div class="col-sm-2">
										<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="day" >
											<option value="monday">{{Lundi}}</option>
											<option value="tuesday">{{Mardi}}</option>
											<option value="wednesday">{{Mercredi}}</option>
											<option value="thursday">{{Jeudi}}</option>
											<option value="friday">{{Vendredi}}</option>
											<option value="saturday">{{Samedi}}</option>
											<option value="sunday">{{Dimanche}}</option>
										</select>
									</div>
									<label class="col-sm-1 control-label">{{du mois}}</label>
								</div>
							</div>
							<div class="repeatMode simple">
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
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='1' checked/>{{Lundis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='2' checked/>{{Mardis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='3' checked/>{{Mercredis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='4' checked/>{{Jeudis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='5' checked/>{{Vendredis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='6' checked/>{{Samedis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='7' checked/>{{Dimanches}}</label>
									</div>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Restriction}}</label>
								<div class="col-sm-3">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="nationalDay" >
										<option value="all">{{Aucune}}</option>
										<option value="exeptNationalDay">{{Tous sauf les jours fériés}}</option>
										<option value="onlyNationalDay">{{Que les jours fériés}}</option>
										<option value="onlyEven">{{Que les semaines paires}}</option>
										<option value="onlyOdd">{{Que les semaines impaires}}</option>
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
								<label class="col-sm-3 control-label">{{Exclure par un autre agenda}}</label>
								<div class="col-sm-3">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDateFromCalendar">
										<option value="">{{Aucun}}</option>
										<?php
										foreach (calendar::byType('calendar') as $calendar) {
											foreach ($calendar->getEvents() as $eventCalendar) {
												if (!is_object($event) || $event->getId() != $eventCalendar->getId()) {
													if ($eventCalendar->getCmd_param('eventName') != '') {
														echo '<option value="' . $eventCalendar->getId() . '">' . $calendar->getName() . ' - ' . $eventCalendar->getCmd_param('eventName') . '</option>';
													} else {
														echo '<option value="' . $eventCalendar->getId() . '">' . $calendar->getName() . ' - ' . $eventCalendar->getCmd_param('name') . '</option>';
													}
												}
											}
										}
										?>
									</select>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		
		<script>
		$("#div_start").sortable({axis: "y", cursor: "move", items: ".start", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
		$("#div_end").sortable({axis: "y", cursor: "move", items: ".end", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
		
		
		setTimeout(function(){
			initCheckBox();
		}, 100);
		
		
		function addAction(_action, _type, _name) {
			if (!isset(_action)) {
				_action = {};
			}
			if (!isset(_action.options)) {
				_action.options = {};
			}
			var div = '<div class="' + _type + ' row" style="margin-bottom:5px">';
			div += '<div class="col-sm-1">';
			div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour desactiver l\'action}}" />';
			div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'éxecute en parrallele des autres actions}}" />';
			div += '</div>';
			div += '<div class="col-sm-4">';
			div += '<div class="input-group">';
			div += '<span class="input-group-btn">';
			div += '<a class="btn btn-default btn-sm bt_removeAction" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
			div += '</span>';
			div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
			div += '<span class="input-group-btn">';
			div += '<a class="btn btn-default btn-sm listCmdAction" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
			div += '</span>';
			div += '</div>';
			div += '</div>';
			div += '<div class="col-sm-7 actionOptions">';
			div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
			div += '</div>';
			$('#div_' + _type).append(div);
			$('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
			taAutosize();
		}
		
		$("#div_start").sortable({axis: "y", cursor: "move", items: ".start", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
		
		$("#div_end").sortable({axis: "y", cursor: "move", items: ".end", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
		
		$('body').undelegate(".cmdAction.expressionAttr[data-l1key=cmd]", 'focusout').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
			var type = $(this).attr('data-type')
			var expression = $(this).closest('.' + type).getValues('.expressionAttr');
			var el = $(this);
			jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
				el.closest('.' + type).find('.actionOptions').html(html);
				taAutosize();
			})
		});
		
		$("body").undelegate(".listCmdAction", 'click').delegate(".listCmdAction", 'click', function () {
			var type = $(this).attr('data-type');
			var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
			jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
				el.value(result.human);
				jeedom.cmd.displayActionOption(el.value(), '', function (html) {
					el.closest('.' + type).find('.actionOptions').html(html);
					taAutosize();
				});
			});
		});
		
		$("body").undelegate('.bt_removeAction', 'click').delegate('.bt_removeAction', 'click', function () {
			var type = $(this).attr('data-type');
			$(this).closest('.' + type).remove();
		});
		
		$('.bt_addAction').off('click').on('click',function(){
			addAction({}, $(this).attr('data-type'), '{{Action}}');
		});
		
		$('.calendarAction[data-action=allDay]').off('click').on('click', function () {
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
			$('.calendarAttr[data-l1key=endDate]').value(startDate + ' 23:59:00');
		});
		
		$('#bt_chooseIcon').on('click', function () {
			chooseIcon(function (_icon) {
				$('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').empty().append(_icon);
			});
		});
		
		
		$("body").undelegate(".calendarAttr[data-l1key=repeat][data-l2key=enable]", 'change').delegate('.calendarAttr[data-l1key=repeat][data-l2key=enable]','change', function () {
			if ($(this).value() == 1) {
				$('#div_eventEdit .div_repeatOption').show();
			} else {
				$('#div_eventEdit .div_repeatOption').hide();
			}
		});
		
		$("body").undelegate(".calendarAttr[data-l1key=repeat][data-l2key=mode]", 'change').delegate('.calendarAttr[data-l1key=repeat][data-l2key=mode]','change', function () {
			$('#div_eventEdit .repeatMode').hide();
			$('#div_eventEdit .repeatMode.'+$(this).value()).show();
		});
		
		if (calendarEvent != null && is_array(calendarEvent)) {
			$('#div_eventEdit').setValues(calendarEvent, '.calendarAttr');
			$(".calendarAttr[data-l1key=repeat][data-l2key=enable]").trigger('change');
			if (isset(calendarEvent.cmd_param.start)) {
				for (var i in calendarEvent.cmd_param.start) {
					addAction(calendarEvent.cmd_param.start[i], 'start', '{{Action}}');
				}
			}
			if (isset(calendarEvent.cmd_param.end)) {
				for (var i in calendarEvent.cmd_param.end) {
					addAction(calendarEvent.cmd_param.end[i], 'end', '{{Action}}');
				}
			}
		}
		
		$('.datetimepicker').datetimepicker({
			lang: 'fr',
			dayOfWeekStart : 1,
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
			var calendarEvent = $('#div_eventEdit').getValues('.calendarAttr');
			calendarEvent = calendarEvent[0];
			calendarEvent.cmd_param.start = $('#div_start .start').getValues('.expressionAttr');
			calendarEvent.cmd_param.end = $('#div_end .end').getValues('.expressionAttr');
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
					try{
						calendar.fullCalendar('refetchEvents');
					}catch (e) {
						
					}
					updateEventList();
					$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
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
										$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
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
										$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
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
								$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
							}
						});
					}
				});
			}
		});
	</script>
	
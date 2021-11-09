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
	throw new Exception('{{L\'id de l\'équipement ne peut être vide}} : ' . init('eqLogic_id'));
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	throw new Exception('{{Aucun équipement associé à l\'id}} : ' . init('eqLogic_id'));
}
$event = null;
if (init('id') != '') {
	$event = jeedom::toHumanReadable(calendar_event::byId(init('id')));
	if (!is_object($event)) {
		throw new Exception('{{Event id non trouvé}} : ' . init('id'));
	}
	sendVarToJS('calendarEvent', utils::o2a($event));
	sendVarToJS('dateEvent', init('date'));
} else {
	sendVarToJS('calendarEvent', null);
	sendVarToJS('dateEvent', null);
}
$calendars = calendar::byType('calendar');
?>
<div id='div_eventEditAlert' style="display: none;"></div>
<div class="input-group pull-right" style="display:inline-flex">
	<span class="input-group-btn">
		<?php	if (is_object($event)) { ?>
			<a class="btn btn-sm btn-default roundedLeft" id="md_eventEditDuplicate"><i class="far fa-clone"></i> {{Dupliquer}}
			</a><a class="btn btn-sm btn-success" id="md_eventEditSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}
			</a><a class="btn btn-sm btn-danger roundedRight" id="md_eventEditRemove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
		<?php	}	else {
			echo '<a class="btn btn-sm btn-success" id="md_eventEditSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}</a>';
		} ?>
	</span>
</div>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#eventtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-calendar-day"></i> {{Evènement}}</a></li>
	<li role="presentation"><a id="bt_calendartab" href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-hand-sparkles"></i> {{Actions}}</a></li>
	<li role="presentation"><a id="bt_calendartab" href="#programmingtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-tools"></i> {{Programmation}}</a></li>
</ul>

<div class="tab-content" id="div_eventEdit">
	<div role="tabpanel" class="tab-pane active" id="eventtab">
		<form class="form-horizontal">
			<fieldset>
				<div class="col-lg-6">
					<legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
					<div class="form-group">
						<label class="col-sm-4 control-label">{{Nom de l'évènement}}</label>
						<div class="col-sm-7">
							<input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='eventName' />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 checkbox-inline control-label">{{Masquer sur le widget}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour que cet évènement ne soit pas visible sur le widget}}"></i></sup>
						</label>
						<div class="col-sm-7">
							<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='noDisplayOnDashboard'/>
						</div>
					</div>
				</div>

				<div class="col-lg-6">
					<legend><i class="fas fa-desktop"></i> {{Affichage}}</legend>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Icône}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Choisir l'icône de l'évènement}}"></i></sup>
						</label>
						<div class="col-sm-1">
							<input type="text" class="calendarAttr form-control" data-l1key="eqLogic_id" style="display: none;" value="<?php echo init('eqLogic_id') ?>"/>
							<input type="text" class="calendarAttr form-control" data-l1key="id" style="display: none;" />
							<div class="calendarAttr" data-l1key="cmd_param" data-l2key="icon" ></div>
						</div>
						<div class="col-sm-4">
							<a class="btn btn-default btn-sm" id="bt_chooseIcon"><i class="fas fa-icons"></i> {{Choisir une icône}}</a>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Couleur de fond}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Choisir la couleur de fond de l'évènement}}"></i></sup>
						</label>
						<div class="col-sm-1">
							<input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='color' value='#2980b9' />
						</div>
						<div class="col-sm-3">
							<label class="checkbox-inline">
								<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='transparent'/>
								{{Transparent}}
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Couleur du texte}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Choisir la couleur du texte de l'évènement}}"></i></sup>
						</label>
						<div class="col-sm-7">
							<input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='text_color' value='#FFFFFF' />
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<div role="tabpanel" class="tab-pane" id="actiontab">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn"></span>
			<a class="btn btn-xs btn-info bt_addAction roundedLeft" data-type="start"><i class="fas fa-plus-circle"></i> {{Action de début}}</a>
			<a class="btn btn-xs btn-warning bt_addAction roundedRight" data-type="end"><i class="fas fa-plus-circle"></i> {{Action de fin}}</a>
		</div>
		<br>
		<br>
		<form class="form-horizontal">
			<fieldset>
				<div id="div_start" class="col-xs-12" style="padding-bottom:10px;margin-bottom:15px;background-color:rgb(var(--bg-color));">
					<legend><i class="fas fa-flag icon_blue"></i> {{Action(s) de début}}</legend>
				</div>
				<div id="div_end" class="col-xs-12" style="padding-bottom:10px;margin-bottom:15px;background-color:rgb(var(--bg-color));">
					<legend><i class="fas fa-flag-checkered icon_orange"></i> {{Action(s) de fin}}</legend>
				</div>
			</fieldset>
		</form>
	</div>

	<div role="tabpanel" class="tab-pane" id="programmingtab">
		<form class="form-horizontal">
			<fieldset>
				<div class="col-lg-6">
					<legend><i class="fas fa-calendar-week"></i> {{Définition de l'évènement}}</legend>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Dates}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Renseigner les dates de début et de fin de l'évènement}}"></i></sup>
						</label>
						<div class="col-sm-8 input-group">
							<span class="input-group-addon roundedLeft">{{Début}}</span>
							<input type="text" class="calendarAttr form-control datetimepicker" data-l1key="startDate" />
							<span class="input-group-addon">{{Fin}}</span>
							<input type="text" class="calendarAttr form-control datetimepicker roundedLeft" data-l1key="endDate" />
							<span class="input-group-btn">
								<a class="btn btn-default calendarAction roundedRight" data-action="allDay" title="{{Toute la journée}}"><i class="fas fa-history"></i></a>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">{{Inclure par date}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Dates sous forme}} AAAA-MM-JJ,AAAA-MM-JJ {{ou plage de dates}} AAAA-MM-JJ:AAAA-MM-JJ"></i></sup>
						</label>
						<div class="col-sm-8">
							<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="includeDate" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Inclure par agenda}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Inclure des dates selon les évènements d'un agenda}}"></i></sup>
						</label>
						<div class="col-sm-4">
							<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="includeDateFromCalendar">
								<option value="">{{Aucun}}</option>
								<?php foreach ($calendars as $calendar) {
									echo '<option value="' . $calendar->getId() . '">' . $calendar->getName() . '</option>';
								} ?>
							</select>
						</div>
						<?php	foreach ($calendars as $calendar) {
							echo '<div class="col-sm-4 hidden" data-calendar_id="'.$calendar->getId().'">';
							echo '<select class="calendarAttr form-control">';
							echo '<option value="all">{{Tous}}</option>';
							foreach ($calendar->getEvents() as $eventCalendar) {
								if (!is_object($event) || $event->getId() != $eventCalendar->getId()) {
									if ($eventCalendar->getCmd_param('eventName') != '') {
										echo '<option value="' . $eventCalendar->getId() . '">' . $eventCalendar->getCmd_param('eventName') . '</option>';
									} else {
										echo '<option value="' . $eventCalendar->getId() . '">' . $eventCalendar->getCmd_param('name') . '</option>';
									}
								}
							}
							echo '</select>';
							echo '</div>';
						} ?>
					</div>
					<div class="div_repeatOption" style="display:none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Exclure par date}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Dates sous forme}} AAAA-MM-JJ,AAAA-MM-JJ {{ou plage de dates}} AAAA-MM-JJ:AAAA-MM-JJ"></i></sup>
							</label>
							<div class="col-sm-8">
								<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDate" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Exclure par agenda}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Exclure des dates selon les évènements d'un agenda}}"></i></sup>
							</label>
							<div class="col-sm-4">
								<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDateFromCalendar">
									<option value="">{{Aucun}}</option>
									<?php foreach ($calendars as $calendar) {
										echo '<option value="' . $calendar->getId() . '">' . $calendar->getName() . '</option>';
									} ?>
								</select>
							</div>
							<?php foreach ($calendars as $calendar) {
								echo '<div class="col-sm-4 hidden" data-calendar_id="'.$calendar->getId().'">';
								echo '<select class="calendarAttr form-control">';
								echo '<option value="all">{{Tous}}</option>';
								foreach ($calendar->getEvents() as $eventCalendar) {
									if (!is_object($event) || $event->getId() != $eventCalendar->getId()) {
										if ($eventCalendar->getCmd_param('eventName') != '') {
											echo '<option value="' . $eventCalendar->getId() . '">' . $eventCalendar->getCmd_param('eventName') . '</option>';
										} else {
											echo '<option value="' . $eventCalendar->getId() . '">' . $eventCalendar->getCmd_param('name') . '</option>';
										}
									}
								}
								echo '</select>';
								echo '</div>';
							} ?>
						</div>
					</div>
				</div>

				<div class="col-lg-6">
					<legend><i class="fas fa-redo-alt"></i> {{Répétition de l'évènement}}</legend>
					<div class="form-group">
						<label class="col-sm-3 control-label">{{Activer}}
							<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour activer les options de répétition de l'évènement}}"></i></sup>
						</label>
						<div class="col-sm-8">
							<input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="enable"/>
						</div>
					</div>
					<br>

					<div class="div_repeatOption" style="display:none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Mode}}</label>
							<div class="col-sm-8">
								<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="mode" >
									<option value="simple">{{Répétition simple}}</option>
									<option value="advance">{{Répétition avancée}}</option>
								</select>
							</div>
						</div>
						<div class="repeatMode advance" style="display:none;">
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Fréquence}}</label>
								<div class="col-sm-8 input-group">
									<span class="input-group-addon roundedLeft">{{le}}</span>
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="positionAt" >
										<option value="first">{{Premier}}</option>
										<option value="second">{{Deuxième}}</option>
										<option value="third">{{Troisième}}</option>
										<option value="fourth">{{Quatrième}}</option>
										<option value="last">{{Dernier}}</option>
									</select>
									<span class="input-group-addon"></span>
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="day" >
										<option value="monday">{{Lundi}}</option>
										<option value="tuesday">{{Mardi}}</option>
										<option value="wednesday">{{Mercredi}}</option>
										<option value="thursday">{{Jeudi}}</option>
										<option value="friday">{{Vendredi}}</option>
										<option value="saturday">{{Samedi}}</option>
										<option value="sunday">{{Dimanche}}</option>
									</select>
									<span class="input-group-addon roundedRight">{{du mois}}</span>
								</div>
							</div>
						</div>

						<div class="repeatMode simple">
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Répéter tous les}}</label>
								<div class="col-sm-3">
									<input type="number" class="calendarAttr form-control" data-l1key="repeat" data-l2key="freq">
								</div>
								<div class="col-sm-5">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="unite" >
										<option value="minutes">{{Minute(s)}}</option>
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
									<br>
									<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='6' checked/>{{Samedis}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='7' checked/>{{Dimanches}}</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">{{Jusqu'à}}</label>
							<div class="col-sm-8">
								<input type="text" class="calendarAttr form-control datetimepicker" data-l1key="until" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Restriction}}</label>
							<div class="col-sm-8">
								<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="nationalDay" >
									<option value="all">{{Aucune}}</option>
									<option value="exeptNationalDay">{{Tous sauf les jours fériés}}</option>
									<option value="onlyNationalDay">{{Uniquement les jours fériés}}</option>
									<option value="onlyEven">{{Uniquement les semaines paires}}</option>
									<option value="onlyOdd">{{Uniquement les semaines impaires}}</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<script>
$(function() {
	$('.calendarAttr').on('change', function() {
		modifyWithoutSave = true
	})
})

function addAction(_action, _type) {
	if (!isset(_action)) {
		_action = {};
	}
	if (!isset(_action.options)) {
		_action.options = {};
	}
	var div = '<div class="' + _type + ' row" style="margin-bottom:5px">';
	div += '<div class="col-sm-1">';
	div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher la case pour désactiver l\'action}}" />';
	div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher la case pour que la commande s\'exécute en parallèle des autres actions}}" />';
	div += '</div>';
	div += '<div class="col-sm-4">';
	div += '<div class="input-group">';
	div += '<span class="input-group-btn">';
	div += '<a class="btn btn-default btn-sm bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
	div += '</span>';
	div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
	div += '<span class="input-group-btn">';
	div += '<a class="btn btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>';
	div += '<a class="btn btn-default btn-sm listCmdAction roundedRight" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
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

$('body').off('focusout',".cmdAction.expressionAttr[data-l1key=cmd]").on('focusout','.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
	var type = $(this).attr('data-type')
	var expression = $(this).closest('.' + type).getValues('.expressionAttr');
	var el = $(this);
	jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
		el.closest('.' + type).find('.actionOptions').html(html);
		taAutosize();
	})
});

$("body").off('click',".listAction").on('click',".listAction",  function () {
	var type = $(this).attr('data-type');
	var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
	jeedom.getSelectActionModal({}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('.' + type).find('.actionOptions').html(html);
			taAutosize();
		});
	});
});

$("body").off('click',".listCmdAction").on('click',".listCmdAction", function () {
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

$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',  function () {
	var type = $(this).attr('data-type');
	$(this).closest('.' + type).remove();
});

$('.bt_addAction').off('click').on('click',function(){
	addAction({}, $(this).attr('data-type'));
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

$("body").off( 'change',".calendarAttr[data-l1key=repeat][data-l2key=enable]").on('change','.calendarAttr[data-l1key=repeat][data-l2key=enable]', function () {
	if ($(this).value() == 1) {
		$('#div_eventEdit .div_repeatOption').show();
	} else {
		$('#div_eventEdit .div_repeatOption').hide();
	}
});

$("body").off('change',".calendarAttr[data-l1key=repeat][data-l2key=mode]").on('change','.calendarAttr[data-l1key=repeat][data-l2key=mode]', function () {
	$('#div_eventEdit .repeatMode').hide();
	$('#div_eventEdit .repeatMode.'+$(this).value()).show();
});

$(".calendarAttr[data-l1key=repeat][data-l2key=includeDateFromCalendar], .calendarAttr[data-l1key=repeat][data-l2key=excludeDateFromCalendar]").on('change', function () {
	$(this).parent().siblings('div').addClass('hidden').find('select').removeAttr('data-l1key').removeAttr('data-l2key')
	if ($(this).value() != '') {
		$(this).parent().siblings('div[data-calendar_id='+$(this).value()+']').removeClass('hidden').find('select').attr({'data-l1key': 'repeat', 'data-l2key': $(this).attr('data-l2key').replace('Calendar', 'Event')})
	}
})

if (calendarEvent != null && is_array(calendarEvent)) {
	$('#div_eventEdit').setValues(calendarEvent, '.calendarAttr');
	$(".calendarAttr[data-l1key=repeat][data-l2key=enable]").trigger('change');
	if (isset(calendarEvent.cmd_param.start)) {
		for (var i in calendarEvent.cmd_param.start) {
			addAction(calendarEvent.cmd_param.start[i], 'start');
		}
	}
	if (isset(calendarEvent.cmd_param.end)) {
		for (var i in calendarEvent.cmd_param.end) {
			addAction(calendarEvent.cmd_param.end[i], 'end');
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
				'Mai', 'Juin', 'Juillet', 'Août',
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
			modifyWithoutSave = false
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
			message: "{{Voulez vous supprimer cette occurence ou l'évènement ?}}",
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
								$('#div_eventEditAlert').showAlert({message: '{{Occurence supprimée avec succès}}', level: 'success'});
								calendar.fullCalendar('refetchEvents');
								updateEventList();
								$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
							}
						});
					}
				},
				danger: {
					label: "{{Evènement}}",
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
								$('#div_eventEditAlert').showAlert({message: '{{Evènement supprimé avec succès}}', level: 'success'});
								calendar.fullCalendar('refetchEvents');
								updateEventList();
								modifyWithoutSave = false
								$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
							}
						});
					}
				},
			}
		});
	} else {
		bootbox.confirm('{{Etes-vous sûr de vouloir supprimer cet évènement ?}}', function (result) {
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
						$('#div_eventEditAlert').showAlert({message: '{{Evènement supprimé avec succès}}', level: 'success'});
						calendar.fullCalendar('refetchEvents');
						updateEventList();
						modifyWithoutSave = false
						$('#div_eventEdit').closest("div.ui-dialog-content").dialog("close");
					}
				});
			}
		});
	}
});
</script>

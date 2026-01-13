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
	throw new Exception('401 - {{Accès non autorisé}}');
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
<div id="md_eventEdit" data-modalType="md_eventEdit">
	<div class="input-group pull-right" style="display:inline-flex">
		<span class="input-group-btn">
			<a class="btn btn-sm btn-default roundedLeft" id="md_eventEditDuplicate" style="display: none;"><i class="far fa-clone"></i> {{Dupliquer}}
			</a><a class="btn btn-sm btn-success" id="md_eventEditSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}
			</a><a class="btn btn-sm btn-danger roundedRight" id="md_eventEditRemove" style="display: none;"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
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
							<div class="col-sm-6">
								<input type="text" class="calendarAttr form-control" data-l1key="cmd_param" data-l2key='eventName'>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 checkbox-inline control-label">{{Masquer sur le widget}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour que cet évènement ne soit pas visible sur le widget}}"></i></sup>
							</label>
							<div class="col-sm-6">
								<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='noDisplayOnDashboard'>
							</div>
						</div>
					</div>

					<div class="col-lg-6">
						<legend><i class="fas fa-desktop"></i> {{Affichage}}</legend>
						<div class="form-group">
							<label class="col-sm-4 control-label">{{Icône}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Choisir l'icône de l'évènement}}"></i></sup>
							</label>
							<div class="col-sm-6">
								<input type="text" class="calendarAttr form-control" data-l1key="eqLogic_id" style="display: none;" value="<?php echo init('eqLogic_id') ?>">
								<input type="text" class="calendarAttr form-control" data-l1key="id" style="display: none;">
								<span class="calendarAttr" data-l1key="cmd_param" data-l2key="icon"></span>
								<a class="btn btn-default btn-sm" id="bt_chooseIcon"><i class="fas fa-icons"></i> {{Choisir une icône}}</a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">{{Couleur de fond}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Choisir la couleur de fond de l'évènement}}"></i></sup>
							</label>
							<div class="col-sm-6">
								<input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='color' value='#2980b9'>
								<label class="checkbox-inline">
									<input type="checkbox" class="calendarAttr" data-l1key="cmd_param" data-l2key='transparent'>
									{{Transparent}}
								</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">{{Couleur du texte}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Choisir la couleur du texte de l'évènement}}"></i></sup>
							</label>
							<div class="col-sm-6">
								<input type="color" class="calendarAttr" data-l1key="cmd_param" data-l2key='text_color' value='#FFFFFF'>
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
								<input type="text" class="calendarAttr form-control in_datepicker" data-l1key="startDate">
								<span class="input-group-addon">{{Fin}}</span>
								<input type="text" class="calendarAttr form-control in_datepicker" data-l1key="endDate">
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
								<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="includeDate">
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
							<?php foreach ($calendars as $calendar) {
								echo '<div class="col-sm-4 hidden" data-calendar_id="' . $calendar->getId() . '">';
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
									<input type="text" class="calendarAttr form-control" data-l1key="repeat" data-l2key="excludeDate">
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
									echo '<div class="col-sm-4 hidden" data-calendar_id="' . $calendar->getId() . '">';
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
								<input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="enable">
							</div>
						</div>
						<br>

						<div class="div_repeatOption" style="display:none;">
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Mode}}</label>
								<div class="col-sm-8">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="mode">
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
										<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="positionAt">
											<option value="first">{{Premier}}</option>
											<option value="second">{{Deuxième}}</option>
											<option value="third">{{Troisième}}</option>
											<option value="fourth">{{Quatrième}}</option>
											<option value="last">{{Dernier}}</option>
										</select>
										<span class="input-group-addon"></span>
										<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="day">
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
										<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="unite">
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
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='1' checked>{{Lundis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='2' checked>{{Mardis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='3' checked>{{Mercredis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='4' checked>{{Jeudis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='5' checked>{{Vendredis}}</label>
										<br>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='6' checked>{{Samedis}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="calendarAttr" data-l1key="repeat" data-l2key="excludeDay" data-l3key='7' checked>{{Dimanches}}</label>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-3 control-label">{{Jusqu'à}}</label>
								<div class="col-sm-8">
									<input type="text" class="calendarAttr form-control in_datepicker" data-l1key="until">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Restriction}}</label>
								<div class="col-sm-8">
									<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="nationalDay">
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
</div>
<script>
	setTimeout(function() {
		jeedomUtils.datePickerInit('Y-m-d H:i')
		document.querySelectorAll('.calendarAttr').forEach(_el => {
			_el.addEventListener('change', function() {
				jeeFrontEnd.modifyWithoutSave = true
			})
		})
  	}, 250)

	function addAction(_action, _type) {
		if (!isset(_action)) {
			_action = {}
		}
		if (!isset(_action.options)) {
			_action.options = {}
		}
		var div = '<div class="' + _type + ' row" style="margin-bottom:5px">'
		div += '<div class="col-sm-1">'
		div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher la case pour désactiver l\'action}}">'
		div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher la case pour que la commande s\'exécute en parallèle des autres actions}}">'
		div += '</div>'
		div += '<div class="col-sm-4">'
		div += '<div class="input-group">'
		div += '<span class="input-group-btn">'
		div += '<a class="btn btn-default btn-sm bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>'
		div += '</span>'
		div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '">'
		div += '<span class="input-group-btn">'
		div += '<a class="btn btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>'
		div += '<a class="btn btn-default btn-sm listCmdAction roundedRight" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>'
		div += '</span>'
		div += '</div>'
		div += '</div>'
		var actionOption_id = jeedomUtils.uniqId()
		div += '<div class="col-sm-7 actionOptions" id="' + actionOption_id + '">'
		div += '</div>'
		div += '</div>'
		document.getElementById('div_' + _type).insertAdjacentHTML('beforeend', div)
		let currentAction = document.querySelectorAll('.' + _type).last()
		currentAction.setJeeValues(_action, '.expressionAttr')
		currentAction.querySelector('.expressionAttr[data-l1key="cmd"]').jeeComplete({
			source: jeedom.scenario.autoCompleteAction,
			forceSingle: true
		})
		if (is_array(actionOptions)) {
			actionOptions.push({
				expression: init(_action.cmd),
				options: _action.options,
				id: actionOption_id
			})
		}
	}

	new Sortable(document.getElementById('div_start'), {
		delay: 50,
		delayOnTouchOnly: true,
		draggable: '.start',
		filter: '.expressionAttr, .btn',
		preventOnFilter: false,
		direction: 'vertical',
		chosenClass: 'dragSelected',
		onUpdate: function(evt) {
			jeeFrontEnd.modifyWithoutSave = true
		}
	})
	new Sortable(document.getElementById('div_end'), {
		delay: 50,
		delayOnTouchOnly: true,
		draggable: '.end',
		filter: '.expressionAttr, .btn',
		preventOnFilter: false,
		direction: 'vertical',
		chosenClass: 'dragSelected',
		onUpdate: function(evt) {
			jeeFrontEnd.modifyWithoutSave = true
		}
	})

	document.getElementById('actiontab').addEventListener('focusout', function(event) {
		if (_target = event.target.closest('.cmdAction.expressionAttr[data-l1key="cmd"]')) {
			var type = _target.getAttribute('data-type')
			var expression = _target.closest('.' + type).getJeeValues('.expressionAttr')
			jeedom.cmd.displayActionOption(_target.jeeValue(), init(expression[0].options), function(html) {
				_target.closest('.' + type).querySelector('.actionOptions').html(html)
				jeedomUtils.taAutosize()
			})
		}
    })

	document.getElementById('actiontab').addEventListener('click', function(event) {
		var _target = null
		if (_target = event.target.closest('.listAction')) {
			var type = _target.getAttribute('data-type')
			var el = _target.closest('.' + type).querySelector('.expressionAttr[data-l1key="cmd"]')
			jeedom.getSelectActionModal({}, function(result) {
				el.jeeValue(result.human)
				jeedom.cmd.displayActionOption(el.jeeValue(), '', function(html) {
					el.closest('.' + type).querySelector('.actionOptions').html(html)
					jeedomUtils.taAutosize()
				})
			})
			return
		}
		if (_target = event.target.closest('.listCmdAction')) {
			var type = _target.getAttribute('data-type')
			var el = _target.closest('.' + type).querySelector('.expressionAttr[data-l1key="cmd"]')
			jeedom.cmd.getSelectModal({ cmd: { type: 'action' } }, function(result) {
				el.jeeValue(result.human)
				jeedom.cmd.displayActionOption(el.jeeValue(), '', function(html) {
					el.closest('.' + type).querySelector('.actionOptions').html(html)
					jeedomUtils.taAutosize()
				})
			})
			return
		}
		if (_target = event.target.closest('.bt_removeAction')) {
			var type = _target.getAttribute('data-type')
			_target.closest('.' + type).remove()
			jeeFrontEnd.modifyWithoutSave = true
			return
		}
		if (_target = event.target.closest('.bt_addAction')) {
			var type = _target.getAttribute('data-type')
			addAction({}, type)
			return
		}
    })

	document.querySelector('.calendarAction[data-action=allDay]').addEventListener('click', function() {
		var startDate = document.querySelector('.calendarAttr[data-l1key=startDate]').jeeValue().substr(0, 10)
		if (startDate == '') {
			var startDate = new Date()
			var y = startDate.getFullYear()
			var m = startDate.getMonth() + 1
			var d = startDate.getDate()
			m = (m < 10) ? "0" + m : m
			d = (d < 10) ? "0" + d : d
			startDate = y + '-' + m + '-' + d
		}
		document.querySelector('.calendarAttr[data-l1key=startDate]').jeeValue(startDate + ' 00:00:00')
		document.querySelector('.calendarAttr[data-l1key=endDate]').jeeValue(startDate + ' 23:59:00')
    });

	document.getElementById('bt_chooseIcon').addEventListener('click', function() {
		jeedomUtils.chooseIcon(function(_icon) {
			document.querySelector('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').innerHTML = _icon
		})
	});

	document.querySelector('.calendarAttr[data-l1key=cmd_param][data-l2key=icon]').addEventListener('dblclick', function() {
		this.innerHTML = ''
	});

	document.querySelector('.calendarAttr[data-l1key=repeat][data-l2key=enable]').addEventListener('change', function(event) {
		if (event.target.jeeValue() == 1) {
			document.querySelectorAll('#div_eventEdit .div_repeatOption').seen()
		} else {
			document.querySelectorAll('#div_eventEdit .div_repeatOption').unseen()
		}
	});

	document.querySelector('.calendarAttr[data-l1key=repeat][data-l2key=mode]').addEventListener('change', function(event) {
		document.querySelectorAll('#div_eventEdit .repeatMode').unseen()
		document.querySelectorAll('#div_eventEdit .repeatMode.' + this.jeeValue()).seen()
	});
      
	[".calendarAttr[data-l1key=repeat][data-l2key=includeDateFromCalendar]", ".calendarAttr[data-l1key=repeat][data-l2key=excludeDateFromCalendar]"].forEach((_selector) => {
		document.querySelector(_selector).addEventListener('change', function(event) {
			let formGroup = this.parentNode.parentNode
			formGroup.querySelectorAll('div[data-calendar_id]').addClass('hidden')
			formGroup.querySelectorAll('div[data-calendar_id] select').forEach(_select => {
				_select.removeAttribute('data-l1key')
				_select.removeAttribute('data-l2key')
			})
			if (this.jeeValue() != '') {
				formGroup.querySelector('div[data-calendar_id="' + this.jeeValue() + '"]').removeClass('hidden')
				formGroup.querySelector('div[data-calendar_id="' + this.jeeValue() + '"] select').setAttribute('data-l1key', 'repeat')
				formGroup.querySelector('div[data-calendar_id="' + this.jeeValue() + '"] select').setAttribute('data-l2key', this.getAttribute('data-l2key').replace('Calendar', 'Event'))
			}
		})
	});

	if (calendarEvent != null && is_array(calendarEvent)) {
		document.getElementById('div_eventEdit').setJeeValues(calendarEvent, '.calendarAttr')
		document.querySelector('.calendarAttr[data-l1key=repeat][data-l2key=enable]').triggerEvent('change')
		actionOptions = []
		if (isset(calendarEvent.cmd_param.start)) {
			for (var i in calendarEvent.cmd_param.start) {
				addAction(calendarEvent.cmd_param.start[i], 'start')
			}
		}
		if (isset(calendarEvent.cmd_param.end)) {
			for (var i in calendarEvent.cmd_param.end) {
				addAction(calendarEvent.cmd_param.end[i], 'end')
			}
		}
		jeedom.cmd.displayActionsOption({
			params: actionOptions,
			async: false,
			error: function(error) {
				jeedomUtils.showAlert({
					message: error.message,
					level: 'danger'
				})
			},
      		success: function(data) {
				for (var i in data) {
					document.getElementById(data[i].id).html(data[i].html.html, true)
				}
				jeedomUtils.taAutosize()
			}
		})
		actionOptions = null
		document.getElementById('md_eventEditRemove').seen()
		document.getElementById('md_eventEditDuplicate').seen()
	}

	document.getElementById('md_eventEditSave').addEventListener('click', function() {
		var calendarEvent = document.getElementById('div_eventEdit').getJeeValues('.calendarAttr')
		calendarEvent = calendarEvent[0]
		calendarEvent.cmd_param.start = document.querySelectorAll('#div_start .start').getJeeValues('.expressionAttr')
		calendarEvent.cmd_param.end = document.querySelectorAll('#div_end .end').getJeeValues('.expressionAttr')
		domUtils.ajax({
			type: 'POST',
			url: 'plugins/calendar/core/ajax/calendar.ajax.php',
			data: {
				action: 'saveEvent',
				event: json_encode(calendarEvent)
			},
			dataType: 'json',
			error: function (request, status, error) {
				domUtils.handleAjaxError(request, status, error) // no attachTo with handleAjaxError
			},
			success: function(data) {
				if (data.state != "ok") {
					jeedomUtils.showAlert({
						message: data.result,
						level: "danger",
						attachTo: jeeDialog.get('#md_eventEdit', 'dialog'),
					})
					return
				}
				jeedomUtils.showAlert({ 
					message: (calendarEvent['id'] != '') ? '{{Evènement modifié avec succès}}' : '{{Evènement ajouté avec succès}}', 
					level: "success",
				})
				try {
					calendar.refetchEvents()
				} catch (e) {}
				updateEventList()
				jeeFrontEnd.modifyWithoutSave = false
				jeeDialog.get('#md_eventEdit').destroy()
			}
		})
	})

	document.getElementById('md_eventEditDuplicate')?.addEventListener('click', function() {
		document.querySelector('.calendarAttr[data-l1key=id]').jeeValue('')
		document.getElementById('md_eventEditRemove').unseen()
		this.unseen()
	})

	document.getElementById('md_eventEditRemove')?.addEventListener('click', function() {
		var eventId = document.querySelector('.calendarAttr[data-l1key=id]').jeeValue()
		if (calendarEvent != null && is_array(calendarEvent) && calendarEvent.repeat.enable == 1 && dateEvent != null && dateEvent != '') {
			jeeDialog.confirm({
				title: "{{Suppression}}",
				message: "{{Voulez vous supprimer cette occurrence ou l'évènement ?}}",
				defaultButtons: {},
				buttons: {
					cancel: {
						label: "{{Annuler}}",
						className: "btn-default",
						callback: {
							click: function(event) {
								var dialog = event.target.closest('div.jeeDialog')
								dialog._jeeDialog.close(dialog)
							}
						},
					},
					success: {
						label: "{{Occurrence}}",
						className: "success",
						callback: {
							click: function(event) {
								domUtils.ajax({
									type: 'POST',
									url: 'plugins/calendar/core/ajax/calendar.ajax.php',
									data: {
										action: 'removeOccurrence',
										id: eventId,
										date: dateEvent
									},
									dataType: 'json',
									error: function (request, status, error) {
										domUtils.handleAjaxError(request, status, error) // no attachTo with handleAjaxError
									},
									success: function(data) {
										if (data.state != 'ok') {
											jeedomUtils.showAlert({
												message: data.result,
												level: "danger",
												attachTo: jeeDialog.get('#md_eventEdit', 'dialog')
											})
											return
										}
										jeedomUtils.showAlert({
											message: '{{Occurrence supprimée avec succès}}',
											level: 'success',
										})
										calendar.refetchEvents()
										updateEventList()
										var dialog = event.target.closest('div.jeeDialog')
										dialog._jeeDialog.close(dialog)
										jeeDialog.get('#md_eventEdit').destroy()
									}
								})
							} 
						}
					},
					danger: {
						label: "{{Evènement}}",
						className: "danger",
						callback: {
							click: function(event) {
								domUtils.ajax({
									type: 'POST',
									url: 'plugins/calendar/core/ajax/calendar.ajax.php',
									data: {
										action: 'removeEvent',
										id: eventId
									},
									dataType: 'json',
									error: function (request, status, error) {
										domUtils.handleAjaxError(request, status, error) // no attachTo with handleAjaxError
									},
									success: function(data) {
										if (data.state != 'ok') {
											jeedomUtils.showAlert({
												message: data.result,
												level: "danger",
												attachTo: jeeDialog.get('#md_eventEdit', 'dialog')
											})
											return
										}
										jeedomUtils.showAlert({
											message: '{{Evènement supprimé avec succès}}',
											level: 'success',
										})
										calendar.refetchEvents()
										updateEventList()
										jeeFrontEnd.modifyWithoutSave = false
										var dialog = event.target.closest('div.jeeDialog')
										dialog._jeeDialog.close(dialog)
										jeeDialog.get('#md_eventEdit').destroy()
									}
								})
							}
						}
					}
				}
			})
		} else {
			jeeDialog.confirm({
				title: "{{Suppression}}",
				message: "{{Etes-vous sûr de vouloir supprimer cet évènement ?}}"
				},
				function(result) {
					if (result) {
						domUtils.ajax({
							type: 'POST',
							url: 'plugins/calendar/core/ajax/calendar.ajax.php',
							data: {
								action: 'removeEvent',
								id: eventId
							},
							dataType: 'json',
							error: function (request, status, error) {
								domUtils.handleAjaxError(request, status, error) // no attachTo with handleAjaxError
							},
							success: function(data) {
								if (data.state != 'ok') {
									jeedomUtils.showAlert({
										message: data.result,
										level: "danger",
										attachTo: jeeDialog.get('#md_eventEdit', 'dialog')
									})
									return
								}
								jeedomUtils.showAlert({
									message: '{{Evènement supprimé avec succès}}',
									level: 'success'
								})
								calendar.refetchEvents()
								updateEventList()
								jeeFrontEnd.modifyWithoutSave = false
								jeeDialog.get('#md_eventEdit').destroy()
							}
						})
					}
				}
			)
        }
	})
</script>
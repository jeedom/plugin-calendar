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
	throw new Exception("{{L'id de l'équipement ne peut être vide}} : " . init('eqLogic_id'));
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	throw new Exception("{{Aucun équipement associé à l'id}} : " . init('eqLogic_id'));
}
$event = null;
if (init('id') != '') {
	$event = jeedom::toHumanReadable(calendar_event::byId(init('id')));
	if (!is_object($event)) {
		throw new Exception('{{Event id non trouvé}} : ' . init('id'));
	}
	sendVarToJS('_calendarEvent', utils::o2a($event));
	sendVarToJS('_dateEvent', init('date'));
} else {
	sendVarToJS('_calendarEvent');
	sendVarToJS('_dateEvent');
}
$calendars = calendar::byType('calendar');
?>

<div class="input-group pull-right" style="display:inline-flex">
	<span class="input-group-btn">
		<?php if (is_object($event)) { ?>
			<a class="btn btn-sm btn-default roundedLeft" id="md_eventEditDuplicate"><i class="far fa-clone"></i> {{Dupliquer}}
			</a><a class="btn btn-sm btn-success" id="md_eventEditSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}
			</a><a class="btn btn-sm btn-danger roundedRight" id="md_eventEditRemove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
		<?php	} else {
			echo '<a class="btn btn-sm btn-success rounded" id="md_eventEditSave"><i class="fas fa-check-circle"></i> {{Enregistrer}}</a>';
		} ?>
	</span>
</div>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#eventtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-calendar-day"></i> {{Evènement}}</a></li>
	<li role="presentation"><a href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-hand-sparkles"></i> {{Actions}}</a></li>
	<li role="presentation"><a href="#programmingtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-tools"></i> {{Programmation}}</a></li>
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
							<input type="text" class="calendarAttr form-control hidden" data-l1key="eqLogic_id" value="<?php echo init('eqLogic_id') ?>">
							<input type="text" class="calendarAttr form-control hidden" data-l1key="id">
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
							<span class="input-group-addon roundedLeft">{{du}}</span>
							<input type="text" class="calendarAttr form-control in_datepicker" data-l1key="startDate">
							<span class="input-group-addon">{{au}}</span>
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
					<div class="div_repeatOption hidden">
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

					<div class="div_repeatOption hidden">
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Mode}}</label>
							<div class="col-sm-8">
								<select class="calendarAttr form-control" data-l1key="repeat" data-l2key="mode">
									<option value="simple">{{Répétition simple}}</option>
									<option value="advance">{{Répétition avancée}}</option>
								</select>
							</div>
						</div>
						<div class="repeatMode advance hidden">
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
									<span class="input-group-addon" style="padding: 0 0 !important;"></span>
									<select class=" calendarAttr form-control" data-l1key="repeat" data-l2key="day">
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

<?php
include_file('desktop', 'event.edit', 'js', 'calendar');
?>

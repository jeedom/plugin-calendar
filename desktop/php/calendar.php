<?php
if (!isConnect('admin')) {
	throw new Exception('401 - {{Accès non autorisé}}');
}
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css');
$plugin = plugin::byId('calendar');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<style>
	:root {
		--fc-button-bg-color: var(--btn-default-color);
		--fc-button-hover-bg-color: var(--btn-default-color);
		--fc-button-active-bg-color: var(--sc-formTxt-color);
		--fc-list-event-hover-bg-color: var(--el-defaultColor);
		--fc-page-bg-color: transparent;
		--fc-button-border-color: transparent;
		--fc-border-color: transparent;
	}

	#calendartab {
		height: 100%;
	}

	.fc .fc-button:not(:disabled):hover {
		color: var(--linkHoverLight-color) !important;
		opacity: .85 !important;
	}

	.fc .fc-button:focus,
	.fc .fc-button-primary:focus,
	.fc .fc-button-primary:not(:disabled).fc-button-active:focus {
		box-shadow: none;
	}

	.fc-event-main {
		margin-left: 2px;
	}

	.fc-list-event {
		cursor: pointer;
	}
</style>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="eqLogicThumbnailContainer">
			<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_healthcalendar">
				<i class="fas fa-medkit"></i>
				<br>
				<span>{{Santé}}</span>
			</div>
		</div>
		<legend><i class="fas fa-calendar-alt"></i> {{Mes agendas}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun Agenda trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '<span class="hiddenAsCard displayTableRight hidden">';
				echo '<span>' . count($eqLogic->getEvents()) . ' {{évènements}}</span>';
				echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
				echo '</span>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-primary btn-sm roundedLeft" id="bt_addEvent"><i class="fas fa-plus-circle"></i> {{Ajouter évènement}}
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#generaltab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Géneral}}</a></li>
			<li role="presentation"><a id="bt_calendartab" href="#calendartab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-calendar"></i> {{Agenda}}</a></li>
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="generaltab">
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'agenda}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement gCalendar}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '">' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-list"></i> {{Widget}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nombre de jours}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Nombre de jours maximum à afficher sur le widget}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetDay">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nombre d'évènements}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Nombre d'évènements maximum à afficher sur le widget}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetMaxEvent">
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<legend><i class="far fa-calendar-check"></i> {{Liste des évènements de l'agenda}}</legend>
							<div id="div_eventList"></div>
						</div>
					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="calendartab">
				<br>
				<div id="div_calendar"></div>
			</div>
		</div>
	</div>
</div>

<?php
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js');
include_file('3rdparty', 'fullcalendar/index.global.min', 'js', 'calendar');
include_file('3rdparty', 'fullcalendar/locales-all.global.min', 'js', 'calendar');
include_file('desktop', 'calendar', 'js', 'calendar');
include_file('core', 'plugin.template', 'js');
?>

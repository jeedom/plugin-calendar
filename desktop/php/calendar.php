<?php
if (!isConnect('admin')) {
	throw new Exception('Error 401 Unauthorized');
}
include_file('3rdparty', 'fullcalendar/fullcalendar', 'css', 'calendar');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'calendar');
$plugin = plugin::byId('calendar');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="eqLogicThumbnailContainer">
			<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br/>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_healthcalendar">
				<i class="fas fa-medkit"></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
		</div>
		<legend><i class="fa fa-calendar"></i> {{Mes agendas}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo "<br/>";
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>
	
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm roundedLeft" id="bt_addEvent"><i class="fas fa-plus-circle"></i> {{Ajouter événement}}</a><a class="btn btn-default eqLogicAction btn-sm" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fa fa-files-o"></i> {{Dupliquer}}</a><a class="btn btn-success eqLogicAction btn-sm" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger eqLogicAction btn-sm roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#generaltab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Géneral}}</a></li>
			<li role="presentation"><a id="bt_calendartab" href="#calendartab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-calendar"></i> {{Agenda}}</a></li>
		</ul>
		
		<div class="tab-content" style="overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="generaltab">
				<br/>
				<div class="col-sm-6">
					<form class="form-horizontal">
						<fieldset>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement gCalendar}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >{{Objet parent}}</label>
								<div class="col-sm-4">
									<select class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										foreach (jeeObject::all() as $object) {
											echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-8">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label"></label>
								<div class="col-sm-8">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Widget, nombre de jours}}</label>
								<div class="col-sm-2">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetDay" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nombre d'événement maximum}}</label>
								<div class="col-sm-2">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetMaxEvent" />
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="col-sm-6">
					<form class="form-horizontal">
						<fieldset>
							<legend><i class="fa fa-list"></i>  {{Liste des événements de l'agenda}}</legend>
							<div id="div_eventList"></div>
							<br/>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="calendartab">
				<br/>
				<div id="div_calendar"></div>
			</div>
		</div>
		
		<?php
		include_file('3rdparty', 'fullcalendar/lib/moment.min', 'js', 'calendar');
		include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'calendar');
		include_file('3rdparty', 'fullcalendar/fullcalendar.min', 'js', 'calendar');
		include_file('3rdparty', 'fullcalendar/locale/fr', 'js', 'calendar');
		include_file('desktop', 'calendar', 'js', 'calendar');
		include_file('core', 'plugin.template', 'js');
		?>
		
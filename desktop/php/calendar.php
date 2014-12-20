<?php
if (!isConnect('admin')) {
    throw new Exception('Error 401 Unauthorized');
}
include_file('3rdparty', 'fullcalendar/fullcalendar', 'css', 'calendar');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'calendar');
sendVarToJS('eqType', 'calendar');
$eqLogics = eqLogic::byType('calendar');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un agenda}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes agendas}}
        </legend>
        <?php
        if (count($eqLogics) == 0) {
            echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>Vous n'avez encore aucune agenda, cliquez à gauche sur le bouton ajouter un équipement un agenda pour commencer</span></center>";
        } else {
            ?>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/calendar/doc/images/calendar_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <div class="row">
            <div class="col-sm-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
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
                                    foreach (object::all() as $object) {
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
                            <label class="col-sm-4 control-label">{{Activer}}</label>
                            <div class="col-sm-1">
                                <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
                            </div>
                            <label class="col-sm-4 control-label">{{Visible}}</label>
                            <div class="col-sm-1">
                                <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{Widget, nombre de jours}}</label>
                            <div class="col-sm-2">
                                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetDay" />
                            </div>
                            <label class="col-sm-4 control-label">{{Nombre d'événement maximum}}</label>
                            <div class="col-sm-2">
                                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="nbWidgetMaxEvent" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-8 control-label">{{Ne pas afficher le status et les commandes d'activation/désactivation}}</label>
                            <div class="col-sm-1">
                                <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="noStateDisplay" />
                            </div>
                        </div>

                    </fieldset> 
                </form>
            </div>
            <div class="col-sm-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>{{Liste des événements de l'agenda}}</legend>
                        <div id="div_eventList"></div>
                        <br/>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <a class="btn btn-default" id="bt_addEvent"><i class="fa fa-plus-circle"></i> {{Ajouter événement}}</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <legend>{{Agenda}}</legend>
        <div id="div_calendar"></div>
        <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>
    </div>
</div>


<?php
include_file('3rdparty', 'fullcalendar/lib/moment.min', 'js', 'calendar');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'calendar');
include_file('3rdparty', 'fullcalendar/fullcalendar.min', 'js', 'calendar');
include_file('3rdparty', 'fullcalendar/lang/fr', 'js', 'calendar');
include_file('desktop', 'calendar', 'js', 'calendar');
include_file('core', 'plugin.template', 'js');
?>
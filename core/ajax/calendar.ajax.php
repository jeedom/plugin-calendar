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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	include_file('core', 'calendar', 'class', 'calendar');

	if (!isConnect()) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}

	if (init('action') == 'getEvents') {
		echo json_encode(calendar_event::calculeEvents(calendar_event::getEventsByEqLogic(init('eqLogic_id'), init('start'), init('end')), init('start'), init('end')), JSON_UNESCAPED_UNICODE);
		die();
	}

	if (init('action') == 'getAllEvents') {
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}
		ajax::success(utils::o2a(calendar_event::getEventsByEqLogic(init('eqLogic_id'))));
	}

	if (init('action') == 'getAllCalendarAndEvents') {
		$return = array();
		foreach (calendar::byType('calendar') as $calendar) {
			$array_calendar = utils::o2a($calendar);
			$array_calendar['events'] = utils::o2a($calendar->getEvents());
			$return[] = $array_calendar;
		}
		ajax::success($return);
	}

	if (init('action') == 'addIncludeDateToEvent') {
		$event = calendar_event::byId(init('id'));
		if (!is_object($event)) {
			throw new Exception(__('Aucun évènement correspondant à', __FILE__) . ' : ' . init('id'));
		}
		if (init('startDate') == '') {
			throw new Exception(__("La date de début ne peut être vide", __FILE__));
		}
		$cmd = $event->getEqLogic()->getCmd('action', 'add_include_date');
		if (!is_object($cmd)) {
			throw new Exception(__("Impossible de trouver la commande d'exclusion", __FILE__));
		}
		$date = (init('endDate') != '' && init('endDate') != init('startDate')) ? init('startDate') . ':' . init('endDate') : init('startDate');
		$cmd->execCmd(array('title' => $event->getName(), 'message' => $date));
		ajax::success();
	}

	if (init('action') == 'saveEvent') {
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}
		$eventSave = json_decode(init('event'), true);
		$event = null;
		if (isset($eventSave['id'])) {
			$event = calendar_event::byId($eventSave['id']);
		}
		if (!is_object($event)) {
			$event = new calendar_event();
		}
		utils::a2o($event, jeedom::fromHumanReadable($eventSave));
		$event->save();
		ajax::success();
	}

	if (init('action') == 'removeEvent') {
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}
		$event = calendar_event::byId(init('id'));
		if (!is_object($event)) {
			throw new Exception(__('Aucun évènement correspondant à', __FILE__) . ' : ' . init('id'));
		}
		$event->remove();
		ajax::success();
	}

	if (init('action') == 'removeOccurence') {
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}
		$event = calendar_event::byId(init('id'));
		if (!is_object($event)) {
			throw new Exception(__('Aucun évènement correspondant à : ', __FILE__) . init('id'));
		}
		if (init('date') == '') {
			throw new Exception(__("La date de l'occurence ne peut être vide", __FILE__) . ' : ' . init('date'));
		}
		$cmd = $event->getEqLogic()->getCmd('action', 'add_exclude_date');
		if (!is_object($cmd)) {
			throw new Exception(__("Impossible de trouver la commande d'exclusion", __FILE__));
		}
		$cmd->execCmd(array('title' => $event->getName(), 'message' => date('Y-m-d', strtotime(init('date')))));
		ajax::success();
	}

	throw new Exception(__('Aucune methode correspondante à', __FILE__) . ' : ' . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}

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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function calendar_install() {
	$sql = file_get_contents(dirname(__FILE__) . '/install.sql');
	DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
	foreach (calendar::byType('calendar') as $calendar) {
		$calendar->save();
	}
}

function calendar_update() {
	$calendar = new calendar();
	foreach (calendar_event::all() as $event) {
		foreach (array('start', 'end') as $_action) {
			if ($event->getCmd_param($_action . '_type') == 'cmd') {
				$_action = array();
				$_action['cmd'] = $event->getCmd_param($_action . '_name');
				$_action['options'] = $event->getCmd_param($_action . '_options');
				$event->setCmd_param($_action . '_type', '');
				$event->setCmd_param($_action . '_name', '');
				$event->setCmd_param($_action . '_options', '');
				$event->setCmd_param($_action, array($_action));
			}
			if ($event->getCmd_param($_action . '_type') == 'scenario') {
				$_action = array();
				$_action['cmd'] = 'scenario';
				$_action['options'] = array('scenario_id' => str_replace(array('#', 'scenario'), '', $event->getCmd_param($_action . '_scenarioName')), 'action' => $event->getCmd_param($_action . '_action'));
				$event->setCmd_param($_action . '_type', '');
				$event->setCmd_param($_action . '_name', '');
				$event->setCmd_param($_action . '_options', '');
				$event->setCmd_param($_action, array($_action));
			}
		}
	}
}

function calendar_remove() {
	DB::Prepare('DROP TABLE IF EXISTS `calendar_event`', array(), DB::FETCH_TYPE_ROW);
}
?>

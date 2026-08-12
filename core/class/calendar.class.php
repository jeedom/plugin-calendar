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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/calendar_event.class.php';

class calendar extends eqLogic {
	/*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array('custom' => true, 'custom::graph' => false, 'custom::layout' => false);

	/*     * ***********************Methode static*************************** */

	public static function pull($_option) {
		$event = calendar_event::byId($_option['event_id']);
		if (!is_object($event)) {
			return;
		}
		$eqLogic = $event->getEqLogic();
		if ($eqLogic->getIsEnable() == 0) {
			return;
		}
		$nowtime = strtotime('now');
		$repeat = $event->getRepeat();
		if ($repeat['enable'] == 1) {
			if ($repeat['nationalDay'] == 'onlyNationalDay' || !isset($repeat['freq']) || $repeat['freq'] == '' || $repeat['unite'] == '') {
				$startDate = (new DateTime('-12 month ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
				$endDate = (new DateTime('+12 month ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
			} else {
				if ($repeat['unite'] == 'hours') {
					$startDate = (new DateTime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
					$endDate = (new DateTime('+' . 9999 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
				} else {
					$startDate = (new DateTime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
					$endDate = (new DateTime('+' . 99 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
				}
			}
		} else {
			$startDate = null;
			$endDate = null;
		}
		log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Reprogrammation', __FILE__));
		$event->reschedule();
		log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Analyse de l\'évènement', __FILE__) . ' : ' . print_r($event, true));
		try {
			if (jeedom::isDateOk()) {
				$results = $event->calculOccurrence($startDate, $endDate);
				if (count($results) == 0) {
					log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Aucune programmation trouvée, exécution des actions de fin', __FILE__));
					$event->doAction('end');
					return null;
				}
				log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Recherche de l\'action à exécuter (début ou fin)', __FILE__));
				for ($i = 0; $i < count($results); $i++) {
					if ((strtotime($results[$i]['end']) + 300) <= $nowtime) {
						continue;
					}
					if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
						log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Action de début', __FILE__));
						$event->doAction('start');
						break;
					}
					if (strtotime($results[$i]['end']) <= $nowtime && (!isset($results[$i + 1]) || strtotime($results[$i + 1]['start']) > $nowtime)) {
						log::add(__CLASS__, 'debug', $eqLogic->getHumanName() . ' ' . __('Action de fin', __FILE__));
						$event->doAction('end');
						break;
					}
				}
			}
		} catch (Exception $e) {
		}
	}

	public static function start() {
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$eqLogic->rescheduleEvent();
		}
	}

	public static function restore() {
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$eqLogic->rescheduleEvent();
		}
	}

	public static function cronDaily() {
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$eqLogic->rescheduleEvent();
		}
	}

	public static function orderEvent($a, $b) {
		$al = strtolower($a['start']);
		$bl = strtolower($b['start']);
		if ($al == $bl) {
			return 0;
		}
		return ($al > $bl) ? +1 : -1;
	}

	public static function deadCmd() {
		$return = array();
		foreach (eqLogic::byType(__CLASS__) as $calendar) {
			foreach (calendar_event::getEventsByEqLogic($calendar->getId()) as $events) {
				foreach ($events->getCmd_param()['start'] as $cmdStart) {
					if ($cmdStart['cmd'] != '' && strpos($cmdStart['cmd'], '#') !== false) {
						if (!cmd::byId(str_replace('#', '', $cmdStart['cmd']))) {
							$return[] = array('detail' => __('Calendrier', __FILE__) . ' ' . $calendar->getHumanName() . ' ' . __('dans l\'évènement', __FILE__) . ' ' . $events->getCmd_param()['eventName'], 'help' => __('Action de début', __FILE__), 'who' => $cmdStart['cmd']);
						}
					}
				}
				foreach ($events->getCmd_param()['end'] as $cmdEnd) {
					if ($cmdEnd['cmd'] != '' && strpos($cmdEnd['cmd'], '#') !== false) {
						if (!cmd::byId(str_replace('#', '', $cmdEnd['cmd']))) {
							$return[] = array('detail' => __('Calendrier', __FILE__) . ' ' . $calendar->getHumanName() . ' ' . __('dans l\'évènement', __FILE__) . ' ' . $events->getCmd_param()['eventName'], 'help' => __('Action de fin', __FILE__), 'who' => $cmdEnd['cmd']);
						}
					}
				}
			}
		}
		return $return;
	}

	public static function customUsedBy($_type, $_id) {
		if ($_type == 'cmd') {
			return calendar_event::searchByCmd_param('#' . $_id . '#');
		}
		if ($_type == 'eqLogic') {
			return array_merge(calendar_event::searchByCmd_param('#eqLogic' . $_id . '#'), calendar_event::searchByCmd_param('"eqLogic":"' . $_id . '"'));
		}
		if ($_type == 'scenario') {
			return array_merge(calendar_event::searchByCmd_param('#scenario' . $_id . '#'), calendar_event::searchByCmd_param('"scenario_id":"' . $_id . '"'));
		}
	}



	/*     * *********************Methode d'instance************************* */

	public function copy($_name) {
		$eqLogicCopy = clone $this;
		$eqLogicCopy->setName($_name);
		$eqLogicCopy->setId('');
		$eqLogicCopy->save();
		foreach ($this->getEvents() as $event) {
			$eventCopy = clone $event;
			$eventCopy->setId('');
			$eventCopy->setEqLogic_id($eqLogicCopy->getId());
			$eventCopy->save();
		}
		return $eqLogicCopy;
	}

	public function preRemove() {
		foreach ($this->getEvents() as $event) {
			$event->remove();
		}
	}

	public function preSave() {
		if ($this->getConfiguration('nbWidgetDay') == '') {
			$this->setConfiguration('nbWidgetDay', 7);
		}
	}

	public function preInsert() {
		$this->setIsEnable(1);
	}

	public function postSave() {
		$state = $this->getCmd(null, 'state');
		if (is_object($state)) {
			$state->remove();
		}

		$enable = $this->getCmd(null, 'enable');
		if (is_object($enable)) {
			$enable->remove();
		}

		$disable = $this->getCmd(null, 'disable');
		if (is_object($disable)) {
			$disable->remove();
		}

		$cmd = $this->getCmd(null, 'in_progress');
		if (!is_object($cmd)) {
			$cmd = new calendarCmd();
			$cmd->setIsVisible(0);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setName(__('En cours', __FILE__));
		$cmd->setType('info');
		$cmd->setSubType('string');
		$cmd->setLogicalId('in_progress');
		$cmd->save();

		$events_name = array();
		$events = $this->getEvents();
		if (count($events) > 0) {
			foreach ($events as $event) {
				$events_name[] = $event->getName();
			}
		}

		$cmd = $this->getCmd(null, 'add_include_date');
		if (!is_object($cmd)) {
			$cmd = new calendarCmd();
			$cmd->setIsVisible(0);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setName(__('Ajouter une date', __FILE__));
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setLogicalId('add_include_date');
		$cmd->setDisplay('message_placeholder', __('Date (AAAA-MM-JJ)', __FILE__));
		$cmd->setDisplay('title_placeholder', __('Nom évènement', __FILE__));
		$cmd->setDisplay('title_possibility_list', json_encode($events_name));
		$cmd->save();

		$cmd = $this->getCmd(null, 'add_exclude_date');
		if (!is_object($cmd)) {
			$cmd = new calendarCmd();
			$cmd->setIsVisible(0);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setName(__('Retirer une date', __FILE__));
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setLogicalId('add_exclude_date');
		$cmd->setDisplay('message_placeholder', __('Date (AAAA-MM-JJ)', __FILE__));
		$cmd->setDisplay('title_placeholder', __('Nom évènement', __FILE__));
		$cmd->setDisplay('title_possibility_list', json_encode($events_name));
		$cmd->save();

		$this->rescheduleEvent();
		$this->refreshWidget();
	}

	public function rescheduleEvent() {
		log::add(__CLASS__, 'debug', $this->getHumanName() . ' ' . __('Reprogrammation de tous les évènements', __FILE__));
		foreach ($this->getEvents() as $event) {
			$event->save();
		}
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$_version = jeedom::versionAlias($_version);

		$startDate = (new DateTime('-' . $this->getConfiguration('nbWidgetDay', 7) . ' days ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
		$endDate = (new DateTime('+' . $this->getConfiguration('nbWidgetDay', 7) . ' days ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
		$events = calendar_event::calculeEvents(calendar_event::getEventsByEqLogic($this->getId(), $startDate, $endDate), $startDate, $endDate);
		usort($events, 'calendar::orderEvent');
		$tEvent = translate::exec(getTemplate('core', $_version, 'event', __CLASS__), 'plugins/calendar/core/template/' . $_version . '/event.html');
		$dEvent = '';
		$nbEvent = 1;
		$eventList = array();
		foreach ($events as $event) {
			if ($this->getConfiguration('nbWidgetMaxEvent', 0) != 0 && $this->getConfiguration('nbWidgetMaxEvent', 0) < $nbEvent) {
				break;
			}
			if (strtotime($event['end']) < strtotime('now') || strtotime($event['start']) > strtotime($endDate)) {
				continue;
			}
			if (isset($eventList[$this->getId() . '_' . $event['id'] . '_' . $event['start'] . '_' . $event['end']])) {
				continue;
			}
			$eventList[$this->getId() . '_' . $event['id'] . '_' . $event['start'] . '_' . $event['end']] = true;
			if ($event['noDisplayOnDashboard'] == 0) {
				$replaceCmd = array(
					'#uid#' => mt_rand() . $this->getId() . $event['id'],
					'#event_id#' => $event['id'],
					'#name#' => $event['title'],
					'#date#' => $event['start'],
					'#start#' => date_fr(date('D', strtotime($event['start']))) . ' ' . date('d', strtotime($event['start'])) . ' ' . date_fr(date('M', strtotime($event['start']))) . ' ' . date('H:i', strtotime($event['start'])),
					'#end#' => date_fr(date('D', strtotime($event['end']))) . ' ' . date('d', strtotime($event['end'])) . ' ' . date_fr(date('M', strtotime($event['end']))) . ' ' . date('H:i', strtotime($event['end'])),
					'#background_color#' => $event['color'],
					'#text_color#' => $event['textColor'],
				);
				$dEvent .= template_replace($replaceCmd, $tEvent);
				$nbEvent++;
			}
		}
		$replace['#events#'] = $dEvent;
		return template_replace($replace, getTemplate('core', $_version, 'eqLogic', __CLASS__));
	}

	/*     * **********************Getteur Setteur*************************** */

	public function getEvents() {
		return calendar_event::getEventsByEqLogic($this->getId());
	}
}

class calendarCmd extends cmd {
	/*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array('custom' => false);

	/*     * *********************Methode d'instance************************* */

	public function dontRemoveCmd() {
		if (in_array($this->getLogicalId(), array('in_progress', 'add_exclude_date', 'add_include_date'))) {
			return true;
		}
		return false;
	}

	public function postInsert() {
		if ($this->getLogicalId() == 'in_progress') {
			$this->event($this->execute());
		}
	}

	public function execute($_options = null) {
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'in_progress') {
			$return = '';
			foreach ($eqLogic->getEvents() as $event) {
				if ($event->getCmd_param('in_progress', 0) == 1) {
					if ($event->getCmd_param('eventName') != '') {
						$return .= $event->getCmd_param('eventName') . ', ';
					} else {
						$return .= $event->getCmd_param('name') . ', ';
					}
				}
			}
			$return = trim(trim(trim($return), ','));
			if ($return == '') {
				$return = __('Aucun', __FILE__);
			}
			return $return;
		}
		if ($this->getLogicalId() == 'add_exclude_date') {
			$events = $eqLogic->getEvents();
			$toDoEvent = explode(',', $_options['title']);
			foreach ($events as $event) {
				if (!in_array($event->getCmd_param('eventName'), $toDoEvent)) {
					continue;
				}
				$event->setRepeat('includeDate', trim(str_replace($_options['message'], '', $event->getRepeat('includeDate')), ','));
				$event->setRepeat('excludeDate', trim(str_replace($_options['message'], '', $event->getRepeat('excludeDate')), ','));
				$event->setRepeat('excludeDate', trim($event->getRepeat('excludeDate') . ',' . $_options['message'], ','));
				$event->save();
				$eqLogic->refreshWidget();
			}
			return;
		}
		if ($this->getLogicalId() == 'add_include_date') {
			$events = $eqLogic->getEvents();
			$toDoEvent = explode(',', $_options['title']);
			foreach ($events as $event) {
				if (!in_array($event->getCmd_param('eventName'), $toDoEvent)) {
					continue;
				}
				$event->setRepeat('excludeDate', trim(str_replace($_options['message'], '', $event->getRepeat('excludeDate')), ','));
				$event->setRepeat('includeDate', trim(str_replace($_options['message'], '', $event->getRepeat('includeDate')), ','));
				$event->setRepeat('includeDate', trim($event->getRepeat('includeDate') . ',' . $_options['message'], ','));
				$event->save();
				$eqLogic->refreshWidget();
			}
			return;
		}
	}
}

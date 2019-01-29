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

class calendar extends eqLogic {
	/*     * *************************Attributs****************************** */
	
	public static $_widgetPossibility = array('custom' => true, 'custom::layout' => false);
	
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
			if ($repeat['nationalDay'] == 'onlyNationalDay') {
				$startDate = date('Y-m-d H:i:s', strtotime('-12 month ' . date('Y-m-d H:i:s')));
				$endDate = date('Y-m-d H:i:s', strtotime('+12 month ' . date('Y-m-d H:i:s')));
			} else {
				$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
				$endDate = date('Y-m-d H:i:s', strtotime('+' . 99 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
			}
		} else {
			$startDate = null;
			$endDate = null;
		}
		log::add('calendar', 'debug', $eqLogic->getHumanName() . ' Reprogrammation');
		$event->reschedule();
		log::add('calendar', 'debug', $eqLogic->getHumanName() . 'Lancement de l\'evenement : ' . print_r($event, true));
		try {
			if (jeedom::isDateOk()) {
				$results = $event->calculOccurence($startDate, $endDate);
				if (count($results) == 0) {
					log::add('calendar', 'debug', $eqLogic->getHumanName() . 'Aucune programmation trouvée, lancement des actions de fin');
					$event->doAction('end');
					return null;
				}
				log::add('calendar', 'debug', $eqLogic->getHumanName() . 'Recherche de l\'action à faire (start ou end)');
				for ($i = 0; $i < count($results); $i++) {
					if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
						log::add('calendar', 'debug', $eqLogic->getHumanName() . 'Action de début');
						$event->doAction('start');
						break;
					}
					if (strtotime($results[$i]['end']) <= $nowtime && (!isset($results[$i + 1]) || strtotime($results[$i + 1]['start']) > $nowtime)) {
						log::add('calendar', 'debug', $eqLogic->getHumanName() . 'Action de fin');
						$event->doAction('end');
						break;
					}
				}
			}
		} catch (Exception $e) {
			
		}
	}
	
	public static function start() {
		foreach (self::byType('calendar') as $eqLogic) {
			$eqLogic->rescheduleEvent();
		}
	}
	
	public static function restore() {
		foreach (self::byType('calendar') as $eqLogic) {
			$eqLogic->rescheduleEvent();
		}
	}
	
	public static function cronDaily() {
		foreach (self::byType('calendar') as $eqLogic) {
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
		foreach (eqLogic::byType('calendar') as $calendar) {
			foreach (calendar_event::getEventsByEqLogic($calendar->getId()) as $events) {
				foreach ($events->getCmd_param()['start'] as $cmdStart) {
					if ($cmdStart['cmd'] != '' && strpos($cmdStart['cmd'], '#') !== false) {
						if (!cmd::byId(str_replace('#', '', $cmdStart['cmd']))) {
							$return[] = array('detail' => 'Calendrier ' . $calendar->getHumanName() . ' dans l\'évènement ' . $events->getCmd_param()['eventName'], 'help' => 'Action de début', 'who' => $cmdStart['cmd']);
						}
					}
				}
				foreach ($events->getCmd_param()['end'] as $cmdEnd) {
					if ($cmdEnd['cmd'] != '' && strpos($cmdEnd['cmd'], '#') !== false) {
						if (!cmd::byId(str_replace('#', '', $cmdEnd['cmd']))) {
							$return[] = array('detail' => 'Calendrier ' . $calendar->getHumanName() . ' dans l\'évènement ' . $events->getCmd_param()['eventName'], 'help' => 'Action de fin', 'who' => $cmdEnd['cmd']);
						}
					}
				}
			}
		}
		return $return;
	}
	
	/*     * ***********************Methode static*************************** */
	
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
		$cmd->save();
		
		$this->rescheduleEvent();
		$this->refreshWidget();
	}
	
	public function rescheduleEvent() {
		log::add('calendar', 'debug', $this->getHumanName() . ' Reprogrammation de tous les évènements');
		foreach ($this->getEvents() as $event) {
			$event->save();
		}
	}
	
	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		
		$startDate = date('Y-m-d H:i:s', strtotime('-' . $this->getConfiguration('nbWidgetDay', 7) . ' days ' . date('Y-m-d H:i:s')));
		$endDate = date('Y-m-d H:i:s', strtotime('+' . $this->getConfiguration('nbWidgetDay', 7) . ' days ' . date('Y-m-d H:i:s')));
		$events = calendar_event::calculeEvents(calendar_event::getEventsByEqLogic($this->getId(), $startDate, $endDate), $startDate, $endDate);
		usort($events, 'calendar::orderEvent');
		$tEvent = getTemplate('core', $version, 'event', 'calendar');
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
		return template_replace($replace, getTemplate('core', $version, 'eqLogic', 'calendar'));
	}
	
	/*     * **********************Getteur Setteur*************************** */
	
	public function getEvents() {
		return calendar_event::getEventsByEqLogic($this->getId());
	}
}

class calendarCmd extends cmd {
	/*     * *************************Attributs****************************** */
	
	public static $_widgetPossibility = array('custom' => false);
	
	/*     * ***********************Methode static*************************** */
	
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
				$event->setRepeat('includeDate', str_replace($_options['message'],'',$event->getRepeat('includeDate')));
				$event->setRepeat('excludeDate', trim($event->getRepeat('excludeDate') . ',' . $_options['message'], ','));
				$event->save();
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
				$event->setRepeat('excludeDate', str_replace($_options['message'],'',$event->getRepeat('excludeDate')));
				$event->setRepeat('includeDate', trim($event->getRepeat('includeDate') . ',' . $_options['message'], ','));
				$event->save();
			}
			return;
		}
		
	}
	
	/*     * **********************Getteur Setteur*************************** */
}

class calendar_event {
	/*     * *************************Attributs****************************** */
	
	private $id;
	private $eqLogic_id;
	private $cmd_param;
	private $startDate;
	private $endDate;
	private $repeat;
	private $until = null;
	private $_changed = false;
	
	/*     * ***********************Methode static*************************** */
	
	public static function sortEventDate($a, $b) {
		if (strtotime($a['start']) == strtotime($b['start'])) {
			return 0;
		}
		return (strtotime($a['start']) < strtotime($b['start'])) ? -1 : 1;
	}
	
	public static function cleanEvents() {
		$events = self::all();
		foreach ($events as $event) {
			if (!is_object($event->getEqLogic())) {
				$event->remove();
			}
		}
	}
	
	public static function byId($_id) {
		$values = array(
			'id' => $_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE id=:id';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function searchByCmd($_cmd_id) {
		$values = array(
			'cmd_param' => '%"cmd":"#' . $_cmd_id . '#"%',
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE cmd_param LIKE :cmd_param';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function all() {
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event';
		return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function getEventsByEqLogic($_eqLogic_id, $_startDate = null, $_endDate = null) {
		$values = array(
			'eqLogic_id' => $_eqLogic_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE eqLogic_id=:eqLogic_id';
		if ($_startDate != null && $_endDate = null) {
			$values['startDate'] = $_startDate;
			$values['endDate'] = $_endDate;
			$sql .= ' AND ((startDate >=:startDate
				AND startDate <=:endDate)
				OR until >=:startDate
				OR until = "0000-00-00 00:00:00"
				OR until is NULL)';
			}
			$sql .= ' ORDER BY startDate';
			return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
		}
		
		public static function calculeEvents($_events, $_startDate = null, $_endDate = null) {
			$return = array();
			foreach ($_events as $event) {
				foreach ($event->calculOccurence($_startDate, $_endDate) as $info_event) {
					$info_event['id'] = $event->getId();
					if ($event->getCmd_param('transparent', 0) == 1) {
						$info_event['color'] = 'transparent';
					} else {
						$info_event['color'] = $event->getCmd_param('color', '#2980b9');
					}
					$info_event['textColor'] = $event->getCmd_param('text_color', 'black');
					$info_event['noDisplayOnDashboard'] = $event->getCmd_param('noDisplayOnDashboard');
					if ($event->getCmd_param('eventName') != '') {
						$info_event['title'] = $event->getCmd_param('icon') . ' ' . $event->getCmd_param('eventName');
					} else {
						$info_event['title'] = $event->getCmd_param('icon') . ' ' . $event->getCmd_param('name');
					}
					$return[] = jeedom::toHumanReadable($info_event);
				}
			}
			return $return;
		}
		
		public static function getNationalDay($year = null) {
			if ($year === null) {
				$year = intval(date('Y'));
			}
			$easterDate = easter_date($year);
			$easterDay = date('j', $easterDate);
			$easterMonth = date('n', $easterDate);
			$easterYear = date('Y', $easterDate);
			$holidays = array(
				// Dates fixes
				date('Y-m-d', mktime(0, 0, 0, 1, 1, $year)), // 1er janvier
				date('Y-m-d', mktime(0, 0, 0, 5, 1, $year)), // Fête du travail
				date('Y-m-d', mktime(0, 0, 0, 5, 8, $year)), // Victoire des alliés
				date('Y-m-d', mktime(0, 0, 0, 7, 14, $year)), // Fête nationale
				date('Y-m-d', mktime(0, 0, 0, 8, 15, $year)), // Assomption
				date('Y-m-d', mktime(0, 0, 0, 11, 1, $year)), // Toussaint
				date('Y-m-d', mktime(0, 0, 0, 11, 11, $year)), // Armistice
				date('Y-m-d', mktime(0, 0, 0, 12, 25, $year)), // Noel
				// Dates variables
				date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear)),
				date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear)),
				date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear)),
				
				date('Y-m-d', mktime(0, 0, 0, 1, 1, $year + 1)), // 1er janvier
				date('Y-m-d', mktime(0, 0, 0, 5, 1, $year + 1)), // Fête du travail
				date('Y-m-d', mktime(0, 0, 0, 5, 8, $year + 1)), // Victoire des alliés
				date('Y-m-d', mktime(0, 0, 0, 7, 14, $year + 1)), // Fête nationale
				date('Y-m-d', mktime(0, 0, 0, 8, 15, $year + 1)), // Assomption
				date('Y-m-d', mktime(0, 0, 0, 11, 1, $year + 1)), // Toussaint
				date('Y-m-d', mktime(0, 0, 0, 11, 11, $year + 1)), // Armistice
				date('Y-m-d', mktime(0, 0, 0, 12, 25, $year + 1)), // Noel
			);
			sort($holidays);
			return $holidays;
		}
		
		/*     * *********************Methode d'instance************************* */
		
		public function reschedule() {
			$next = $this->nextOccurrence();
			if ($next === null || $next === false) {
				log::add('calendar', 'debug', $this->getEqLogic()->getHumanName() . ' Aucune reprogrammation à faire car aucune occurence suivante trouvée');
				return;
			}
			log::add('calendar', 'debug', $this->getEqLogic()->getHumanName() . ' Reprogrammation à : ' . print_r($next, true) . ' de  : ' . print_r($this, true));
			$cron = cron::byClassAndFunction('calendar', 'pull', array('event_id' => intval($this->getId())));
			if ($next != null) {
				if (!is_object($cron)) {
					$cron = new cron();
					$cron->setClass('calendar');
					$cron->setFunction('pull');
					$cron->setOption(array('event_id' => intval($this->getId())));
					$cron->setLastRun(date('Y-m-d H:i:s'));
				}
				$next = strtotime($next);
				$cron->setSchedule(date('i', $next) . ' ' . date('H', $next) . ' ' . date('d', $next) . ' ' . date('m', $next) . ' * ' . date('Y', $next));
				$cron->save();
			} else {
				if (is_object($cron)) {
					$cron->remove(false);
				}
			}
		}
		
		public function nextOccurrence($_position = null, $_details = false) {
			$startDate = null;
			$endDate = null;
			$repeat = $this->getRepeat();
			if ($repeat['enable'] == 1) {
				if ($repeat['nationalDay'] == 'onlyNationalDay') {
					$startDate = date('Y-m-d H:i:s', strtotime('-1 month ' . date('Y-m-d H:i:s')));
					$endDate = date('Y-m-d H:i:s', strtotime('+1 month ' . date('Y-m-d H:i:s')));
				} else {
					$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
					$endDate = date('Y-m-d H:i:s', strtotime('+' . 99 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
				}
			}
			$results = $this->calculOccurence($startDate, $endDate);
			if (count($results) == 0) {
				return null;
			}
			foreach ($results as $result) {
				if (strtotime($result['start']) > strtotime('now') && ($_position == null || $_position == 'start')) {
					if ($_details) {
						return array('date' => $result['start'], 'position' => 'start');
					} else {
						return $result['start'];
					}
				}
				if (strtotime($result['end']) > strtotime('now') && ($_position == null || $_position == 'end')) {
					if ($_details) {
						return array('date' => $result['end'], 'position' => 'end');
					} else {
						return $result['end'];
					}
				}
			}
			return null;
		}
		
		public function calculOccurence($_startDate, $_endDate, $_max = 9999999999, $_recurence = 0) {
			if ($_recurence > 5) {
				return array();
			}
			$_recurence++;
			$startTime = ($_startDate != null) ? strtotime($_startDate) : strtotime('now - 2 year');
			$endTime = ($_endDate != null) ? strtotime($_endDate) : strtotime('now + 2 year');
			$return = array();
			$repeat = $this->getRepeat();
			if ($this->getRepeat('enable') == 1) {
				$excludeDate = array();
				if (isset($repeat['excludeDate']) && $repeat['excludeDate'] != '') {
					$excludeDate_tmp = explode(',', $repeat['excludeDate']);
					foreach ($excludeDate_tmp as $date) {
						if (strpos($date, ':') !== false) {
							$expDate = explode(':', $date);
							if (count($expDate) == 2) {
								$startDate = $expDate[0];
								$endDate = $expDate[1];
								while (strtotime($startDate) <= strtotime($endDate)) {
									$excludeDate[] = $startDate;
									$startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
								}
							}
						} else {
							$excludeDate[] = $date;
						}
					}
				}
				if (isset($repeat['excludeDateFromCalendar']) && $repeat['excludeDateFromCalendar'] != '') {
					$excludeEvent = self::byId($repeat['excludeDateFromCalendar']);
					if (is_object($excludeEvent)) {
						$excludeEventOccurence = $excludeEvent->calculOccurence($_startDate, $_endDate, $_max, $_recurence);
						foreach ($excludeEventOccurence as $occurence) {
							$startDate = date('Y-m-d', strtotime($occurence['start']));
							$endDate = date('Y-m-d', strtotime($occurence['end']));
							if ($startDate == $endDate) {
								$excludeDate[] = $startDate;
							} else {
								while (strtotime($startDate) <= strtotime($endDate)) {
									$excludeDate[] = $startDate;
									$startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
								}
							}
						}
					}
				}
				$startDate = $this->getStartDate();
				/*if (date('I') != date('I', strtotime($startDate)) && date('G', strtotime($startDate)) == 2) {
				
				while (date('I') != date('I', strtotime($startDate)) && strtotime('now') > strtotime($startDate)) {
				$startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $startDate));
			}
			$startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $startDate));
			if (date('I')) {
			$startDate = date('Y-m-d H:i:s', strtotime($startDate . ' -1 hour'));
		} else {
		$startDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 hour'));
	}
}*/
$endDate = $this->getEndDate();
if (date('I') != date('I', strtotime($endDate)) && date('G', strtotime($endDate)) == 2) {
	while (date('I') != date('I', strtotime($endDate)) && strtotime('now') > strtotime($endDate)) {
		$endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
	}
	$endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
	if (date('I')) {
		$endDate = date('Y-m-d H:i:s', strtotime($endDate . ' -1 hour'));
	}
}
$initStartTime = date('H:i:s', strtotime($startDate));
$initEndTime = date('H:i:s', strtotime($endDate));
$first = true;
while ((strtotime($this->getUntil()) > strtotime($startDate) || $this->getUntil() == '0000-00-00 00:00:00' || $this->getUntil() == null) && (strtotime($endDate) <= $endTime || $first)) {
	$first = false;
	if (!in_array(date('Y-m-d', strtotime($startDate)), $excludeDate) && ($startTime < strtotime($startDate) || strtotime($endDate) > $startTime)) {
		if ($repeat['excludeDay'][date('N', strtotime($startDate))] == 1 || (isset($repeat['mode']) && $repeat['mode'] == 'advance')) {
			if (!isset($repeat['nationalDay']) || $repeat['nationalDay'] == 'all') {
				$return[] = array(
					'start' => $startDate,
					'end' => $endDate,
				);
			} else if ($repeat['nationalDay'] == 'exeptNationalDay') {
				$nationalDay = self::getNationalDay(date('Y'), strtotime($startDate));
				if (!in_array(date('Y-m-d', strtotime($startDate)), $nationalDay)) {
					$return[] = array(
						'start' => $startDate,
						'end' => $endDate,
					);
				}
			} else if ($repeat['nationalDay'] == 'onlyNationalDay') {
				$nationalDay = self::getNationalDay(date('Y'), strtotime($startDate));
				if (in_array(date('Y-m-d', strtotime($startDate)), $nationalDay)) {
					$return[] = array(
						'start' => $startDate,
						'end' => $endDate,
					);
				}
			} else if ($repeat['nationalDay'] == 'onlyEven') {
				if ((date('W', strtotime($startDate)) % 2) == 0) {
					$return[] = array(
						'start' => $startDate,
						'end' => $endDate,
					);
				}
			} else if ($repeat['nationalDay'] == 'onlyOdd') {
				if ((date('W', strtotime($startDate)) % 2) == 1) {
					$return[] = array(
						'start' => $startDate,
						'end' => $endDate,
					);
				}
			}
			if (count($return) >= $_max) {
				return $return;
			}
		}
	}
	$prevStartDate = $startDate;
	if (isset($repeat['mode']) && $repeat['mode'] == 'advance') {
		$nextMonth = date('F', strtotime('+1 month ' . $startDate));
		$year = date('Y', strtotime('+1 month ' . $startDate));
		$tmp_startDate = date('Y-m-d', strtotime($repeat['positionAt'] . ' ' . $repeat['day'] . ' of ' . $nextMonth . ' ' . $year));
		if ($tmp_startDate == '1970-01-01') {
			break;
		}
		$endDate = $tmp_startDate . ' ' . date('H:i:s', strtotime($endDate));
		$startDate = $tmp_startDate . ' ' . date('H:i:s', strtotime($startDate));
	} else {
		if ($repeat['freq'] == 0) {
			break;
		}
		$startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $startDate));
		$endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
	}
	if (strtotime($startDate) <= strtotime($prevStartDate)) {
		break;
	}
}
} else {
	if (($endTime == null || strtotime($this->getStartDate()) <= $endTime) && ($startTime == null || strtotime($this->getStartDate()) >= $startTime)) {
		$return[] = array(
			'start' => $this->getStartDate(),
			'end' => $this->getEndDate(),
		);
	}
}

$startDate = $this->getStartDate();
$endDate = $this->getEndDate();
$initStartTime = date('H:i:s', strtotime($startDate));
$initEndTime = date('H:i:s', strtotime($endDate));

$includeDate = array();

if (isset($repeat['includeDate']) && $repeat['includeDate'] != '') {
	$includeDate_tmp = explode(',', $repeat['includeDate']);
	foreach ($includeDate_tmp as $date) {
		if (strpos($date, ':') !== false) {
			$expDate = explode(':', $date);
			if (count($expDate) == 2) {
				$startDate = $expDate[0];
				$endDate = $expDate[1];
				while (strtotime($startDate) <= strtotime($endDate)) {
					$includeDate[$startDate] = $startDate;
					$startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
				}
			}
		} else {
			$includeDate[$date] = $date;
		}
	}
}

if (isset($repeat['includeDateFromCalendar']) && $repeat['includeDateFromCalendar'] != '') {
	$includeEvent = self::byId($repeat['includeDateFromCalendar']);
	if (is_object($includeEvent)) {
		$includeEventOccurence = $includeEvent->calculOccurence($_startDate, $_endDate, $_max, $_recurence);
		foreach ($includeEventOccurence as $occurence) {
			$startDate = date('Y-m-d', strtotime($occurence['start']));
			$endDate = date('Y-m-d', strtotime($occurence['end']));
			if ($startDate == $endDate) {
				$includeDate[$startDate] = $startDate;
			} else {
				while (strtotime($startDate) <= strtotime($endDate)) {
					$includeDate[$startDate] = $startDate;
					$startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
				}
			}
		}
	}
}

foreach ($includeDate as $date) {
	$return[] = array(
		'start' => $date . ' ' . $initStartTime,
		'end' => $date . ' ' . $initEndTime,
	);
}
usort($return, array('calendar_event', 'sortEventDate'));
return $return;
}

public function preSave() {
	if ($this->getEqLogic_id() == '') {
		throw new Exception(__('[calendar] L\'id de eqLogic ne peut être vide', __FILE__));
	}
	if (trim($this->getCmd_param('eventName')) == '') {
		throw new Exception(__('Le nom de l\'évenement ne peut etre vide', __FILE__));
	}
	$eqLogic = $this->getEqLogic();
	if (!is_object($eqLogic)) {
		throw new Exception(__('Impossible de trouver eqLogic correspondante à l\'id : ', __FILE__) . $this->getEqLogic_id());
	}
	if ((strtotime($this->getStartDate()) + 59) >= strtotime($this->getEndDate())) {
		throw new Exception(__('La date de début d\'évenement ne peut être égale ou après la date de fin', __FILE__));
	}
	$repeat = $this->getRepeat();
	$allEmpty = true;
	foreach ($repeat['excludeDay'] as $day) {
		if ($day == 1) {
			$allEmpty = false;
			break;
		}
	}
	if ($allEmpty) {
		$repeat['excludeDay'][1] = 1;
		$repeat['excludeDay'][2] = 1;
		$repeat['excludeDay'][3] = 1;
		$repeat['excludeDay'][4] = 1;
		$repeat['excludeDay'][5] = 1;
		$repeat['excludeDay'][6] = 1;
		$repeat['excludeDay'][7] = 1;
		$this->setRepeat('excludeDay', $repeat['excludeDay']);
	}
	
	if ($this->getRepeat('enable') == 1) {
		if ($this->getRepeat('mode') == 'simple') {
			if (!is_numeric($this->getRepeat('freq')) || $this->getRepeat('freq') == '' || $this->getRepeat('freq') <= 0) {
				throw new Exception(__('La fréquence de répétition ne peut etre vide, nulle ou négative', __FILE__));
			}
			if ($this->getRepeat('unite') == '') {
				throw new Exception(__('L\'unité de répétition ne peut etre vide', __FILE__));
			}
		}
	} else {
		$this->setRepeat('freq', 0);
		$this->setUntil('');
	}
	if ($this->getUntil() == '') {
		$this->setUntil(null);
	}
}

public function save() {
	return DB::save($this);
}

public function dontRemoveCmd() {
	return true;
}

public function postSave() {
	$eqLogic = $this->getEqLogic();
	if ($eqLogic->getIsEnable() == 0) {
		$this->setCmd_param('in_progress', 0);
		DB::save($this, true);
		$cmd = $eqLogic->getCmd('info', 'in_progress');
		if (is_object($cmd)) {
			$cmd->event($cmd->execute());
		}
		return;
	}
	$repeat = $this->getRepeat();
	if ($repeat['enable'] == 1) {
		$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
		$endDate = date('Y-m-d H:i:s', strtotime('+' . 99 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
	} else {
		$startDate = null;
		$endDate = null;
	}
	$this->reschedule();
	$in_progress = $this->getCmd_param('in_progress', 0);
	$this->setCmd_param('in_progress', 0);
	$nowtime = strtotime('now');
	try {
		if (jeedom::isDateOk()) {
			$results = $this->calculOccurence($startDate, $endDate);
			if (count($results) != 0) {
				for ($i = 0; $i < count($results); $i++) {
					if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
						$this->setCmd_param('in_progress', 1);
						if ($in_progress != 1) {
							$this->doAction('start');
						}
						break;
					}
				}
			}
		}
		if ($this->getCmd_param('in_progress', 0) == 0 && $in_progress == 1) {
			$this->doAction('end');
		}
	} catch (Exception $e) {
		
	}
	DB::save($this, true);
	$cmd = $eqLogic->getCmd('info', 'in_progress');
	if (is_object($cmd)) {
		$cmd->event($cmd->execute());
	}
}

public function remove() {
	$cron = cron::byClassAndFunction('calendar', 'pull', array('event_id' => intval($this->getId())));
	if (is_object($cron)) {
		$cron->remove();
	}
	$eqLogic = $this->getEqLogic();
	DB::remove($this);
}

public function doAction($_action = 'start') {
	$eqLogic = $this->getEqLogic();
	if ($eqLogic->getIsEnable() == 0) {
		$this->setCmd_param('in_progress', 0);
		DB::save($this, true);
		return;
	}
	if ($_action == 'start') {
		$this->setCmd_param('in_progress', 1);
		DB::save($this, true);
	}
	if ($_action == 'end') {
		$this->setCmd_param('in_progress', 0);
		DB::save($this, true);
	}
	$eqLogic = $this->getEqLogic();
	$cmd = $eqLogic->getCmd('info', 'in_progress');
	if (is_object($cmd)) {
		$cmd->event($cmd->execute());
	}
	foreach ($this->getCmd_param($_action) as $action) {
		try {
			$options = array();
			if (isset($action['options'])) {
				$options = $action['options'];
			}
			scenarioExpression::createAndExec('action', $action['cmd'], $options);
		} catch (Exception $e) {
			log::add('calendar', 'error', $eqLogic->getHumanName() . __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
		}
	}
	return true;
}

public function getName() {
	if ($this->getCmd_param('eventName') != '') {
		return $this->getCmd_param('eventName');
	} else {
		return $this->getCmd_param('name');
	}
}

/*     * **********************Getteur Setteur*************************** */

public function getId() {
	return $this->id;
}

public function getStartDate() {
	return $this->startDate;
}

public function getEndDate() {
	return $this->endDate;
}

public function setId($_id) {
	$this->_changed = utils::attrChanged($this->_changed,$this->id,$_id);
	$this->id = $_id;
}

public function setStartDate($_startDate) {
	$this->_changed = utils::attrChanged($this->_changed,$this->startDate,$_startDate);
	$this->startDate = $_startDate;
}

public function setEndDate($_endDate) {
	$this->_changed = utils::attrChanged($this->_changed,$this->endDate,$_endDate);
	$this->endDate = $_endDate;
}

public function getEqLogic_id() {
	return $this->eqLogic_id;
}

public function getEqLogic() {
	return calendar::byId($this->eqLogic_id);
}

public function setEqLogic_id($_eqLogic_id) {
	$this->_changed = utils::attrChanged($this->_changed,$this->eqLogic_id,$_eqLogic_id);
	$this->eqLogic_id = $_eqLogic_id;
}

public function getRepeat($_key = '', $_default = '') {
	return utils::getJsonAttr($this->repeat, $_key, $_default);
}

public function setRepeat($_key, $_value) {
	$repeat = utils::setJsonAttr($this->repeat, $_key, $_value);
	$this->_changed = utils::attrChanged($this->_changed,$this->repeat,$repeat);
	$this->repeat = $repeat;
}

public function getUntil() {
	return $this->until;
}

public function setUntil($_until) {
	$this->_changed = utils::attrChanged($this->_changed,$this->until,$_until);
	$this->until = $_until;
}

public function getCmd_param($_key = '', $_default = '') {
	return utils::getJsonAttr($this->cmd_param, $_key, $_default);
}

public function setCmd_param($_key, $_value) {
	$cmd_param = utils::setJsonAttr($this->cmd_param, $_key, $_value);
	$this->_changed = utils::attrChanged($this->_changed,$this->cmd_param,$cmd_param);
	$this->cmd_param = $cmd_param;
}

public function getChanged() {
	return $this->_changed;
}

public function setChanged($_changed) {
	$this->_changed = $_changed;
	return $this;
}

}

?>

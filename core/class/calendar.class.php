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

	public static function pull($_option) {
		$event = calendar_event::byId($_option['event_id']);
		if (is_object($event)) {
			$eqLogic = $event->getEqLogic();
			$nowtime = strtotime('now');
			$repeat = $event->getRepeat();
			if ($repeat['enable'] == 1) {
				if ($repeat['nationalDay'] == 'onlyNationalDay') {
					$startDate = date('Y-m-d H:i:s', strtotime('-6 month ' . date('Y-m-d H:i:s')));
					$endDate = date('Y-m-d H:i:s', strtotime('+6 month ' . date('Y-m-d H:i:s')));
				} else {
					$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
					$endDate = date('Y-m-d H:i:s', strtotime('+' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
				}
			} else {
				$startDate = null;
				$endDate = null;
			}
			log::add('calendar', 'debug', 'Lancement de l\'evenement : ' . print_r($event, true));
			if (jeedom::isDateOk() && $eqLogic->getConfiguration('enableCalendar', 1) == 1) {
				$results = $event->calculOccurence($startDate, $endDate);
				if (count($results) == 0) {
					return null;
				}
				log::add('calendar', 'debug', 'Recherche de l\'action à faire (start ou end)');
				for ($i = 0; $i < count($results); $i++) {
					if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
						log::add('calendar', 'debug', 'Action de début');
						$event->doAction('start');
						break;
					}
					if (strtotime($results[$i]['end']) <= $nowtime && (!isset($results[$i + 1]) || strtotime($results[$i + 1]['start']) > $nowtime)) {
						log::add('calendar', 'debug', 'Action de fin');
						$event->doAction('end');
						break;
					}
				}
			}
			log::add('calendar', 'debug', 'Reprogrammation');
			$event->reschedule();
		}
	}

	public static function start() {
		foreach (calendar_event::all() as $event) {
			$event->save();
		}
	}

	public static function cronHourly() {
		foreach (cron::searchClassAndFunction('calendar', 'pull') as $cron) {
			$c = new Cron\CronExpression($cron->getSchedule(), new Cron\FieldFactory);
			try {
				if (!$c->isDue()) {
					$c->getNextRunDate();
				}
			} catch (Exception $ex) {
				if ($c->getPreviousRunDate()->getTimestamp() < (strtotime('now') - 300)) {
					$option = $cron->getOption();
					$event = calendar_event::byId($option['event_id']);
					if (is_object($event)) {
						$event->reschedule();
					}
				}
			}
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
		$this->setIsEnable(1);
	}

	public function postSave() {
		$enable = $this->getCmd(null, 'enable');
		if (!is_object($enable)) {
			$enable = new calendarCmd();
			$enable->setIsVisible(1);
		}
		$enable->setEqLogic_id($this->getId());
		$enable->setName(__('Activer', __FILE__));
		$enable->setType('action');
		$enable->setSubType('other');
		$enable->setLogicalId('enable');
		$enable->setDisplay('icon', '<i class="fa fa-check"></i>');
		$enable->save();

		$disable = $this->getCmd(null, 'disable');
		if (!is_object($disable)) {
			$disable = new calendarCmd();
			$disable->setIsVisible(1);
		}
		$disable->setEqLogic_id($this->getId());
		$disable->setName(__('Désactiver', __FILE__));
		$disable->setType('action');
		$disable->setSubType('other');
		$disable->setLogicalId('disable');
		$disable->setDisplay('icon', '<i class="fa fa-times"></i>');
		$disable->save();

		$disable = $this->getCmd(null, 'in_progress');
		if (!is_object($disable)) {
			$disable = new calendarCmd();
			$disable->setIsVisible(0);
		}
		$disable->setEqLogic_id($this->getId());
		$disable->setName(__('En cours', __FILE__));
		$disable->setType('info');
		$disable->setSubType('string');
		$disable->setLogicalId('in_progress');
		$disable->save();

		$this->refreshWidget();
	}

	public function toHtml($_version = 'dashboard') {
		if (!$this->hasRight('r')) {
			return '';
		}
		$_version = jeedom::versionAlias($_version);
		$startDate = date('Y-m-d H:i:s');
		$endDate = date('Y-m-d H:i:s', strtotime('+' . $this->getConfiguration('nbWidgetDay', 7) . ' days ' . date('Y-m-d H:i:s')));
		$events = calendar_event::calculeEvents(calendar_event::getEventsByEqLogic($this->getId(), $startDate, $endDate), $startDate, $endDate);
		usort($events, 'calendar::orderEvent');
		$tEvent = getTemplate('core', $_version, 'event', 'calendar');
		$dEvent = '';
		$nbEvent = 1;
		foreach ($events as $event) {
			if ($this->getConfiguration('nbWidgetMaxEvent', 0) != 0 && $this->getConfiguration('nbWidgetMaxEvent', 0) < $nbEvent) {
				break;
			}
			if ($event['noDisplayOnDashboard'] == 0) {
				$replace = array(
					'#uid#' => mt_rand() . $this->getId() . $event['id'],
					'#event_id#' => $event['id'],
					'#name#' => $event['title'],
					'#date#' => $event['start'],
					'#start#' => date_fr(date('D', strtotime($event['start']))) . ' ' . date('d', strtotime($event['start'])) . ' ' . date_fr(date('M', strtotime($event['start']))) . ' ' . date('H:i', strtotime($event['start'])),
					'#end#' => date_fr(date('D', strtotime($event['end']))) . ' ' . date('d', strtotime($event['end'])) . ' ' . date_fr(date('M', strtotime($event['end']))) . ' ' . date('H:i', strtotime($event['end'])),
					'#background_color#' => $event['color'],
					'#text_color#' => $event['textColor'],
					'#eventLink#' => 'index.php?v=d&m=calendar&p=calendar&id=' . $this->getId() . '&event_id=' . $event['id'],
				);
				$dEvent .= template_replace($replace, $tEvent);
				$nbEvent++;
			}
		}

		$replace = array(
			'#id#' => $this->getId(),
			'#name#' => ($this->getConfiguration('enableCalendar', 1) == 1) ? $this->getName() : '<del>' . $this->getName() . '</del>',
			'#eqLink#' => $this->getLinkToConfiguration(),
			'#category#' => $this->getPrimaryCategory(),
			'#background_color#' => $this->getBackgroundColor($_version),
			'#events#' => $dEvent,
		);
		if ($this->getConfiguration('noStateDisplay') == 0) {
			if ($this->getConfiguration('enableCalendar', 1) == 1) {
				$replace['#icon#'] = '<i class="fa fa-check"></i>';
			} else {
				$replace['#icon#'] = '<i class="fa fa-times"></i>';
			}
		} else {
			$replace['#icon#'] = '';
		}
		if ($_version == 'dview' || $_version == 'mview') {
			$object = $this->getObject();
			$replace['#name#'] = (is_object($object)) ? $object->getName() . ' - ' . $replace['#name#'] : $replace['#name#'];
		}
		$info = '';
		if ($this->getConfiguration('noStateDisplay') == 0) {
			foreach ($this->getCmd(null, null, true) as $cmd) {
				$info .= $cmd->toHtml($_version);
			}
		}
		$replace['#cmd#'] = $info;
		$parameters = $this->getDisplay('parameters');
		if (is_array($parameters)) {
			foreach ($parameters as $key => $value) {
				$replace['#' . $key . '#'] = $value;
			}
		}
		return template_replace($replace, getTemplate('core', $_version, 'eqLogic', 'calendar'));
	}

	/*     * **********************Getteur Setteur*************************** */

	public function getEvents() {
		return calendar_event::getEventsByEqLogic($this->getId());
	}
}

class calendarCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function dontRemoveCmd() {
		if ($this->getLogicalId() == 'enable') {
			return true;
		}
		if ($this->getLogicalId() == 'disable') {
			return true;
		}
		if ($this->getLogicalId() == 'in_progress') {
			return true;
		}
		return false;
	}

	public function execute($_options = null) {
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'enable') {
			$eqLogic->setConfiguration('enableCalendar', 1);
			$eqLogic->save();
			$eqLogic->refreshWidget();
			foreach (calendar_event::getEventsByEqLogic($eqLogic->getId()) as $event) {
				if ($eqLogic->getConfiguration('enableCalendar', 1) == 0) {
					continue;
				}
				$nowtime = strtotime('now');
				$repeat = $event->getRepeat();
				if ($repeat['enable'] == 1) {
					$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
					$endDate = date('Y-m-d H:i:s', strtotime('+' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
				} else {
					$startDate = null;
					$endDate = null;
				}
				if (jeedom::isDateOk() && $eqLogic->getConfiguration('enableCalendar', 1) == 1) {
					$results = $event->calculOccurence($startDate, $endDate);
					if (count($results) == 0) {
						return null;
					}
					for ($i = 0; $i < count($results); $i++) {
						if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
							$event->doAction('start');
							break;
						}
						if (strtotime($results[$i]['end']) <= $nowtime && (!isset($results[$i + 1]) || strtotime($results[$i + 1]['start']) > $nowtime)) {
							break;
						}
					}
				}
				$event->reschedule();
			}
		}
		if ($this->getLogicalId() == 'disable') {
			$eqLogic->setConfiguration('enableCalendar', 0);
			$eqLogic->save();
			$eqLogic->refreshWidget();
		}

		if ($this->getLogicalId() == 'in_progress') {
			$return = '';
			foreach $eqLogic->getEvents() as $event) {
				if ($event->getCmd_param('in_progress', 0) == 1) {
					if ($event->getCmd_param('eventName') != '') {
						$return .= $event->getCmd_param('eventName') . ', ';
					} else {
						$return .= $event->getCmd_param('name') . ', ';
					}
				}
			}
			return trim(trim($return), ',');
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
	private $until = '0000-00-00 00:00:00';

	/*     * ***********************Methode static*************************** */

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
			'cmd_param' => '%"start_type":"cmd"%#' . $_cmd_id . '#%',
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE cmd_param LIKE :cmd_param';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}

	public static function all() {
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event';
		return DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
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
OR until = "0000-00-00 00:00:00")';
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
		log::add('calendar', 'debug', 'Reprogrammation à : ' . print_r($next, true) . ' de  : ' . print_r($this, true));
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
				$cron->remove();
			}
		}
	}

	public function nextOccurrence($_position = null, $_details = false) {
		$repeat = $this->getRepeat();
		if ($repeat['enable'] == 1) {
			if ($repeat['nationalDay'] == 'onlyNationalDay') {
				$startDate = date('Y-m-d H:i:s', strtotime('-6 month ' . date('Y-m-d H:i:s')));
				$endDate = date('Y-m-d H:i:s', strtotime('+6 month ' . date('Y-m-d H:i:s')));
			} else {
				$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
				$endDate = date('Y-m-d H:i:s', strtotime('+' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')));
			}
		} else {
			$startDate = null;
			$endDate = null;
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
		$startTime = ($_startDate != null) ? strtotime($_startDate) : 0;
		$endTime = ($_endDate != null) ? strtotime($_endDate) : 999999999999;
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
			$endDate = $this->getEndDate();
			$initStartTime = date('H:i:s', strtotime($startDate));
			$initEndTime = date('H:i:s', strtotime($endDate));
			$first = true;
			while ((strtotime($this->getUntil()) > strtotime($startDate) || $this->getUntil() == '0000-00-00 00:00:00') && (strtotime($endDate) <= $endTime || $first)) {
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
			if (strtotime($this->getStartDate()) <= $endTime && strtotime($this->getStartDate()) >= $startTime) {
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
							$includeDate[] = $startDate;
							$startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
						}
					}
				} else {
					$includeDate[] = $date;
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
						$includeDate[] = $startDate;
					} else {
						while (strtotime($startDate) <= strtotime($endDate)) {
							$includeDate[] = $startDate;
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
		return $return;
	}

	public function preSave() {
		if ($this->getEqLogic_id() == '') {
			throw new Exception(__('[calendar] L\'id de eqLogic ne peut être vide', __FILE__));
		}
		if (trim($this->getCmd_param('eventName')) == '') {
			throw new Exception(__('[calendar] Le nom de l\'évenement ne peut etre vide', __FILE__));
		}
		$eqLogic = $this->getEqLogic();
		if (!is_object($eqLogic)) {
			throw new Exception(__('[calendar] Impossible de trouver eqLogic correspondante à l\'id : ', __FILE__) . $this->getEqLogic_id());
		}
		if ((strtotime($this->getStartDate()) + 59) >= strtotime($this->getEndDate())) {
			throw new Exception(__('[calendar] La date de début d\'évenement ne peut être égale ou après la date de fin', __FILE__));
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

		if ($this->getRepeat('enable') == 1 && $this->getRepeat('mode') == 'simple') {
			if (!is_numeric($this->getRepeat('freq')) || $this->getRepeat('freq') == '' || $this->getRepeat('freq') <= 0) {
				throw new Exception(__('La fréquence de répétition ne peut etre vide, nulle ou négative', __FILE__));
			}
			if ($this->getRepeat('unite') == '') {
				throw new Exception(__('L\'unité de répétition ne peut etre vide', __FILE__));
			}
		} else {
			$this->setRepeat('freq', 0);
			$this->setUntil('');
		}
		if ($this->getUntil() == '') {
			$this->setUntil('0000-00-00 00:00:00');
		}
	}

	public function save() {
		return DB::save($this);
	}

	public function dontRemoveCmd() {
		return true;
	}

	public function postSave() {
		$repeat = $this->getRepeat();
		if ($repeat['enable'] == 1) {
			$startDate = date('Y-m-d H:i:s', strtotime('-' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
			$endDate = date('Y-m-d H:i:s', strtotime('+' . 8 * $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')));
		} else {
			$startDate = null;
			$endDate = null;
		}
		$this->reschedule();
	}

	public function remove() {
		$cron = cron::byClassAndFunction('calendar', 'pull', array('event_id' => intval($this->getId())));
		if (is_object($cron)) {
			$cron->remove();
		}
		return DB::remove($this);
	}

	public function doAction($_action = 'start') {
		if ($_action == 'start') {
			$this->setCmd_param('in_progress', 1);
			DB::save($this, true);
		}
		if ($_action == 'end') {
			$this->setCmd_param('in_progress', 0);
			DB::save($this, true);
		}

		if ($this->getCmd_param($_action . '_type') == 'cmd') {
			$cmd = cmd::byId(str_replace('#', '', $this->getCmd_param($_action . '_name')));
			if (is_object($cmd) && $cmd->getType() == 'action') {
				$options = $this->getCmd_param($_action . '_options');
				if (is_array($options)) {
					foreach ($options as $key => $value) {
						$options[$key] = str_replace('"', '', scenarioExpression::setTags($value, $scenario));
						if (evaluate($options[$key]) != 0) {
							$options[$key] = evaluate($options[$key]);
						}
					}
				}
				log::add('calendar', 'debug', 'Execution de : ' . $cmd->getHumanName() . ' du à : ' . $this->getName() . ' avec les options : ' . print_r($options, true));
				$cmd->execCmd($options);
			}
		}
		if ($this->getCmd_param($_action . '_type') == 'scenario') {
			$scenario = scenario::byId(str_replace(array('#', 'scenario'), '', $this->getCmd_param($_action . '_scenarioName')));
			if (is_object($scenario)) {
				log::add('calendar', 'debug', 'Execution du scénario : ' . $scenario->getHumanName() . ' du à : ' . $this->getName());
				switch ($this->getCmd_param($_action . '_action')) {
					case 'start':
						$name = $this->getCmd_param('eventName', $this->getCmd_param('name'));
						$scenario->launch(false, __('Lancement provoque par le calendrier  : ', __FILE__) . $name);
						break;
					case 'stop':
						$scenario->stop();
						break;
					case 'deactivate':
						$scenario->setIsActive(0);
						$scenario->save();
						break;
					case 'activate':
						$scenario->setIsActive(1);
						$scenario->save();
						break;
				}
			} else {
				log::add('calendar', 'error', __('Scénario non trouvé : ', __FILE__) . $this->getCmd_param($_action . '_scenarioName'));
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

	public function setId($id) {
		$this->id = $id;
	}

	public function setStartDate($startDate) {
		$this->startDate = $startDate;
	}

	public function setEndDate($endDate) {
		$this->endDate = $endDate;
	}

	public function getEqLogic_id() {
		return $this->eqLogic_id;
	}

	public function getEqLogic() {
		return calendar::byId($this->eqLogic_id);
	}

	public function setEqLogic_id($eqLogic_id) {
		$this->eqLogic_id = $eqLogic_id;
	}

	public function getRepeat($_key = '', $_default = '') {
		return utils::getJsonAttr($this->repeat, $_key, $_default);
	}

	public function setRepeat($_key, $_value) {
		$this->repeat = utils::setJsonAttr($this->repeat, $_key, $_value);
	}

	public function getUntil() {
		return $this->until;
	}

	public function setUntil($until) {
		$this->until = $until;
	}

	public function getCmd_param($_key = '', $_default = '') {
		return utils::getJsonAttr($this->cmd_param, $_key, $_default);
	}

	public function setCmd_param($_key, $_value) {
		$this->cmd_param = utils::setJsonAttr($this->cmd_param, $_key, $_value);
	}

}

?>
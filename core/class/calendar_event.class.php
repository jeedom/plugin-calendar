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

require_once __DIR__ . '/../../../../core/php/core.inc.php';

class calendar_event {

  private $id;
  private $eqLogic_id;
  private $cmd_param;
  private $startDate;
  private $endDate;
  private $repeat;
  private $until = null;
  private $_changed = false;

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

  public static function searchByCmd_param($_search) {
    $value = array(
      'search' => '%' . $_search . '%',
    );
    $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE cmd_param LIKE :search';
    return DB::Prepare($sql, $value, DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
  }

  public static function getEventsByEqLogic($_eqLogic_id, $_startDate = null, $_endDate = null) {
    $values = array(
      'eqLogic_id' => $_eqLogic_id,
    );
    $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM calendar_event
		WHERE eqLogic_id=:eqLogic_id';
    if ($_startDate != null && $_endDate != null) {
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
      foreach ($event->calculOccurrence($_startDate, $_endDate) as $info_event) {
        $info_event['id'] = $event->getId();
        if ($event->getCmd_param('transparent', 0) == 1) {
          $info_event['color'] = 'transparent';
        } else {
          $info_event['color'] = $event->getCmd_param('color', '#2980b9');
        }
        $info_event['textColor'] = $event->getCmd_param('text_color', 'white');
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

  public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = 3) {
    if (isset($_data['node']['calendar' . $this->getId()])) {
      return;
    }
    $_level++;
    if ($_level > $_drill) {
      return $_data;
    }
    $_data['node']['calendar' . $this->getId()] = array(
      'id' => 'calendar' . $this->getId(),
      'type' => __('Agenda', __FILE__),
      'name' => __('Agenda', __FILE__),
      'image' => 'plugins/calendar/plugin_info/calendar_icon.png',
      'fontsize' => '1.5em',
      'fontweight' => ($_level == 1) ? 'bold' : 'normal',
      'width' => 40,
      'height' => 40,
      'texty' => -14,
      'textx' => 0,
      'title' => $this->getName(),
      'url' => 'index.php?v=d&p=calendar&m=calendar',
    );
  }

  public function reschedule() {
    $next = $this->nextOccurrence();
    if ($next === null || $next === false) {
      log::add('calendar', 'debug', $this->getEqLogic()->getHumanName() . ' ' . __('Aucune reprogrammation car aucune occurrence suivante trouvée', __FILE__));
      return;
    }
    log::add('calendar', 'debug', $this->getEqLogic()->getHumanName() . ' ' . __('Reprogrammation à', __FILE__) . ' : ' . print_r($next, true) . ' ' . __('de', __FILE__) . ' : ' . print_r($this, true));
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
    if (isset($repeat['enable']) && $repeat['enable'] == 1) {
      if ($repeat['nationalDay'] == 'onlyNationalDay' || !isset($repeat['freq']) || $repeat['freq'] == '' || $repeat['unite'] == '') {
        $startDate = (new DateTime('-12 month ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
        $endDate = (new DateTime('+12 month ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
      } else {
        $startDate = (new DateTime('-' . (8 * $repeat['freq']) . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
        $endDate = (new DateTime('+' . (99 * $repeat['freq']) . ' ' . $repeat['unite'] . ' ' . date('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
      }
    }
    $results = $this->calculOccurrence($startDate, $endDate);
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

  public function calculOccurrence($_startDate, $_endDate, $_max = 9999999999, $_recurence = 0) {
    if ($_recurence > 5) {
      return array();
    }
    $_recurence++;
    $startTime = ($_startDate != null) ? (new DateTime($_startDate))->format('U') : strtotime('now - 2 year');
    $endTime = ($_endDate != null) ? (new DateTime($_endDate))->format('U') : strtotime('now + 2 year');
    $return = array();
    $repeat = $this->getRepeat();
    if (isset($repeat['enable']) && $repeat['enable'] == 1) {
      $excludeDate = array();
      if (isset($repeat['excludeDate']) && $repeat['excludeDate'] != '') {
        $excludeDate_tmp = explode(',', $repeat['excludeDate']);
        foreach ($excludeDate_tmp as $date) {
          if (strpos($date, ':') !== false) {
            $expDate = explode(':', $date);
            if (count($expDate) == 2) {
              $startDate = date('Y-m-d', strtotime($expDate[0]));
              $endDate = date('Y-m-d', strtotime($expDate[1]));
              while (strtotime($startDate) <= strtotime($endDate)) {
                $excludeDate[] = $startDate;
                $startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
              }
            }
          } else {
            $excludeDate[] = date('Y-m-d', strtotime($date));
          }
        }
      }
      if (isset($repeat['excludeDateFromCalendar']) && $repeat['excludeDateFromCalendar'] != '') {
        $excludeCalendar = calendar::byId($repeat['excludeDateFromCalendar']);
        if (is_object($excludeCalendar)) {
          $excludeEvents = array();
          if ($repeat['excludeDateFromEvent'] == 'all') {
            $excludeEvents = self::getEventsByEqLogic($excludeCalendar->getId());
          } else {
            $excludeEvents[] = self::byId($repeat['excludeDateFromEvent']);
          }
          foreach ($excludeEvents as $excludeEvent) {
            if (is_object($excludeEvent) && $excludeEvent->getId() != $this->getId()) {
              $excludeEventOccurrence = $excludeEvent->calculOccurrence($_startDate, $_endDate, $_max, $_recurence);
              foreach ($excludeEventOccurrence as $occurrence) {
                $startDate = date('Y-m-d', strtotime($occurrence['start']));
                $endDate = date('Y-m-d', strtotime($occurrence['end']));
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
        }
      }
      $startDate = $this->getStartDate();
      $endDate = $this->getEndDate();
      // if (date('I') != date('I', strtotime($endDate)) && date('G', strtotime($endDate)) == 2) {
      // 	while (date('I') != date('I', strtotime($endDate)) && strtotime('now') > strtotime($endDate)) {
      // 		$endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
      // 	}
      // 	$endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
      // 	if (date('I')) {
      // 		$endDate = date('Y-m-d H:i:s', strtotime($endDate . ' -1 hour'));
      // 	}
      // }
      $initStartTime = date('H:i:s', strtotime($startDate));
      $initEndTime = date('H:i:s', strtotime($endDate));
      while ($this->getUntil() == null || strtotime($this->getUntil()) > strtotime($startDate) || $this->getUntil() == '0000-00-00 00:00:00') {
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
          $endDate = date('Y-m-d H:i:s', strtotime($tmp_startDate . ' ' . $initEndTime));
          $startDate = date('Y-m-d H:i:s', strtotime($tmp_startDate . ' ' . $initStartTime));
        } else {
          if ($repeat['freq'] == 0) {
            break;
          }
          if ($repeat['unite'] == 'hours') {
            $startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $startDate));
            $endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
          } else {
            $startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . substr($startDate, 0, 10) . ' ' . $initStartTime));
            $endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . substr($endDate, 0, 10) . ' ' . $initEndTime));
          }
        }
        if (strtotime($startDate) <= strtotime($prevStartDate)) {
          break;
        }
        if (strtotime($startDate) > $endTime) {
          break;
        }
      }
    } else {
      if ($endTime == null && $startTime == null) {
        $return[] = array(
          'start' => $this->getStartDate(),
          'end' => $this->getEndDate(),
        );
      } elseif (strtotime($this->getStartDate()) <= $endTime && strtotime($this->getEndDate()) >= $startTime) {
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
      $includeDate_tmp = explode(',', trim($repeat['includeDate'], ','));
      foreach ($includeDate_tmp as $date) {
        if (strpos($date, ':') !== false) {
          $expDate = explode(':', $date);
          if (count($expDate) == 2) {
            $startDate = date('Y-m-d', strtotime($expDate[0]));
            $endDate = date('Y-m-d', strtotime($expDate[1]));
            while (strtotime($startDate) <= strtotime($endDate)) {
              $includeDate[$startDate] = $startDate;
              $startDate = date('Y-m-d', strtotime('+1 day ' . $startDate));
            }
          }
        } else {
          if (strtotime($date) >= $startTime && strtotime($date) <= $endTime) {
            $includeDate[$date] = date('Y-m-d', strtotime($date));
          }
        }
      }
    }
    if (isset($repeat['includeDateFromCalendar']) && $repeat['includeDateFromCalendar'] != '') {
      $includeCalendar = calendar::byId($repeat['includeDateFromCalendar']);
      if (is_object($includeCalendar)) {
        $includeEvents = array();
        if ($repeat['includeDateFromEvent'] == 'all') {
          $includeEvents = self::getEventsByEqLogic($includeCalendar->getId());
        } else {
          $includeEvents[] = self::byId($repeat['includeDateFromEvent']);
        }
        foreach ($includeEvents as $includeEvent) {
          if (is_object($includeEvent) && $includeEvent->getId() != $this->getId()) {
            $includeEventOccurrence = $includeEvent->calculOccurrence($_startDate, $_endDate, $_max, $_recurence);
            foreach ($includeEventOccurrence as $occurrence) {
              $startDate = date('Y-m-d', strtotime($occurrence['start']));
              $endDate = date('Y-m-d', strtotime($occurrence['end']));
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
      }
    }

    $startdatetime = strtotime(date('Y-m-d 00:00:00', strtotime($this->getStartDate())));
    $enddatetime = strtotime(date('Y-m-d 00:00:00', strtotime($this->getEndDate())));
    $diff_day = floor(($enddatetime - $startdatetime) / 86400);
    foreach ($includeDate as $date) {
      foreach ($return as $value) {
        if ($value['start'] == $date . ' ' . $initStartTime && $value['end'] == $date . ' ' . $initEndTime) {
          continue (2);
        }
      }
      if ($diff_day > 0) {
        $return[] = array(
          'start' => $date . ' ' . $initStartTime,
          'end' => date('Y-m-d H:i:s', strtotime($date . ' ' . $initEndTime) + ($diff_day * 86400)),
        );
      } else {
        $return[] = array(
          'start' => $date . ' ' . $initStartTime,
          'end' => $date . ' ' . $initEndTime,
        );
      }
    }
    usort($return, array('calendar_event', 'sortEventDate'));
    return $return;
  }

  public function preSave() {
    if ($this->getEqLogic_id() == '') {
      throw new Exception(__("L'id de l'équipement ne peut être vide", __FILE__));
    }
    if (trim($this->getCmd_param('eventName')) == '') {
      throw new Exception(__("Le nom de l'évènement ne peut être vide", __FILE__));
    }
    $eqLogic = $this->getEqLogic();
    if (!is_object($eqLogic)) {
      throw new Exception(__("Impossible de trouver l'équipement correspondant à l'id", __FILE__) . ' : ' . $this->getEqLogic_id());
    }
    if ((strtotime($this->getStartDate()) + 15) >= strtotime($this->getEndDate())) {
      throw new Exception(__("La date de début d'évènement ne peut être postérieure ou égale à la date de fin", __FILE__));
    }
    $repeat = $this->getRepeat();
    $allEmpty = true;
    if (isset($repeat['excludeDay']) && is_array($repeat['excludeDay'])) {
      foreach ($repeat['excludeDay'] as $day) {
        if ($day == 1) {
          $allEmpty = false;
          break;
        }
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
          throw new Exception(__('La fréquence de répétition ne peut être vide, nulle ou négative', __FILE__));
        }
        if ($this->getRepeat('unite') == '') {
          throw new Exception(__("L'unité de répétition ne peut être vide", __FILE__));
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
    if (isset($repeat['enable']) && $repeat['enable'] == 1) {
      if ($repeat['nationalDay'] == 'onlyNationalDay' || !isset($repeat['freq']) || $repeat['freq'] == '' || $repeat['unite'] == '') {
        $startDate = (new DateTime('-12 month ' . date('Y-m-d')))->format('Y-m-d H:i:s');
        $endDate = (new DateTime('+12 month ' . date('Y-m-d')))->format('Y-m-d H:i:s');
      } else {
        $startDate = (new DateTime('-' . (8 * $repeat['freq']) . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')))->format('Y-m-d H:i:s');
        $endDate = (new DateTime('+' . (99 * $repeat['freq']) . ' ' . $repeat['unite'] . ' ' . date('Y-m-d')))->format('Y-m-d H:i:s');
      }
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
        $results = $this->calculOccurrence($startDate, $endDate);
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

    $cmd = $eqLogic->getCmd('info', 'in_progress');
    if (is_object($cmd)) {
      $cmd->event($cmd->execute());
    }
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
    $actions = $this->getCmd_param($_action);
    if (is_array($actions)) {
      foreach ($actions as $action) {
        try {
          $options = array();
          if (isset($action['options'])) {
            $options = $action['options'];
          }
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
          log::add('calendar', 'error', $eqLogic->getHumanName() . __("Erreur lors de l'exécution de", __FILE__) . ' ' . $action['cmd'] . '. ' . __('Détails', __FILE__) . ' : ' . $e->getMessage());
        }
      }
    }
    return true;
  }

  public function hasRight($_right, $_user = null) {
    return $this->getEqLogic()->hasRight($_right, $_user);
  }

  public function getName() {
    if ($this->getCmd_param('eventName') != '') {
      return $this->getCmd_param('eventName');
    } else {
      return $this->getCmd_param('name');
    }
  }

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
    $this->_changed = utils::attrChanged($this->_changed, $this->id, $_id);
    $this->id = $_id;
    return $this;
  }

  public function setStartDate($_startDate) {
    $this->_changed = utils::attrChanged($this->_changed, $this->startDate, $_startDate);
    $this->startDate = $_startDate;
    return $this;
  }

  public function setEndDate($_endDate) {
    $this->_changed = utils::attrChanged($this->_changed, $this->endDate, $_endDate);
    $this->endDate = $_endDate;
    return $this;
  }

  public function getEqLogic_id() {
    return $this->eqLogic_id;
  }

  public function getEqLogic() {
    return calendar::byId($this->eqLogic_id);
  }

  public function setEqLogic_id($_eqLogic_id) {
    $this->_changed = utils::attrChanged($this->_changed, $this->eqLogic_id, $_eqLogic_id);
    $this->eqLogic_id = $_eqLogic_id;
    return $this;
  }

  public function getRepeat($_key = '', $_default = '') {
    return utils::getJsonAttr($this->repeat, $_key, $_default);
  }

  public function setRepeat($_key, $_value) {
    $repeat = utils::setJsonAttr($this->repeat, $_key, $_value);
    $this->_changed = utils::attrChanged($this->_changed, $this->repeat, $repeat);
    $this->repeat = $repeat;
    return $this;
  }

  public function getUntil() {
    return $this->until;
  }

  public function setUntil($_until) {
    $this->_changed = utils::attrChanged($this->_changed, $this->until, $_until);
    $this->until = $_until;
    return $this;
  }

  public function getCmd_param($_key = '', $_default = '') {
    return utils::getJsonAttr($this->cmd_param, $_key, $_default);
  }

  public function setCmd_param($_key, $_value) {
    $cmd_param = utils::setJsonAttr($this->cmd_param, $_key, $_value);
    $this->_changed = utils::attrChanged($this->_changed, $this->cmd_param, $cmd_param);
    $this->cmd_param = $cmd_param;
    return $this;
  }

  public function getChanged() {
    return $this->_changed;
  }

  public function setChanged($_changed) {
    $this->_changed = $_changed;
    return $this;
  }
}

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
        log::add('calendar','debug','================PULL============');
        $event = calendar_event::byId($_option['event_id']);
        if (is_object($event)) {
            log::add('calendar','debug','Event : '.print_r($event,true));
            $eqLogic = $event->getEqLogic();
            $nowtime = strtotime('now');
            $repeat = $event->getRepeat();
            if ($repeat['enable'] == 1) {
                if($repeat['nationalDay'] == 'onlyNationalDay'){
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
            log::add('calendar','debug','Startdate : '.$startDate.' / Enddate => '.$endDate);
            if (jeedom::isDateOk() && $eqLogic->getConfiguration('enableCalendar', 1) == 1) {
                $results = $event->calculOccurence($startDate, $endDate);
                log::add('calendar','debug','Occurence : '.print_r($results,true));
                if (count($results) == 0) {
                    return null;
                }
                for ($i = 0; $i < count($results); $i++) {
                    if (strtotime($results[$i]['start']) <= $nowtime && strtotime($results[$i]['end']) > $nowtime) {
                        log::add('calendar','debug','Do start action');
                        $event->doAction('start');
                        break;
                    }
                    if (strtotime($results[$i]['end']) <= $nowtime && (!isset($results[$i + 1]) || strtotime($results[$i + 1]['start']) > $nowtime)) {
                        log::add('calendar','debug','Do end action');
                        $event->doAction('end');
                        break;
                    }
                }
            }
            $event->reschedule();
        }
    }

    public static function start() {
        foreach (calendar_event::all() as $event) {
            $event->reschedule();
        }
    }

    public static function cronHourly() {
        foreach (cron::byClassAndFunction('calendar', 'pull') as $cron) {
            $c = new Cron\CronExpression($cron->getSchedule(), new Cron\FieldFactory);
            try {
                $c->getNextRunDate();
            } catch (Exception $ex) {
                $options = $cron->getOption();
                $cron->remove();
                $event = calendar_event::byId($options['event_id']);
                if (is_object($event)) {
                    $event->reschedule();
                } else {
                    log::add('calendar', 'error', __('Impossible de trouver l\'evenement correspondant à l\'id :', __FILE__) . print_r($options, true));
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

    public function preRemove() {
        $events = calendar_event::getEventsByEqLogic($this->getId());
        foreach ($events as $event) {
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
        $this->refreshWidget();
    }

    public function toHtml($_version = 'dashboard') {
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
                    '#start#' => date_fr(date('D', strtotime($event['start']))) . ' ' . date('d H:i', strtotime($event['start'])),
                    '#end#' => date_fr(date('D', strtotime($event['end']))) . ' ' . date('d H:i', strtotime($event['end'])),
                    '#background_color#' => $event['color'],
                    '#text_color#' => $event['textColor']
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
            '#events#' => $dEvent
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
            'id' => $_id
            );
        $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
        FROM calendar_event
        WHERE id=:id';
        return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
    }

    public static function searchByCmd($_cmd_id) {
        $values = array(
            'cmd_param' => '%"start_type":"cmd"%#' . $_cmd_id . '#%'
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
            date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 40, $easterYear)),
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
       if($repeat['nationalDay'] == 'onlyNationalDay'){
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

public function calculOccurence($_startDate, $_endDate, $_max = 9999999999) {
    $startTime = ($_startDate != null) ? strtotime($_startDate) : 0;
    $endTime = ($_endDate != null) ? strtotime($_endDate) : 999999999999;
    $return = array();
    if ($this->getRepeat('enable') == 1) {
        $repeat = $this->getRepeat();
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $excludeDate = explode(',', $repeat['excludeDate']);
        while ((strtotime($this->getUntil()) > strtotime($startDate) || $this->getUntil() == '0000-00-00 00:00:00') && strtotime($endDate) <= $endTime) {
            if (!in_array(date('Y-m-d', strtotime($startDate)), $excludeDate) && ($startTime < strtotime($startDate) || strtotime($endDate) > $startTime)) {
                if ($repeat['excludeDay'][date('N', strtotime($startDate))] == 1) {
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
            $startDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $startDate));
            $endDate = date('Y-m-d H:i:s', strtotime('+' . $repeat['freq'] . ' ' . $repeat['unite'] . ' ' . $endDate));
        }
    } else {
        if (strtotime($this->getStartDate()) <= $endTime && strtotime($this->getStartDate()) >= $startTime) {
            $return[] = array(
                'start' => $this->getStartDate(),
                'end' => $this->getEndDate(),
                );
        }
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

    if ($this->getRepeat('enable') == 1) {
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
    if ($this->getCmd_param($_action . '_type') == 'cmd') {
        $cmd = cmd::byId(str_replace('#', '', $this->getCmd_param($_action . '_name')));
        if (is_object($cmd) && $cmd->getType() == 'action') {
            $options = $this->getCmd_param($_action . '_options');
            log::add('calendar', 'debug', 'Execution de : ' . $cmd->getHumanName() . ' => ' . print_r($options, true));
            $cmd->execCmd($options);
        }
    }
    if ($this->getCmd_param($_action . '_type') == 'scenario') {
        $scenario = scenario::byId(str_replace(array('#', 'scenario'), '', $this->getCmd_param($_action . '_scenarioName')));
        if (is_object($scenario)) {
            switch ($this->getCmd_param($_action . '_action')) {
                case 'start':
                $name = $this->getCmd_param('eventName',$this->getCmd_param('name'));
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
        }else{
            log::add('calendar','error',__('Scénario non trouvé : ',__FILE__). $this->getCmd_param($_action . '_scenarioName'));
        }
    }
    return true;
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

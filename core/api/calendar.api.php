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

header('Content-Type: application/json');

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
global $jsonrpc;
GLOBAL $_USER_GLOBAL;
if (!is_object($jsonrpc)) {
	throw new Exception(__('JSONRPC object not defined', __FILE__), -32699);
}

$params = $jsonrpc->getParams();

if ($jsonrpc->getMethod() == 'event::getAllCalendarAndEvents') {
    log::add('calendar', 'debug', 'event::getAllCalendarAndEvents');

    $return = array();
    foreach (calendar::byType('calendar') as $calendar) {
        $array_calendar = utils::o2a($calendar);
        $array_calendar['events'] = utils::o2a($calendar->getEvents());
        $return[] = $array_calendar;
    }

    $jsonrpc->makeSuccess($return);
}

if ($jsonrpc->getMethod() == 'event::getAllEvents') {
    log::add('calendar', 'debug', 'calendar_event::getEventsByEqLogic '. $params['eqLogic_id']);
    $getAllEvents = calendar_event::getEventsByEqLogic($params['eqLogic_id']);

    if(count($getAllEvents) <= 0) {
        throw new Exception(__('Aucun calendrier correspondant à', __FILE__) . ' : ' . $params['eqLogic_id']);
    }

    $events = array();
    foreach ($getAllEvents as $event) {
        $events[] = utils::o2a($event);
    }

    $jsonrpc->makeSuccess($events);
}

if ($jsonrpc->getMethod() == 'event::byId') {
    log::add('calendar', 'debug', 'calendar_event::byId '. $params['event_id']);
    $event = calendar_event::byId($params['event_id']);
    if (!is_object($event)) {
        throw new Exception(__('Aucun évènement correspondant à', __FILE__) . ' : ' . $params['event_id']);
    }
    $jsonrpc->makeSuccess(utils::o2a($event));
}

if ($jsonrpc->getMethod() == 'event::save') {

    log::add('calendar', 'debug', 'calendar_event::save id: '. $params['event']['id'] . ' object: ' . json_encode($params['event']));

    $event = null;

    if (!empty($params['event']['id'])) {
        $event = calendar_event::byId($params['event']['id']);
    }
    if (!is_object($event)) {
        $event = new calendar_event();
    }
    utils::a2o($event, jeedom::fromHumanReadable($params['event']));

    $event->save();

    $jsonrpc->makeSuccess(utils::o2a($event));
}

if ($jsonrpc->getMethod() == 'event::remove') {

    log::add('calendar', 'debug', 'calendar_event::remove '. $params['event_id']);

    $event = calendar_event::byId($params['event_id']);
    if (!is_object($event)) {
        throw new Exception(__('Aucun évènement correspondant à', __FILE__) . ' : ' . $params['event_id']);
    }

    try {
        $event->remove();
        $jsonrpc->makeSuccess('success');
    } catch (Exception $e) {
        throw new Exception( $e->getMessage() );
    }
}


throw new Exception(__('Erreur de methode', __FILE__));

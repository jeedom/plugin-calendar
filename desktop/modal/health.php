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

if (!isConnect('admin')) {
	throw new Exception('401 - {{Accès non autorisé}}');
}
$eqLogics = calendar::byType('calendar');
?>

<table class="table table-condensed tablesorter" id="table_healthmpower">
	<thead>
		<tr>
			<th>{{Nom}}</th>
			<th>{{ID}}</th>
			<th>{{Evènements}}</th>
			<th>{{En cours}}</th>
			<th>{{Etat}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($eqLogics as $eqLogic) {
			$eventNumber = count($eqLogic->getEvents());
			$state = '<span class="label label-danger" style="font-size : 1em;cursor:default;">{{Inactif}}</span>';
			if ($eqLogic->getIsEnable() == 1) {
				$state = '<span class="label label-success" style="font-size : 1em;cursor:default;">{{Actif}}</span>';
			}
			echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
			echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getId() . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em;">' . $eventNumber . '</span></td>';
			echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getCmd('info', 'in_progress')->execCmd() . '</span></td>';
			echo '<td>' . $state . '</td>';
			echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
		}
		?>
	</tbody>
</table>

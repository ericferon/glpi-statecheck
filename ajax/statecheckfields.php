<?php
/*
 -------------------------------------------------------------------------
 Statecheck plugin for GLPI
 Copyright (C) 2009-2018 by Eric Feron.
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Statecheck.

 Statecheck is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 at your option any later version.

 Statecheck is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Statecheck. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

$DB = new DB;
//header("Content-Type: text/html; charset=UTF-8");
if (isset($_GET['classname'])) {
	$classname = $DB->escape(utf8_decode($_GET['classname']));
} else {
    die("No 'classname' parameter");
}
if (isset($_GET['mainstatefield'])) {
	$mainstatefield = $DB->escape(utf8_decode($_GET['mainstatefield']));
}/* else {
    die("No 'mainstatefield' parameter");
}*/
$fields = [];
if (isset($_GET['statefields'])) {
	$statefields = $_GET['statefields'];
	if (is_array($statefields)) {
		foreach ($statefields as $statefield) {
			$fields[] = $DB->escape(utf8_decode($statefield));
		}
	}
}/* else {
    die("No 'statefields' parameter");
}*/
$values = [];
if (isset($_GET['statefieldvalues'])) {
	$statefieldvalues = $_GET['statefieldvalues'];
	if (is_array($statefieldvalues)) {
		foreach ($statefieldvalues as $statefieldvalue) {
			$values[] = $DB->escape(utf8_decode($statefieldvalue));
		}
	}
}/* else {
    die("No 'statefieldvalues' parameter");
}*/
$checkedfield = [];
$imainstatefield = array_search($mainstatefield,$fields);
$queryrule = "select glpi_plugin_statecheck_rules.id, glpi_plugin_statecheck_rules.plugin_statecheck_targetstates_id,
glpi_plugin_statecheck_rulecriterias.criteria, glpi_plugin_statecheck_rulecriterias.condition, glpi_plugin_statecheck_rulecriterias.pattern
from glpi_plugin_statecheck_rules
inner join glpi_plugin_statecheck_tables on glpi_plugin_statecheck_rules.plugin_statecheck_tables_id = glpi_plugin_statecheck_tables.id
left join glpi_plugin_statecheck_rulecriterias on glpi_plugin_statecheck_rules.id = glpi_plugin_statecheck_rulecriterias.plugin_statecheck_rules_id
where  class = '".$classname."' 
and is_active = true
and (plugin_statecheck_targetstates_id = 0";
if (!$imainstatefield && !empty($values) && !empty($values[$imainstatefield]))
	$queryrule .= " or plugin_statecheck_targetstates_id = ".$values[$imainstatefield].")";
else
	$queryrule .= ")";
if ($resultrule=$DB->query($queryrule)) {
	while ($datarule=$DB->fetchAssoc($resultrule)) {
		$rules_id = $datarule['id'];
		$criteriacheck = true;
//		get the index of this criteria in fields/values arrays and get the value from the form
		if (!empty($datarule['criteria'])) {
			$istatefield = array_search($datarule['criteria'],$fields);
			if ($istatefield) {
				$formvalue = '';
				if ($istatefield) {
					$formvalue = $values[$istatefield];
				}
				switch ($datarule['condition']) {
					case Rule::PATTERN_IS :
						$criteriacheck &= ($formvalue==$datarule['pattern']?true:false);
					break 1;
					case Rule::PATTERN_IS_NOT :
						$criteriacheck &= ($formvalue!=$datarule['pattern']?true:false);
					break 1;
					case Rule::PATTERN_CONTAIN :
					break 1;
					case Rule::PATTERN_NOT_CONTAIN :
					break 1;
					case Rule::PATTERN_BEGIN :
					break 1;
					case Rule::PATTERN_END :
					break 1;
					case Rule::REGEX_MATCH :
						$criteriacheck &= preg_match($datarule['pattern'],unclean_cross_side_scripting_deep($formvalue));
					break 1;
					case Rule::REGEX_NOT_MATCH :
						$criteriacheck &= (preg_match($datarule['pattern'],unclean_cross_side_scripting_deep($formvalue))?false:true);
					break 1;
					case Rule::PATTERN_EXISTS :
						if (substr($datarule['criteria'],-3) == '_id') {
							$criteriacheck &= ($formvalue!=0?true:false);
						} else {
							$criteriacheck &= ($formvalue!=""?true:false);
						}
					break 1;
					case Rule::PATTERN_DOES_NOT_EXISTS :
						if (substr($datarule['criteria'],-3) == '_id') {
							$criteriacheck &= ($formvalue==0?true:false);
						} else {
							$criteriacheck &= ($formvalue==""?true:false);
						}
					break 1;
					default:
					break 1;
				}
			}
		}
//		if rule applies
		if ($criteriacheck) {
				$queryaction = "select field, action_type, value from glpi_plugin_statecheck_ruleactions ".
							"where plugin_statecheck_rules_id = $rules_id ";
				if ($resultaction=$DB->query($queryaction)) {
					while ($dataaction=$DB->fetchAssoc($resultaction)) {
						$checkedfield[] = $dataaction;
				}
			}
		}
	}
}
//	var_dump($checkedfield);echo '<br/>';
echo json_encode($checkedfield);
?>

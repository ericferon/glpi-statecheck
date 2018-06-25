<?php
/*
 * @version $Id: HEADER 2011-03-12 18:01:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: CAILLAUD Xavier
// Adapted by Eric Feron
// Purpose of file: plugin dataflows v1.0.0 - GLPI 0.80
// ----------------------------------------------------------------------
 */
//define('GLPI_ROOT', '../..');

// Hook done on purge item case

function plugin_pre_item_statecheck($item)
{
	global $CFG_GLPI, $DB, $_SESSION;
//	$table_id = 3;
	$actioncheck = true;
//	if (isset($item['id'])) $item_id = $item['id'];
	if (!isset($_SERVER['HTTP_REFERER'])) return $item;
	$start = strpos($_SERVER['HTTP_REFERER'],'/front') + 7;
	$end = strpos($_SERVER['HTTP_REFERER'],'.',$start);//"glpi_plugin_dataflows_dataflows";
	$frontname = substr($_SERVER['HTTP_REFERER'],$start,$end-$start);
//	retrieve the value of item's state
	$targetstates_id = 0;
	$querystate = "select statetable from glpi_plugin_statecheck_tables";
	if ($resultstate=$DB->query($querystate)) {
		while ($datastate=$DB->fetch_assoc($resultstate)) {
			$statefield = substr($datastate['statetable'],5)."_id";
			if (is_array($item)) {
				if (isset($item[$statefield])) {
					$targetstates_id = $item[$statefield];
				}
			} else {
				if (isset($item->input[$statefield])) {
					$targetstates_id = $item->input[$statefield];
				}
			}
		}
	}
	if ($targetstates_id == 0) {
		return $item;
	}

//	retrieve the rules that apply
	$queryrule = "select glpi_plugin_statecheck_rules.id, glpi_plugin_statecheck_rules.name as rulename, glpi_plugin_statecheck_tables.id as tableid, glpi_plugin_statecheck_tables.name as tablename, glpi_plugin_statecheck_tables.class ".
			"from glpi_plugin_statecheck_rules,glpi_plugin_statecheck_tables ".
			"where glpi_plugin_statecheck_rules.plugin_statecheck_tables_id = glpi_plugin_statecheck_tables.id ".
			"and frontname = '$frontname' ".
			"and (plugin_statecheck_targetstates_id = $targetstates_id or plugin_statecheck_targetstates_id = 0)".
			"and is_active = true";
	if ($resultrule=$DB->query($queryrule)) {
		if (is_array($item)) {
			$item['hookerror'] = false;
			if (isset($item['hookmessage']))
				unset($item['hookmessage']);
		} else {
			$item->hookerror = false;
			if (isset($item->hookmessage))
				unset($item->hookmessage);
		}
		$itemtype = "";
		while ($datarule=$DB->fetch_assoc($resultrule)) {
			$rules_id = $datarule['id'];
			$rules_name = $datarule['rulename'];
			$table_name = $datarule['tablename'];
			$table_id = $datarule['tableid'];
			$itemtype = $datarule['class'];
//			for each rule, retrieve the pre-conditions to apply the rule
			$criteriacheck = true;
			$querycriteria = "select * from glpi_plugin_statecheck_rulecriterias ".
							"where plugin_statecheck_rules_id = $rules_id ";
			if ($resultcriteria=$DB->query($querycriteria)) {
				while ($datacriteria=$DB->fetch_assoc($resultcriteria)) {
					switch ($datacriteria['condition']) {
						case Rule::PATTERN_IS :
							if (is_array($item)) {
								$criteriacheck &= ($item[$datacriteria['criteria']]==$datacriteria['pattern']?true:false);
							} else {
								$criteriacheck &= ($item->input[$datacriteria['criteria']]==$datacriteria['pattern']?true:false);
							}
						break 1;
						case Rule::PATTERN_IS_NOT :
							if (is_array($item)) {
								$criteriacheck &= ($item[$datacriteria['criteria']]!=$datacriteria['pattern']?true:false);
							} else {
								$criteriacheck &= ($item->input[$datacriteria['criteria']]!=$datacriteria['pattern']?true:false);
							}
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
							if (is_array($item)) {
								$criteriacheck &= preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item[$datacriteria['criteria']]));
							} else {
								$criteriacheck &= preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->input[$datacriteria['criteria']]));
							}
						break 1;
						case Rule::REGEX_NOT_MATCH :
							if (is_array($item)) {
								$criteriacheck &= (preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item[$datacriteria['criteria']]))?false:true);
							} else {
								$criteriacheck &= (preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->input[$datacriteria['criteria']]))?false:true);
							}
						break 1;
						case Rule::PATTERN_EXISTS :
							if (substr($datacriteria['criteria'],-3) == '_id') {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]!=0?true:false);
								} else {
									$criteriacheck &= ($item->input[$datacriteria['criteria']]!=0?true:false);
								}
							} else {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]!=""?true:false);
								} else {
									$criteriacheck &= ($item->input[$datacriteria['criteria']]!=""?true:false);
								}
							}
						break 1;
						case Rule::PATTERN_DOES_NOT_EXISTS :
							if (substr($datacriteria['criteria'],-3) == '_id') {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]==0?true:false);
								} else {
									$criteriacheck &= ($item->input[$datacriteria['criteria']]==0?true:false);
								}
							} else {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]==""?true:false);
								} else {
									$criteriacheck &= ($item->input[$datacriteria['criteria']]==""?true:false);
								}
							}
						break 1;

						default:
						break 1;
					}
				}
			}
//			if rule applies
			if ($criteriacheck) {
//				retrieve the fields to check on behalf of this rule and check the condition of the current field value
				$queryaction = "select * from glpi_plugin_statecheck_ruleactions ".
							"where plugin_statecheck_rules_id = $rules_id ";
				if ($resultaction=$DB->query($queryaction)) {
//					get field name and label
					$ruleaction = new PluginStatecheckRuleAction;
					$fields = $ruleaction->getActionFields($table_id);
//					check values against rules
					while ($dataaction=$DB->fetch_assoc($resultaction)) {
						if (substr($dataaction['field'],0,8) == 'session_') {
							switch ($dataaction['field']) {
								case "session_users_id":
									$valuetocheck = $_SESSION['glpiID'];
									$comparisonoperation = $dataaction['action_type'];
									break 1;
								case "session_groups_id":
									$arraytocheck = array();
									$arraytocheck = $_SESSION['glpigroups'];
									$comparisonoperation = $dataaction['action_type']."inarray";
									break 1;
							}
							$fieldtocheck = substr($dataaction['field'],8);
						} else {
							if (is_array($item)) {
								$valuetocheck = $item[$dataaction['field']];
							} else {
								$valuetocheck = $item->input[$dataaction['field']];
							}
							$fieldtocheck = $dataaction['field'];
							$comparisonoperation = $dataaction['action_type'];
						}
						switch ($comparisonoperation) {
							case "isempty":
								if (substr($dataaction['field'],-3) == '_id') {
									$actioncheck = ($valuetocheck==0?true:false);
								} else {
									$actioncheck = ($valuetocheck==""?true:false);
								}
							break 1;
							case "isnotempty":
								if (substr($dataaction['field'],-3) == '_id') {
									$actioncheck = ($valuetocheck!=0?true:false);
								} else {
									$actioncheck = ($valuetocheck!=""?true:false);
								}
							break 1;
							case "is":
								$actioncheck = ($valuetocheck==$dataaction['value']?true:false);
							break 1;
							case "isnot":
								$actioncheck = ($valuetocheck!=$dataaction['value']?true:false);
							break 1;
							case "isinarray":
								$actioncheck = in_array($dataaction['value'],$arraytocheck);
							break 1;
							case "isnotinarray":
								$actioncheck = !in_array($dataaction['value'],$arraytocheck);
							break 1;
							case "regex_check":
								$actioncheck = preg_match($dataaction['value'],$valuetocheck);
							break 1;
							default:
							break 1;
						}
						if (!$actioncheck) {
							if (is_array($item)) {
								$item['hookerror'] = true;
								if (substr($fieldtocheck,-3) == '_id') {
									$ruleactions_value = Html::clean(Dropdown::getDropdownName('glpi_'.substr($fieldtocheck,0,strlen($fieldtocheck)-3),$dataaction['value']));
								}
								else {
									$ruleactions_value = $dataaction['value'];
								}
								$ruleactions_label = $fields[$dataaction['field']]['name'];
								if (isset($item['hookmessage'])) {
									$item['hookmessage'] .= ";".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." '".$ruleactions_value."'";
								}
								else {
									$item['hookmessage'] = __("StateCheck Rules")." :;".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." '".$ruleactions_value."'";
								}
							} else {
								$item->hookerror = true;
								if (substr($fieldtocheck,-3) == '_id') {
									$ruleactions_value = Html::clean(Dropdown::getDropdownName('glpi_'.substr($fieldtocheck,0,strlen($fieldtocheck)-3),$dataaction['value']));
								}
								else {
									$ruleactions_value = $dataaction['value'];
								}
								$ruleactions_label = $fields[$dataaction['field']]['name'];
								if (isset($item->hookmessage)) {
									$item->hookmessage .= ";".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." '".$ruleactions_value."'";
								}
								else {
									$item->hookmessage = __("StateCheck Rules")." :;".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." '".$ruleactions_value."'";
								}
							}
						}
					}
				}
			}
		}
		if ($CFG_GLPI["use_mailing"]) {
			if ($actioncheck)
				$eventtype = "_success";
			else
				$eventtype = "_failure";
			if (is_object($item)) {
				$itemobj = new PluginStatecheckRule;
//				$itemobj->fields = $item->fields;
				$itemobj->fields = $item->input;
				$itemobj->hookerror = $item->hookerror;
//				$itemobj = cast($item,get_class($itemobj));
				NotificationEvent::raiseEvent($itemtype."_".$targetstates_id.$eventtype,$itemobj);
			} else {
				$itemobj = new PluginStatecheckRule;
				$itemobj->fields = $item;
				$itemobj->hookerror = $item['hookerror'];
				if (!$actioncheck) $itemobj->hookmessage = $item['hookmessage'];
				NotificationEvent::raiseEvent($itemtype."_".$targetstates_id.$eventtype,$itemobj);
			}
		}
		if (is_array($item)) {
			if(!$item['hookerror']) {
				unset ($item['hookerror'],$item['hookmessage']);
			}
		} else {
			if(!$item->hookerror) {
				unset ($item->hookerror,$item->hookmessage);
			}
		}
	}
	return $item;
}

function cast($obj, $to_class) {
  if(class_exists($to_class)) {
    $obj_in = serialize($obj);
    $obj_out = 'O:' . strlen($to_class) . ':"' . $to_class . '":' . substr($obj_in, $obj_in[2] + 7);
    return unserialize($obj_out);
  }
  else
    return false;
}
?>
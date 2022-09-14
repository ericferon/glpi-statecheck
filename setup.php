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

// Init the hooks of the plugins -Needed
function plugin_init_statecheck() {
   global $PLUGIN_HOOKS, $DB;

   $PLUGIN_HOOKS['csrf_compliant']['statecheck'] = true;
   $PLUGIN_HOOKS['change_profile']['statecheck'] = ['PluginStatecheckProfile', 'initProfile'];
//   $PLUGIN_HOOKS['assign_to_ticket']['statecheck'] = true;
   
// display a warning message, before item display on form : highlighted fields are controlled
   $PLUGIN_HOOKS['pre_item_form']['statecheck'] = 'hook_pre_item_form';
// highlight controlled fields, after item display on form
   $PLUGIN_HOOKS['post_item_form']['statecheck'] = 'hook_post_item_form';
   //$PLUGIN_HOOKS['assign_to_ticket_dropdown']['statecheck'] = true;
   //$PLUGIN_HOOKS['assign_to_ticket_itemtype']['statecheck'] = ['PluginStatecheckRule_Item'];
   
   Plugin::registerClass('PluginStatecheckRule', [
//         'linkgroup_tech_types'   => true,
//         'linkuser_tech_types'    => true,
         'notificationtemplates_types' => true,
         'document_types'         => true,
//         'ticket_types'           => true,
         'helpdesk_visible_types' => true//,
//         'addtabon'               => 'Supplier'
   ]);
   Plugin::registerClass('PluginStatecheckProfile',
                         ['addtabon' => 'Profile']);
                         
	if ($DB->TableExists("glpi_plugin_statecheck_tables")) {
		$query = "select * from glpi_plugin_statecheck_tables";
		if ($result=$DB->query($query)) {
			$checkitems = [];
			while ($data=$DB->fetchAssoc($result)) {
				$itemtype = $data['class'];
				if (substr($data['name'],0,12) == "glpi_plugin_") {
					$type = substr($data['name'],12,strrpos($data['name'],"_")-12);
				} else {
					$type = substr($data['name'],5);
				}
				$checkitems[$itemtype] = 'plugin_pre_item_statecheck';
			}
			$PLUGIN_HOOKS['pre_item_update']['statecheck'] = $checkitems;
			$PLUGIN_HOOKS['pre_item_add']['statecheck'] = $checkitems;
		}
	}
	   
   if (Session::getLoginUserID()) {

      if (Session::haveRight("plugin_statecheck", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['statecheck'] = ['admin'   => 'PluginStatecheckMenu'];
      }

      if (Session::haveRight("plugin_statecheck", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['statecheck']=1;
      }

      if (class_exists('PluginStatecheckRule_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['plugin_datainjection_populate']['statecheck'] = 'plugin_datainjection_populate_statecheck';
      }

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['statecheck'] = 'plugin_statecheck_postinit';

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['statecheck'] = 'plugin_datainjection_migratetypes_statecheck';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_statecheck() {

   return [
      'name' => _n('Statecheck Rule', 'Statecheck Rules', 2, 'statecheck'),
      'version' => '2.3.7',
      'author'  => "Eric Feron",
      'license' => 'GPLv2+',
      'homepage'=> 'https://github.com/ericferon/glpi-statecheck',
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'dev' => false
         ]
      ]
   ];

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_statecheck_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '10.0', 'lt')
       || version_compare(GLPI_VERSION, '10.1', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '10.0');
      }
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_statecheck_check_config() {
   return true;
}

function plugin_datainjection_migratetypes_statecheck($types) {
   $types[2400] = 'PluginStatecheckRule';
   return $types;
}

function hook_pre_item_form(array $params) {
	global $DB, $_SERVER;
// check that the current form is listed for statecheck
	if (isset($_SERVER['HTTP_REFERER'])) {
		$start = strpos($_SERVER['HTTP_REFERER'],'/front') + 7;
		$end = strpos($_SERVER['HTTP_REFERER'],'.',$start);
		$frontname = substr($_SERVER['HTTP_REFERER'],$start,$end-$start);
        $dbu = new DbUtils();
		if ($dbu->countElementsInTable('glpi_plugin_statecheck_tables', ['frontname' => $frontname])) {
            Session::addMessageAfterRedirect('<font color="red"><b>'.__('!! Highlighted fields are controlled !!').'</b></font>');
            Html::displayMessageAfterRedirect();
		}
	}
}

function hook_post_item_form(array $params) {
	global $DB, $_SERVER;
	if ($params['item']->canCreate())
	{
// check that the current form is listed for statecheck
		if (isset($_SERVER['HTTP_REFERER'])) {
			$start = strpos($_SERVER['HTTP_REFERER'],'/front') + 7;
			$end = strpos($_SERVER['HTTP_REFERER'],'.',$start);
			$frontname = substr($_SERVER['HTTP_REFERER'],$start,$end-$start);
			$query = "select * from glpi_plugin_statecheck_tables where frontname = '".$frontname."'";
			if ($result=$DB->query($query)) {
				if ($DB->fetchAssoc($result)) {
					$classname = get_class($params['item']);
					$statecheckrule = new PluginStatecheckRule;
					$statecheckrule->plugin_statecheck_renderfields($classname);
				}
			}
		}
	}
}

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
		while ($datastate=$DB->fetchAssoc($resultstate)) {
			$statefield = substr($datastate['statetable'],5)."_id";
			if (is_array($item)) {
				if (isset($item[$statefield])) {
					$targetstates_id = $item[$statefield];
				}
			} else {
				if (isset($item->input[$statefield])) {
					$targetstates_id = $item->input[$statefield];
				}
				else if (isset($item->fields[$statefield]))
					$targetstates_id = $item->fields[$statefield];
			}
		}
	}
/*	if ($targetstates_id == 0) {
		return $item;
	}
*/
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
		while ($datarule=$DB->fetchAssoc($resultrule)) {
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
				while ($datacriteria=$DB->fetchAssoc($resultcriteria)) {
					switch ($datacriteria['condition']) {
						case Rule::PATTERN_IS :
							if (is_array($item)) {
								$criteriacheck &= ($item[$datacriteria['criteria']]==$datacriteria['pattern']?true:false);
							} else if (isset($item->input[$datacriteria['criteria']]))
								{
									$criteriacheck &= ($item->input[$datacriteria['criteria']]==$datacriteria['pattern']?true:false);
								}
								else
								{
									$criteriacheck &= ($item->fields[$datacriteria['criteria']]==$datacriteria['pattern']?true:false);
								}
						break 1;
						case Rule::PATTERN_IS_NOT :
							if (is_array($item)) {
								$criteriacheck &= ($item[$datacriteria['criteria']]!=$datacriteria['pattern']?true:false);
							} else if (isset($item->input[$datacriteria['criteria']]))
								{
									$criteriacheck &= ($item->input[$datacriteria['criteria']]!=$datacriteria['pattern']?true:false);
								}
								else
								{
									$criteriacheck &= ($item->fields[$datacriteria['criteria']]!=$datacriteria['pattern']?true:false);
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
							} else if (isset($item->input[$datacriteria['criteria']]))
								{
									$criteriacheck &= preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->input[$datacriteria['criteria']]));
								}
								else
								{
									$criteriacheck &= preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->fields[$datacriteria['criteria']]));
								}
						break 1;
						case Rule::REGEX_NOT_MATCH :
							if (is_array($item)) {
								$criteriacheck &= (preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item[$datacriteria['criteria']]))?false:true);
							} else if (isset($item->input[$datacriteria['criteria']]))
								{
									$criteriacheck &= (preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->input[$datacriteria['criteria']]))?false:true);
								}
								else
								{
									$criteriacheck &= (preg_match($datacriteria['pattern'],unclean_cross_side_scripting_deep($item->fields[$datacriteria['criteria']]))?false:true);
								}
						break 1;
						case Rule::PATTERN_EXISTS :
							if (substr($datacriteria['criteria'],-3) == '_id') {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]!=0?true:false);
								} else if (isset($item->input[$datacriteria['criteria']]))
									{
										$criteriacheck &= ($item->input[$datacriteria['criteria']]!=0?true:false);
									}
									else
									{
										$criteriacheck &= ($item->fields[$datacriteria['criteria']]!=0?true:false);
									}
							} else {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]!=""?true:false);
								} else if (isset($item->input[$datacriteria['criteria']]))
									{
										$criteriacheck &= ($item->input[$datacriteria['criteria']]!=""?true:false);
									}
									else
									{
										$criteriacheck &= ($item->fields[$datacriteria['criteria']]!=""?true:false);
									}
							}
						break 1;
						case Rule::PATTERN_DOES_NOT_EXISTS :
							if (substr($datacriteria['criteria'],-3) == '_id') {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]==0?true:false);
								} else if (isset($item->input[$datacriteria['criteria']]))
									{
										$criteriacheck &= ($item->input[$datacriteria['criteria']]==0?true:false);
									}
									else
									{
										$criteriacheck &= ($item->fields[$datacriteria['criteria']]==0?true:false);
									}
							} else {
								if (is_array($item)) {
									$criteriacheck &= ($item[$datacriteria['criteria']]==""?true:false);
								} else if (isset($item->input[$datacriteria['criteria']]))
									{
										$criteriacheck &= ($item->input[$datacriteria['criteria']]==""?true:false);
									}
									else
									{
										$criteriacheck &= ($item->fields[$datacriteria['criteria']]==""?true:false);
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
					while ($dataaction=$DB->fetchAssoc($resultaction)) {
						if (substr($dataaction['field'],0,8) == 'session_') {
							switch ($dataaction['field']) {
								case "session_users_id":
									$valuetocheck = $_SESSION['glpiID'];
									$comparisonoperation = $dataaction['action_type'];
									break 1;
								case "session_groups_id":
									$arraytocheck = [];
									$arraytocheck = $_SESSION['glpigroups'];
									$comparisonoperation = $dataaction['action_type']."inarray";
									break 1;
							}
							$fieldtocheck = substr($dataaction['field'],8);
						} else {
							if (is_array($item)) {
								$valuetocheck = $item[$dataaction['field']];
							} else if (isset($item->input[$dataaction['field']]))
								{
									$valuetocheck = $item->input[$dataaction['field']];
								}
								else
								{
									$valuetocheck = $item->fields[$dataaction['field']];
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
									$ruleactions_value = trim(strip_tags(html_entity_decode(Dropdown::getDropdownName('glpi_'.substr($fieldtocheck,0,strlen($fieldtocheck)-3),$dataaction['value']))));
								}
								else {
									$ruleactions_value = $dataaction['value'];
								}
								$ruleactions_label = $fields[$dataaction['field']]['name'];
								if (isset($item['hookmessage'])) {
									$item['hookmessage'] .= ";".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
								}
								else {
									$item['hookmessage'] = __("StateCheck Rules")." :;".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
								}
							} else {
								$item->hookerror = true;
								if (substr($fieldtocheck,-3) == '_id') {
									$ruleactions_value = strip_tags(html_entity_decode(Dropdown::getDropdownName('glpi_'.substr($fieldtocheck,0,strlen($fieldtocheck)-3),$dataaction['value'])));
								}
								else {
									$ruleactions_value = $dataaction['value'];
								}
								$ruleactions_label = $fields[$dataaction['field']]['name'];
								if (isset($item->hookmessage)) {
									$item->hookmessage .= ";".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
								}
								else {
									$item->hookmessage = __("StateCheck Rules")." :;".__("Rule")." '$rules_name' ($rules_id) : ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
								}
							}
						}
					}
				}
			}
		}
		if (isset($CFG_GLPI["use_mailing"])) {
			if (is_object($item)) {
				if ($item->hookerror)
					$eventtype = "_failure";
				else
					$eventtype = "_success";
				$itemobj = new PluginStatecheckRule;
				$itemobj->fields = $item->input;
				$itemobj->hookerror = $item->hookerror;
//				cast($item,get_class($itemobj));
				NotificationEvent::raiseEvent($itemtype."_".$targetstates_id.$eventtype,$itemobj);
			} else {
				if ($item[hookerror])
					$eventtype = "_failure";
				else
					$eventtype = "_success";
				$itemobj = new PluginStatecheckRule;
				$itemobj->fields = $item;
				$itemobj->hookerror = $item['hookerror'];
				if (!$actioncheck) $itemobj->hookmessage = $item['hookmessage'];
				NotificationEvent::raiseEvent($itemtype."_".$targetstates_id.$eventtype,$itemobj);
			}
		}
		if (is_array($item)) {
			if($item['hookerror']) {
				Session::addMessageAfterRedirect(str_replace(";","<br/>",$item['hookmessage'])."<br/><font color='red'>".__(" Record not inserted or updated")."</font>", false, ERROR);
				$item['input'] = false; // do not insert or update
			} else {
				unset ($item['hookerror'],$item['hookmessage']);
			}
		} else {
			if($item->hookerror) {
				Session::addMessageAfterRedirect(str_replace(";","<br/>",$item->hookmessage)."<br/><font color='red'>".__(" Record not inserted or updated")."</font>", false, ERROR);
				$item->input = false; // do not insert or update
			} else {
				unset ($item->hookerror,$item->hookmessage);
			}
		}
	}
	return $item;
}

?>

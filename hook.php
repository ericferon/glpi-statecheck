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

function plugin_statecheck_install() {
   global $DB;

   include_once (Plugin::getPhpDir("statecheck")."/inc/profile.class.php");

   $update=false;
   if (!$DB->TableExists("glpi_plugin_statecheck_rules")) {

		$DB->runFile(Plugin::getPhpDir("statecheck")."/sql/empty-1.0.2.sql");

//      insert notification template for archisw, if installed
        $query = "select * from glpi_plugins where directory = 'archisw' and state = 1";
        $result_query = $DB->query($query);
        if($DB->numRows($result_query) == 1) {
            $DB->runFile(Plugin::getPhpDir("statecheck")."/sql/archisw-1.0.0.sql");
        }
        else {
           $query = "delete from glpi_plugin_statecheck_tables where name = 'glpi_plugin_archisw_swcomponents';";
           $DB->query($query);
        }

//      insert notification template for dataflows, if installed
        $query = "select * from glpi_plugins where directory = 'dataflows' and state = 1";
        $result_query = $DB->query($query);
        if($DB->numRows($result_query) == 1) {
            $DB->runFile(Plugin::getPhpDir("statecheck")."/sql/dataflows-1.0.0.sql");
        }
        else {
           $query = "delete from glpi_plugin_statecheck_tables where name = 'glpi_plugin_dataflows_dataflows';";
           $DB->query($query);
        }

//      insert notification template for databases, if installed
        $query = "select * from glpi_plugins where directory = 'databases' and state = 1";
        $result_query = $DB->query($query);
        if($DB->numRows($result_query) == 1) {
            $DB->runFile(Plugin::getPhpDir("statecheck")."/sql/databases-1.0.0.sql");
        }
        else {
           $query = "delete from glpi_plugin_statecheck_tables where name = 'glpi_plugin_databases_databases';";
           $DB->query($query);
        }
	}
	else {
/*		if ($DB->TableExists("glpi_plugin_statecheck_rules") && !!$DB->FieldExists("glpi_plugin_statecheck_rules","plugin_statecheck_indicators_id")) {
			$update=true;
			$DB->runFile(Plugin::getPhpDir("statecheck")."/sql/update-1.0.1.sql");
		}
*/
        if (!$DB->FieldExists("glpi_plugin_statecheck_rules", "is_active_warn_popup")) {
			$DB->runFile(Plugin::getPhpDir("statecheck")."/sql/update-1.0.2.sql");
        }
    }

   if ($update) {
      $query_="SELECT *
            FROM `glpi_plugin_statecheck_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_statecheck_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }

      $query="ALTER TABLE `glpi_plugin_statecheck_profiles`
               DROP `name` ;";
      $result=$DB->query($query);

      Plugin::migrateItemType(
         [2400=>'PluginStatecheckRule'],
         ["glpi_savedsearches", "glpi_savedsearches_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_items_tickets"],
         ["glpi_plugin_statecheck_rules_items"]);

   }

   PluginStatecheckProfile::initProfile();
   PluginStatecheckProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_statecheck_profiles');

   return true;
}

function plugin_statecheck_uninstall() {
	global $DB;

	include_once (Plugin::getPhpDir("statecheck")."/inc/profile.class.php");
	include_once (Plugin::getPhpDir("statecheck")."/inc/menu.class.php");

	$tables = ["glpi_plugin_statecheck_rules",
					"glpi_plugin_statecheck_tables",
					"glpi_plugin_statecheck_targetstates",
					"glpi_plugin_statecheck_rulecriterias",
					"glpi_plugin_statecheck_ruleactions",
					"glpi_plugin_statecheck_profiles"];

	foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

	$tables_glpi = ["glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_savedsearches",
					"glpi_logs"];

	foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginStatecheck%' ;");

	//notifications
		$notif = new Notification();

	$options = ['itemtype' => 'PluginStatecheckRule',
                    'event'    => 'success',
					'FIELDS'   => 'id'];
	foreach ($DB->request('glpi_notifications', $options) as $data) {
		$notif->delete($data);
	}
	$options = ['itemtype' => 'PluginStatecheckRule',
                    'event'    => 'failure',
                    'FIELDS'   => 'id'];
	foreach ($DB->request('glpi_notifications', $options) as $data) {
		$notif->delete($data);
	}
	//templates
	$template = new NotificationTemplate();
	$translation = new NotificationTemplateTranslation();
	$options = ['itemtype' => 'PluginStatecheckRule',
                    'FIELDS'   => 'id'];
	foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
		$options_template = ['notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id'];

		foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
		}
		$template->delete($data);
	}
	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(['itemtype'=>'PluginStatecheckRule']);
   }
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginStatecheckProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginStatecheckMenu::removeRightsFromSession();
   PluginStatecheckProfile::removeRightsFromSession();

   return true;
}


// Define dropdown relations
function plugin_statecheck_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("statecheck"))
		return ["glpi_plugin_statecheck_rules"=>["glpi_plugin_statecheck_rulecriterias"=>"plugin_statecheck_rules_id"],
					"glpi_plugin_statecheck_rules"=>["glpi_plugin_statecheck_ruleactions"=>"plugin_statecheck_rules_id"],
					 "glpi_plugin_statecheck_tables"=>["glpi_plugin_statecheck_rules"=>"plugin_statecheck_tables_id"],
					 "glpi_plugin_statecheck_targetstates"=>["glpi_plugin_statecheck_rules"=>"plugin_statecheck_targetstates_id"],
					 "glpi_entities"=>["glpi_plugin_statecheck_rules"=>"entities_id"],
					 "glpi_groups"=>["glpi_plugin_statecheck_rules"=>"groups_id"],
					 "glpi_users"=>["glpi_plugin_statecheck_rules"=>"users_id"]
					 ];
   else
      return [];
}

// Define Dropdown tables to be manage in GLPI :
function plugin_statecheck_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("statecheck"))
		return ['PluginStatecheckTable'=>_n('Table', 'Tables', Session::getPluralNumber())
                ];
   else
      return [];
}

////// SEARCH FUNCTIONS ///////() {

function plugin_statecheck_getAddSearchOptions($itemtype) {

   $sopt=[];

   return $sopt;
}

function plugin_statecheck_giveItem($type,$ID,$data,$num) {
   global $DB;

   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/*function plugin_statecheck_MassiveActions($type) {

   if (in_array($type,PluginStatecheckRule::getTypes(true))) {
      return ['PluginStatecheckRule'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_statecheck__add_item' =>
                                                              __('Associate to the statecheck rule', 'statecheck')];
   }
   return [];
}
*/

function plugin_datainjection_populate_statecheck() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginStatecheckRuleInjection'] = 'statecheck';
}

function plugin_pre_item_add_statecheck($item) {
    global $DB;
    $firstKey = array_key_first($item);
    $currentValues = $item[$firstKey];
    $item = $currentValues;

    $queryrule = "SELECT `glpi_plugin_statecheck_rules`.`id`, `glpi_plugin_statecheck_rules`.`name` as rulename, `glpi_plugin_statecheck_tables`.`id` as tableid, `glpi_plugin_statecheck_tables`.`name` as tablename, `glpi_plugin_statecheck_tables`.`class` FROM `glpi_plugin_statecheck_rules`, `glpi_plugin_statecheck_tables` WHERE `glpi_plugin_statecheck_rules`.`plugin_statecheck_tables_id` = `glpi_plugin_statecheck_tables`.`id` AND `frontname` LIKE '$firstKey' AND `is_active` = true";

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
            $querycriteria = "SELECT * from `glpi_plugin_statecheck_rulecriterias` WHERE `plugin_statecheck_rules_id` = $rules_id";
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
                $queryaction = "SELECT * from `glpi_plugin_statecheck_ruleactions` WHERE `plugin_statecheck_rules_id` = $rules_id";
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
                                $actioncheck = ($valuetocheck==html_entity_decode($dataaction['value'])?true:false);
                            break 1;
                            case "isnot":
                                $actioncheck = ($valuetocheck!=html_entity_decode($dataaction['value'])?true:false);
                            break 1;
                            case "isinarray":
                                $actioncheck = in_array(html_entity_decode($dataaction['value']),$arraytocheck);
                            break 1;
                            case "isnotinarray":
                                $actioncheck = !in_array(html_entity_decode($dataaction['value']),$arraytocheck);
                            break 1;
                            case "regex_check":
                                $actioncheck = preg_match(html_entity_decode($dataaction['value']),$valuetocheck);
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
                                    $item['hookmessage'] .= ";\n".__("Rule")." '$rules_name': ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
                                }
                                else {
                                    $item['hookmessage'] = __("Rule")." '$rules_name': ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
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
                                    $item->hookmessage .= ";\n".__("Rule")." '$rules_name': ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
                                }
                                else {
                                    $item->hookmessage = __("Rule")." '$rules_name': ".__("Field")." '".$ruleactions_label."' ".__("is not compliant with the rule")." : ".$dataaction['action_type']." ".$ruleactions_value;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (is_array($item)) {
            if($item['hookerror']) {
                $item['input'] = false; // do not insert or update
            } else {
                unset ($item['hookerror'],$item['hookmessage']);
            }
        } else {
            if($item->hookerror) {
                $item->input = false; // do not insert or update
            } else {
                unset ($item->hookerror,$item->hookmessage);
            }
        }

    }

    return $item;
}

?>

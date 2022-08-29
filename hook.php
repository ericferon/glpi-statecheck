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

		$DB->runFile(Plugin::getPhpDir("statecheck")."/sql/empty-1.0.0.sql");
   
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
*/	}


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

?>

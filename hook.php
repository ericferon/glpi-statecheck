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

   include_once (GLPI_ROOT."/plugins/statecheck/inc/profile.class.php");

   $update=false;
   if (!$DB->TableExists("glpi_plugin_statecheck_rules")) {

		$DB->runFile(GLPI_ROOT ."/plugins/statecheck/sql/empty-1.0.0.sql");
	}
	else {
/*		if ($DB->TableExists("glpi_plugin_statecheck_rules") && !FieldExists("glpi_plugin_statecheck_rules","plugin_statecheck_indicators_id")) {
			$update=true;
			$DB->runFile(GLPI_ROOT ."/plugins/statecheck/sql/update-1.0.1.sql");
		}
*/	}

   
   if ($DB->TableExists("glpi_plugin_statecheck_profiles")) {
   
      $notepad_tables = array('glpi_plugin_statecheck_rules');

      foreach ($notepad_tables as $t) {
         // Migrate data
         if (FieldExists($t, 'notepad')) {
            $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
            foreach ($DB->request($query) as $data) {
               $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('PluginStatecheckRule', '".$data['id']."',
                              '".addslashes($data['notepad'])."', NOW(), NOW())";
               $DB->queryOrDie($iq, "0.85 migrate notepad data");
            }
            $query = "ALTER TABLE `glpi_plugin_statecheck_rules` DROP COLUMN `notepad`;";
            $DB->query($query);
         }
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
         array(2400=>'PluginStatecheckRule'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_items_tickets"),
         array("glpi_plugin_statecheck_rules_items"));

   }

   PluginStatecheckProfile::initProfile();
   PluginStatecheckProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_statecheck_profiles');
   
   return true;
}

function plugin_statecheck_uninstall() {
	global $DB;
   
	include_once (GLPI_ROOT."/plugins/statecheck/inc/profile.class.php");
	include_once (GLPI_ROOT."/plugins/statecheck/inc/menu.class.php");
   
	$tables = array("glpi_plugin_statecheck_rules",
					"glpi_plugin_statecheck_tables",
					"glpi_plugin_statecheck_targetstates",
					"glpi_plugin_statecheck_rulecriterias",
					"glpi_plugin_statecheck_ruleactions",
					"glpi_plugin_statecheck_profiles");

	foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

	$tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs");

	foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginStatecheck%' ;");

	//notifications
		$notif = new Notification();
   
	$options = array('itemtype' => 'PluginStatecheckRule',
                    'event'    => 'success',
					'FIELDS'   => 'id');
	foreach ($DB->request('glpi_notifications', $options) as $data) {
		$notif->delete($data);
	}
	$options = array('itemtype' => 'PluginStatecheckRule',
                    'event'    => 'failure',
                    'FIELDS'   => 'id');
	foreach ($DB->request('glpi_notifications', $options) as $data) {
		$notif->delete($data);
	}
	//templates
	$template = new NotificationTemplate();
	$translation = new NotificationTemplateTranslation();
	$options = array('itemtype' => 'PluginStatecheckRule',
                    'FIELDS'   => 'id');
	foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
		$options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
		foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
		}
		$template->delete($data);
	}
	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginStatecheckRule'));
   }
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginStatecheckProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   PluginStatecheckMenu::removeRightsFromSession();
   PluginStatecheckProfile::removeRightsFromSession();
   
   return true;
}


// Define dropdown relations
function plugin_statecheck_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("statecheck"))
		return array("glpi_plugin_statecheck_rules"=>array("glpi_plugin_statecheck_rulecriterias"=>"plugin_statecheck_rules_id"),
					"glpi_plugin_statecheck_rules"=>array("glpi_plugin_statecheck_ruleactions"=>"plugin_statecheck_rules_id"),
					 "glpi_plugin_statecheck_tables"=>array("glpi_plugin_statecheck_rules"=>"plugin_statecheck_tables_id"),
					 "glpi_plugin_statecheck_targetstates"=>array("glpi_plugin_statecheck_rules"=>"plugin_statecheck_targetstates_id"),
					 "glpi_entities"=>array("glpi_plugin_statecheck_rules"=>"entities_id"),
					 "glpi_groups"=>array("glpi_plugin_statecheck_rules"=>"groups_id"),
					 "glpi_users"=>array("glpi_plugin_statecheck_rules"=>"users_id")
					 );
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_statecheck_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("statecheck"))
		return array('PluginStatecheckTable'=>_n('Table', 'Tables', Session::getPluralNumber())
                );
   else
      return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_statecheck_getAddSearchOptions($itemtype) {

   $sopt=array();

   return $sopt;
}

function plugin_statecheck_giveItem($type,$ID,$data,$num) {
   global $DB;

   return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/*function plugin_statecheck_MassiveActions($type) {

   if (in_array($type,PluginStatecheckRule::getTypes(true))) {
      return array('PluginStatecheckRule'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_statecheck__add_item' =>
                                                              __('Associate to the statecheck rule', 'statecheck'));
   }
   return array();
}
*/

function plugin_datainjection_populate_statecheck() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginStatecheckRuleInjection'] = 'statecheck';
}

?>
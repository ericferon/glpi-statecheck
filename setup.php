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
   $PLUGIN_HOOKS['change_profile']['statecheck'] = array('PluginStatecheckProfile', 'initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['statecheck'] = true;
   
   //$PLUGIN_HOOKS['assign_to_ticket_dropdown']['statecheck'] = true;
   //$PLUGIN_HOOKS['assign_to_ticket_itemtype']['statecheck'] = array('PluginStatecheckRule_Item');
   
   Plugin::registerClass('PluginStatecheckRule', array(
//         'linkgroup_tech_types'   => true,
//         'linkuser_tech_types'    => true,
         'notificationtemplates_types' => true,
         'document_types'         => true,
         'ticket_types'           => true,
         'helpdesk_visible_types' => true//,
//         'addtabon'               => 'Supplier'
   ));
   Plugin::registerClass('PluginStatecheckProfile',
                         array('addtabon' => 'Profile'));
                         
   //Plugin::registerClass('PluginStatecheckRule_Item',
   //                      array('ticket_types' => true));

	if ($DB->TableExists("glpi_plugin_statecheck_tables")) {
		$query = "select * from glpi_plugin_statecheck_tables";
		if ($result=$DB->query($query)) {
			while ($data=$DB->fetch_assoc($result)) {
				$itemtype = $data['class'];
				if (substr($data['name'],0,12) == "glpi_plugin_") {
					$type = substr($data['name'],12,strrpos($data['name'],"_")-12);
				} else {
					$type = substr($data['name'],5);
				}
				$PLUGIN_HOOKS['pre_item_update'][$type] = array($itemtype => 'plugin_pre_item_statecheck');
				$PLUGIN_HOOKS['pre_item_add'][$type] = array($itemtype => 'plugin_pre_item_statecheck');
			}
		}
	}
	   
   if (Session::getLoginUserID()) {

      $plugin = new Plugin();
      if (!$plugin->isActivated('environment')
         && Session::haveRight("plugin_statecheck", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['statecheck'] = array('admin'   => 'PluginStatecheckMenu');
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

   return array (
      'name' => _n('Statecheck Rule', 'Statecheck Rules', 2, 'statecheck'),
      'version' => '2.0.1',
      'author'  => "Eric Feron",
      'license' => 'GPLv2+',
      'homepage'=>'',
      'minGlpiVersion' => '0.90',
   );

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_statecheck_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.90','lt') || version_compare(GLPI_VERSION,'9.3','ge')) {
      _e('This plugin requires GLPI >= 0.90', 'statecheck');
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

?>

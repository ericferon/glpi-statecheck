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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginStatecheckNotificationTargetRule extends NotificationTarget {

   const STATECHECK_LOGGED_GROUP = 2300;
   const STATECHECK_LOGGED_USER = 2301;
   const STATECHECK_ITEM_GROUP_MANAGER = 2302;
   const STATECHECK_ITEM_GROUP = 2303;
   const STATECHECK_ITEM_USER = 2304;
   
function getEvents() {
	global $LANG, $DB;
	$events = array();
	$queryclass = "select * from glpi_plugin_statecheck_tables";
	if ($resultclass=$DB->query($queryclass)) {
		while ($dataclass=$DB->fetch_assoc($resultclass)) {
			$statetable = $dataclass['statetable'];
			$querystate = "select * from $statetable";
			if ($resultstate=$DB->query($querystate)) {
				while ($datastate=$DB->fetch_assoc($resultstate)) {
					$events[$dataclass['class'].'_'.$datastate['id'].'_success'] = $dataclass['comment']." ".__('Statecheck succeeded for ')."'".$datastate['name']."'";
					$events[$dataclass['class'].'_'.$datastate['id'].'_failure'] = $dataclass['comment']." ".__('Statecheck failed for ')."'".$datastate['name']."'";
				}
			}
		}
	}
	asort ($events);
	return $events ;
}

   /**
    * Get additionnals targets for Tickets
    */
	function addAdditionalTargets($event='') {
      global $LANG, $DB;
      
	$eventparts = explode("_",$event);
	$itemtype = $eventparts[0];
	$queryclass = "select * from glpi_plugin_statecheck_tables where class = '$itemtype'";
	if ($resultclass=$DB->query($queryclass)) {
		$dataclass=$DB->fetch_assoc($resultclass);
		$frontname = $dataclass['frontname'];
		$this->addTarget(PluginStatecheckNotificationTargetRule::STATECHECK_ITEM_GROUP_MANAGER,__("Manager").' '.__("Group responsible of the ").$frontname,Notification::SUPERVISOR_GROUP_TYPE);
		$this->addTarget(PluginStatecheckNotificationTargetRule::STATECHECK_ITEM_USER,__("User responsible of the ").$frontname);
		$this->addTarget(PluginStatecheckNotificationTargetRule::STATECHECK_LOGGED_USER,__("Logged user"));
		$this->addTarget(PluginStatecheckNotificationTargetRule::STATECHECK_LOGGED_GROUP,__("Logged user's group"),Notification::SUPERVISOR_GROUP_TYPE);
      }

   }

   function addSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case PluginStatecheckNotificationTargetRule::STATECHECK_LOGGED_GROUP :
//            $this->getLoggedGroupAddress();
            break;
         case PluginStatecheckNotificationTargetRule::STATECHECK_LOGGED_USER :
            $this->getLoggedUserAddress($options);
            break;
         case PluginStatecheckNotificationTargetRule::STATECHECK_ITEM_GROUP_MANAGER :
            $this->getItemGroupAddress($options);
            break;
         case PluginStatecheckNotificationTargetRule::STATECHECK_ITEM_GROUP :
            $this->getItemGroupAddress($options);
            break;
         case PluginStatecheckNotificationTargetRule::STATECHECK_ITEM_USER :
            $this->getItemUserAddress($options);
            break;
      }
   }

   function getLoggedGroupAddress () {
      global $DB;

      $group_field = "groups_id";

      if (isset($this->obj->fields[$group_field])
                && $this->obj->fields[$group_field]>0) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`)".
                   $this->getJoinProfileSql()."
                    WHERE `glpi_groups_users`.`groups_id` = '".$this->obj->fields[$group_field]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   function getLoggedUserAddress($options=array()) {
      global $DB;

      if (isset($options['tasks_id'])) {
         $query = "SELECT DISTINCT `glpi_users`.`email` AS email,
                          `glpi_users`.`language` AS language
                   FROM `glpi_users`
                   WHERE `glpi_users`.`id` = '".$_SESSION['glpiID']."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getItemGroupAddress ($options=array()) {
      global $DB;

      if (isset($options['groups_id'])
                && $options['groups_id']>0
                && isset($options['tasks_id'])) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`".
                   " LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) ".
//                   " LEFT JOIN `glpi_plugin_statecheck_tasks` ON (`glpi_plugin_statecheck_tasks`.`groups_id` = `glpi_groups_users`.`groups_id`)".
//                   " WHERE `glpi_plugin_statecheck_tasks`.`id` = '".$options['tasks_id'].
				   "'";
         
         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function getItemUserAddress ($options=array()) {
      global $DB;

      if (isset($options['groups_id'])
                && $options['groups_id']>0
                && isset($options['tasks_id'])) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`".
                   " LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) ".
//                   " LEFT JOIN `glpi_plugin_statecheck_tasks` ON (`glpi_plugin_statecheck_tasks`.`groups_id` = `glpi_groups_users`.`groups_id`)".
//                   " WHERE `glpi_plugin_statecheck_tasks`.`id` = '".$options['tasks_id'].
				   "'";
         
         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function addDataForTemplate($event, $options=array()) {
     global $LANG,$CFG_GLPI,$DB,$_SESSION;

		$classinfo = explode("_",$this->raiseevent);
		$this->data['##statecheck.class##'] = $classinfo[0];
		$this->data['##statecheck.stateid##'] = $classinfo[1];
		$events = $this->getAllEvents();

		$queryclass = "select * from glpi_plugin_statecheck_tables where class = '".$classinfo[0]."'";
		if ($resultclass=$DB->query($queryclass)) {
			$dataclass=$DB->fetch_assoc($resultclass);
			$statetable = $dataclass['statetable'];
			$this->data['##statecheck.classname##'] = $dataclass['comment'];
			$this->data['##lang.statecheck.status##'] = __('Mail to user');
			$this->data['##statecheck.status##'] =  Dropdown::getDropdownName($statetable, $classinfo[1]);
			$frontname = $dataclass['frontname'];
			$tablename = $dataclass['name'];
			$tableid = $dataclass['id'];
			$queryfield = "show columns from $tablename";
			if ($resultfield=$DB->query($queryfield)) {
				while ($datafield=$DB->fetch_assoc($resultfield)) {
					$fieldname = $datafield['Field'];
					$tagname = "##statecheck.".$frontname.".".$fieldname."##";
					if (substr($fieldname,-3) == '_id') {
						$dropdowntable = "glpi_".substr($fieldname,0,-3);
						$this->data[$tagname] = Html::clean(Dropdown::getDropdownName($dropdowntable, $this->obj->getField($fieldname)));
					} else {
						$this->data[$tagname] = Html::clean(stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>",$this->obj->getField($fieldname))));
					}
				}
			}
			$itemobj = new $classinfo[0];
			$searchfields = $itemobj->getsearchOptions();
			foreach($searchfields as $fieldlabel) {
				if (isset($fieldlabel['table']) && isset($fieldlabel['name'])) {
					$fieldtable = $fieldlabel['table'];
					$fielddisplay = isset($fieldlabel['datatype'])?$fieldlabel['datatype']:"text";
					if (substr($fielddisplay,-8) != "dropdown") {
						$fieldname = $fieldlabel['field'];
					} else {
						$fieldname = substr($fieldtable,5)."_id";
					}
					$fielddescr = $fieldlabel['name'];
					$tagname = "##lang.statecheck.".$frontname.".".$fieldname."##";
					$this->data[$tagname] = $fielddescr;
				}
			}
		}
		$this->data['##statecheck.action##'] = $events[$event];

		$this->data['##lang.statecheck.title##'] = $events[$event];
		$this->data['##statecheck.id##'] = $this->obj->getField("id");
		$this->data['##statecheck.loggeduser##'] = $_SESSION['glpiname'];
		$this->data['##lang.statecheck.errormessage##'] = __('On failure message');
		$this->data['##statecheck.errormessage##'] = Html::clean(stripslashes(str_replace(array('\r\n', '\n', '\r', ';'), "<br/>",$this->obj->getField('hookmessage'))));

         
      $this->getTags();
      foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
         if (!isset($this->data[$tag])) {
            $this->data[$tag] = $values['label'];
         }
      }
   }


   function getTags() {
      global $LANG;

      $tags = array('statecheck.name'           => __('Name'),
                    'statecheck.entity'         => __('Entity'));

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'   => $tag,
                                   'label' => $label,
                                   'value' => true));
      }

/*      $this->addTagToList(array('tag'     => 'statechecks',
                                'label'   => $LANG['reports'][57],
                                'value'   => false,
                                'foreach' => true));
*/
      asort($this->tag_descriptions);
   }
}

?>
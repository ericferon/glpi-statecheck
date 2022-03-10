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

/** @file
* @brief
*/

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"ruleaction.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
} else if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

Session::checkLoginUser();

// Non define case
if (isset($_POST["sub_type"]) && class_exists($_POST["sub_type"])) {
   if (!isset($_POST["field"])) {
      $_POST["field"] = key(PluginStatecheckRule::getActionsByType($_POST["sub_type"]));
   }
   if (!($item = getItemForItemtype($_POST["sub_type"]))) {
      exit();
   }
   if (!isset($_POST[$item->getRuleIdField()])) {
      exit();
   }

   // Existing action
   if ($_POST['ruleactions_id'] > 0) {
      $already_used = false;
   } else { // New action
      $ra           = getItemForItemtype($item->getStatecheckRuleActionClass());
      $used         = $ra->getAlreadyUsedForRuleID($_POST[$item->getRuleIdField()],
                                                   $item->getType());
      $already_used = in_array($_POST["field"], $used);
   }

   echo "<table width='100%'><tr><td width='30%'>";

   $action_type = '';
   if (isset($_POST["action_type"])) {
      $action_type = $_POST["action_type"];
   }

   $randaction = PluginStatecheckRuleAction::dropdownActions(['subtype'     => $_POST["sub_type"],
                                                   'name'        => "action_type",
                                                   'field'       => $_POST["field"],
                                                   'value'       => $action_type,
                                                   'alreadyused' => $already_used]);

   echo "</td><td>";
   echo "<span id='action_type_span$randaction'>\n";
   echo "</span>\n";

   $paramsaction = ['action_type'                   => '__VALUE__',
                         'field'                         => $_POST["field"],
                         'sub_type'                      => $_POST["sub_type"],
						 'plugin_statecheck_tables_id'	  => $_POST["plugin_statecheck_tables_id"]];

   Ajax::updateItemOnSelectEvent("dropdown_action_type$randaction", "action_type_span$randaction",
                                 Plugin::getWebDir('statecheck')."/ajax/ruleactionvalue.php", $paramsaction);

   if (isset($_POST['value'])) {
      $paramsaction['value'] = stripslashes($_POST['value']);
   }

   Ajax::updateItem("action_type_span$randaction", Plugin::getWebDir('statecheck')."/ajax/ruleactionvalue.php",
                    $paramsaction, "dropdown_action_type$randaction");
   echo "</td></tr></table>";
}
?>

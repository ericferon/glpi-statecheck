<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015-2016 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.
 
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
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

include ('../../../inc/includes.php');

$rule = new PluginStatecheckRule();

//$rule->check(READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$rulecriteria = new PluginStatecheckRuleCriteria("PluginStatecheckRule");
$ruleaction   = new PluginStatecheckRuleAction("PluginStatecheckRule");

if (isset($_POST["add_action"])) {
   $rule->check(CREATE);
   $ruleaction->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $rule->check(UPDATE);
   $rule->update($_POST);

   Event::log($_POST['id'], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["add"])) {
   $rule->check(CREATE);

   $newID = $rule->add($_POST);
   Event::log($newID, "rules", 4, "setup",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $newID));
   Html::redirect($_SERVER['HTTP_REFERER']."?id=$newID");

} else if (isset($_POST["purge"])) {
   $rule->check(PURGE);
   $rule->deleteRuleOrder($_POST["ranking"]);
   $rule->delete($_POST, 1);

   Event::log($_POST["id"], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $rule->redirectToList();
}

Html::header(PluginStatecheckRule::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'admin',
             'pluginstatecheckmenu');

$rule->display(array('id' => $_GET["id"]));
Html::footer();
?>
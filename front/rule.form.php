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

use Glpi\Event;
include ('../../../inc/includes.php');

$rule = new PluginStatecheckRule();

//$rule->check(READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$rulecriteria = new PluginStatecheckRuleCriteria("PluginStatecheckRule");
$ruleaction   = new PluginStatecheckRuleAction("PluginStatecheckRule");

if (isset($_POST["add_action"])) {
   $rule->check(-1, CREATE, $_POST);
   $ruleaction->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $rule->check($_POST['id'], UPDATE);
   $rule->update($_POST);

   Event::log($_POST['id'], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["add"])) {
   $rule->check(-1, CREATE, $_POST);

   $newID = $rule->add($_POST);
   Event::log($newID, "rules", 4, "setup",
              sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $newID));
   Html::redirect($_SERVER['HTTP_REFERER']."?id=$newID");

} else if (isset($_POST["purge"])) {
   $rule->check($_POST['id'], PURGE);
//   $rule->deleteRuleOrder($_POST["ranking"]);
   $rule->delete($_POST, 1);

   Event::log($_POST["id"], "rules", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $rule->redirectToList();
}

Html::header(PluginStatecheckRule::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'admin',
             'pluginstatecheckmenu');

$rule->display(['id' => $_GET["id"]]);
Html::footer();
?>

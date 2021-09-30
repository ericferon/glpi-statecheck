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

include ('../../../inc/includes.php');

$rule = new PluginStatecheckRule();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_POST["action"])) {
   $rule->check($_POST["id"], UPDATE);
   $rule->changeRuleOrder($_POST["id"],$_POST["action"], $_POST['condition']);
   Html::back();
// POST and GET needed to manage reload
} else if (isset($_POST["replay_rule"]) || isset($_GET["replay_rule"])) {
   $rule->check($_POST["id"], UPDATE);

   // Current time
   $start = explode(" ",microtime());
   $start = $start[0]+$start[1];

   // Limit computed from current time
   $max = get_cfg_var("max_execution_time");
   $max = $start + ($max>0 ? $max/2.0 : 30.0);

   Html::header(Rule::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "admin", 'pluginstatecheckmenu');

   if (!(isset($_POST['replay_confirm']) || isset($_GET['offset']))
       && $rule->warningBeforeReplayRulesOnExistingDB($_SERVER['PHP_SELF'])) {
      Html::footer();
      exit();
   }

   echo "<table class='tab_cadrehov'>";

   echo "<tr><th><div class='relative b'>" .$rule->getTitle(). "<br>" .
         __('Replay the rules dictionary'). "</div></th></tr>\n";
   echo "<tr><td class='center'>";
   Html::createProgressBar(__('Work in progress...'));
   echo "</td></tr>\n";
   echo "</table>";

   if (!isset($_GET['offset'])) {
      // First run
      $offset       = $rule->replayRulesOnExistingDB(0, $max, [], $_POST);
      $manufacturer = (isset($_POST["manufacturer"]) ? $_POST["manufacturer"] : 0);

   } else {
      // Next run
      $offset       = $rule->replayRulesOnExistingDB($_GET['offset'], $max, [],
                                                               $_GET);
      $manufacturer = $_GET["manufacturer"];

      // global start for stat
      $start = $_GET["start"];
   }

   if ($offset < 0) {
      // Work ended
      $end   = explode(" ",microtime());
      $duree = round($end[0]+$end[1]-$start);
      Html::changeProgressBarMessage(sprintf(__('Task completed in %s'),
                                             Html::timestampToString($duree)));
      echo "<a href='".$_SERVER['PHP_SELF']."'>".__('Back')."</a>";

   } else {
      // Need more work
      Html::redirect($_SERVER['PHP_SELF']."?start=$start&replay_rule=1&offset=$offset&manufacturer=".
                     "$manufacturer");
   }

   Html::footer(true);
   exit();
}

Html::header(PluginStatecheckRule::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'admin', 'pluginstatecheckmenu');
			 
if ($rule->canView() || Session::haveRight("config", UPDATE)) {
   Search::show('PluginStatecheckRule');
} else {
   Html::displayRightError();
}
Html::footer();
?>

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

$rule = new PluginStatecheckRule;
$rule->getFromDB(intval($_POST['plugin_statecheck_rules_id']));

$criteria = new PluginStatecheckRuleCriteria();

if (isset($_POST["add"])) {
   $criteria->check(-1, CREATE, $_POST);
   $criteria->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $criteria->check($_POST['id'], UPDATE);
   $criteria->update($_POST);

   Html::back();

} else if (isset($_POST["purge"])) {
   $criteria->check($_POST['id'], PURGE);
   $criteria->delete($_POST, 1);

   Html::back();
}
?>
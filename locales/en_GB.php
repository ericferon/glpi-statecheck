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


$title = "StateCheck Rules";

$LANG['plugin_statecheck'][0] = "Name";
$LANG['plugin_statecheck'][1] = "Table";
$LANG['plugin_statecheck'][2] = "Target State ('----'=All)";
$LANG['plugin_statecheck'][3] = "Description";
$LANG['plugin_statecheck'][4] = "".$title."";
$LANG['plugin_statecheck'][5] = "Active";
$LANG['plugin_statecheck'][6] = "Status StartDate";
$LANG['plugin_statecheck'][7] = "On success message";
$LANG['plugin_statecheck'][8] = "On failure message";
$LANG['plugin_statecheck'][9] = "Last saved value of";
$LANG['plugin_statecheck'][10] = "Check value of";
$LANG['plugin_statecheck'][11] = "Check type";
$LANG['plugin_statecheck'][12] = "is";
$LANG['plugin_statecheck'][13] = "is not";
$LANG['plugin_statecheck'][14] = "is empty";
$LANG['plugin_statecheck'][15] = "is not empty";
$LANG['plugin_statecheck'][16] = "is regexp";
$LANG['plugin_statecheck'][17] = "Load Method";
$LANG['plugin_statecheck'][18] = "Mail to group";
$LANG['plugin_statecheck'][19] = "Mail to user";
$LANG['plugin_statecheck'][20] = "Priority";
$LANG['plugin_statecheck'][21] = "Associated ".$title."(s)";
$LANG['plugin_statecheck'][22] = "Type";
$LANG['plugin_statecheck'][23] = "Indicator";
$LANG['plugin_statecheck'][24] = "Logged user";
$LANG['plugin_statecheck'][25] = "Logged user's group";
$LANG['plugin_statecheck'][29] = "Rule";
$LANG['plugin_statecheck'][30] = "Field";
$LANG['plugin_statecheck'][31] = "is not compliant with the rule";
$LANG['plugin_statecheck'][32] = "Record not inserted";
$LANG['plugin_statecheck'][33] = "Record not updated";
$LANG['plugin_statecheck'][34] = "!! Highlighted fields are controlled !!";
$LANG['plugin_statecheck'][40] = "is the user";
$LANG['plugin_statecheck'][41] = "is member of the group";

$LANG['plugin_statecheck']['mailing'][0] = "With creation, modification, deletion of a project";
$LANG['plugin_statecheck']['mailing'][1] = "With creation, modification, deletion, expiration of a task";
$LANG['plugin_statecheck']['mailing'][2] = "succeeded";
$LANG['plugin_statecheck']['mailing'][3] = "failed";
$LANG['plugin_statecheck']['mailing'][4] = "Statecheck succeeded for ";
$LANG['plugin_statecheck']['mailing'][5] = "Statecheck failed for ";
$LANG['plugin_statecheck']['mailing'][6] = "A project has been deleted";
$LANG['plugin_statecheck']['mailing'][7] = "A task has been added";
$LANG['plugin_statecheck']['mailing'][8] = "A task has been modified or opened";
$LANG['plugin_statecheck']['mailing'][9] = "A task has been deleted";
$LANG['plugin_statecheck']['mailing'][10] = "A task is outdated";
$LANG['plugin_statecheck']['mailing'][11] = "Group responsible of the ";
$LANG['plugin_statecheck']['mailing'][12] = "User responsible of the ";
$LANG['plugin_statecheck']['mailing'][13] = "Groupe responsible of the item";
$LANG['plugin_statecheck']['mailing'][14] = "Modifications";
$LANG['plugin_statecheck']['mailing'][15] = "Outdated tasks";
$LANG['plugin_statecheck']['mailing'][16] = "Associated tasks";

$LANG['plugin_statecheck']['profile'][0] = "Rights management";
$LANG['plugin_statecheck']['profile'][1] = "$title";

$LANG['plugin_statecheck']['setup'][1] = "Flow group";
$LANG['plugin_statecheck']['setup'][11] = "Server";
$LANG['plugin_statecheck']['setup'][12] = "Language";
$LANG['plugin_statecheck']['setup'][14] = "Supplier";
$LANG['plugin_statecheck']['setup'][15] = "Associated item(s)";
$LANG['plugin_statecheck']['setup'][23] = "Associate";
$LANG['plugin_statecheck']['setup'][24] = "Dissociate";
$LANG['plugin_statecheck']['setup'][25] = "Associate to dataflow";
$LANG['plugin_statecheck']['setup'][28] = "Editor";
$LANG['plugin_statecheck']['setup'][29] = "Duplicate";
?>
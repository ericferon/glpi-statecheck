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

   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      // Yllen: you always have parent for action
      $rule = $options['parent'];

      if ($ID > 0) {
//         $this->check($ID, READ);
      } else {
         // Create item
         $options[static::$items_id] = $rule->getField('id');

         //force itemtype of parent
         static::$itemtype = get_class($rule);

         $this->check(-1, CREATE, $options);
      }
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1 center'>";
      echo "<td>"._n('Check value of', 'Check values of', 1) . "</td><td colspan='3'>";
      echo "<input type='hidden' name='".$rule->getStatecheckRuleIdField()."' value='".
             $this->fields[static::$items_id]."'>";
      $used = $this->getAlreadyUsedForRuleID($this->fields[static::$items_id], $rule->getType());
      // On edit : unset selected value
      if ($ID
          && isset($used[$this->fields['field']])) {
         unset($used[$this->fields['field']]);
      }
      $rand   = $rule->dropdownActions(array('value' => $this->fields['field'],
                                             'used'  => $used));
      $params = array('field'                 => '__VALUE__',
                      'sub_type'              => $rule->getType(),
                      'ruleactions_id'        => $this->getID(),
                      $rule->getStatecheckRuleIdField() => $this->fields[static::$items_id],
					  'plugin_statecheck_tables_id' => $this->input["parent"]->fields["plugin_statecheck_tables_id"]
					  );

      Ajax::updateItemOnSelectEvent("dropdown_field$rand", "action_span",
                                    $CFG_GLPI["root_doc"]."/plugins/statecheck/ajax/ruleaction.php", $params);

      if (isset($this->fields['field']) && !empty($this->fields['field'])) {
         $params['field']       = $this->fields['field'];
         $params['action_type'] = $this->fields['action_type'];
         $params['value']       = $this->fields['value'];
         echo "<script type='text/javascript' >\n";
         Ajax::updateItemJsCode("action_span",
                                 $CFG_GLPI["root_doc"]."/plugins/statecheck/ajax/ruleaction.php",
                                 $params);
         echo '</script>';
      }
      echo "</td></tr>";
      echo "<tr><td colspan='4'><span id='action_span'>\n";
      echo "</span></td>\n";
      echo "</tr>\n";
      $this->showFormButtons($options);
   }

}
?>
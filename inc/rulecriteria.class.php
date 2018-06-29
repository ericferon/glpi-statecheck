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

      // Yllen: you always have parent for criteria
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

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>"._n('Criterion', 'Criteria', 1) . "</td><td colspan='3'>";
      echo "<input type='hidden' name='".$rule->getStatecheckRuleIdField()."' value='".
             $this->fields[$rule->getStatecheckRuleIdField()]."'>";

      $rand   = $rule->dropdownCriteria(array('value' => $this->fields['criteria']));
      $params = array('criteria' => '__VALUE__',
                      'rand'     => $rand,
                      'sub_type' => $rule->getType(),
					  'plugin_statecheck_tables_id' => $this->input["parent"]->fields["plugin_statecheck_tables_id"]);

      Ajax::updateItemOnSelectEvent("dropdown_criteria$rand", "criteria_span",
                                    $CFG_GLPI["root_doc"]."/plugins/statecheck/ajax/rulecriteria.php", $params);

      if (isset($this->fields['criteria']) && !empty($this->fields['criteria'])) {
         $params['criteria']  = $this->fields['criteria'];
         $params['condition'] = $this->fields['condition'];
         $params['pattern']   = $this->fields['pattern'];
         echo "<script type='text/javascript' >\n";
         Ajax::updateItemJsCode("criteria_span",
                                 $CFG_GLPI["root_doc"]."/plugins/statecheck/ajax/rulecriteria.php",
                                 $params);
         echo '</script>';
      }

      if ($rule->specific_parameters) {
         $itemtype = get_class($rule).'Parameter';
         echo "<img alt='' title=\"".__s('Add a criterion')."\" src='".$CFG_GLPI["root_doc"].
                "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                onClick=\"".Html::jsGetElementbyID('addcriterion'.$rand).".dialog('open');\">";
         Ajax::createIframeModalWindow('addcriterion'.$rand,
                                       Toolbox::getItemTypeFormURL($itemtype),
                                       array('reloadonclose' => true));
      }

      echo "</td></tr>";
      echo "<tr><td colspan='4'>";
	  echo "<span id='criteria_span'>\n";
      echo "</span></td></tr>\n";
      $this->showFormButtons($options);
   }

}
?>

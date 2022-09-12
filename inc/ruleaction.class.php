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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginStatecheckRuleAction extends RuleAction {

   // From CommonDBChild
   static public $items_id        = 'plugin_statecheck_rules_id';
   public $dohistory              = true;
   public $auto_message_on_action = false;

   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }


   /**
    * @param $rule_type
   **/
   function __construct($rule_type='PluginStatecheckRule') {
      static::$itemtype = $rule_type;
   }


   /**
    * @since version 0.84.3
    *
    * @see CommonDBTM::post_getFromDB()
    */
   function post_getFromDB() {

      // Get correct itemtype if defult one is used
      if (static::$itemtype == 'PluginStatecheckRule') {
/*         $rule = new PluginStatecheckRule();
         if ($rule->getFromDB($this->fields['plugin_statecheck_rules_id'])) {
            static::$itemtype = "PluginStatecheckRule";
         }
*/      }
   }


   /**
    * Get title used in rule
    *
    * @param $nb  integer  (default 0)
    *
    * @return Title of the rule
   **/
   static function getTypeName($nb=0) {
      return _n('Action', 'Actions', $nb, 'statecheck');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='PluginStatecheckRule') {
         return self::getTypeName(2);
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='PluginStatecheckRule') {
         $self = new self();

         $self->showForm($item->getID(), ['parent'=>$item]);
      }
      return true;
   }

   /**
    * @see CommonDBTM::getRawName()
   **/
   function getRawName() {

      if ($rule = getItemForItemtype(static::$itemtype)) {
         return Html::clean($rule->getMinimalActionText($this->fields));
      }
      return '';
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBChild::post_addItem()
   **/
   function post_addItem() {

      parent::post_addItem();
      if (isset($this->input['plugin_statecheck_rules_id'])
          && ($realrule = PluginStatecheckRule::getRuleObjectByID($this->input['plugin_statecheck_rules_id']))) {
         $realrule->update(['id'       => $this->input['plugin_statecheck_rules_id'],
                                 'date_mod' => $_SESSION['glpi_currenttime']]);
      }
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBTM::post_purgeItem()
   **/
   function post_purgeItem() {

      parent::post_purgeItem();
      if (isset($this->fields['plugin_statecheck_rules_id'])
          && ($realrule = PluginStatecheckRule::getRuleObjectByID($this->fields['plugin_statecheck_rules_id']))) {
         $realrule->update(['id'       => $this->fields['plugin_statecheck_rules_id'],
                                 'date_mod' => $_SESSION['glpi_currenttime']]);
      }
   }


   /**
    * @since version 0.84
   **/
   function prepareInputForAdd($input) {

      if (!isset($input['field']) || empty($input['field'])) {
         return false;
      }
      return parent::prepareInputForAdd($input);
   }


   function getSearchOptions() {

      $tab                        = [];

      $tab[1]['table']            = $this->getTable();
      $tab[1]['field']            = 'action_type';
      $tab[1]['name']             = self::getTypeName(1);
      $tab[1]['massiveaction']    = false;
      $tab[1]['datatype']         = 'specific';
      $tab[1]['additionalfields'] = ['plugin_statecheck_rules_id'];

      $tab[2]['table']            = $this->getTable();
      $tab[2]['field']            = 'field';
      $tab[2]['name']             = _n('Field', 'Fields', Session::getPluralNumber(), 'statecheck');
      $tab[2]['massiveaction']    = false;
      $tab[2]['datatype']         = 'specific';
      $tab[2]['additionalfields'] = ['plugin_statecheck_rules_id'];

      $tab[3]['table']            = $this->getTable();
      $tab[3]['field']            = 'value';
      $tab[3]['name']             = __('Value', 'statecheck');
      $tab[3]['massiveaction']    = false;
      $tab[3]['datatype']         = 'specific';
      $tab[3]['additionalfields'] = ['plugin_statecheck_rules_id'];

      return $tab;
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
   **/
   static function getSpecificValueToDisplay($field, $values, array $options=[]) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'field' :
            $generic_rule = new PluginStatecheckRule;
            if (isset($values['plugin_statecheck_rules_id'])
                && !empty($values['plugin_statecheck_rules_id'])
                && $generic_rule->getFromDB($values['plugin_statecheck_rules_id'])) {
               if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                  return $rule->getAction($values[$field]);
               }
            }
            break;

         case 'action_type' :
            return self::getActionByID($values[$field]);

         case 'value' :
            if (!isset($values["field"]) || !isset($values["action_type"])) {
               return NOT_AVAILABLE;
            }
            $generic_rule = new PluginStatecheckRule;
            if (isset($values['plugin_statecheck_rules_id'])
                && !empty($values['plugin_statecheck_rules_id'])
                && $generic_rule->getFromDB($values['plugin_statecheck_rules_id'])) {
               if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                  return $rule->getCriteriaDisplayPattern($values["criteria"], $values["condition"],
                                                          $values[$field]);
               }
            }
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name               (default '')
    * @param $values             (default '')
    * @param $options      array
   **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=[]) {
      global $DB;

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      switch ($field) {
         case 'field' :
            $generic_rule = new PluginStatecheckRule;
            if (isset($values['plugin_statecheck_rules_id'])
                && !empty($values['plugin_statecheck_rules_id'])
                && $generic_rule->getFromDB($values['plugin_statecheck_rules_id'])) {
               if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                  $options['value'] = $values[$field];
                  $options['name']  = $name;
                  return $rule->dropdownActions($options);
               }
            }
            break;

         case 'action_type' :
            $generic_rule = new PluginStatecheckRule;
            if (isset($values['plugin_statecheck_rules_id'])
                && !empty($values['plugin_statecheck_rules_id'])
                && $generic_rule->getFromDB($values['plugin_statecheck_rules_id'])) {
               return self::dropdownActions(['subtype'     => $generic_rule->fields["sub_type"],
                                                  'name'        => $name,
                                                  'value'       => $values[$field],
                                                  'alreadyused' => false,
                                                  'display'     => false]);
            }
            break;

         case 'pattern' :
            if (!isset($values["field"]) || !isset($values["action_type"])) {
               return NOT_AVAILABLE;
            }
            $generic_rule = new PluginStatecheckRule;
            if (isset($values['plugin_statecheck_rules_id'])
                && !empty($values['plugin_statecheck_rules_id'])
                && $generic_rule->getFromDB($values['plugin_statecheck_rules_id'])) {
               if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                  /// TODO review it : need to pass display param and others...
                  return $this->displayActionSelectPattern($values);
               }
            }
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Get all actions for a given rule
    *
    * @param $ID the rule_description ID
    *
    * @return an array of RuleAction objects
   **/
   function getStatecheckRuleActions($ID) {
      global $DB;

      $sql = "SELECT *
              FROM `".$this->getTable()."`
              WHERE `".static::$items_id."` = '$ID'
              ORDER BY `id`";
      $result = $DB->query($sql);

      $rules_actions = [];
      while ($rule = $DB->fetchAssoc($result)) {
         $tmp             = new self();
         $tmp->fields     = $rule;
         $rules_actions[] = $tmp;
      }
      return $rules_actions;
   }


   /**
    * Add an action
    *
    * @param $action    action type
    * @param $ruleid    rule ID
    * @param $field     field name
    * @param $value     value
   **/
   function addActionByAttributes($action, $ruleid, $field, $value) {

      $input["action_type"]      = $action;
      $input["field"]            = $field;
      $input["value"]            = $value;
      $input[static::$items_id]  = $ruleid;
      $this->add($input);
   }


   /**
    * Display a dropdown with all the possible actions
    *
    * @param $options   array of possible options:
    *    - subtype
    *    - name
    *    - field
    *    - value
    *    - alreadyused
    *    - display
   **/
   static function dropdownActions($options=[]) {

      $p['subtype']     = '';
      $p['name']        = '';
      $p['field']       = '';
      $p['value']       = '';
      $p['alreadyused'] = false;
      $p['display']     = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      if ($rule = getItemForItemtype($p['subtype'])) {
         $actions_options = $rule->getAllActions();
         $actions         = ["is","isnot","isempty","isnotempty","regex_check"];
         // Manage permit several.
         $field = $p['field'];
         if ($p['alreadyused']) {
            if (!isset($actions_options[$field]['permitseveral'])) {
               return false;
            }
            $actions = $actions_options[$field]['permitseveral'];

         } else {
            if (isset($actions_options[$field]['force_actions'])) {
               $actions = $actions_options[$field]['force_actions'];
            }
         }

         $elements = [];
         foreach ($actions as $action) {
            $elements[$action] = self::getActionByID($action);
         }

         return Dropdown::showFromArray($p['name'], $elements, ['value'   => $p['value'],
                                                                     'display' => $p['display']]);
      }
   }


   static function getActions() {

      return ['assign'              => __('Assign', 'statecheck'),
                   'append'              => __('Add', 'statecheck'),
                   'regex_result'        => __('Assign the value from regular expression', 'statecheck'),
                   'append_regex_result' => __('Add the result of regular expression', 'statecheck'),
                   'affectbyip'          => __('Assign: equipment by IP address', 'statecheck'),
                   'affectbyfqdn'        => __('Assign: equipment by name + domain', 'statecheck'),
                   'affectbymac'         => __('Assign: equipment by MAC address', 'statecheck'),
                   'compute'             => __('Recalculate', 'statecheck'),
                   'send'                => __('Send', 'statecheck'),
                   'add_validation'      => __('Send', 'statecheck'),
                   'is'					 => __('is', 'statecheck'),
				   'isnot'				 => __('is not', 'statecheck'),
				   'isempty'			 => __('is empty', 'statecheck'),
				   'isnotempty'			 => __('is not empty', 'statecheck'),
				   'regex_check'         => __('is regexp', 'statecheck'),
				   'isuser'				 => __('is the user', 'statecheck'),
				   'ismemberof'			 => __('is member of the group', 'statecheck'),
                   'fromuser'            => __('Copy from user', 'statecheck'),
                   'fromitem'            => __('Copy from item', 'statecheck')];
   }


   static function getActionFields($table_id = -1) {
	global $CFG_GLPI;

	$rule = new PluginStatecheckRule;
	$fields = $rule->getCriterias($table_id);

    return $fields;
   }
   /**
    * @param $ID
   **/
   static function getActionByID($ID) {

      $actions = self::getActions();
      if (isset($actions[$ID])) {
         return $actions[$ID];
      }
      return '';
   }


   /**
    * @param $action
    * @param $regex_result
   **/
   static function getRegexResultById($action, $regex_result) {

      $results = [];

      if (count($regex_result) > 0) {
         if (preg_match_all("/#([0-9])/", $action, $results) > 0) {
            foreach ($results[1] as $result) {
               $action = str_replace("#$result",
                                     (isset($regex_result[$result])?$regex_result[$result]:''),
                                     $action);
            }
         }
      }
      return $action;
   }


   /**
    * @param $plugin_statecheck_rules_id
    * @param $sub_type
   **/
   function getAlreadyUsedForRuleID($plugin_statecheck_rules_id, $sub_type) {
      global $DB;

      if ($rule = getItemForItemtype($sub_type)) {
         $actions_options = $rule->getAllActions();

         $actions = [];
         $res     = $DB->query("SELECT `field`
                                FROM `".$this->getTable()."`
                                WHERE `".static::$items_id."` = '".$plugin_statecheck_rules_id."'");

         while ($action = $DB->fetchAssoc($res)) {
            if (isset($actions_options[$action["field"]])
                 && ($action["field"] != 'groups_id_validate')
                 && ($action["field"] != 'users_id_validate')
                 && ($action["field"] != 'affectobject')) {
               $actions[$action["field"]] = $action["field"];
            }
         }
         return $actions;
      }
   }


   /**
    * @param $options   array
   **/
   function displayActionSelectPattern($options=[]) {

      $display = false;

      $param['value'] = '';
      if (isset($options['value'])) {
         $param['value'] = $options['value'];
      }

      switch ($options["action_type"]) {
         //If a regex value is used, then always display an autocompletiontextfield
         case "regex_result" :
         case "regex_check" :
         case "append_regex_result" :
            echo Html::input('value',['value' => $param['value'], 'id' => "value"]);
//            Html::autocompletionTextField($this, "value", $param);
            break;

         case 'fromuser' :
         case 'fromitem' :
            Dropdown::showYesNo("value", $param['value'], 0);
            $display = true;
            break;

         case "isempty" :
         case "isnotempty" :
            break;

         default :
			 $tableid = $_POST["plugin_statecheck_tables_id"];
             $actions = PluginStatecheckRuleAction::getActions();
			 $fields = PluginStatecheckRuleAction::getActionFields($tableid);
             if (isset($fields[$options["field"]]['type'])) {

               switch($fields[$options["field"]]['type']) {
                  case "dropdown" :
                     $table   = $fields[$options["field"]]['table'];
                     $param['name'] = "value";
                     if (isset($fields[$options["field"]]['condition'])) {
                        $param['condition'] = $fields[$options["field"]]['condition'];
                     }
                     Dropdown::show(getItemTypeForTable($table), $param);
                     $display = true;
                     break;

                  case "dropdown_tickettype" :
                     Ticket::dropdownType('value', $param);
                     $display = true;
                     break;

                  case "dropdown_assign" :
                     $param['name']  = 'value';
                     $param['right'] = 'own_ticket';
                     User::dropdown($param);
                     $display = true;
                     break;

                  case "dropdown_users" :
                     $param['name']  = 'value';
                     $param['right'] = 'all';
                     User::dropdown($param);
                     $display = true;
                     break;

                  case "dropdown_urgency" :
                     $param['name']  = 'value';
                     Ticket::dropdownUrgency($param);
                     $display = true;
                     break;

                  case "dropdown_impact" :
                     $param['name']  = 'value';
                     Ticket::dropdownImpact($param);
                     $display = true;
                     break;

                  case "dropdown_priority" :
                     if ($_POST["action_type"] != 'compute') {
                        $param['name']  = 'value';
                        Ticket::dropdownPriority($param);
                     }
                     $display = true;
                     break;

                  case "dropdown_status" :
                     $param['name']  = 'value';
                     Ticket::dropdownStatus($param);
                     $display = true;
                     break;

                  case "yesonly" :
                     Dropdown::showYesNo("value",$param['value'],0);
                     $display = true;
                     break;

                  case "yesno" :
                     Dropdown::showYesNo("value", $param['value']);
                     $display = true;
                     break;

                  case "dropdown_management":
                     $param['name']                 = 'value';
                     $param['management_restrict']  = 2;
                     $param['withtemplate']         = false;
                     Dropdown::showGlobalSwitch(0, $param);
                     $display = true;
                     break;

                  case "dropdown_users_validate" :
                     $used = [];
                     if ($item = getItemForItemtype($options["sub_type"])) {
                        $rule_data = getAllDatasFromTable('glpi_ruleactions',
                                                          "`action_type` = 'add_validation'
                                                           AND `field` = 'users_id_validate'
                                                           AND `".$item->getStatecheckRuleIdField()."`
                                                            = '".$options[$item->getStatecheckRuleIdField()]."'");

                        foreach ($rule_data as $data) {
                           $used[] = $data['value'];
                        }
                     }
                     $param['name']  = 'value';
                     $param['right'] = ['validate_incident', 'validate_request'];
                     $param['used']  = $used;
                     User::dropdown($param);
                     $display        = true;
                     break;

                  case "dropdown_groups_validate" :
                     $used = [];
                     if ($item = getItemForItemtype($options["sub_type"])) {
                        $rule_data = getAllDatasFromTable('glpi_ruleactions',
                                                          "`action_type` = 'add_validation'
                                                           AND `field` = 'groups_id_validate'
                                                           AND `".$item->getStatecheckRuleIdField()."`
                                                            = '".$options[$item->getStatecheckRuleIdField()]."'");

                        foreach ($rule_data as $data) {
                           $used[] = $data['value'];
                        }
                     }

                     $condition = "(SELECT count(`users_id`)
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` = `glpi_groups`.`id`)";
                     $param['name']      = 'value';
                     $param['condition'] = $condition;
                     $param['right']     = ['validate_incident', 'validate_request'];
                     $param['used']      = $used;
                     Group::dropdown($param);
                     $display            = true;
                     break;

                  case "dropdown_validation_percent" :
                     $ticket = new Ticket();
                     echo $ticket->getValueToSelect('validation_percent', 'value', $param['value']);
                     $display       = true;
                     break;

                  default :
                     if ($rule = getItemForItemtype($options["sub_type"])) {
//                        $display = $rule->displayAdditionalRuleAction($fields[$options["field"]], $param['value']);
                     }
                     break;
               }
            }

            if (!$display) {
               echo Html::input('value',['value' => $param['value'], 'id' => "value"]);
//               Html::autocompletionTextField($this, "value", $param);
            }
      }
   }

   /** form for rule action
    *
    * @since version 0.85
    *
    * @param $ID      integer : Id of the action
    * @param $options array of possible options:
    *     - rule Object : the rule
   **/
   function showForm($ID, $options=[]) {
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
      echo "<td>"._n('Check value of', 'Check values of', 1, 'statecheck') . "</td><td colspan='3'>";
      echo "<input type='hidden' name='".$rule->getStatecheckRuleIdField()."' value='".
             $this->fields[static::$items_id]."'>";
      $used = $this->getAlreadyUsedForRuleID($this->fields[static::$items_id], $rule->getType());
      // On edit : unset selected value
      if ($ID
          && isset($used[$this->fields['field']])) {
         unset($used[$this->fields['field']]);
      }
      $rand   = $rule->dropdownActions(['value' => $this->fields['field'],
                                             'used'  => $used]);
      $params = ['field'                 => '__VALUE__',
                      'sub_type'              => $rule->getType(),
                      'ruleactions_id'        => $this->getID(),
                      $rule->getStatecheckRuleIdField() => $this->fields[static::$items_id],
					  'plugin_statecheck_tables_id' => $this->input["parent"]->fields["plugin_statecheck_tables_id"]
					  ];

      Ajax::updateItemOnSelectEvent("dropdown_field$rand", "action_span",
                                    Plugin::getWebDir('statecheck')."/ajax/ruleaction.php", $params);

      if (isset($this->fields['field']) && !empty($this->fields['field'])) {
         $params['field']       = $this->fields['field'];
         $params['action_type'] = $this->fields['action_type'];
         $params['value']       = $this->fields['value'];
         echo "<script type='text/javascript' >\n";
         Ajax::updateItemJsCode("action_span",
                                 Plugin::getWebDir('statecheck')."/ajax/ruleaction.php",
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

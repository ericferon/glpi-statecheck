# Statecheck
Statecheck Plugin for GLPI

This plugin let you check the validity of form fields.
For instance you can make a field mandatory or not, depending on some value in another field

For that purpose, you define one or more rules :
- a rule is related to 1 inventory class (computer, dataflow, ...) and depends on the value of 1 field (= 1 state) in this class (f.i a type or category value)
- for each rule, you can specify other conditions for a rule to be fired (f.i the value of another field must contain or start with a certain value)
- for each rule, you specify which check is performed (f.i yet another field may not be empty, or must comply with a regular expression)<br/>
Several rules may be defined for 1 state, with different supplementary conditions.

To use the statecheck plugin :
1. Modify the file GLPI_ROOT/inc/plugin.class.php, function doHook :
- line 1124 and 1143 : change to : $retcode = call_user_func(...
- before the end of the (line 1116) "if (($param != NULL) && is_object($param))" block and the corresponding "else" block, add : 
		if (isset($retcode)) {
			$data = $retcode;
		}
2. Modify the file GLPI_ROOT/inc/commondbtm.class.php
- function "add" and "update", 
	* pass the $options array by reference : put & before $options in the function definition :
		function add(array $input,& $options=array(), $history=true) {
		function update(array $input, $history=1,& $options=array()) {
	* modify the line 'Plugin::doHook("pre_item_... into '$ret=Plugin::doHook("pre_item_...'
	* add just after these lines :
		if (isset($ret['hookerror']) && $ret['hookerror']) {
			$options['message']['plugin_statecheck'] = $ret['hookmessage'];
			return 0;
		}
	  } else {
		if (isset($ret->hookerror) && $ret->hookerror) {
			$options['message']['plugin_statecheck'] = $ret->hookmessage;
			return 0;
		}
3. At the end of the file GLPI_ROOT/plugins/yourplugin/hook.php, add the following line : 
include_once(GLPI_ROOT . "/plugins/statecheck/hookinclude.php");
4. In GLPI_ROOT/plugins/yourplugin/inc/yourclass.class.php, 
- in the method showForm :
	* add at the beginning :
		$plugin = new Plugin();
		if ($plugin->isActivated("statecheck")) {
			Session::addMessageAfterRedirect('<font color="red"><b>'.__('!! Highlighted fields are controlled !!').'</b></font>');
			Html::displayMessageAfterRedirect();
		}
	* add at the end (before "return true") :
		if ($this->canCreate() && $plugin->isActivated("statecheck")) {
			$classname = get_class($this);
			plugin_statecheck_renderfields($classname);
		}
 

5. You may want to warn the user in case of problem.
	In your file GLPI_ROOT/plugins/yourplugin/front/yourview.form.php :
		- after the "include", define the $options array :
			$options = array();
		- in the "if (isset($_POST["add"]))" block replace 
			Html::back();
			by 
			if (!$newID) {
				if (isset($options['message']['plugin_statecheck'])) {
					echo "<script type='text/javascript' >\n";
					echo "alert(\"".str_replace(";","\\n",$options['message']['plugin_statecheck'])."\\n".__("Record not inserted")." !\");";
					echo "history.go(-1);";
					echo "</script>";
				}
				else {
					if ($_SESSION['glpibackcreated']) {
						Html::redirect($dataflow->getFormURL()."?id=".$newID);
					}
					Html::back();
				}
			} else {
				if ($_SESSION['glpibackcreated']) {
					Html::redirect($dataflow->getFormURL()."?id=".$newID);
				}
				Html::back();
			}
		- in the "if (isset($_POST["update"]))" block replace 
			Html::back();
			by 
			if (isset($options['message']['plugin_statecheck'])) {
				echo "<script type='text/javascript' >\n";
				echo "alert(\"".str_replace(";","\\n",$options['message']['plugin_statecheck'])."\\n".__("Record not updated")." !\");";
				echo "history.go(-1);";
				echo "</script>";
			}
			else {
				if ($_SESSION['glpibackcreated']) {
					Html::redirect($dataflow->getFormURL()."?id=".$newID);
				}
				Html::back();
			}
5. In the menu Dropdowns->Statecheck->Tables, define the object class on which you want to perform checks :
- Name : the DB table name in which your objects are stored
- Class : the PHP class name
- State table: the DB table name in which the "states" are defined
- State class : the PHP class name of this State table
6. Create some rules in glpi via the menu Administration->Statecheck Rules
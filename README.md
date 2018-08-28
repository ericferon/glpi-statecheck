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
1. In the menu Dropdowns->Statecheck->Tables, define the object class on which you want to perform checks :
- Name : the DB table name in which your objects are stored
- Class : the PHP class name
- State table: the DB table name in which the "states" are defined
- State class : the PHP class name of this State table
- Frontname : the form name (that is : the last part of the URL path - without the php suffix - where you insert/update/delete objects)
2. Create some rules in glpi via the menu Administration->Statecheck Rules

For instance, you may decide that, depending on the field "Status", you want to check some field values of a dataflow form.<br/>
If you want to create a rule for checking that 
- a dataflow with a defined value "Tibco" in the field "Protocol", 
- when its status is "In specification", 
- must have some value in the fields "Name" and "Flow Group",

you must perform the following steps :
1. Create an entry in the configuration table to define once the dataflow object, via the menu Dropdowns->Statecheck->Tables : see screenshot [example1](https://raw.githubusercontent.com/ericferon/glpi-statecheck/master/statecheck-example1.png)
2. Create the rule, via the menu Administration->Statecheck Rules->+ : the target state is "In specification" ; see screenshot [example2](https://raw.githubusercontent.com/ericferon/glpi-statecheck/master/statecheck-example2.png)
3. Create the additional criteria on the field "Protocol", via the tab "Criteria" and "Add a new criteria" : see screenshot [example3](https://raw.githubusercontent.com/ericferon/glpi-statecheck/master/statecheck-example3.png)
4. Specify the checks, via the tab "Actions" and "Add a new check" : "Name" is not empty and "Flow Group" is not emty ; see screenshot [example4](https://raw.githubusercontent.com/ericferon/glpi-statecheck/master/statecheck-example4.png)

You may define several rules, that will apply to one form.<br/>
For instance, you may decide that any dataflow in the status "In specification" must have the fields "Protocol" and "Description" filled (even if the protocol is not "Tibco").<br/>
To perform the creation of this rule, you repeat the steps 2 and 4 only, because
- step 1 must only be performed once per object type (class)
- step 3 has no additional criteria (the rule applies for all dataflows in status "In specification").

So, 
- step 2 is not very different than example2 (you change only the name and the comment)
- in step 4, you create 2 actions : "Protocol" is not empty and "Description" is not empty.

The final result, applying both rules, is shown in [example5](https://raw.githubusercontent.com/ericferon/glpi-statecheck/master/statecheck-example5.png) : the mandatory fields are highlighted in red
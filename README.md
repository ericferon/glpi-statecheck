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

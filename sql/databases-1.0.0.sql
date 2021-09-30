
-- -------------------------------------------------------------------------------------
-- Insert 1 notification template for successful and failed check, per 'Database' status
-- -------------------------------------------------------------------------------------
INSERT INTO `glpi_plugin_statecheck_tables` ( `id` , `name` , `comment`, `statetable`, `stateclass`, `class`, `frontname`)  
		VALUES (1,'glpi_plugin_databases_databases','Databases','glpi_plugin_databases_databasetypes','PluginDatabasesDatabaseType','PluginDatabasesDatabase','database');
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','succeeded (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_success)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginDatabasesDatabase'
ORDER BY name;
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','failed (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_failure)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginDatabasesDatabase'
ORDER BY name;


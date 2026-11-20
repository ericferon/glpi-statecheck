
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Internal ID';
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `entities_id` INT UNSIGNED NOT NULL default '0';
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `plugin_statecheck_tables_id` INT UNSIGNED NOT NULL default '0';
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `plugin_statecheck_targetstates_id` INT UNSIGNED NOT NULL default '0';
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `successnotifications_id` INT UNSIGNED NOT NULL default '0' COMMENT 'notification in case of success';
ALTER TABLE `glpi_plugin_statecheck_rules` MODIFY COLUMN `failurenotifications_id` INT UNSIGNED NOT NULL default '0' COMMENT 'notification in case of failure';
ALTER TABLE `glpi_plugin_statecheck_tables` MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `glpi_plugin_statecheck_rulecriterias` MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `glpi_plugin_statecheck_rulecriterias` MODIFY COLUMN `plugin_statecheck_rules_id` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_statecheck_ruleactions` MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `glpi_plugin_statecheck_ruleactions` MODIFY COLUMN `plugin_statecheck_rules_id` INT UNSIGNED NOT NULL DEFAULT '0';

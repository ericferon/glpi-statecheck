
-- -----------------------------------------------------
-- Table `glpi_plugin_statecheck_rules`
-- -----------------------------------------------------
ALTER TABLE `glpi_plugin_statecheck_rules` ADD COLUMN `is_active_warn_popup` tinyint(1) NOT NULL DEFAULT '1' AFTER `is_active`;


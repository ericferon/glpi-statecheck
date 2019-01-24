
-- -----------------------------------------------------
-- Table `glpi_plugin_statecheck_rules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_statecheck_rules`;
CREATE  TABLE `glpi_plugin_statecheck_rules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal ID' ,
  `entities_id` INT(11) NOT NULL default '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_statecheck_tables_id` INT(11) NOT NULL default '0',
  `plugin_statecheck_targetstates_id` INT(11) NOT NULL default '0',
  `ranking` int(11) NOT NULL DEFAULT '0',
  `match` char(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'see define.php *_MATCHING constant',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `comment` text COLLATE utf8_unicode_ci,
  `successnotifications_id` INT(11) NOT NULL default '0' COMMENT 'notification in case of success',
  `failurenotifications_id` INT(11) NOT NULL default '0' COMMENT 'notification in case of failure',
  `date_mod` datetime DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_statecheck_tables_id` (`plugin_statecheck_tables_id`),
  KEY `plugin_statecheck_targetstates_id` (`plugin_statecheck_targetstates_id`),
  KEY `is_active` (`is_active`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- -----------------------------------------------------
-- Table `glpi_plugin_statecheck_tables`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_statecheck_tables`;
CREATE  TABLE `glpi_plugin_statecheck_tables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `comment` VARCHAR(45) NOT NULL ,
  `statetable` VARCHAR(45) NOT NULL ,
  `stateclass` VARCHAR(45) NOT NULL ,
  `class` VARCHAR(45) NOT NULL ,
  `frontname` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `plugin_statecheck_tables_name` (`name` ASC) )
ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

-- -------------------------------------------------------------------------------------------
-- Insert 1 notification template for successful and failed check, per 'Apps Structure' status
-- -------------------------------------------------------------------------------------------
INSERT INTO `glpi_plugin_statecheck_tables` ( `id` , `name` , `comment`, `statetable`, `stateclass`, `class`, `frontname` )  
		VALUES (2,'glpi_plugin_archisw_swcomponents','Apps Structure','glpi_plugin_archisw_swcomponentstates','PluginArchiswSwcomponentState','PluginArchiswSwcomponent','swcomponent');
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','succeeded (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_success)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginArchiswSwcomponent'
ORDER BY name;
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','failed (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_failure)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginArchiswSwcomponent'
ORDER BY name;

-- -------------------------------------------------------------------------------------
-- Insert 1 notification template for successful and failed check, per 'Dataflow' status
-- -------------------------------------------------------------------------------------
INSERT INTO `glpi_plugin_statecheck_tables` ( `id` , `name` , `comment`, `statetable`, `stateclass`, `class`, `frontname` )  
		VALUES (3,'glpi_plugin_dataflows_dataflows','Dataflows','glpi_plugin_dataflows_states','PluginDataflowsState','PluginDataflowsDataflow','dataflow');
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','succeeded (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_success)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginDataflowsDataflow'
ORDER BY name;
INSERT INTO `glpi_notificationtemplates` (`id` , `name` , `itemtype`, `date_mod` , `comment` , `css`)
SELECT NULL,CONCAT('Statecheck ',glpi_plugin_statecheck_tables.comment,' ',glpi_plugin_dataflows_states.name,' ','failed (',glpi_plugin_statecheck_tables.class,'_',cast(glpi_plugin_dataflows_states.id as char),'_failure)') as name,'PluginStatecheckRule',NOW(),'',NULL 
FROM glpi_plugin_statecheck_tables, glpi_plugin_dataflows_states
WHERE glpi_plugin_statecheck_tables.class = 'PluginDataflowsDataflow'
ORDER BY name;
INSERT INTO `glpi_notificationtemplatetranslations`
SELECT NULL, id, '','From ##statecheck.loggeduser## : ##statecheck.action## (##statecheck.entity##)','##statecheck.classname## : ##statecheck.id##\n##lang.statecheck.dataflow.plugin_dataflows_states_id## : ##statecheck.dataflow.plugin_dataflows_states_id##\n##lang.statecheck.dataflow.name## : ##statecheck.dataflow.name##\n##lang.statecheck.dataflow.shortdescription## : ##statecheck.dataflow.shortdescription##\n##lang.statecheck.dataflow.plugin_dataflows_fromswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_fromswcomponents_id##\n##lang.statecheck.dataflow.plugin_dataflows_toswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_toswcomponents_id##\n##lang.statecheck.dataflow.plugin_dataflows_transferprotocols_id## : ##statecheck.dataflow.plugin_dataflows_transferprotocols_id##\n##lang.statecheck.dataflow.users_id## : ##statecheck.dataflow.users_id##\n##lang.statecheck.dataflow.groups_id## : ##statecheck.dataflow.groups_id##','##statecheck.classname## : ##statecheck.id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_states_id## : ##statecheck.dataflow.plugin_dataflows_states_id##&lt;br/&gt;##lang.statecheck.dataflow.name## : ##statecheck.dataflow.name##&lt;br/&gt;##lang.statecheck.dataflow.shortdescription## : ##statecheck.dataflow.shortdescription##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_fromswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_fromswcomponents_id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_toswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_toswcomponents_id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_transferprotocols_id## : ##statecheck.dataflow.plugin_dataflows_transferprotocols_id##&lt;br/&gt;##lang.statecheck.dataflow.users_id## : ##statecheck.dataflow.users_id##&lt;br/&gt;##lang.statecheck.dataflow.groups_id## : ##statecheck.dataflow.groups_id##'
FROM `glpi_notificationtemplates`
WHERE `itemtype` = 'PluginStatecheckRule'
AND `name` like '%(PluginDataflowsDataflow_%_success)'
ORDER BY id;
INSERT INTO `glpi_notificationtemplatetranslations`
SELECT NULL, id, '','From ##statecheck.loggeduser## : ##statecheck.action## (##statecheck.entity##)','##statecheck.classname## : ##statecheck.id##\n##lang.statecheck.dataflow.plugin_dataflows_states_id## : ##statecheck.dataflow.plugin_dataflows_states_id##\n##lang.statecheck.dataflow.name## : ##statecheck.dataflow.name##\n##lang.statecheck.dataflow.shortdescription## : ##statecheck.dataflow.shortdescription##\n##lang.statecheck.dataflow.plugin_dataflows_fromswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_fromswcomponents_id##\n##lang.statecheck.dataflow.plugin_dataflows_toswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_toswcomponents_id##\n##lang.statecheck.dataflow.plugin_dataflows_transferprotocols_id## : ##statecheck.dataflow.plugin_dataflows_transferprotocols_id##\n##lang.statecheck.dataflow.users_id## : ##statecheck.dataflow.users_id##\n##lang.statecheck.dataflow.groups_id## : ##statecheck.dataflow.groups_id##\n##lang.statecheck.errormessage## : ##statecheck.errormessage##','##statecheck.classname## : ##statecheck.id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_states_id## : ##statecheck.dataflow.plugin_dataflows_states_id##&lt;br/&gt;##lang.statecheck.dataflow.name## : ##statecheck.dataflow.name##&lt;br/&gt;##lang.statecheck.dataflow.shortdescription## : ##statecheck.dataflow.shortdescription##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_fromswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_fromswcomponents_id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_toswcomponents_id## : ##statecheck.dataflow.plugin_dataflows_toswcomponents_id##&lt;br/&gt;##lang.statecheck.dataflow.plugin_dataflows_transferprotocols_id## : ##statecheck.dataflow.plugin_dataflows_transferprotocols_id##&lt;br/&gt;##lang.statecheck.dataflow.users_id## : ##statecheck.dataflow.users_id##&lt;br/&gt;##lang.statecheck.dataflow.groups_id## : ##statecheck.dataflow.groups_id##&lt;br/&gt;##lang.statecheck.errormessage## : ##statecheck.errormessage##'
FROM `glpi_notificationtemplates`
WHERE `itemtype` = 'PluginStatecheckRule'
AND `name` like '%(PluginDataflowsDataflow_%_failure)'
ORDER BY id;
-- ----------------------------------------------------------------
-- Table `glpi_plugin_statecheck_rulecriterias`
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_statecheck_rulecriterias`;
CREATE TABLE `glpi_plugin_statecheck_rulecriterias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_statecheck_rules_id` int(11) NOT NULL DEFAULT '0',
  `criteria` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `condition` int(11) NOT NULL DEFAULT '0' COMMENT 'see define.php PATTERN_* and REGEX_* constant',
  `pattern` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_statecheck_rules_id` (`plugin_statecheck_rules_id`),
  KEY `condition` (`condition`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- ----------------------------------------------------------------
-- Table `glpi_plugin_statecheck_ruleactions`
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_statecheck_ruleactions`;
CREATE TABLE `glpi_plugin_statecheck_ruleactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_statecheck_rules_id` int(11) NOT NULL DEFAULT '0',
  `action_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'VALUE IN (assign, regex_result, append_regex_result, affectbyip, affectbyfqdn, affectbymac)',
  `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_statecheck_rules_id` (`plugin_statecheck_rules_id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- -----------------------------------------------------
-- Table `glpi_plugin_statecheck_profiles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_statecheck_profiles`;
CREATE TABLE `glpi_plugin_statecheck_profiles` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`statecheck` char(1) collate utf8_unicode_ci default NULL,
	`open_ticket` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginStatecheckRule','2','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginStatecheckRule','6','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginStatecheckRule','7','4','0');


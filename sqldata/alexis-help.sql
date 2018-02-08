/*
Lilac - HELP
These following lines are required to use the help
*/

ALTER TABLE `nagios_command` ADD COLUMN `help` TEXT NULL AFTER `description`;
ALTER TABLE `nagios_command` ADD COLUMN `typehelp` INT(1) NOT NULL DEFAULT '0' AFTER `help`;
INSERT INTO `label` (`section`, `name`, `label`) VALUES ('nagios_commands_desc', 'command_cmd_help', 'You have to enter the name of your script followed by the option to get help. Example: check_ping --help');
INSERT INTO `label` (`section`, `name`, `label`) VALUES ('nagios_commands_desc', 'command_select_help', 'Command Line: Run your check script with help option directly on the add_service page.\r\nText: Show a basic text help');
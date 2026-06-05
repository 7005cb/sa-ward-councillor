-- =====================================================
-- TIMELINE CLEANUP TOOL - UNINSTALL
-- =====================================================

DELETE FROM `sys_std_pages_widgets` WHERE `widget_id` IN (SELECT `id` FROM `sys_std_widgets` WHERE `module` = 'sa_timeline_cleanup');
DELETE FROM `sys_std_widgets` WHERE `module` = 'sa_timeline_cleanup';
DELETE FROM `sys_std_pages` WHERE `name` = 'sa_timeline_cleanup';

DELETE `sys_localization_strings` FROM `sys_localization_strings`
JOIN `sys_localization_keys` k ON `sys_localization_strings`.`IDKey` = k.`ID`
WHERE k.`Key` LIKE '_sa_timeline_cleanup%';

DELETE FROM `sys_options` WHERE `name` LIKE 'sa_timeline_cleanup_%';

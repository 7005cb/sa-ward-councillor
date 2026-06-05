DELETE FROM `sys_menu_items`   WHERE `module` = 'sa_rentals';
DELETE FROM `sys_pages_blocks`  WHERE `module` = 'sa_rentals';
DELETE FROM `sys_objects_page`  WHERE `module` = 'sa_rentals';
DELETE FROM `sys_objects_storage` WHERE `object` = 'sa_rentals_files';
DELETE FROM `sys_std_widgets`   WHERE `module` = 'sa_rentals';
DELETE FROM `sys_std_pages`     WHERE `name`   = 'sa_rentals';

DELETE FROM `sys_acl_matrix` WHERE `IDAction` IN
  (SELECT `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_rentals');
DELETE FROM `sys_acl_actions` WHERE `Module` = 'sa_rentals';

DELETE FROM `sys_objects_content_info` WHERE `name` = 'sa_rentals';
DELETE FROM `sys_objects_privacy`      WHERE `object` = 'sa_rentals_view';
DELETE FROM `bx_timeline_handlers`     WHERE `alert_unit` = 'sa_rentals';
DELETE FROM `bx_notifications_handlers` WHERE `alert_unit` = 'sa_rentals';
DELETE FROM `sys_alerts`               WHERE `unit` = 'sa_rentals';

DELETE FROM `sys_options` WHERE `name` IN (
  'sa_rentals_require_tenant_reg',
  'sa_rentals_agent_verification',
  'sa_rentals_blacklisting',
  'sa_rentals_show_banners',
  'sa_rentals_moderation'
);

DROP TABLE IF EXISTS `sa_rentals_files`;
DROP TABLE IF EXISTS `sa_rentals_listings`;

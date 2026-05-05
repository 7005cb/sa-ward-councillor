-- =====================================================
-- WARD COUNCILOR PORTAL MODULE UNINSTALLATION
-- =====================================================

-- Remove menu items
DELETE FROM `sys_menu_items` WHERE `module` = 'sa_ward_councilor';

-- Remove page blocks
DELETE FROM `sys_pages_blocks` WHERE `module` = 'sa_ward_councilor';

-- Remove pages
DELETE FROM `sys_objects_page` WHERE `module` = 'sa_ward_councilor';

-- Remove studio widget
SET @iPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'sa_ward_councilor' LIMIT 1);
DELETE FROM `sys_std_pages_widgets` WHERE `widget_id` IN (SELECT `id` FROM `sys_std_widgets` WHERE `page_id` = @iPageId);
DELETE FROM `sys_std_widgets` WHERE `page_id` = @iPageId;
DELETE FROM `sys_std_pages` WHERE `name` = 'sa_ward_councilor';

-- Drop tables (optional - uncomment to remove data)
-- DROP TABLE IF EXISTS `sa_ward_councilor_requests`;
-- DROP TABLE IF EXISTS `sa_ward_councilor_meetings`;
-- DROP TABLE IF EXISTS `sa_ward_councilor_announcements`;
-- DROP TABLE IF EXISTS `sa_ward_councilor_notes`;
-- DROP TABLE IF EXISTS `sa_ward_councilor_info`;
DELETE FROM `sys_menu_items` WHERE `set_name` = 'sa_ward_councilor_menu';
DELETE FROM `sys_objects_menu` WHERE `object` = 'sa_ward_councilor_menu';
DELETE FROM `sys_menu_sets` WHERE `set_name` = 'sa_ward_councilor_menu';
UPDATE `sys_menu_items` SET `submenu_object` = '' WHERE `module` = 'sa_ward_councilor' AND `set_name` = 'sys_site';

-- =====================================================
-- LANGUAGE STRINGS CLEANUP
-- =====================================================
DELETE s FROM `sys_localization_strings` s
JOIN `sys_localization_keys` k ON s.`IDKey` = k.`ID`
WHERE k.`Key` LIKE '_sa_ward_councilor%';

DELETE FROM `sys_localization_keys` WHERE `Key` LIKE '_sa_ward_councilor%';

DELETE FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

-- ─── ACL: Remove module actions ─────────────────────────────────────────────
DELETE FROM `sys_acl_matrix` WHERE `IDAction` IN
  (SELECT `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_ward_councilor');
DELETE FROM `sys_acl_actions` WHERE `Module` = 'sa_ward_councilor';
-- ────────────────────────────────────────────────────────────────────────────

DELETE FROM `sys_objects_privacy` WHERE `module` = 'sa_ward_councilor';
DELETE FROM `sys_objects_content_info` WHERE `name` = 'sa_ward_councilor';
DELETE FROM `bx_timeline_handlers` WHERE `alert_unit` = 'sa_ward_councilor';
DELETE FROM `sys_alerts` WHERE `unit` = 'sa_ward_councilor';
DELETE FROM `bx_notifications_handlers` WHERE `alert_unit` = 'sa_ward_councilor';

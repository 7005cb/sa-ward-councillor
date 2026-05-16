-- =====================================================
-- COMMUNITY SUPPORT SCHEME MODULE UNINSTALLATION
-- =====================================================

-- STUDIO WIDGET
DELETE FROM `tp`, `tw`, `tpw`
USING `sys_std_pages` AS `tp`, `sys_std_widgets` AS `tw`, `sys_std_pages_widgets` AS `tpw`
WHERE `tp`.`id` = `tw`.`page_id` AND `tw`.`id` = `tpw`.`widget_id` AND `tp`.`name` = 'sa_support_scheme';

-- PAGES
DELETE FROM `sys_pages_blocks` WHERE `module` = 'sa_support_scheme';
DELETE FROM `sys_objects_page` WHERE `module` = 'sa_support_scheme';

-- MENU
DELETE FROM `sys_menu_items` WHERE `module` = 'sa_support_scheme';

-- DROP TABLES
DROP TABLE IF EXISTS `sa_support_scheme_donations`;
DROP TABLE IF EXISTS `sa_support_scheme_campaigns`;
DROP TABLE IF EXISTS `sa_support_scheme_beneficiaries`;

-- =====================================================
-- WARD COUNCILOR PORTAL MODULE ENABLE
-- =====================================================

-- Show menu item
UPDATE `sys_menu_items` SET `active` = 1 WHERE `module` = 'sa_ward_councilor';
UPDATE `sys_objects_menu` SET `active` = 1 WHERE `object` = 'sa_ward_councilor_menu';

-- Show page blocks
UPDATE `sys_pages_blocks` SET `active` = 1 WHERE `module` = 'sa_ward_councilor';

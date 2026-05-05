-- =====================================================
-- WARD COUNCILOR PORTAL MODULE DISABLE
-- =====================================================

-- Hide menu item
UPDATE `sys_menu_items` SET `active` = 0 WHERE `module` = 'sa_ward_councilor';
UPDATE `sys_objects_menu` SET `active` = 0 WHERE `object` = 'sa_ward_councilor_menu';

-- Hide page blocks
UPDATE `sys_pages_blocks` SET `active` = 0 WHERE `module` = 'sa_ward_councilor';

-- =====================================================
-- WARD COUNCILOR PORTAL MODULE UPGRADE
-- =====================================================

INSERT INTO `sys_objects_page`
(`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`)
SELECT
0, UNIX_TIMESTAMP(), 'sa_ward_councilor_meeting', 'view-ward-meeting', '_sa_ward_councilor_page_meeting_sys', '_sa_ward_councilor_page_meeting', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-ward-meeting', '', '', '', '', '', 0, 1, '', '', '', 0, '', ''
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_objects_page` WHERE `object` = 'sa_ward_councilor_meeting' OR `uri` = 'view-ward-meeting'
);

INSERT INTO `sys_pages_blocks`
(`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`)
SELECT
'sa_ward_councilor_meeting', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_meeting_details', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:25:"get_meeting_details_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_pages_blocks` WHERE `object` = 'sa_ward_councilor_meeting' AND `module` = 'sa_ward_councilor'
);

INSERT INTO `sys_menu_sets` (`set_name`, `module`, `title`, `deletable`)
SELECT 'sa_ward_councilor_menu', 'sa_ward_councilor', 'Ward Councilor Navigation', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_menu_sets` WHERE `set_name` = 'sa_ward_councilor_menu'
);

INSERT INTO `sys_objects_menu`
(`object`, `title`, `set_name`, `module`, `template_id`, `config_api`, `persistent`, `deletable`, `active`, `override_class_name`, `override_class_file`)
SELECT 'sa_ward_councilor_menu', 'Ward Councilor Navigation', 'sa_ward_councilor_menu', 'sa_ward_councilor', 8, '', 0, 1, 1, '', ''
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_objects_menu` WHERE `object` = 'sa_ward_councilor_menu'
);

UPDATE `sys_menu_items`
SET `submenu_object` = 'sa_ward_councilor_menu'
WHERE `module` = 'sa_ward_councilor' AND `set_name` = 'sys_site' AND `name` = 'ward-councilor';

UPDATE `sys_objects_page`
SET `submenu` = 'sa_ward_councilor_menu'
WHERE `module` = 'sa_ward_councilor' AND (`submenu` = '' OR `submenu` IS NULL);

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
SELECT `ID`, '_sa_ward_councilor_page_meeting_sys'
FROM `sys_localization_categories`
WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
SELECT `ID`, '_sa_ward_councilor_page_meeting'
FROM `sys_localization_categories`
WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
SELECT `ID`, '_sa_ward_councilor_block_meeting_details'
FROM `sys_localization_categories`
WHERE `Name` = 'sa_ward_councilor';

SET @iWardCouncilorEnLang = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'en' LIMIT 1);

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
SELECT `ID`, @iWardCouncilorEnLang, 'Meeting Details'
FROM `sys_localization_keys`
WHERE `Key` = '_sa_ward_councilor_page_meeting_sys';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
SELECT `ID`, @iWardCouncilorEnLang, 'Ward Meeting'
FROM `sys_localization_keys`
WHERE `Key` = '_sa_ward_councilor_page_meeting';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
SELECT `ID`, @iWardCouncilorEnLang, 'Meeting Details'
FROM `sys_localization_keys`
WHERE `Key` = '_sa_ward_councilor_block_meeting_details';

-- SETTINGS

SET @iTypeOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_options_types` WHERE `group` = 'modules');
INSERT INTO `sys_options_types` (`group`, `name`, `caption`, `icon`, `order`) VALUES 
('modules', 'sa_support_scheme', '_sa_support_scheme_adm_stg_cpt_type', 'sa_support_scheme@modules/sa/support_scheme/|std-mi.png', @iTypeOrder);
SET @iTypeId = LAST_INSERT_ID();

INSERT INTO `sys_options_categories` (`type_id`, `name`, `caption`, `order` )  
VALUES (@iTypeId, 'sa_support_scheme_general', '_sa_support_scheme_adm_stg_cpt_category_general', 1);
SET @iCategoryId = LAST_INSERT_ID();

INSERT INTO `sys_options`(`category_id`, `name`, `caption`, `value`, `type`, `extra`, `check`, `check_error`, `order`) VALUES
(@iCategoryId, 'sa_support_scheme_enabled', '_sa_support_scheme_option_enabled', 'on', 'checkbox', '', '', '', 1),
(@iCategoryId, 'sa_support_scheme_min_donation', '_sa_support_scheme_option_min_donation', '10', 'digit', '', '', '', 2),
(@iCategoryId, 'sa_support_scheme_max_donation', '_sa_support_scheme_option_max_donation', '50000', 'digit', '', '', '', 3),
(@iCategoryId, 'sa_support_scheme_currency', '_sa_support_scheme_option_currency', 'ZAR', 'select', 'ZAR,USD,EUR,GBP', '', '', 4);

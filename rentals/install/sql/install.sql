-- SA RENTALS install.sql — UNA CMS 14.0.x
-- sys_menu_items: 29 columns — live verified 2026-04-07
-- All serializations mathematically verified

SET @sStorageEngine = (SELECT `value` FROM `sys_options` WHERE `name` = 'sys_storage_default');

INSERT INTO `sys_std_pages`(`index`,`name`,`header`,`caption`,`icon`) VALUES
(3,'sa_rentals','_sa_rentals','_sa_rentals','sa_rentals@modules/sa/rentals/|std-pi.png');

SET @iPageId = LAST_INSERT_ID();
SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT IFNULL(MAX(`order`),0)+1 FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);

INSERT INTO `sys_std_widgets`(`page_id`,`module`,`url`,`click`,`icon`,`caption`,`cnt_notices`,`cnt_actions`) VALUES
(@iPageId,'sa_rentals','{url_studio}module.php?name=sa_rentals','','sa_rentals@modules/sa/rentals/|std-wi.png','_sa_rentals','','a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');

INSERT INTO `sys_std_pages_widgets`(`page_id`,`widget_id`,`order`) VALUES
(@iParentPageId,LAST_INSERT_ID(),@iParentPageOrder);

CREATE TABLE IF NOT EXISTS `sa_rentals_listings` (
  `id`                   int(11)       NOT NULL AUTO_INCREMENT,
  `title`                varchar(255)  NOT NULL,
  `description`          text,
  `property_type`        enum('room','house','flat','backyard','townhouse') NOT NULL DEFAULT 'room',
  `province`             varchar(100)  NOT NULL DEFAULT '',
  `city`                 varchar(100)  NOT NULL DEFAULT '',
  `address`              varchar(255)  NOT NULL DEFAULT '',
  `rent_zar`             decimal(10,2) NOT NULL DEFAULT 0.00,
  `deposit_zar`          decimal(10,2) NOT NULL DEFAULT 0.00,
  `contact`              varchar(255)  NOT NULL DEFAULT '',
  `contact_phone`        varchar(20)   NOT NULL DEFAULT '',
  `contact_whatsapp`     varchar(20)   NOT NULL DEFAULT '',
  `contact_email`        varchar(255)  NOT NULL DEFAULT '',
  `available_from`       date                   DEFAULT NULL,
  `lease_term`           enum('month-to-month','6 months','12 months','24 months') NOT NULL DEFAULT 'month-to-month',
  `bedrooms`             tinyint(4)    NOT NULL DEFAULT 0,
  `bathrooms`            tinyint(4)    NOT NULL DEFAULT 0,
  `parking`              tinyint(1)    NOT NULL DEFAULT 0,
  `pets_allowed`         tinyint(1)    NOT NULL DEFAULT 0,
  `furnished`            enum('unfurnished','semi-furnished','fully-furnished') NOT NULL DEFAULT 'unfurnished',
  `utilities_included`   tinyint(1)    NOT NULL DEFAULT 0,
  `prepaid_electricity`  tinyint(1)    NOT NULL DEFAULT 0,
  `wifi_available`       tinyint(1)    NOT NULL DEFAULT 0,
  `security_features`    varchar(255)  NOT NULL DEFAULT '',
  `visibility`           enum('public','space','group') NOT NULL DEFAULT 'public',
  `space_id`             int(11)       NOT NULL DEFAULT 0,
  `group_id`             int(11)       NOT NULL DEFAULT 0,
  `media_storage_ids`    text                   DEFAULT NULL,
  `featured`             tinyint(1)    NOT NULL DEFAULT 0,
  `views_count`          int(11)       NOT NULL DEFAULT 0,
  `latitude`             decimal(10,8)          DEFAULT NULL,
  `longitude`            decimal(11,8)          DEFAULT NULL,
  `author_id`            int(11)       NOT NULL DEFAULT 0,
  `status`               enum('available','hold','booked','taken') NOT NULL DEFAULT 'available',
  `created`              datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`              datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `province` (`province`),
  KEY `property_type` (`property_type`),
  KEY `visibility` (`visibility`),
  KEY `space_id` (`space_id`),
  KEY `group_id` (`group_id`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sys_objects_page`(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`) VALUES
(0,UNIX_TIMESTAMP(),'sa_rentals_listings','rentals-listings','_sa_rentals_page_listings_sys','_sa_rentals_page_listings','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=rentals-listings','','','','','',0,1,'','','',0,'','');

INSERT INTO `sys_objects_page`(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`) VALUES
(0,UNIX_TIMESTAMP(),'sa_rentals_view','view-rentals-listing','_sa_rentals_page_view_sys','_sa_rentals_page_view','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=view-rentals-listing','','','','','',0,1,'','','',0,'','');

INSERT INTO `sys_objects_page`(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`) VALUES
(0,UNIX_TIMESTAMP(),'sa_rentals_create','create-rentals-listing','_sa_rentals_page_create_sys','_sa_rentals_page_create','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=create-rentals-listing','','','','','',0,1,'','','',0,'','');

INSERT INTO `sys_objects_page`(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`) VALUES
(0,UNIX_TIMESTAMP(),'sa_rentals_edit','rentals-edit','_sa_rentals_page_edit_sys','_sa_rentals_page_edit','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=rentals-edit','','','','','',0,1,'','','',0,'','');

INSERT INTO `sys_objects_page`(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`) VALUES
(0,UNIX_TIMESTAMP(),'sa_rentals_my','my-rentals-listings','_sa_rentals_page_my_sys','_sa_rentals_page_my','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=my-rentals-listings','','','','','',0,1,'','','',0,'','');

-- Serialized: sa_rentals=10, get_listings_block=18
INSERT INTO `sys_pages_blocks`(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`) VALUES
('sa_rentals_listings',1,'sa_rentals','','_sa_rentals_block_listings',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:18:"get_listings_block";}','','',0,'',0,'',0,1,1,0,1);

-- Serialized: sa_rentals=10, get_listing_detail_block=24
INSERT INTO `sys_pages_blocks`(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`) VALUES
('sa_rentals_view',1,'sa_rentals','','_sa_rentals_block_view',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:24:"get_listing_detail_block";}','','',0,'',0,'',0,1,1,0,1);

-- Serialized: sa_rentals=10, get_create_listing_block=24
INSERT INTO `sys_pages_blocks`(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`) VALUES
('sa_rentals_create',1,'sa_rentals','','_sa_rentals_block_create',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:24:"get_create_listing_block";}','','',0,'',0,'',0,1,1,0,1);

-- Serialized: sa_rentals=10, get_edit_listing_block=22
INSERT INTO `sys_pages_blocks`(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`) VALUES
('sa_rentals_edit',1,'sa_rentals','','_sa_rentals_block_edit',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:22:"get_edit_listing_block";}','','',0,'',0,'',0,1,1,0,1);

-- Serialized: sa_rentals=10, get_my_listings_block=21
INSERT INTO `sys_pages_blocks`(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`) VALUES
('sa_rentals_my',1,'sa_rentals','','_sa_rentals_block_my',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:21:"get_my_listings_block";}','','',0,'',0,'',0,1,1,0,1);

SET @iMenuOrder = (SELECT IFNULL(MAX(`order`),0)+1 FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `parent_id` = 0);

INSERT INTO `sys_menu_items`(`parent_id`,`set_name`,`module`,`name`,`title_system`,`title`,`link`,`onclick`,`target`,`icon`,`addon`,`addon_cache`,`markers`,`submenu_object`,`submenu_popup`,`visible_for_levels`,`visibility_custom`,`hidden_on`,`hidden_on_cxt`,`hidden_on_pt`,`hidden_on_col`,`config_api`,`primary`,`collapsed`,`active`,`active_api`,`copyable`,`editable`,`order`) VALUES
(0,'sys_site','sa_rentals','rentals','_sa_rentals_menu_item_sys','_sa_rentals_menu_item','page.php?i=rentals-listings','','','home col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,@iMenuOrder);

-- Storage files table for media uploads
CREATE TABLE IF NOT EXISTS `sa_rentals_files` (
  `id`          int(11)           NOT NULL AUTO_INCREMENT,
  `profile_id`  int(10) unsigned  NOT NULL,
  `remote_id`   varchar(128)      NOT NULL,
  `path`        varchar(255)      NOT NULL,
  `file_name`   varchar(255)      NOT NULL,
  `mime_type`   varchar(128)      NOT NULL,
  `ext`         varchar(32)       NOT NULL,
  `size`        bigint(20)        NOT NULL,
  `added`       int(11)           NOT NULL,
  `modified`    int(11)           NOT NULL,
  `private`     int(11)           NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remote_id` (`remote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sys_objects_storage`
(`object`, `engine`, `params`, `token_life`, `cache_control`, `levels`, `table_files`, `ext_mode`, `ext_allow`, `ext_deny`, `quota_size`, `current_size`, `quota_number`, `current_number`, `max_file_size`, `ts`) VALUES
('sa_rentals_files', @sStorageEngine, '', 360, 2592000, 3, 'sa_rentals_files', 'allow-deny', '{imagevideo}', '', 0, 0, 0, 0, 0, 0);

-- ─── ACL ────────────────────────────────────────────────────────────────────
INSERT INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('sa_rentals', 'view entry',       NULL, '_acl_txt_sa_rentals_view_entry',       '', 0, ''),
  ('sa_rentals', 'create entry',     NULL, '_acl_txt_sa_rentals_create_entry',     '', 0, ''),
  ('sa_rentals', 'edit own entry',   NULL, '_acl_txt_sa_rentals_edit_own_entry',   '', 0, ''),
  ('sa_rentals', 'edit any entry',   NULL, '_acl_txt_sa_rentals_edit_any_entry',   '', 0, ''),
  ('sa_rentals', 'delete own entry', NULL, '_acl_txt_sa_rentals_delete_own_entry', '', 0, ''),
  ('sa_rentals', 'delete any entry', NULL, '_acl_txt_sa_rentals_delete_any_entry', '', 0, '');

INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 3, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_rentals'
AND `Name` IN ('view entry', 'create entry', 'edit own entry', 'delete own entry');

INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 5, `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_rentals';

INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 8, `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_rentals';
-- ────────────────────────────────────────────────────────────────────────────

-- ─── Timeline ───────────────────────────────────────────────────────────────
INSERT INTO `sys_objects_content_info`
  (`name`, `title`, `alert_unit`, `alert_action_add`, `alert_action_update`, `alert_action_delete`, `class_name`, `class_file`)
VALUES
  ('sa_rentals', '_sa_rentals_content_info', 'sa_rentals', 'added', 'edited', 'deleted', '', '');
-- ────────────────────────────────────────────────────────────────────────────

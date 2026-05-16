-- SA Rentals compatibility upgrade
-- Align older installs with the current runtime schema and page objects.

SET @sStorageEngine = (SELECT `value` FROM `sys_options` WHERE `name` = 'sys_storage_default');

ALTER TABLE `sa_rentals_listings`
  ADD COLUMN IF NOT EXISTS `deposit_zar`          decimal(10,2)     NOT NULL DEFAULT 0.00 AFTER `rent_zar`,
  ADD COLUMN IF NOT EXISTS `contact_phone`        varchar(20)       NOT NULL DEFAULT '' AFTER `contact`,
  ADD COLUMN IF NOT EXISTS `contact_whatsapp`     varchar(20)       NOT NULL DEFAULT '' AFTER `contact_phone`,
  ADD COLUMN IF NOT EXISTS `contact_email`        varchar(255)      NOT NULL DEFAULT '' AFTER `contact_whatsapp`,
  ADD COLUMN IF NOT EXISTS `available_from`       date                       DEFAULT NULL AFTER `contact_email`,
  ADD COLUMN IF NOT EXISTS `lease_term`           enum('month-to-month','6 months','12 months','24 months') NOT NULL DEFAULT 'month-to-month' AFTER `available_from`,
  ADD COLUMN IF NOT EXISTS `bedrooms`             tinyint(4)        NOT NULL DEFAULT 0 AFTER `deposit_zar`,
  ADD COLUMN IF NOT EXISTS `bathrooms`            tinyint(4)        NOT NULL DEFAULT 0 AFTER `bedrooms`,
  ADD COLUMN IF NOT EXISTS `parking`              tinyint(1)        NOT NULL DEFAULT 0 AFTER `bathrooms`,
  ADD COLUMN IF NOT EXISTS `pets_allowed`         tinyint(1)        NOT NULL DEFAULT 0 AFTER `parking`,
  ADD COLUMN IF NOT EXISTS `furnished`            enum('unfurnished','semi-furnished','fully-furnished') NOT NULL DEFAULT 'unfurnished' AFTER `pets_allowed`,
  ADD COLUMN IF NOT EXISTS `utilities_included`   tinyint(1)        NOT NULL DEFAULT 0 AFTER `furnished`,
  ADD COLUMN IF NOT EXISTS `prepaid_electricity`  tinyint(1)        NOT NULL DEFAULT 0 AFTER `utilities_included`,
  ADD COLUMN IF NOT EXISTS `wifi_available`       tinyint(1)        NOT NULL DEFAULT 0 AFTER `prepaid_electricity`,
  ADD COLUMN IF NOT EXISTS `security_features`    varchar(255)      NOT NULL DEFAULT '' AFTER `wifi_available`,
  ADD COLUMN IF NOT EXISTS `visibility`           enum('public','space','group') NOT NULL DEFAULT 'public' AFTER `security_features`,
  ADD COLUMN IF NOT EXISTS `space_id`             int(11)           NOT NULL DEFAULT 0 AFTER `visibility`,
  ADD COLUMN IF NOT EXISTS `group_id`             int(11)           NOT NULL DEFAULT 0 AFTER `space_id`,
  ADD COLUMN IF NOT EXISTS `media_storage_ids`    text                       DEFAULT NULL AFTER `group_id`,
  ADD COLUMN IF NOT EXISTS `views_count`          int(11)           NOT NULL DEFAULT 0 AFTER `media_storage_ids`,
  ADD COLUMN IF NOT EXISTS `featured`             tinyint(1)        NOT NULL DEFAULT 0 AFTER `views_count`,
  ADD COLUMN IF NOT EXISTS `latitude`             decimal(10,8)              DEFAULT NULL AFTER `featured`,
  ADD COLUMN IF NOT EXISTS `longitude`            decimal(11,8)              DEFAULT NULL AFTER `latitude`;

ALTER TABLE `sa_rentals_listings`
  MODIFY COLUMN `status` enum('active','inactive','draft','available','hold','booked','taken') NOT NULL DEFAULT 'available';

UPDATE `sa_rentals_listings` SET `status` = 'available' WHERE `status` = 'active' OR `status` = '';
UPDATE `sa_rentals_listings` SET `status` = 'hold' WHERE `status` = 'draft';
UPDATE `sa_rentals_listings` SET `status` = 'taken' WHERE `status` = 'inactive';
UPDATE `sa_rentals_listings` SET `visibility` = 'public' WHERE `visibility` IS NULL OR `visibility` = '';

ALTER TABLE `sa_rentals_listings`
  MODIFY COLUMN `status` enum('available','hold','booked','taken') NOT NULL DEFAULT 'available';

INSERT INTO `sys_objects_page`
(`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`)
SELECT
0,UNIX_TIMESTAMP(),'sa_rentals_edit','rentals-edit','_sa_rentals_page_edit_sys','_sa_rentals_page_edit','sa_rentals',0,0,'',1,5,0,'',2147483647,1,'page.php?i=rentals-edit','','','','','',0,1,'','','',0,'',''
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_objects_page` WHERE `object` = 'sa_rentals_edit' OR `uri` = 'rentals-edit'
);

INSERT INTO `sys_pages_blocks`
(`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`)
SELECT
'sa_rentals_edit',1,'sa_rentals','','_sa_rentals_block_edit',11,'','',0,0,2147483647,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:22:"get_edit_listing_block";}','','',0,'',0,'',0,1,1,0,1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_pages_blocks` WHERE `object` = 'sa_rentals_edit' AND `module` = 'sa_rentals'
);

INSERT INTO `sys_objects_storage`
(`object`, `engine`, `params`, `token_life`, `cache_control`, `levels`, `table_files`, `ext_mode`, `ext_allow`, `ext_deny`, `quota_size`, `current_size`, `quota_number`, `current_number`, `max_file_size`, `ts`)
SELECT
'sa_rentals_files', @sStorageEngine, '', 360, 2592000, 3, 'sa_rentals_files', 'allow-deny', '{imagevideo}', '', 0, 0, 0, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_objects_storage` WHERE `object` = 'sa_rentals_files'
);

-- Storage files table
CREATE TABLE IF NOT EXISTS `sa_rentals_files` (
  `id`            int(11)           NOT NULL AUTO_INCREMENT,
  `profile_id`    int(10) unsigned  NOT NULL,
  `remote_id`     varchar(128)      NOT NULL,
  `path`          varchar(255)      NOT NULL,
  `file_name`     varchar(255)      NOT NULL,
  `mime_type`     varchar(128)      NOT NULL,
  `ext`           varchar(32)       NOT NULL,
  `size`          bigint(20)        NOT NULL,
  `added`         int(11)           NOT NULL,
  `modified`      int(11)           NOT NULL,
  `private`       int(11)           NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remote_id` (`remote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

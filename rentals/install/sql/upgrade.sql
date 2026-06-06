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

-- ─── Upgrade: add pending status to enum ─────────────────────────────────────
ALTER TABLE `sa_rentals_listings`
  MODIFY COLUMN `status` enum('available','hold','booked','taken','pending') NOT NULL DEFAULT 'available';

-- ─── Upgrade: sys_objects_privacy ────────────────────────────────────────────
INSERT IGNORE INTO `sys_objects_privacy`
  (`object`, `module`, `action`, `title`, `default_group`, `spaces`,
   `table`, `table_field_id`, `table_field_author`,
   `override_class_name`, `override_class_file`)
VALUES
  ('sa_rentals_allow_view_to', 'sa_rentals', 'view',
   '_sa_rentals_form_input_allow_view_to', '3', 'all',
   'sa_rentals_listings', 'id', 'author_id', '', '');

-- ─── Upgrade: new ACL actions ────────────────────────────────────────────────
INSERT IGNORE INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('sa_rentals', 'approve entry', NULL, '_acl_txt_sa_rentals_approve_entry', '', 0, ''),
  ('sa_rentals', 'feature entry', NULL, '_acl_txt_sa_rentals_feature_entry', '', 0, '');

-- Grant approve to moderators (5), feature+approve to admins (8)
INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 5, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_rentals' AND `Name` = 'approve entry';

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 8, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_rentals' AND `Name` IN ('approve entry', 'feature entry');

-- ─── Upgrade: feature toggles ────────────────────────────────────────────────
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_require_tenant_reg',  'on',  0, '_sa_rentals_opt_require_tenant_reg',  'checkbox', '', '', '', '', 1),
  ('sa_rentals_agent_verification',  'on',  0, '_sa_rentals_opt_agent_verification',  'checkbox', '', '', '', '', 2),
  ('sa_rentals_blacklisting',        '',    0, '_sa_rentals_opt_blacklisting',         'checkbox', '', '', '', '', 3),
  ('sa_rentals_show_banners',        '',    0, '_sa_rentals_opt_show_banners',         'checkbox', '', '', '', '', 4),
  ('sa_rentals_moderation',          '',    0, '_sa_rentals_opt_moderation',           'checkbox', '', '', '', '', 5);
-- ─── Upgrade: admin page + block ─────────────────────────────────────────────
INSERT INTO `sys_objects_page`
  (`author`,`added`,`object`,`uri`,`title_system`,`title`,`module`,`cover`,`cover_image`,`cover_title`,`type_id`,`layout_id`,`sticky_columns`,`submenu`,`visible_for_levels`,`visible_for_levels_editable`,`url`,`content_info`,`meta_title`,`meta_description`,`meta_keywords`,`meta_robots`,`cache_lifetime`,`cache_editable`,`inj_head`,`inj_footer`,`config_api`,`deletable`,`override_class_name`,`override_class_file`)
SELECT
  0,UNIX_TIMESTAMP(),'sa_rentals_admin','sa-rentals-admin','_sa_rentals_page_admin_sys','_sa_rentals_page_admin','sa_rentals',0,0,'',1,5,0,'',36,1,'page.php?i=sa-rentals-admin','','','','','',0,1,'','','',0,'',''
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `sys_objects_page` WHERE `object` = 'sa_rentals_admin');

-- Serialized: sa_rentals=10, get_admin_listings_block=24
INSERT INTO `sys_pages_blocks`
  (`object`,`cell_id`,`module`,`title_system`,`title`,`designbox_id`,`class`,`submenu`,`tabs`,`async`,`visible_for_levels`,`hidden_on`,`type`,`content`,`content_empty`,`text`,`text_updated`,`help`,`cache_lifetime`,`config_api`,`deletable`,`copyable`,`active`,`active_api`,`order`)
SELECT
  'sa_rentals_admin',1,'sa_rentals','','_sa_rentals_block_admin',11,'','',0,0,36,'','service','a:2:{s:6:"module";s:10:"sa_rentals";s:6:"method";s:24:"get_admin_listings_block";}','','',0,'',0,'',0,1,1,0,1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `sys_pages_blocks` WHERE `object` = 'sa_rentals_admin' AND `module` = 'sa_rentals');

-- ─── Phase 1 Enhancement: new sys_options (v3 spec) ──────────────────────────
-- Category: Listings
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_expiry_days',          '30', 0, '_sa_rentals_opt_expiry_days',          'select', 'a:5:{i:0;s:5:"Never";i:30;s:5:"30 Days";i:60;s:5:"60 Days";i:90;s:5:"90 Days";i:180;s:7:"180 Days";}', '', '', '', 6),
  ('sa_rentals_expiry_reminder_days', '7',  0, '_sa_rentals_opt_expiry_reminder_days', 'digit',  '', '', '', '', 7),
  ('sa_rentals_verified_badge',       'on', 0, '_sa_rentals_opt_verified_badge',       'checkbox', '', '', '', '', 8);

-- Category: Quotas
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_landlord_quota',  '3',  0, '_sa_rentals_opt_landlord_quota',  'digit', '', '', '', '', 9),
  ('sa_rentals_agent_quota',     '0',  0, '_sa_rentals_opt_agent_quota',     'digit', '', '', '', '', 10),
  ('sa_rentals_landlord_photos', '5',  0, '_sa_rentals_opt_landlord_photos', 'digit', '', '', '', '', 11),
  ('sa_rentals_agent_photos',    '20', 0, '_sa_rentals_opt_agent_photos',    'digit', '', '', '', '', 12);

-- Category: Enquiries
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_enquiry_enabled',  'on', 0, '_sa_rentals_opt_enquiry_enabled',  'checkbox', '', '', '', '', 13),
  ('sa_rentals_enquiry_throttle', '5',  0, '_sa_rentals_opt_enquiry_throttle', 'digit',    '', '', '', '', 14);

-- Category: Features (whatsapp_enabled is new; others already inserted above but IGNORE is safe)
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_whatsapp_enabled', 'on', 0, '_sa_rentals_opt_whatsapp_enabled', 'checkbox', '', '', '', '', 15);

-- Category: Paid Levels
INSERT IGNORE INTO `sys_options`
  (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_params`, `check_error`, `order`)
VALUES
  ('sa_rentals_landlord_level_id', '0', 0, '_sa_rentals_opt_landlord_level_id', 'digit', '', '', '', '', 16),
  ('sa_rentals_agent_level_id',    '0', 0, '_sa_rentals_opt_agent_level_id',    'digit', '', '', '', '', 17);
-- ─────────────────────────────────────────────────────────────────────────────

-- ─── Phase 1 Enhancement: new ACL actions ────────────────────────────────────
INSERT IGNORE INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('sa_rentals', 'verify listing',  NULL, '_acl_txt_sa_rentals_verify_listing',  '', 0, ''),
  ('sa_rentals', 'blacklist member', NULL, '_acl_txt_sa_rentals_blacklist_member', '', 0, '');

-- verify listing + blacklist member: moderators (7) and admin (8)
INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 7, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_rentals' AND `Name` IN ('blacklist member');

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 8, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_rentals' AND `Name` IN ('verify listing', 'blacklist member');
-- ─────────────────────────────────────────────────────────────────────────────

-- ─── Phase 1 Enhancement: new schema columns ─────────────────────────────────
ALTER TABLE `sa_rentals_listings`
  ADD COLUMN IF NOT EXISTS `expires_at`  date       NULL AFTER `updated`,
  ADD COLUMN IF NOT EXISTS `renewed_at`  date       NULL AFTER `expires_at`,
  ADD COLUMN IF NOT EXISTS `verified`    tinyint(1) NOT NULL DEFAULT 0 AFTER `renewed_at`,
  ADD COLUMN IF NOT EXISTS `verified_by` int(11)    NOT NULL DEFAULT 0 AFTER `verified`,
  ADD COLUMN IF NOT EXISTS `verified_at` datetime   NULL AFTER `verified_by`;
-- ─────────────────────────────────────────────────────────────────────────────

-- ─── Phase 2: tables (safe — CREATE TABLE IF NOT EXISTS) ─────────────────────
CREATE TABLE IF NOT EXISTS `sa_rentals_agents` (
  `id`          int(11)       NOT NULL AUTO_INCREMENT,
  `member_id`   int(11)       NOT NULL,
  `agency_name` varchar(255)  NOT NULL DEFAULT '',
  `ppre_number` varchar(50)   NOT NULL DEFAULT '',
  `status`      enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` int(11)       NOT NULL DEFAULT 0,
  `verified_at` datetime      NULL,
  `created`     datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sa_rentals_tenants` (
  `id`         int(11)  NOT NULL AUTO_INCREMENT,
  `member_id`  int(11)  NOT NULL,
  `income_band` varchar(50) NOT NULL DEFAULT '',
  `references` text,
  `qualified`  tinyint(1) NOT NULL DEFAULT 0,
  `created`    datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sa_rentals_enquiries` (
  `id`         int(11)  NOT NULL AUTO_INCREMENT,
  `listing_id` int(11)  NOT NULL,
  `tenant_id`  int(11)  NOT NULL,
  `message`    text,
  `status`     enum('pending','read','replied','closed') NOT NULL DEFAULT 'pending',
  `created`    datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sa_rentals_blacklist` (
  `id`         int(11)       NOT NULL AUTO_INCREMENT,
  `member_id`  int(11)       NOT NULL DEFAULT 0,
  `email`      varchar(255)  NOT NULL DEFAULT '',
  `ip`         varchar(45)   NOT NULL DEFAULT '',
  `reason`     text,
  `added_by`   int(11)       NOT NULL DEFAULT 0,
  `expires_at` datetime      NULL,
  `created`    datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ─────────────────────────────────────────────────────────────────────────────

-- =====================================================
-- COMMUNITY SUPPORT SCHEME MODULE INSTALLATION
-- Compatible with UNACMS 14.0.0
-- =====================================================

-- STUDIO WIDGET
INSERT INTO `sys_std_pages`(`index`, `name`, `header`, `caption`, `icon`) VALUES
(3, 'sa_support_scheme', '_sa_support_scheme', '_sa_support_scheme', 'sa_support_scheme@modules/sa/support_scheme/|std-pi.png');

SET @iPageId = LAST_INSERT_ID();
SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);

INSERT INTO `sys_std_widgets` (`page_id`, `module`, `url`, `click`, `icon`, `caption`, `cnt_notices`, `cnt_actions`) VALUES
(@iPageId, 'sa_support_scheme', '{url_studio}module.php?name=sa_support_scheme', '', 'sa_support_scheme@modules/sa/support_scheme/|std-wi.png', '_sa_support_scheme', '', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');

INSERT INTO `sys_std_pages_widgets` (`page_id`, `widget_id`, `order`) VALUES
(@iParentPageId, LAST_INSERT_ID(), @iParentPageOrder);

-- =====================================================
-- DATABASE TABLES
-- =====================================================

-- CAMPAIGNS TABLE
CREATE TABLE IF NOT EXISTS `sa_support_scheme_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `goal_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `current_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) NOT NULL DEFAULT 'ZAR',
  `author_id` int(11) NOT NULL,
  `space_id` int(11) DEFAULT NULL,
  `status` enum('active','completed','paused','draft') NOT NULL DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `beneficiary_name` varchar(255) DEFAULT NULL,
  `beneficiary_story` text,
  `end_date` datetime DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `urgent` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `views` int(11) NOT NULL DEFAULT '0',
  `donations_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `urgent` (`urgent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DONATIONS TABLE
CREATE TABLE IF NOT EXISTS `sa_support_scheme_donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'ZAR',
  `message` text,
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `donor_id` (`donor_id`),
  KEY `payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- BENEFICIARIES TABLE
CREATE TABLE IF NOT EXISTS `sa_support_scheme_beneficiaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `story` text,
  `needs` text,
  `location` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` int(11) DEFAULT NULL,
  `verified_date` datetime DEFAULT NULL,
  `space_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PAGES
-- =====================================================

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_support_scheme_campaigns', 'support-scheme-campaigns', '_sa_support_scheme_page_campaigns_sys', '_sa_support_scheme_page_campaigns', 'sa_support_scheme', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=support-scheme-campaigns', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_support_scheme_campaign', 'view-support-scheme-campaign', '_sa_support_scheme_page_campaign_sys', '_sa_support_scheme_page_campaign', 'sa_support_scheme', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-support-scheme-campaign', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_support_scheme_create_campaign', 'create-support-scheme-campaign', '_sa_support_scheme_page_create_sys', '_sa_support_scheme_page_create', 'sa_support_scheme', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=create-support-scheme-campaign', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_support_scheme_my_campaigns', 'my-support-scheme-campaigns', '_sa_support_scheme_page_my_campaigns_sys', '_sa_support_scheme_page_my_campaigns', 'sa_support_scheme', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=my-support-scheme-campaigns', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- =====================================================
-- PAGE BLOCKS
-- =====================================================

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_support_scheme_campaigns', 1, 'sa_support_scheme', '', '_sa_support_scheme_block_campaigns', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:19:"get_campaigns_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_support_scheme_campaign', 1, 'sa_support_scheme', '', '_sa_support_scheme_block_campaign_details', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:26:"get_campaign_details_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_support_scheme_campaign', 2, 'sa_support_scheme', '', '_sa_support_scheme_block_donate', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:23:"get_donation_form_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 2);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_support_scheme_create_campaign', 1, 'sa_support_scheme', '', '_sa_support_scheme_block_create_campaign', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:25:"get_create_campaign_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_support_scheme_my_campaigns', 1, 'sa_support_scheme', '', '_sa_support_scheme_block_my_campaigns', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:22:"get_my_campaigns_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- =====================================================
-- MENU ITEMS - UNACMS 14.0.0 Compatible
-- =====================================================

SET @iMenuOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `parent_id` = 0);

INSERT INTO `sys_menu_items` (`parent_id`, `set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `addon_cache`, `markers`, `submenu_object`, `submenu_popup`, `visible_for_levels`, `visibility_custom`, `hidden_on`, `hidden_on_cxt`, `hidden_on_pt`, `hidden_on_col`, `config_api`, `primary`, `collapsed`, `active`, `active_api`, `copyable`, `editable`, `order`) VALUES
(0, 'sys_site', 'sa_support_scheme', 'support-scheme', '_sa_support_scheme_menu_item_sys', '_sa_support_scheme_menu_item', 'page.php?i=support-scheme-campaigns', '', '', 'hand-holding-heart col-red3', '', 0, '', '', 0, 2147483647, '', '', '', 0, 0, '', 0, 0, 1, 0, 1, 1, @iMenuOrder);

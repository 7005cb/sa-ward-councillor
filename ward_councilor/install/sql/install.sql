-- =====================================================
-- WARD COUNCILOR PORTAL MODULE INSTALLATION
-- =====================================================

-- STUDIO WIDGET
INSERT INTO `sys_std_pages`(`index`, `name`, `header`, `caption`, `icon`) VALUES
(3, 'sa_ward_councilor', '_sa_ward_councilor', '_sa_ward_councilor', 'sa_ward_councilor@modules/sa/ward_councilor/|std-pi.png');

SET @iPageId = LAST_INSERT_ID();
SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);

INSERT INTO `sys_std_widgets` (`page_id`, `module`, `url`, `click`, `icon`, `caption`, `cnt_notices`, `cnt_actions`) VALUES
(@iPageId, 'sa_ward_councilor', '{url_studio}module.php?name=sa_ward_councilor', '', 'sa_ward_councilor@modules/sa/ward_councilor/|std-wi.png', '_sa_ward_councilor', '', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');

INSERT INTO `sys_std_pages_widgets` (`page_id`, `widget_id`, `order`) VALUES
(@iParentPageId, LAST_INSERT_ID(), @iParentPageOrder);

-- =====================================================
-- DATABASE TABLES
-- =====================================================

-- SERVICE REQUESTS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','active','rejected','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `location` varchar(500) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `space_id` int(11) DEFAULT NULL,
  `allow_view_to` int(11) NOT NULL DEFAULT '2',
  `councilor_notes` text,
  `views` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MEETINGS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `meeting_date` datetime NOT NULL,
  `location` varchar(500) DEFAULT NULL,
  `type` enum('community','public_forum','committee','special') NOT NULL DEFAULT 'community',
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `space_id` int(11) DEFAULT NULL,
  `allow_view_to` int(11) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `space_id` (`space_id`),
  KEY `meeting_date` (`meeting_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ANNOUNCEMENTS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `author_id` int(11) DEFAULT NULL,
  `space_id` int(11) DEFAULT NULL,
  `allow_view_to` int(11) NOT NULL DEFAULT '1',
  `views` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `pinned` (`pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ACTIVITY NOTES TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `note` text NOT NULL,
  `status_change` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WARD INFO TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `space_id` int(11) NOT NULL,
  `ward_number` varchar(20) DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `population` int(11) DEFAULT NULL,
  `councillor_name` varchar(255) DEFAULT NULL,
  `description` text,
  `office_address` varchar(500) DEFAULT NULL,
  `office_hours` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `space_id` (`space_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PAGES
-- =====================================================

-- Main Dashboard Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_dashboard', 'ward-councilor-dashboard', '_sa_ward_councilor_page_dashboard_sys', '_sa_ward_councilor_page_dashboard', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=ward-councilor-dashboard', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Service Requests Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_requests', 'ward-requests', '_sa_ward_councilor_page_requests_sys', '_sa_ward_councilor_page_requests', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=ward-requests', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- View Request Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_request', 'view-ward-request', '_sa_ward_councilor_page_request_sys', '_sa_ward_councilor_page_request', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-ward-request', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Create Request Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_create_request', 'create-ward-request', '_sa_ward_councilor_page_create_request_sys', '_sa_ward_councilor_page_create_request', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=create-ward-request', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Meetings Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_meetings', 'ward-meetings', '_sa_ward_councilor_page_meetings_sys', '_sa_ward_councilor_page_meetings', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=ward-meetings', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- View Meeting Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_meeting', 'view-ward-meeting', '_sa_ward_councilor_page_meeting_sys', '_sa_ward_councilor_page_meeting', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-ward-meeting', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Create Meeting Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_create_meeting', 'create-ward-meeting', '_sa_ward_councilor_page_create_meeting_sys', '_sa_ward_councilor_page_create_meeting', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=create-ward-meeting', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Announcements Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_announcements', 'ward-announcements', '_sa_ward_councilor_page_announcements_sys', '_sa_ward_councilor_page_announcements', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=ward-announcements', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- View Announcement Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_announcement', 'view-ward-announcement', '_sa_ward_councilor_page_announcement_sys', '_sa_ward_councilor_page_announcement', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-ward-announcement', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Create Announcement Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_create_announcement', 'create-ward-announcement', '_sa_ward_councilor_page_create_announcement_sys', '_sa_ward_councilor_page_create_announcement', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=create-ward-announcement', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- My Requests Page
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_my_requests', 'my-ward-requests', '_sa_ward_councilor_page_my_requests_sys', '_sa_ward_councilor_page_my_requests', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=my-ward-requests', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- Manage Page (back-office)
INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), 'sa_ward_councilor_manage', 'ward-manage', '_sa_ward_councilor_page_manage_sys', '_sa_ward_councilor_page_manage', 'sa_ward_councilor', 0, 0, '', 1, 5, 0, 'sa_ward_councilor_menu', 2147483647, 1, 'page.php?i=ward-manage', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

UPDATE `sys_objects_page` SET `submenu` = 'sa_ward_councilor_menu' WHERE `module` = 'sa_ward_councilor';

-- =====================================================
-- PAGE BLOCKS
-- "sa_ward_councilor" = 18 chars
-- =====================================================

-- Dashboard Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_dashboard', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_dashboard', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:19:"get_dashboard_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Requests List Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_requests', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_requests', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:18:"get_requests_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Request Details Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_request', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_request_details', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:25:"get_request_details_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Create Request Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_create_request', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_create_request', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:24:"get_create_request_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Meetings List Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_meetings', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_meetings', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:18:"get_meetings_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Meeting Details Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_meeting', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_meeting_details', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:25:"get_meeting_details_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Create Meeting Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_create_meeting', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_create_meeting', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:24:"get_create_meeting_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Announcements List Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_announcements', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_announcements', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:23:"get_announcements_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Announcement Details Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_announcement', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_announcement_details', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:30:"get_announcement_details_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Create Announcement Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_create_announcement', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_create_announcement', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:29:"get_create_announcement_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- My Requests Block
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_my_requests', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_my_requests', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:21:"get_my_requests_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Manage Block (back-office)
INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('sa_ward_councilor_manage', 1, 'sa_ward_councilor', '', '_sa_ward_councilor_block_manage', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:16:"get_manage_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- Space Summary block (inline on Space page)
INSERT INTO `sys_pages_blocks`
    (`object`, `cell_id`, `module`,
     `title_system`, `title`,
     `designbox_id`, `class`, `submenu`, `tabs`, `async`,
     `visible_for_levels`, `hidden_on`,
     `type`, `content`,
     `content_empty`, `text`, `text_updated`, `help`,
     `cache_lifetime`, `config_api`,
     `deletable`, `copyable`, `active`, `active_api`, `order`)
VALUES
    ('bx_spaces_view_profile', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_space_summary',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:24:"get_space_summary_block";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 1);

-- Ward Navigation Strip block (inline on Space page)
INSERT INTO `sys_pages_blocks`
    (`object`, `cell_id`, `module`,
     `title_system`, `title`,
     `designbox_id`, `class`, `submenu`, `tabs`, `async`,
     `visible_for_levels`, `hidden_on`,
     `type`, `content`,
     `content_empty`, `text`, `text_updated`, `help`,
     `cache_lifetime`, `config_api`,
     `deletable`, `copyable`, `active`, `active_api`, `order`)
VALUES
    ('bx_spaces_view_profile', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_nav_strip',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:18:"get_ward_nav_strip";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 2);

-- Ward Sidebar block (inline on Space page)
INSERT INTO `sys_pages_blocks`
    (`object`, `cell_id`, `module`,
     `title_system`, `title`,
     `designbox_id`, `class`, `submenu`, `tabs`, `async`,
     `visible_for_levels`, `hidden_on`,
     `type`, `content`,
     `content_empty`, `text`, `text_updated`, `help`,
     `cache_lifetime`, `config_api`,
     `deletable`, `copyable`, `active`, `active_api`, `order`)
VALUES
    ('bx_spaces_view_profile', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_sidebar',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:17:"get_sidebar_block";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 3);

-- =====================================================
-- MENU ITEMS
-- =====================================================

-- Main Menu Item
SET @iMenuOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `parent_id` = 0);
INSERT INTO `sys_menu_items` (`parent_id`, `set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `addon_cache`, `markers`, `submenu_object`, `submenu_popup`, `visible_for_levels`, `visibility_custom`, `hidden_on`, `hidden_on_cxt`, `hidden_on_pt`, `hidden_on_col`, `config_api`, `primary`, `collapsed`, `active`, `active_api`, `copyable`, `editable`, `order`) VALUES
(0, 'sys_site', 'sa_ward_councilor', 'ward-councilor', '_sa_ward_councilor_menu_item_sys', '_sa_ward_councilor_menu_item', 'page.php?i=ward-councilor-dashboard', '', '', 'landmark col-green3', '', 0, '', '', 0, 2147483647, '', '', '', 0, 0, '', 0, 0, 1, 0, 1, 1, @iMenuOrder);

-- =====================================================
-- HORIZONTAL NAV MENU
-- =====================================================
UPDATE `sys_menu_items` SET `submenu_object` = 'sa_ward_councilor_menu' WHERE `module` = 'sa_ward_councilor' AND `set_name` = 'sys_site' AND `name` = 'ward-councilor';

INSERT IGNORE INTO `sys_menu_sets` (`set_name`, `module`, `title`, `deletable`) VALUES
('sa_ward_councilor_menu', 'sa_ward_councilor', 'Ward Councilor Navigation', 1);

INSERT IGNORE INTO `sys_objects_menu` (`object`, `title`, `set_name`, `module`, `template_id`, `config_api`, `persistent`, `deletable`, `active`, `override_class_name`, `override_class_file`) VALUES
('sa_ward_councilor_menu', 'Ward Councilor Navigation', 'sa_ward_councilor_menu', 'sa_ward_councilor', 8, '', 0, 1, 1, '', '');

SET @iNavOrder = 1;
INSERT INTO `sys_menu_items` (`parent_id`, `set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `addon_cache`, `markers`, `submenu_object`, `submenu_popup`, `visible_for_levels`, `visibility_custom`, `hidden_on`, `hidden_on_cxt`, `hidden_on_pt`, `hidden_on_col`, `config_api`, `primary`, `collapsed`, `active`, `active_api`, `copyable`, `editable`, `order`) VALUES
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-dashboard','_sa_ward_councilor_menu_dashboard_sys','_sa_ward_councilor_menu_dashboard','page.php?i=ward-councilor-dashboard','','','landmark col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,1),
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-requests','_sa_ward_councilor_menu_requests_sys','_sa_ward_councilor_menu_requests','page.php?i=ward-requests','','','list-alt col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,2),
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-meetings','_sa_ward_councilor_menu_meetings_sys','_sa_ward_councilor_menu_meetings','page.php?i=ward-meetings','','','far calendar col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,3),
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-announcements','_sa_ward_councilor_menu_announcements_sys','_sa_ward_councilor_menu_announcements','page.php?i=ward-announcements','','','bullhorn col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,4),
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-my-requests','_sa_ward_councilor_menu_my_requests_sys','_sa_ward_councilor_menu_my_requests','page.php?i=my-ward-requests','','','far user col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,5),
(0,'sa_ward_councilor_menu','sa_ward_councilor','ward-manage','_sa_ward_councilor_menu_manage_sys','_sa_ward_councilor_menu_manage','page.php?i=ward-manage','','','cog col-green3','',0,'','',0,2147483647,'','','',0,0,'',0,0,1,0,1,1,6);

-- =====================================================
-- LANGUAGE STRINGS
-- =====================================================
INSERT IGNORE INTO `sys_localization_categories` (`Name`) VALUES ('sa_ward_councilor');

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_dashboard_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_dashboard' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_requests_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_requests' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_meetings_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_meetings' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_announcements_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_announcements' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_my_requests_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_my_requests' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_item_sys' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`) SELECT `ID`, '_sa_ward_councilor_menu_item' FROM `sys_localization_categories` WHERE `Name`='sa_ward_councilor';

SET @lid = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name`='en' LIMIT 1);
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Dashboard' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_dashboard_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Dashboard' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_dashboard';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Service Requests' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_requests_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Service Requests' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_requests';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Meetings' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_meetings_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Meetings' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_meetings';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Announcements' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_announcements_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Announcements' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_announcements';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'My Requests' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_my_requests_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'My Requests' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_my_requests';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Ward Councilor Portal' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_item_sys';
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`) SELECT k.`ID`, @lid, 'Ward Portal' FROM `sys_localization_keys` k WHERE k.`Key`='_sa_ward_councilor_menu_item';

-- ─── ACL: Register module actions ──────────────────────────────────────────
INSERT INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('sa_ward_councilor', 'view entry',       NULL, '_acl_txt_sa_ward_councilor_view_entry',       '', 0, ''),
  ('sa_ward_councilor', 'create entry',     NULL, '_acl_txt_sa_ward_councilor_create_entry',     '', 0, ''),
  ('sa_ward_councilor', 'edit own entry',   NULL, '_acl_txt_sa_ward_councilor_edit_own_entry',   '', 0, ''),
  ('sa_ward_councilor', 'edit any entry',   NULL, '_acl_txt_sa_ward_councilor_edit_any_entry',   '', 0, ''),
  ('sa_ward_councilor', 'delete own entry', NULL, '_acl_txt_sa_ward_councilor_delete_own_entry', '', 0, ''),
  ('sa_ward_councilor', 'delete any entry', NULL, '_acl_txt_sa_ward_councilor_delete_any_entry', '', 0, ''),
  ('sa_ward_councilor', 'approve entry',   NULL, '_acl_txt_sa_ward_councilor_approve_entry',   '', 0, '');

-- Grant Standard members basic content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor'
AND a.Name IN ('view entry', 'create entry', 'edit own entry', 'delete own entry')
AND l.Name IN ('Standard', '_adm_prm_txt_level_standard');

-- Grant Moderators full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor'
AND l.Name IN ('Moderator', '_adm_prm_txt_level_moderator');

-- Grant Administrators full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor'
AND l.Name IN ('Administrator', '_adm_prm_txt_level_administrator');

-- Grant Councillors full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor'
AND l.Name LIKE '%Councillor%';

-- Grant Leadership full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor'
AND l.Name LIKE '%Leadership%';
-- ────────────────────────────────────────────────────────────────────────────

-- ─── Timeline: Register content info object ─────────────────────────────────
INSERT INTO `sys_objects_content_info`
  (`name`, `title`, `alert_unit`, `alert_action_add`, `alert_action_update`, `alert_action_delete`, `class_name`, `class_file`)
VALUES
  ('sa_ward_councilor', '_sa_ward_councilor_content_info', 'sa_ward_councilor', 'added', 'edited', 'deleted', '', '');

-- NOTE: bx_timeline_handlers, sys_alerts, and bx_notifications_handlers are NOT inserted here.
-- They are registered automatically via serviceGetTimelineData() when the module is enabled
-- through the relation_handlers mechanism (bx_timeline on_enable => add_handlers).
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `sys_objects_privacy`
  (`object`, `module`, `action`, `title`, `default_group`, `spaces`,
   `table`, `table_field_id`, `table_field_author`, `override_class_name`, `override_class_file`)
VALUES
  ('sa_ward_councilor_request_allow_view_to', 'sa_ward_councilor', 'view_request',
   '_sa_ward_councilor_privacy_view', '2', 'bx_spaces',
   'sa_ward_councilor_requests', 'id', 'author_id', '', ''),
  ('sa_ward_councilor_announcement_allow_view_to', 'sa_ward_councilor', 'view_announcement',
   '_sa_ward_councilor_privacy_view', '1', 'bx_spaces',
   'sa_ward_councilor_announcements', 'id', 'author_id', '', ''),
  ('sa_ward_councilor_meeting_allow_view_to', 'sa_ward_councilor', 'view_meeting',
   '_sa_ward_councilor_privacy_view', '1', 'bx_spaces',
   'sa_ward_councilor_meetings', 'id', 'author_id', '', '');
-- ────────────────────────────────────────────────────────────────────────────

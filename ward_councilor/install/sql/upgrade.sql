-- =====================================================
-- UPGRADE: Ward Nav Strip + Sidebar + Space Summary
-- Serialization audit:
-- sa_ward_councilor        = 17 chars
-- get_ward_nav_strip       = 18 chars
-- get_sidebar_block        = 17 chars
-- get_space_summary_block  = 24 chars
-- =====================================================

-- Set language ID first so all string INSERTs below can use it
SET @lid = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'en' LIMIT 1);

-- ── Remove any duplicates from previous upgrade runs ──────────
DELETE FROM `sys_pages_blocks`
WHERE `module` = 'sa_ward_councilor'
  AND `type` = 'service'
  AND `title` IN (
      '_sa_ward_councilor_block_nav_strip',
      '_sa_ward_councilor_block_sidebar',
      '_sa_ward_councilor_block_space_summary'
  );

-- ── Ward Summary block (inline on Space page) ─────────────────
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
    ('sa_ward_councilor_dashboard', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_space_summary',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:24:"get_space_summary_block";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 1);

-- ── Ward Navigation Strip block ───────────────────────────────
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
    ('sa_ward_councilor_dashboard', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_nav_strip',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:18:"get_ward_nav_strip";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 2);

-- ── Ward Sidebar block ────────────────────────────────────────
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
    ('sa_ward_councilor_dashboard', 0, 'sa_ward_councilor',
     '', '_sa_ward_councilor_block_sidebar',
     13, '', '', 0, 0,
     2147483647, '',
     'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:17:"get_sidebar_block";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 3);

-- ── Language keys ─────────────────────────────────────────────
INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_block_space_summary'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_block_nav_strip'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_block_sidebar'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_nav_ward_label'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_sidebar_ward_functions'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_sidebar_child_spaces'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_localization_keys` (`IDCategory`, `Key`)
    SELECT `ID`, '_sa_ward_councilor_menu_manage'
    FROM `sys_localization_categories` WHERE `Name` = 'sa_ward_councilor';

-- ── Language strings (all use @lid set at top) ────────────────
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward Summary'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_block_space_summary';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward Navigation Strip'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_block_nav_strip';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward Sidebar'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_block_sidebar';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward {0}'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_nav_ward_label';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward Functions'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_sidebar_ward_functions';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Sub-Areas'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_sidebar_child_spaces';

INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Manage'
    FROM `sys_localization_keys` k WHERE k.`Key` = '_sa_ward_councilor_menu_manage';

-- ─── Upgrade: approve entry ACL action ───────────────────────────
INSERT IGNORE INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('sa_ward_councilor', 'approve entry', NULL, '_acl_txt_sa_ward_councilor_approve_entry', '', 0, '');

-- Grant approve entry to moderation roles (dynamic name lookup)
INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor' AND a.Name = 'approve entry'
AND l.Name IN ('Moderator', '_adm_prm_txt_level_moderator');

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor' AND a.Name = 'approve entry'
AND l.Name IN ('Administrator', '_adm_prm_txt_level_administrator');

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor' AND a.Name = 'approve entry'
AND l.Name LIKE '%Leadership%';

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT l.ID, a.ID FROM sys_acl_levels l, sys_acl_actions a
WHERE a.Module = 'sa_ward_councilor' AND a.Name = 'approve entry'
AND l.Name LIKE '%Councillor%';

-- ─── Upgrade: add active + rejected to requests status ENUM ──────
ALTER TABLE `sa_ward_councilor_requests`
  MODIFY COLUMN `status` enum('pending','active','rejected','in_progress','resolved','closed') NOT NULL DEFAULT 'pending';

-- ─── Upgrade: add missing allow_view_to column to requests table ──
ALTER TABLE `sa_ward_councilor_requests`
  ADD COLUMN IF NOT EXISTS `allow_view_to` int(11) NOT NULL DEFAULT '2' AFTER `space_id`;

-- ─── Upgrade: add missing columns to notes table ─────────────────
ALTER TABLE `sa_ward_councilor_notes`
  ADD COLUMN IF NOT EXISTS `author_name` varchar(255) DEFAULT NULL AFTER `author_id`,
  ADD COLUMN IF NOT EXISTS `actor_role` varchar(50) DEFAULT NULL AFTER `author_name`;

-- ─── Upgrade: add space page blocks (summary, nav_strip, sidebar) ──
INSERT IGNORE INTO `sys_pages_blocks`
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
     'service', 'a:2:{s:6:\"module\";s:17:\"sa_ward_councilor\";s:6:\"method\";s:24:\"get_space_summary_block\";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 1);

INSERT IGNORE INTO `sys_pages_blocks`
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
     'service', 'a:2:{s:6:\"module\";s:17:\"sa_ward_councilor\";s:6:\"method\";s:18:\"get_ward_nav_strip\";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 2);

INSERT IGNORE INTO `sys_pages_blocks`
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
     'service', 'a:2:{s:6:\"module\";s:17:\"sa_ward_councilor\";s:6:\"method\";s:17:\"get_sidebar_block\";}',
     '', '', 0, '',
     0, '',
     0, 1, 1, 0, 3);

-- ── Create Request page/block backfill ────────────────────────
-- Runtime membership enforcement lives in serviceGetCreateRequestBlock().
INSERT INTO `sys_objects_page`
    (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`,
     `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`,
     `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`,
     `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`,
     `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`,
     `deletable`, `override_class_name`, `override_class_file`)
SELECT
    0, UNIX_TIMESTAMP(), 'sa_ward_councilor_create_request', 'create-ward-request',
    '_sa_ward_councilor_page_create_request_sys', '_sa_ward_councilor_page_create_request',
    'sa_ward_councilor', 0, 0, '', 1, 5, 0, '', 2147483647, 1,
    'page.php?i=create-ward-request', '', '', '', '', '', 0, 1, '', '', '', 0, '', ''
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `sys_objects_page`
    WHERE `object` = 'sa_ward_councilor_create_request'
       OR `uri` = 'create-ward-request'
);

INSERT INTO `sys_pages_blocks`
    (`object`, `cell_id`, `module`,
     `title_system`, `title`,
     `designbox_id`, `class`, `submenu`, `tabs`, `async`,
     `visible_for_levels`, `hidden_on`,
     `type`, `content`,
     `content_empty`, `text`, `text_updated`, `help`,
     `cache_lifetime`, `config_api`,
     `deletable`, `copyable`, `active`, `active_api`, `order`)
SELECT
    'sa_ward_councilor_create_request', 1, 'sa_ward_councilor',
    '', '_sa_ward_councilor_block_create_request',
    11, '', '', 0, 0,
    2147483647, '',
    'service', 'a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:24:"get_create_request_block";}',
    '', '', 0, '',
    0, '',
    0, 1, 1, 0, 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `sys_pages_blocks`
    WHERE `object` = 'sa_ward_councilor_create_request'
      AND `module` = 'sa_ward_councilor'
);

-- ─── Upgrade: fix menu visible_for_levels ────────────────────────
UPDATE `sys_menu_items` SET `visible_for_levels` = 2752
WHERE `module` = 'sa_ward_councilor' AND `name` = 'ward-manage';

UPDATE `sys_menu_items` SET `visible_for_levels` = 2147483644
WHERE `module` = 'sa_ward_councilor' AND `name` = 'ward-my-requests';

-- =====================================================
-- Community requests table (members ask for a new community)
-- =====================================================
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_community_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `community_name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(100) NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

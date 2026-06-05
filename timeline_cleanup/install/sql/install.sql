-- =====================================================
-- TIMELINE CLEANUP TOOL - INSTALL
-- =====================================================

-- STUDIO WIDGET
INSERT INTO `sys_std_pages`(`index`, `name`, `header`, `caption`, `icon`) VALUES
(3, 'sa_timeline_cleanup', '_sa_timeline_cleanup', '_sa_timeline_cleanup', 'sa_timeline_cleanup@modules/sa/timeline_cleanup/|std-pi.png');

SET @iPageId = LAST_INSERT_ID();
SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);

INSERT INTO `sys_std_widgets` (`page_id`, `module`, `url`, `click`, `icon`, `caption`, `cnt_notices`, `cnt_actions`) VALUES
(@iPageId, 'sa_timeline_cleanup', '{url_studio}module.php?name=sa_timeline_cleanup', '', 'sa_timeline_cleanup@modules/sa/timeline_cleanup/|std-wi.png', '_sa_timeline_cleanup', '', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');

INSERT INTO `sys_std_pages_widgets` (`page_id`, `widget_id`, `order`) VALUES
(@iParentPageId, LAST_INSERT_ID(), @iParentPageOrder);

-- =====================================================
-- SETTINGS
-- =====================================================

INSERT INTO `sys_options` (`name`, `value`, `type`, `caption`, `category_id`, `extra`, `check`, `order`) VALUES
('sa_timeline_cleanup_days', '90', 'digit', '_sa_timeline_cleanup_days', 0, '', '', 10),
('sa_timeline_cleanup_batch', '50', 'digit', '_sa_timeline_cleanup_batch', 0, '', '', 20),
('sa_timeline_cleanup_event_types', 'timeline_common_post,timeline_common_repost', 'text', '_sa_timeline_cleanup_event_types', 0, '', '', 30),
('sa_timeline_cleanup_dry_run', '1', 'checkbox', '_sa_timeline_cleanup_dry_run', 0, '', '', 40);

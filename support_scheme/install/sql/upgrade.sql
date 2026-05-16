-- =====================================================
-- COMMUNITY SUPPORT SCHEME - UPGRADE SQL
-- Run this to add new features to existing installation
-- =====================================================

-- Add featured and urgent columns to campaigns table
ALTER TABLE `sa_support_scheme_campaigns` 
ADD COLUMN `featured` tinyint(1) NOT NULL DEFAULT '0' AFTER `space_id`,
ADD COLUMN `urgent` tinyint(1) NOT NULL DEFAULT '0' AFTER `featured`;

-- Add indexes for new columns
ALTER TABLE `sa_support_scheme_campaigns` 
ADD INDEX `featured` (`featured`),
ADD INDEX `urgent` (`urgent`);

-- Verify the update
SELECT 'Upgrade complete! New columns added: featured, urgent' as status;

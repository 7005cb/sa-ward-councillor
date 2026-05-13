-- =====================================================
-- QUICK FIX: Force correct display names for all 3 nav blocks
-- Replaces stale/missing localization strings
-- =====================================================

SET @lid = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'en' LIMIT 1);

-- ── Force-update the space summary title (was showing raw key) ─
UPDATE `sys_localization_strings` ss
JOIN `sys_localization_keys` sk ON ss.`IDKey` = sk.`ID`
SET ss.`String` = 'Ward Summary'
WHERE sk.`Key` = '_sa_ward_councilor_block_space_summary'
  AND ss.`IDLanguage` = @lid;

-- If no row existed at all, insert it
INSERT IGNORE INTO `sys_localization_strings` (`IDKey`, `IDLanguage`, `String`)
    SELECT k.`ID`, @lid, 'Ward Summary'
    FROM `sys_localization_keys` k
    WHERE k.`Key` = '_sa_ward_councilor_block_space_summary'
      AND NOT EXISTS (
          SELECT 1 FROM `sys_localization_strings` ss2
          JOIN `sys_localization_keys` sk2 ON ss2.`IDKey` = sk2.`ID`
          WHERE sk2.`Key` = '_sa_ward_councilor_block_space_summary'
            AND ss2.`IDLanguage` = @lid
      );

-- ── Also ensure nav strip and sidebar titles are clean ─────────
UPDATE `sys_localization_strings` ss
JOIN `sys_localization_keys` sk ON ss.`IDKey` = sk.`ID`
SET ss.`String` = 'Ward Navigation Strip'
WHERE sk.`Key` = '_sa_ward_councilor_block_nav_strip'
  AND ss.`IDLanguage` = @lid;

UPDATE `sys_localization_strings` ss
JOIN `sys_localization_keys` sk ON ss.`IDKey` = sk.`ID`
SET ss.`String` = 'Ward Sidebar'
WHERE sk.`Key` = '_sa_ward_councilor_block_sidebar'
  AND ss.`IDLanguage` = @lid;

-- ── Remove duplicate blocks (keep lowest id per method) ────────
DELETE b1 FROM `sys_pages_blocks` b1
INNER JOIN `sys_pages_blocks` b2
    ON b1.`module` = 'sa_ward_councilor'
   AND b2.`module` = 'sa_ward_councilor'
   AND b1.`content` = b2.`content`
   AND b1.`id` > b2.`id`;

-- ── Clear compiled language cache ──────────────────────────────
DELETE FROM `sys_localization_strings_compiled`
WHERE `IDKey` IN (
    SELECT `ID` FROM `sys_localization_keys`
    WHERE `Key` IN (
        '_sa_ward_councilor_block_space_summary',
        '_sa_ward_councilor_block_nav_strip',
        '_sa_ward_councilor_block_sidebar'
    )
);

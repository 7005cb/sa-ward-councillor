---
name: una-fundamentals
description: >
  Shared UNA CMS 14.0 building blocks and execution protocols for all ModForge projects.
  Always load alongside any project skill before starting a build.
  Contains: architecture overview, module vs theme distinction, Module Builder v2.4,
  Theme Builder v2.2, serialization mathematics, failure patterns, execution modes,
  production deployment checklist, UNA wiki reference.
  Trigger on: UNA fundamentals, building blocks, execution protocol, start a build,
  module structure, theme structure, permissions architecture, mobile menus,
  ACL methods, how does UNA handle, what is the correct method for.
---

# UNA CMS 14.0 — Fundamentals & Execution Protocols v2.5

Load this alongside the project skill at every session start.
**When uncertain about any UNA pattern — check the wiki first (see bottom of this file).**

---

## UNA Architecture

Everything is a module: content | tool | service | template (theme).
Never edit core files. Work via module overrides, template overrides, Studio.
UNA version: 14.0.x — v15 columns forbidden in all SQL.

## Module vs Theme

| | Module | Theme |
|--|--------|-------|
| Base | BxDolModule | BxBaseModTemplateModule |
| Config type | standard | BX_DOL_MODULE_TYPE_TEMPLATE |
| CSS | template/css/main.css | data/template/*/css/ (delta only) |
| Protocol | Module Builder v2.4 | Theme Builder v2.2 |

---

## Non-Negotiable Rules

```
RULE 1:  Never trust memory. Read live DB first.
RULE 2:  Each stage is a gate. Don't skip.
RULE 3:  Live instance overrides the guide.
RULE 4:  One class per file. Always.
RULE 5:  Serialization is mathematics. Compute s:N byte counts. Never copy-paste.
RULE 6:  PHP security guard line 1: defined('BX_DOL') or die('hack attempt');
RULE 7:  Artificer CSS baseline. addHtmlHeader()+!important for non-Artificer.
RULE 8:  space_id column mandatory in all content module tables (Option A minimum).
RULE 9:  ACL actions mandatory. Gate every operation with BxDolAcl::getInstance()->isMemberLevelInSet().
RULE 10: Never use BxDolModuleQuery::isModuleInstalled() — method does not exist in UNA 14.
         Use: BxDolModuleQuery::getInstance()->getModuleByName('module_name')
         Or skip the check if module is always present on the platform.
RULE 11: ACL dynamic level lookup — never key $aStrings by IDKey integer.
         sys_localization_strings.IDKey is an integer FK, not the key string.
         Always join sys_localization_keys to get the varchar Key:
         SELECT k.Key, s.String FROM sys_localization_strings s
         JOIN sys_localization_keys k ON k.ID = s.IDKey
         WHERE k.Key LIKE '_adm_prm_txt_level_name_%'
         Then: $aStrings[$aLoc['Key']] = $aLoc['String']
RULE 12: Standard UNA ACL level IDs are FIXED on every install — use them directly:
         ID 3=Standard | ID 7=Moderator | ID 8=Administrator
         Custom levels (Leadership, Councillor) have variable IDs — never hardcode in install.sql.
         Grant custom levels via Studio > Permissions after install.
RULE 13: Use bx_process_input for all user input. Use BxDolDb::prepare for all SQL.
         Never use process_db_input, process_pass_data or BxDolDb::unescape.
RULE 14: Never use require_once — use bx_import instead (except index.php/faq.php type files).
RULE 15: CSS classes — use .bx-* for system, .bx-pre-* for module prefix.
         Module-specific prefix: .{vendor_initial}{module_initial}- (e.g. .wc- for ward councillor)
```

---

## Serialization Formula

```
a:2:{s:6:"module";s:N:"MODULE_NAME";s:6:"method";s:M:"METHOD_NAME";}
N = exact byte count of MODULE_NAME — count every character
M = exact byte count of METHOD_NAME — count every character
```

Examples: `sa_ward_councilor` = 18 | `sa_rentals` = 10 | `get_items_block` = 15

---

## Menu visible_for_levels — Bit Field Formula

From UNA wiki (Menus.md):
```
level_id → 2^(level_id - 1)

ID 1  (Unauthenticated) → 1
ID 2  (Account)         → 2
ID 3  (Standard)        → 4
ID 7  (Moderator)       → 64
ID 8  (Administrator)   → 128
ID 10 (custom)          → 512
ID 11 (custom)          → 1024
ID 12 (custom)          → 2048
All levels              → 2147483647
```

Examples:
- Moderator + Admin only: 64 + 128 = 192
- Admin + Leadership(10) + Councillor(12): 128 + 512 + 2048 = 2688
- Everyone: 2147483647

---

## Permissions Architecture — Run Before Stage 0

Three systems. Decide all three before code generation:

**ACL (BxDolAcl — membership level gates)**
Standard actions: view entry | create entry | edit own | edit any | delete own | delete any | approve | feature
Checklist: action list defined | default level per action | moderation flow decided

**Privacy (BxDolPrivacy — per-content visibility)**
Option A: space_id column only (RULE 8 minimum — manual filtering in queries)
Option B: sys_objects_privacy registration — native "Visible to…" dropdown + Space timeline integration
Checklist: A or B decided | default privacy integer per action | per-item or module-wide

**Context (space/group scoping)**
Spaces: bx_spaces_data | Groups: bx_groups_data | Both resolve to sys_profiles.id
Checklist: standalone/space/group/either | selector required? | member-only posting?

**Layered check pattern (generate for every gate):**
```php
public function checkAllowView($iEntryId) {
    if (!BxDolAcl::getInstance()->isMemberLevelInSet('view entry')) return false;
    $oPrivacy = BxDolPrivacy::getObject('{vendor}_{module}_entry_view');
    if ($oPrivacy && !$oPrivacy->check($iEntryId)) return false;
    $aEntry = $this->_oDb->getEntry($iEntryId);
    if (!empty($aEntry['space_id'])) {
        $oSpace = BxDolProfile::getInstance($aEntry['space_id']);
        if (!$oSpace || !$oSpace->isMember()) return false;
    }
    return true;
}
```

**Dynamic ACL level resolver pattern (for custom levels):**
```php
protected function _getModeratorLevelIds()
{
    if(self::$_aModLevelIds !== null) return self::$_aModLevelIds;
    $oDb = BxDolDb::getInstance();
    $aIds = array(7, 8); // Moderator, Administrator — always fixed IDs
    $aLevels = $oDb->getAll("SELECT ID, Name FROM sys_acl_levels WHERE ID > 8");
    $aStrings = array();
    // RULE 11: join sys_localization_keys — key by varchar Key not integer IDKey
    $aLocRows = $oDb->getAll("SELECT k.`Key`, s.`String`
        FROM sys_localization_strings s
        JOIN sys_localization_keys k ON k.ID = s.IDKey
        WHERE s.IDLanguage = (SELECT ID FROM sys_localization_languages WHERE Name = 'en' LIMIT 1)
        AND k.`Key` LIKE '_adm_prm_txt_level_name_%'");
    foreach($aLocRows as $aLoc) { $aStrings[$aLoc['Key']] = $aLoc['String']; }
    foreach($aLevels as $aLevel) {
        $sName = isset($aStrings[$aLevel['Name']]) ? $aStrings[$aLevel['Name']] : $aLevel['Name'];
        $sLower = strtolower($sName);
        if(strpos($sLower, 'councillor') !== false || strpos($sLower, 'leadership') !== false)
            $aIds[] = (int)$aLevel['ID'];
    }
    self::$_aModLevelIds = array_unique($aIds);
    return self::$_aModLevelIds;
}
```

---

## Module Builder v2.4 — Stage Summary

**Stage 0** — Confirm build spec + execution mode + permissions architecture.
Batch DB queries — never drip-feed. Check patterns.unaaistudio.com.
Gate: spec + mode + permissions decided.

**Stage 1** — Live environment. Batch ALL at once:
```sql
DESCRIBE sys_menu_items; DESCRIBE sys_pages_blocks; DESCRIBE sys_objects_page;
SELECT id FROM sys_std_pages WHERE name='home' LIMIT 1;
SELECT value FROM sys_options WHERE name='tmpl';
DESCRIBE sys_objects_privacy; SELECT * FROM sys_objects_privacy LIMIT 3;
DESCRIBE sys_acl_actions; SELECT * FROM sys_acl_levels ORDER BY id;
SELECT * FROM sys_acl_actions WHERE Module='bx_events' LIMIT 5;
```
Record: sys_menu_items column count | active theme | sys_acl_levels IDs.
Forbidden: title_attr, info, area_label, persistent.

**Stage 2** — Reference module extraction.
References: Events/Groups (content) | Market (commerce) | Tasks (tool) | Timeline (minimal).
Extract: sys_menu_items INSERT | sys_pages_blocks serialization | sys_acl_actions INSERT | addCss() signature.
addCss() fix: `public function addCss($mixedFiles = 'main.css', $bDynamic = false)`

**Stage 3** — File generation (15 files, this order):
config.php → installer.php → langs → Config → Db → Module → Template → CSS → HTML → request.php → install.sql (LAST) → uninstall/enable/disable/upgrade.sql
Module class MUST include: checkAllowView() | checkAllowEdit() | checkAllowDelete()
Module class MUST include: serviceGetTimelineData() | serviceGetTimelinePost() | serviceGetNotificationsData()
onEnable() MUST call add_handlers for bx_timeline AND bx_notifications.
alert_unit in BxDolAlerts MUST exactly match sys_objects_content_info name.

**Stage 4** — Serialization audit. Compute table for every service block. No estimates.

**Stage 5** — Write + read-back. Every file read back after write. Patch don't rewrite.
Mode C/D: ZIP bundle + deploy.sh mandatory.

**Stage 6** — Structural validation. Key checks:
- sys_acl_actions INSERT present | sys_acl_matrix uses fixed level IDs (3,7,8) only
- sys_objects_content_info registered | NO manual bx_timeline_handlers INSERT
- serviceGetTimelinePost() returns object_owner_id (int) + content (non-empty array)
- serviceGetTimelineData() handler groups use _object suffix (not _object_add etc.)
- serviceGetNotificationsData() has all three keys: handlers, settings, alerts
- uninstall.sql uses: DELETE table FROM table JOIN (NOT DELETE alias FROM table alias JOIN)
- install.sql ACL matrix uses INSERT IGNORE not INSERT

**Stage 7** — Handoff report with permissions architecture + timeline/notifications section.

---

## install.sql — ACL Matrix Pattern (MariaDB Safe)

```sql
-- Standard level IDs are fixed on ALL UNA installs — safe to hardcode
INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 3, `ID` FROM `sys_acl_actions`
WHERE `Module` = 'sa_ward_councilor'
AND `Name` IN ('view entry', 'create entry', 'edit own entry', 'delete own entry');

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 7, `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_ward_councilor';

INSERT IGNORE INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 8, `ID` FROM `sys_acl_actions` WHERE `Module` = 'sa_ward_councilor';

-- Custom levels (Leadership, Councillor) — grant via Studio > Permissions after install
-- Their IDs vary per installation
```

## uninstall.sql — MULTI DELETE Pattern (MariaDB Safe)

```sql
-- CORRECT (MariaDB compatible):
DELETE `sys_localization_strings` FROM `sys_localization_strings`
JOIN `sys_localization_keys` k ON `sys_localization_strings`.`IDKey` = k.`ID`
WHERE k.`Key` LIKE '_sa_ward_councilor%';

-- WRONG (fails on MariaDB — alias in DELETE target):
-- DELETE s FROM `sys_localization_strings` s JOIN ...
```

---

## Theme Builder v2.2 — Key Points

Override map:
- data/template/system/ → overrides /template/
- data/template/studio/ → overrides /studio/template/
- data/template/{vendor}/{module}/ → overrides module templates

Copy ONLY files you change. CSS/LESS = deltas only. Never fork full tree.
Class chain: BxBaseModTemplateConfig | BxBaseModTemplateDb | BxBaseModTemplateModule | BxBaseModGeneralTemplate
config.php: BX_DOL_MODULE_TYPE_TEMPLATE | sys_options_types.group = 'templates'
Cache: Studio → Settings → System → disable page/block/HTML/CSS cache during dev.

---

## Production Deployment Checklist

Before rsync to prod, verify locally:
```
[ ] All 500 errors resolved and tested as Councillor/Leadership/Standard user
[ ] install.sql ACL matrix uses INSERT IGNORE + fixed level IDs only
[ ] uninstall.sql MULTI DELETE uses table name not alias
[ ] upgrade.sql has ALTER TABLE for all new columns
[ ] All DB table columns match what PHP code actually INSERTs
[ ] No BxDolModuleQuery::isModuleInstalled() calls anywhere
[ ] Dynamic level resolver uses Key string not IDKey integer
[ ] display_errors removed from index.php
[ ] git commit with descriptive message
[ ] git push origin main
```

After rsync to prod:
```
[ ] Space page blocks manually inserted if not in install.sql
[ ] Lang cache cleared: rm /var/www/neighbors/cache/lang-en.php
[ ] curl -s https://neighborsocial.org > /dev/null (rebuild lang cache)
[ ] Custom ACL levels granted via Studio > Permissions
[ ] Test all pages as: admin | Councillor | Leadership | Standard member
[ ] Check sys_modules.enabled = 1
[ ] Check logs/db.err.log for any new errors
```

---

## Execution Modes

| Mode | Condition | Action |
|------|-----------|--------|
| A | MCP fully callable | Full auto |
| B | MCP read-only | Human deploys Stage 5 |
| C | No MCP | Human provides DESCRIBE; AI produces ZIP |
| D | MCP claimed, one call fails | Declare immediately → ZIP |

---

## Common Failures

| Symptom | Cause | Fix |
|---------|-------|-----|
| Menu item missing | Wrong sys_menu_items column count | Stage 1+6 |
| Block shows nothing | Wrong serialized byte length | Stage 4 |
| Install SQL error | v15 columns in v14 | Stage 1+6 |
| PHP fatal on load | Multiple classes in one file | Stage 3+6 |
| addCss() fatal | Wrong signature | Stage 2.3+6 |
| Space dropdown empty | bx_spaces_fans wrong table | Stage 3.4 |
| ACL not in Studio | sys_acl_actions INSERT missing | Stage 3+6 |
| Timeline in DB, not feed | serviceGetTimelinePost() wrong keys | Stage 3.11+6 |
| No notification settings | serviceGetNotificationsData() missing 'settings' key | Stage 3.12+6 |
| Alert fires, no timeline | alert_unit ≠ sys_objects_content_info name | Stage 3.11 |
| Handler not registered | Used add_handlers_for_module not add_handlers | Stage 3.11 |
| 500 on all pages for Councillor | BxDolModuleQuery::isModuleInstalled() called | RULE 10 — remove/replace |
| ACL level lookup returns wrong IDs | $aStrings keyed by IDKey integer | RULE 11 — key by Key string |
| install.sql ACL matrix fails MariaDB | Comma join FROM t1, t2 WHERE | RULE 12 — use fixed IDs 3,7,8 |
| uninstall.sql MULTI DELETE fails | DELETE alias FROM table alias JOIN | Use table name not alias |
| 500 on view-request page | Notes table missing author_name/actor_role columns | ALTER TABLE + upgrade.sql |
| Manage tab 500 for standard user | visible_for_levels = 2147483647 on manage menu item | Set correct bit field value |
| Space blocks show lang keys in Studio | Lang cache not rebuilt after string insert | rm lang-en.php + curl site |
| Space blocks missing after reinstall | Blocks not in install.sql — added manually to DB | Add INSERT to install.sql |

---

## UNA Wiki Reference

Official wiki cloned at: `/var/www/una-wiki/` on production VPS
Update: `cd /var/www/una-wiki && git pull`

**Before writing any code — check the relevant wiki file:**

| Question | Wiki File | Size |
|----------|-----------|------|
| Mobile menus, menu rendering, visible_for_levels | Menus.md | 3KB |
| ACL, permissions, levels | Permissions-Builder.md | 1KB |
| Forms, input types, form builder | Dev-Forms.md | 40KB |
| Page blocks, page system | Dev-Pages.md | 6KB |
| Grid displays | Dev-Grids.md | 21KB |
| API methods, service calls, OAuth | Dev-API.md | 36KB |
| Coding standards, class names, CSS | Code-Convention.md | 14KB |
| Common mistakes to avoid | Common-Mistakes.md | 3KB |
| Timeline integration | Timeline.md | 7KB |
| Language keys, localization | Language-Apps.md | 12KB |
| Template macros | Macros.md | 79KB |
| Storage, file uploads | Dev-Storage.md | 7KB |
| User profiles | User-Profiles.md | 4KB |
| Directory structure | Directories-structure.md | 4KB |

**How to read a wiki file during a Hermes session:**
```bash
# SSH into VPS first, then:
cat /var/www/una-wiki/Menus.md
cat /var/www/una-wiki/Common-Mistakes.md
cat /var/www/una-wiki/Dev-Forms.md | head -100
```

**Never guess a UNA method name, table reference, or pattern.**
**When in doubt — read the wiki before writing any code.**

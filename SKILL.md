---
name: una-module-builder
description: >
  Expert execution skill for building custom UNA CMS 14.0 modules from scratch. Use this skill
  whenever the user wants to create, scaffold, generate, or extend a UNA CMS 14 module — including
  content modules (listings, posts, items), tool modules, service modules, and integration modules.
  Trigger on any mention of: UNA module, boonex module, install.sql, sys_menu_items, BxDolModule,
  UNA CMS development, modforge, Ward Councilor, Rentals, or building a custom UNA feature.
  This skill enforces the 7-stage gated execution protocol, Artificer-first CSS, serialization
  mathematics, sys_objects_privacy integration, and ACL action registration. Do NOT use this skill
  for template theme builds — use una-theme-builder for that.
---

# UNA CMS 14.0 — Custom Module Builder

You are executing the **UNA CMS 14.0 Module Development Execution Protocol v2.3**.
This is a **strict gated contract** — each stage is a checkpoint. Do not skip stages or
proceed without satisfying the gate checklist.

---

## Critical Rules — Non-Negotiable

```
RULE 1: Never trust memory for structural facts.
        Column counts, serialization lengths, INSERT patterns —
        always read from the live instance or reference module first.

RULE 2: Each stage is a gate. Do not proceed until the current stage
        produces its required output.

RULE 3: The live instance overrides the guide.
        DESCRIBE output from the live DB is the specification.

RULE 4: One class per file. Always.

RULE 5: Serialization is mathematics.
        Every s:N:"string" — N must equal the exact byte length of string.
        Compute it. Never copy-paste from examples.

RULE 6: PHP files must start with the security guard.
        First line after <?php must be: defined('BX_DOL') or die('hack attempt');

RULE 7: Artificer is the CSS baseline. Always.
        Write CSS for Artificer first. If client uses another theme,
        inject via BxDolTemplate::addHtmlHeader() with !important on every rule.

RULE 8: sys_objects_privacy is mandatory for all content modules.
        Every content module MUST include space_id column (Option A minimum).

RULE 9: ACL actions are mandatory for all content modules.
        Every content module MUST register its actions in sys_acl_actions
        and gate every operation with BxDolAcl::isMemberLevelInSet().
        Never rely on login-check alone as an access control mechanism.

RULE 10: Timeline integration is mandatory for all content modules.
         Every content module MUST register in sys_objects_content_info
         AND fire BxDolAlerts on add/edit/delete. Without both, content
         creation is invisible to the platform — no feed event fires,
         no Space/Group timeline entry, no notifications.
         owner_id in the alert MUST be set to space_id (not author) when
         content belongs to a Space or Group.
```

---

## Pre-Build Planning — Permissions Architecture

**Run this before Stage 0.** These questions determine the SQL schema, PHP class structure,
and install.sql content. Answering them wrong late in the build causes major rework.

UNA has three overlapping access systems. Your module must declare which it uses for each
before code generation begins.

### System 1 — ACL (Membership Level Gates)

ACL governs **what actions a membership tier can perform** — site-wide, coarse-grained.
Lives in `sys_acl_*` tables. Applied via `BxDolAcl::isMemberLevelInSet()`.

**Standard action set for a content module — decide which apply:**

| Action | Description | Typically Allowed By |
|--------|-------------|----------------------|
| `view entry` | Read a single content item | Standard+ |
| `create entry` | Submit new content | Standard+ |
| `edit own entry` | Modify own content | Standard+ |
| `edit any entry` | Modify anyone's content | Moderator, Admin |
| `delete own entry` | Remove own content | Standard+ |
| `delete any entry` | Remove anyone's content | Moderator, Admin |
| `approve entry` | Publish pending content | Moderator, Admin |
| `feature entry` | Pin/feature content | Admin |

**Planning checklist for ACL:**
```
[ ] Full action list defined (at minimum: create, edit own, delete own)
[ ] Default allowed membership level recorded for each action
[ ] Any countable actions identified (e.g. "post up to 5 per month")?
[ ] Moderation flow decided: auto-publish or pending approval?
```

### System 2 — Privacy (Per-Content Visibility)

Privacy governs **who can see a specific piece of content** — based on relationship to the
author or container, not membership level. Implemented via `sys_objects_privacy` and
`BxDolPrivacy`.

**Privacy group integers (built-in UNA defaults):**

| Integer | Meaning |
|---------|---------|
| 1 | Everyone (public) |
| 2 | Logged-in members only |
| 3 | Friends of the author |
| 4 | Author only (private) |
| 5+ | Custom groups (Space members, Group members, etc.) |

**Two implementation options:**

- **Option A** — `space_id` column only. You filter content in your DB queries manually.
  Minimum required by RULE 8. No native "Visible to…" UI.

- **Option B** — Full `sys_objects_privacy` registration. Gives native "Visible to…"
  dropdown on forms, automatic filtering, Space/Group timeline integration.
  Requires per-action registration (view, comment, react, etc.) in `sys_objects_privacy`.

**Planning checklist for Privacy:**
```
[ ] Option A or Option B decided?
[ ] If Option B: which actions need privacy objects? (view, comment, react, etc.)
[ ] Default privacy group integer recorded for each action
[ ] Does content carry its own privacy setting per item, or is it module-wide?
```

### System 3 — Content Context (Space / Group Scoping)

Context governs **where content lives** — inside a Space, a Group, or site-wide.
Both resolve to `sys_profiles.id`. Your `space_id` column stores that profile ID.

| Context Type | Profile Type | Join Table |
|-------------|-------------|------------|
| Spaces | `bx_spaces` | `bx_spaces_data` |
| Groups | `bx_groups` | `bx_groups_data` |
| Site-wide | n/a | n/a (space_id = 0) |

**Planning checklist for Context:**
```
[ ] standalone / space-aware / group-aware / either decided?
[ ] If space/group-aware: selector required or optional on create form?
[ ] Can members post to any Space/Group, or only ones they belong to?
[ ] Should content posted inside a Space be visible only to Space members?
     (This ties System 2 and System 3 together — requires Option B)
```

### System 4 — Timeline / Activity Feed Integration (RULE 10)

Timeline integration is **not automatic**. Your module must do two things explicitly:

**Part A — Register your content type** in `sys_objects_content_info`. This is the
directory entry that tells UNA's timeline module what your content is, how to get its
title and URL, and how to render it inside a feed card. Without this row the timeline
module cannot display your content even if it receives the alert.

**Part B — Fire `BxDolAlerts`** in your service methods after a successful create, edit,
or delete. The timeline module (`bx_timeline`) has listeners that catch these alerts and
create feed events. No alert = no feed event.

**The `owner_id` rule** — this determines *whose* timeline the event appears in:

| Content state | owner_id to pass | Result |
|--------------|-----------------|--------|
| Belongs to a Space (`space_id > 0`) | `$aEntry['space_id']` | Appears in that Space's timeline only |
| Belongs to a Group (`space_id > 0`, context_type = bx_groups) | `$aEntry['space_id']` | Appears in that Group's timeline only |
| Site-wide, public (`space_id = 0`, privacy = 1) | `$iAuthorProfileId` | Appears in author's feed; followers see it |
| Site-wide, private (`space_id = 0`, privacy = 4) | `$iAuthorProfileId` | Alert fires but timeline suppresses for non-permitted viewers |

**Planning checklist for Timeline:**
```
[ ] sys_objects_content_info row planned (alert_unit, alert_action_add/update/delete)
[ ] serviceGetContentInfoArray() method planned (returns title, url, image, author, added)
[ ] BxDolAlerts fire points identified: after create, after edit, after delete
[ ] owner_id strategy decided per content context (space_id vs author profile ID)
[ ] Should edits and deletes also fire timeline events, or create only?
```

### Layered Permission Check Pattern

All three systems should be chained in a single method in your Module class.
Generate this pattern in Stage 3 for every content gate (view, edit, delete):

```php
public function checkAllowView($iEntryId)
{
    // 1. ACL — membership level gate (site-wide)
    if (!BxDolAcl::getInstance()->isMemberLevelInSet('view entry'))
        return false;

    // 2. Privacy — relationship gate (per-item)
    $oPrivacy = BxDolPrivacy::getObject('sa_yourmodule_entry_view');
    if ($oPrivacy && !$oPrivacy->check($iEntryId))
        return false;

    // 3. Context — space/group membership gate (container)
    $aEntry = $this->_oDb->getEntry($iEntryId);
    if (!empty($aEntry['space_id'])) {
        $oSpace = BxDolProfile::getInstance($aEntry['space_id']);
        if (!$oSpace || !$oSpace->isMember())
            return false;
    }

    return true;
}
```

Repeat for `checkAllowEdit()` and `checkAllowDelete()`, varying the ACL action name
and privacy object name per gate.

---

## Stage 0 — Platform & Tool Inventory

Before generating anything, establish what's available.

### 0.1 — Tool Availability Check

| Tool | Check |
|------|-------|
| MCP live connection (`modforge-mcp`) | Can you call `list_modules`? |
| Direct DB access | Can you run `DESCRIBE sys_menu_items`? |
| File read/write to modules dir | Can you read/write `/var/www/una/modules/`? |

**Execution Modes:**
- **Mode A** — Full Auto: MCP available and callable → all stages execute automatically
- **Mode B** — Semi-Auto: Read-only MCP or file access → human deploys Stage 5 manually
- **Mode C** — Manual Assist: No MCP → human provides DESCRIBE outputs; AI generates ZIP bundle
- **Mode D** — MCP reported active but not callable → declare after ONE failed attempt, produce ZIP bundle

**MCP Claim Rule:** If human says "MCP is connected" but you can't verify in one attempt → declare Mode D immediately.

### 0.2 — Declare Build Specification

Confirm these before proceeding:

```
Module name:      {vendor}_{module}      e.g. sa_rentals
Vendor:           {vendor}               e.g. sa
Module slug:      {module}               e.g. rentals
Class prefix:     {ClassPrefix}          e.g. SaRentals
DB prefix:        {vendor}_{module}      e.g. sa_rentals
URI:              {uri}                  e.g. rentals
Module type:      content / tool / service / integration
UNA version:      14.0.x
Reference module: {ref_module}           e.g. boonex/events
Content context:  standalone / space-aware / group-aware

ACL actions:      {list from Pre-Build Planning}
Privacy option:   A (space_id only) / B (sys_objects_privacy)
Privacy default:  {integer per action, e.g. view=1, create=2}
Moderation flow:  auto-publish / pending approval
Timeline:         enabled / disabled
Timeline owner:   space_id / author profile ID / conditional
```

### 0.3 — Pattern Lookup (Optional but Recommended)

Before Stage 1, check if a relevant pattern exists at:
**https://patterns.unaaistudio.com**

Search for patterns matching the module type or domain (e.g. "rentals", "listings", "booking").
If a matching pattern is found, extract its structural approach and note it.
If no match is found, proceed with the reference module from 0.2.

**Gate 0:** Build spec confirmed + execution mode declared + permissions architecture decided → proceed to Stage 1.

---

## Stage 1 — Live Environment Verification

No generation happens here. Establish ground truth from the live instance.

### Batch these commands — request them all at once:

```sql
-- Run ALL of these and paste the full output:
DESCRIBE sys_menu_items;
DESCRIBE sys_pages_blocks;
DESCRIBE sys_objects_page;
SELECT id FROM sys_std_pages WHERE name = 'home' LIMIT 1;
SELECT value FROM sys_options WHERE name = 'tmpl';
DESCRIBE sys_objects_privacy;
SELECT * FROM sys_objects_privacy LIMIT 3;
DESCRIBE sys_acl_actions;
SELECT * FROM sys_acl_levels ORDER BY id;
SELECT * FROM sys_acl_actions WHERE Module = 'bx_events' LIMIT 5;
DESCRIBE sys_objects_content_info;
SELECT * FROM sys_objects_content_info WHERE name = 'bx_events' LIMIT 1;
```

**Record:**
- `sys_menu_items column count = __` (this governs all INSERT statements)
- `active_theme = __` (if NOT 'artificer' → flag and apply RULE 7)
- `sys_acl_levels IDs = __` (needed for sys_acl_matrix INSERT patterns)
- `sys_acl_actions column structure = __` (governs ACL INSERT statements)
- `sys_objects_content_info column list = __` (governs timeline registration INSERT)

**Forbidden v15-only columns** — if any appear in DESCRIBE output, flag immediately:
`title_attr`, `info`, `area_label`, `persistent`

**Timeout Rule:** If human doesn't respond within 2 exchanges, proceed with guide-based values
and flag every assumption in the Stage 7 Handoff Report.

### Gate 1 Checklist:
```
[ ] sys_menu_items column count recorded
[ ] sys_pages_blocks structure confirmed
[ ] sys_objects_page structure confirmed
[ ] sys_std_pages home page id recorded
[ ] Active theme confirmed
[ ] sys_objects_privacy confirmed
[ ] sys_acl_actions column structure confirmed
[ ] sys_acl_levels IDs recorded
[ ] sys_objects_content_info column list recorded
[ ] Execution mode confirmed
```

---

## Stage 2 — Reference Module Extraction

Read a working module from the live VPS. Never generate SQL patterns from memory.

### 2.1 — Select Reference

| Module Type | Reference |
|-------------|-----------|
| Content (list/view/create) | boonex/events or boonex/groups |
| Commerce / listings | boonex/market |
| Tool / utility | boonex/tasks |
| Notifications / service | boonex/notifications |
| Simple / minimal | boonex/timeline |

### 2.2 — Extract These Exact Patterns

Using MCP `read_file` / `search_in_files`, or by human-provided file content:

- Exact `INSERT INTO sys_menu_items` column list and value count
- Exact `sys_pages_blocks` service content serialization format
- Exact `sys_objects_page` column list
- Exact `INSERT INTO sys_acl_actions` column list and value count from reference module
- Exact `INSERT INTO sys_objects_privacy` pattern if reference module uses Option B
- Exact `INSERT INTO sys_objects_content_info` column list from reference module
- How and where the reference module fires `BxDolAlerts` (search for `new BxDolAlerts`)
- How the reference module calls `addCss()` (critical — see 2.3)

### 2.3 — Verify addCss() Signature

**This caused a fatal error in Ward Councilor module. Never skip this.**

The parent signature in BxDolModuleTemplate is:
```php
public function addCss($mixedFiles, $bDynamic = false)
```

If your Template class overrides addCss(), it MUST match. The correct pattern:
```php
public function addCss($mixedFiles = 'main.css', $bDynamic = false)
{
    return parent::addCss($mixedFiles, $bDynamic);
}
```

A parameterless `addCss()` causes a fatal error on module load.

### Gate 2 Checklist:
```
[ ] Reference module selected and documented
[ ] sys_menu_items INSERT pattern extracted
[ ] sys_pages_blocks serialization pattern extracted
[ ] sys_objects_page INSERT pattern extracted
[ ] sys_acl_actions INSERT pattern extracted from reference module
[ ] sys_objects_privacy INSERT pattern extracted (if Option B)
[ ] sys_objects_content_info INSERT pattern extracted from reference module
[ ] BxDolAlerts firing pattern extracted from reference module
[ ] config.php structure confirmed
[ ] addCss() signature verified
```

---

## Stage 3 — Constrained File Generation

Generate all build files using Stage 1 and Stage 2 as hard constraints.

### 3.1 — File Generation Order

```
1.  install/config.php
2.  install/installer.php
3.  install/langs/en.xml
4.  classes/{Prefix}Config.php
5.  classes/{Prefix}Db.php
6.  classes/{Prefix}Module.php          ← includes checkAllow*() methods
7.  classes/{Prefix}Template.php
8.  template/css/main.css              ← Artificer-first
9.  template/page_main.html            (if content module)
10. request.php
11. install/sql/install.sql            ← generated LAST
12. install/sql/uninstall.sql
13. install/sql/enable.sql
14. install/sql/disable.sql
15. install/sql/upgrade.sql            ← empty stub, must exist
```

### 3.2 — PHP File Rules

Every PHP file must:
```php
<?php defined('BX_DOL') or die('hack attempt');
// ↑ This is line 1, always, no exceptions.

// Use bx_import() — never require() or include()
bx_import('BxDolModuleDb');

// One class per file. File name = class name exactly.
```

### 3.3 — Required Class Inheritance

| File | Must extend |
|------|-------------|
| {Prefix}Config.php | BxDolModuleConfig |
| {Prefix}Db.php | BxDolModuleDb |
| {Prefix}Module.php | BxDolModule |
| {Prefix}Template.php | BxDolModuleTemplate |
| installer.php | BxDolStudioInstaller |

### 3.4 — Database Schema: Space/Group Context (RULE 8)

Every content module table MUST include:
```sql
`space_id` int(11) NOT NULL DEFAULT 0 COMMENT 'UNA Space/Group profile ID — sys_profiles.id. 0 = site-wide.',
KEY `space_id` (`space_id`)
```

Add this comment in the module class:
```php
// space_id maps to sys_profiles.id
// type 'bx_spaces' = Space container
// type 'bx_groups' = Group container
// 0 = no container (site-wide content)
// Option B upgrade: register with sys_objects_privacy for native "Visible to..." dropdown
```

Space selector query (for forms, when context = space-aware):
```sql
SELECT p.id, sp.title
FROM sys_profiles p
JOIN bx_spaces_data sp ON p.content_id = sp.id
WHERE p.type = 'bx_spaces' AND p.status = 'active'
ORDER BY sp.title ASC
```

Group selector query (for forms, when context = group-aware):
```sql
SELECT p.id, gp.title
FROM sys_profiles p
JOIN bx_groups_data gp ON p.content_id = gp.id
WHERE p.type = 'bx_groups' AND p.status = 'active'
ORDER BY gp.title ASC
```

When context is "either" (Space or Group), union both queries or use a single selector
with a type discriminator column in your content table:
```sql
`context_type` enum('','bx_spaces','bx_groups') NOT NULL DEFAULT '' COMMENT 'Profile type of container',
```

### 3.5 — SQL Generation Rules

- **sys_menu_items:** Use exact column count from Stage 1. Never add v15 columns.
- **sys_objects_page:** Set `cover = 0` unless cover image is explicitly required.
  (`cover = 1` with `cover_image = 0` renders an empty cover container — layout bug.)
- **sys_pages_blocks content:** Leave as `'__SERIALIZED_CONTENT_PLACEHOLDER__'` — computed in Stage 4.
- **All tables:** `ENGINE=InnoDB`, `charset=utf8mb4`

### 3.6 — CSS Strategy (RULE 7)

**Artificer-first. Always.**

```css
/* Use Artificer CSS variables */
.{vendor}-{module}-wrapper {
    background: var(--bx-def-color-bg-block);
    color: var(--bx-def-font-color);
    border-color: var(--bx-def-border-color);
}
/* Scope ALL selectors to module prefix: .{vi}-{mi}- */
/* Where vi = vendor initial, mi = module initial */
/* Examples: .wc- (ward councilor), .rn- (rentals) */
```

If active theme (Stage 1) is NOT Artificer → inject via addHtmlHeader():
```php
static $bCssInjected = false;
if (!$bCssInjected) {
    $sCss = file_get_contents(
        BX_DIRECTORY_PATH_MODULES . '{vendor}/{module}/template/css/main.css'
    );
    BxDolTemplate::getInstance()->addHtmlHeader('<style>' . $sCss . '</style>');
    $bCssInjected = true;
}
```
Every CSS rule must carry `!important` when using this injection approach.

### 3.7 — Service Method Naming

```php
// PHP:  serviceGetItemsBlock()
// SQL:  method = get_items_block
// Rule: camelCase after "service" prefix → snake_case in SQL
```

Record every service method name — Stage 4 uses these exact strings.

### 3.8 — ACL Action Registration (RULE 9)

For every action declared in Pre-Build Planning, add to install.sql:

```sql
-- Register ACL actions for this module
INSERT INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('{vendor}_{module}', 'view entry',       NULL, 'View Entry',       '', 0, ''),
  ('{vendor}_{module}', 'create entry',     NULL, 'Create Entry',     '', 0, ''),
  ('{vendor}_{module}', 'edit own entry',   NULL, 'Edit Own Entry',   '', 0, ''),
  ('{vendor}_{module}', 'edit any entry',   NULL, 'Edit Any Entry',   '', 0, ''),
  ('{vendor}_{module}', 'delete own entry', NULL, 'Delete Own Entry', '', 0, ''),
  ('{vendor}_{module}', 'delete any entry', NULL, 'Delete Any Entry', '', 0, ''),
  ('{vendor}_{module}', 'approve entry',    NULL, 'Approve Entry',    '', 0, ''),
  ('{vendor}_{module}', 'feature entry',    NULL, 'Feature Entry',    '', 0, '');
```

Only include the actions your module actually uses. Trim the list to what was declared
in the Pre-Build Planning step.

Then set default permissions per membership level in `sys_acl_matrix`. Use exact level
IDs from Stage 1:

```sql
-- Allow Standard members to view, create, edit own, delete own
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES
  (3, LAST_INSERT_ID()),   -- Standard: view entry
  ...;
-- Use subquery pattern to look up action IDs by name and module:
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 3, `ID` FROM `sys_acl_actions`
WHERE `Module` = '{vendor}_{module}' AND `Name` IN ('view entry', 'create entry', 'edit own entry', 'delete own entry');
```

In uninstall.sql, always remove ACL entries:
```sql
DELETE FROM `sys_acl_matrix` WHERE `IDAction` IN
  (SELECT `ID` FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}');
DELETE FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}';
```

Gate every operation in your Module class (RULE 9):
```php
if (!BxDolAcl::getInstance()->isMemberLevelInSet('create entry'))
    return MsgBox(_t('_Access Denied'));
```

### 3.9 — Privacy Registration (Option B)

If Privacy Option B was selected in Pre-Build Planning, add to install.sql for each
content action that needs a visibility gate:

```sql
INSERT INTO `sys_objects_privacy`
  (`object`, `module`, `action`, `title`, `default_group`,
   `table`, `table_field_id`, `table_field_author_id`, `extra_field`)
VALUES
  ('{vendor}_{module}_entry_view',    '{vendor}_{module}', 'view',    'View Entry',    1,
   '{vendor}_{module}_entries', 'id', 'author_id', ''),
  ('{vendor}_{module}_entry_comment', '{vendor}_{module}', 'comment', 'Comment Entry', 2,
   '{vendor}_{module}_entries', 'id', 'author_id', '');
```

`default_group` uses the privacy integer from Pre-Build Planning (1=Everyone, 2=Logged-in,
3=Friends, 4=Only Me).

In uninstall.sql:
```sql
DELETE FROM `sys_objects_privacy` WHERE `module` = '{vendor}_{module}';
```

In your Module class, apply the privacy check as the second layer of `checkAllowView()`:
```php
$oPrivacy = BxDolPrivacy::getObject('{vendor}_{module}_entry_view');
if ($oPrivacy && !$oPrivacy->check($iEntryId))
    return false;
```

### 3.10 — Layered Permission Check Methods

Generate these three methods in your `{Prefix}Module.php` for every content module.
Adjust action names to match what was registered in 3.8.

```php
public function checkAllowView($iEntryId)
{
    // Layer 1: ACL — membership level gate
    if (!BxDolAcl::getInstance()->isMemberLevelInSet('view entry'))
        return false;

    // Layer 2: Privacy — per-item relationship gate (Option B only)
    $oPrivacy = BxDolPrivacy::getObject('{vendor}_{module}_entry_view');
    if ($oPrivacy && !$oPrivacy->check($iEntryId))
        return false;

    // Layer 3: Context — Space/Group membership gate
    $aEntry = $this->_oDb->getEntry($iEntryId);
    if (!empty($aEntry['space_id'])) {
        $oSpace = BxDolProfile::getInstance($aEntry['space_id']);
        if (!$oSpace || !$oSpace->isMember())
            return false;
    }

    return true;
}

public function checkAllowEdit($iEntryId)
{
    $aEntry = $this->_oDb->getEntry($iEntryId);
    $iProfileId = bx_get_logged_profile_id();

    // Edit any = moderator/admin
    if (BxDolAcl::getInstance()->isMemberLevelInSet('edit any entry'))
        return true;

    // Edit own = must be author
    if (BxDolAcl::getInstance()->isMemberLevelInSet('edit own entry')
        && (int)$aEntry['author_id'] === (int)$iProfileId)
        return true;

    return false;
}

public function checkAllowDelete($iEntryId)
{
    $aEntry = $this->_oDb->getEntry($iEntryId);
    $iProfileId = bx_get_logged_profile_id();

    if (BxDolAcl::getInstance()->isMemberLevelInSet('delete any entry'))
        return true;

    if (BxDolAcl::getInstance()->isMemberLevelInSet('delete own entry')
        && (int)$aEntry['author_id'] === (int)$iProfileId)
        return true;

    return false;
}
```

### 3.11 — Timeline Integration (RULE 10)

**The Ward Councillor lesson — two required PHP methods, not one.**
UNA's Dev API documents this mandatory pair for every content module:
```
serviceGetTimelineData()    ← configuration half — THE MOST COMMONLY MISSED METHOD
serviceGetTimelinePost()    ← rendering half
```
This mirrors the notifications pair exactly (`serviceGetNotificationsData()` +
`serviceGetNotificationsPost()`). If `serviceGetTimelineData()` is absent, events
are inserted into `bx_timeline_events` with `published = 0` and never appear in
any feed — even when `BxDolAlerts` fires correctly and `bx_timeline_handlers` is
registered. This was the exact failure mode in the Ward Councillor Portal module.

**Do NOT manually register `bx_timeline_handlers` in install.sql.** That table is
populated dynamically by the timeline module when `serviceGetTimelineData()` is
implemented and the enable hook is present. Manual SQL registration without the
service method produces `published = 0` rows that never display.

#### Part A — Register content type in install.sql

Use the exact column list from Stage 1 (`sys_objects_content_info`).

```sql
-- ─── Timeline: Register content info object ─────────────────────────────────
INSERT INTO `sys_objects_content_info`
  (`name`, `title`, `alert_unit`, `alert_action_add`, `alert_action_update`,
   `alert_action_delete`, `class_name`, `class_file`)
VALUES
  ('{vendor}_{module}',
   '_{vendor}_{module}_content_info_title',
   '{vendor}_{module}',
   'added',
   'edited',
   'deleted',
   '',
   '');
-- ────────────────────────────────────────────────────────────────────────────
```

In uninstall.sql:
```sql
DELETE FROM `sys_objects_content_info` WHERE `name` = '{vendor}_{module}';
```

#### Part B — serviceGetTimelineData() in Module class ← THE MISSING METHOD

This is the configuration method the timeline module calls to understand how to
process events from your module and set `published` correctly.

```php
public function serviceGetTimelineData()
{
    $sModule = $this->_aModule['name'];

    return [
        'handlers' => [
            [
                'group'         => $sModule . '_object_add',
                'type'          => 'insert',
                'alert_unit'    => $sModule,
                'alert_action'  => 'added',
                'module_name'   => $sModule,
                'module_method' => 'get_timeline_post',  // snake_case of serviceGetTimelinePost
            ],
            [
                'group'         => $sModule . '_object_update',
                'type'          => 'update',
                'alert_unit'    => $sModule,
                'alert_action'  => 'edited',
                'module_name'   => $sModule,
                'module_method' => 'get_timeline_post',
            ],
            [
                'group'         => $sModule . '_object_delete',
                'type'          => 'delete',
                'alert_unit'    => $sModule,
                'alert_action'  => 'deleted',
                'module_name'   => $sModule,
                'module_method' => 'get_timeline_post',
            ],
        ],
        'settings' => [
            'title' => '_t_' . $sModule . '_cpt_timeline',
        ],
    ];
}
```

`module_method` is the service method name in snake_case without the `service` prefix:
`serviceGetTimelinePost` → `get_timeline_post`. Never use camelCase here.

#### Part C — serviceGetTimelinePost() in Module class

The timeline module calls this to render content in feed cards. The returned array
MUST include `'published' => (int)$aEntry['added']` — this is what the timeline
pipeline reads to SET the `published` field in `bx_timeline_events`. If this key
is missing or 0, events are stored but never displayed.

```php
public function serviceGetTimelinePost($aEvent)
{
    $iEntryId = (int)($aEvent['object_id'] ?? 0);
    $aEntry   = $this->_oDb->getEntry($iEntryId);
    if (!$aEntry)
        return false;

    return [
        'id'          => (int)($aEvent['id'] ?? 0),
        'object_id'   => $iEntryId,
        'owner_id'    => (int)($aEvent['owner_id'] ?? 0),
        'type'        => $aEvent['type'] ?? 'system',
        'action'      => $aEvent['action'] ?? '',
        'title'       => bx_process_output($aEntry['title']),
        'description' => bx_process_output($aEntry['description'] ?? ''),
        'link'        => BX_DOL_URL_ROOT . 'page/index.php?i={uri}&id=' . $iEntryId,
        'image'       => '',   // populate with thumbnail URL if module has media
        'video'       => '',
        'author'      => (int)$aEntry['author_id'],
        'added'       => (int)$aEntry['added'],
        'published'   => (int)$aEntry['added'],  // ← REQUIRED: sets published in bx_timeline_events
        'views'       => 0,
        'votes'       => 0,
        'comments'    => 0,
        'privacy'     => (int)($aEntry['allow_view_to'] ?? 1),
        'content'     => '',   // rendered HTML card body — leave empty for plain text events
    ];
}
```

#### Part D — serviceGetContentInfoArray() in Module class

Called by the platform to render content previews in search results and widgets.

```php
public function serviceGetContentInfoArray($iEntryId)
{
    $aEntry = $this->_oDb->getEntry((int)$iEntryId);
    if (!$aEntry)
        return false;

    return [
        'id'          => (int)$aEntry['id'],
        'title'       => bx_process_output($aEntry['title']),
        'description' => bx_process_output($aEntry['description'] ?? ''),
        'url'         => BX_DOL_URL_ROOT . 'page/index.php?i={uri}&id=' . $iEntryId,
        'image'       => '',
        'author'      => (int)$aEntry['author_id'],
        'added'       => (int)$aEntry['added'],
        'privacy'     => (int)($aEntry['allow_view_to'] ?? 0),
    ];
}
```

#### Part E — Enable / Disable hooks in installer.php ← REQUIRED

Handler registration must happen at **module enable time**, not just install time.
This is the call that actually invokes `serviceGetTimelineData()` and populates
`bx_timeline_handlers`. Without this, `published` stays 0.

This mirrors how notifications works: `serviceGetNotificationsData()` is called by
the relations mechanism when the module is enabled.

**⚠️ CRITICAL — VERIFIED WORKING PATTERN (Ward Councillor, 2026-05):**

`BxDolStudioInstaller` does NOT expose `onEnable()` as a public override point.
Using `public function onEnable($aParams)` will silently never be called.

The correct hooks are `_onEnableAfter()` and `_onDisableBefore()`.
Use `_updateRelations()` which reads `sys_modules_relations` and calls whatever
`bx_timeline` and `bx_notifications` registered as their `on_enable` handler.
This is the same mechanism UNA uses internally for all boonex modules.

```php
// In install/installer.php — CORRECT pattern (copy exactly):

protected function _onEnableAfter()
{
    $mixedResult = parent::_onEnableAfter();
    $this->_updateRelations('enable');
    return $mixedResult;
}

protected function _onDisableBefore()
{
    $this->_updateRelations('disable');
    return parent::_onDisableBefore();
}

protected function _updateRelations($sOperation)
{
    $aConfig = $this->_aConfig;
    if(empty($aConfig['relations']) || !is_array($aConfig['relations']))
        return;

    foreach($aConfig['relations'] as $sModule) {
        if(!$this->oDb->isModuleByName($sModule))
            continue;

        $aRelation = $this->oDb->getRelationsBy(array('type' => 'module', 'value' => $sModule));
        if(empty($aRelation) || empty($aRelation['on_' . $sOperation]))
            continue;

        if(!BxDolRequest::serviceExists($aRelation['module'], $aRelation['on_' . $sOperation]))
            continue;

        bx_srv_ii($aRelation['module'], $aRelation['on_' . $sOperation], array($aConfig['home_uri']));
    }
}
```

Also ensure `config.php` has the `relations` array:
```php
'relations' => array(
    'bx_timeline',
    'bx_notifications',
),
```

#### Part F — BxDolAlerts in service methods

Add alert firing immediately after a **confirmed successful** DB operation.

**On create:**
```php
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'added', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

**On edit:**
```php
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'edited', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

**On delete — fetch entry BEFORE deleting:**
```php
$aEntry = $this->_oDb->getEntry($iEntryId);  // get space_id while row still exists
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
// ... run DB delete ...
$oAlert = new BxDolAlerts('{vendor}_{module}', 'deleted', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

#### Timeline visibility rules (summary)

| Content | owner_id to pass | Visible in |
|---------|-----------------|-----------|
| Space content (space_id > 0) | space_id | That Space's feed only |
| Group content (space_id > 0, bx_groups) | space_id | That Group's feed only |
| Public site-wide (privacy = 1) | author profile ID | Global feed for all |
| Friends-only (privacy = 3) | author profile ID | Author's friends' feeds |
| Private (privacy = 4) | author profile ID | Alert fires but display is suppressed |

### Gate 3 Checklist:
```
[ ] All 15 files generated
[ ] Every PHP file has security guard on line 1
[ ] Every PHP file uses bx_import(), not require/include
[ ] Every PHP file has exactly one class
[ ] install/config.php has compatible_with: ['14.0.x']
[ ] install.sql uses correct column count for sys_menu_items
[ ] install.sql has cover=0 in sys_objects_page
[ ] All service method names recorded for Stage 4
[ ] sys_pages_blocks content fields contain placeholder
[ ] space_id column present in all content tables
[ ] context_type column present if module is space-or-group-aware
[ ] sys_acl_actions INSERT present for all declared actions (RULE 9)
[ ] sys_acl_matrix INSERT present with correct level IDs from Stage 1
[ ] sys_objects_privacy INSERT present (if Option B)
[ ] checkAllowView(), checkAllowEdit(), checkAllowDelete() generated in Module class
[ ] sys_objects_content_info INSERT present in install.sql (RULE 10)
[ ] sys_objects_content_info DELETE present in uninstall.sql
[ ] bx_timeline_handlers NOT manually inserted in install.sql (populated by enable hook)
[ ] serviceGetTimelineData() present in Module class with handlers array
[ ] serviceGetTimelinePost() present — returns array including 'published' => (int)$aEntry['added']
[ ] serviceGetContentInfoArray() present in Module class
[ ] installer.php has _onEnableAfter() calling _updateRelations('enable')
[ ] installer.php has _onDisableBefore() calling _updateRelations('disable')
[ ] installer.php has _updateRelations() method (copy from Ward Councillor pattern)
[ ] config.php has 'relations' => array('bx_timeline', 'bx_notifications')
[ ] BxDolAlerts fired in service create method with correct owner_id
[ ] BxDolAlerts fired in service edit method with correct owner_id
[ ] BxDolAlerts fired in service delete method — after fetching entry, before or after DB delete
[ ] addCss() signature in Template class matches BxDolModuleTemplate
[ ] CSS written Artificer-first with injection fallback if needed
```

---

## Stage 4 — PHP Serialization Audit

**The single most common failure point. Every serialized string must be computed mathematically.**

### 4.1 — The Formula

```
a:2:{s:6:"module";s:N:"MODULE_NAME";s:6:"method";s:M:"METHOD_NAME";}
```

N = exact byte count of MODULE_NAME (count each character)
M = exact byte count of METHOD_NAME (count each character)

### 4.2 — Computation Table

For every service block, fill this in:

| Block | Module Name | N (count) | Method Name | M (count) | Verified |
|-------|-------------|-----------|-------------|-----------|----------|
| ...   | ...         | ?         | ...         | ?         | ✓/✗     |

**Verify by counting each character manually. Do not estimate.**

Example: `sa_rentals` = s-a-_-r-e-n-t-a-l-s = 10 characters → `s:10:"sa_rentals"`

### 4.3 — Replace All Placeholders

Replace every `__SERIALIZED_CONTENT_PLACEHOLDER__` with the verified string.

### Gate 4 Checklist:
```
[ ] Computation table completed for every service block
[ ] Every s:N:"value" token verified by character count
[ ] All placeholders replaced in install.sql
[ ] No serialized string copied from memory without re-verification
```

---

## Stage 5 — Write and Read-Back Verify

### 5.1 — Write Order

```
1. classes/ files
2. install/config.php + installer.php
3. install/langs/en.xml
4. template/ files (CSS first)
5. request.php
6. install/sql/install.sql   ← last
7. install/sql/uninstall.sql
8. install/sql/enable.sql + disable.sql + upgrade.sql
```

### 5.2 — Read-Back After Every Write

After writing each file:
- Read back immediately with `read_file` (MCP) or manual verification
- Confirm first line = security guard (PHP files)
- Confirm no placeholder strings remain (install.sql)
- Confirm file is non-zero

**A write is not confirmed until the content is read back.**

### 5.3 — Patch, Don't Rewrite

For small fixes: use `patch_file` (MCP) or surgical string replacement.
Never rewrite an entire file to fix one line.

### 5.4 — Mode C/D Fallback

When MCP is not available: produce a ZIP bundle with `deploy.sh`.
This is mandatory for Mode C and D — not optional.

### Gate 5 Checklist:
```
[ ] All files written and read-back confirmed
[ ] PHP files have security guard on line 1
[ ] install.sql has no placeholder strings
[ ] File permissions: 755 dirs, 644 files, www-data owner
```

---

## Stage 6 — Structural Validation

Run `validate_module` (MCP) if available, then manually verify:

### SQL Validation:
```
[ ] No v15 columns: title_attr, info, area_label, persistent in sys_menu_items
[ ] No placeholder strings remaining
[ ] All serialized strings verified (Stage 4)
[ ] cover=0 in sys_objects_page
[ ] ENGINE=InnoDB on all CREATE TABLE
[ ] charset=utf8mb4 on all CREATE TABLE
[ ] space_id column present in all content tables
[ ] sys_acl_actions INSERT present for all declared module actions
[ ] sys_acl_matrix INSERT uses level IDs verified in Stage 1 (not assumed)
[ ] sys_objects_privacy INSERT present (if Option B selected)
[ ] uninstall.sql reverses sys_acl_actions, sys_acl_matrix, sys_objects_privacy
[ ] uninstall.sql reverses everything else
```

### PHP Validation:
```
[ ] checkAllowView() present in Module class
[ ] checkAllowEdit() present in Module class
[ ] checkAllowDelete() present in Module class
[ ] Every service method that mutates data calls a checkAllow*() first
[ ] BxDolAcl::getInstance()->isMemberLevelInSet() used — not a raw login check
[ ] serviceGetTimelineData() present in Module class
[ ] serviceGetTimelinePost() present — includes 'published' key with non-zero timestamp
[ ] serviceGetContentInfoArray() present in Module class
[ ] BxDolAlerts fired after successful create — not before, not on failure
[ ] BxDolAlerts fired after successful edit
[ ] BxDolAlerts fired on delete — entry data fetched BEFORE DB delete for owner_id
[ ] owner_id in each alert is space_id when space_id > 0, author profile ID otherwise
[ ] alert_unit string in BxDolAlerts matches name in sys_objects_content_info exactly
[ ] installer.php has _onEnableAfter() + _updateRelations() — NOT onEnable()
[ ] installer.php has _onDisableBefore() + _updateRelations()
```

### Timeline SQL Validation:
```
[ ] sys_objects_content_info INSERT present in install.sql
[ ] alert_unit in sys_objects_content_info matches BxDolAlerts first argument exactly
[ ] sys_objects_content_info DELETE present in uninstall.sql
[ ] bx_timeline_handlers NOT present in install.sql (this is a warning — it must NOT be there)
```

### CSS Validation:
```
[ ] main.css written Artificer-first
[ ] If non-Artificer active: addHtmlHeader() injection in place with !important
[ ] No theme-specific class names hardcoded (.bx-lucid, .bx-protean)
[ ] Module CSS scoped to module prefix class
```

**Gate 6:** All checked → proceed to Stage 7.

---

## Stage 7 — Handoff Report

Produce this report at the end of every build:

```
=== ModForge Handoff Report ===
Build Type:     custom module
Module:         {vendor}_{module}
Generated:      {date}
UNA Version:    14.0.x
MCP Server:     unified modforge-mcp / none / partial
Active Theme:   {theme from Stage 1}
CSS Strategy:   Artificer-native / addHtmlHeader() injection

PERMISSIONS ARCHITECTURE
------------------------
ACL Actions registered: {list}
Default allowed levels: {action → level mapping}
Privacy option:         A (space_id only) / B (sys_objects_privacy)
Privacy objects:        {list of object names if Option B}
Content context:        standalone / space-aware / group-aware
Moderation flow:        auto-publish / pending approval

TIMELINE INTEGRATION
--------------------
sys_objects_content_info: registered as {vendor}_{module}
Alert unit:               {vendor}_{module}
Alert actions:            added / edited / deleted
owner_id strategy:        space_id when present / author profile ID for site-wide
serviceGetContentInfoArray(): present

VERIFICATION SUMMARY
--------------------
Stage 1: sys_menu_items columns: {count} — {source: live/guide-based}
Stage 1: sys_acl_levels IDs: {ids} — {source: live/guide-based}
Stage 1: Active theme: {theme}
Stage 2: Reference used: {module path}
Stage 2: addCss() signature: verified compatible
Stage 3: space_id column: present in all content tables
Stage 3: ACL actions: registered | checkAllow*() methods: generated
Stage 3: sys_objects_privacy: registered (Option B) / skipped (Option A)
Stage 3: sys_objects_content_info: registered | BxDolAlerts: wired to add/edit/delete
Stage 4: Serialized strings computed: {count} | verified: {count}
Stage 5: Files written: {count} | Read-back confirmed: yes/no
Stage 6: Structure validated: pass/fail

KNOWN ASSUMPTIONS
-----------------
{List any fact taken from memory rather than verified live.
 If none: "None — all structural facts verified from live instance."}

OPTION B UPGRADE NOTE (sys_objects_privacy)
--------------------------------------------
If Option A was used: to upgrade to native "Visible to..." dropdown (Option B):
1. Register privacy objects in sys_objects_privacy with default_group per action
2. Replace manual space_id dropdown with BxDolPrivacy::getObject()
3. Add $oPrivacy->check($iEntryId) as Layer 2 in checkAllowView()
4. Content will appear natively in Space/Group timelines

HUMAN ACTION REQUIRED
---------------------
1. Verify module at /var/www/una/modules/{vendor}/{module}/
2. Studio → Modules → Install
3. Studio → ACL → confirm module actions appear and levels are correct
4. Check /var/www/una/storage/logs/ after install
5. Confirm menu item appears in site navigation
6. Test form submission with space_id stored correctly
7. Test permission gates: verify Standard member cannot perform Moderator actions
8. Create a test entry → confirm it appears in the site timeline (Timeline page)
9. If space-aware: create entry inside a Space → confirm it appears in that Space's feed
   and NOT in the global public feed (unless privacy = Everyone)
10. Confirm serviceGetContentInfoArray() renders the correct title/URL in the feed card
```

---

## Common Failure Patterns

| Symptom | Root Cause | Stage |
|---------|-----------|-------|
| Menu item missing after install | Wrong sys_menu_items column count | Stage 1 + 6 |
| Page block shows nothing | Wrong serialized method name or byte length | Stage 4 |
| Install fails with SQL error | v15 columns in v14 INSERT | Stage 1 + 6 |
| PHP fatal on module load | Multiple classes in one file | Stage 3 + 6 |
| Fatal: addCss() mismatch | Wrong signature in Template class | Stage 2.3 + 6 |
| White on white form fields | Theme CSS beating module CSS | RULE 7 + Stage 3.6 |
| Ward/Space dropdown empty | Querying wrong table/profile type | Stage 3.4 |
| Cover area blank at top | cover=1 with cover_image=0 | Stage 3.5 |
| CSS breaks on Lucid/Protean | No !important injection | RULE 7 |
| ACL actions not in Studio | sys_acl_actions INSERT missing or wrong module name | Stage 3.8 + 6 |
| All members can delete any content | checkAllowDelete() missing "any" vs "own" branch | Stage 3.10 + 6 |
| Privacy dropdown not showing | Option B not registered in sys_objects_privacy | Stage 3.9 + 6 |
| Space content visible site-wide | space_id filter missing in DB query WHERE clause | Stage 3.4 + 6 |
| Group context broken | bx_groups_data join used bx_spaces_data table | Stage 3.4 |
| Uninstall leaves orphan ACL rows | DELETE from sys_acl_matrix not in uninstall.sql | Stage 3.8 + 6 |
| Entry created but nothing in timeline | BxDolAlerts not fired, or sys_objects_content_info missing | RULE 10 + Stage 3.11 |
| Events in bx_timeline_events but published=0 | serviceGetTimelineData() missing — the most common failure | Stage 3.11B |
| Events in bx_timeline_events but published=0 | installer.php uses onEnable() instead of _onEnableAfter() — onEnable() is never called by BxDolStudioInstaller | Stage 3.11E |
| Events in bx_timeline_events but published=0 | serviceGetTimelinePost() missing 'published' key or returning 0 | Stage 3.11C |
| Timeline card renders blank/broken | serviceGetContentInfoArray() missing from Module class | Stage 3.11D + 6 |
| Space entry appears in global feed | owner_id set to author ID instead of space_id | Stage 3.11F + 6 |
| Public entry not appearing in global feed | owner_id set to space_id when space_id = 0 | Stage 3.11F + 6 |
| Timeline fires on failed create | BxDolAlerts called before DB insert success check | Stage 3.11F |
| alert_unit mismatch (silent failure) | BxDolAlerts string differs from sys_objects_content_info name | Stage 3.11 + 6 |
| Handlers registered but vanish after disable/enable | onDisable() missing delete_handlers_for_module call | Stage 3.11E |

---

## Repetitive Command Loop Prevention

Each verification command may be requested **exactly once per stage**.
If the human doesn't provide output within one exchange → proceed with guide-based assumption and flag it.
Batch all commands into one request — never drip-feed one command per exchange.

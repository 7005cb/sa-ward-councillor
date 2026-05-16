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

You are executing the **UNA CMS 14.0 Module Development Execution Protocol v2.4**.
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
```

**Record:**
- `sys_menu_items column count = __` (this governs all INSERT statements)
- `active_theme = __` (if NOT 'artificer' → flag and apply RULE 7)
- `sys_acl_levels IDs = __` (needed for sys_acl_matrix INSERT patterns)
- `sys_acl_actions column structure = __` (governs ACL INSERT statements)

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
6.  classes/{Prefix}Module.php          ← includes checkAllow*() + timeline/notifications services
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

When context is "either" (Space or Group), use a type discriminator:
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
INSERT INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('{vendor}_{module}', 'view entry',       NULL, 'View Entry',       '', 0, ''),
  ('{vendor}_{module}', 'create entry',     NULL, 'Create Entry',     '', 0, ''),
  ('{vendor}_{module}', 'edit own entry',   NULL, 'Edit Own Entry',   '', 0, ''),
  ('{vendor}_{module}', 'edit any entry',   NULL, 'Edit Any Entry',   '', 0, ''),
  ('{vendor}_{module}', 'delete own entry', NULL, 'Delete Own Entry', '', 0, ''),
  ('{vendor}_{module}', 'delete any entry', NULL, 'Delete Any Entry', '', 0, '');

INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 3, `ID` FROM `sys_acl_actions`
WHERE `Module` = '{vendor}_{module}' AND `Name` IN ('view entry', 'create entry', 'edit own entry', 'delete own entry');
```

In uninstall.sql:
```sql
DELETE FROM `sys_acl_matrix` WHERE `IDAction` IN
  (SELECT `ID` FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}');
DELETE FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}';
```

Gate every operation in your Module class:
```php
if (!BxDolAcl::getInstance()->isMemberLevelInSet('create entry'))
    return MsgBox(_t('_Access Denied'));
```

### 3.9 — Privacy Registration (Option B)

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

In uninstall.sql:
```sql
DELETE FROM `sys_objects_privacy` WHERE `module` = '{vendor}_{module}';
```

### 3.10 — Layered Permission Check Methods

```php
public function checkAllowView($iEntryId)
{
    if (!BxDolAcl::getInstance()->isMemberLevelInSet('view entry'))
        return false;

    $oPrivacy = BxDolPrivacy::getObject('{vendor}_{module}_entry_view');
    if ($oPrivacy && !$oPrivacy->check($iEntryId))
        return false;

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

    if (BxDolAcl::getInstance()->isMemberLevelInSet('edit any entry'))
        return true;

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

### 3.11 — Timeline Integration

Every content module that fires `BxDolAlerts` must provide a complete four-part timeline
stack. Missing any single part causes events to sit in `bx_timeline_events` with blank
`title` and `object_owner_id=0` — they exist in the DB but **never render in any feed**.

#### Part 1 — install.sql: sys_objects_content_info

Verify column list first with `DESCRIBE sys_objects_content_info;`.

```sql
INSERT INTO `sys_objects_content_info`
  (`name`, `title`, `alert_unit`, `alert_action_add`, `alert_action_update`,
   `alert_action_delete`, `class_name`, `class_file`)
VALUES
  ('{vendor}_{module}',
   '_{vendor}_{module}_content_info_title',
   '{vendor}_{module}',
   'added', 'edited', 'deleted',
   '', '');
```

In uninstall.sql:
```sql
DELETE FROM `sys_objects_content_info` WHERE `name` = '{vendor}_{module}';
```

#### Part 2 — installer.php: onEnable() / onDisable()

The correct service method is **`add_handlers`** (NOT `add_handlers_for_module`).
**Never** INSERT rows directly into `bx_timeline_handlers` in install.sql — `add_handlers`
does this correctly by reading `serviceGetTimelineData()`. Manual insertion without a
working `serviceGetTimelineData()` causes silent `getData()` failures.

```php
public function onEnable($aParams)
{
    $mixedResult = parent::onEnable($aParams);
    if ($mixedResult !== true)
        return $mixedResult;

    BxDolService::call('bx_timeline',      'add_handlers', [$this->_aModule['name']]);
    BxDolService::call('bx_notifications', 'add_handlers', [$this->_aModule['name']]);

    return true;
}

public function onDisable($aParams)
{
    $mixedResult = parent::onDisable($aParams);
    if ($mixedResult !== true)
        return $mixedResult;

    BxDolService::call('bx_timeline',      'delete_handlers', [$this->_aModule['name']]);
    BxDolService::call('bx_notifications', 'delete_handlers', [$this->_aModule['name']]);

    return true;
}
```

#### Part 3 — serviceGetTimelineData()

**This is the most commonly missed method.** Without it, `add_handlers` has nothing to
read and no handlers get registered.

Critical naming rules — verified from live SA Rentals module:
- Handler `group` MUST use the single `_object` suffix → `sa_rentals_object`
  **NOT** `_object_add`, `_object_update`, `_object_delete` — those cause lookup key failures
- Only the `insert` handler carries content fields (`module_name`, `module_method`,
  `module_class`, `groupable`, `group_by`)
- Top-level key is **`alerts`** (not `groups`, not `settings`)
- `BxTimelineConfig::getHandlers()` looks up by key `{alert_unit}_{alert_action}`
  e.g. `sa_rentals_added` — alert_unit + alert_action must match your BxDolAlerts calls

```php
public function serviceGetTimelineData()
{
    $sModule = $this->_aModule['name'];
    return array(
        'handlers' => array(
            // INSERT handler: module_name/method/class/groupable/group_by are REQUIRED here only
            array(
                'group'         => $sModule . '_object',
                'type'          => 'insert',
                'alert_unit'    => $sModule,
                'alert_action'  => 'added',
                'module_name'   => $sModule,
                'module_method' => 'get_timeline_post',
                'module_class'  => 'Module',
                'groupable'     => 0,
                'group_by'      => '',
            ),
            // UPDATE/DELETE: no content fields — group name uses the SAME '_object' suffix
            array('group' => $sModule . '_object', 'type' => 'update', 'alert_unit' => $sModule, 'alert_action' => 'edited'),
            array('group' => $sModule . '_object', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'deleted'),
        ),
        'alerts' => array(  // ← key is 'alerts', NOT 'groups' or 'settings'
            array('unit' => $sModule, 'action' => 'added'),
            array('unit' => $sModule, 'action' => 'edited'),
            array('unit' => $sModule, 'action' => 'deleted'),
        ),
    );
}
```

#### Part 4 — serviceGetTimelinePost()

**`BxTimelineTemplate::getData()` (line 1684) hard-fails with `return false` if:**
- `$aResult['object_owner_id']` is empty, zero, null, or absent
- `$aResult['content']` is an empty string `''`, empty array `[]`, or absent

When `getData()` returns false, `onPost()` does nothing and the event row stays with
`title=''` and `object_owner_id=0` in `bx_timeline_events`. **This is the real failure
indicator — not `published=0`.** The `published` column is a scheduled-posts field unused
in display queries. Never diagnose timeline failures by checking `published`.

**Canonical return format — verified working (SA Rentals module):**

```php
public function serviceGetTimelinePost($aEvent, $aBrowseParams = array())
{
    $iEntryId = (int)($aEvent['object_id'] ?? 0);
    $aEntry   = $this->_oDb->getEntry($iEntryId);
    if (!$aEntry) return false;

    $iAuthorId = (int)$aEntry['author_id'];
    $sUrl      = BX_DOL_URL_ROOT . 'page.php?i=view-{module}-listing&id=' . $iEntryId;

    // content MUST be a non-empty array — '' or [] will cause getData() to return false
    $aContent = array(
        'url'   => $sUrl,
        'title' => bx_process_output($aEntry['title']),
        'text'  => bx_process_output($aEntry['description'] ?? ''),
    );

    // Optionally attach first image from storage
    if (!empty($aEntry['media_storage_ids'])) {
        $oStorage = BxDolStorage::getObjectInstance('{vendor}_{module}_files');
        if ($oStorage) {
            $iFirst = (int)trim(explode(',', $aEntry['media_storage_ids'])[0]);
            if ($iFirst) {
                $sImageUrl = (string)$oStorage->getFileUrlById($iFirst);
                if ($sImageUrl)
                    $aContent['images'] = array($sImageUrl);
            }
        }
    }

    // REQUIRED keys: object_owner_id (non-zero int) + content (non-empty array)
    // Do NOT include: author, id, views, votes, privacy, published — getData() ignores them
    return array(
        '_cache'          => true,
        'object_owner_id' => $iAuthorId,   // REQUIRED — integer author profile ID, must be non-zero
        'url'             => $sUrl,
        'content'         => $aContent,    // REQUIRED — non-empty array with at least url+title
        'title'           => bx_process_output($aEntry['title']),
        'description'     => '',
    );
}
```

#### BxDolAlerts wiring

Fire alerts after every confirmed successful DB operation. Fetch entry data BEFORE delete:

```php
// After successful create:
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'added', $iEntryId, $iAuthorProfileId,
    array('owner_id' => $iOwnerId));
$oAlert->alert();

// After successful edit:
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'edited', $iEntryId, $iAuthorProfileId,
    array('owner_id' => $iOwnerId));
$oAlert->alert();

// Before delete — fetch entry BEFORE deleting so space_id is still available:
$aEntry = $this->_oDb->getEntry($iEntryId);
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
// ... run DB delete ...
$oAlert = new BxDolAlerts('{vendor}_{module}', 'deleted', $iEntryId, $iAuthorProfileId,
    array('owner_id' => $iOwnerId));
$oAlert->alert();
```

**The `alert_unit` string in every `new BxDolAlerts(...)` call MUST be byte-for-byte
identical to the `name` in `sys_objects_content_info`. A mismatch is silent — no error,
no feed event.**

#### Timeline Failure Diagnosis Path

```
Step 1: Check bx_timeline_events WHERE type = '{vendor}_{module}'
        → title='' AND object_owner_id=0 → getData() is failing (fix serviceGetTimelinePost)
        → published=0 → NOT a failure indicator, ignore this column

Step 2: Verify bx_timeline_handlers has rows with alert_unit = '{vendor}_{module}'
        → Empty → serviceGetTimelineData() is wrong or add_handlers was never called
        → Group names have _object_add/_update/_delete → wrong; must be single _object

Step 3: Call getData() in a diagnostic script — if empty, check serviceGetTimelinePost()
        returns object_owner_id (non-zero int) and content (non-empty array)

Step 4: If handlers are stale/wrong group names:
        DELETE FROM bx_timeline_handlers WHERE alert_unit = '{vendor}_{module}';
        Then: BxDolService::call('bx_timeline', 'add_handlers', ['{vendor}_{module}']);
        Old wrong-group-name handlers block re-registration on duplicate key.
```

### 3.12 — Notifications Integration

#### serviceGetNotificationsData()

**Critical: the `settings` key is required.** Without it, `BxBaseModNotificationsDb::insertData()`
never populates `bx_notifications_settings`, and the module never appears on the
`/notifications-settings` page. This is a silent failure — no error, just no rows.

```php
public function serviceGetNotificationsData()
{
    $sModule = $this->_aModule['name'];
    return array(
        // handlers → rows in bx_notifications_handlers
        // Only INSERT handler carries content fields
        'handlers' => array(
            array(
                'group'                => $sModule . '_object',
                'type'                 => 'insert',
                'alert_unit'           => $sModule,
                'alert_action'         => 'added',
                'module_name'          => $sModule,
                'module_method'        => 'get_notifications_post',
                'module_class'         => 'Module',
                'module_event_privacy' => '',
            ),
            array('group' => $sModule . '_object', 'type' => 'update', 'alert_unit' => $sModule, 'alert_action' => 'edited'),
            array('group' => $sModule . '_object', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'deleted'),
        ),
        // settings → rows in bx_notifications_settings (REQUIRED — module appears on /notifications-settings)
        // personal = to author, follow_member = to followers of author, follow_context = Space/Group followers
        'settings' => array(
            array(
                'group'  => 'object',
                'unit'   => $sModule,
                'action' => 'added',
                'types'  => array('personal', 'follow_member', 'follow_context'),
            ),
        ),
        // alerts → rows in sys_alerts wiring unit/action to the bx_notifications handler
        'alerts' => array(
            array('unit' => $sModule, 'action' => 'added'),
            array('unit' => $sModule, 'action' => 'edited'),
            array('unit' => $sModule, 'action' => 'deleted'),
        ),
    );
}
```

#### serviceGetNotificationsPost()

```php
public function serviceGetNotificationsPost($aEvent)
{
    $iEntryId = (int)($aEvent['object_id'] ?? 0);
    $aEntry   = $this->_oDb->getEntry($iEntryId);
    if (!$aEntry) return false;

    $sAction = ($aEvent['action'] ?? '') === 'edited'
        ? _t('_{vendor}_{module}_notification_edited')
        : _t('_{vendor}_{module}_notification_added');

    return array(
        'id'      => (int)($aEvent['id'] ?? 0),
        'title'   => bx_process_output($aEntry['title']),
        'content' => $sAction,
        'url'     => BX_DOL_URL_ROOT . 'page.php?i=view-{module}-listing&id=' . $iEntryId,
        'image'   => '',
        'author'  => (int)$aEntry['author_id'],
    );
}
```

Add lang strings to `install/langs/en.xml`:
```xml
<string name="_{vendor}_{module}_notification_added"><![CDATA[New listing added]]></string>
<string name="_{vendor}_{module}_notification_edited"><![CDATA[Listing was updated]]></string>
```

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
[ ] addCss() signature in Template class matches BxDolModuleTemplate
[ ] CSS written Artificer-first with injection fallback if needed
[ ] sys_objects_content_info INSERT in install.sql (3.11)
[ ] sys_objects_content_info DELETE in uninstall.sql (3.11)
[ ] NO manual bx_timeline_handlers INSERT in install.sql (3.11)
[ ] serviceGetTimelineData() present with single _object group suffix (3.11)
[ ] serviceGetTimelinePost() returns object_owner_id (int) + non-empty content array (3.11)
[ ] serviceGetNotificationsData() present with handlers + settings + alerts keys (3.12)
[ ] serviceGetNotificationsPost() present (3.12)
[ ] onEnable()/onDisable() in installer.php call add_handlers/delete_handlers (3.11/3.12)
[ ] BxDolAlerts fired after create, edit, delete — alert_unit matches sys_objects_content_info name (3.11)
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
[ ] sys_objects_content_info INSERT present in install.sql
[ ] sys_objects_content_info DELETE present in uninstall.sql
[ ] NO manual bx_timeline_handlers INSERT in install.sql
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
[ ] serviceGetTimelineData() handler groups use _object suffix (not _object_add etc.)
[ ] serviceGetTimelinePost() returns object_owner_id (int) + content (non-empty array)
[ ] serviceGetNotificationsData() has all three keys: handlers, settings, alerts
[ ] onEnable() calls add_handlers for both bx_timeline and bx_notifications
[ ] BxDolAlerts alert_unit matches sys_objects_content_info name exactly
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

TIMELINE & NOTIFICATIONS
------------------------
sys_objects_content_info: registered as {vendor}_{module}
alert_unit:               {vendor}_{module}
serviceGetTimelineData:   present — handler group: {vendor}_{module}_object
serviceGetTimelinePost:   present — returns object_owner_id + content array
serviceGetNotificationsData: present — handlers + settings + alerts keys
onEnable/onDisable:       add_handlers / delete_handlers wired for both

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
Stage 4: Serialized strings computed: {count} | verified: {count}
Stage 5: Files written: {count} | Read-back confirmed: yes/no
Stage 6: Structure validated: pass/fail

KNOWN ASSUMPTIONS
-----------------
{List any fact taken from memory rather than verified live.
 If none: "None — all structural facts verified from live instance."}

HUMAN ACTION REQUIRED
---------------------
1. Verify module at /var/www/una/modules/{vendor}/{module}/
2. Studio → Modules → Install (or uninstall + reinstall if updating)
3. Studio → ACL → confirm module actions appear and levels are correct
4. Check /var/www/una/storage/logs/ after install
5. Confirm menu item appears in site navigation
6. Test form submission with space_id stored correctly
7. Test permission gates: verify Standard member cannot perform Moderator actions
8. Create a test entry → Timeline page → confirm entry appears in feed
9. Check /notifications-settings → confirm module appears in the list
10. bx_timeline_events test row: confirm non-empty title and non-zero object_owner_id
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
| Timeline events in DB, never in feed | serviceGetTimelinePost() returns wrong keys: 'author' not 'object_owner_id', or content='' not non-empty array | Stage 3.11 + 6 |
| bx_timeline_events row: title='' object_owner_id=0 | BxTimelineTemplate::getData() returned false — fix serviceGetTimelinePost() return format | Stage 3.11 |
| Wrong handler group names registered | serviceGetTimelineData() used _object_add/_update/_delete; must be single _object suffix | Stage 3.11 + 6 |
| Module missing from /notifications-settings | serviceGetNotificationsData() lacks 'settings' key — bx_notifications_settings never populated | Stage 3.12 + 6 |
| published=0 misdiagnosed as failure | published is a scheduled-posts field, not a display filter. Real indicator: title='' and object_owner_id=0 | Stage 3.11 |
| Timeline handlers not registered after enable | add_handlers_for_module is wrong name; correct method is add_handlers | Stage 3.11 |
| Alert fires but no timeline event appears | alert_unit in BxDolAlerts does not exactly match name in sys_objects_content_info | Stage 3.11 |

---

## Repetitive Command Loop Prevention

Each verification command may be requested **exactly once per stage**.
If the human doesn't provide output within one exchange → proceed with guide-based assumption and flag it.
Batch all commands into one request — never drip-feed one command per exchange.

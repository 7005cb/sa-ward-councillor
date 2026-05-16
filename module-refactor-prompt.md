# UNA CMS Module Refactor Prompt
# Paste this into a new conversation and attach your module files.
# ─────────────────────────────────────────────────────────────────

---

You are refactoring an existing custom UNA CMS 14.0 module. The module was previously built
but is missing four critical systems that all content modules require:

1. **ACL action registration** — actions are not registered in `sys_acl_actions` / `sys_acl_matrix`,
   and no `BxDolAcl::isMemberLevelInSet()` gates exist on operations.

2. **Layered permission checks** — the Module class lacks `checkAllowView()`, `checkAllowEdit()`,
   and `checkAllowDelete()` methods that chain ACL → Privacy → Space/Group context.

3. **Privacy / visibility** — `sys_objects_privacy` is not registered (Option B), and the
   existing `space_id` column (Option A) is not being filtered correctly in all DB queries.

4. **Timeline / activity feed integration** — the module is missing the complete
   four-part timeline stack. Each part must be present or events sit in
   `bx_timeline_events` with blank `title` and `object_owner_id=0`, never appearing in any feed:
   - `sys_objects_content_info` registration in install.sql
   - `serviceGetTimelineData()` in the Module class (most commonly missed — wrong group names break lookup)
   - `serviceGetTimelinePost()` in the Module class returning the **canonical format** (see Step 3, 5c)
   - `onEnable()` / `onDisable()` hooks in installer.php calling `add_handlers`

   **Note:** `bx_timeline_handlers` must NOT be manually inserted in install.sql —
   it is populated by the timeline module when `onEnable()` calls `add_handlers`.
   Manual SQL registration without a working `serviceGetTimelineData()` causes silent
   `getData()` failures (events in DB, `title=''`, `object_owner_id=0`, never rendered).

   **Failure indicator:** Check `bx_timeline_events` for `title=''` and `object_owner_id=0`
   after adding an entry. That means `BxTimelineTemplate::getData()` returned false.
   `published=0` is NOT a failure indicator — it is a scheduled-posts field, not a display filter.

You are NOT rebuilding the module from scratch. You are performing **surgical additions** to the
existing files. Do not change any working logic, rename any methods, or alter any SQL tables
beyond what is specified below.

---

## STEP 1 — Read Every File First

Before generating any output, read and acknowledge these files (attached or provided):

- `install/sql/install.sql`
- `install/sql/uninstall.sql`
- `classes/{Prefix}Module.php`
- `classes/{Prefix}Db.php`
- `install/config.php`
- `install/installer.php`

From these files, extract and record:

```
Module name (vendor_module):        ___________   e.g. sa_rentals
Class prefix:                       ___________   e.g. SaRentals
Content table name:                 ___________   e.g. sa_rentals_entries
Author ID column name:              ___________   e.g. author_id
space_id column present?            yes / no
Existing service methods:           ___________   (list all serviceXxx() methods found)
Existing permission checks:         ___________   (list any BxDolAcl or login checks found)
BxDolAlerts fired anywhere?          yes / no
sys_objects_content_info entry?      yes / no   (search install.sql)
bx_timeline_handlers in install.sql? yes / no   (YES = problem — must be removed)
serviceGetTimelineData() exists?     yes / no
  → if yes: does handler group use single _object suffix? yes / no
serviceGetTimelinePost() exists?     yes / no
  → if yes: does it return 'object_owner_id' (not 'author')? yes / no
  → if yes: does it return 'content' as a non-empty array?  yes / no
serviceGetNotificationsData() exists? yes / no
  → if yes: does it have 'handlers', 'settings', AND 'alerts' keys? yes / no
serviceGetContentInfoArray() exists? yes / no
installer.php onEnable() exists?     yes / no
  → calls add_handlers (not add_handlers_for_module)? yes / no
```

Do not proceed until this extraction table is complete.

---

## STEP 2 — Confirm the Permissions Architecture

Answer these questions based on what the module does. If unsure, ask the user before proceeding.

**ACL Actions needed:**
Review what the module allows users to do and select from:
- `view entry` — read a single content item
- `create entry` — submit new content
- `edit own entry` — modify own content
- `edit any entry` — modify anyone's content (moderators)
- `delete own entry` — remove own content
- `delete any entry` — remove anyone's content (moderators)
- `approve entry` — publish pending content (if moderation flow exists)
- `feature entry` — pin/feature content (if that feature exists)

Record the final action list and default allowed membership level for each.

**Privacy option:**
- Option A: `space_id` column only, manual WHERE clause filtering. Simpler.
- Option B: Full `sys_objects_privacy` registration with native "Visible to…" dropdown.

Ask the user which they want if not clear from the existing module.

**Content context:**
- `standalone` — content is site-wide, space_id is always 0
- `space-aware` — content belongs to a Space (bx_spaces)
- `group-aware` — content belongs to a Group (bx_groups)
- `either` — content can belong to Space or Group, uses context_type discriminator

---

## STEP 3 — Generate the Five Additions

Produce exactly these five additions as surgical patches. Do not touch anything else.

### Addition 1 — ACL SQL block (goes into install.sql)

Insert this block AFTER the existing `sys_menu_items` inserts and BEFORE the final
semicolon of install.sql:

```sql
-- ─── ACL: Register module actions ──────────────────────────────────────────
INSERT INTO `sys_acl_actions`
  (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`)
VALUES
  ('{vendor}_{module}', 'view entry',       NULL, '_acl_txt_{vendor}_{module}_view_entry',       '', 0, ''),
  ('{vendor}_{module}', 'create entry',     NULL, '_acl_txt_{vendor}_{module}_create_entry',     '', 0, ''),
  ('{vendor}_{module}', 'edit own entry',   NULL, '_acl_txt_{vendor}_{module}_edit_own_entry',   '', 0, ''),
  ('{vendor}_{module}', 'edit any entry',   NULL, '_acl_txt_{vendor}_{module}_edit_any_entry',   '', 0, ''),
  ('{vendor}_{module}', 'delete own entry', NULL, '_acl_txt_{vendor}_{module}_delete_own_entry', '', 0, ''),
  ('{vendor}_{module}', 'delete any entry', NULL, '_acl_txt_{vendor}_{module}_delete_any_entry', '', 0, '');

-- Grant Standard members (level 3) basic content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 3, `ID` FROM `sys_acl_actions`
WHERE `Module` = '{vendor}_{module}'
AND `Name` IN ('view entry', 'create entry', 'edit own entry', 'delete own entry');

-- Grant Moderators (level 5) full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 5, `ID` FROM `sys_acl_actions`
WHERE `Module` = '{vendor}_{module}';

-- Grant Administrators (level 6) full content rights
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`)
SELECT 6, `ID` FROM `sys_acl_actions`
WHERE `Module` = '{vendor}_{module}';
-- ────────────────────────────────────────────────────────────────────────────
```

**IMPORTANT:** Replace level integers (3, 5, 6) with the actual IDs from the live
`sys_acl_levels` table. Ask the user to run:
```sql
SELECT id, Name FROM sys_acl_levels ORDER BY id;
```
and use those IDs.

### Addition 2 — ACL uninstall block (goes into uninstall.sql)

Add this BEFORE the final line of uninstall.sql:

```sql
-- ─── ACL: Remove module actions ─────────────────────────────────────────────
DELETE FROM `sys_acl_matrix` WHERE `IDAction` IN
  (SELECT `ID` FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}');
DELETE FROM `sys_acl_actions` WHERE `Module` = '{vendor}_{module}';
-- ────────────────────────────────────────────────────────────────────────────
```

### Addition 3 — sys_objects_privacy SQL block (Option B only — skip if Option A)

Add this block to install.sql, after the ACL block from Addition 1:

```sql
-- ─── Privacy: Register visibility objects ───────────────────────────────────
INSERT INTO `sys_objects_privacy`
  (`object`, `module`, `action`, `title`, `default_group`,
   `table`, `table_field_id`, `table_field_author_id`, `extra_field`)
VALUES
  ('{vendor}_{module}_entry_view',    '{vendor}_{module}', 'view',    '_privacy_view',    1,
   '{content_table}', 'id', 'author_id', ''),
  ('{vendor}_{module}_entry_comment', '{vendor}_{module}', 'comment', '_privacy_comment', 2,
   '{content_table}', 'id', 'author_id', '');
-- ────────────────────────────────────────────────────────────────────────────
```

`default_group` integers: 1=Everyone, 2=Logged-in, 3=Friends, 4=Only Me.
Adjust per the privacy architecture decided in Step 2.

Add to uninstall.sql:
```sql
DELETE FROM `sys_objects_privacy` WHERE `module` = '{vendor}_{module}';
```

### Addition 4 — Permission check methods (goes into {Prefix}Module.php)

Add these three methods inside the class body, before the closing `}` of the class.
Do NOT alter any existing methods.

```php
    // ─── Permission Gates ──────────────────────────────────────────────────

    public function checkAllowView($iEntryId)
    {
        // Layer 1: ACL membership gate
        if (!BxDolAcl::getInstance()->isMemberLevelInSet('view entry'))
            return false;

        // Layer 2: Privacy gate (remove this block if using Option A)
        $oPrivacy = BxDolPrivacy::getObject('{vendor}_{module}_entry_view');
        if ($oPrivacy && !$oPrivacy->check($iEntryId))
            return false;

        // Layer 3: Space/Group context gate
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

    // ──────────────────────────────────────────────────────────────────────
```

### Addition 5 — Timeline + Notifications integration

**WARNING before starting:** If `bx_timeline_handlers` is present in install.sql,
remove it entirely. That table must NOT be manually populated — `onEnable()` calling
`add_handlers` populates it correctly by reading `serviceGetTimelineData()`.

**5a — install.sql:** Add `sys_objects_content_info` registration. Verify the column
list first with `DESCRIBE sys_objects_content_info;`:

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

**5b — uninstall.sql:** Add:
```sql
DELETE FROM `sys_objects_content_info` WHERE `name` = '{vendor}_{module}';
```

**5c — {Prefix}Module.php:** Add these four methods before the closing `}`.

**CRITICAL format rules verified from live SA Rentals module:**
- `serviceGetTimelineData()`: handler `group` MUST use single `_object` suffix (e.g. `sa_rentals_object`).
  Using `_object_add`, `_object_update`, `_object_delete` breaks `BxTimelineConfig::getHandlers()` lookup.
  Top-level key is `alerts`, not `groups`. Only the `insert` handler carries content fields.
- `serviceGetTimelinePost()`: MUST return `object_owner_id` (non-zero integer) and `content`
  (non-empty array). If either is missing/empty, `BxTimelineTemplate::getData()` returns false
  and the event row stays with `title=''` in `bx_timeline_events`, never rendering.
  Do NOT return `author`, `views`, `votes`, `privacy`, or `published` — those keys are ignored.
- `serviceGetNotificationsData()`: MUST include a `settings` key. Without it,
  `bx_notifications_settings` is never populated and the module never appears on /notifications-settings.

```php
    // ─── Timeline Integration ──────────────────────────────────────────────

    public function serviceGetTimelineData()
    {
        $sModule = $this->_aModule['name'];
        // Handler group MUST use single _object suffix — not _object_add/_update/_delete
        // Top-level key is 'alerts', not 'groups'
        // Only insert handler carries module_name/method/class/groupable/group_by
        return array(
            'handlers' => array(
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
                array('group' => $sModule . '_object', 'type' => 'update', 'alert_unit' => $sModule, 'alert_action' => 'edited'),
                array('group' => $sModule . '_object', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'deleted'),
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'added'),
                array('unit' => $sModule, 'action' => 'edited'),
                array('unit' => $sModule, 'action' => 'deleted'),
            ),
        );
    }

    public function serviceGetTimelinePost($aEvent, $aBrowseParams = array())
    {
        $iEntryId = (int)($aEvent['object_id'] ?? 0);
        $aEntry   = $this->_oDb->getEntry($iEntryId);
        if (!$aEntry)
            return false;

        $iAuthorId = (int)$aEntry['author_id'];
        $sUrl      = BX_DOL_URL_ROOT . 'page.php?i=view-{uri}-listing&id=' . $iEntryId;

        // content MUST be a non-empty array — '' or [] causes getData() to return false
        $aContent = array(
            'url'   => $sUrl,
            'title' => bx_process_output($aEntry['title']),
            'text'  => bx_process_output($aEntry['description'] ?? ''),
        );

        // REQUIRED: object_owner_id (non-zero int) + content (non-empty array)
        // Do NOT return: author, id, views, votes, privacy, published — getData() ignores them
        return array(
            '_cache'          => true,
            'object_owner_id' => $iAuthorId,
            'url'             => $sUrl,
            'content'         => $aContent,
            'title'           => bx_process_output($aEntry['title']),
            'description'     => '',
        );
    }

    public function serviceGetNotificationsData()
    {
        $sModule = $this->_aModule['name'];
        // 'settings' key is REQUIRED — without it, module never appears on /notifications-settings
        return array(
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
            'settings' => array(
                array(
                    'group'  => 'object',
                    'unit'   => $sModule,
                    'action' => 'added',
                    'types'  => array('personal', 'follow_member', 'follow_context'),
                ),
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'added'),
                array('unit' => $sModule, 'action' => 'edited'),
                array('unit' => $sModule, 'action' => 'deleted'),
            ),
        );
    }

    public function serviceGetNotificationsPost($aEvent)
    {
        $iEntryId = (int)($aEvent['object_id'] ?? 0);
        $aEntry   = $this->_oDb->getEntry($iEntryId);
        if (!$aEntry)
            return false;

        $sAction = ($aEvent['action'] ?? '') === 'edited'
            ? _t('_{vendor}_{module}_notification_edited')
            : _t('_{vendor}_{module}_notification_added');

        return array(
            'id'      => (int)($aEvent['id'] ?? 0),
            'title'   => bx_process_output($aEntry['title']),
            'content' => $sAction,
            'url'     => BX_DOL_URL_ROOT . 'page.php?i=view-{uri}-listing&id=' . $iEntryId,
            'image'   => '',
            'author'  => (int)$aEntry['author_id'],
        );
    }

    public function serviceGetContentInfoArray($iEntryId)
    {
        $aEntry = $this->_oDb->getEntry((int)$iEntryId);
        if (!$aEntry)
            return false;

        return array(
            'id'          => (int)$aEntry['id'],
            'title'       => bx_process_output($aEntry['title']),
            'description' => bx_process_output($aEntry['description'] ?? ''),
            'url'         => BX_DOL_URL_ROOT . 'page.php?i=view-{uri}-listing&id=' . $iEntryId,
            'image'       => '',
            'author'      => (int)$aEntry['author_id'],
            'added'       => (int)($aEntry['added'] ?? 0),
            'privacy'     => 1,
        );
    }

    // ──────────────────────────────────────────────────────────────────────
```

**5d — install/installer.php:** Add or update `onEnable()` and `onDisable()`.

**Use `add_handlers` — NOT `add_handlers_for_module`** (that method does not exist):

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

---

## STEP 4 — Wire the Gates Into Existing Service Methods

Scan all existing `serviceXxx()` methods in `{Prefix}Module.php` and locate:
- Any method that creates, edits, or deletes content
- Any method that returns a single content item for display

For each one, add the appropriate gate at the top of the method body:

```php
// For display/view methods:
if (!$this->checkAllowView($iEntryId))
    return MsgBox(_t('_Access Denied'));

// For edit methods:
if (!$this->checkAllowEdit($iEntryId))
    return MsgBox(_t('_Access Denied'));

// For delete methods:
if (!$this->checkAllowDelete($iEntryId))
    return MsgBox(_t('_Access Denied'));

// For create methods (ACL only — no entry ID yet):
if (!BxDolAcl::getInstance()->isMemberLevelInSet('create entry'))
    return MsgBox(_t('_Access Denied'));
```

List every method you modified and what gate was added.

---

## STEP 5 — Wire BxDolAlerts Into Service Methods

Locate the service methods responsible for creating, editing, and deleting content.
Add alert firing immediately after each **confirmed successful** DB operation.
Never fire before confirming the DB call succeeded.

**Determine owner_id once and apply consistently across all three alerts:**
```php
// Space/Group-aware content:
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;

// Standalone (site-wide only) content:
$iOwnerId = $iAuthorProfileId;
```

**After successful create:**
```php
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'added', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

**After successful edit:**
```php
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
$oAlert = new BxDolAlerts('{vendor}_{module}', 'edited', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

**After (or immediately before) successful delete:**
```php
// Fetch entry data BEFORE deleting so space_id is still available
$aEntry = $this->_oDb->getEntry($iEntryId);
$iAuthorProfileId = bx_get_logged_profile_id();
$iOwnerId = !empty($aEntry['space_id']) ? (int)$aEntry['space_id'] : $iAuthorProfileId;
// ... perform DB delete ...
$oAlert = new BxDolAlerts('{vendor}_{module}', 'deleted', $iEntryId, $iAuthorProfileId, [
    'owner_id' => $iOwnerId,
]);
$oAlert->alert();
```

**CRITICAL:** The `alert_unit` string in every `new BxDolAlerts(...)` call MUST be
byte-for-byte identical to the `name` value in `sys_objects_content_info`. A mismatch
is silent — no error, no feed event.

List every method you modified and confirm the alert_unit string.

---

## STEP 6 — Verify space_id Filtering in DB Queries

Open `{Prefix}Db.php` and locate all methods that return lists of content items
(typically `getEntries()`, `getEntriesBySpace()`, or similar).

For every query that retrieves a list, confirm the WHERE clause includes:
```sql
AND `space_id` = :space_id
```
when a space context is active, or filters correctly when `space_id = 0` (site-wide).

If any list query fetches content without filtering by `space_id`, add the filter.
Show the before and after for each query you modify.

---

## STEP 7 — Output Summary

Produce a refactor summary in this format:

```
=== Module Refactor Summary ===
Module:             {vendor}_{module}
Files modified:     install.sql, uninstall.sql, installer.php, {Prefix}Module.php, {Prefix}Db.php

ACL ADDITIONS
-------------
Actions registered:     {list}
Levels granted:         {level name → actions list}
Service methods gated:  {list of methods + which gate}

PRIVACY ADDITIONS
-----------------
Option used:            A (space_id only) / B (sys_objects_privacy)
Privacy objects added:  {list, or "none — Option A"}
space_id filter fixed:  {list of DB methods fixed, or "already correct"}

TIMELINE & NOTIFICATIONS ADDITIONS
-----------------------------------
sys_objects_content_info: registered as {vendor}_{module}
alert_unit:               {vendor}_{module}
BxDolAlerts wired in:     {list service methods}
owner_id strategy:        space_id when space_id > 0 / author profile ID for site-wide
serviceGetTimelineData:   present — handler group: {vendor}_{module}_object
serviceGetTimelinePost:   present — returns object_owner_id + non-empty content array
serviceGetNotificationsData: present — handlers + settings + alerts keys
serviceGetNotificationsPost: present
onEnable/onDisable:       add_handlers / delete_handlers wired for both

WHAT WAS NOT CHANGED
---------------------
{List any existing methods or SQL that was intentionally left untouched}

HUMAN ACTION REQUIRED
---------------------
1. Run the updated install.sql on your live DB (or uninstall + reinstall the module)
2. Studio → ACL → confirm the module actions appear under the correct membership levels
3. Test: log in as Standard member → confirm create/view/edit own works
4. Test: log in as Standard member → confirm edit any / delete any is blocked
5. Test: log in as Moderator → confirm edit any / delete any works
6. Create a test entry → open Timeline page → confirm the entry appears in the feed
7. Check bx_timeline_events: confirm test row has non-empty title and non-zero object_owner_id
8. Check /notifications-settings → confirm module appears in the list
9. If space-aware: create entry inside a Space → confirm it appears in the Space feed
10. Click the timeline card → confirm it links to the correct entry URL
11. Check /var/www/una/storage/logs/ for any PHP errors after reload
```

---

## Rules for This Refactor

- Read all files before generating any output.
- Do not rename any existing methods or variables.
- Do not alter any existing SQL tables or columns beyond adding the ACL and privacy rows.
- Do not rewrite working logic — only add the four additions and the gates.
- If the `getEntry()` method in `{Prefix}Db.php` does not exist, flag it — checkAllowEdit
  and checkAllowDelete depend on it fetching `author_id`.
- If `space_id` column is absent from the content table, flag it — do NOT add it silently,
  as this requires a schema migration the user must approve.
- The `alert_unit` string in every `new BxDolAlerts(...)` call MUST be byte-for-byte
  identical to the `name` column in `sys_objects_content_info`. A mismatch is silent —
  no error, no feed event.
- Fire BxDolAlerts only after confirming the DB operation succeeded. Never fire on failure.
- For delete operations: fetch the entry data (including space_id) BEFORE running the
  DELETE query, so owner_id can be set correctly in the alert.
- Produce complete file diffs or clearly marked insertion blocks — not vague descriptions.
- `published=0` in `bx_timeline_events` is NOT a failure indicator. The real indicator is
  `title=''` and `object_owner_id=0` after an add event.

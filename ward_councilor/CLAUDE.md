# Claude Code Task — Auto-fix orphaned ward data + deploy CSS

## Your job
Run every step below in order. No manual input from the user is needed.
All DB credentials are in `/var/www/unamodforge/inc/header.inc.php`.
Read them first, then use them for every mysql command.

---

## Step 0 — Read DB credentials

```bash
grep -E "DB_NAME|DB_USER|DB_PASS|DB_HOST" /var/www/unamodforge/inc/header.inc.php
```

Extract the values. Use them as $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME for all steps below.

---

## Step 1 — Diagnose orphaned records

Run these queries and print the full output so we can see what needs fixing:

```sql
-- All ward announcements and their space_id
SELECT id, title, space_id, status, created 
FROM sa_ward_councilor_announcements 
ORDER BY created DESC;

-- All ward requests and their space_id
SELECT id, title, space_id, status, created 
FROM sa_ward_councilor_requests 
ORDER BY created DESC LIMIT 20;

-- All ward meetings and their space_id
SELECT id, title, space_id, status, meeting_date 
FROM sa_ward_councilor_meetings 
ORDER BY meeting_date DESC LIMIT 20;

-- All active spaces with their sys_profiles.id
SELECT p.id AS profile_id, p.content_id, d.space_name
FROM sys_profiles p
JOIN bx_spaces_data d ON p.content_id = d.id
WHERE p.type = 'bx_spaces' AND p.status = 'active'
ORDER BY d.space_name ASC;
```

---

## Step 2 — Fix orphaned records

For every announcement, request, or meeting where `space_id IS NULL OR space_id = 0`:

1. Look at the record's title/content to determine which space it belongs to
   (e.g. a Boksburg announcement belongs to the Boksburg space)
2. Find the correct `sys_profiles.id` for that space from the query above
3. Run the UPDATE

If you cannot confidently determine the space from the record content, assign it to
the space whose name most closely matches any location reference in the title.
If truly ambiguous, print a warning and skip that record.

Example fix pattern:
```sql
UPDATE sa_ward_councilor_announcements 
SET space_id = <profile_id> 
WHERE id = <record_id>;
```

---

## Step 3 — Verify the fix

```sql
-- Confirm no more NULL space_ids
SELECT 
    'announcements' AS tbl, COUNT(*) AS nulls 
    FROM sa_ward_councilor_announcements WHERE space_id IS NULL OR space_id = 0
UNION ALL
SELECT 
    'requests', COUNT(*) 
    FROM sa_ward_councilor_requests WHERE space_id IS NULL OR space_id = 0
UNION ALL
SELECT 
    'meetings', COUNT(*) 
    FROM sa_ward_councilor_meetings WHERE space_id IS NULL OR space_id = 0;

-- Confirm stats now return counts per space
SELECT p.id AS space_profile_id, d.space_name,
    (SELECT COUNT(*) FROM sa_ward_councilor_announcements a WHERE a.space_id = p.id) AS announcements,
    (SELECT COUNT(*) FROM sa_ward_councilor_requests r WHERE r.space_id = p.id) AS requests,
    (SELECT COUNT(*) FROM sa_ward_councilor_meetings m WHERE m.space_id = p.id) AS meetings
FROM sys_profiles p
JOIN bx_spaces_data d ON p.content_id = d.id
WHERE p.type = 'bx_spaces' AND p.status = 'active'
ORDER BY d.space_name;
```

Print the full result table.

---

## Step 4 — Verify bx_spaces_data column names

We need to confirm the correct column name for child space relationships
and the correct column name for the space URI (used in navigation links).

```sql
DESCRIBE bx_spaces_data;
```

Print all columns. Then check:
- Is there a `parent_space` column? Or `parent_id`? Or something else?
- Is there a `uri` column? Or is the URI only in `sys_seo_links`?
- What is the exact column for the space's short name used in URLs?

---

## Step 5 — Fix _getChildSpaces() if needed

Open `/var/www/unamodforge/modules/sa/ward_councilor/classes/SaWardCouncilorTemplate.php`

Find the `_getChildSpaces()` method. The current query uses `d.parent_space` — 
confirm this matches the actual column name from Step 4.

If the column name is different, fix it with a targeted str_replace edit.

Also confirm what to use for the space URL. If `sys_seo_links` has the URI:
```sql
SELECT * FROM sys_seo_links WHERE module = 'bx_spaces' LIMIT 3;
```

Update the child space link in `getSidebarBlock()` to use the correct URL pattern.

---

## Step 6 — Fix _getCurrentSpaceId() space URL check

Open `SaWardCouncilorModule.php`, find `_getCurrentSpaceId()`.

The current code has this check:
```php
if(strpos($sPageUri, 'space') !== false || strpos((string)$_SERVER['REQUEST_URI'], 'space') !== false)
```

This check gates whether we resolve `$_GET['id']` as a space content ID.
Confirm what `$_GET['i']` actually equals on a space profile page by checking
the spaces module page object name:

```sql
SELECT name, title, uri FROM sys_objects_page WHERE uri LIKE '%space%' LIMIT 10;
```

Then update the strpos check to use the exact page URI string from the DB result
so it reliably matches. Use `str_replace` for a surgical edit.

---

## Step 7 — PHP lint all modified files

```bash
php -l /var/www/unamodforge/modules/sa/ward_councilor/classes/SaWardCouncilorTemplate.php
php -l /var/www/unamodforge/modules/sa/ward_councilor/classes/SaWardCouncilorModule.php
php -l /var/www/unamodforge/modules/sa/ward_councilor/request.php
```

All three must return `No syntax errors detected`. Fix any errors before continuing.

---

## Step 8 — Clear all relevant caches

```bash
# PHP file cache
find /var/www/unamodforge/cache -maxdepth 1 -name "*.php" -not -name "lang-*.php" -delete 2>/dev/null

# Object cache
find /var/www/unamodforge/cache/objects -maxdepth 1 -name "*.php" -delete 2>/dev/null

# Any CSS cache
find /var/www/unamodforge/cache -name "*.css" -delete 2>/dev/null

echo "Cache cleared"
```

---

## Step 9 — Final verification summary

Print a clean summary:

```
=== DEPLOYMENT COMPLETE ===

DB fixes applied:
  - Announcements fixed: X records assigned to spaces
  - Requests fixed: X records assigned to spaces  
  - Meetings fixed: X records assigned to spaces

Schema confirmed:
  - parent column in bx_spaces_data: <actual column name>
  - space URL pattern: <confirmed pattern>

PHP lint: PASS / FAIL

Cache: CLEARED

Next: Reload a Space page in the browser to verify stats show correct counts.
If counts still show 0, run Step 1 queries again and check space_id values match
what _getCurrentSpaceId() is actually returning (add a temporary error_log() call
if needed to debug the live value).
```

---

## Files involved
- `/var/www/unamodforge/modules/sa/ward_councilor/classes/SaWardCouncilorTemplate.php`
- `/var/www/unamodforge/modules/sa/ward_councilor/classes/SaWardCouncilorModule.php`
- `/var/www/unamodforge/modules/sa/ward_councilor/request.php`
- `/var/www/unamodforge/modules/sa/ward_councilor/template/css/nav.css` (already updated)
- DB tables: `sa_ward_councilor_announcements`, `sa_ward_councilor_requests`, `sa_ward_councilor_meetings`

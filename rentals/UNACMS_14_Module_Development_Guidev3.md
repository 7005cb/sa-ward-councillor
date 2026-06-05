# UNACMS 14.0 Module Development Guide

## Complete Reference for Building Compatible Modules

> **Version**: v3 — Pipeline Edition  
> **Last Updated**: April 2026  
> **Critical Addition**: All AI-assisted module development MUST follow the Validation-First Pipeline defined in Section 0 before writing any code or SQL.

---

## Table of Contents

0. [AI Development Pipeline (MANDATORY)](#0-ai-development-pipeline-mandatory)
1. [Overview](#overview)
2. [Module Structure](#module-structure)
3. [Naming Conventions](#naming-conventions)
4. [Configuration File](#configuration-file)
5. [Database Development](#database-development)
6. [PHP Class Files](#php-class-files)
7. [Template System](#template-system)
8. [Language Files](#language-files)
9. [SQL Installation Files](#sql-installation-files)
10. [Service Methods](#service-methods)
11. [Pages and Blocks](#pages-and-blocks)
12. [Menu Items](#menu-items)
13. [Input Validation & Output Escaping](#input-validation--output-escaping)
14. [Hooks & Alerts System](#hooks--alerts-system)
15. [Storage System](#storage-system)
16. [Permissions & Access Control](#permissions--access-control)
17. [Cross-Module Service Calls](#cross-module-service-calls)
18. [Common Pitfalls](#common-pitfalls)
19. [Debugging](#debugging)
20. [Complete Module Template](#complete-module-template)
21. [BxTemplFormView::getCode() Parameter Behavior](#21-bxtemplformviewgetcode-parameter-behavior)
22. [Form Display Rendering Pipeline](#22-form-display-rendering-pipeline)
23. [BxDolForm::getObjectInstance() Failure Modes](#23-bxdolformgetobjectinstance-failure-modes)
24. [Template parseHtmlByName() Path Resolution](#24-template-parsehtmlbyname-path-resolution)
25. [Page Cover Setting Impact](#25-page-cover-setting-impact)
26. [Form Input Visibility Filtering](#26-form-input-visibility-filtering)

---

## 0. AI Development Pipeline (MANDATORY)

> ⚠️ **THIS SECTION GOVERNS ALL AI-ASSISTED MODULE DEVELOPMENT.**  
> Any AI tool (Claude, Perplexity, Open WebUI, or other) receiving this document MUST execute this pipeline in full before generating any module file. Skipping stages is the primary cause of module failures.

---

### Why AI Tools Fail Without This Pipeline

AI tools generating UNA CMS modules without live verification fail because they **guess** at critical values that must be exact:

- **PHP serialized string byte-lengths** in `sys_pages_blocks.content` — off by even 1 character causes silent block failure
- **Column counts** in `sys_menu_items` — v15 has 34 columns, v14 has 30; inserting v15 columns causes fatal SQL errors
- **Method name alignment** — the method name in the serialized block `content` must exactly match the `service{MethodName}` function in the Module class
- **File structure** — missing files cause installer failures that are hard to diagnose without PHP error display

The solution is a **validation-first, reference-driven pipeline** that reads from the live UNA instance before writing anything.

---

### The 6-Stage Development Pipeline

Every module generation session — whether in Perplexity, Open WebUI, Claude, or any other AI tool — MUST execute these stages in order:

---

#### Stage 1 — MCP Connect & Environment Verify

**Before generating any file**, confirm the MCP connection to the live UNA VPS is active.

```
REQUIRED ACTIONS:
1. Verify MCP connection is live (list_directory or list_modules)
2. Confirm UNA version on the VPS: SELECT value FROM sys_options WHERE name = 'ver'
3. Confirm sys_menu_items column count: DESCRIBE sys_menu_items
4. Note the exact column count — this number governs ALL sys_menu_items INSERT statements
```

**If MCP is offline:** Generate files only. Do NOT write to VPS. Flag all serialized strings as UNVERIFIED and require manual review before installation.

**Gate:** Do not proceed to Stage 2 if environment cannot be confirmed and MCP is critical for this module.

---

#### Stage 2 — Read Reference Module (Live, Not Memory)

**Before generating**, read a working module of similar type from the live VPS. Never rely on memory patterns alone.

```
REQUIRED ACTIONS:
1. Identify reference module based on type:
   - Content/CRUD modules  → bx_events (modules/boonex/events/)
   - Task/utility modules  → bx_tasks  (modules/boonex/tasks/)
   - Payment/commerce      → bx_payment (modules/boonex/payment/)
   - Simple widget         → bx_ads (modules/boonex/ads/)

2. Read these files from the reference:
   - modules/{vendor}/{name}/install/config.php
   - modules/{vendor}/{name}/install/sql/install.sql  (first 300 lines minimum)
   - modules/{vendor}/{name}/classes/{Prefix}Module.php

3. Extract and record:
   - Exact column list from sys_menu_items INSERT in reference SQL
   - Exact format of sys_pages_blocks content serialized strings
   - sys_objects_page column count and order
   - Service method naming patterns used
```

**Gate:** Record the exact sys_menu_items column count from the reference INSERT statement. This number overrides all other sources.

---

#### Stage 3 — Generate With Hard Constraints

Generate all module files with the reference patterns as binding constraints, not suggestions.

```
ABSOLUTE GENERATION RULES:
1. ONE class per PHP file — never bundle multiple classes
2. Every PHP file starts: <?php defined('BX_DOL') or die('hack attempt');
3. Use bx_import() — never require/include for UNA classes
4. sys_menu_items INSERT must use EXACTLY the column count confirmed in Stage 1/2
5. DO NOT include: title_attr, info, area_label, persistent (v15-only columns)
6. compatible_with MUST be '14.0.x' — never '15.x' or generic
7. DO NOT override parseHtmlByName() in Template class — causes fatal errors in v14
8. service method names: PHP function is serviceGetItemsBlock(),
   SQL method name is get_items_block (snake_case, no "service" prefix)
9. PHP serialized content strings: compute byte-lengths in Stage 4, use placeholder {SERIALIZE} here
```

**Required files — every module must include all of these:**

| File | Notes |
|------|-------|
| `install/config.php` | Must include `compatible_with: ['14.0.x']` |
| `install/installer.php` | Extends `BxDolStudioInstaller` |
| `install/langs/en.xml` | All language keys referenced in SQL |
| `install/sql/install.sql` | Main install — see Stage 4 for serialization |
| `install/sql/uninstall.sql` | Full rollback of all SQL |
| `install/sql/enable.sql` | Can be empty but must exist |
| `install/sql/disable.sql` | Can be empty but must exist |
| `install/sql/upgrade.sql` | Can be empty but must exist |
| `classes/{Prefix}Config.php` | Extends `BxDolModuleConfig` |
| `classes/{Prefix}Db.php` | Extends `BxDolModuleDb` |
| `classes/{Prefix}Module.php` | Extends `BxDolModule` |
| `classes/{Prefix}Template.php` | Extends `BxDolModuleTemplate` |
| `template/css/main.css` | Module styles |
| `template/images/icons/` | std-wi.png, std-pi.png, std-si.png, std-mi.png |
| `request.php` | Standard request handler |

---

#### Stage 4 — PHP Serialization Computation (Critical)

> ⚠️ **THIS IS THE #1 FAILURE POINT.** The `content` field in `sys_pages_blocks` uses PHP-serialized arrays. The integer `s:N:` must equal the exact byte-length of the string that follows. Wrong values cause blocks to load blank with no error output.

**Formula for a 2-key service block:**
```
a:2:{s:6:"module";s:N:"<module_prefix>";s:6:"method";s:M:"<method_name>";}
```

Where:
- `N` = `strlen("<module_prefix>")` — byte count, not character count
- `M` = `strlen("<method_name>")` — byte count of snake_case method name

**Computation rules:**
- Count bytes, not characters (matters for non-ASCII, though module names should be ASCII)
- The method string is snake_case WITHOUT the `service` prefix
- Always recompute — never copy N/M values from memory or examples

**Example — correct computation for module `sa_ward_councilor`:**
```
Module prefix: sa_ward_councilor  → strlen = 17
Method: get_items_block           → strlen = 15

Result:
a:2:{s:6:"module";s:17:"sa_ward_councilor";s:6:"method";s:15:"get_items_block";}
```

**Verification step:** After computing, scan every `s:N:` in the SQL and verify `N == strlen(following string)`. Any mismatch must be corrected before proceeding.

**For modules built in Perplexity with MCP active:** The assistant will compute all serialized strings automatically and verify them before writing.

---

#### Stage 5 — Write & Read-Back Verify

When MCP is active, files are NOT considered written until read-back is confirmed.

```
FOR EACH FILE:
1. write_file(path, content)
2. read_file(path) — verify content matches
3. If read-back fails or mismatches → retry write, then report error
4. Log: "✓ written and verified" or "✗ write failed: [reason]"
```

**Do not proceed to Stage 6 if critical files (install.sql, Module.php) failed write-back.**

---

#### Stage 6 — Structural Validation

After all files are written, validate the complete module structure.

```
VALIDATION CHECKLIST (all must pass before declaring module ready):

File presence:
☐ All 15 required files exist on disk
☐ install/config.php contains compatible_with: 14.0.x
☐ install/config.php db_prefix matches {vendor}_{module}

SQL correctness:
☐ sys_menu_items INSERT has exactly N columns (confirmed in Stage 1)
☐ No v15 columns present: title_attr, info, area_label, persistent
☐ sys_pages_blocks content: all s:N: values verified correct
☐ sys_objects_page uses UNIX_TIMESTAMP() for `added`
☐ uninstall.sql fully reverses all install.sql changes

PHP correctness:
☐ Each PHP file has exactly ONE class
☐ No parseHtmlByName() override in Template class
☐ All service methods named serviceXxx() in Module class
☐ SQL escaping uses addslashes() + quotes or BxDolDb::prepare()
☐ No raw $_GET/$_POST — uses bx_process_input() / bx_get()

Language:
☐ All _{prefix}_xxx keys used in SQL have entries in en.xml
☐ No untranslated hardcoded strings in PHP service methods
```

**If any validation check fails:** Fix the specific issue using a targeted patch. Do not regenerate the entire module unless the failure is systemic.

---

### Pipeline Summary Card

```
┌─────────────────────────────────────────────────────────────────┐
│  MODFORGE PIPELINE — EXECUTE IN ORDER, NO SKIPPING             │
├────┬──────────────────────┬──────────────────────────────────── │
│ 1  │ MCP Connect          │ Verify VPS live, get column counts  │
│ 2  │ Read Reference       │ Read bx_events or similar from VPS  │
│ 3  │ Generate Files       │ All 15 files, constraints applied   │
│ 4  │ Serialize            │ Compute ALL s:N: byte-lengths       │
│ 5  │ Write + Verify       │ write_file → read_file confirm      │
│ 6  │ Validate             │ Full checklist, fix before done     │
└────┴──────────────────────┴─────────────────────────────────────┘
```

---

### Quick-Start Command for AI Sessions

When starting a new module development session, provide this command:

```
FORGE MODULE:
- Vendor: {vendor}
- Module: {module_name}
- Title: {Human Title}
- Type: content|service|widget
- Features: acl, menu, storage, alerts, create_form (list what's needed)
- Reference: bx_events (or specify another)

Execute full ModForge pipeline: Stage 1 → Stage 2 → Stage 3 → Stage 4 → Stage 5 → Stage 6.
Do not present files until serialization is verified (Stage 4 complete).
```

---

### Patch Mode

When a module is already generated and needs a targeted fix:

```
PATCH MODULE: {prefix}
Issue: [describe the specific problem]
Affected file(s): [list if known]

Rules:
- Read the current file from VPS before patching
- Apply minimal change — do not regenerate unaffected files
- Recompute any affected serialized strings after patching
- Write patched file and verify read-back
- Re-run validation checklist for affected sections only
```

---


## Overview

UNACMS (formerly BoonEx Dolphin) is a modular social networking platform. This guide focuses specifically on **version 14.0.0**, which has significant differences from version 15.x.

### Key Differences: UNACMS 14 vs 15

| Feature | UNACMS 14 | UNACMS 15 |
|---------|-----------|-----------|
| `sys_menu_items` columns | 30 columns | 34 columns (adds: `title_attr`, `info`, `area_label`, `persistent`) |
| Template method signature | Simpler | Additional parameters |
| PHP version | 7.4+ | 8.0+ |
| MySQL engine | MYISAM (default) | InnoDB |

---

## Module Structure

```
modules/
└── {vendor}/
    └── {module_name}/
        ├── classes/
        │   ├── {Prefix}Config.php
        │   ├── {Prefix}Db.php
        │   ├── {Prefix}Module.php
        │   └── {Prefix}Template.php
        ├── install/
        │   ├── config.php
        │   ├── installer.php
        │   ├── langs/
        │   │   └── en.xml
        │   └── sql/
        │       ├── install.sql
        │       ├── uninstall.sql
        │       ├── enable.sql
        │       ├── disable.sql
        │       └── upgrade.sql
        ├── template/
        │   ├── css/
        │   │   └── main.css
        │   ├── images/
        │   │   └── icons/
        │   │       ├── std-wi.png
        │   │       ├── std-pi.png
        │   │       ├── std-si.png
        │   │       └── std-mi.png
        │   └── *.html
        └── request.php
```

---

## Naming Conventions

### Module Naming

- **Module name format**: `{vendor}_{module}` (e.g., `sa_support_scheme`)
- **`db_prefix` in config.php**: Module name WITHOUT trailing underscore (e.g., `sa_support_scheme`) — the system appends `_` automatically
- **Class prefix**: CamelCase version (e.g., `SaSupportScheme`)

### Examples

| Module Name | DB Prefix | Class Prefix | Folder |
|-------------|-----------|--------------|--------|
| `sa_support_scheme` | `sa_support_scheme_` | `SaSupportScheme` | `sa/support_scheme/` |
| `sa_ward_councilor` | `sa_ward_councilor_` | `SaWardCouncilor` | `sa/ward_councilor/` |
| `bx_events` | `bx_events_` | `BxEvents` | `boonex/events/` |

---

## Configuration File

### File: `install/config.php`

```php
<?php 
/**
 * @defgroup    {ModuleName} {Module Title}
 * @ingroup     {Vendor}Modules
 * @{
 */

$aConfig = array(
    /**
     * Main Section
     */
    'type' => BX_DOL_MODULE_TYPE_MODULE,
    'name' => '{vendor}_{module}',           // Module name
    'title' => '{Module Title}',              // Display title
    'note' => '{Module description}',         // Description
    'version' => '1.0.0',                     // Version
    'vendor' => '{Vendor Name}',              // Vendor name
    'product_url' => 'https://example.com',   // Product URL
    'update_url' => '',                       // Update URL
    
    /**
     * Compatibility - IMPORTANT: Set to 14.0.x for UNACMS 14
     */
    'compatible_with' => array(
        '14.0.x'
    ),

    /**
     * Paths - Must be unique
     */
    'home_dir' => '{vendor}/{module}/',       // Directory path
    'home_uri' => '{module}',                  // URI path
    
    /**
     * Prefixes
     */
    'db_prefix' => '{vendor}_{module}',       // Database table prefix
    'class_prefix' => '{Prefix}',              // Class name prefix (CamelCase)

    /**
     * Language Category
     */
    'language_category' => '{Module Title}',

    /**
     * Installation Settings
     */
    'install' => array(
        'execute_sql' => 1,
        'update_languages' => 1,
    ),
    'uninstall' => array(
        'execute_sql' => 1,
        'update_languages' => 1,
    ),
    'enable' => array(
        'execute_sql' => 1,
        'recompile_global_paramaters' => 1,
        'clear_db_cache' => 1,
    ),
    'disable' => array(
        'execute_sql' => 1,
        'recompile_global_paramaters' => 1,
        'clear_db_cache' => 1,
    ),

    /**
     * Dependencies
     */
    'dependencies' => array(
        // 'bx_core' => '1.0.0',  // Example dependency
    ),
);

/** @} */
```

---

## Database Development

### UNACMS 14 Database Table Guidelines

```sql
-- Example table for UNACMS 14
CREATE TABLE IF NOT EXISTS `{prefix}_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `author_id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Database Class (`{Prefix}Db.php`)

```php
<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleDb');

class {Prefix}Db extends BxDolModuleDb
{
    function __construct(&$oConfig) 
    {
        parent::__construct($oConfig);
    }

    /**
     * Get items with filtering
     */
    function getItems($aParams = array())
    {
        $sWhere = " WHERE 1 ";
        $sOrder = " ORDER BY `created` DESC ";
        $sLimit = "";
        
        // Status filter - ALWAYS use addslashes() or quote manually
        if(isset($aParams['status'])) {
            $sStatus = addslashes($aParams['status']);
            $sWhere .= " AND `status` = '$sStatus'";
        }
        
        // Author filter - Cast to int for safety
        if(isset($aParams['author_id'])) {
            $sWhere .= " AND `author_id` = " . (int)$aParams['author_id'];
        }
        
        // Search filter
        if(isset($aParams['search'])) {
            $sSearch = addslashes($aParams['search']);
            $sWhere .= " AND (`title` LIKE '%$sSearch%' OR `description` LIKE '%$sSearch%')";
        }
        
        // Limit
        if(isset($aParams['limit'])) {
            $sLimit = " LIMIT " . (int)$aParams['limit'];
        }
        
        $sSql = "SELECT * FROM `{prefix}_items` " . $sWhere . $sOrder . $sLimit;
        return $this->getAll($sSql);
    }

    /**
     * Get single item by ID
     */
    function getItem($iId)
    {
        $sSql = "SELECT * FROM `{prefix}_items` WHERE `id` = " . (int)$iId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    /**
     * Add new item
     */
    function addItem($aData)
    {
        $sTitle = addslashes($aData['title']);
        $sDescription = addslashes($aData['description']);
        $iAuthor = (int)$aData['author_id'];
        $sStatus = addslashes($aData['status']);
        $sCreated = addslashes($aData['created']);
        
        $sSql = "INSERT INTO `{prefix}_items` 
            (`title`, `description`, `author_id`, `status`, `created`) 
            VALUES ('$sTitle', '$sDescription', $iAuthor, '$sStatus', '$sCreated')";
        
        return $this->query($sSql);
    }

    /**
     * Update item
     */
    function updateItem($iId, $aData)
    {
        $aUpdates = array();
        
        if(isset($aData['title'])) {
            $aUpdates[] = "`title` = '" . addslashes($aData['title']) . "'";
        }
        if(isset($aData['description'])) {
            $aUpdates[] = "`description` = '" . addslashes($aData['description']) . "'";
        }
        if(isset($aData['status'])) {
            $aUpdates[] = "`status` = '" . addslashes($aData['status']) . "'";
        }
        
        if(empty($aUpdates)) return false;
        
        $sSql = "UPDATE `{prefix}_items` SET " . implode(', ', $aUpdates) . 
                " WHERE `id` = " . (int)$iId;
        
        return $this->query($sSql);
    }

    /**
     * Delete item
     */
    function deleteItem($iId)
    {
        return $this->query("DELETE FROM `{prefix}_items` WHERE `id` = " . (int)$iId);
    }
}
```

### ⚠️ CRITICAL: SQL Escaping in UNACMS

**NEVER use the `escape()` method directly** - it does NOT add quotes!

```php
// ❌ WRONG - Will cause SQL errors
$sWhere .= " AND `status` = " . $this->escape($aParams['status']);

// ✅ CORRECT - Use addslashes() with manual quotes
$sWhere .= " AND `status` = '" . addslashes($aParams['status']) . "'";

// ✅ CORRECT - Cast integers
$sWhere .= " AND `author_id` = " . (int)$aParams['author_id'];
```

---

## PHP Class Files

### Config Class (`{Prefix}Config.php`)

```php
<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleConfig');

class {Prefix}Config extends BxDolModuleConfig
{
    function __construct($aModule)
    {
        parent::__construct($aModule);
    }
}
```

### Module Class (`{Prefix}Module.php`)

```php
<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModule');

class {Prefix}Module extends BxDolModule 
{
    function __construct(&$aModule) 
    {
        parent::__construct($aModule);
    }

    /**
     * Service: Get content block for page
     * Called via: module=vendor_module&method=get_items_block
     */
    function serviceGetItemsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        $aItems = $this->_oDb->getItems(array('status' => 'active'));
        
        $sContent = '';
        if(empty($aItems)) {
            $sContent = '<div class="empty-state">
                <h3>No Items Found</h3>
                <p>Create your first item to get started!</p>
            </div>';
        } else {
            foreach($aItems as $aItem) {
                $sContent .= $this->_renderItemCard($aItem);
            }
        }
        
        return '<div class="module-wrapper">' . $sContent . '</div>';
    }

    /**
     * Render item card (internal method)
     */
    protected function _renderItemCard($aItem)
    {
        $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-item&id=' . $aItem['id'];
        
        return '<div class="item-card">
            <h3><a href="' . $sUrl . '">' . htmlspecialchars($aItem['title']) . '</a></h3>
            <p>' . htmlspecialchars(substr($aItem['description'], 0, 150)) . '</p>
        </div>';
    }
}
```

### Template Class (`{Prefix}Template.php`)

```php
<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleTemplate');

class {Prefix}Template extends BxDolModuleTemplate 
{    
    function __construct(&$oConfig, &$oDb) 
    {
        parent::__construct($oConfig, $oDb);
    }
}
```

**⚠️ DO NOT override `parseHtmlByName()` in UNACMS 14** - The parent class handles it correctly. Overriding causes fatal errors.

---

## Template System

### CSS File (`template/css/main.css`)

```css
/* Module Styles */
.module-wrapper {
    padding: 20px;
}

.item-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    background: #fff;
}

.item-card h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
}

.item-card h3 a {
    color: #333;
    text-decoration: none;
}

.item-card h3 a:hover {
    color: #0073aa;
}

.item-card p {
    color: #666;
    margin: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: #333;
}

.empty-state p {
    color: #666;
    margin: 0 0 16px 0;
}

/* Buttons */
.btn-primary {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
}

.btn-primary:hover {
    background: #005a87;
    color: #fff;
}
```

### HTML Templates (`template/*.html`)

UNACMS uses HTML templates with variable placeholders:

```html
<!-- template/item_card.html -->
<div class="item-card">
    <h3><a href="__url__">__title__</a></h3>
    <p>__description__</p>
    <div class="meta">
        <span>By __author__</span>
        <span>__date__</span>
    </div>
</div>
```

---

## Language Files

### File: `install/langs/en.xml`

```xml
<?xml version="1.0" encoding="utf-8"?>
<resources name="en" flag="gb" title="English">
    <!-- Module Name -->
    <string name="_{prefix}"><![CDATA[Module Title]]></string>
    
    <!-- Admin Settings -->
    <string name="_{prefix}_adm_stg_cpt_type"><![CDATA[Module Title]]></string>
    <string name="_{prefix}_adm_stg_cpt_category_general"><![CDATA[General Settings]]></string>
    
    <!-- Pages -->
    <string name="_{prefix}_page_list_sys"><![CDATA[Items]]></string>
    <string name="_{prefix}_page_list"><![CDATA[All Items]]></string>
    <string name="_{prefix}_page_view_sys"><![CDATA[Item Details]]></string>
    <string name="_{prefix}_page_view"><![CDATA[View Item]]></string>
    <string name="_{prefix}_page_create_sys"><![CDATA[Create Item]]></string>
    <string name="_{prefix}_page_create"><![CDATA[Create New Item]]></string>
    
    <!-- Blocks -->
    <string name="_{prefix}_block_list"><![CDATA[Items List]]></string>
    <string name="_{prefix}_block_view"><![CDATA[Item Details]]></string>
    <string name="_{prefix}_block_create"><![CDATA[Create Item]]></string>
    
    <!-- Menu -->
    <string name="_{prefix}_menu_item_sys"><![CDATA[Items]]></string>
    <string name="_{prefix}_menu_item"><![CDATA[My Items]]></string>
    
    <!-- Form Labels -->
    <string name="_{prefix}_field_title"><![CDATA[Title]]></string>
    <string name="_{prefix}_field_description"><![CDATA[Description]]></string>
    
    <!-- Messages -->
    <string name="_{prefix}_no_items"><![CDATA[No items found.]]></string>
    <string name="_{prefix}_item_created"><![CDATA[Item created successfully.]]></string>
    <string name="_{prefix}_error_required"><![CDATA[This field is required.]]></string>
</resources>
```

---

## SQL Installation Files

### install.sql - Main Installation

```sql
-- =====================================================
-- MODULE INSTALLATION FOR UNACMS 14.0.0
-- =====================================================

-- STUDIO WIDGET
INSERT INTO `sys_std_pages`(`index`, `name`, `header`, `caption`, `icon`) VALUES
(3, '{prefix}', '_{prefix}', '_{prefix}', '{prefix}@modules/{path}/|std-pi.png');

SET @iPageId = LAST_INSERT_ID();
SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);

INSERT INTO `sys_std_widgets` (`page_id`, `module`, `url`, `click`, `icon`, `caption`, `cnt_notices`, `cnt_actions`) VALUES
(@iPageId, '{prefix}', '{url_studio}module.php?name={prefix}', '', '{prefix}@modules/{path}/|std-wi.png', '_{prefix}', '', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');

INSERT INTO `sys_std_pages_widgets` (`page_id`, `widget_id`, `order`) VALUES
(@iParentPageId, LAST_INSERT_ID(), @iParentPageOrder);

-- =====================================================
-- DATABASE TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `{prefix}_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `author_id` int(11) NOT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PAGES
-- =====================================================

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), '{prefix}_items', '{uri}-items', '_{prefix}_page_list_sys', '_{prefix}_page_list', '{prefix}', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i={uri}-items', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), '{prefix}_item', 'view-{uri}-item', '_{prefix}_page_view_sys', '_{prefix}_page_view', '{prefix}', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=view-{uri}-item', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

INSERT INTO `sys_objects_page` (`author`, `added`, `object`, `uri`, `title_system`, `title`, `module`, `cover`, `cover_image`, `cover_title`, `type_id`, `layout_id`, `sticky_columns`, `submenu`, `visible_for_levels`, `visible_for_levels_editable`, `url`, `content_info`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `cache_lifetime`, `cache_editable`, `inj_head`, `inj_footer`, `config_api`, `deletable`, `override_class_name`, `override_class_file`) VALUES
(0, UNIX_TIMESTAMP(), '{prefix}_create', 'create-{uri}-item', '_{prefix}_page_create_sys', '_{prefix}_page_create', '{prefix}', 1, 0, '', 1, 5, 0, '', 2147483647, 1, 'page.php?i=create-{uri}-item', '', '', '', '', '', 0, 1, '', '', '', 0, '', '');

-- =====================================================
-- PAGE BLOCKS
-- =====================================================

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('{prefix}_items', 1, '{prefix}', '', '_{prefix}_block_list', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:{len}:"{prefix}";s:6:"method";s:16:"get_items_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('{prefix}_item', 1, '{prefix}', '', '_{prefix}_block_view', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:{len}:"{prefix}";s:6:"method";s:21:"get_item_detail_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

INSERT INTO `sys_pages_blocks` (`object`, `cell_id`, `module`, `title_system`, `title`, `designbox_id`, `class`, `submenu`, `tabs`, `async`, `visible_for_levels`, `hidden_on`, `type`, `content`, `content_empty`, `text`, `text_updated`, `help`, `cache_lifetime`, `config_api`, `deletable`, `copyable`, `active`, `active_api`, `order`) VALUES
('{prefix}_create', 1, '{prefix}', '', '_{prefix}_block_create', 11, '', '', 0, 0, 2147483647, '', 'service', 'a:2:{s:6:"module";s:{len}:"{prefix}";s:6:"method";s:22:"get_create_item_block";}', '', '', 0, '', 0, '', 0, 1, 1, 0, 1);

-- =====================================================
-- MENU ITEMS - UNACMS 14.0.0 Compatible
-- DO NOT include: title_attr, info, area_label, persistent
-- =====================================================

SET @iMenuOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `parent_id` = 0);

INSERT INTO `sys_menu_items` (`parent_id`, `set_name`, `module`, `name`, `title_system`, `title`, `link`, `onclick`, `target`, `icon`, `addon`, `addon_cache`, `markers`, `submenu_object`, `submenu_popup`, `visible_for_levels`, `visibility_custom`, `hidden_on`, `hidden_on_cxt`, `hidden_on_pt`, `hidden_on_col`, `config_api`, `primary`, `collapsed`, `active`, `active_api`, `copyable`, `editable`, `order`) VALUES
(0, 'sys_site', '{prefix}', '{uri}', '_{prefix}_menu_item_sys', '_{prefix}_menu_item', 'page.php?i={uri}-items', '', '', 'folder col-blue3', '', 0, '', '', 0, 2147483647, '', '', '', 0, 0, '', 0, 0, 1, 0, 1, 1, @iMenuOrder);
```

### uninstall.sql

```sql
-- Remove menu items
DELETE FROM `sys_menu_items` WHERE `module` = '{prefix}';

-- Remove page blocks
DELETE FROM `sys_pages_blocks` WHERE `module` = '{prefix}';

-- Remove pages
DELETE FROM `sys_objects_page` WHERE `module` = '{prefix}';

-- Remove studio widget
SET @iWidgetId = (SELECT `widget_id` FROM `sys_std_pages_widgets` WHERE `page_id` = (SELECT `id` FROM `sys_std_pages` WHERE `name` = '{prefix}'));
DELETE FROM `sys_std_pages_widgets` WHERE `widget_id` = @iWidgetId;
DELETE FROM `sys_std_widgets` WHERE `id` = @iWidgetId;
DELETE FROM `sys_std_pages` WHERE `name` = '{prefix}';

-- Drop tables
DROP TABLE IF EXISTS `{prefix}_items`;
```

### enable.sql

```sql
-- Enable module settings (if needed)
```

### disable.sql

```sql
-- Disable module settings (if needed)
```

---

## Service Methods

Services are the primary way to expose module functionality to pages.

### Defining Services

```php
// In {Prefix}Module.php

/**
 * Service methods MUST start with "service" prefix
 * Method name after "service" becomes available to pages
 */

// serviceGetItemsBlock() becomes: method=get_items_block
function serviceGetItemsBlock()
{
    $this->_oTemplate->addCss(array('main.css'));
    // ... return HTML content
}

// serviceGetItemDetailBlock() becomes: method=get_item_detail_block
function serviceGetItemDetailBlock()
{
    $iItemId = (int)bx_get('id');
    // ... return HTML content
}

// serviceGetCreateItemBlock() becomes: method=get_create_item_block
function serviceGetCreateItemBlock()
{
    if(!isLogged()) {
        return '<div class="error">Please log in to continue.</div>';
    }
    // ... return form HTML
}

// serviceGetConfig() - returns configuration values
function serviceGetConfig($sKey)
{
    return getParam('{prefix}_' . $sKey);
}
```

### Calling Services in SQL

```sql
-- In page blocks, use serialized array for service content
INSERT INTO `sys_pages_blocks` (..., `type`, `content`, ...) VALUES
(..., 'service', 'a:2:{s:6:"module";s:17:"sa_support_scheme";s:6:"method";s:19:"get_items_block";}', ...);
```

The serialized array format:
```
a:2:{s:6:"module";s:17:"module_name";s:6:"method";s:12:"method_name";}
```

---

## Pages and Blocks

### Page Object Structure

```sql
INSERT INTO `sys_objects_page` (
    `author`,           -- 0 for system pages
    `added`,            -- UNIX_TIMESTAMP()
    `object`,           -- Page object name (unique identifier)
    `uri`,              -- URL slug (used in page.php?i=uri)
    `title_system`,     -- System title (language key)
    `title`,            -- Display title (language key)
    `module`,           -- Module name
    `cover`,            -- 1 or 0
    `cover_image`,      -- Cover image ID or 0
    `cover_title`,      -- Cover title text
    `type_id`,          -- 1 = default
    `layout_id`,        -- Layout ID (5 = common)
    `sticky_columns`,   -- 0 or 1
    `submenu`,          -- Submenu object name
    `visible_for_levels`, -- 2147483647 = all levels
    `visible_for_levels_editable`, -- 1
    `url`,              -- Canonical URL
    `content_info`,     -- Content info object
    `meta_title`,       -- SEO title
    `meta_description`, -- SEO description
    `meta_keywords`,    -- SEO keywords
    `meta_robots`,      -- SEO robots
    `cache_lifetime`,   -- 0 = no cache
    `cache_editable`,   -- 1
    `inj_head`,         -- Head injection
    `inj_footer`,       -- Footer injection
    `config_api`,       -- API config
    `deletable`,        -- 0
    `override_class_name`, -- Custom class name
    `override_class_file`  -- Custom class file
) VALUES (...);
```

### Block Object Structure

```sql
INSERT INTO `sys_pages_blocks` (
    `object`,           -- Page object name
    `cell_id`,          -- Cell position (1, 2, 3...)
    `module`,           -- Module name
    `title_system`,     -- System title
    `title`,            -- Display title (language key)
    `designbox_id`,     -- Design box style (11 = common)
    `class`,            -- CSS class
    `submenu`,          -- Submenu
    `tabs`,             -- 0 or 1
    `async`,            -- 0 or 1 for async loading
    `visible_for_levels`, -- 2147483647 = all
    `hidden_on`,        -- Hidden on devices
    `type`,             -- 'service', 'text', 'raw', 'html', 'rss', 'image'
    `content`,          -- Content (service array or text)
    `content_empty`,    -- Empty content message
    `text`,             -- Text content
    `text_updated`,     -- Text update timestamp
    `help`,             -- Help text
    `cache_lifetime`,   -- Cache duration
    `config_api`,       -- API config
    `deletable`,        -- 0 or 1
    `copyable`,         -- 1
    `active`,           -- 1 = active
    `active_api`,       -- 0 or 1
    `order`             -- Display order
) VALUES (...);
```

---

## Menu Items

### UNACMS 14 Menu Items Table Structure

**Available columns (30 total):**
```
id, parent_id, set_name, module, name, title_system, title, link, onclick, target, 
icon, addon, addon_cache, markers, submenu_object, submenu_popup, visible_for_levels, 
visibility_custom, hidden_on, hidden_on_cxt, hidden_on_pt, hidden_on_col, config_api, 
primary, collapsed, active, active_api, copyable, editable, order
```

**NOT in UNACMS 14 (only in v15):**
- `title_attr`
- `info`
- `area_label`
- `persistent`

### Menu Item SQL

```sql
SET @iMenuOrder = (SELECT IFNULL(MAX(`order`), 0) + 1 FROM `sys_menu_items` WHERE `set_name` = 'sys_site' AND `parent_id` = 0);

INSERT INTO `sys_menu_items` (
    `parent_id`,
    `set_name`,
    `module`,
    `name`,
    `title_system`,
    `title`,
    `link`,
    `onclick`,
    `target`,
    `icon`,
    `addon`,
    `addon_cache`,
    `markers`,
    `submenu_object`,
    `submenu_popup`,
    `visible_for_levels`,
    `visibility_custom`,
    `hidden_on`,
    `hidden_on_cxt`,
    `hidden_on_pt`,
    `hidden_on_col`,
    `config_api`,
    `primary`,
    `collapsed`,
    `active`,
    `active_api`,
    `copyable`,
    `editable`,
    `order`
) VALUES (
    0,                          -- parent_id: 0 for top-level
    'sys_site',                 -- set_name: menu set
    '{prefix}',                 -- module: module name
    '{uri}',                    -- name: unique identifier
    '_{prefix}_menu_item_sys',  -- title_system: system title
    '_{prefix}_menu_item',      -- title: display title
    'page.php?i={uri}-items',   -- link: URL
    '',                         -- onclick: JavaScript onclick
    '',                         -- target: link target
    'folder col-blue3',        -- icon: icon class
    '',                         -- addon: addon content
    0,                          -- addon_cache
    '',                         -- markers
    '',                         -- submenu_object
    0,                          -- submenu_popup
    2147483647,                 -- visible_for_levels: all levels
    '',                         -- visibility_custom
    '',                         -- hidden_on
    '',                         -- hidden_on_cxt
    0,                          -- hidden_on_pt
    0,                          -- hidden_on_col
    '',                         -- config_api
    0,                          -- primary
    0,                          -- collapsed
    1,                          -- active
    0,                          -- active_api
    1,                          -- copyable
    1,                          -- editable
    @iMenuOrder                 -- order: position
);
```

---

## Input Validation & Output Escaping

Security is non-negotiable. Every piece of user input must be validated; every piece of output must be escaped.

### Input Validation

Always use `bx_process_input()` to sanitize all external data (GET, POST, COOKIE, API):

```php
// ✅ CORRECT
$iId     = bx_process_input(bx_get('id'),    BX_DATA_INT);
$sTitle  = bx_process_input(bx_get('title'), BX_DATA_TEXT);
$sBody   = bx_process_input(bx_get('body'),  BX_DATA_HTML);
$sEmail  = bx_process_input(bx_get('email'), BX_DATA_EMAIL);

// ❌ WRONG - never use raw $_GET/$_POST
$iId = $_GET['id'];
```

| Constant | Use for |
|----------|---------|
| `BX_DATA_INT` | Integer values |
| `BX_DATA_TEXT` | Plain text (strips HTML) |
| `BX_DATA_HTML` | Rich text (sanitized HTML) |
| `BX_DATA_EMAIL` | Email addresses |

### Prepared Statements (Preferred for Complex Queries)

Use `BxDolDb::prepare()` for parameterized queries — this is the modern, preferred approach:

```php
// ✅ PREFERRED - prepared statement
$oDb = BxDolDb::getInstance();
$sSql = $oDb->prepare("SELECT * FROM `{prefix}_items` WHERE `author_id` = ? AND `status` = ?", $iAuthorId, $sStatus);
$aItems = $oDb->getAll($sSql);

// ✅ ALSO CORRECT - manual escaping for simple cases
$sWhere = " AND `author_id` = " . (int)$iAuthorId;
$sWhere .= " AND `status` = '" . addslashes($sStatus) . "'";
```

### Output Escaping

```php
// Plain text in HTML
echo bx_process_output($aItem['title'], BX_DATA_TEXT);

// HTML attribute values
echo '<input value="' . bx_html_attribute($aItem['title']) . '">';

// JavaScript strings
echo '<script>var title = "' . bx_js_string($aItem['title']) . '";</script>';

// URLs
echo '<a href="' . bx_html_attribute($sUrl) . '">';
```

**Never use raw `htmlspecialchars()` alone** — use the UNA output functions which handle encoding correctly for the platform context.

---

## Hooks & Alerts System

UNA uses an alerts/hooks system to allow modules to react to events fired by other modules — without modifying core files.

### Firing an Alert (from your module)

```php
// bx_alert($sUnit, $sAction, $iObjectId, $iSenderId, $aExtras)
bx_alert('my_module', 'item_created', $iItemId, getLoggedId(), array(
    'item' => $aItem,
));
```

### Listening to an Alert (responding to another module's event)

Register your response handler in your module's `__construct` or a dedicated method:

```php
// In {Prefix}Module.php
function __construct(&$aModule)
{
    parent::__construct($aModule);

    $this->_oAlerts = BxDolAlerts::getInstance();
    $this->_oAlerts->subscribe('profile', 'delete', $this);
}

// Handler method - called when the alert fires
function responseDeleteProfile($oAlert)
{
    $iProfileId = $oAlert->iObject;
    // Clean up data related to this profile
    $this->_oDb->deleteItemsByAuthor($iProfileId);
}
```

### Common System Alerts to Hook Into

| Unit | Action | Fired when |
|------|--------|-----------|
| `profile` | `delete` | A profile is deleted |
| `profile` | `edit` | A profile is edited |
| `system` | `page_output_add_header` | Page header is being built |
| `system` | `form_output` | A form is being rendered |

**Key rule:** Never modify core files to add behavior — always use alerts/hooks.

---

## Storage System

Use `BxDolStorage` for all file handling. Never write files directly to the filesystem.

### Getting a Storage Object

```php
// Get storage object by its configured name
$oStorage = BxDolStorage::getObjectInstance('my_module_files');
if (!$oStorage)
    return false;
```

### Uploading a File

```php
// Upload from $_FILES
$iFileId = $oStorage->storeFileFromForm($_FILES['file'], false, getLoggedId());
if ($iFileId === false) {
    $sError = $oStorage->getErrorString();
}
```

### Getting a File URL

```php
// Get public URL for a stored file
$sUrl = $oStorage->getFileUrlById($iFileId);

// Get file info
$aFile = $oStorage->getFile($iFileId);
// $aFile['file_name'], $aFile['mime_type'], $aFile['size'], etc.
```

### Deleting a File

```php
$oStorage->deleteFile($iFileId, getLoggedId());
```

### Registering a Storage Object in install.sql

```sql
INSERT INTO `sys_objects_storage` (`object`, `backend_type`, `backend_params`, `cache_control`, `levels`, `table_files`, `ext_mode`, `ext_allow`, `ext_deny`, `quota_size`, `current_size`, `quota_number`, `current_number`, `max_file_size`, `ts`) VALUES
('my_module_files', 'Local', '', 2592000, 3, 'my_module_files', 'allow-deny', 'jpg,jpeg,png,gif,pdf,zip', '', 0, 0, 0, 0, 0, UNIX_TIMESTAMP());
```

---

## Permissions & Access Control

### Checking if a User is Logged In

```php
if (!isLogged()) {
    return MsgBox(_t('_sys_txt_access_denied'));
}
```

### Checking ACL Permissions

```php
$oACL = BxDolACL::getInstance();

// Check if current user has a specific action allowed
if (!$oACL->isAllowed('my_module', 'create_item', true)) {
    return MsgBox(_t('_sys_txt_access_denied'));
}
```

### Registering ACL Actions in install.sql

```sql
INSERT INTO `sys_acl_actions` (`Module`, `Name`, `AdditionalParamName`, `Title`, `Desc`, `Countable`, `DisabledForLevels`) VALUES
('my_module', 'create item', NULL, '_my_module_acl_action_create_item', '', 1, 3);

SET @iActionId = LAST_INSERT_ID();

-- Allow for standard members (level 3) and above
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`, `AllowedCount`, `AllowedPeriodLen`, `AllowedPeriodStart`, `AllowedPeriodEnd`, `DisabledHeader`, `Disabled`) VALUES
(3, @iActionId, NULL, NULL, NULL, NULL, 0, 0);
```

---

## Cross-Module Service Calls

### Calling a Service from Another Module

```php
// BxDolService::call($sModule, $sMethod, $aParams, $sClass)
$mResult = BxDolService::call('other_module', 'get_items_block', array(), 'Module');

// With parameters
$mResult = BxDolService::call('bx_timeline', 'get_block_view', array($iProfileId), 'Module');
```

### Calling Your Own Services Internally

```php
// Within your module class
$sHtml = $this->serviceGetItemsBlock();
```

### Checking if a Module Exists Before Calling

```php
if (BxDolModuleQuery::getInstance()->getModuleByName('bx_timeline')) {
    $mResult = BxDolService::call('bx_timeline', 'get_block_view', array($iProfileId), 'Module');
}
```

---

## Common Pitfalls

### 1. SQL Column Mismatch

**Problem:** Using v15 columns in v14 install.sql
```
Error: Unknown column 'title_attr' in 'INSERT INTO'
```

**Solution:** Always check table structure before writing install.sql:
```sql
DESCRIBE sys_menu_items;
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='sys_menu_items' AND TABLE_SCHEMA='your_database';
```

### 2. Template Method Override Error

**Problem:** Overriding parseHtmlByName() with wrong signature
```
Fatal error: Declaration of MyTemplate::parseHtmlByName() must be compatible with BxDolTemplate::parseHtmlByName()
```

**Solution:** Don't override parseHtmlByName() in UNACMS 14:
```php
// ❌ DON'T DO THIS
class MyTemplate extends BxDolModuleTemplate {
    function parseHtmlByName($sName, $aVariables = array()) {
        return parent::parseHtmlByName($sName, $aVariables);
    }
}

// ✅ DO THIS
class MyTemplate extends BxDolModuleTemplate {
    function __construct(&$oConfig, &$oDb) {
        parent::__construct($oConfig, $oDb);
    }
    // Let parent handle parseHtmlByName()
}
```

### 3. SQL Escape Issues

**Problem:** Using escape() without quotes causes SQL errors
```php
// ❌ WRONG - escape() doesn't add quotes
$sWhere .= " AND `status` = " . $this->escape($aParams['status']);

// ✅ CORRECT - add quotes manually
$sWhere .= " AND `status` = '" . addslashes($aParams['status']) . "'";
```

### 4. Module Not Enabled

**Problem:** Module installed but pages show headings only

**Solution:** Check and enable the module:
```sql
SELECT name, enabled FROM sys_modules WHERE name = 'your_module';
UPDATE sys_modules SET enabled = 1 WHERE name = 'your_module';
```

### 5. Language Strings Not Loading

**Problem:** Page titles show as `_module_page_title`

**Solutions:**
1. Reinstall the module
2. Clear cache: `rm -rf cache/*.php`
3. Check language file format in `install/langs/en.xml`

### 6. File Permissions

**Problem:** Module files not readable

**Solution:**
```bash
chown -R www-data:www-data /var/www/una/modules/your_module/
chmod -R 755 /var/www/una/modules/your_module/
```

---

## Debugging

### Check Log Files

```bash
# UNACMS logs
tail -f /var/www/una/logs/db.err.log
tail -f /var/www/una/logs/sys_modules.log

# Apache logs
tail -f /var/log/apache2/error.log

# PHP errors
tail -f /var/log/php_errors.log
```

### Check Module Registration

```sql
-- Check if module is registered
SELECT * FROM sys_modules WHERE name = 'your_module';

-- Check pages
SELECT object, uri, title FROM sys_objects_page WHERE module = 'your_module';

-- Check blocks
SELECT object, title, type, content FROM sys_pages_blocks WHERE module = 'your_module';

-- Check tables
SHOW TABLES LIKE 'your_prefix_%';

-- Check menu items
SELECT * FROM sys_menu_items WHERE module = 'your_module';
```

### Enable PHP Error Display (Development Only)

```php
// In inc/header.inc.php temporarily add:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Clear Cache

```bash
rm -rf /var/www/una/cache/*.php
rm -rf /var/www/una/cache/db/*.php
```

---

## Complete Module Template

### request.php

```php
<?php defined('BX_DOL') or die('hack attempt');

require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

check_logged();

bx_import('BxDolRequest');
BxDolRequest::processAsAction($GLOBALS['aModule'], $GLOBALS['aRequest']);
```

### installer.php

```php
<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolStudioInstaller');

class {Prefix}Installer extends BxDolStudioInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }
}
```

### Full Module Checklist

- [ ] `install/config.php` - Module configuration
- [ ] `install/installer.php` - Installer class
- [ ] `install/langs/en.xml` - Language strings
- [ ] `install/sql/install.sql` - Installation SQL
- [ ] `install/sql/uninstall.sql` - Uninstallation SQL
- [ ] `install/sql/enable.sql` - Enable SQL (can be empty)
- [ ] `install/sql/disable.sql` - Disable SQL (can be empty)
- [ ] `install/sql/upgrade.sql` - Upgrade SQL (can be empty)
- [ ] `classes/{Prefix}Config.php` - Config class
- [ ] `classes/{Prefix}Db.php` - Database class
- [ ] `classes/{Prefix}Module.php` - Module class
- [ ] `classes/{Prefix}Template.php` - Template class
- [ ] `template/css/main.css` - Module styles
- [ ] `template/images/icons/` - Module icons (std-wi.png, std-pi.png, std-si.png, std-mi.png)
- [ ] `request.php` - Request handler

---

## Quick Reference

### Database Methods

```php
$this->getAll($sSql)          // Returns array of all rows
$this->getRow($sSql)          // Returns single row
$this->getOne($sSql)          // Returns single value
$this->query($sSql)           // Execute query, return affected rows
$this->getLastId()            // Get last insert ID
```

### UNACMS Helpers

```php
bx_get('param')               // Get request parameter (sanitized)
isLogged()                    // Check if user is logged in
getLoggedId()                 // Get logged in user ID
getParam('option_name')       // Get system option
BX_DOL_URL_ROOT               // Site root URL
```

### Template Methods

```php
$this->_oTemplate->addCss(array('main.css'));
$this->_oTemplate->addJs(array('script.js'));
$this->_oTemplate->parseHtmlByName('template.html', $aVariables);
```

---

## Advanced Internals

This section documents internal UNACMS mechanisms that are critical for debugging but not well-documented elsewhere.

### 21. BxTemplFormView::getCode() Parameter Behavior

The `getCode()` method in `BxBaseFormView` (which `BxTemplFormView` extends) accepts a single boolean parameter:

```php
function getCode($bDynamicMode = false)
```

#### Parameter: `$bDynamicMode`

| Value | Behavior |
|-------|----------|
| `false` (default) | Form outputs normally with standard CSS/JS includes in the page head. Use for forms rendered during initial page load. |
| `true` | Form is marked as "dynamic mode" - CSS/JS are output inline with the form HTML. Use for forms loaded via AJAX, in popups, or added dynamically to the page. |

#### Automatic Detection

The method also auto-detects dynamic context:

```php
if(!$bDynamicMode && bx_is_dynamic_request())
    $bDynamicMode = true;
```

If `bx_is_dynamic_request()` returns true (HTMX/AJAX requests), dynamic mode is automatically enabled.

#### Internal Flow

```php
function getCode($bDynamicMode = false)
{
    // 1. Auto-detect dynamic mode
    if(!$bDynamicMode && bx_is_dynamic_request())
        $bDynamicMode = true;

    $this->_bDynamicMode = $bDynamicMode;
    
    // 2. Process form attributes (replace markers)
    $this->aFormAttrs = $this->_replaceMarkers($this->aFormAttrs);

    // 3. Fire hook for code override
    bx_alert('system', 'form_output', 0, 0, [
        'dynamic' => $this->_bDynamicMode,
        'object' => &$this,
        'code' => &$this->sCode,
        'include' => &$sInclude
    ]);

    // 4. Generate form HTML if not overridden by hook
    if($this->sCode === false)
        $this->sCode = $this->genForm();

    // 5. Add CSS/JS and process for dynamic mode
    $this->addCssJs();
    $sDynamicCssJs = $this->_processCssJs();
    
    return $sInclude . $sDynamicCssJs . $this->sCode;
}
```

#### Usage Examples

```php
// Standard form (initial page load)
$oForm = BxDolForm::getObjectInstance('my_form', 'my_form_add');
echo $oForm->getCode();  // $bDynamicMode = false (default)

// Form in AJAX popup
$oForm = BxDolForm::getObjectInstance('my_form', 'my_form_add');
echo $oForm->getCode(true);  // $bDynamicMode = true - inline CSS/JS

// Form loaded via HTMX
// Auto-detected - no need to pass true
echo $oForm->getCode();  // bx_is_dynamic_request() returns true
```

---

### 22. Form Display Rendering Pipeline

The complete flow from database to HTML:

#### Database Tables Involved

```
sys_objects_form          → Form definition (object, table, action, params)
    ↓
sys_form_displays         → Display variant (display_name, view_mode, object)
    ↓
sys_form_inputs           → Input definitions (type, name, caption, checker, db)
    ↓
sys_form_display_inputs   → Display-input mapping (active, order, visible_for_levels)
```

#### Query Flow in BxDolFormQuery::getFormArray()

```php
// 1. Get form object
$sQuery = "SELECT * FROM `sys_objects_form` WHERE `active` = 1 AND `object` = ?";
$aObject = $oDb->fromMemory('sys_objects_form_' . $sObject, 'getRow', $sQuery);

// Returns FALSE if:
// - No record found
// - active != 1

// 2. Get display
$sQuery = "SELECT * FROM `sys_form_displays` WHERE `object` = ? AND `display_name` = ?";
$aDisplay = $oDb->fromMemory('sys_form_displays_' . $sObject . '_' . $sDisplayName, 'getRow', $sQuery);

// Returns FALSE if:
// - No matching display_name for this object

// 3. Get inputs with display-specific settings
$sQuery = "SELECT `i`.*, `d`.`visible_for_levels` 
           FROM `sys_form_inputs` AS `i` 
           INNER JOIN `sys_form_display_inputs` AS `d` ON (`d`.`input_name` = `i`.`name`) 
           WHERE `d`.`active` = 1 AND `d`.`display_name` = ? AND `i`.`object` = ? 
           ORDER BY `d`.`order` ASC";

$aInputs = $oDb->getAllWithKey($sQuery, 'name');
```

#### Input Processing Steps

For each input retrieved:

```php
$aInput = array (
    'id' => $a['id'],
    'type' => $a['type'],           // text, textarea, select, etc.
    'name' => $a['name'],
    'caption' => _t($a['caption']), // Translated
    'info' => $a['info'] ? _t($a['info']) : '',
    'required' => $a['required'] ? true : false,
    'value' => $a['value'],          // Default value
    'values' => /* unserialized or from data list */,
    'checker' => /* checker config */,
    'db' => /* db pass config */,
    'visible_for_levels' => $a['visible_for_levels'],
);
```

#### Memory Caching

Forms are cached in memory with keys:

```
sys_objects_form_{object_name}
sys_form_displays_{object_name}_{display_name}
```

**To clear form cache:**
```sql
-- No direct SQL cache clear - it's PHP array memory
-- Re-install module or restart PHP-FPM
```

---

### 23. BxDolForm::getObjectInstance() Failure Modes

```php
static public function getObjectInstance($sObject, $sDisplayName, $oTemplate = false, $sParam = '')
```

#### Return Values

| Return | Cause |
|--------|-------|
| `false` | Form object not found in `sys_objects_form` OR display not found in `sys_form_displays` |
| `BxTemplFormView` object | Success - form loaded from database |
| Existing instance | Previously created instance in same request (singleton pattern) |

#### Failure Reason Breakdown

```php
// Step 1: Check singleton cache
$sKey = 'BxDolForm!'.$sObject.'!'.$sDisplayName.'!'.$sParam;
if (isset($GLOBALS['bxDolClasses'][$sKey]))
    return $GLOBALS['bxDolClasses'][$sKey];  // Return cached instance

// Step 2: Load form definition
$aObject = BxDolFormQuery::getFormArray($sObject, $sDisplayName);
if (!$aObject || !is_array($aObject))
    return false;  // ← RETURNS FALSE HERE if form/display not found

// Step 3: Determine class
$sClass = 'BxTemplFormView';
if (!empty($aObject['override_class_name'])) {
    $sClass = $aObject['override_class_name'];
    if (!empty($aObject['override_class_file']))
        require_once(BX_DIRECTORY_PATH_ROOT . $aObject['override_class_file']);
}

// Step 4: Create instance
$o = new $sClass($aObject, $oTemplate);
return ($GLOBALS['bxDolClasses'][$sKey] = $o);
```

#### Common Failure Scenarios

**1. Form object doesn't exist:**
```sql
SELECT * FROM sys_objects_form WHERE object = 'your_form_object';
-- Empty result = getObjectInstance returns false
```

**2. Form object not active:**
```sql
SELECT * FROM sys_objects_form WHERE object = 'your_form_object' AND active = 1;
-- The query in getFormArray filters by active = 1
```

**3. Display doesn't exist:**
```sql
SELECT * FROM sys_form_displays WHERE object = 'your_form_object' AND display_name = 'your_display';
-- Empty result = getObjectInstance returns false
```

**4. Display has no active inputs:**
```sql
SELECT COUNT(*) FROM sys_form_display_inputs 
WHERE display_name = 'your_display' AND active = 1;
-- Zero inputs = form renders empty (not false, but empty form)
```

#### Debugging getObjectInstance Failures

```php
$oForm = BxDolForm::getObjectInstance('my_form', 'my_display');
if (!$oForm) {
    // Debug: Check each component
    $oDb = BxDolDb::getInstance();
    
    // Check form object
    $aForm = $oDb->getRow("SELECT * FROM sys_objects_form WHERE object = 'my_form'");
    if (!$aForm) {
        echo "Form object 'my_form' not found in sys_objects_form";
    } elseif ($aForm['active'] != 1) {
        echo "Form object 'my_form' is not active";
    } else {
        // Check display
        $aDisplay = $oDb->getRow("SELECT * FROM sys_form_displays WHERE object = 'my_form' AND display_name = 'my_display'");
        if (!$aDisplay) {
            echo "Display 'my_display' not found for form 'my_form'";
        }
    }
}
```

---

### 24. Template parseHtmlByName() Path Resolution

The method searches for HTML templates in a specific order:

```php
function parseHtmlByName($sName, $aVariables, $mixedKeyWrapperHtml = null, $sCheckIn = BX_DOL_TEMPLATE_CHECK_IN_BOTH)
```

#### Search Order (`_getAbsoluteLocation` method)

**For module templates** (using `$this->_oTemplate` in module context):

```
Priority 1: Module's template override in active theme
            modules/{template_path}/data/tmpl/{theme_key}/template/{sName}
            
Priority 2: Module's base template directory  
            modules/{module_path}/template/{sName}
            
Priority 3: System template override
            modules/{template_path}/data/tmpl/{theme_key}/template/{sName}
            
Priority 4: System base template
            inc/tmpl/{tmpl_name}/scripts/{sName} or inc/tmpl/uni/scripts/{sName}
```

#### Location Path Format

For module templates with location prefix:

```php
// Using module location syntax
$sResult = $this->_oTemplate->parseHtmlByName('my_template.html', $aVars);

// The template is searched at:
// 1. modules/boonex/uni/data/tmpl/{active_theme}/sa/my_template.html  (theme override)
// 2. modules/sa/support_scheme/template/my_template.html               (module default)
```

#### Using Location Prefix in Template Names

```php
// Explicit module location
$html = $this->_oTemplate->parseHtmlByName('sa@modules/sa/support_scheme/|my_template.html', $aVars);

// This tells the system:
// - Location key: 'sa'
// - Location path: 'modules/sa/support_scheme/'
// - Template file: 'my_template.html'
```

#### The `$sCheckIn` Parameter

| Constant | Value | Behavior |
|----------|-------|----------|
| `BX_DOL_TEMPLATE_CHECK_IN_BOTH` | 'both' | Check theme first, then base (default) |
| `BX_DOL_TEMPLATE_CHECK_IN_TMPL` | 'tmpl' | Only check theme-specific templates |
| `BX_DOL_TEMPLATE_CHECK_IN_BASE` | 'base' | Only check base/parent templates |

#### Module Template Path Construction

In `BxDolModuleTemplate::__construct()`:

```php
// Module template paths are registered as locations
$this->addLocation($this->_sPrefix, $this->_sRootPath, $this->_sRootUrl);

// This allows parseHtmlByName to find templates at:
// {module_root}/template/{template_name}.html
```

#### Debugging Template Not Found

```php
// Check if template exists
if (!$this->_oTemplate->isHtml('my_template.html')) {
    // Template not found - debug paths
    
    // Method 1: Get absolute path
    $sPath = $this->_oTemplate->getTemplatePath('my_template.html');
    // Returns empty string if not found
    
    // Method 2: Check locations manually
    $sModulePath = BX_DIRECTORY_PATH_MODULES . 'sa/support_scheme/template/my_template.html';
    if (file_exists($sModulePath)) {
        echo "Template exists at: $sModulePath";
    }
}
```

#### File Extension Requirement

**IMPORTANT:** Always include `.html` extension:

```php
// ✅ CORRECT
$this->_oTemplate->parseHtmlByName('my_template.html', $aVars);

// ❌ WRONG - will not find the file
$this->_oTemplate->parseHtmlByName('my_template', $aVars);
```

---

### 25. Page Cover Setting Impact

The `cover` field in `sys_objects_page` controls cover image display:

```sql
`cover` tinyint(1) -- 1 = show cover area, 0 = no cover area
`cover_image` int(11) -- Storage file ID for cover image (0 = no image)
`cover_title` varchar(255) -- Title text overlay on cover
```

#### Cover Behavior Matrix

| cover | cover_image | Result |
|-------|-------------|--------|
| 0 | 0 | No cover container rendered at all |
| 0 | N | Ignored - no cover container |
| 1 | 0 | Empty cover area rendered (can cause layout issues) |
| 1 | N | Cover with image from storage file ID N |

#### Impact on Block Cell Rendering

When `cover = 1` and `cover_image = 0`:

1. **Cover container is still rendered** - An empty cover `<div>` appears
2. **Layout cell positioning** - May affect first content block position
3. **CSS classes applied** - Cover-related classes added to page wrapper

**Recommendation:** If you don't have a cover image, set `cover = 0`:

```sql
-- For pages without cover images
INSERT INTO sys_objects_page (..., `cover`, `cover_image`, ...) 
VALUES (..., 0, 0, ...);

-- For pages with cover images
INSERT INTO sys_objects_page (..., `cover`, `cover_image`, ...) 
VALUES (..., 1, 123, ...);  -- 123 = storage file ID
```

#### Cover Rendering in Page Template

The page template checks cover settings:

```php
// In page rendering (simplified)
if ($this->_aObject['cover']) {
    // Cover container is added to output
    $sCover = $this->_getCover();
    // If cover_image = 0, _getCover() returns empty or placeholder
}
```

#### Fixing Empty Cover Issues

If you see unwanted space at the top of your page:

```sql
-- Check and fix cover settings
UPDATE sys_objects_page SET cover = 0 WHERE cover = 1 AND cover_image = 0;
```

---

### 26. Form Input Visibility Filtering

Form inputs can be hidden based on member levels:

```sql
-- sys_form_display_inputs.visible_for_levels is a bitmask
-- Same format as page/block visibility

-- Values:
-- 1 = Guest only
-- 2 = Member (level 2)
-- 4 = Member (level 3)
-- 8 = Member (level 4)
-- ...
-- 2147483647 = All levels (default)
```

#### Checking Visibility in Code

```php
// In BxBaseFormView::getCodeAPI()
if(isset($aInput['visible_for_levels']) && !self::isVisible($aInput)) 
    continue;  // Skip this input

// isVisible checks against current user's level
```

#### Setting Visibility in SQL

```sql
-- Input visible to all
INSERT INTO sys_form_display_inputs (..., visible_for_levels, ...) 
VALUES (..., 2147483647, ...);

-- Input visible to admins only (level 8 = 2^(8-1) = 128)
INSERT INTO sys_form_display_inputs (..., visible_for_levels, ...) 
VALUES (..., 128, ...);
```

---

## Resources

### Official Sources
- **Vendor Test Module** (official reference): https://github.com/unacms/una-vendor-test
- **Vendor Template Module**: https://github.com/unacms/una-vendor-template
- **UNACMS GitHub**: https://github.com/unacms/una
- **API Docs** (auto-generated, latest): https://ci.una.io/docs/

### Official Wiki (version-agnostic, safe for v14)
- **Developer Architecture**: https://unacms.com/wiki/developer-architecture
- **Code Convention**: https://unacms.com/wiki/code-convention
- **Code Quality**: https://unacms.com/wiki/code-quality
- **Directory Structure**: https://unacms.com/wiki/directory-structure
- **Storage System**: https://unacms.com/wiki/storage-system
- **Common Mistakes**: https://unacms.com/wiki/common-mistakes

### Core Class Source References
- **BxDolForm**: https://github.com/unacms/una/blob/master/inc/classes/BxDolForm.php
- **BxDolTemplate**: https://github.com/unacms/una/blob/master/inc/classes/BxDolTemplate.php
- **BxDolStorage**: https://github.com/unacms/una/blob/master/inc/classes/BxDolStorage.php
- **BxDolAlerts**: https://github.com/unacms/una/blob/master/inc/classes/BxDolAlerts.php
- **BxDolACL**: https://github.com/unacms/una/blob/master/inc/classes/BxDolACL.php
- **BxBaseFormView**: https://github.com/unacms/una/blob/master/template/scripts/BxBaseFormView.php

---

## Complete Class Hierarchy Reference

| Class | Extends | File |
|-------|---------|------|
| `{Prefix}Module` | `BxDolModule` | `classes/{Prefix}Module.php` |
| `{Prefix}Db` | `BxDolModuleDb` | `classes/{Prefix}Db.php` |
| `{Prefix}Config` | `BxDolModuleConfig` | `classes/{Prefix}Config.php` |
| `{Prefix}Template` | `BxDolModuleTemplate` | `classes/{Prefix}Template.php` |
| `{Prefix}Installer` | `BxDolStudioInstaller` | `install/installer.php` |

> **Confirmed by**: official `una-vendor-test` repository (https://github.com/unacms/una-vendor-test)

---

*This guide is specifically for UNACMS 14.0.0. **Version v3 — Pipeline Edition**, April 2026. Section 0 (AI Development Pipeline) added to enforce validation-first module development. Sources: original UNACMS_14_Module_Development_Guidev2 + ModForge pipeline design + official vendor-test repo + unacms.com wiki.*

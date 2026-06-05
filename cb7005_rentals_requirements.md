# sa_rentals — Module Requirements & UNA CMS Translation
**Version:** 1.0  
**Date:** 2026-04-24  
**Platform:** UNA CMS 14.0.x  
**Site:** NeighborSocial (unamodforge.dev)  
**Vendor:** sa  
**Status:** Active development — base module installed, v2.4 compliance upgrade pending

---

## 1. Vision Statement

A controlled, community-aware rental listings marketplace integrated into NeighborSocial.
Not an open spam board — a protected environment where landlords, verified estate agents,
and qualified tenants interact within the trust framework of UNA Spaces and Groups.

The module must be independently deployable and upgradeable without touching UNA core.
All Space and Group integration happens through UNA's native privacy system —
not hardcoded joins.

---

## 2. User Roles & Permissions

### 2.1 Role Definitions

| Role | UNA Equivalent | Description |
|------|---------------|-------------|
| Landlord / Home Owner | Standard Member | Posts and maintains own listings |
| Estate Agent | Verified Member (custom level) | Professional lister — must register AND be verified before posting |
| Tenant | Standard Member | Browses and enquires on listings — must register to contact |
| Moderator | UNA Moderator level | Reviews flagged listings, manages blacklist |
| Administrator | UNA Admin | Full control, feature toggles, verification approvals |

### 2.2 ACL Actions (sys_acl_actions)

| Action | Description | Default Allowed Level |
|--------|-------------|----------------------|
| `view entry` | View a rental listing | Guest + (all) |
| `create entry` | Post a new listing | Standard Member+ |
| `edit own entry` | Edit own listing | Standard Member+ |
| `edit any entry` | Edit any listing | Moderator, Admin |
| `delete own entry` | Remove own listing | Standard Member+ |
| `delete any entry` | Remove any listing | Moderator, Admin |
| `approve entry` | Publish pending listing | Moderator, Admin |
| `feature entry` | Feature/pin a listing | Admin |
| `verify agent` | Approve estate agent status | Admin |
| `blacklist member` | Blacklist a user from listings | Moderator, Admin |

### 2.3 Permission Gates (PHP — checkAllow methods)

Every mutation operation in the Module class must call a permission check:

```php
// Pattern for all three gates — chain ACL + Privacy + Context
public function checkAllowView($iEntryId) { ... }
public function checkAllowEdit($iEntryId) { ... }
public function checkAllowDelete($iEntryId) { ... }
```

ACL checked first (membership level), then Privacy (sys_objects_privacy),
then Context (Space/Group membership if space_id is set).

---

## 3. Listing Visibility — UNA Privacy Integration

### 3.1 Design Decision: Option B (sys_objects_privacy)

Rentals uses UNA's native "Visible to..." privacy system — the same dropdown
used by Posts, Events, and all core content modules.

**Why Option B and not custom space_id/group_id columns:**
- One field covers Spaces, Groups, AND Organisations (all resolve to sys_profiles.id)
- UNA handles filtering automatically — no custom WHERE clauses needed
- Survives UNA upgrades without module changes
- Estate agents can advertise exclusively to a Group (e.g. "Elite Properties")
- Landlords can post to a specific Space/Ward without separate group logic

### 3.2 Privacy Registration (install.sql)

```sql
INSERT INTO sys_objects_privacy (
    object, module, action,
    allow_view_to, allow_view_to_editable,
    allow_post_to, allow_post_to_editable
) VALUES (
    'sa_rentals_view', 'sa_rentals', 'view',
    2147483647, 1,
    2147483647, 1
);
```

### 3.3 Visibility Scenarios

| Scenario | space_id | Privacy Setting | Who Sees It |
|----------|----------|-----------------|-------------|
| Public listing | 0 | Everyone | All site visitors |
| Ward/Neighbourhood | set | Space members | Members of that Space |
| Elite Agents Group | set | Group members | Members of that Group |
| Private draft | 0 | Only me | Listing owner only |

### 3.4 space_id Column

Retained in schema — maps to `sys_profiles.id`.
Covers Spaces, Groups, and Organisations through the same field.
`space_id = 0` means site-wide public.

---

## 4. Feature Specification

### 4.1 Core Features (Phase 1 — Current Build)

- Listing creation with property details
- Listing browse/search with filters
- View single listing
- My listings (owner view)
- Space-aware listings (space_id)

### 4.2 Phase 2 Features (Planned)

- Estate Agent verification workflow
- Tenant registration qualification
- Enquiry/application system
- Blacklisting (member, listing, IP/email)
- "Coming soon" education banners
- Featured listings

### 4.3 Feature Toggle System

All Phase 2 features are controlled by `sys_options` toggles — can be activated
or deactivated from Studio without code changes.

| Toggle | Option Name | Default |
|--------|-------------|---------|
| Require tenant registration | `sa_rentals_require_tenant_reg` | ON |
| Estate agent verification | `sa_rentals_agent_verification` | ON |
| Blacklisting | `sa_rentals_blacklisting` | OFF |
| Education banners | `sa_rentals_show_banners` | OFF |
| Pending approval for new listings | `sa_rentals_moderation` | OFF |

---

## 5. Data Schema

### 5.1 Main Listings Table (sa_rentals_listings)

```sql
CREATE TABLE IF NOT EXISTS `sa_rentals_listings` (
  `id`            int(11)       NOT NULL AUTO_INCREMENT,
  `title`         varchar(255)  NOT NULL,
  `description`   text,
  `property_type` enum('room','house','flat','backyard','townhouse') NOT NULL DEFAULT 'room',
  `province`      varchar(100)  NOT NULL DEFAULT '',
  `city`          varchar(100)  NOT NULL DEFAULT '',
  `address`       varchar(255)  NOT NULL DEFAULT '',
  `rent_zar`      decimal(10,2) NOT NULL DEFAULT 0.00,
  `deposit_zar`   decimal(10,2) NOT NULL DEFAULT 0.00,
  `bedrooms`      int(11)       NOT NULL DEFAULT 0,
  `bathrooms`     int(11)       NOT NULL DEFAULT 0,
  `contact`       varchar(255)  NOT NULL DEFAULT '',
  `author_id`     int(11)       NOT NULL DEFAULT 0,
  `space_id`      int(11)       NOT NULL DEFAULT 0 COMMENT 'sys_profiles.id — Space, Group or Org',
  `allow_view_to` int(11)       NOT NULL DEFAULT 2147483647 COMMENT 'sys_objects_privacy value',
  `status`        enum('active','inactive','draft','pending','blacklisted') NOT NULL DEFAULT 'active',
  `featured`      tinyint(1)    NOT NULL DEFAULT 0,
  `views`         int(11)       NOT NULL DEFAULT 0,
  `created`       datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`       datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `province` (`province`),
  KEY `property_type` (`property_type`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.2 Planned Phase 2 Tables

```sql
-- Agent profiles (Phase 2)
sa_rentals_agents     -- verified agent records, PPRE number, status

-- Tenant profiles (Phase 2)  
sa_rentals_tenants    -- qualification status, income band, references

-- Enquiries (Phase 2)
sa_rentals_enquiries  -- tenant→listing enquiry/application records

-- Blacklist (Phase 2)
sa_rentals_blacklist  -- member_id, email, ip, reason, added_by, expires
```

---

## 6. Pages & Blocks

### 6.1 Page Map

| URI | Page Object | Block | Service Method |
|-----|-------------|-------|----------------|
| `/rentals-listings` | sa_rentals_listings | Listings grid | `get_listings_block` |
| `/view-rentals-listing` | sa_rentals_view | Listing detail | `get_listing_detail_block` |
| `/create-rentals-listing` | sa_rentals_create | Create form | `get_create_listing_block` |
| `/my-rentals-listings` | sa_rentals_my | My listings | `get_my_listings_block` |

### 6.2 Serialization Reference

Module name: `sa_rentals` = **14 bytes**

| Method (snake_case) | Byte Count |
|--------------------|------------|
| `get_listings_block` | 18 |
| `get_listing_detail_block` | 24 |
| `get_create_listing_block` | 24 |
| `get_my_listings_block` | 21 |

**Verified serialization format:**
```
a:2:{s:6:"module";s:14:"sa_rentals";s:6:"method";s:18:"get_listings_block";}
```

---

## 7. UNA CMS Translation Notes

### 7.1 How Handlers Work in This Module

UNA uses an alert/handler system for cross-module communication.
When a listing is created, an alert fires — Timeline and Notifications
subscribe to that alert via handlers registered on module enable.

```
Listing created → BxDolAlerts::fire('sa_rentals', 'add')
                → bx_timeline picks it up via registered handler
                → listing appears in Space/site timeline feed
```

Handlers are registered in `onEnable()` and removed in `onDisable()`.
Never hardcode handler INSERT in install.sql — use `add_handlers` method.

### 7.2 How Language Keys Work

All user-facing strings use language keys, never hardcoded text.

Pattern: `_sa_rentals_{context}_{description}`

Examples:
```
_sa_rentals_page_listings        = "Available Rentals"
_sa_rentals_block_listings       = "Rental Listings"
_sa_rentals_field_title          = "Listing Title"
_sa_rentals_field_rent_zar       = "Monthly Rent (R)"
_sa_rentals_status_active        = "Available"
_sa_rentals_status_inactive      = "Unavailable"
```

All keys defined in `install/langs/en.xml`.
Referenced in SQL (sys_menu_items title, sys_objects_page title, etc.)
and in PHP template output.

### 7.3 How Serialization Works

PHP serialization in `sys_pages_blocks.content` tells UNA which service
method to call when rendering a page block.

Format: `a:2:{s:6:"module";s:N:"module_name";s:6:"method";s:M:"method_name";}`

N and M are **exact byte counts** of the strings. Off by 1 = blank block, no error.
This is RULE 5 in the execution protocol — always compute, never copy.

### 7.4 How Space/Group Integration Works

`space_id` stores a `sys_profiles.id` value.
All Spaces, Groups, and Organisations are profiles in UNA.

```sql
-- Get all active Spaces for dropdown
SELECT p.id, sp.title
FROM sys_profiles p
JOIN bx_spaces_data sp ON p.content_id = sp.id
WHERE p.type = 'bx_spaces' AND p.status = 'active'
ORDER BY sp.title ASC
```

When `space_id = 0` → site-wide public listing.
When `space_id > 0` → scoped to that Space/Group/Organisation.

The `sys_objects_privacy` registration (Option B) means UNA's native
"Visible to..." dropdown handles this automatically on the form.

---

## 8. Current Build State

### 8.1 What Exists

- Base module files at `/var/www/unamodforge/modules/sa/rentals/`
- All four PHP classes with correct inheritance
- install.sql with s:14 serialization verified
- space_id column in listings table
- sys_std_pages_widgets cleanup in uninstall.sql
- Committed to `https://github.com/7005cb/sa-una-modules.git`

### 8.2 What Works in Browser

- `/rentals-listings` — listing grid with filters renders ✓
- `/create-rentals-listing` — create form renders ✓
- Property type, Province, City, Address, Monthly Rent, Deposit, Bedrooms fields present ✓

### 8.3 Outstanding Issues

1. **DB query error on open** — likely table/column mismatch from pre-space_id install
   - Fix: `DESCRIBE sa_rentals_listings` in unamodforge_db
   - If space_id missing: ALTER TABLE to add it

2. **v2.4 compliance gap** — missing:
   - sys_acl_actions registration
   - checkAllowView/Edit/Delete() methods
   - sys_objects_privacy Option B registration
   - sys_objects_content_info registration
   - Timeline and Notifications integration

### 8.4 Next Development Steps

```
Priority 1: Fix DB query error (immediate)
Priority 2: v2.4 compliance upgrade — ACL + permission gates
Priority 3: sys_objects_privacy Option B registration
Priority 4: Timeline integration (listing appears in Space feed)
Priority 5: Phase 2 — Agent verification, Tenant qualification
Priority 6: Feature toggle system in sys_options
Priority 7: Blacklisting system
```

---

## 9. Process Flow — End to End

```
LANDLORD FLOW:
Register → Login → Post Listing → Select Visibility (Public/Space/Group)
→ Listing live (or pending if moderation ON) → Manage own listings

ESTATE AGENT FLOW:
Register → Apply for Agent status → Admin verifies → Agent badge assigned
→ Post listings (same as landlord but under Agent profile page)
→ Can post to specific Groups (e.g. "Elite Properties")

TENANT FLOW:
Register → Complete profile (Phase 2: qualification) → Browse listings
→ Filter by type/location/price → Enquire on listing (Phase 2)
→ Landlord/Agent notified via UNA messaging

ADMIN FLOW:
Monitor listings → Approve agents → Manage blacklist
→ Toggle features via Studio sys_options
→ Post education banners when deactivating features
```

---

## 10. Files Reference

| File | Location |
|------|----------|
| Module root | `/var/www/unamodforge/modules/sa/rentals/` |
| Git repo | `https://github.com/7005cb/sa-una-modules.git` |
| Execution protocol | `una-module-builder-SKILL-v2_4.md` |
| Dev guide | `UNACMS_14_Module_Development_Guidev3.md` |
| Wiki docs | `/home/sa/Downloads/una-wiki-docs/` |
| Handover doc | `/var/www/unamodforge/modules/sa/HANDOVER_KIRO.md` |
| Session state | `/var/www/unamodforge/modules/sa/STATUS.md` |
| Pattern library | `https://patterns.unaaistudio.com/api/patterns` |

---

## 11. Key Wiki References for This Module

From `/home/sa/Downloads/una-wiki-docs/`:

| File | Why Relevant |
|------|-------------|
| `Permissions-Builder.md` | ACL actions, membership levels, BxDolAcl |
| `Dev-Forms.md` | Create listing form, BxDolForm patterns |
| `Dev-Pages.md` | Page and block registration |
| `Menus.md` | Navigation menu item registration |
| `Timeline.md` | Listing appears in Space/site feed |
| `Dev-Grids.md` | Listings grid with filters |
| `Dev-Storage.md` | Listing photos/images |
| `Common-Mistakes.md` | Avoid known pitfalls |
| `Architecture.md` | UNA system overview |

---

*Document version 1.0 — 2026-04-24*  
*Produced for AnythingLLM sa_rentals workspace*  
*Reflects actual running module state on unamodforge.dev*  
*Update this document when Phase 2 development begins*

# Ward Councilor Portal Module - Installation Guide

## Module Overview

This module provides a complete Ward Councilor Portal for South African communities with:

### Features:
- **Dashboard** - Overview of all ward activities
- **Service Requests** - Citizens can submit and track service requests
- **Ward Meetings** - Schedule and manage community meetings
- **Announcements** - Post news and updates for the community
- **Ward Info** - Store ward details and councilor contact info

### Request Categories:
- Water & Sanitation
- Electricity
- Roads & Transport
- Refuse & Waste
- Housing
- Health Services
- Safety & Security
- Parks & Recreation
- Other

---

## Installation Steps

### Step 1: Copy Module Files
Copy the entire `ward_councilor` folder to your UNACMS modules directory:
```
/var/www/html/modules/sa/ward_councilor/
```

### Step 2: Create Database Tables
Run this SQL in your database:

```sql
-- SERVICE REQUESTS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `location` varchar(500) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `space_id` int(11) DEFAULT NULL,
  `councilor_notes` text,
  `views` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MEETINGS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `meeting_date` datetime NOT NULL,
  `location` varchar(500) DEFAULT NULL,
  `type` enum('community','public_forum','committee','special') NOT NULL DEFAULT 'community',
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `space_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `space_id` (`space_id`),
  KEY `meeting_date` (`meeting_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ANNOUNCEMENTS TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `author_id` int(11) DEFAULT NULL,
  `space_id` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `space_id` (`space_id`),
  KEY `status` (`status`),
  KEY `pinned` (`pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WARD INFO TABLE
CREATE TABLE IF NOT EXISTS `sa_ward_councilor_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `space_id` int(11) NOT NULL,
  `ward_number` varchar(20) DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `population` int(11) DEFAULT NULL,
  `description` text,
  `office_address` varchar(500) DEFAULT NULL,
  `office_hours` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `space_id` (`space_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 3: Install Module via UNACMS Studio
1. Go to Studio > Modules
2. Find "Ward Councilor Portal"
3. Click Install
4. The module will create pages and menu items automatically

---

## Pages Created

| URI | Description |
|-----|-------------|
| `ward-councilor-dashboard` | Main dashboard |
| `ward-requests` | List all service requests |
| `view-ward-request` | View single request |
| `create-ward-request` | Submit new request |
| `ward-meetings` | List upcoming meetings |
| `create-ward-meeting` | Schedule meeting |
| `ward-announcements` | List announcements |
| `view-ward-announcement` | View announcement |
| `create-ward-announcement` | Post announcement |
| `my-ward-requests` | User's own requests |

---

## How It Works

### Space-Based Councilor System
- Each Space in UNACMS represents a Ward/Community
- The Space owner becomes the Ward Councilor
- Citizens submit requests to specific spaces/wards
- Councilors can respond and update request status

### User Flow:
1. Citizen submits a service request (selects their ward/space)
2. Request gets auto-generated reference number (e.g., WAT-20260301-1234)
3. Councilor sees pending requests on dashboard
4. Councilor updates status and adds notes
5. Citizen can track their request status

---

## Module Files Structure

```
modules/sa/ward_councilor/
├── classes/
│   ├── SaWardCouncilorConfig.php
│   ├── SaWardCouncilorDb.php
│   ├── SaWardCouncilorModule.php
│   └── SaWardCouncilorTemplate.php
├── install/
│   ├── config.php
│   ├── installer.php
│   ├── langs/
│   │   └── en.xml
│   └── sql/
│       ├── install.sql
│       ├── uninstall.sql
│       ├── disable.sql
│       ├── enable.sql
│       └── upgrade.sql
├── template/
│   ├── css/
│   │   └── main.css
│   └── images/
│       └── icons/
└── request.php
```

---

## Customization

### Add More Request Categories
Edit `SaWardCouncilorModule.php` and modify the `$_aRequestCategories` array:

```php
protected $_aRequestCategories = array(
    'water' => 'Water & Sanitation',
    'electricity' => 'Electricity',
    // Add more categories here
);
```

### Change Theme Colors
Edit `template/css/main.css` and modify the CSS variables:

```css
:root {
    --wc-primary: #007749;      /* SA Green */
    --wc-secondary: #ffb612;    /* SA Gold */
    --wc-danger: #de3838;       /* SA Red */
    --wc-blue: #002395;         /* SA Blue */
}
```

---

## Support

For issues or feature requests, contact the SA Community development team.

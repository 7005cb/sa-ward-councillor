# HERMES.md — Ward Councillor Portal
# Agent context for Hermes sessions on sa_ward_councilor

## Module Identity
Module:     sa_ward_councilor
Vendor:     sa
Type:       Content module (space-aware)
UNA:        14.0.x
Protocol:   Module Builder v2.4 (load /una-fundamentals skill)

## Workspace Path
/home/cb7005/workspace/modules/sa/ward_councilor
(symlink → /var/www/unamodforge/modules/sa/ward_councilor)

## Module Structure
classes/        PHP classes (Config, Db, Module, Template)
install/        config.php, installer.php, langs/, sql/
template/       css/main.css, HTML templates
deploy.sh       Deploy script
fix_blocks.sh   Block repair utility
CLAUDE.md       Claude Code context (see for deployment steps)
HERMES.md       This file

## CSS Prefix
.wc- (ward councillor)
Artificer-first. addHtmlHeader()+!important for non-Artificer themes.

## Permissions Architecture
ACL:     create, edit own, delete own, edit any, delete any, approve
Privacy: Option A (space_id) — Option B upgrade planned for Space timeline
Context: Space-aware
Gate:    _isCouncilor() via bx_spaces_admins

## Key Integrations
bx_spaces_fans    — member-filtered space selector
bx_spaces_admins  — councillor identity gate
sys_objects_content_info — timeline/notifications (registered on enable)

## Last Stable State
- Space ID resolution: fixed
- Pill badge stats: fixed
- Collapsible AJAX tab panel: fixed
- NULL space_id records: resolved

## Active Development
See ward-councilor skill: /home/cb7005/.hermes/skills/una-modules/ward-councilor/SKILL.md
Current scope and feature discussion maintained there.

## Execution Mode
Check MCP at /var/www/unamodforge/mcp/ — if callable: Mode A (full auto)
If not callable after one attempt: Mode D → produce ZIP bundle via deploy.sh

## Deploy
bash deploy.sh
Then: Studio → Modules → Uninstall → Install
Check: /var/www/unamodforge/storage/logs/

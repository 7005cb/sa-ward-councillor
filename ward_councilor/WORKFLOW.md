# WORKFLOW.md — Ward Councillor Portal
# ModForge Development Workflow — Three Tool Integration

## The Three Tools

| Tool | Role | Strength |
|------|------|----------|
| **Claude** (claude.ai) | Architect & Author | Design decisions, complex code authoring, UAT analysis, documentation, communication |
| **Claude Code** | Implementor | File editing, PHP/JS execution, refactoring, lint, complex string manipulation, git operations |
| **Hermes** | Agent & Deployer | Persistent memory, VPS access, DB queries, deployment, monitoring, cron jobs |

---

## The Golden Rule

```
Claude ARCHITECTS
Claude Code IMPLEMENTS  
Hermes DEPLOYS
Git is the SINGLE SOURCE OF TRUTH
```

No tool overwrites another's committed work without going through git.

---

## Development Flow

### Phase 1 — Design (Claude)
1. Identify the feature or fix needed
2. Claude architects the solution — class design, DB schema, method signatures
3. Claude writes the complete PHP/SQL code
4. Claude produces a brief for Claude Code to implement

### Phase 2 — Implementation (Claude Code)
1. Claude Code reads the current file state from git
2. Applies the code changes precisely
3. PHP lint — must pass before any commit
4. Reads back every changed file to confirm
5. Updates install.sql and upgrade.sql if DB schema changed
6. Git commit with descriptive message
7. Git push to origin/main

### Phase 3 — Deployment (Hermes)
1. Hermes pulls latest from git
2. Deploys to VPS via rsync or direct file copy
3. Runs DB verification queries on production
4. Confirms deployment success
5. Reports any production-specific issues

### Phase 4 — Verification (Claude + UAT)
1. Claude reviews results and UAT findings
2. Any issues go back to Phase 1
3. Production deployment only after UAT PASS

---

## Task Routing — Which Tool Does What

### Always Claude:
- Architecture decisions
- Complex code with nested strings/JS/PHP
- Design patterns and integration logic
- UAT analysis and issue diagnosis
- Documentation and communication
- install.sql schema planning

### Always Claude Code:
- File editing and replacement
- PHP lint verification
- Complex string escaping in PHP files
- Git add/commit/push
- Refactoring existing methods
- Any edit where escaping is involved

### Always Hermes:
- SSH commands on VPS
- Production DB queries
- rsync deployments
- Cache clearing on prod
- Cron job management
- Reading wiki files from VPS
- Monitoring and health checks

### Either Claude Code or Hermes:
- Reading file contents for review
- Running DB queries on dev
- Verifying git status

---

## Communication Between Tools

### Claude → Claude Code
Claude produces a brief containing:
- Exact file path
- Exact method name to change
- Complete replacement code (no placeholders)
- PHP lint requirement
- Git commit message

### Claude Code → Hermes
After commit, Hermes receives:
- Commit hash confirmation
- Files changed
- rsync command to run
- Post-deploy DB verification queries

### Hermes → Claude
Hermes reports:
- Deployment success/failure
- Any production errors found
- DB query results for Claude to analyse

---

## Git Discipline

```bash
# Always work from module directory
cd /home/cb7005/workspace/modules/sa/ward_councilor

# Before any edit — check status
git status
git pull origin main

# After Claude Code edits
git add -A
git commit -m "type: description of change

- Detail 1
- Detail 2
- install.sql updated: yes/no"

git push origin main

# Hermes deploys after push
ssh neighborsocial "rsync ..."
```

### Commit Message Format:
```
feat: new feature added
fix: bug fixed
refactor: code restructured
docs: documentation updated
sql: install.sql/upgrade.sql updated
uat: changes from UAT testing
```

---

## install.sql Discipline

**Every code change that affects DB structure MUST update install.sql and upgrade.sql in the same commit.**

Checklist before every commit:
```
[ ] PHP lint passes
[ ] install.sql CREATE TABLE matches live DESCRIBE output
[ ] upgrade.sql has ALTER TABLE for any new columns
[ ] visible_for_levels correct for all menu items
[ ] INSERT IGNORE used throughout (not INSERT)
[ ] uninstall.sql uses table name not alias in MULTI DELETE
```

---

## UAT Gate — Before Any Production Deployment

```
[ ] All pages tested as: Guest | Standard | Leadership | Councillor | Admin
[ ] Zero 500 errors across all roles
[ ] Community prompt shows when no space context
[ ] install.sql reinstall tested (uninstall → delete → reinstall)
[ ] git commit with UAT results noted in message
[ ] Hermes confirms prod deployment success
[ ] Post-deploy smoke test on prod as each role
```

---

## Module Root Files

| File | Owner | Purpose |
|------|-------|---------|
| CLAUDE.md | Claude Code | Claude Code context and deployment instructions |
| HERMES.md | Hermes | Hermes agent context and VPS paths |
| WORKFLOW.md | All | This file — development process |
| README.md | All | Module overview and install instructions |

---

## Reference Files

| Resource | Location | Purpose |
|----------|----------|---------|
| UNA Wiki | /var/www/una-wiki/ on VPS | Authoritative UNA API reference |
| una-fundamentals skill | ~/.hermes/skills/una-fundamentals/ | Execution protocols, rules, failure patterns |
| ward-councilor skill | ~/.hermes/skills/una-modules/ward-councilor/ | Project profile, scope, integration architecture |
| Module Builder v2.4 | una-fundamentals SKILL.md | 7-stage gated build protocol |

**When uncertain about any UNA pattern:**
```bash
ssh neighborsocial "cat /var/www/una-wiki/[relevant-file].md"
```
Never guess. Always check the wiki first.

---

## The Flow in One Sentence

> Claude thinks → Claude Code builds → Hermes ships → UAT validates → repeat.

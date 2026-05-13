#!/bin/bash
# Ward Councillor — block title fix + cache clear
# Run as: bash /var/www/unamodforge/modules/sa/ward_councilor/fix_blocks.sh

DB_USER="unamodforge_user"
DB_PASS="Dellboy1967?"
DB_NAME="unamodforge_db"
MODULE_DIR="/var/www/unamodforge/modules/sa/ward_councilor"
CACHE_DIR="/var/www/unamodforge/cache"

echo ""
echo "=== Ward Councillor Block Fix ==="
echo ""

# Step 1 — Run SQL fix
echo "[1/4] Applying SQL fix..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MODULE_DIR/install/sql/fix_block_titles.sql" 2>/dev/null
echo "      Done."

# Step 2 — Verify block titles in DB
echo ""
echo "[2/4] Verifying block titles..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null <<'SQL'
SELECT
    pb.id,
    COALESCE(ls.String, pb.title) AS display_title,
    pb.title AS lang_key,
    SUBSTRING(pb.content, 40, 30) AS method_snippet
FROM sys_pages_blocks pb
LEFT JOIN sys_localization_keys lk ON lk.Key = pb.title
LEFT JOIN sys_localization_strings ls ON ls.IDKey = lk.ID
WHERE pb.module = 'sa_ward_councilor'
  AND pb.type = 'service'
  AND pb.title LIKE '%nav%' OR pb.title LIKE '%sidebar%' OR pb.title LIKE '%summary%'
ORDER BY pb.id;
SQL

# Step 3 — Clear file cache
echo ""
echo "[3/4] Clearing cache..."
find "$CACHE_DIR" -maxdepth 1 -name "*.php" -not -name "lang-*.php" -delete 2>/dev/null
find "$CACHE_DIR/objects" -maxdepth 1 -name "*.php" -delete 2>/dev/null
find "$CACHE_DIR" -maxdepth 2 -name "sys_localization*" -delete 2>/dev/null
echo "      Done."

# Step 4 — PHP lint
echo ""
echo "[4/4] PHP lint check..."
php -l "$MODULE_DIR/classes/SaWardCouncilorModule.php" 2>&1
php -l "$MODULE_DIR/classes/SaWardCouncilorTemplate.php" 2>&1

echo ""
echo "=== Complete. Refresh Studio > Pages > Add New Block ==="
echo ""

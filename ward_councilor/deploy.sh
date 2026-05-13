#!/bin/bash
CACHE="/var/www/unamodforge/cache"
WC="/var/www/unamodforge/modules/sa/ward_councilor"

echo "[1/2] PHP lint..."
php -l $WC/classes/SaWardCouncilorTemplate.php
php -l $WC/classes/SaWardCouncilorModule.php
php -l $WC/request.php

echo "[2/2] Cache clear..."
find "$CACHE" -maxdepth 1 -name "*.php" -not -name "lang-*.php" -delete 2>/dev/null
find "$CACHE/objects" -maxdepth 1 -name "*.php" -delete 2>/dev/null
echo "Done."

#!/bin/bash
# SA Rentals — deploy to UNA CMS 14.0.x
# Usage: bash deploy.sh
# Run from the directory containing this script

BASE=/var/www/una/modules/sa/rentals
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Creating directories..."
mkdir -p $BASE/{classes,install/sql,install/langs,template/css,template/images/icons}

echo "Copying files..."
cp -r $SCRIPT_DIR/classes/*       $BASE/classes/
cp -r $SCRIPT_DIR/install/*       $BASE/install/
cp -r $SCRIPT_DIR/template/*      $BASE/template/
cp    $SCRIPT_DIR/request.php     $BASE/

echo "Copying placeholder icons from boonex/events..."
for f in std-wi std-pi std-si std-mi; do
  cp /var/www/una/modules/boonex/events/template/images/icons/${f}.png      $BASE/template/images/icons/${f}.png 2>/dev/null || true
done

echo "Setting permissions..."
chown -R www-data:www-data $BASE
chmod -R 755 $BASE

echo ""
echo "Done! Now go to: UNA Studio > Modules > Install > sa_rentals"

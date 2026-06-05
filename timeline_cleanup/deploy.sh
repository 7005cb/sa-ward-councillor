#!/bin/bash
# Deploy Timeline Cleanup Tool to production
# Usage: ./deploy.sh [production|staging]

set -e

TARGET="${1:-production}"

if [ "$TARGET" = "production" ]; then
    DEST="root@168.231.116.28:/var/www/neighbors/modules/sa/timeline_cleanup/"
elif [ "$TARGET" = "staging" ]; then
    DEST="/var/www/unamodforge/modules/sa/timeline_cleanup/"
else
    echo "Usage: $0 [production|staging]"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "=== Deploying Timeline Cleanup Tool to $TARGET ==="

if [ "$TARGET" = "production" ]; then
    rsync -avz --delete \
        --exclude='.git' \
        --exclude='deploy.sh' \
        "$SCRIPT_DIR/" "$DEST"
else
    # Local staging — already in place, just verify
    echo "Module is at: $SCRIPT_DIR"
fi

echo "=== Deploy complete ==="
echo ""
echo "Next steps:"
echo "1. Go to Studio > Modules > Timeline Cleanup Tool > Install"
echo "2. Access the tool at: /modules/sa/timeline_cleanup/request.php"
echo "3. ALWAYS start with Dry Run mode ON"

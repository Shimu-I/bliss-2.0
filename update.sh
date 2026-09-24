#!/usr/bin/env bash
# Bliss Day Care - update script
# Usage:  bash update.sh [path-to-your-bliss-php-folder]
# Default target is the current directory. Old versions are backed up first.
set -e
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="${1:-.}"
TARGET="$(cd "$TARGET" 2>/dev/null && pwd)" || { echo "Folder not found: $1"; exit 1; }

if [ ! -f "$TARGET/app.php" ] || [ ! -d "$TARGET/inc" ]; then
  echo "This does not look like the Bliss project folder: $TARGET"
  echo "Run:  bash update.sh /path/to/bliss-php"
  exit 1
fi

BACKUP="$TARGET/.backup-$(date +%Y%m%d-%H%M%S)"
COUNT=0
cd "$HERE/files"
while IFS= read -r f; do
  f="${f#./}"
  mkdir -p "$(dirname "$TARGET/$f")"
  if [ -f "$TARGET/$f" ]; then
    mkdir -p "$(dirname "$BACKUP/$f")"
    cp "$TARGET/$f" "$BACKUP/$f"
  fi
  cp "$f" "$TARGET/$f"
  echo "  updated  $f"
  COUNT=$((COUNT+1))
done < <(find . -type f | sort)

echo
echo "Done: $COUNT files updated in $TARGET"
[ -d "$BACKUP" ] && echo "Old versions saved in $BACKUP"
echo "Your config.php was replaced: re-enter your database settings (your old copy is in the backup folder shown above)."

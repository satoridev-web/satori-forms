#!/bin/bash
# Bump version script for SATORI Forms

set -e

if [ -z "$1" ]; then
  echo "Usage: $0 [patch|minor|major]"
  exit 1
fi

VERSION_FILE="satori-forms.php"
CHANGELOG="CHANGELOG.md"

CURRENT_VERSION=$(grep "Version:" $VERSION_FILE | awk '{print $2}')
IFS='.' read -r -a parts <<< "$CURRENT_VERSION"

case $1 in
  patch) parts[2]=$((parts[2]+1)) ;;
  minor) parts[1]=$((parts[1]+1)); parts[2]=0 ;;
  major) parts[0]=$((parts[0]+1)); parts[1]=0; parts[2]=0 ;;
  *) echo "Invalid argument: $1"; exit 1 ;;
esac

NEW_VERSION="${parts[0]}.${parts[1]}.${parts[2]}"

# Update plugin file
sed -i "s/Version: .*/Version: $NEW_VERSION/" $VERSION_FILE

# Update constant
sed -i "s/SATORI_FORMS_VERSION', '.*'/SATORI_FORMS_VERSION', '$NEW_VERSION'/" $VERSION_FILE

# Update changelog
DATE=$(date +%Y-%m-%d)
sed -i "1i ## $NEW_VERSION - $DATE\n- Bumped version" $CHANGELOG

echo "Bumped version to $NEW_VERSION"

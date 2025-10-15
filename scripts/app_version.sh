#!/bin/bash
if [ -z "$APP_VERSION" ]; then
  if [ -d .git ]; then
    # Check if working directory is dirty
    if git diff-index --quiet HEAD -- 2>/dev/null; then
      # Clean working directory - use tag if available
      APP_VERSION=$(git describe --tags --exact-match 2>/dev/null || git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    else
      # Dirty working directory - use commit hash only
      APP_VERSION=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    fi
  else
    APP_VERSION="unknown"
  fi
fi
echo "$APP_VERSION" > .app_version

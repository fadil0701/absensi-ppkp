#!/bin/bash

# Script untuk memperbaiki permissions Laravel di server
# Usage: bash fix-permissions.sh

set -e

PROJECT_DIR="/var/www/html/absensi-ppkp"

echo "🔧 Fixing Laravel permissions..."

# Set ownership
echo "Setting ownership to www-data..."
sudo chown -R www-data:www-data $PROJECT_DIR

# Set directory permissions
echo "Setting directory permissions..."
sudo find $PROJECT_DIR -type d -exec chmod 755 {} \;

# Set file permissions
echo "Setting file permissions..."
sudo find $PROJECT_DIR -type f -exec chmod 644 {} \;

# Set special permissions for storage and cache
echo "Setting special permissions for storage and bootstrap/cache..."
sudo chmod -R 775 $PROJECT_DIR/storage
sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Set executable permissions for artisan
echo "Setting executable permission for artisan..."
sudo chmod +x $PROJECT_DIR/artisan

echo "✅ Permissions fixed!"
echo ""
echo "Storage and cache directories are now writable by web server."


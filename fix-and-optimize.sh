#!/bin/bash

# Script lengkap untuk fix permissions dan optimize Laravel
# Usage: bash fix-and-optimize.sh

set -e

PROJECT_DIR="/var/www/html/absensi-ppkp"

echo "🔧 Fixing Laravel permissions and optimizing..."

# Step 1: Set ownership
echo "Step 1: Setting ownership to www-data..."
sudo chown -R www-data:www-data $PROJECT_DIR

# Step 2: Set directory permissions
echo "Step 2: Setting directory permissions..."
sudo find $PROJECT_DIR -type d -exec chmod 755 {} \;

# Step 3: Set file permissions
echo "Step 3: Setting file permissions..."
sudo find $PROJECT_DIR -type f -exec chmod 644 {} \;

# Step 4: Set special permissions for storage
echo "Step 4: Setting permissions for storage directories..."
sudo chmod -R 775 $PROJECT_DIR/storage
sudo chmod -R 775 $PROJECT_DIR/storage/logs
sudo chmod -R 775 $PROJECT_DIR/storage/framework
sudo chmod -R 775 $PROJECT_DIR/storage/framework/cache
sudo chmod -R 775 $PROJECT_DIR/storage/framework/sessions
sudo chmod -R 775 $PROJECT_DIR/storage/framework/views
sudo chmod -R 775 $PROJECT_DIR/storage/app
sudo chmod -R 775 $PROJECT_DIR/storage/app/public

# Step 5: Set permissions for bootstrap/cache
echo "Step 5: Setting permissions for bootstrap/cache..."
sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Step 6: Create log file if doesn't exist
echo "Step 6: Creating log file if needed..."
sudo touch $PROJECT_DIR/storage/logs/laravel.log
sudo chown www-data:www-data $PROJECT_DIR/storage/logs/laravel.log
sudo chmod 664 $PROJECT_DIR/storage/logs/laravel.log

# Step 7: Set executable permission for artisan
echo "Step 7: Setting executable permission for artisan..."
sudo chmod +x $PROJECT_DIR/artisan

# Step 8: Clear old cache
echo "Step 8: Clearing old cache..."
sudo rm -rf $PROJECT_DIR/bootstrap/cache/*.php
sudo rm -rf $PROJECT_DIR/storage/framework/cache/data/*
sudo rm -rf $PROJECT_DIR/storage/framework/views/*

# Step 9: Run optimize as www-data user
echo "Step 9: Running optimize..."
cd $PROJECT_DIR
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

echo ""
echo "✅ All done! Permissions fixed and application optimized."
echo ""
echo "Verification:"
ls -la $PROJECT_DIR/storage/logs/ | head -5
ls -la $PROJECT_DIR/bootstrap/cache/ | head -5


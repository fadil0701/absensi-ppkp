#!/bin/bash

# Script untuk memperbaiki SatpelkesSeeder di server
# Usage: bash fix-seeder-server.sh

PROJECT_DIR="/var/www/html/absensi-ppkp"
SEEDER_FILE="$PROJECT_DIR/database/seeders/SatpelkesSeeder.php"

echo "🔧 Fixing SatpelkesSeeder.php on server..."

# Backup file terlebih dahulu
cp $SEEDER_FILE ${SEEDER_FILE}.backup

# Fix latitude dan longitude (ganti koma dengan titik)
sed -i 's/-6,18191530/-6.18191530/g' $SEEDER_FILE
sed -i 's/106,82915070/106.82915070/g' $SEEDER_FILE
sed -i 's/-6,18195980/-6.18195980/g' $SEEDER_FILE
sed -i 's/106,82914410/106.82914410/g' $SEEDER_FILE

echo "✅ SatpelkesSeeder.php fixed!"
echo ""
echo "Changes made:"
echo "  - Fixed latitude: -6,18191530 → -6.18191530"
echo "  - Fixed longitude: 106,82915070 → 106.82915070"
echo "  - Fixed latitude: -6,18195980 → -6.18195980"
echo "  - Fixed longitude: 106,82914410 → 106.82914410"
echo ""
echo "Backup saved to: ${SEEDER_FILE}.backup"
echo ""
echo "Now you can run: php artisan db:seed"


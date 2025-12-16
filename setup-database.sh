#!/bin/bash

# Script untuk Setup Database Absensi PPKP
# Usage: bash setup-database.sh

set -e

echo "🗄️  Setting up database for Absensi PPKP..."

# Configuration
DB_NAME="absensi_ppkp"
DB_USER="absensi_user"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Get MySQL root password
read -sp "Enter MySQL root password: " MYSQL_ROOT_PASSWORD
echo ""

# Get database user password
read -sp "Enter password for database user '$DB_USER': " DB_PASSWORD
echo ""

# Create database and user
echo -e "${GREEN}Creating database and user...${NC}"

mysql -u root -p$MYSQL_ROOT_PASSWORD <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✅ Database setup completed!${NC}"
echo ""
echo "Database Information:"
echo "  Database Name: $DB_NAME"
echo "  Database User: $DB_USER"
echo "  Database Password: [hidden]"
echo ""
echo -e "${YELLOW}Please update your .env file with these credentials:${NC}"
echo "  DB_DATABASE=$DB_NAME"
echo "  DB_USERNAME=$DB_USER"
echo "  DB_PASSWORD=$DB_PASSWORD"


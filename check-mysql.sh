#!/bin/bash

# Script untuk mengecek status MySQL dan database
# Usage: bash check-mysql.sh

echo "🔍 Checking MySQL Status and Databases..."
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}✗ MySQL client is not installed${NC}"
    exit 1
fi

# Check MySQL service status
echo -e "${GREEN}📊 MySQL Service Status:${NC}"
if systemctl is-active --quiet mysql || systemctl is-active --quiet mysqld; then
    echo -e "${GREEN}✓ MySQL service is running${NC}"
    systemctl status mysql --no-pager -l 2>/dev/null || systemctl status mysqld --no-pager -l 2>/dev/null
else
    echo -e "${RED}✗ MySQL service is not running${NC}"
    echo "Start MySQL with: sudo systemctl start mysql"
fi

echo ""
echo -e "${GREEN}📋 MySQL Version:${NC}"
mysql --version

echo ""
echo -e "${YELLOW}⚠ To check databases, you need MySQL root password${NC}"
echo "Run the following commands:"
echo ""
echo "1. Check all databases:"
echo "   mysql -u root -p -e 'SHOW DATABASES;'"
echo ""
echo "2. Check MySQL users:"
echo "   mysql -u root -p -e 'SELECT User, Host FROM mysql.user;'"
echo ""
echo "3. Check if absensi_ppkp database exists:"
echo "   mysql -u root -p -e 'SHOW DATABASES LIKE \"absensi_ppkp\";'"
echo ""
echo "4. Check database size:"
echo "   mysql -u root -p -e 'SELECT table_schema AS \"Database\", ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS \"Size (MB)\" FROM information_schema.TABLES GROUP BY table_schema;'"
echo ""
echo "5. Login to MySQL:"
echo "   mysql -u root -p"


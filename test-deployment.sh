#!/bin/bash

# Script untuk test deployment aplikasi Absensi PPKP
# Usage: bash test-deployment.sh

set -e

DOMAIN="puspelkes.jakarta.go.id"
PROJECT_DIR="/var/www/html/absensi-ppkp"
BASE_URL="http://localhost/absensi-ppkp"

echo "🧪 Testing Deployment..."
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Test 1: Check Nginx status
echo -e "${YELLOW}Test 1: Checking Nginx status...${NC}"
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx is running${NC}"
else
    echo -e "${RED}✗ Nginx is not running${NC}"
    exit 1
fi

# Test 2: Check PHP-FPM status
echo -e "${YELLOW}Test 2: Checking PHP 8.2 FPM status...${NC}"
if systemctl is-active --quiet php8.2-fpm; then
    echo -e "${GREEN}✓ PHP 8.2 FPM is running${NC}"
else
    echo -e "${RED}✗ PHP 8.2 FPM is not running${NC}"
    exit 1
fi

# Test 3: Check Nginx configuration
echo -e "${YELLOW}Test 3: Testing Nginx configuration...${NC}"
if sudo nginx -t > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Nginx configuration is valid${NC}"
else
    echo -e "${RED}✗ Nginx configuration has errors${NC}"
    sudo nginx -t
    exit 1
fi

# Test 4: Check if project directory exists
echo -e "${YELLOW}Test 4: Checking project directory...${NC}"
if [ -d "$PROJECT_DIR" ]; then
    echo -e "${GREEN}✓ Project directory exists${NC}"
else
    echo -e "${RED}✗ Project directory not found${NC}"
    exit 1
fi

# Test 5: Check if .env file exists
echo -e "${YELLOW}Test 5: Checking .env file...${NC}"
if [ -f "$PROJECT_DIR/.env" ]; then
    echo -e "${GREEN}✓ .env file exists${NC}"
else
    echo -e "${RED}✗ .env file not found${NC}"
fi

# Test 6: Check permissions
echo -e "${YELLOW}Test 6: Checking storage permissions...${NC}"
if [ -w "$PROJECT_DIR/storage" ]; then
    echo -e "${GREEN}✓ Storage directory is writable${NC}"
else
    echo -e "${RED}✗ Storage directory is not writable${NC}"
    echo "Run: sudo chmod -R 775 $PROJECT_DIR/storage"
fi

if [ -w "$PROJECT_DIR/bootstrap/cache" ]; then
    echo -e "${GREEN}✓ Bootstrap/cache directory is writable${NC}"
else
    echo -e "${RED}✗ Bootstrap/cache directory is not writable${NC}"
    echo "Run: sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache"
fi

# Test 7: Test HTTP response
echo -e "${YELLOW}Test 7: Testing HTTP response...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/ || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "301" ]; then
    echo -e "${GREEN}✓ HTTP response: $HTTP_CODE${NC}"
else
    echo -e "${RED}✗ HTTP response: $HTTP_CODE${NC}"
fi

# Test 8: Test login page
echo -e "${YELLOW}Test 8: Testing login page...${NC}"
LOGIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" $BASE_URL/login || echo "000")
if [ "$LOGIN_CODE" = "200" ] || [ "$LOGIN_CODE" = "302" ]; then
    echo -e "${GREEN}✓ Login page accessible: $LOGIN_CODE${NC}"
else
    echo -e "${RED}✗ Login page error: $LOGIN_CODE${NC}"
fi

# Test 9: Check database connection
echo -e "${YELLOW}Test 9: Testing database connection...${NC}"
cd $PROJECT_DIR
DB_TEST=$(php artisan tinker --execute="echo DB::connection()->getPdo() ? 'connected' : 'failed';" 2>/dev/null || echo "failed")
if [ "$DB_TEST" = "connected" ]; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Check .env file for database credentials"
fi

# Test 10: Check Laravel routes
echo -e "${YELLOW}Test 10: Checking Laravel routes...${NC}"
ROUTES=$(php artisan route:list --columns=uri,method 2>/dev/null | head -5 || echo "failed")
if [ "$ROUTES" != "failed" ]; then
    echo -e "${GREEN}✓ Routes loaded successfully${NC}"
    echo "$ROUTES" | head -3
else
    echo -e "${RED}✗ Failed to load routes${NC}"
fi

# Summary
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${YELLOW}📊 Test Summary${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Application URL: $BASE_URL"
echo "Domain: $DOMAIN"
echo ""
echo "To access from browser:"
echo "  http://$DOMAIN/absensi-ppkp"
echo "  http://$DOMAIN/absensi-ppkp/login"
echo ""
echo "If all tests passed, your application is ready!"
echo ""


#!/bin/bash
# ============================================
# Production Deployment Script
# Server: 10.15.101.117
# Repository: https://github.com/fadil0701/absensi-ppkp.git
# ============================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/html/absensi-ppkp"
REPO_URL="https://github.com/fadil0701/absensi-ppkp.git"
NGINX_USER="www-data"
PHP_VERSION="8.2"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Production Deployment - Absensi PPKP${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    echo -e "${YELLOW}Note: Some commands require sudo. You may be prompted for password.${NC}"
    SUDO="sudo"
else
    SUDO=""
fi

# Step 1: Clone/Update Repository
echo -e "${GREEN}[1/10] Cloning/Updating repository...${NC}"
if [ -d "$PROJECT_DIR" ]; then
    echo "Directory exists, pulling latest changes..."
    cd "$PROJECT_DIR"
    $SUDO git pull origin main
else
    echo "Cloning repository..."
    $SUDO mkdir -p /var/www/html
    cd /var/www/html
    $SUDO git clone "$REPO_URL" absensi-ppkp
    cd "$PROJECT_DIR"
fi
echo -e "${GREEN}✓ Repository ready${NC}"
echo ""

# Step 2: Check PHP version
echo -e "${GREEN}[2/10] Checking PHP version...${NC}"
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "not found")
if [ "$PHP_VER" != "$PHP_VERSION" ]; then
    echo -e "${YELLOW}Warning: PHP version is $PHP_VER, expected $PHP_VERSION${NC}"
else
    echo -e "${GREEN}✓ PHP $PHP_VERSION detected${NC}"
fi
echo ""

# Step 3: Install/Update Composer Dependencies
echo -e "${GREEN}[3/10] Installing Composer dependencies...${NC}"
if [ ! -f "composer.phar" ]; then
    echo "Downloading Composer..."
    $SUDO php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    $SUDO php composer-setup.php
    $SUDO php -r "unlink('composer-setup.php');"
fi
$SUDO php composer.phar install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Composer dependencies installed${NC}"
echo ""

# Step 4: Setup .env file
echo -e "${GREEN}[4/10] Setting up .env file...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        $SUDO cp .env.example .env
        echo ".env file created from .env.example"
    else
        echo -e "${RED}Error: .env.example not found!${NC}"
        echo "Please create .env file manually."
        exit 1
    fi
else
    echo ".env file already exists, backing up..."
    $SUDO cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

# Update APP_URL in .env
$SUDO sed -i "s|APP_URL=.*|APP_URL=https://puspelkes.jakarta.go.id/absensi|g" .env
echo -e "${GREEN}✓ .env configured${NC}"
echo ""

# Step 5: Generate Application Key
echo -e "${GREEN}[5/10] Generating application key...${NC}"
$SUDO php artisan key:generate --force 2>/dev/null || echo "Key may already exist"
echo -e "${GREEN}✓ Application key ready${NC}"
echo ""

# Step 6: Setup Storage Link
echo -e "${GREEN}[6/10] Setting up storage link...${NC}"
$SUDO php artisan storage:link 2>/dev/null || echo "Storage link may already exist"
echo -e "${GREEN}✓ Storage link created${NC}"
echo ""

# Step 7: Run Migrations
echo -e "${GREEN}[7/10] Running database migrations...${NC}"
read -p "Run database migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    $SUDO php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}Migrations skipped${NC}"
fi
echo ""

# Step 8: Optimize Laravel
echo -e "${GREEN}[8/10] Optimizing Laravel...${NC}"
$SUDO php artisan config:cache
$SUDO php artisan route:cache
$SUDO php artisan view:cache
echo -e "${GREEN}✓ Laravel optimized${NC}"
echo ""

# Step 9: Set Permissions
echo -e "${GREEN}[9/10] Setting permissions...${NC}"
$SUDO chown -R $NGINX_USER:$NGINX_USER "$PROJECT_DIR"
$SUDO chmod -R 755 "$PROJECT_DIR"
$SUDO chmod -R 775 storage bootstrap/cache
$SUDO chown -R $NGINX_USER:$NGINX_USER storage bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Step 10: Deploy NGINX Configuration
echo -e "${GREEN}[10/10] Deploying NGINX configuration...${NC}"
if [ -f "nginx-puspelkes.conf" ]; then
    echo "Backing up existing NGINX config..."
    $SUDO cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "No existing config to backup"
    
    echo "Copying new NGINX config..."
    $SUDO cp nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id
    
    echo "Enabling site..."
    $SUDO ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/
    
    echo "Creating log directories..."
    $SUDO mkdir -p /var/log/nginx
    $SUDO touch /var/log/nginx/puspelkes-absensi-access.log
    $SUDO touch /var/log/nginx/puspelkes-absensi-error.log
    $SUDO touch /var/log/nginx/puspelkes-access.log
    $SUDO touch /var/log/nginx/puspelkes-error.log
    $SUDO chown $NGINX_USER:$NGINX_USER /var/log/nginx/puspelkes*.log
    
    echo "Testing NGINX configuration..."
    if $SUDO nginx -t; then
        echo "Reloading NGINX..."
        $SUDO systemctl reload nginx
        echo -e "${GREEN}✓ NGINX deployed and reloaded${NC}"
    else
        echo -e "${RED}NGINX configuration test failed!${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}nginx-puspelkes.conf not found, skipping NGINX deployment${NC}"
fi
echo ""

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Deployment Completed!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo "Next steps:"
echo "1. Update .env file with database credentials and other settings"
echo "2. Run: php artisan migrate (if not done yet)"
echo "3. Test the application: https://puspelkes.jakarta.go.id/absensi"
echo "4. Check logs: tail -f /var/log/nginx/puspelkes-absensi-error.log"
echo ""


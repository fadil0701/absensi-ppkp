#!/bin/bash

# Script Deployment Otomatis untuk Aplikasi Absensi PPKP
# Usage: bash deploy.sh

set -e  # Exit on error

echo "🚀 Starting deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/html/absensi-ppkp"
GIT_REPO="https://github.com/fadil0701/absensi-ppkp.git"
DB_NAME="absensi_ppkp"
DB_USER="absensi_user"
DOMAIN="puspelkes.jakarta.go.id/absensi-ppkp"

# Functions
print_step() {
    echo -e "${GREEN}▶ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    print_error "Please do not run as root. Use sudo when needed."
    exit 1
fi

# Step 1: Clone or Update Repository
print_step "Cloning/Updating repository..."
if [ -d "$PROJECT_DIR" ]; then
    echo "Directory exists, pulling latest changes..."
    cd $PROJECT_DIR
    git pull origin main
else
    echo "Directory does not exist, cloning repository..."
    mkdir -p $(dirname $PROJECT_DIR)
    git clone $GIT_REPO $PROJECT_DIR
    cd $PROJECT_DIR
fi

# Step 2: Install Composer Dependencies
print_step "Installing Composer dependencies..."
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi
composer install --no-dev --optimize-autoloader

# Step 3: Setup .env file
print_step "Setting up .env file..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        print_warning "Please edit .env file with your configuration"
        print_warning "Run: nano $PROJECT_DIR/.env"
    else
        print_error ".env.example not found!"
        exit 1
    fi
else
    print_warning ".env file already exists, skipping..."
fi

# Step 4: Generate APP_KEY if not exists
print_step "Generating APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate
else
    print_warning "APP_KEY already exists, skipping..."
fi

# Step 5: Setup Database (interactive)
print_step "Database setup..."
read -p "Do you want to create database? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "MySQL root password: " -s MYSQL_ROOT_PASSWORD
    echo
    
    mysql -u root -p$MYSQL_ROOT_PASSWORD <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    print_warning "Please set DB_USERNAME and DB_PASSWORD in .env file"
fi

# Step 6: Run Migrations
print_step "Running database migrations..."
read -p "Do you want to run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
fi

# Step 7: Seed Database (optional)
print_step "Database seeding..."
read -p "Do you want to seed the database? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed
fi

# Step 8: Create Storage Link
print_step "Creating storage link..."
php artisan storage:link

# Step 9: Set Permissions
print_step "Setting permissions..."
sudo chown -R www-data:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR
sudo chmod -R 775 $PROJECT_DIR/storage
sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Step 10: Clear Cache
print_step "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# Step 11: Build Assets (if needed)
print_step "Building assets..."
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        npm install
        npm run build
    else
        print_warning "NPM not found, skipping asset build..."
    fi
fi

echo ""
echo -e "${GREEN}✅ Deployment completed!${NC}"
echo ""
echo "Next steps:"
echo "1. Edit .env file: nano $PROJECT_DIR/.env"
echo "2. Configure web server (Nginx/Apache)"
echo "3. Setup SSL certificate (recommended)"
echo "4. Access application at: https://$DOMAIN"
echo ""
print_warning "Don't forget to:"
echo "- Set correct DB_USERNAME and DB_PASSWORD in .env"
echo "- Configure APP_URL in .env"
echo "- Setup web server configuration"
echo "- Configure SSL certificate"


#!/bin/bash

# Script untuk setup Nginx configuration langsung di server
# Usage: bash setup-nginx-server.sh

set -e

DOMAIN="puspelkes.jakarta.go.id"
PROJECT_DIR="/var/www/html/absensi-ppkp"
NGINX_CONFIG="/etc/nginx/sites-available/absensi-ppkp"

echo "🔧 Setting up Nginx configuration..."

# Buat file konfigurasi Nginx
echo "Creating Nginx configuration file..."
sudo tee $NGINX_CONFIG > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN;
    
    root /var/www/html;
    index index.php index.html index.htm;

    # Logging
    access_log /var/log/nginx/absensi-ppkp-access.log;
    error_log /var/log/nginx/absensi-ppkp-error.log;

    # Location untuk subdirectory /absensi-ppkp
    location /absensi-ppkp {
        alias $PROJECT_DIR/public;
        try_files \$uri \$uri/ @absensi;
        
        # Laravel specific
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_param SCRIPT_FILENAME \$request_filename;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_read_timeout 300;
        }
        
        # Deny access to hidden files
        location ~ /\. {
            deny all;
            access_log off;
            log_not_found off;
        }
    }

    # Rewrite rule untuk Laravel
    location @absensi {
        rewrite ^/absensi-ppkp/(.*)$ /absensi-ppkp/index.php?/\$1 last;
    }

    # Deny access to .htaccess files
    location ~ /\.ht {
        deny all;
    }
}
EOF

echo "✅ Nginx configuration file created at $NGINX_CONFIG"

# Aktifkan konfigurasi
echo "Enabling Nginx site..."
sudo ln -sf $NGINX_CONFIG /etc/nginx/sites-enabled/absensi-ppkp

# Test konfigurasi Nginx
echo "Testing Nginx configuration..."
if sudo nginx -t; then
    echo "✅ Nginx configuration is valid"
    
    # Reload Nginx
    echo "Reloading Nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx reloaded successfully"
else
    echo "❌ Nginx configuration test failed!"
    exit 1
fi

echo ""
echo "✅ Nginx configuration completed!"
echo ""
echo "Configuration file: $NGINX_CONFIG"
echo "Enabled link: /etc/nginx/sites-enabled/absensi-ppkp"
echo ""
echo "Your application should be accessible at: http://$DOMAIN/absensi-ppkp"
echo ""
echo "Next steps:"
echo "1. Setup SSL certificate: sudo certbot --nginx -d $DOMAIN"
echo "2. Make sure PHP-FPM is running: sudo systemctl status php8.2-fpm"
echo "3. Check Nginx status: sudo systemctl status nginx"


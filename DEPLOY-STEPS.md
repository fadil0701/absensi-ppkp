# Langkah-langkah Deployment ke Server Production

## Informasi Server
- **IP:** 10.15.101.117
- **Domain:** puspelkes.jakarta.go.id
- **Path:** /var/www/html/absensi-ppkp
- **Repository:** https://github.com/fadil0701/absensi-ppkp.git

---

## METODE 1: Deployment Otomatis (Recommended)

### Langkah 1: Masuk ke Server
```bash
ssh user@10.15.101.117
```

### Langkah 2: Upload Script Deployment
Dari komputer lokal, upload script:
```bash
scp deploy-production.sh user@10.15.101.117:/tmp/
```

### Langkah 3: Jalankan Script
Di server:
```bash
cd /tmp
chmod +x deploy-production.sh
sudo bash deploy-production.sh
```

---

## METODE 2: Deployment Manual

### 1. Clone Repository ke Server

```bash
# Masuk ke server
ssh user@10.15.101.117

# Buat direktori dan clone
sudo mkdir -p /var/www/html
cd /var/www/html
sudo git clone https://github.com/fadil0701/absensi-ppkp.git
cd absensi-ppkp
```

### 2. Install Dependencies

```bash
# Install Composer dependencies
sudo php composer.phar install --no-dev --optimize-autoloader

# Atau jika belum ada composer.phar
cd /var/www/html/absensi-ppkp
sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php
sudo php composer.phar install --no-dev --optimize-autoloader
```

### 3. Setup Environment File

```bash
# Copy .env.example ke .env (jika ada)
sudo cp .env.example .env

# Atau buat .env baru
sudo nano .env
```

**Konfigurasi .env yang penting:**
```env
APP_NAME="Absensi PPKP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://puspelkes.jakarta.go.id/absensi

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=nama_user
DB_PASSWORD=password

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_PATH=/absensi

# Sanctum
SANCTUM_STATEFUL_DOMAINS=puspelkes.jakarta.go.id
```

### 4. Generate Application Key

```bash
sudo php artisan key:generate --force
```

### 5. Setup Storage Link

```bash
sudo php artisan storage:link
```

### 6. Run Database Migrations

```bash
# Pastikan database sudah dibuat dan .env sudah dikonfigurasi
sudo php artisan migrate --force
```

### 7. Set Permissions

```bash
cd /var/www/html/absensi-ppkp

# Set ownership
sudo chown -R www-data:www-data .

# Set permissions
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 8. Optimize Laravel

```bash
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

### 9. Deploy NGINX Configuration

```bash
cd /var/www/html/absensi-ppkp

# Backup existing config
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "No existing config"

# Copy new config
sudo cp nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id

# Enable site
sudo ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/

# Create log directories
sudo mkdir -p /var/log/nginx
sudo touch /var/log/nginx/puspelkes-absensi-access.log
sudo touch /var/log/nginx/puspelkes-absensi-error.log
sudo touch /var/log/nginx/puspelkes-access.log
sudo touch /var/log/nginx/puspelkes-error.log
sudo chown www-data:www-data /var/log/nginx/puspelkes*.log

# Test and reload NGINX
sudo nginx -t
sudo systemctl reload nginx
```

### 10. Setup Firewall (Optional)

```bash
cd /var/www/html/absensi-ppkp
sudo bash ufw-firewall-rules.sh
```

---

## Verifikasi Deployment

### 1. Check Services

```bash
# Check NGINX status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check listening ports
sudo netstat -tlnp | grep -E ':80|:443|:8000|:8081'
```

### 2. Test Endpoints

```bash
# Test HTTP to HTTPS redirect
curl -I http://puspelkes.jakarta.go.id

# Test HTTPS
curl -I https://puspelkes.jakarta.go.id

# Test absensi endpoint
curl -I https://puspelkes.jakarta.go.id/absensi
```

### 3. Check Logs

```bash
# NGINX error log
sudo tail -f /var/log/nginx/puspelkes-absensi-error.log

# NGINX access log
sudo tail -f /var/log/nginx/puspelkes-absensi-access.log

# Laravel log
sudo tail -f /var/www/html/absensi-ppkp/storage/logs/laravel.log
```

---

## Troubleshooting

### 502 Bad Gateway
```bash
# Check PHP-FPM is running
sudo systemctl status php8.2-fpm

# Check PHP-FPM socket
ls -la /var/run/php/php8.2-fpm.sock

# Check NGINX error log
sudo tail -50 /var/log/nginx/error.log
```

### 403 Forbidden
```bash
# Check permissions
ls -la /var/www/html/absensi-ppkp

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/absensi-ppkp
sudo chmod -R 755 /var/www/html/absensi-ppkp
sudo chmod -R 775 /var/www/html/absensi-ppkp/storage
```

### Assets not loading
```bash
# Clear cache
cd /var/www/html/absensi-ppkp
sudo php artisan config:clear
sudo php artisan cache:clear
sudo php artisan view:clear

# Re-optimize
sudo php artisan config:cache
sudo php artisan route:cache
```

### Database connection error
```bash
# Check .env configuration
cat .env | grep DB_

# Test database connection
sudo php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Checklist Deployment

- [ ] Repository cloned to /var/www/html/absensi-ppkp
- [ ] Composer dependencies installed
- [ ] .env file configured
- [ ] Application key generated
- [ ] Storage link created
- [ ] Database migrations run
- [ ] Permissions set correctly
- [ ] Laravel optimized (config/route/view cache)
- [ ] NGINX configuration deployed
- [ ] NGINX tested and reloaded
- [ ] Firewall configured (optional)
- [ ] Services running (NGINX, PHP-FPM)
- [ ] Application accessible via browser
- [ ] Logs checked for errors

---

**Setelah deployment selesai, pastikan untuk:**
1. Update .env dengan konfigurasi production yang benar
2. Setup database dan run migrations
3. Test semua fungsi aplikasi
4. Monitor logs untuk error
5. Setup backup routine


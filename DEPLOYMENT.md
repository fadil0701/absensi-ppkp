# 🚀 Panduan Deployment Aplikasi Absensi PPKP

## 📋 Prerequisites

Pastikan server sudah memiliki:
- PHP >= 8.2 dengan extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- Composer
- MySQL/MariaDB
- Node.js & NPM (untuk build assets)
- Git
- Web server (Nginx/Apache)

## 🔧 Langkah-langkah Deployment

### 1. Clone Repository dari GitHub

```bash
cd /var/www/html  # atau direktori web server Anda
git clone https://github.com/fadil0701/absensi-ppkp.git
cd absensi-ppkp
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies (jika diperlukan)
npm install
npm run build
```

### 3. Setup Environment File

```bash
# Copy file .env.example ke .env
cp .env.example .env

# Edit file .env dengan konfigurasi yang sesuai
nano .env
```

**Konfigurasi .env yang perlu diubah:**

```env
APP_NAME="Sistem Absensi PPKP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://puspelkes.jakarta.go.id/absensi-ppkp

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_ppkp
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

SANCTUM_STATEFUL_DOMAINS=puspelkes.jakarta.go.id
SESSION_DOMAIN=puspelkes.jakarta.go.id
```

**Generate APP_KEY:**
```bash
php artisan key:generate
```

### 4. Setup Database

```bash
# Login ke MySQL
mysql -u root -p

# Buat database dan user
CREATE DATABASE absensi_ppkp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'Ppkp-DKI1';
GRANT ALL PRIVILEGES ON absensi_ppkp.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Jalankan Migrasi Database

```bash

```

### 6. Seed Database (Optional)

```bash
php artisan db:seed
```

### 7. Setup Storage Link

```bash
php artisan storage:link
```

### 8. Set Permissions

```bash
# Set ownership (ganti 'www-data' dengan user web server Anda)
sudo chown -R www-data:www-data /var/www/html/absensi-ppkp

# Set permissions
sudo chmod -R 755 /var/www/html/absensi-ppkp
sudo chmod -R 775 /var/www/html/absensi-ppkp/storage
sudo chmod -R 775 /var/www/html/absensi-ppkp/bootstrap/cache
```

### 9. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

### 10. Konfigurasi Web Server

#### Untuk Nginx:

Buat file konfigurasi di `/etc/nginx/sites-available/absensi-ppkp`:

```nginx
server {
    listen 80;
    server_name puspelkes.jakarta.go.id;
    
    root /var/www/html/absensi-ppkp/public;
    index index.php index.html;

    location /absensi-ppkp {
        alias /var/www/html/absensi-ppkp/public;
        try_files $uri $uri/ @absensi;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }

    location @absensi {
        rewrite /absensi-ppkp/(.*)$ /absensi-ppkp/index.php?/$1 last;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/absensi-ppkp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Untuk Apache:

Buat file konfigurasi di `/etc/apache2/sites-available/absensi-ppkp.conf`:

```apache
<VirtualHost *:80>
    ServerName puspelkes.jakarta.go.id
    DocumentRoot /var/www/html/absensi-ppkp/public

    <Directory /var/www/html/absensi-ppkp/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/absensi-ppkp_error.log
    CustomLog ${APACHE_LOG_DIR}/absensi-ppkp_access.log combined
</VirtualHost>
```

Aktifkan konfigurasi:
```bash
sudo a2ensite absensi-ppkp.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 11. Setup SSL Certificate (HTTPS) - Recommended

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx  # untuk Nginx
# atau
sudo apt-get install certbot python3-certbot-apache  # untuk Apache

# Generate SSL certificate
sudo certbot --nginx -d puspelkes.jakarta.go.id  # untuk Nginx
# atau
sudo certbot --apache -d puspelkes.jakarta.go.id  # untuk Apache
```

### 12. Setup Queue Worker (Optional)

Jika menggunakan queue, setup supervisor atau systemd service:

```bash
# Install supervisor
sudo apt-get install supervisor

# Buat file konfigurasi
sudo nano /etc/supervisor/conf.d/absensi-ppkp-worker.conf
```

Isi file:
```ini
[program:absensi-ppkp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/absensi-ppkp/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/absensi-ppkp/storage/logs/worker.log
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start absensi-ppkp-worker:*
```

## 🔄 Update Deployment

Untuk update aplikasi di masa depan:

```bash
cd /var/www/html/absensi-ppkp
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

## ✅ Checklist Deployment

- [ ] Repository di-clone dari GitHub
- [ ] Dependencies di-install (Composer & NPM)
- [ ] File .env dikonfigurasi dengan benar
- [ ] APP_KEY sudah di-generate
- [ ] Database dibuat dan user dibuat
- [ ] Migrasi database berhasil
- [ ] Storage link dibuat
- [ ] Permissions di-set dengan benar
- [ ] Web server dikonfigurasi
- [ ] SSL certificate di-setup (jika diperlukan)
- [ ] Cache di-clear
- [ ] Aplikasi dapat diakses di https://puspelkes.jakarta.go.id/absensi-ppkp

## 🐛 Troubleshooting

### Error: Permission denied
```bash
sudo chown -R www-data:www-data /var/www/html/absensi-ppkp
sudo chmod -R 775 /var/www/html/absensi-ppkp/storage
```

### Error: Database connection
- Pastikan database sudah dibuat
- Pastikan credentials di .env benar
- Pastikan MySQL service berjalan: `sudo systemctl status mysql`

### Error: 500 Internal Server Error
- Cek log: `tail -f storage/logs/laravel.log`
- Pastikan permissions sudah benar
- Pastikan APP_KEY sudah di-generate

### Error: Route not found
- Clear route cache: `php artisan route:clear`
- Pastikan .htaccess atau Nginx config sudah benar

## 📞 Support

Jika ada masalah, cek:
1. Log Laravel: `storage/logs/laravel.log`
2. Log web server: `/var/log/nginx/error.log` atau `/var/log/apache2/error.log`
3. Pastikan semua requirements terpenuhi


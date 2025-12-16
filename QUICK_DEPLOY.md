# ⚡ Quick Deployment Guide

## 🚀 Langkah Cepat Deploy ke Server

### 1. Clone Repository
```bash
cd /var/www/html
git clone https://github.com/fadil0701/absensi-ppkp.git
cd absensi-ppkp
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Setup Environment
```bash
cp .env.example .env
nano .env  # Edit konfigurasi
php artisan key:generate
```

**Isi .env yang penting:**
```env
APP_NAME="Sistem Absensi PPKP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://puspelkes.jakarta.go.id/absensi-ppkp

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_ppkp
DB_USERNAME=absensi_user
DB_PASSWORD=your_password_here

SANCTUM_STATEFUL_DOMAINS=puspelkes.jakarta.go.id
SESSION_DOMAIN=puspelkes.jakarta.go.id
```

### 4. Setup Database
```bash
# Buat database dan user
mysql -u root -p
```

Di MySQL:
```sql
CREATE DATABASE absensi_ppkp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON absensi_ppkp.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Migrasi & Setup
```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

### 6. Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/absensi-ppkp
sudo chmod -R 755 /var/www/html/absensi-ppkp
sudo chmod -R 775 /var/www/html/absensi-ppkp/storage
sudo chmod -R 775 /var/www/html/absensi-ppkp/bootstrap/cache
```

### 7. Setup Web Server

**Untuk Nginx:**
```bash
sudo cp nginx-absensi-ppkp.conf /etc/nginx/sites-available/absensi-ppkp
sudo ln -s /etc/nginx/sites-available/absensi-ppkp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

**Untuk Apache:**
```bash
sudo cp apache-absensi-ppkp.conf /etc/apache2/sites-available/absensi-ppkp.conf
sudo a2ensite absensi-ppkp.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 8. Setup SSL (HTTPS)
```bash
# Untuk Nginx
sudo certbot --nginx -d puspelkes.jakarta.go.id

# Untuk Apache
sudo certbot --apache -d puspelkes.jakarta.go.id
```

## ✅ Selesai!

Akses aplikasi di: **https://puspelkes.jakarta.go.id/absensi-ppkp**

## 📝 Update di Masa Depan

```bash
cd /var/www/html/absensi-ppkp
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## 🆘 Troubleshooting

**Error 500:**
- Cek log: `tail -f storage/logs/laravel.log`
- Pastikan permissions benar
- Pastikan APP_KEY sudah di-generate

**Database Error:**
- Pastikan database sudah dibuat
- Cek credentials di .env
- Pastikan MySQL service berjalan

**Route Not Found:**
- Clear cache: `php artisan route:clear`
- Pastikan .htaccess atau Nginx config benar


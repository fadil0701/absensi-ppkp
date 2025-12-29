# Konfigurasi NGINX Reverse Proxy - Ringkasan
## Puspelkes Jakarta Production Server

---

## INFORMASI SERVER

- **Domain:** puspelkes.jakarta.go.id
- **IP Production:** 10.15.101.117
- **Backend Main System:** 127.0.0.1:8081
- **Backend Laravel Absensi:** 127.0.0.1:8000
- **Path Absensi:** `/absensi`

---

## FILE KONFIGURASI YANG DIBUAT

### 1. `nginx-puspelkes.conf`
Konfigurasi utama NGINX server block untuk domain puspelkes.jakarta.go.id
- **Lokasi deployment:** `/etc/nginx/sites-available/puspelkes.jakarta.go.id`
- **Fitur:** SSL, security headers, rate limiting, path rewriting

### 2. `nginx-rate-limit.conf`
Konfigurasi rate limiting untuk proteksi brute-force
- **Lokasi deployment:** `/etc/nginx/conf.d/rate-limit.conf` (optional)
- **Limit login:** 5 requests/minute
- **Limit umum:** 30 requests/second

### 3. `ufw-firewall-rules.sh`
Script untuk mengonfigurasi firewall UFW
- **Port yang dibuka:** 22 (SSH), 80 (HTTP), 443 (HTTPS)
- **Port yang diblokir:** 8000, 8081 (hanya localhost)

### 4. `laravel-env-config.txt`
Konfigurasi Laravel .env untuk proxy
- **APP_URL:** https://puspelkes.jakarta.go.id/absensi
- **SESSION_PATH:** /absensi
- **SESSION_SECURE_COOKIE:** true

### 5. `nginx-deployment-commands.sh`
Script untuk deployment dan testing
- Backup konfigurasi lama
- Test dan reload NGINX
- Validasi koneksi dan port blocking

### 6. `DEPLOYMENT-GUIDE.md`
Panduan lengkap deployment dengan checklist

---

## LANGKAH DEPLOYMENT CEPAT

```bash
# 1. Copy konfigurasi NGINX
sudo cp nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id

# 2. Enable site
sudo ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/

# 3. Test dan reload
sudo nginx -t && sudo systemctl reload nginx

# 4. Setup firewall
chmod +x ufw-firewall-rules.sh
sudo ./ufw-firewall-rules.sh

# 5. Update Laravel .env (sesuai laravel-env-config.txt)
# Edit .env file di project Laravel

# 6. Clear Laravel cache
cd /path/to/laravel/project
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

## TESTING

```bash
# Test domain
curl -I https://puspelkes.jakarta.go.id
curl -I https://puspelkes.jakarta.go.id/absensi

# Test port blocking (harus gagal/timeout)
curl -I http://10.15.101.117:8000
curl -I http://10.15.101.117:8081

# Check logs
sudo tail -f /var/log/nginx/puspelkes-absensi-error.log
```

---

## KONFIGURASI LARAVEL .env

Update file `.env` di project Laravel dengan:

```env
APP_URL=https://puspelkes.jakarta.go.id/absensi
SESSION_PATH=/absensi
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SANCTUM_STATEFUL_DOMAINS=puspelkes.jakarta.go.id
```

---

## KEAMANAN YANG DITERAPKAN

✅ **SSL/TLS Hardening** - TLS 1.2+, strong ciphers  
✅ **Security Headers** - HSTS, X-Frame-Options, X-Content-Type-Options  
✅ **Rate Limiting** - 5 req/min untuk login, 30 req/s umum  
✅ **Port Blocking** - Port 8000 & 8081 hanya localhost  
✅ **Path Protection** - Blokir akses file sensitif (.env, artisan)  
✅ **Separate Logging** - Log khusus untuk sistem absensi  
✅ **HTTPS Enforcement** - Redirect HTTP ke HTTPS  

---

## TROUBLESHOOTING

**502 Bad Gateway:**
- Pastikan backend services running: `sudo systemctl status` (check services di port 8000, 8081)

**Assets tidak load:**
- Pastikan `APP_URL` di .env sudah benar
- Clear cache: `php artisan config:clear && php artisan cache:clear`

**Session/CSRF error:**
- Pastikan `SESSION_SECURE_COOKIE=true` dan `SESSION_PATH=/absensi`
- Check proxy headers di NGINX

**Redirect loop:**
- Check `proxy_redirect` directives di NGINX config
- Pastikan Laravel tidak punya redirect yang konflik

---

**Dokumen ini dibuat untuk deployment di server production 10.15.101.117**


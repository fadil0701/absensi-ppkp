# Quick Start - Deploy ke Server Production

## Server Information
- **IP:** 10.15.101.117
- **Domain:** puspelkes.jakarta.go.id

---

## STEP 1: Koneksi SSH ke Server

### Windows (PowerShell/Git Bash/WSL):
```bash
ssh root@10.15.101.117
```

### Atau dengan user lain:
```bash
ssh username@10.15.101.117
```

**Catatan:** Ganti `username` dengan user yang sesuai. Masukkan password saat diminta.

---

## STEP 2: Upload File Konfigurasi

Dari komputer lokal Anda (dalam direktori project ini), jalankan:

### Windows PowerShell:
```powershell
scp nginx-puspelkes.conf root@10.15.101.117:/tmp/
scp ufw-firewall-rules.sh root@10.15.101.117:/tmp/
scp nginx-deployment-commands.sh root@10.15.101.117:/tmp/
```

### Atau upload semua sekaligus:
```powershell
scp nginx-puspelkes.conf nginx-rate-limit.conf ufw-firewall-rules.sh nginx-deployment-commands.sh root@10.15.101.117:/tmp/
```

---

## STEP 3: Setelah Masuk ke Server via SSH

Setelah berhasil masuk ke server, jalankan perintah berikut:

```bash
# 1. Masuk ke /tmp
cd /tmp

# 2. Beri permission execute
chmod +x ufw-firewall-rules.sh nginx-deployment-commands.sh

# 3. Backup konfigurasi NGINX yang ada
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "File belum ada, akan dibuat baru"

# 4. Copy konfigurasi baru
sudo cp nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id

# 5. Enable site
sudo ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/

# 6. Buat log directories
sudo mkdir -p /var/log/nginx
sudo touch /var/log/nginx/puspelkes-absensi-access.log
sudo touch /var/log/nginx/puspelkes-absensi-error.log
sudo touch /var/log/nginx/puspelkes-access.log
sudo touch /var/log/nginx/puspelkes-error.log
sudo chown www-data:www-data /var/log/nginx/puspelkes*.log

# 7. Test konfigurasi
sudo nginx -t

# 8. Jika test berhasil, reload NGINX
sudo systemctl reload nginx

# 9. Check status
sudo systemctl status nginx
```

---

## STEP 4: Setup Firewall (Opsional)

```bash
cd /tmp
sudo bash ufw-firewall-rules.sh
```

---

## STEP 5: Test Deployment

```bash
# Test HTTPS
curl -I https://puspelkes.jakarta.go.id

# Test absensi endpoint
curl -I https://puspelkes.jakarta.go.id/absensi

# Check logs
sudo tail -20 /var/log/nginx/puspelkes-absensi-error.log
```

---

## ATAU Gunakan Script Deployment Otomatis

Setelah upload file ke server, jalankan:

```bash
cd /tmp
sudo bash nginx-deployment-commands.sh
```

Script ini akan melakukan semua langkah di atas secara otomatis.

---

## Troubleshooting

### Jika SSH connection refused:
- Pastikan server 10.15.101.117 bisa diakses
- Pastikan SSH service running di server
- Cek firewall di server

### Jika nginx -t gagal:
- Check error message
- Pastikan semua path SSL certificate benar
- Pastikan syntax NGINX config benar

### Jika perlu rollback:
```bash
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.* /etc/nginx/sites-available/puspelkes.jakarta.go.id
sudo nginx -t && sudo systemctl reload nginx
```

---

## File-file Konfigurasi yang Tersedia

1. **nginx-puspelkes.conf** - Konfigurasi utama NGINX
2. **nginx-rate-limit.conf** - Rate limiting (optional)
3. **ufw-firewall-rules.sh** - Script firewall rules
4. **nginx-deployment-commands.sh** - Script deployment otomatis
5. **laravel-env-config.txt** - Konfigurasi Laravel .env

---

**Selamat deployment! 🚀**


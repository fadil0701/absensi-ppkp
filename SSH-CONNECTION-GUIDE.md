# Panduan Koneksi SSH ke Server Production

## Informasi Server
- **IP:** 10.15.101.117
- **Domain:** puspelkes.jakarta.go.id
- **User:** (sesuaikan dengan user yang ada)

---

## 1. Koneksi SSH Manual

### Koneksi dengan Password
```bash
ssh username@10.15.101.117
```

### Koneksi dengan SSH Key
```bash
ssh -i ~/.ssh/your_key username@10.15.101.117
```

### Koneksi dengan User Root (jika diizinkan)
```bash
ssh root@10.15.101.117
```

---

## 2. Upload File ke Server

### Menggunakan SCP (Secure Copy)

**Upload konfigurasi NGINX:**
```bash
scp nginx-puspelkes.conf username@10.15.101.117:/tmp/
```

**Upload semua file konfigurasi:**
```bash
scp nginx-puspelkes.conf nginx-rate-limit.conf ufw-firewall-rules.sh nginx-deployment-commands.sh username@10.15.101.117:/tmp/
```

### Menggunakan SFTP
```bash
sftp username@10.15.101.117
put nginx-puspelkes.conf /tmp/
put ufw-firewall-rules.sh /tmp/
exit
```

---

## 3. Langkah Deployment Setelah Masuk ke Server

Setelah berhasil masuk ke server via SSH:

### 3.1. Persiapan
```bash
# Masuk ke direktori /tmp
cd /tmp

# Beri permission execute untuk script
chmod +x ufw-firewall-rules.sh nginx-deployment-commands.sh

# Lihat file yang sudah di-upload
ls -la *.conf *.sh
```

### 3.2. Backup Konfigurasi Lama
```bash
# Backup konfigurasi NGINX yang ada
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.$(date +%Y%m%d_%H%M%S)

# Atau jika file belum ada, buat backup direktori
sudo cp -r /etc/nginx/sites-available /etc/nginx/sites-available.backup.$(date +%Y%m%d_%H%M%S)
```

### 3.3. Deploy Konfigurasi NGINX
```bash
# Copy konfigurasi baru
sudo cp /tmp/nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id

# Enable site (jika belum)
sudo ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/

# Test konfigurasi
sudo nginx -t
```

### 3.4. Buat Log Directories
```bash
# Buat direktori log jika belum ada
sudo mkdir -p /var/log/nginx

# Buat file log
sudo touch /var/log/nginx/puspelkes-absensi-access.log
sudo touch /var/log/nginx/puspelkes-absensi-error.log
sudo touch /var/log/nginx/puspelkes-access.log
sudo touch /var/log/nginx/puspelkes-error.log

# Set permission
sudo chown www-data:www-data /var/log/nginx/puspelkes*.log
sudo chmod 644 /var/log/nginx/puspelkes*.log
```

### 3.5. Reload NGINX
```bash
# Jika test berhasil, reload NGINX
sudo systemctl reload nginx

# Check status
sudo systemctl status nginx
```

### 3.6. Setup Firewall (Opsional)
```bash
# Jalankan script firewall
cd /tmp
sudo bash ufw-firewall-rules.sh

# Atau manual:
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 8000/tcp
sudo ufw deny 8081/tcp
sudo ufw --force enable
```

---

## 4. Deployment Otomatis (Menggunakan Script)

Alternatif: jalankan script deployment otomatis:

```bash
cd /tmp
sudo bash nginx-deployment-commands.sh
```

Script ini akan:
- Backup konfigurasi lama
- Copy konfigurasi baru
- Create log files
- Test dan reload NGINX
- Test koneksi

---

## 5. Verifikasi Deployment

### 5.1. Check NGINX Status
```bash
sudo systemctl status nginx
sudo nginx -t
```

### 5.2. Check Listening Ports
```bash
sudo netstat -tlnp | grep -E ':80|:443|:8000|:8081'
# atau
sudo ss -tlnp | grep -E ':80|:443|:8000|:8081'
```

### 5.3. Test Endpoints
```bash
# Test HTTP to HTTPS redirect
curl -I http://puspelkes.jakarta.go.id

# Test HTTPS
curl -I https://puspelkes.jakarta.go.id

# Test absensi endpoint
curl -I https://puspelkes.jakarta.go.id/absensi
```

### 5.4. Check Logs
```bash
# View access logs
sudo tail -f /var/log/nginx/puspelkes-absensi-access.log

# View error logs
sudo tail -f /var/log/nginx/puspelkes-absensi-error.log

# View general logs
sudo tail -f /var/log/nginx/puspelkes-access.log
```

---

## 6. Troubleshooting Koneksi SSH

### Jika koneksi ditolak:
```bash
# Test koneksi dengan verbose
ssh -v username@10.15.101.117

# Test koneksi ke port tertentu (jika non-standard)
ssh -p 2222 username@10.15.101.117

# Test dengan timeout
ssh -o ConnectTimeout=10 username@10.15.101.117
```

### Jika perlu menggunakan VPN:
Pastikan VPN sudah terkoneksi sebelum melakukan SSH

### Jika firewall memblokir:
```bash
# Test dengan telnet (jika SSH port diubah)
telnet 10.15.101.117 22
```

---

## 7. Checklist Sebelum Deploy

- [ ] Pastikan backend services running (port 8000, 8081)
- [ ] Backup konfigurasi NGINX yang ada
- [ ] Backup Laravel .env file
- [ ] Pastikan SSL certificate tersedia
- [ ] Test koneksi SSH berhasil
- [ ] File konfigurasi sudah di-upload ke server
- [ ] Memiliki akses sudo di server

---

## 8. Quick Reference Commands

```bash
# SSH ke server
ssh username@10.15.101.117

# Upload file
scp file.conf username@10.15.101.117:/tmp/

# Test NGINX config
sudo nginx -t

# Reload NGINX
sudo systemctl reload nginx

# Restart NGINX (jika reload tidak cukup)
sudo systemctl restart nginx

# View NGINX error log
sudo tail -f /var/log/nginx/error.log

# Check NGINX process
ps aux | grep nginx
```

---

**Catatan:** Ganti `username` dengan user yang sesuai untuk server production Anda.


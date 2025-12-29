# NGINX Reverse Proxy Deployment Guide
## Puspelkes Jakarta - Hardened Configuration

**Production Server IP:** 10.15.101.117  
**Domain:** puspelkes.jakarta.go.id  
**Backend Ports:** 8000 (Laravel), 8081 (Main System)

---

## SECTION 1: Final NGINX Server Block

**File:** `/etc/nginx/sites-available/puspelkes.jakarta.go.id`

See: `nginx-puspelkes.conf`

**Key Features:**
- HTTP to HTTPS redirect
- Main system proxied to 127.0.0.1:8081
- Laravel absensi at `/absensi` proxied to 127.0.0.1:8000
- Security headers (HSTS, X-Frame-Options, etc.)
- Rate limiting for `/absensi/login` (5 req/min)
- Separate access/error logs for absensi
- Path rewriting for Laravel compatibility
- SSL/TLS hardening

---

## SECTION 2: Rate Limiting Configuration

**File:** `/etc/nginx/conf.d/rate-limit.conf` (optional, or include in main nginx.conf)

See: `nginx-rate-limit.conf`

**Limits:**
- Login endpoints: 5 requests/minute (burst 2)
- General `/absensi`: 30 requests/second (burst 10)
- Connection limit: 20 concurrent connections per IP

---

## SECTION 3: UFW Firewall Rules

**File:** `ufw-firewall-rules.sh`

**Commands:**
```bash
chmod +x ufw-firewall-rules.sh
sudo ./ufw-firewall-rules.sh
```

**Rules Applied:**
- Allow: 22 (SSH), 80 (HTTP), 443 (HTTPS)
- Deny: 8000, 8081 (public access blocked)
- Backend ports accessible only from localhost

---

## SECTION 4: Laravel .env Configuration

**File:** `.env` (in Laravel project root)

See: `laravel-env-config.txt` for complete list

**Critical Settings:**
```
APP_URL=https://puspelkes.jakarta.go.id/absensi
SESSION_PATH=/absensi
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SANCTUM_STATEFUL_DOMAINS=puspelkes.jakarta.go.id
```

---

## SECTION 5: Reload & Validation Commands

**File:** `nginx-deployment-commands.sh`

**Quick Commands:**
```bash
# Test configuration
sudo nginx -t

# Reload NGINX
sudo systemctl reload nginx

# Check status
sudo systemctl status nginx

# View logs
sudo tail -f /var/log/nginx/puspelkes-absensi-error.log
sudo tail -f /var/log/nginx/puspelkes-absensi-access.log
```

**Full Deployment:**
```bash
chmod +x nginx-deployment-commands.sh
sudo ./nginx-deployment-commands.sh
```

---

## SECTION 6: Deployment Checklist

### Pre-Deployment
- [ ] Backup existing NGINX configuration
- [ ] Verify SSL certificates exist at `/etc/letsencrypt/live/puspelkes.jakarta.go.id/`
- [ ] Ensure backend services are running (ports 8000, 8081)
- [ ] Backup Laravel `.env` file

### NGINX Configuration
- [ ] Copy `nginx-puspelkes.conf` to `/etc/nginx/sites-available/puspelkes.jakarta.go.id`
- [ ] Create log directories and set permissions
- [ ] Test configuration: `sudo nginx -t`
- [ ] Reload NGINX: `sudo systemctl reload nginx`

### Firewall Configuration
- [ ] Review `ufw-firewall-rules.sh`
- [ ] Execute firewall rules script
- [ ] Verify ports 8000, 8081 are blocked from external access
- [ ] Test SSH access (critical!)

### Laravel Configuration
- [ ] Update `.env` file with proxy-safe settings
- [ ] Set `APP_URL` to include `/absensi` prefix
- [ ] Configure session settings for HTTPS
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear route cache: `php artisan route:clear`

### Testing
- [ ] Test HTTP to HTTPS redirect
- [ ] Test main system at root: `https://puspelkes.jakarta.go.id`
- [ ] Test absensi at: `https://puspelkes.jakarta.go.id/absensi`
- [ ] Verify login rate limiting (try multiple login attempts)
- [ ] Test Laravel routes (dashboard, presensi, etc.)
- [ ] Verify static assets load correctly (CSS, JS, images)
- [ ] Test CSRF protection (forms should work)
- [ ] Verify sessions work (login persists)
- [ ] Check backend ports are blocked (should timeout/fail)

### Monitoring
- [ ] Monitor error logs: `/var/log/nginx/puspelkes-absensi-error.log`
- [ ] Monitor access logs: `/var/log/nginx/puspelkes-absensi-access.log`
- [ ] Check NGINX status: `sudo systemctl status nginx`
- [ ] Verify rate limiting is working (check logs)

### Security Verification
- [ ] Test security headers (use browser DevTools or curl)
- [ ] Verify HSTS header is present
- [ ] Confirm X-Frame-Options prevents clickjacking
- [ ] Test that `.env` and sensitive files are blocked
- [ ] Verify SSL/TLS configuration (A+ rating on SSL Labs)

---

## IMPORTANT NOTES

### Path Handling
- Laravel routes are at root (`/`, `/login`, `/absensi`, etc.)
- NGINX rewrites `/absensi/*` to `/*` when forwarding to Laravel
- `APP_URL` must include `/absensi` prefix for URL generation

### Session & CSRF
- Sessions work via cookies with proper `SESSION_PATH=/absensi`
- CSRF tokens are validated correctly with proper proxy headers
- Ensure `X-Forwarded-Proto: https` is set

### Asset Loading
- Static assets (CSS, JS) should load from `/absensi/css/`, `/absensi/js/`
- Laravel's `asset()` helper uses `APP_URL` as base
- Storage files served from `/absensi/storage/`

### Troubleshooting

**Issue: 502 Bad Gateway**
- Check backend services are running: `sudo netstat -tlnp | grep -E '8000|8081'`
- Check NGINX error log: `sudo tail -50 /var/log/nginx/error.log`

**Issue: Assets not loading (404)**
- Verify `APP_URL` includes `/absensi` in Laravel `.env`
- Clear Laravel cache: `php artisan config:clear && php artisan cache:clear`
- Check asset paths in browser DevTools

**Issue: Session/CSRF errors**
- Verify `SESSION_SECURE_COOKIE=true` in `.env`
- Check proxy headers are set correctly (X-Forwarded-Proto, etc.)
- Ensure `SESSION_PATH=/absensi` matches NGINX location

**Issue: Redirect loops**
- Check NGINX redirect rules (proxy_redirect directives)
- Verify Laravel doesn't have conflicting redirects
- Check trailing slash handling

---

## ROLLBACK PROCEDURE

If issues occur:

```bash
# Restore backup
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.* /etc/nginx/sites-available/puspelkes.jakarta.go.id

# Test and reload
sudo nginx -t && sudo systemctl reload nginx

# Restore Laravel .env backup
cp .env.backup .env
php artisan config:clear
```

---

**Last Updated:** $(date)
**Configuration Version:** 1.0
**Laravel Version:** 11.x
**PHP Version:** 8.2


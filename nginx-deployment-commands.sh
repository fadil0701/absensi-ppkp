#!/bin/bash
# ============================================
# NGINX Deployment & Validation Commands
# Run these commands to deploy and test configuration
# ============================================

# 1. Backup existing configuration
sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.$(date +%Y%m%d_%H%M%S)

# 2. Copy new configuration (adjust path as needed)
sudo cp nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id

# 3. Enable site (if not already enabled)
sudo ln -sf /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-enabled/

# 4. Create log directories (if they don't exist)
sudo mkdir -p /var/log/nginx
sudo touch /var/log/nginx/puspelkes-absensi-access.log
sudo touch /var/log/nginx/puspelkes-absensi-error.log
sudo touch /var/log/nginx/puspelkes-access.log
sudo touch /var/log/nginx/puspelkes-error.log
sudo chown www-data:www-data /var/log/nginx/puspelkes*.log

# 5. Test NGINX configuration
echo "Testing NGINX configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✓ NGINX configuration is valid"
    
    # 6. Reload NGINX (graceful reload)
    echo "Reloading NGINX..."
    sudo systemctl reload nginx
    
    if [ $? -eq 0 ]; then
        echo "✓ NGINX reloaded successfully"
    else
        echo "✗ NGINX reload failed"
        exit 1
    fi
else
    echo "✗ NGINX configuration test failed - NOT reloading"
    exit 1
fi

# 7. Check NGINX status
echo ""
echo "NGINX Status:"
sudo systemctl status nginx --no-pager -l

# 8. Check if ports are listening
echo ""
echo "Checking listening ports:"
sudo netstat -tlnp | grep -E ':80|:443|:8000|:8081' || ss -tlnp | grep -E ':80|:443|:8000|:8081'

# 9. Test HTTP to HTTPS redirect
echo ""
echo "Testing HTTP to HTTPS redirect:"
curl -I http://puspelkes.jakarta.go.id 2>&1 | head -5

# 10. Test main system endpoint
echo ""
echo "Testing main system (should work):"
curl -k -I https://puspelkes.jakarta.go.id 2>&1 | head -5

# 11. Test absensi endpoint
echo ""
echo "Testing absensi system:"
curl -k -I https://puspelkes.jakarta.go.id/absensi 2>&1 | head -5

# 12. Test that backend ports are blocked (should fail or timeout)
echo ""
echo "Testing backend port blocking (should fail/timeout):"
echo "Port 8000 (should be blocked from external):"
timeout 3 curl -I http://10.15.101.117:8000 2>&1 | head -3 || echo "Connection blocked or timeout (expected)"

echo "Port 8081 (should be blocked from external):"
timeout 3 curl -I http://10.15.101.117:8081 2>&1 | head -3 || echo "Connection blocked or timeout (expected)"

# 13. Check rate limiting logs
echo ""
echo "Recent absensi access logs:"
sudo tail -20 /var/log/nginx/puspelkes-absensi-access.log 2>/dev/null || echo "No logs yet"

# 14. Check error logs
echo ""
echo "Recent absensi error logs:"
sudo tail -20 /var/log/nginx/puspelkes-absensi-error.log 2>/dev/null || echo "No errors"

echo ""
echo "============================================"
echo "Deployment validation complete!"
echo "============================================"


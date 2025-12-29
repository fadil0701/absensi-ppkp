#!/bin/bash
# ============================================
# Script untuk Deploy Konfigurasi ke Server Production
# Server: 10.15.101.117
# ============================================

SERVER_IP="10.15.101.117"
SERVER_USER="root"  # Ganti dengan user yang sesuai
SSH_KEY=""  # Ganti dengan path ke SSH key jika menggunakan key-based auth
# contoh: SSH_KEY="-i ~/.ssh/puspelkes_key"

echo "============================================"
echo "Deploy Konfigurasi NGINX ke Server Production"
echo "Server: $SERVER_IP"
echo "============================================"
echo ""

# 1. Test koneksi SSH
echo "1. Testing SSH connection..."
if [ -z "$SSH_KEY" ]; then
    ssh -o ConnectTimeout=5 $SERVER_USER@$SERVER_IP "echo 'SSH connection OK'" 2>/dev/null
else
    ssh $SSH_KEY -o ConnectTimeout=5 $SERVER_USER@$SERVER_IP "echo 'SSH connection OK'" 2>/dev/null
fi

if [ $? -ne 0 ]; then
    echo "❌ Gagal koneksi ke server!"
    echo "Pastikan:"
    echo "  - Server $SERVER_IP bisa diakses"
    echo "  - SSH service running"
    echo "  - User $SERVER_USER memiliki akses SSH"
    echo "  - Atau gunakan: ssh $SERVER_USER@$SERVER_IP"
    exit 1
fi

echo "✓ SSH connection OK"
echo ""

# 2. Copy file ke server
echo "2. Copying configuration files to server..."

if [ -z "$SSH_KEY" ]; then
    SCP_CMD="scp"
else
    SCP_CMD="scp $SSH_KEY"
fi

# Copy NGINX config
echo "  - Copying nginx-puspelkes.conf..."
$SCP_CMD nginx-puspelkes.conf $SERVER_USER@$SERVER_IP:/tmp/nginx-puspelkes.conf

# Copy rate limit config (optional)
if [ -f "nginx-rate-limit.conf" ]; then
    echo "  - Copying nginx-rate-limit.conf..."
    $SCP_CMD nginx-rate-limit.conf $SERVER_USER@$SERVER_IP:/tmp/nginx-rate-limit.conf
fi

# Copy firewall rules script
if [ -f "ufw-firewall-rules.sh" ]; then
    echo "  - Copying ufw-firewall-rules.sh..."
    $SCP_CMD ufw-firewall-rules.sh $SERVER_USER@$SERVER_IP:/tmp/ufw-firewall-rules.sh
fi

# Copy deployment commands
if [ -f "nginx-deployment-commands.sh" ]; then
    echo "  - Copying nginx-deployment-commands.sh..."
    $SCP_CMD nginx-deployment-commands.sh $SERVER_USER@$SERVER_IP:/tmp/nginx-deployment-commands.sh
fi

echo "✓ Files copied to /tmp/ on server"
echo ""

# 3. Instructions
echo "============================================"
echo "Next Steps:"
echo "============================================"
echo ""
echo "1. SSH ke server:"
echo "   ssh $SERVER_USER@$SERVER_IP"
echo ""
echo "2. Setelah masuk ke server, jalankan:"
echo "   cd /tmp"
echo "   chmod +x ufw-firewall-rules.sh nginx-deployment-commands.sh"
echo ""
echo "3. Backup konfigurasi NGINX yang ada:"
echo "   sudo cp /etc/nginx/sites-available/puspelkes.jakarta.go.id /etc/nginx/sites-available/puspelkes.jakarta.go.id.backup.\$(date +%Y%m%d_%H%M%S)"
echo ""
echo "4. Copy konfigurasi baru:"
echo "   sudo cp /tmp/nginx-puspelkes.conf /etc/nginx/sites-available/puspelkes.jakarta.go.id"
echo ""
echo "5. Test dan reload NGINX:"
echo "   sudo nginx -t && sudo systemctl reload nginx"
echo ""
echo "6. Setup firewall (optional):"
echo "   sudo ./ufw-firewall-rules.sh"
echo ""
echo "ATAU jalankan script deployment otomatis:"
echo "   sudo bash /tmp/nginx-deployment-commands.sh"
echo ""
echo "============================================"


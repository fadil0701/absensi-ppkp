#!/bin/bash
# ============================================
# UFW Firewall Rules for Puspelkes Server
# Production IP: 10.15.101.117
# Blocks public access to backend ports 8000 & 8081
# ============================================

# Enable UFW if not already enabled
sudo ufw --force enable

# Allow SSH (CRITICAL - do this first!)
sudo ufw allow 22/tcp comment 'SSH'

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'

# Explicitly deny public access to backend ports
# These will only be accessible from localhost
sudo ufw deny 8000/tcp comment 'Block Laravel backend from public'
sudo ufw deny 8081/tcp comment 'Block Main system backend from public'

# Allow localhost access (this is default, but being explicit)
# Note: UFW doesn't directly control localhost, but this ensures
# the ports are not accessible from external interfaces

# Optional: Allow specific IP ranges if needed for monitoring/admin
# sudo ufw allow from 10.0.0.0/8 to any port 8000 comment 'Allow internal network'
# sudo ufw allow from 192.168.0.0/16 to any port 8000 comment 'Allow private network'

# Show status
echo "UFW Status:"
sudo ufw status verbose

# Show numbered rules (for easy removal if needed)
echo ""
echo "UFW Numbered Rules:"
sudo ufw status numbered


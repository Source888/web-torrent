# /bin/bash
# This script installs the necessary dependencies for the Web Torrent application.

echo "Installing dependencies for Web Torrent..."
# Update package list
sudo apt-get update
# Install PHP and required extensions
sudo apt-get install -y php php-cli php-curl php-zip php-xml php-mbstring
#Install Torrent dameon
# Exit on errors
set -e

echo "Installing transmission-daemon..."
sudo apt-get update
sudo apt-get install -y transmission-daemon

echo "Stopping transmission to apply settings..."
sudo systemctl stop transmission-daemon

# Ensure config directory exists
CONFIG_DIR="/var/lib/transmission-daemon/.config/transmission-daemon"
sudo mkdir -p "$CONFIG_DIR"

# Copy default config if not exists
if [ ! -f "$CONFIG_DIR/settings.json" ]; then
    echo "Copying default settings.json to $CONFIG_DIR"
    sudo cp /etc/transmission-daemon/settings.json "$CONFIG_DIR/"
fi

# Set ownership to debian-transmission
echo "Fixing permissions..."
sudo chown -R debian-transmission:debian-transmission /var/lib/transmission-daemon

# Disable whitelist in config
echo "Disabling RPC whitelist..."
sudo sed -i 's/"rpc-whitelist-enabled": true/"rpc-whitelist-enabled": false/' "$CONFIG_DIR/settings.json"

# Patch systemd unit to use --foreground
SERVICE_FILE="/usr/lib/systemd/system/transmission-daemon.service"
if grep -q -- "--foreground" "$SERVICE_FILE"; then
    echo "Service file already patched."
else
    echo "Patching service file to use --foreground..."
    sudo sed -i 's|ExecStart=.*|ExecStart=/usr/bin/transmission-daemon --log-level=error --foreground|' "$SERVICE_FILE"
fi

# Reload and start service
echo "Reloading systemd and starting transmission-daemon..."
sudo systemctl daemon-reload
sudo systemctl enable transmission-daemon
sudo systemctl restart transmission-daemon

# Final status check
echo "Transmission status:"
systemctl status transmission-daemon --no-pager


#Create neccessary directories
mkdir -p /torrents
mkdir -p /downloads

# Set permissions for the directories
sudo chown -R www-data:www-data /torrents
sudo chown -R www-data:www-data /downloads
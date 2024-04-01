#!/bin/bash

# Define variables
HUMHUB_VERSION="1.15.4"  # Specify the desired version of HumHub
HUMHUB_INSTALL_DIR="/var/www/humhub"  # Specify the installation directory
HUMHUB_DOWNLOAD_URL="https://download.humhub.com/downloads/install/humhub-$HUMHUB_VERSION.zip"  # Download URL for HumHub
DB_NAME="humhubdb"  # Database name for HumHub
DB_USER="humhubuser"  # Database user for HumHub
DB_PASS="password123"  # Password for the database user

# Create a directory for HumHub
mkdir -p "$HUMHUB_INSTALL_DIR"

# Download HumHub
wget -O /tmp/humhub.zip "$HUMHUB_DOWNLOAD_URL"

# Unzip HumHub archive
unzip /tmp/humhub.zip -d "$HUMHUB_INSTALL_DIR"

# Remove the downloaded zip file
rm /tmp/humhub.zip

# Set correct permissions
chown -R www-data:www-data "$HUMHUB_INSTALL_DIR"
chmod -R 755 "$HUMHUB_INSTALL_DIR"

# Create a symbolic link for Apache (assuming Apache is used)
ln -s "$HUMHUB_INSTALL_DIR" /var/www/html

# Set up virtual host configuration for Apache (assuming Apache is used)
cat <<EOF > /etc/apache2/sites-available/humhub.conf
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/html/humhub

    <Directory /var/www/html/humhub>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/humhub-error.log
    CustomLog ${APACHE_LOG_DIR}/humhub-access.log combined
</VirtualHost>
EOF

# Enable the virtual host
a2ensite humhub.conf

# Reload Apache
systemctl reload apache2

# Create a MySQL database for HumHub
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Output success message
echo "HumHub installation completed successfully."
echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "Database password: $DB_PASS"

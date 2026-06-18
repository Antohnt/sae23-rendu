#!/bin/bash
# deploy_www.sh
# Deploy SAE23 website to Apache on CT 104 (LAMPP stack)
# Run from usine/ folder: bash scripts/deploy_www.sh

if grep -q $'\r' "$0"; then
    sed -i 's/\r$//' "$0"
    exec bash "$0" "$@"
fi

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
WWW_DIR="$PROJECT_DIR/www"
APACHE_SITE="/etc/apache2/sites-available/sae23.conf"


if [ ! -d "$WWW_DIR" ]; then
    echo "Error: www folder not found at $WWW_DIR"
    exit 1
fi

sudo a2enmod rewrite ssl headers

if [ ! -f /etc/ssl/certs/sae23-selfsigned.crt ]; then
    sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/private/sae23-selfsigned.key \
        -out /etc/ssl/certs/sae23-selfsigned.crt \
        -subj "/CN=sae23-ct/O=SAE23/C=FR"
    sudo chmod 600 /etc/ssl/private/sae23-selfsigned.key
fi

sudo tee "$APACHE_SITE" > /dev/null << 'APACHEEOF'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sae23-error.log
    CustomLog ${APACHE_LOG_DIR}/sae23-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/sae23-selfsigned.crt
    SSLCertificateKeyFile /etc/ssl/private/sae23-selfsigned.key

    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sae23-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/sae23-ssl-access.log combined
</VirtualHost>
APACHEEOF

sudo a2dissite 000-default.conf || true
sudo a2ensite sae23.conf

sudo rm -f /var/www/html/index.html
sudo cp -a "$WWW_DIR"/. /var/www/html/
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/

sudo apache2ctl configtest
sudo systemctl restart apache2

echo ""
echo "Site HTTP  : http://localhost/"
echo "Site HTTPS : https://localhost/"
echo "Pages      : index.php, connexion.php, consultation.php, gestion.php, admin.php, projet.php"
echo "PhpMyAdmin : http://localhost/phpmyadmin/ (root / sae23)"
echo ""
echo "Test accounts:"
echo "  admin / admin"
echo "  gestionnaire_e / geste"
echo "  gestionnaire_c / gestc"

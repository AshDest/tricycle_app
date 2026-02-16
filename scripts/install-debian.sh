#!/bin/bash
#############################################
# Installation des prérequis sur Debian VPS
# Tricycle App - New Technology Hub Sarl
# À exécuter en tant que root ou avec sudo
#############################################
set -e

echo "🚀 Installation des prérequis sur Debian..."

# Mettre à jour le système
echo "📦 Mise à jour du système..."
apt update && apt upgrade -y

# Installer les dépendances de base
apt install -y curl wget gnupg2 ca-certificates lsb-release apt-transport-https \
    software-properties-common unzip git acl

# Ajouter le repository PHP (Sury pour Debian)
echo "📦 Ajout du repository PHP..."
curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
apt update

# Installer PHP 8.2 et extensions
echo "📦 Installation de PHP 8.2..."
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-readline php8.2-opcache

# Installer Composer
echo "📦 Installation de Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Installer Node.js 20
echo "📦 Installation de Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Installer Nginx
echo "📦 Installation de Nginx..."
apt install -y nginx

# Installer MariaDB
echo "📦 Installation de MariaDB..."
apt install -y mariadb-server mariadb-client

# Démarrer et activer les services
echo "🔧 Configuration des services..."
systemctl enable php8.2-fpm
systemctl enable nginx
systemctl enable mariadb
systemctl start php8.2-fpm
systemctl start nginx
systemctl start mariadb

# Créer l'utilisateur deploy s'il n'existe pas
if ! id "deploy" &>/dev/null; then
    echo "👤 Création de l'utilisateur deploy..."
    useradd -m -s /bin/bash deploy
    usermod -aG www-data deploy
    echo "⚠️ N'oubliez pas de définir un mot de passe: sudo passwd deploy"
fi

# Configurer sudo pour deploy
echo "🔐 Configuration sudo pour deploy..."
cat > /etc/sudoers.d/deploy << 'SUDOERS'
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart nginx
deploy ALL=(ALL) NOPASSWD: /usr/bin/chown
deploy ALL=(ALL) NOPASSWD: /usr/bin/chmod
deploy ALL=(ALL) NOPASSWD: /usr/bin/mkdir
deploy ALL=(ALL) NOPASSWD: /usr/bin/cp
deploy ALL=(ALL) NOPASSWD: /usr/bin/ln
deploy ALL=(ALL) NOPASSWD: /usr/bin/rm
deploy ALL=(ALL) NOPASSWD: /usr/sbin/nginx
SUDOERS
chmod 440 /etc/sudoers.d/deploy

# Créer le répertoire de l'application
echo "📁 Création du répertoire de l'application..."
mkdir -p /var/www/tricycle_app
chown -R deploy:deploy /var/www/tricycle_app

# Vérifier les installations
echo ""
echo "✅ Installation terminée!"
echo ""
echo "📋 Versions installées:"
php -v | head -1
composer -V
node -v
npm -v
nginx -v 2>&1
mysql --version
echo ""
echo "📝 Prochaines étapes:"
echo "   1. Configurer MariaDB: sudo mysql_secure_installation"
echo "   2. Créer la base de données (voir DEPLOYMENT.md)"
echo "   3. Configurer SSH pour l'utilisateur deploy"
echo "   4. Cloner et déployer l'application"

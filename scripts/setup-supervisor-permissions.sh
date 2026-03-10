#!/bin/bash

# =============================================================
# Script de configuration des permissions Supervisor
# Permet à www-data de contrôler supervisor sans mot de passe
# =============================================================

set -e

echo "============================================="
echo "Configuration des permissions Supervisor"
echo "============================================="
echo ""

# Créer le fichier sudoers pour www-data
SUDOERS_FILE="/etc/sudoers.d/tricycle-supervisor"

echo "[INFO] Création des permissions sudoers..."

sudo tee $SUDOERS_FILE > /dev/null << 'EOF'
# Permettre à www-data de contrôler supervisor sans mot de passe
# Pour l'application Tricycle App
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl start tricycle-queue-worker:*
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl stop tricycle-queue-worker:*
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart tricycle-queue-worker:*
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl status
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl reread
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl update
EOF

# Définir les permissions correctes
sudo chmod 440 $SUDOERS_FILE

# Vérifier la syntaxe
echo "[INFO] Vérification de la syntaxe sudoers..."
sudo visudo -c -f $SUDOERS_FILE

echo ""
echo "============================================="
echo "Configuration terminée avec succès!"
echo "============================================="
echo ""
echo "L'utilisateur www-data peut maintenant contrôler"
echo "les workers supervisor depuis l'interface web."
echo ""
echo "Test avec: sudo -u www-data sudo supervisorctl status"
echo ""


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

# Supprimer l'ancien fichier s'il existe
sudo rm -f $SUDOERS_FILE

# Créer le nouveau fichier avec les bonnes permissions
sudo bash -c "cat > $SUDOERS_FILE << 'SUDOERS_EOF'
# Permettre a www-data de controler supervisor sans mot de passe
# Pour application Tricycle App
www-data ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
SUDOERS_EOF"

# Définir les permissions correctes
sudo chmod 440 $SUDOERS_FILE

# Vérifier la syntaxe
echo "[INFO] Vérification de la syntaxe sudoers..."
if sudo visudo -c -f $SUDOERS_FILE; then
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
else
    echo ""
    echo "ERREUR: La syntaxe du fichier sudoers est invalide!"
    echo "Suppression du fichier pour éviter les problèmes..."
    sudo rm -f $SUDOERS_FILE
    exit 1
fi


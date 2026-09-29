#!/bin/bash
set -e
cd /var/www/azohub

# Repasse le site en ligne dans TOUS les cas en sortie de script (succès, erreur, Ctrl+C) —
# sans ce trap, un échec après le "down" ci-dessous (set -e arrête tout net) laissait le site
# en mode maintenance indéfiniment, jusqu'à un "php artisan up" manuel.
trap 'php artisan up >/dev/null 2>&1 || true' EXIT

echo "→ Mode maintenance..."
php artisan down --retry=15

echo "→ Sauvegarde de la base avant migration..."
# Même script que la sauvegarde quotidienne planifiée (cron) : au cas où une migration se passe
# mal, on peut restaurer ce dump précis (voir deploy/README.md) plutôt que perdre jusqu'à 24h de
# données en repartant de la dernière sauvegarde nocturne.
bash /home/ubuntu/backup-db.sh

echo "→ Récupération du code..."
git pull origin main

echo "→ Dépendances PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Migrations..."
php artisan migrate --force

echo "→ Assets front-end..."
npm ci --no-audit --no-fund
npm run build

echo "→ Permissions (storage)..."
# php-fpm (www-data) compile des vues à la volée sur une visite en direct pendant qu'un
# déploiement tourne : le fichier créé appartient alors à www-data en 0644, illisible en
# écriture par 'ubuntu' (propriétaire du reste) — le déploiement suivant échoue sur
# 'Permission denied' en tentant de régénérer CE fichier précis via artisan view:cache.
sudo chown -R ubuntu:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

echo "→ Caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Redémarrage de la file d'attente..."
sudo supervisorctl restart azohub-worker:*

echo "✓ Déploiement terminé : $(git rev-parse --short HEAD)"

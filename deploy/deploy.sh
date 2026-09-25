#!/bin/bash
set -e
cd /var/www/azohub

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

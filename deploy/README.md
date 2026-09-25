# Configuration serveur (référence)

Ce dossier n'est **pas** appliqué automatiquement — c'est une copie de sauvegarde de la
configuration réellement en place sur le VPS OVH (`/etc/nginx/sites-available/azohub` et
`/var/www/azohub/deploy.sh`), gardée ici pour ne pas avoir à la reconstruire de mémoire
après un changement.

## Piège récurrent : les blocs `location` statiques doivent retomber sur PHP

Deux bugs de production distincts sont venus du même oubli dans ce fichier : un bloc
`location` pensé pour servir un fichier statique (images/CSS/JS compilés, `favicon.ico`,
`robots.txt`) interceptait aussi une route **dynamique** qui porte le même nom ou la même
extension (`/livewire/livewire.min.js`, puis `/robots.txt` une fois devenu une route Laravel
plutôt qu'un fichier dans `public/`) — et renvoyait un 404 **avant même que Laravel ne soit
sollicité**, sans que rien côté application n'y soit pour quelque chose.

**Tout bloc qui sert des fichiers statiques doit avoir un `try_files` qui retombe sur
`/index.php?$query_string`** :

```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    try_files $uri /index.php?$query_string;
    ...
}

location = /favicon.ico { try_files $uri /index.php?$query_string; ... }
location = /robots.txt  { try_files $uri /index.php?$query_string; ... }
```

Si un fichier existe réellement sur le disque, Nginx le sert directement (rapide). S'il
n'existe pas, la requête part vers Laravel au lieu de s'arrêter sur un 404 muet.

## Redéploiement du serveur (mémo)

Le fichier `deploy.sh` (racine du projet sur le serveur, copie de sauvegarde ci-contre) fait
tout : `git pull`, `composer install`, migrations, build des assets, mise en cache,
redémarrage de la file d'attente. Après toute modification de `nginx.conf` ci-dessous, il
faut la recopier manuellement sur le serveur puis `sudo nginx -t && sudo systemctl reload
nginx` — ce fichier n'est lu par rien automatiquement. Même chose pour `deploy.sh` : une
modification faite directement sur le serveur doit être recopiée ici pour ne pas la perdre.

## Piège récurrent : fichiers de vue compilés appartenant à `www-data`

`php artisan view:cache` (exécuté par `ubuntu`) échoue par intermittence sur
`Permission denied` en tentant de régénérer un fichier de `storage/framework/views/` qu'une
visite en direct a fait recompiler entre-temps par PHP-FPM (utilisateur `www-data`, mode 0644
— illisible en écriture par `ubuntu`, même s'il appartient au groupe `www-data`). D'où l'étape
`chown -R ubuntu:www-data storage bootstrap/cache` + `chmod -R ug+rwX` juste avant la mise en
cache dans `deploy.sh` : elle remet tout au bon propriétaire/droits avant que le déploiement
n'y touche.

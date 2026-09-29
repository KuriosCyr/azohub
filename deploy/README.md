# Configuration serveur (référence)

Ce dossier n'est **pas** appliqué automatiquement — c'est une copie de sauvegarde de la
configuration réellement en place sur le VPS OVH (`/etc/nginx/sites-available/azohub` et
`/var/www/azohub/deploy.sh`), gardée ici pour ne pas avoir à la reconstruire de mémoire
après un changement.

## API Payout FedaPay : non autorisée sur ce compte (au 29/09/2026)

`PaymentService::initiatePayout()` (retraits automatisés, voir son propre commit) est
codée et testée (logique métier) mais **ne peut pas encore être utilisée en pratique** :
vérifié en sandbox, même un simple `GET /v1/payouts` renvoie `403 {"message":"Opération
non autorisée"}` sur ce compte FedaPay — l'API Transaction (paiements entrants) fonctionne
normalement, mais l'API Payout (virements sortants) est une fonctionnalité à part, que
FedaPay doit activer séparément (souvent après une vérification KYC/compliance
supplémentaire, les virements sortants étant plus sensibles). Le bouton manuel ("Marquer
comme payé") reste donc la seule voie fonctionnelle tant que ce n'est pas activé.

**Pour débloquer** : contacter le support FedaPay (ou l'interlocuteur commercial du
compte) et demander explicitement l'activation de l'API Payout. Une fois activé, revérifier
en sandbox avant de passer en environnement `live` (`FEDAPAY_ENVIRONMENT` dans `.env`) — et
vérifier au passage le champ `mode` de `Payout::create()` (`mtn` confirmé fonctionnel côté
validation, `moov`/`celtiis` devinés par analogie avec `payment_method` mais jamais
vérifiés faute d'accès à l'API à ce moment-là).

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

## En cas de déploiement raté : comment revenir en arrière

`deploy.sh` n'a pas de rollback automatique (ni le code, ni les migrations), mais il pose
deux filets avant de toucher à quoi que ce soit :

1. **`php artisan down`** au tout début (site en maintenance le temps du déploiement), remis
   automatiquement en ligne à la fin du script — même s'il échoue en cours de route (`trap ...
   EXIT`). Si le site reste bloqué en maintenance malgré tout (ex. serveur coupé en plein
   déploiement), `php artisan up` à la main suffit.
2. **Une sauvegarde de la base** juste avant `migrate --force`, avec le même script que la
   sauvegarde nocturne planifiée (`/home/ubuntu/backup-db.sh` → `/var/backups/azohub-db/`).

**Pour revenir en arrière après un déploiement qui a mal tourné :**

```bash
# 1. Revenir au commit précédent
cd /var/www/azohub
git log --oneline -5              # repérer le commit d'avant
git reset --hard <commit-d-avant>

# 2. Si une migration a corrompu des données (pas seulement "cassé le code") : restaurer le
#    dump pris juste avant cette migration plutôt que de compter sur `migrate:rollback`
#    (les méthodes down() ne sont pas systématiquement tenues à jour) :
ls -la /var/backups/azohub-db/    # repérer le fichier au bon horodatage
gunzip < /var/backups/azohub-db/azohub_AAAAMMJJ_HHMMSS.sql.gz | mysql -u<user> -p azohub

# 3. Redéployer l'ancien code (recompile assets, recache, relance le worker)
bash deploy.sh
```

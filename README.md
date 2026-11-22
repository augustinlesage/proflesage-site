# proflesage.fr

Site publié sur l'hébergement OVH par l'intégration Git (déploiement automatique à chaque envoi sur `main`).

- `index.html` : page d'accueil
- `flashcards/` : appli flashcards (version élèves), allégée par `outils/alleger_flashcards.py` : `index.html` sans les gros fichiers, `a/` images WebP + polices (cache d'un an), `sw.js` (hors connexion après une visite), `lots.json` + `couv/` (liste des lots et couvertures lues par la rubrique Entraînements du Laboratoire, produites par `outils/lots_flashcards.js`)
- `mots/` : « Les mots nouveaux », le vocabulaire de la classe (version élèves, copie de la page Claude)
- `labo/` : Laboratoire de la langue (`index.html`), espace professeur et tableau de bord (`prof.html`), serveur PHP (`api/index.php`). Construits par `laboratoire/src/build_ovh.py DOSSIER_DU_SITE [--flashcards]`. Les données des élèves sont dans `labo-donnees/`, à côté de `www/` (jamais dans le dépôt). `labo/api/config.php` est écrit à la publication à partir du secret GitHub `PROF_MDP`.
- `.htaccess` : https, compression

Contenus sous licence CC BY-NC-SA 4.0.

## Mettre à jour les flashcards

1. `python3 build_ovh.py DOSSIER_DU_SITE --flashcards` (dans `laboratoire/src`) : version élève + connexion au Laboratoire + allègement (nécessite Pillow).
3. Valider `flashcards/` en entier (les anciens fichiers de `a/` sont supprimés par le script), puis envoyer sur `main`.

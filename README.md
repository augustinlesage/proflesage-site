# proflesage.fr

Site publié sur l'hébergement OVH par l'intégration Git (déploiement automatique à chaque envoi sur `main`).

- `index.html` : page d'accueil
- `flashcards/` : appli flashcards (version élèves), allégée par `outils/alleger_flashcards.py` : `index.html` sans les gros fichiers, `a/` images WebP + polices (cache d'un an), `sw.js` (hors connexion après une visite)
- `mots/` : « Les mots nouveaux », le vocabulaire de la classe (version élèves, copie de la page Claude)
- `.htaccess` : https, redirection de `/labo` (provisoire, vers Netlify), compression

Contenus sous licence CC BY-NC-SA 4.0.

## Mettre à jour les flashcards

1. Construire la version élève (`build.py --eleve`) et y ajouter le script `FC_BILAN_API` juste après `<meta charset="utf-8">`.
2. `python3 outils/alleger_flashcards.py "Flashcards - élèves.html" flashcards/` (nécessite Pillow).
3. Valider `flashcards/` en entier (les anciens fichiers de `a/` sont supprimés par le script), puis envoyer sur `main`.

#!/usr/bin/env node
/* Liste des lots de l'appli flashcards (version élève) → flashcards/lots.json
   Lue par le Laboratoire (rubrique « Entraînements ») : chaque lot publié sur proflesage.fr/flashcards
   y apparaît tout seul, avec sa rubrique, ses niveaux et son nombre de cartes.

   Usage : node outils/lots_flashcards.js flashcards/index.html flashcards/lots.json
   (appelé par alleger_flashcards.py ; nécessite Playwright + Chromium.)
   La page est ouverte dans un navigateur sans fenêtre, puis on lit l'état des lots tel que l'appli le calcule
   (lots scindés par niveau, rubriques, niveaux par défaut) : la liste est donc toujours celle que voient les élèves. */
const path = require('path'), fs = require('fs');
const [src, out] = process.argv.slice(2);
if (!src || !out) { console.error('usage : node lots_flashcards.js flashcards/index.html flashcards/lots.json'); process.exit(2); }
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch();
  try {
    const p = await b.newPage();
    await p.goto('file://' + path.resolve(src));
    await p.waitForFunction(() => typeof state !== 'undefined' && state.decks && state.decks.length, null, { timeout: 60000 });
    const r = await p.evaluate(() => {
      const lots = state.decks.filter(d => d.cards && d.cards.length).map(d => {
        const o = { id: d.id, nom: String(d.name || d.id), rub: deckCat(d), niv: deckLevels(d), n: d.cards.length };
        if (d.levelOf) o.base = d.levelOf; /* lot scindé par niveau : id du lot d'origine */
        return o;
      });
      const util = new Set(lots.map(l => l.rub));
      return { rubriques: DECK_CATS.filter(c => util.has(c.id)).map(c => ({ id: c.id, nom: c.label })), lots };
    });
    const d = new Date(), z = n => String(n).padStart(2, '0');
    r.maj = z(d.getDate()) + '/' + z(d.getMonth() + 1) + '/' + d.getFullYear();
    fs.writeFileSync(out, JSON.stringify(r, null, 0).replace(/\},\{"id"/g, '},\n{"id"') + '\n');
    console.log('lots.json : ' + r.lots.length + ' lots, ' + r.rubriques.length + ' rubriques');
  } finally { await b.close(); }
})().catch(e => { console.error('lots.json non généré : ' + e.message); process.exit(1); });

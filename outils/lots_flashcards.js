#!/usr/bin/env node
/* Liste des lots de l'appli flashcards (version élève) → flashcards/lots.json + flashcards/couv/*.jpg
   Lue par le Laboratoire (rubrique « Entraînements ») : chaque lot publié sur proflesage.fr/flashcards
   y apparaît tout seul, avec sa rubrique, ses niveaux, son nombre de cartes et sa couverture.

   Usage : node outils/lots_flashcards.js flashcards/index.html flashcards/lots.json
   (appelé par alleger_flashcards.py ; nécessite Playwright + Chromium.)
   La page est ouverte dans un navigateur sans fenêtre (petit serveur local, pour que les images se chargent
   comme en ligne), puis on lit l'état des lots tel que l'appli le calcule (lots scindés par niveau, rubriques,
   niveaux par défaut) et on photographie la vignette de chaque lot de l'accueil élève (couverture).
   Les couvertures sont nommées par empreinte (couv/<id>-<empreinte>.jpg) : cache navigateur sans risque. */
const path = require('path'), fs = require('fs'), http = require('http'), crypto = require('crypto');
const [src, out] = process.argv.slice(2);
if (!src || !out) { console.error('usage : node lots_flashcards.js flashcards/index.html flashcards/lots.json'); process.exit(2); }
const { chromium } = require('playwright');
const RACINE = path.dirname(path.resolve(src)), PAGE = path.basename(src);
const COUV = path.join(path.dirname(path.resolve(out)), 'couv');
const TYPES = { '.html': 'text/html; charset=utf-8', '.js': 'text/javascript', '.webp': 'image/webp', '.jpg': 'image/jpeg', '.png': 'image/png', '.woff': 'font/woff', '.woff2': 'font/woff2', '.json': 'application/json', '.pdf': 'application/pdf' };

(async () => {
  const srv = http.createServer((q, r) => {
    let f = decodeURIComponent(q.url.split('?')[0]); if (f === '/') f = '/' + PAGE;
    const p = path.join(RACINE, path.normalize(f));
    if (!p.startsWith(RACINE) || !fs.existsSync(p) || fs.statSync(p).isDirectory()) { r.writeHead(404); return r.end(); }
    r.writeHead(200, { 'Content-Type': TYPES[path.extname(p)] || 'application/octet-stream' }); fs.createReadStream(p).pipe(r);
  });
  await new Promise(ok => srv.listen(0, '127.0.0.1', ok));
  const b = await chromium.launch();
  try {
    const p = await b.newPage({ viewport: { width: 1200, height: 900 }, deviceScaleFactor: 2 });
    /* accueil élève sur « Toutes » les classes, sans mode Dys (vignettes visibles) */
    await p.addInitScript(() => { try { localStorage.setItem('flashcards.eleve', JSON.stringify({ lv: '', dys: false })); } catch (e) { } });
    await p.goto('http://127.0.0.1:' + srv.address().port + '/' + PAGE + '?accueil'); /* ?accueil : sinon la page renvoie au Laboratoire */
    await p.waitForFunction(() => typeof state !== 'undefined' && state.decks && state.decks.length, null, { timeout: 60000 });
    const r = await p.evaluate(() => {
      const lots = state.decks.filter(d => d.cards && d.cards.length).map(d => {
        const o = { id: d.id, nom: String(d.name || d.id), rub: deckCat(d), niv: deckLevels(d), n: d.cards.length };
        if (d.levelOf) o.base = d.levelOf; /* lot scindé par niveau : id du lot d'origine */
        return o;
      });
      /* liens vers d'autres sites, affichés comme des lots dans leur rubrique (CAT_LINKS : Lire au collège, Cémantix…), quel que soit le niveau */
      const liens = [];
      Object.entries(typeof CAT_LINKS !== 'undefined' ? CAT_LINKS : {}).forEach(([rub, L]) => L.forEach(l => liens.push({ id: l.id, nom: l.title, sous: l.sub, url: l.url, rub })));
      const util = new Set(lots.map(l => l.rub).concat(liens.map(l => l.rub)));
      return { rubriques: DECK_CATS.filter(c => util.has(c.id)).map(c => ({ id: c.id, nom: c.label })), lots, liens };
    });

    /* couvertures : vignette de chaque lot sur l'accueil élève */
    await p.waitForSelector('.el-tile[data-el-deck] .el-thumb', { timeout: 60000 });
    await p.evaluate(() => document.fonts && document.fonts.ready);
    await p.waitForLoadState('networkidle').catch(() => { });
    await p.waitForTimeout(1500);
    fs.mkdirSync(COUV, { recursive: true });
    const gardes = new Set(); let nb = 0;
    for (const l of r.lots) {
      const el = await p.$(`.el-tile[data-el-deck="${l.id.replace(/"/g, '\\"')}"] .el-thumb .fc-stage`);
      if (!el) continue;
      await el.scrollIntoViewIfNeeded(); await p.waitForTimeout(60);
      const nomAff = (await p.$eval(`.el-tile[data-el-deck="${l.id.replace(/"/g, '\\"')}"] .el-tname`, x => x.textContent).catch(() => '')).replace(/\s+/g, ' ').trim();
      const plat = t => t.replace(/[\s\u202f\u00a0]+/g, ' ');
      if (nomAff && nomAff !== l.nom && plat(nomAff) === plat(l.nom)) l.titre = nomAff; /* même nom, avec les espaces fines de l'appli */
      const img = await el.screenshot({ type: 'jpeg', quality: 82 });
      const nom = l.id + '-' + crypto.createHash('sha256').update(img).digest('hex').slice(0, 8) + '.jpg';
      if (!fs.existsSync(path.join(COUV, nom))) fs.writeFileSync(path.join(COUV, nom), img);
      l.couv = 'couv/' + nom; gardes.add(nom); nb++;
    }
    /* vignettes des liens (même rendu que dans l'appli) */
    for (const l of r.liens) {
      const el = await p.$(`.el-link-tile[href="${l.url.replace(/"/g, '\\"')}"] .el-link-thumb`);
      if (!el) continue;
      await el.scrollIntoViewIfNeeded(); await p.waitForTimeout(60);
      const img = await el.screenshot({ type: 'jpeg', quality: 82 });
      const nom = 'lien-' + l.id + '-' + crypto.createHash('sha256').update(img).digest('hex').slice(0, 8) + '.jpg';
      if (!fs.existsSync(path.join(COUV, nom))) fs.writeFileSync(path.join(COUV, nom), img);
      l.couv = 'couv/' + nom; gardes.add(nom); nb++;
    }
    for (const f of fs.readdirSync(COUV)) if (!gardes.has(f) && f !== '.htaccess') fs.unlinkSync(path.join(COUV, f));
    fs.writeFileSync(path.join(COUV, '.htaccess'), '<IfModule mod_headers.c>\n  Header set Cache-Control "public, max-age=31536000, immutable"\n</IfModule>\n');

    const d = new Date(), z = n => String(n).padStart(2, '0');
    r.maj = z(d.getDate()) + '/' + z(d.getMonth() + 1) + '/' + d.getFullYear();
    fs.writeFileSync(out, JSON.stringify(r).replace(/\},\{"id"/g, '},\n{"id"') + '\n');
    console.log('lots.json : ' + r.lots.length + ' lots, ' + r.liens.length + ' liens, ' + r.rubriques.length + ' rubriques, ' + nb + ' couvertures');
  } finally { await b.close(); srv.close(); }
})().catch(e => { console.error('lots.json non généré : ' + e.message); process.exit(1); });

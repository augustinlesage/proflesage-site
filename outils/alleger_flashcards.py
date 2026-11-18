#!/usr/bin/env python3
"""Allège l'appli flashcards pour proflesage.fr.

L'appli est construite en UN seul fichier HTML (build.py --eleve) : images de fond,
polices et données y sont intégrées en base64 (~12 Mo). Pratique hors ligne et pour
l'artefact claude.ai, mais lent à charger sur le site.

Ce script prend ce fichier unique et produit, dans le dossier cible :
  index.html  le même code, sans les gros fichiers intégrés ;
  a/          images (converties en WebP), polices et PDF, nommés par empreinte
              (leur nom change quand leur contenu change → cache navigateur d'un an) ;
  sw.js       petit service worker : l'appli reste utilisable hors connexion
              après une première visite.

Usage : python3 outils/alleger_flashcards.py "Flashcards - élèves.html" flashcards/
(le fichier source doit déjà contenir le script FC_BILAN_API.)
"""
import base64, hashlib, io, json, os, re, sys

try:
    from PIL import Image
except ImportError:
    Image = None

MIN_B64 = 2000          # en dessous, on laisse en ligne (icônes minuscules)
WEBP_QUALITE = 80
EXT = {'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp', 'image/gif': 'gif',
       'font/woff': 'woff', 'font/woff2': 'woff2', 'application/pdf': 'pdf'}


def main(src, dest):
    s = open(src, encoding='utf-8').read()
    if 'FC_BILAN_API' not in s:
        sys.exit('Le fichier source ne contient pas FC_BILAN_API : ajoute-le d’abord.')
    adir = os.path.join(dest, 'a')
    os.makedirs(adir, exist_ok=True)
    ecrits = {}

    def sauver(data, mime):
        ext = EXT[mime]
        if mime in ('image/jpeg', 'image/png') and Image is not None:
            im = Image.open(io.BytesIO(data))
            if im.mode not in ('RGB', 'RGBA'):
                im = im.convert('RGBA' if 'A' in im.getbands() else 'RGB')
            o = io.BytesIO()
            if mime == 'image/png':
                im.save(o, 'WEBP', lossless=True, method=6)
            else:
                im.save(o, 'WEBP', quality=WEBP_QUALITE, method=6)
            if len(o.getvalue()) < len(data):
                data, ext = o.getvalue(), 'webp'
        nom = hashlib.sha256(data).hexdigest()[:12] + '.' + ext
        if nom not in ecrits:
            with open(os.path.join(adir, nom), 'wb') as f:
                f.write(data)
            ecrits[nom] = len(data)
        return 'a/' + nom

    # 1. Toutes les URI data: base64 (CSS, chaînes JS, JSON des lots)
    def rep_data(m):
        mime, b64 = m.group(1), m.group(2)
        if len(b64) < MIN_B64 or mime not in EXT:
            return m.group(0)
        return sauver(base64.b64decode(b64), mime)
    s = re.sub(r'data:([a-z0-9+./-]+);base64,([A-Za-z0-9+/=]+)', rep_data, s)

    # 2. Polices des cartes stockées en base64 brut dans FONTS_B64
    m = re.search(r'const FONTS_B64 = \{', s)
    if m:
        fin = s.index('};', m.end())
        bloc = re.sub(r'"([A-Z0-9_]+)": "([A-Za-z0-9+/=]+)"',
                      lambda k: '"%s": "%s"' % (k.group(1), sauver(base64.b64decode(k.group(2)), 'font/woff')),
                      s[m.start():fin])
        s = s[:m.start()] + bloc + s[fin:]
        n = s.count("'data:font/woff;base64,' + FONTS_B64[k]")
        s = s.replace("'data:font/woff;base64,' + FONTS_B64[k]", "FONTS_B64[k]")
        print('polices FONTS_B64 externalisées, %d appel(s) adapté(s)' % n)

    # 3. PDF des ressources (RES_FILES) : lien direct au lieu du base64
    m = re.search(r'const RES_FILES = \{', s)
    if m:
        fin = s.index('};', m.end())
        bloc = re.sub(r"(\w+): (['\"])([A-Za-z0-9+/=]{%d,})\2" % MIN_B64,
                      lambda k: "%s: '%s'" % (k.group(1), sauver(base64.b64decode(k.group(3)), 'application/pdf')),
                      s[m.start():fin])
        s = s[:m.start()] + bloc + s[fin:]
        ancien = "const b64 = RES_FILES[resFile.dataset.resFile]; if (b64) {"
        if ancien in s:
            s = s.replace(ancien, "const b64 = RES_FILES[resFile.dataset.resFile]; if (b64 && /^a\\//.test(b64)) { const l = document.createElement('a'); l.href = b64; l.download = resFile.dataset.resName || ''; document.body.appendChild(l); l.click(); l.remove(); return; } if (b64) {")

    reste = re.findall(r'[A-Za-z0-9+/=]{20000,}', s)
    if reste:
        print('ATTENTION : %d bloc(s) base64 restant(s), %d Ko' % (len(reste), sum(map(len, reste)) // 1000))

    # 4. Service worker (hors ligne après la 1re visite)
    version = hashlib.sha256(s.encode()).hexdigest()[:10]
    fichiers = sorted(ecrits)
    sw = SW.replace('__VERSION__', version).replace('__ASSETS__', json.dumps(['a/' + f for f in fichiers]))
    with open(os.path.join(dest, 'sw.js'), 'w', encoding='utf-8') as f:
        f.write(sw)
    s = s.replace('</body>', SW_REG + '</body>', 1) if '</body>' in s else s + SW_REG

    with open(os.path.join(dest, 'index.html'), 'w', encoding='utf-8') as f:
        f.write(s)

    # 5. Ménage : anciens fichiers de a/ qui ne servent plus
    for f in os.listdir(adir):
        if f not in ecrits and f != '.htaccess':
            os.remove(os.path.join(adir, f))

    print('index.html : %.2f Mo (avant %.2f Mo)' % (len(s.encode()) / 1e6, os.path.getsize(src) / 1e6))
    print('a/ : %d fichiers, %.2f Mo' % (len(ecrits), sum(ecrits.values()) / 1e6))


SW_REG = """<script>
if ('serviceWorker' in navigator && location.protocol === 'https:') {
  addEventListener('load', function () { navigator.serviceWorker.register('sw.js').catch(function () {}); });
}
</script>
"""

SW = r"""/* Flashcards : garde l'appli disponible hors connexion (généré par alleger_flashcards.py) */
const VERSION = 'fc-__VERSION__';
const ASSETS = __ASSETS__;

self.addEventListener('install', e => {
  e.waitUntil((async () => {
    const c = await caches.open(VERSION);
    await c.add(new Request('./', { cache: 'no-cache' })).catch(() => {});
    for (const u of ASSETS) {
      if (await c.match(u)) continue;
      const vieux = await caches.match(u);           // déjà en cache d'une version précédente
      if (vieux) { await c.put(u, vieux); continue; }
      await c.add(u).catch(() => {});
    }
    self.skipWaiting();
  })());
});

self.addEventListener('activate', e => {
  e.waitUntil((async () => {
    for (const k of await caches.keys()) if (k !== VERSION && k.startsWith('fc-')) await caches.delete(k);
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', e => {
  const r = e.request;
  if (r.method !== 'GET') return;
  const url = new URL(r.url);
  if (url.origin !== location.origin) return;
  if (url.pathname.includes('/a/')) {               // fichiers à empreinte : jamais modifiés
    e.respondWith(caches.match(r).then(x => x || fetch(r).then(res => {
      if (res.ok) { const copie = res.clone(); caches.open(VERSION).then(c => c.put(r, copie)); }
      return res;
    })));
  } else if (r.mode === 'navigate') {               // la page : réseau d'abord, cache si hors ligne
    e.respondWith(fetch(r).then(res => {
      if (res.ok) { const copie = res.clone(); caches.open(VERSION).then(c => c.put('./', copie)); }
      return res;
    }).catch(() => caches.match('./')));
  }
});
"""

if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    main(sys.argv[1], sys.argv[2])

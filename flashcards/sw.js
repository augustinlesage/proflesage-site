/* Flashcards : garde l'appli disponible hors connexion (généré par alleger_flashcards.py) */
const VERSION = 'fc-34594a4432';
const ASSETS = ["a/0058b7809b17.webp", "a/0183de4763c0.webp", "a/021b355e5e04.webp", "a/034f61d8dde6.woff", "a/04292ebb8c05.webp", "a/07058291b679.woff", "a/084f633de0a0.webp", "a/08dd93de28a6.webp", "a/0a3f419bad1b.webp", "a/0a7dfb4f31ff.woff", "a/0d47efd62a2f.webp", "a/0dfed04849e9.webp", "a/0f66b3d03fef.webp", "a/123a5f819cb2.webp", "a/13368b5609f1.webp", "a/13452575ac35.webp", "a/14758664ccdb.webp", "a/1508621ca1c5.woff", "a/186029b63c0c.webp", "a/1bf312a1d7ea.webp", "a/1e4a7fd4924c.webp", "a/1e8f26d34d54.webp", "a/1f2ef3c73c6e.webp", "a/1f86fe4a425d.webp", "a/1fa59db37df6.webp", "a/1fc3fe958472.webp", "a/20ee23cbfb26.webp", "a/210c73247735.woff", "a/2263cd4d2cdd.webp", "a/252731ce0273.webp", "a/258abaaaa868.webp", "a/26bbbb61e9b4.woff", "a/27c9fb6d5512.webp", "a/2905b48d3343.webp", "a/2909bfb76ccb.webp", "a/2980d49017f3.webp", "a/29fc38039abb.webp", "a/2a04c8a007f2.webp", "a/2a9bd45cbab3.woff", "a/2be21f899b4f.webp", "a/2d419951e4c7.webp", "a/2de15bb16c4f.woff", "a/2eadc5545dad.webp", "a/2fbe3065da99.webp", "a/30f75b04d8a6.woff", "a/311722529d89.webp", "a/322818820afe.webp", "a/36901611b06b.woff", "a/36d799c776fe.webp", "a/3746fff108cf.webp", "a/37ec9b3dcba3.webp", "a/37f9e74d82d7.webp", "a/3863f899153a.webp", "a/397ccaf84082.woff", "a/3c75d058025f.webp", "a/4042a5f8ef85.webp", "a/404a9216f997.webp", "a/40910a6f0ac6.webp", "a/456031f4d5b5.webp", "a/45891528fcfd.webp", "a/4aca7d951da6.webp", "a/5162bb27a33d.webp", "a/55eb945fd74b.woff", "a/55fffef7b100.woff", "a/568a19711378.webp", "a/56e4c416fd84.webp", "a/577513bf8ebc.woff", "a/585630080117.webp", "a/5ca3dfb9ec45.webp", "a/5eed564b5689.webp", "a/5ffe889494aa.webp", "a/6115db834325.woff", "a/632b8e79cbcb.woff", "a/664ed9afb504.woff", "a/66d0b14839bf.webp", "a/6782277a7a8a.webp", "a/67f650d0d226.woff", "a/698153de65e4.webp", "a/6c1a04cabdad.webp", "a/6c4fc9181802.woff", "a/6d1ac91b3006.webp", "a/6e0feaab2e4c.webp", "a/6f87a094e566.webp", "a/70db84ec8276.woff", "a/74770c5d97ba.webp", "a/75e38df49df1.webp", "a/75e40064584c.webp", "a/7c681c34d511.woff", "a/7ced73c96b71.webp", "a/7e3629461876.webp", "a/7fe6b72a803a.webp", "a/8066f804828e.webp", "a/8195ca0cce2b.woff", "a/81d021922e62.webp", "a/82075c2e6465.webp", "a/88fc5d1add47.webp", "a/8ace75b21003.webp", "a/8b3c81a3240d.woff", "a/8cd5ad3216a6.webp", "a/8f814889c902.woff", "a/916b68bc4ac1.pdf", "a/91c4316a902e.webp", "a/9284832f79a8.webp", "a/92d2e32fe151.webp", "a/92d39b8c7aa2.webp", "a/9330ed4553ee.webp", "a/956e57b858ed.webp", "a/98e7c9bf4606.webp", "a/9ae05f1ba4c5.webp", "a/9af72fa32776.woff", "a/9c82a899112d.webp", "a/9edb77d526b6.webp", "a/9fedb578ed13.webp", "a/a04c00b7608f.webp", "a/a242c959d713.webp", "a/a36d1f2c6e27.webp", "a/a4fb51d0f06d.webp", "a/a5cf3e20116e.webp", "a/a62f69bb8ef4.webp", "a/a972ec9852f9.woff", "a/aaeb2370d4bc.webp", "a/abd8023b0afa.woff", "a/abdc91ec7e44.woff", "a/ac8b0fd2fdda.webp", "a/adddbea4ac0a.webp", "a/ae008f72698d.webp", "a/aef4974fbd98.woff", "a/b1c0b7d3bc0b.webp", "a/b26348c9a0cf.woff", "a/b2fefdfcd910.woff", "a/b3354a06c57b.webp", "a/b39a2f963e77.webp", "a/b3e63ed69bf8.webp", "a/b459f457c51e.webp", "a/b5743670eaad.webp", "a/ba30bd7e0eae.webp", "a/bb01d6b05148.webp", "a/bb7b7b6dce18.woff", "a/bcdd8c2e3aeb.webp", "a/be2fbf9c1b0d.woff", "a/bf8cd5954ddd.webp", "a/c07aad192f99.webp", "a/c100612e2132.webp", "a/c22400ef3062.webp", "a/c2da230b40e6.webp", "a/c4c2c3025ead.webp", "a/c58ac4505736.webp", "a/c5a62c2deb81.webp", "a/c90f6a00b437.woff", "a/c93947440960.webp", "a/ca6f7ba0a393.woff", "a/cd2e0a597d7a.webp", "a/cda26d44a1ab.webp", "a/cefe4b56877a.webp", "a/d140b04b1477.webp", "a/d48c9618387d.woff", "a/d60f32e31e1c.woff", "a/d62de6966e32.webp", "a/d77f30636386.webp", "a/da9b309d879f.webp", "a/de8fc4b73201.webp", "a/df3978b04b31.webp", "a/e13ab1521d21.webp", "a/e2c5d10d1b54.webp", "a/e483cebd8614.webp", "a/e59169c9c63e.webp", "a/e64f65436d80.webp", "a/e6fc6c9947a3.webp", "a/e888c8defa4b.webp", "a/e92604f066fa.webp", "a/e99dd508ec00.webp", "a/ea2cde622e6b.webp", "a/ecff0dbfa648.woff", "a/edfa5d28ef65.webp", "a/f1f28674cb12.woff", "a/f5d2c5ac123f.webp", "a/f61c13220983.webp", "a/f67b17de2141.webp", "a/f9c785c4966a.webp", "a/faba616d154a.webp", "a/fc1c02dfd18c.webp", "a/fe6cfa656bfa.webp"];

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

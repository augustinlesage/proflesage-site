<?php
/* =====================================================================
   Laboratoire de la langue : serveur de sauvegarde (PHP, hébergement OVH de proflesage.fr).
   Remplace la fonction Netlify (api.mjs) à l'identique pour les pages, et ajoute :
   - un journal par élève (une ligne par série de flashcards, ceinture, étape de parcours, test, exercice, bilan),
     déduit des différences entre deux sauvegardes : l'historique n'est jamais écrasé ;
   - l'import des données de l'ancien serveur Netlify, l'export complet (sauvegarde) et le tableau de bord.
   Données : fichiers JSON dans un dossier privé (hors du site si possible), jamais accessibles par le web.
   Élèves : classe + prénom (+ code secret haché si activé). Ni nom, ni e-mail, ni adresse IP.
   Configuration : config.php (créé à la publication à partir des secrets GitHub) :
     return ['prof_mdp' => '…', 'code_eleve' => true];
   ===================================================================== */
declare(strict_types=1);
mb_internal_encoding('UTF-8');

const JOURS_JETON = 60;
const MAX_ETAT = 300 * 1024;
const MAX_BILAN = 60 * 1024;
const BILANS_PAR_ELEVE = 60;
const ORIGINES = ['https://proflesage.fr', 'https://www.proflesage.fr'];

/* ---------------------------- configuration et dossiers ---------------------------- */
function config(): array {
  static $c = null;
  if ($c === null) { $f = __DIR__ . '/config.php'; $c = is_file($f) ? (array) require $f : []; }
  return $c;
}
function dossier(): string {
  static $d = null;
  if ($d !== null) return $d;
  $cands = [];
  if (!empty(config()['donnees'])) $cands[] = config()['donnees'];
  $cands[] = dirname(__DIR__, 3) . '/labo-donnees';   /* hors du site : …/www/labo/api → …/labo-donnees */
  $cands[] = __DIR__ . '/donnees';                      /* repli : dans le site, protégé par .htaccess */
  foreach ($cands as $x) {
    if (!is_dir($x)) @mkdir($x, 0700, true);
    if (is_dir($x) && is_writable($x)) {
      if (strpos(realpath($x), realpath(dirname(__DIR__, 2))) === 0 && !is_file("$x/.htaccess"))
        @file_put_contents("$x/.htaccess", "Require all denied\nDeny from all\n");
      return $d = realpath($x);
    }
  }
  throw new RuntimeException('Dossier de données impossible à créer.');
}
function secret(): string {
  static $s = null;
  if ($s !== null) return $s;
  if (!empty(config()['secret'])) return $s = (string) config()['secret'];
  $f = dossier() . '/secret.key';
  if (!is_file($f)) { file_put_contents($f, bin2hex(random_bytes(32)), LOCK_EX); @chmod($f, 0600); }
  return $s = trim((string) file_get_contents($f));
}
function avecCode(): bool { return !empty(config()['code_eleve']); }

/* ---------------------------- fichiers JSON ---------------------------- */
function chemin(string $rel): string { return dossier() . '/' . $rel; }
function lireJ(string $rel, $defaut = null) {
  $f = chemin($rel); if (!is_file($f)) return $defaut;
  $h = fopen($f, 'r'); flock($h, LOCK_SH); $t = stream_get_contents($h); flock($h, LOCK_UN); fclose($h);
  $v = json_decode((string) $t, true); return $v === null ? $defaut : $v;
}
function ecrireJ(string $rel, $v): void {
  $f = chemin($rel); if (!is_dir(dirname($f))) mkdir(dirname($f), 0700, true);
  $tmp = $f . '.' . bin2hex(random_bytes(4)) . '.tmp';
  file_put_contents($tmp, json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
  rename($tmp, $f);
}
function ajouterJournal(string $cl, string $el, array $evts): void {
  if (!$evts) return;
  $f = chemin("journal/$cl/$el.jsonl"); if (!is_dir(dirname($f))) mkdir(dirname($f), 0700, true);
  $t = ''; foreach ($evts as $e) $t .= json_encode($e, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
  file_put_contents($f, $t, FILE_APPEND | LOCK_EX);
}
function lireJournal(string $cl, string $el): array {
  $f = chemin("journal/$cl/$el.jsonl"); if (!is_file($f)) return [];
  $out = []; foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) { $v = json_decode($l, true); if (is_array($v)) $out[] = $v; }
  return $out;
}
/* verrou par élève pour les lectures-modifications-écritures */
function avecVerrou(string $nom, callable $fn) {
  $f = chemin('verrous/' . preg_replace('/[^a-z0-9_-]/i', '_', $nom) . '.lock');
  if (!is_dir(dirname($f))) mkdir(dirname($f), 0700, true);
  $h = fopen($f, 'c'); flock($h, LOCK_EX);
  try { return $fn(); } finally { flock($h, LOCK_UN); fclose($h); }
}

/* ---------------------------- outils ---------------------------- */
function repondre($d, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex');
  echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
}
function erreur(string $m, int $s = 400): void { repondre(['erreur' => $m], $s); }
function okId($s): bool { return is_string($s) && preg_match('/^[a-z0-9_-]{1,40}$/i', $s) === 1; }
function norm($s): string {
  $s = (string) $s;
  if (class_exists('Normalizer')) $s = Normalizer::normalize($s, Normalizer::FORM_D);
  $s = preg_replace('/\p{Mn}/u', '', $s);
  return strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
}
function b64u(string $b): string { return rtrim(strtr(base64_encode($b), '+/', '-_'), '='); }
function signe(string $s): string { return b64u(hash_hmac('sha256', $s, secret(), true)); }
function maintenant(): string { return gmdate('Y-m-d\TH:i:s.v\Z'); }
function classes(): array { $c = lireJ('classes.json', null); return is_array($c) && isset($c['list']) ? $c : ['list' => []]; }
function classeParCode($code): ?array { foreach (classes()['list'] as $c) if (!empty($c['code']) && norm($c['code']) === norm($code)) return $c; return null; }
function classeParId($id): ?array { foreach (classes()['list'] as $c) if ($c['id'] === $id) return $c; return null; }
function eleveDe(?array $c, $id): ?array { if (!$c) return null; foreach ($c['eleves'] as $e) if ($e['id'] === $id) return $e; return null; }
function recRel(string $cl, string $el): string { return "eleves/$cl/$el.json"; }

function jeton(string $cl, string $el, int $pv): string {
  $exp = (int) round(microtime(true) * 1000) + JOURS_JETON * 86400000; $base = "$cl.$el.$pv.$exp";
  return $base . '.' . signe($base);
}
function lireJeton($t): ?array {
  $p = explode('.', (string) $t); if (count($p) !== 5) return null;
  $base = implode('.', array_slice($p, 0, 4));
  if (!hash_equals(signe($base), $p[4]) || (float) $p[3] < microtime(true) * 1000) return null;
  return ['cl' => $p[0], 'el' => $p[1], 'pv' => (int) $p[2]];
}
function etatPropre($e): array {
  $e = is_array($e) ? $e : [];
  return ['labo' => (isset($e['labo']) && is_array($e['labo'])) ? $e['labo'] : new stdClass(), 'fc' => (isset($e['fc']) && is_array($e['fc'])) ? $e['fc'] : new stdClass()];
}
function etatPropreA($e): array { $p = etatPropre($e); return ['labo' => (array) $p['labo'], 'fc' => (array) $p['fc']]; }

/* ---------------------------- fusion (même logique que merge.js) ---------------------------- */
function objA($o): array { return (is_array($o) && ($o === [] || array_keys($o) !== range(0, count($o) - 1))) ? $o : []; }
function jourFr($d): int { return preg_match('/^(\d\d)\/(\d\d)\/(\d{4})$/', (string) $d, $m) ? (int) ($m[3] . $m[2] . $m[1]) : 0; }
function rangC($s): int { if (!$s) return 0; $e = $s['etat'] ?? ''; return $e === 'validee' ? 3 : ($e === 'reussie' ? 2 : 1); }
function plusRecent($s, $v): bool {
  $s = objA($s); $v = objA($v); $d = jourFr($s['date'] ?? '') - jourFr($v['date'] ?? ''); if ($d) return $d > 0;
  $n = (float) ($s['nb'] ?? 0) - (float) ($v['nb'] ?? 0); if ($n) return $n > 0; return (float) ($s['tot'] ?? 0) >= (float) ($v['tot'] ?? 0);
}
function fusion($A, $B): array {
  $A = objA($A); $B = objA($B);
  $la = objA($A['labo'] ?? []); $lb = objA($B['labo'] ?? []); $fa = objA($A['fc'] ?? []); $fb = objA($B['fc'] ?? []);
  $C = objA($la['ceintures'] ?? []);
  foreach (objA($lb['ceintures'] ?? []) as $id => $s) { $v = $C[$id] ?? null;
    if (!$v || rangC($s) > rangC($v) || (rangC($s) === rangC($v) && (float) ($s['score'] ?? 0) >= (float) ($v['score'] ?? 0))) $C[$id] = $s; }
  $pa = objA($la['parcours'] ?? []); $pb = objA($lb['parcours'] ?? []);
  $P = objA($pa['prog'] ?? []);
  foreach (objA($pb['prog'] ?? []) as $k => $s) { $v = $P[$k] ?? null;
    if (!$v || (!empty($s['done']) && empty($v['done'])) || ((bool) ($s['done'] ?? false) === (bool) ($v['done'] ?? false) && (float) ($s['score'] ?? 0) >= (float) ($v['score'] ?? 0))) $P[$k] = $s; }
  $labo = array_merge($la, $lb);
  $labo['ceintures'] = $C ?: new stdClass();
  $labo['parcours'] = array_merge($pa, $pb, ['prog' => $P ?: new stdClass(), 'diag' => (array_merge(objA($pa['diag'] ?? []), objA($pb['diag'] ?? [])) ?: new stdClass()), 'last' => $pb['last'] ?? ($pa['last'] ?? null)]);
  foreach (['exercices', 'lecons'] as $k) if (isset($la[$k]) || isset($lb[$k])) $labo[$k] = array_merge(objA($la[$k] ?? []), objA($lb[$k] ?? [])) ?: new stdClass();
  $R = objA($fa['res'] ?? []);
  foreach (objA($fb['res'] ?? []) as $k => $s) { $v = $R[$k] ?? null; if (!$v || plusRecent($s, $v)) $R[$k] = $s; }
  $fc = array_merge($fa, $fb); $fc['res'] = $R ?: new stdClass();
  if (!empty($fa['test']) && (empty($fb['test']) || jourFr($fa['test']['date'] ?? '') > jourFr($fb['test']['date'] ?? ''))) $fc['test'] = $fa['test'];
  return ['labo' => $labo, 'fc' => $fc];
}

/* ---------------------------- journal : ce qui a changé entre deux états ---------------------------- */
function differences(array $avant, array $apres, string $ts): array {
  $ev = [];
  $ra = objA(($avant['fc'] ?? [])['res'] ?? []); $rb = objA(($apres['fc'] ?? [])['res'] ?? []);
  foreach ($rb as $id => $s) {
    if (!is_array($s) || empty($s['n'])) continue; $v = $ra[$id] ?? null;
    $cle = fn($x) => is_array($x) ? json_encode([$x['date'] ?? '', $x['ok'] ?? 0, $x['n'] ?? 0, $x['nb'] ?? 0, $x['sec'] ?? null, $x['tot'] ?? null]) : '';
    if ($cle($v) === $cle($s)) continue;
    $e = ['ts' => $ts, 't' => 'serie', 'id' => (string) $id];
    foreach (['ok', 'n', 'date', 'sec', 'tot', 'mode', 'val', 'nb', 'best', 'nom', 'niv'] as $k) if (array_key_exists($k, $s)) $e[$k] = $s[$k];
    $ev[] = $e;
  }
  $ta = ($avant['fc'] ?? [])['test'] ?? null; $tb = ($apres['fc'] ?? [])['test'] ?? null;
  if (is_array($tb) && json_encode($tb) !== json_encode($ta)) {
    $doms = []; foreach (objA($tb['doms'] ?? []) as $k => $d) $doms[$k] = is_array($d) ? ($d['status'] ?? '') : '';
    $ev[] = ['ts' => $ts, 't' => 'test', 'date' => $tb['date'] ?? '', 'cls' => $tb['cls'] ?? '', 'doms' => $doms ?: new stdClass()];
  }
  $ca = objA(($avant['labo'] ?? [])['ceintures'] ?? []); $cb = objA(($apres['labo'] ?? [])['ceintures'] ?? []);
  foreach ($cb as $id => $s) {
    if (!is_array($s)) continue; $v = $ca[$id] ?? null;
    if ($v && rangC($v) === rangC($s) && ($v['score'] ?? null) === ($s['score'] ?? null) && ($v['date'] ?? '') === ($s['date'] ?? '')) continue;
    $e = ['ts' => $ts, 't' => 'ceinture', 'id' => (string) $id];
    foreach (['etat', 'score', 'total', 'date', 'valide', 'code'] as $k) if (array_key_exists($k, $s)) $e[$k] = $s[$k];
    $ev[] = $e;
  }
  $pa = objA((($avant['labo'] ?? [])['parcours'] ?? [])['prog'] ?? []); $pb = objA((($apres['labo'] ?? [])['parcours'] ?? [])['prog'] ?? []);
  foreach ($pb as $k => $s) { if (!is_array($s) || json_encode($s) === json_encode($pa[$k] ?? null)) continue;
    $ev[] = ['ts' => $ts, 't' => 'etape', 'id' => (string) $k, 'done' => !empty($s['done']), 'score' => $s['score'] ?? null, 'total' => $s['total'] ?? null]; }
  $da = objA((($avant['labo'] ?? [])['parcours'] ?? [])['diag'] ?? []); $db = objA((($apres['labo'] ?? [])['parcours'] ?? [])['diag'] ?? []);
  foreach ($db as $k => $s) if (json_encode($s) !== json_encode($da[$k] ?? null)) $ev[] = ['ts' => $ts, 't' => 'diagnostic', 'id' => (string) $k, 'date' => is_array($s) ? ($s['date'] ?? '') : ''];
  /* exercices de grammaire et leçons à venir : { id: { date, ok, n, ... } } */
  foreach (['exercices' => 'exercice', 'lecons' => 'lecon'] as $col => $t) {
    $xa = objA(($avant['labo'] ?? [])[$col] ?? []); $xb = objA(($apres['labo'] ?? [])[$col] ?? []);
    foreach ($xb as $k => $s) if (is_array($s) && json_encode($s) !== json_encode($xa[$k] ?? null)) $ev[] = array_merge(['ts' => $ts, 't' => $t, 'id' => (string) $k], array_intersect_key($s, array_flip(['ok', 'n', 'score', 'total', 'date', 'sec', 'done', 'niveau'])));
  }
  return $ev;
}

/* ---------------------------- élèves ---------------------------- */
function eleve(string $action, array $body): void {
  if ($action === 'classe') {
    $c = classeParCode($body['code'] ?? ''); if (!$c) erreur('Code de classe inconnu.', 404);
    $actifs = [];
    foreach (glob(chemin("eleves/{$c['id']}/*.json")) ?: [] as $f) { $r = json_decode((string) file_get_contents($f), true); $actifs[basename($f, '.json')] = !empty($r['pin']); }
    repondre(['id' => $c['id'], 'nom' => $c['nom'], 'code' => avecCode(), 'eleves' => array_map(fn($e) => ['id' => $e['id'], 'prenom' => $e['prenom'], 'actif' => avecCode() ? ($actifs[$e['id']] ?? false) : isset($actifs[$e['id']])], $c['eleves'])]);
  }
  if ($action === 'connexion') {
    $cl = $body['cl'] ?? ''; $el = $body['el'] ?? ''; $pin = (string) ($body['pin'] ?? '');
    if (!okId($cl) || !okId($el) || (avecCode() && !preg_match('/^\d{4}$/', $pin))) erreur('Demande incomplète.');
    $c = classeParId($cl); $e = eleveDe($c, $el);
    if (!$e || empty($c['code']) || norm($c['code']) !== norm($body['code'] ?? '')) erreur('Élève inconnu.', 404);
    avecVerrou("$cl-$el", function () use ($cl, $el, $pin, $body, $c, $e) {
      $k = "$cl/$el"; $rec = lireJ(recRel($cl, $el)); $now = microtime(true) * 1000;
      if (!avecCode()) {
        $rec = $rec ?: ['etat' => ['labo' => new stdClass(), 'fc' => new stdClass()], 'cree' => maintenant(), 'pv' => 0];
      } elseif (!$rec || empty($rec['pin'])) {
        if (empty($body['nouveau'])) repondre(['nouveau' => true]);
        $rec = $rec ?: ['etat' => ['labo' => new stdClass(), 'fc' => new stdClass()], 'cree' => maintenant()];
        $rec = array_merge($rec, ['pin' => password_hash(secret() . $k . $pin, PASSWORD_DEFAULT), 'pv' => $rec['pv'] ?? 0, 'essais' => 0, 'bloque' => 0]);
        ajouterJournal($cl, $el, [['ts' => maintenant(), 't' => 'code', 'quoi' => 'créé']]);
      } else {
        if (($rec['bloque'] ?? 0) > $now) erreur('Trop d’essais. Réessaie dans ' . ceil(($rec['bloque'] - $now) / 60000) . ' min ou demande à ton professeur.', 429);
        $bon = strpos((string) $rec['pin'], '$') === 0 ? password_verify(secret() . $k . $pin, $rec['pin']) : hash_equals((string) $rec['pin'], hash('sha256', secret() . "e/$k" . $pin));
        if (!$bon) {
          $rec['essais'] = ($rec['essais'] ?? 0) + 1;
          if ($rec['essais'] >= 5) { $rec['bloque'] = $now + 600000; $rec['essais'] = 0; }
          ecrireJ(recRel($cl, $el), $rec); erreur('Code secret incorrect.', 403);
        }
        $rec['essais'] = 0; $rec['bloque'] = 0;
      }
      $rec['vu'] = maintenant(); ecrireJ(recRel($cl, $el), $rec);
      repondre(['jeton' => jeton($cl, $el, (int) ($rec['pv'] ?? 0)), 'etat' => etatPropre($rec['etat'] ?? []), 'prenom' => $e['prenom'], 'classe' => $c['nom']]);
    });
  }
  if ($action === 'charger' || $action === 'sauver' || $action === 'journal') {
    $j = lireJeton($body['jeton'] ?? ''); if (!$j) erreur('Session expirée : reconnecte-toi.', 401);
    avecVerrou("{$j['cl']}-{$j['el']}", function () use ($j, $action, $body) {
      $rec = lireJ(recRel($j['cl'], $j['el']));
      if (!$rec || (int) ($rec['pv'] ?? 0) !== $j['pv']) erreur('Session expirée : reconnecte-toi.', 401);
      if ($action === 'charger') repondre(['etat' => etatPropre($rec['etat'] ?? [])]);
      if ($action === 'journal') repondre(['journal' => array_slice(lireJournal($j['cl'], $j['el']), -500), 'bilans' => listeBilans($j['cl'], $j['el'])]);
      $txt = json_encode($body['etat'] ?? new stdClass()); if (strlen((string) $txt) > MAX_ETAT) erreur('Progression trop volumineuse.', 413);
      $avant = etatPropreA($rec['etat'] ?? []);
      $apres = fusion($avant, etatPropreA($body['etat'] ?? []));
      ajouterJournal($j['cl'], $j['el'], differences($avant, $apres, maintenant()));
      $rec['etat'] = $apres; $rec['maj'] = $rec['vu'] = maintenant();
      ecrireJ(recRel($j['cl'], $j['el']), $rec);
      repondre(['ok' => true, 'etat' => etatPropre($apres)]);
    });
  }
  if ($action === 'bilan') {
    $c = classeParCode($body['code'] ?? ''); $e = ($c && okId($body['el'] ?? null)) ? eleveDe($c, $body['el']) : null;
    if (!$e) erreur('Élève inconnu.', 404);
    $b = $body['bilan'] ?? null;
    if (!is_array($b) || strlen((string) json_encode($b)) > MAX_BILAN) erreur('Bilan invalide.');
    $ts = (string) (int) round(microtime(true) * 1000);
    ecrireJ("bilans/{$c['id']}/{$e['id']}/$ts.json", ['recu' => maintenant(), 'bilan' => $b]);
    ajouterJournal($c['id'], $e['id'], [['ts' => maintenant(), 't' => 'bilan', 'cle' => $ts, 'lots' => count($b['lots'] ?? [])]]);
    $fs = glob(chemin("bilans/{$c['id']}/{$e['id']}/*.json")) ?: []; sort($fs);
    foreach (array_slice($fs, 0, max(0, count($fs) - BILANS_PAR_ELEVE)) as $f) @unlink($f);
    repondre(['ok' => true, 'prenom' => $e['prenom'], 'classe' => $c['nom']]);
  }
  erreur('Action inconnue.', 404);
}
function listeBilans(string $cl, string $el): array {
  $out = [];
  foreach (glob(chemin("bilans/$cl/$el/*.json")) ?: [] as $f) { $r = json_decode((string) file_get_contents($f), true); if ($r) $out[] = ['cle' => "b/$cl/$el/" . basename($f, '.json'), 'el' => $el, 'recu' => $r['recu'] ?? '', 'bilan' => $r['bilan'] ?? null]; }
  return $out;
}

/* ---------------------------- professeur ---------------------------- */
function prof(string $action, array $body): void {
  if ($action === 'classes') repondre(classes());
  if ($action === 'enregistrer-classes') {
    $list = $body['list'] ?? null; $ok = is_array($list);
    if ($ok) foreach ($list as $c) { if (!okId($c['id'] ?? null) || !is_string($c['nom'] ?? null) || !is_array($c['eleves'] ?? null)) { $ok = false; break; }
      foreach ($c['eleves'] as $e) if (!okId($e['id'] ?? null) || !is_string($e['prenom'] ?? null)) { $ok = false; break 2; } }
    if (!$ok) erreur('Liste de classes invalide.');
    ecrireJ('classes.json', ['list' => array_map(fn($c) => ['id' => $c['id'], 'nom' => mb_substr($c['nom'], 0, 40), 'code' => mb_substr((string) ($c['code'] ?? ''), 0, 40),
      'eleves' => array_map(fn($e) => ['id' => $e['id'], 'prenom' => mb_substr($e['prenom'], 0, 60)], $c['eleves'])], $list)]);
    repondre(['ok' => true]);
  }
  if ($action === 'suivi') {
    $out = [];
    foreach (glob(chemin('eleves/*/*.json')) ?: [] as $f) {
      $cl = basename(dirname($f)); $el = basename($f, '.json');
      if (!empty($body['cl']) && $body['cl'] !== $cl) continue;
      $r = json_decode((string) file_get_contents($f), true); if (!$r) continue;
      $out[] = ['cl' => $cl, 'el' => $el, 'etat' => etatPropre($r['etat'] ?? []), 'cree' => $r['cree'] ?? null, 'maj' => $r['maj'] ?? null, 'vu' => $r['vu'] ?? null, 'code' => !empty($r['pin']), 'bloque' => ($r['bloque'] ?? 0) > microtime(true) * 1000];
    }
    repondre(['eleves' => $out]);
  }
  if ($action === 'bilans') {
    if (!okId($body['cl'] ?? null)) erreur('Classe inconnue.');
    $out = []; foreach (glob(chemin("bilans/{$body['cl']}/*"), GLOB_ONLYDIR) ?: [] as $d) $out = array_merge($out, listeBilans($body['cl'], basename($d)));
    repondre(['bilans' => $out]);
  }
  if ($action === 'tableau') { /* tout ce qu'il faut pour le tableau de bord d'une classe */
    if (!okId($body['cl'] ?? null)) erreur('Classe inconnue.');
    $cl = $body['cl']; $out = [];
    $c = classeParId($cl);
    foreach (($c['eleves'] ?? []) as $e) {
      $r = lireJ(recRel($cl, $e['id']));
      $out[] = ['el' => $e['id'], 'etat' => $r ? etatPropre($r['etat'] ?? []) : null, 'cree' => $r['cree'] ?? null, 'vu' => $r['vu'] ?? null, 'maj' => $r['maj'] ?? null, 'code' => !empty($r['pin']),
        'journal' => lireJournal($cl, $e['id']), 'bilans' => listeBilans($cl, $e['id'])];
    }
    repondre(['eleves' => $out, 'maintenant' => maintenant()]);
  }
  if ($action === 'effacer-bilan') {
    $k = $body['cle'] ?? ''; if (!is_string($k) || !preg_match('/^b\/([a-z0-9_-]+)\/([a-z0-9_-]+)\/(\d+)$/i', $k, $m)) erreur('Bilan inconnu.');
    @unlink(chemin("bilans/{$m[1]}/{$m[2]}/{$m[3]}.json")); repondre(['ok' => true]);
  }
  if (in_array($action, ['reinit-code', 'effacer', 'valider'], true)) {
    $cl = $body['cl'] ?? ''; $el = $body['el'] ?? ''; if (!okId($cl) || !okId($el)) erreur('Élève inconnu.');
    avecVerrou("$cl-$el", function () use ($action, $cl, $el, $body) {
      if ($action === 'effacer') {
        @unlink(chemin(recRel($cl, $el))); @unlink(chemin("journal/$cl/$el.jsonl"));
        foreach (glob(chemin("bilans/$cl/$el/*.json")) ?: [] as $f) @unlink($f); @rmdir(chemin("bilans/$cl/$el"));
        repondre(['ok' => true]);
      }
      $r = lireJ(recRel($cl, $el)); if (!$r) erreur('Élève sans progression.', 404);
      if ($action === 'reinit-code') { $r['pin'] = ''; $r['pv'] = ($r['pv'] ?? 0) + 1; $r['essais'] = 0; $r['bloque'] = 0; ajouterJournal($cl, $el, [['ts' => maintenant(), 't' => 'code', 'quoi' => 'remis à zéro']]); }
      else {
        $avant = etatPropreA($r['etat'] ?? []);
        $s = $r['etat']['labo']['ceintures'][$body['ceinture'] ?? ''] ?? null;
        if (!$s || ($s['etat'] ?? '') !== 'reussie') erreur('Ceinture non réussie.');
        $r['etat']['labo']['ceintures'][$body['ceinture']]['etat'] = 'validee';
        $r['etat']['labo']['ceintures'][$body['ceinture']]['valide'] = (string) ($body['date'] ?? '');
        ajouterJournal($cl, $el, differences($avant, etatPropreA($r['etat']), maintenant()));
      }
      ecrireJ(recRel($cl, $el), $r); repondre(['ok' => true]);
    });
  }
  if ($action === 'exporter') { /* sauvegarde complète, à garder sur l'ordinateur du professeur */
    $el = []; foreach (glob(chemin('eleves/*/*.json')) ?: [] as $f) { $cl = basename(dirname($f)); $id = basename($f, '.json'); $r = json_decode((string) file_get_contents($f), true);
      if ($r) { unset($r['pin']); $el[] = ['cl' => $cl, 'el' => $id, 'rec' => $r, 'journal' => lireJournal($cl, $id)]; } }
    $bi = []; foreach (glob(chemin('bilans/*/*/*.json')) ?: [] as $f) { $r = json_decode((string) file_get_contents($f), true);
      if ($r) $bi[] = ['cl' => basename(dirname($f, 2)), 'el' => basename(dirname($f)), 'ts' => basename($f, '.json'), 'recu' => $r['recu'] ?? '', 'bilan' => $r['bilan'] ?? null]; }
    repondre(['format' => 'labo-sauvegarde-1', 'date' => maintenant(), 'classes' => classes(), 'eleves' => $el, 'bilans' => $bi]);
  }
  if ($action === 'importer') { importer($body); }
  if ($action === 'mcp-adresse') repondre(['url' => adresseMcp()]);
  erreur('Action inconnue.', 404);
}

/* Import (ancien serveur Netlify ou sauvegarde) : on fusionne, on ne remplace jamais une progression plus récente.
   Les bilans et l'état importés produisent des lignes de journal datées (marquées « import »). */
function tsDeFr($d, string $repli): string { return preg_match('/^(\d\d)\/(\d\d)\/(\d{4})$/', (string) $d, $m) ? "{$m[3]}-{$m[2]}-{$m[1]}T12:00:00.000Z" : $repli; }
function importer(array $D): void {
  $n = ['classes' => 0, 'eleves' => 0, 'bilans' => 0, 'journal' => 0];
  if (!empty($D['classes']['list']) && is_array($D['classes']['list'])) {
    $cur = classes(); $par = []; foreach ($cur['list'] as $c) $par[$c['id']] = $c;
    foreach ($D['classes']['list'] as $c) { if (!okId($c['id'] ?? null)) continue;
      if (!isset($par[$c['id']])) { $par[$c['id']] = $c; $n['classes']++; continue; }
      $ids = array_column($par[$c['id']]['eleves'], 'id');
      foreach ($c['eleves'] ?? [] as $e) if (okId($e['id'] ?? null) && !in_array($e['id'], $ids, true)) $par[$c['id']]['eleves'][] = ['id' => $e['id'], 'prenom' => (string) $e['prenom']];
      if (empty($par[$c['id']]['code']) && !empty($c['code'])) $par[$c['id']]['code'] = $c['code'];
    }
    ecrireJ('classes.json', ['list' => array_values($par)]);
  }
  $BI = is_array($D['bilans'] ?? null) ? $D['bilans'] : []; usort($BI, fn($x, $y) => strcmp((string) ($x['recu'] ?? ''), (string) ($y['recu'] ?? '')));
  foreach ($BI as $b) {
    $cl = $b['cl'] ?? ''; $el = $b['el'] ?? ''; $ts = (string) ($b['ts'] ?? '');
    if (!okId($cl) || !okId($el) || !preg_match('/^\d+$/', $ts) || !is_array($b['bilan'] ?? null)) continue;
    $rel = "bilans/$cl/$el/$ts.json"; if (is_file(chemin($rel))) continue;
    ecrireJ($rel, ['recu' => $b['recu'] ?? '', 'bilan' => $b['bilan']]); $n['bilans']++;
    $evs = [['ts' => $b['recu'] ?: maintenant(), 't' => 'bilan', 'cle' => $ts, 'lots' => count($b['bilan']['lots'] ?? []), 'import' => 1]];
    $deja = []; foreach (lireJournal($cl, $el) as $e) if (($e['t'] ?? '') === 'serie') $deja[($e['id'] ?? '') . '|' . ($e['date'] ?? '') . '|' . ($e['ok'] ?? '') . '|' . ($e['n'] ?? '')] = 1;
    foreach (($b['bilan']['lots'] ?? []) as $l) { if (!is_array($l) || empty($l['id']) || empty($l['n'])) continue;
      $k = $l['id'] . '|' . ($l['date'] ?? '') . '|' . ($l['ok'] ?? '') . '|' . $l['n']; if (isset($deja[$k])) continue; $deja[$k] = 1;
      $e = ['ts' => tsDeFr($l['date'] ?? '', $b['recu'] ?? maintenant()), 't' => 'serie', 'id' => (string) $l['id'], 'import' => 1];
      foreach (['ok', 'n', 'date', 'sec', 'tot', 'mode', 'val', 'nb', 'best', 'nom', 'niv'] as $kk) if (array_key_exists($kk, $l)) $e[$kk] = $l[$kk];
      $evs[] = $e; }
    /* la progression de l'élève reprend aussi ces résultats (utile quand il se connecte sur un autre appareil) */
    $res = []; foreach (($b['bilan']['lots'] ?? []) as $l) if (is_array($l) && !empty($l['id']) && !empty($l['n'])) $res[$l['id']] = array_intersect_key($l, array_flip(['ok', 'n', 'date', 'sec', 'tot', 'mode', 'val', 'nb', 'best', 'nom', 'niv']));
    $neuf = ['labo' => [], 'fc' => ['res' => $res]]; if (!empty($b['bilan']['test']['doms'])) $neuf['fc']['test'] = $b['bilan']['test'];
    avecVerrou("$cl-$el", function () use ($cl, $el, $neuf, $b) {
      $rec = lireJ(recRel($cl, $el)) ?: ['etat' => ['labo' => [], 'fc' => []], 'cree' => $b['recu'] ?? maintenant(), 'pv' => 0];
      $rec['etat'] = fusion(etatPropreA($rec['etat'] ?? []), $neuf); ecrireJ(recRel($cl, $el), $rec);
    });
    if (!empty($b['bilan']['test']['doms'])) { $t = $b['bilan']['test']; $doms = []; foreach ($t['doms'] as $k => $d) $doms[$k] = $d['status'] ?? '';
      $evs[] = ['ts' => tsDeFr($t['date'] ?? '', $b['recu'] ?? maintenant()), 't' => 'test', 'date' => $t['date'] ?? '', 'cls' => $t['cls'] ?? '', 'doms' => $doms, 'import' => 1]; }
    usort($evs, fn($x, $y) => strcmp($x['ts'], $y['ts']));
    avecVerrou("$cl-$el", fn() => ajouterJournal($cl, $el, $evs)); $n['journal'] += count($evs);
  }
  foreach (($D['eleves'] ?? []) as $x) {
    $cl = $x['cl'] ?? ''; $el = $x['el'] ?? ''; if (!okId($cl) || !okId($el)) continue;
    $src = $x['rec'] ?? ['etat' => $x['etat'] ?? [], 'cree' => $x['cree'] ?? null, 'maj' => $x['maj'] ?? null, 'vu' => $x['vu'] ?? null];
    avecVerrou("$cl-$el", function () use ($cl, $el, $src, $x, &$n) {
      $rec = lireJ(recRel($cl, $el)) ?: ['etat' => ['labo' => [], 'fc' => []], 'cree' => $src['cree'] ?? maintenant(), 'pv' => 0];
      $avant = etatPropreA($rec['etat'] ?? []);
      $apres = fusion($avant, etatPropreA($src['etat'] ?? []));
      /* journal : on reprend celui de la sauvegarde s'il existe, sinon on le reconstitue à partir des dates */
      if (!empty($x['journal']) && is_array($x['journal'])) {
        $deja = []; foreach (lireJournal($cl, $el) as $e) $deja[json_encode($e)] = 1;
        $evs = array_values(array_filter($x['journal'], fn($e) => is_array($e) && !isset($deja[json_encode($e)])));
      } else {
        $evs = differences($avant, $apres, maintenant());
        foreach ($evs as &$e) { $e['import'] = 1; $d = $e['date'] ?? ($e['t'] === 'etape' ? '' : ''); $e['ts'] = tsDeFr($d, $src['maj'] ?? ($src['vu'] ?? $e['ts'])); } unset($e);
        usort($evs, fn($p, $q) => strcmp($p['ts'], $q['ts']));
      }
      ajouterJournal($cl, $el, $evs); $n['journal'] += count($evs);
      $rec['etat'] = $apres;
      foreach (['cree', 'maj', 'vu'] as $k) if (!empty($src[$k]) && (empty($rec[$k]) || ($k === 'cree' ? $src[$k] < $rec[$k] : $src[$k] > $rec[$k]))) $rec[$k] = $src[$k];
      ecrireJ(recRel($cl, $el), $rec); $n['eleves']++;
    });
  }
  repondre(['ok' => true, 'importe' => $n]);
}

/* ---------------------------- badges pour le Cahier de suivi ----------------------------
   Même calcul que tdb.js (analyse) + badges.js (dates), pour toutes les classes : format « labo-cahier-1 »,
   identique au fichier « Badges pour le Cahier (JSON) » de l'espace professeur. */
function jourCle(DateTimeInterface $d): string { return $d->format('Y-m-d'); }
function dateTs($ts): ?DateTimeImmutable { if (!is_string($ts) || $ts === '') return null; try { return (new DateTimeImmutable($ts))->setTimezone(new DateTimeZone('Europe/Paris')); } catch (Throwable $e) { return null; } }
function dateFrMidi($s): ?DateTimeImmutable { return preg_match('/^(\d\d)\/(\d\d)\/(\d{4})/', (string) $s, $m) ? new DateTimeImmutable("{$m[3]}-{$m[2]}-{$m[1]} 12:00:00", new DateTimeZone('Europe/Paris')) : null; }
function ceintureInfo(string $id): array {
  if (preg_match('/^(\w+)-([3456])-(\w+)$/', $id, $m)) return ['ech' => $m[1], 'niv' => $m[2], 'coul' => $m[3]];
  if (preg_match('/^(\w+)-expert$/', $id, $m)) return ['ech' => $m[1], 'niv' => '', 'coul' => 'expert'];
  return ['ech' => '?', 'niv' => '', 'coul' => ''];
}
function datesBadges(array $chrono, array $ceintures, string $niv, ?DateTimeImmutable $test): array {
  $R = []; $pose = function (string $id, $d) use (&$R) { if (!isset($R[$id]) && $d) $R[$id] = $d; };
  $lots = []; $jours = []; $sem = []; $bas = []; $temps = 0; $ecrites = 0;
  foreach ($chrono as $e) {
    $temps += $e['t'];
    if ($temps >= 3600) $pose('lab-1h', $e['d']);
    if ($temps >= 5 * 3600) $pose('lab-5h', $e['d']);
    $s = $e['serie']; if (!$s) continue;
    $pose('lab-premier', $e['d']);
    $lots[$s['id']] = 1; if (count($lots) >= 10) $pose('lab-10lots', $e['d']);
    $jours[jourCle($e['d'])] = 1; if (count($jours) >= 5) $pose('lab-assidu', $e['d']);
    $l = $e['d']->setTime(12, 0)->modify('-' . ((int) $e['d']->format('N') - 1) . ' days'); $sem[jourCle($l)] = 1;
    if (isset($sem[jourCle($l->modify('-7 days'))], $sem[jourCle($l->modify('-14 days'))])) $pose('lab-regulier', $e['d']);
    if ($s['pct'] === 100 && $s['n'] >= 20 && $s['mode'] && $s['mode'] !== 'honneur') $pose('lab-parfaite', $e['d']);
    if ($s['mode'] === 'ecrit' && ++$ecrites >= 10) $pose('lab-plume', $e['d']);
    if ($s['pct'] < 60) $bas[$s['id']] = true; elseif ($s['pct'] >= 80 && !empty($bas[$s['id']])) $pose('lab-progres', $e['d']);
  }
  $pose('lab-test', $test);
  $C = array_map(fn($c) => $c + ['d' => dateFrMidi($c['s']['date'] ?? '') ?: new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'))], $ceintures);
  usort($C, fn($a, $b) => $a['d'] <=> $b['d']);
  $par = []; $nc = (int) $niv;
  foreach ($C as $c) {
    $par[$c['id']] = $c; $v = array_values($par);
    $pose('lb-1', $c['d']);
    if (count($v) >= 5) $pose('lb-5', $c['d']);
    if (count($v) >= 15) $pose('lb-15', $c['d']);
    if (count(array_unique(array_column($v, 'ech'))) >= 5) $pose('lb-ech', $c['d']);
    if (isset($c['s']['score'], $c['s']['total']) && (float) $c['s']['score'] === (float) $c['s']['total']) $pose('lb-parfait', $c['d']);
    if ($nc && $c['niv'] !== '' && (int) $c['niv'] < $nc) $pose('lb-avance', $c['d']);
    if ($c['coul'] === 'noire') $pose('lb-noire', $c['d']);
    if ($c['coul'] === 'expert') $pose('lb-expert', $c['d']);
    if (count(array_filter($v, fn($x) => $x['coul'] === 'expert')) >= 5) $pose('lb-expert5', $c['d']);
    if ($c['niv'] !== '') { $tout = true; foreach (['blanche', 'jaune', 'orange', 'verte', 'bleue', 'marron', 'noire'] as $k) if (!isset($par["{$c['ech']}-{$c['niv']}-$k"])) { $tout = false; break; } if ($tout) $pose('lb-complet', $c['d']); }
  }
  return $R;
}
function eleveCahier(array $c, array $e, string $niv): array {
  $r = lireJ(recRel($c['id'], $e['id'])) ?: [];
  $etat = etatPropreA($r['etat'] ?? []);
  $J = lireJournal($c['id'], $e['id']);
  usort($J, fn($a, $b) => strcmp((string) ($a['ts'] ?? ''), (string) ($b['ts'] ?? '')));
  $vMin = 2; $parLot = []; $chrono = []; $tempsTotal = 0; $nOk = 0; $sP = 0; $nC = 0; $dates = [];
  foreach ($J as $e2) {
    $d = dateTs($e2['ts'] ?? null); if (!$d) continue;
    if (empty($e2['import'])) $dates[] = $d;
    if (($e2['t'] ?? '') !== 'serie' || empty($e2['n'])) continue;
    $id = (string) ($e2['id'] ?? ''); $prev = $parLot[$id] ?? null;
    $nouvelle = !$prev || ((isset($e2['nb']) && isset($prev['nb'])) ? +$e2['nb'] > +$prev['nb']
      : (($e2['date'] ?? null) !== ($prev['date'] ?? null) || ($e2['ok'] ?? null) !== ($prev['ok'] ?? null) || ($e2['sec'] ?? null) !== ($prev['sec'] ?? null)));
    $t = ($prev && isset($e2['tot']) && isset($prev['tot'])) ? max(0, +$e2['tot'] - +$prev['tot']) : (+($e2['sec'] ?? 0) ?: 0);
    if (!$prev && isset($e2['tot']) && +($e2['nb'] ?? 0) > 1 && !empty($e2['import'])) $t = +$e2['tot'];
    $parLot[$id] = $e2;
    $ok = +($e2['ok'] ?? 0); $n = +$e2['n']; $pct = (int) round($ok / $n * 100);
    $ec = ($e2['mode'] ?? '') === 'ecrit' ? 2 : 1;
    $suspect = $ok > $n || (isset($e2['sec']) && +$e2['sec'] / $n < $vMin * $ec);
    if (!$suspect) $tempsTotal += $t;
    $serie = $nouvelle && !$suspect ? ['id' => $id, 'pct' => $pct, 'n' => $n, 'mode' => ($e2['mode'] ?? null) ?: null] : null;
    $chrono[] = ['d' => $d, 't' => $suspect ? 0 : $t, 'serie' => $serie];
    if ($nouvelle) $dates[] = $d;
    if ($serie) { $nOk++; $sP += $pct * $n; $nC += $n; }
  }
  if (!empty($r['vu']) && ($d = dateTs($r['vu']))) $dates[] = $d;
  $C = (array) ($etat['labo']['ceintures'] ?? []); $cein = []; $ceintures = [];
  foreach ($C as $id => $s) {
    if (!is_array($s) || !in_array($s['etat'] ?? '', ['reussie', 'validee'], true)) continue;
    $cein[] = ['id' => (string) $id, 's' => $s] + ceintureInfo((string) $id);
    $ceintures[(string) $id] = ['etat' => $s['etat'], 'score' => $s['score'] ?? null, 'total' => $s['total'] ?? null, 'date' => $s['date'] ?? null, 'code' => $s['code'] ?? '', 'valide' => $s['valide'] ?? ''];
  }
  $t1 = null; foreach ($J as $x) if (($x['t'] ?? '') === 'test') { $t1 = dateTs($x['ts'] ?? null); break; }
  if (!$t1 && !empty($etat['fc']['test']['date'])) $t1 = dateFrMidi($etat['fc']['test']['date']);
  $B = datesBadges($chrono, $cein, $niv, $t1);
  rsort($dates);
  return ['cl' => $c['id'], 'classe' => $c['nom'], 'el' => $e['id'], 'prenom' => $e['prenom'],
    'badges' => (object) array_map('jourCle', $B), 'ceintures' => (object) $ceintures,
    'stats' => ['temps' => (int) round($tempsTotal / 60), 'series' => $nOk, 'reussite' => $nC ? (int) round($sP / $nC) : null, 'derniere' => $dates ? jourCle($dates[0]) : '']];
}
function pourCahier(): array {
  $out = ['format' => 'labo-cahier-1', 'date' => maintenant(), 'eleves' => []];
  foreach (classes()['list'] as $c) {
    $niv = preg_match('/([3456])/', (string) $c['nom'], $m) ? $m[1] : '';
    foreach (($c['eleves'] ?? []) as $e) $out['eleves'][] = eleveCahier($c, $e, $niv);
  }
  return $out;
}

/* ---------------------------- connecteur MCP pour le Cahier de suivi ----------------------------
   Adresse secrète : /labo/api/mcp/<jeton> (jeton dérivé de secret.key, affiché dans l'espace professeur).
   À ajouter une fois dans claude.ai (Réglages › Connecteurs › connecteur personnalisé, nom « Laboratoire ») :
   le Cahier appelle alors l'outil badges_cahier à chaque ouverture. Lecture seule. */
function jetonMcp(): string { return substr(b64u(hash_hmac('sha256', 'mcp-cahier', secret(), true)), 0, 32); }
function adresseMcp(): string { return 'https://' . ($_SERVER['HTTP_HOST'] ?? 'proflesage.fr') . '/labo/api/mcp/' . jetonMcp(); }
function mcp(string $jeton): void {
  if (!hash_equals(jetonMcp(), $jeton)) { usleep(400000); erreur('Adresse inconnue.', 404); }
  $m = $_SERVER['REQUEST_METHOD'] ?? '';
  if ($m === 'GET' || $m === 'DELETE') { header('Allow: POST'); http_response_code(405); exit; }
  if ($m !== 'POST') erreur('Méthode non permise.', 405);
  $req = json_decode((string) file_get_contents('php://input'), true);
  if (!is_array($req)) repondre(['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'Parse error']], 400);
  $lot = isset($req[0]); $rep = [];
  foreach ($lot ? $req : [$req] as $q) { $r = mcpUn(is_array($q) ? $q : []); if ($r !== null) $rep[] = $r; }
  if (!$rep) { http_response_code(202); exit; }
  repondre($lot ? $rep : $rep[0]);
}
function mcpUn(array $q): ?array {
  $id = $q['id'] ?? null; $meth = (string) ($q['method'] ?? ''); $p = is_array($q['params'] ?? null) ? $q['params'] : [];
  if (!array_key_exists('id', $q)) return null; /* notification */
  $ok = fn($res) => ['jsonrpc' => '2.0', 'id' => $id, 'result' => $res];
  $ko = fn(int $c, string $msg) => ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $c, 'message' => $msg]];
  $outil = ['name' => 'badges_cahier', 'title' => 'Badges et ceintures du Laboratoire',
    'description' => "Badges du Laboratoire (avec leur date d'obtention), ceintures réussies ou validées et quelques chiffres (temps de travail, séries, réussite) de tous les élèves de toutes les classes. Format labo-cahier-1, lu par le Cahier de suivi.",
    'inputSchema' => ['type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false],
    'annotations' => ['title' => 'Badges et ceintures du Laboratoire', 'readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => false]];
  switch ($meth) {
    case 'initialize':
      return $ok(['protocolVersion' => is_string($p['protocolVersion'] ?? null) ? $p['protocolVersion'] : '2025-06-18', 'capabilities' => ['tools' => ['listChanged' => false]],
        'serverInfo' => ['name' => 'laboratoire', 'title' => 'Laboratoire de la langue', 'version' => '1.0.0'],
        'instructions' => 'Lecture seule : badges et ceintures des élèves du Laboratoire de la langue (proflesage.fr/labo), pour le Cahier de suivi.']);
    case 'ping': return $ok(new stdClass());
    case 'tools/list': return $ok(['tools' => [$outil]]);
    case 'tools/call':
      if (($p['name'] ?? '') !== 'badges_cahier') return $ko(-32602, 'Outil inconnu.');
      $d = pourCahier();
      return $ok(['content' => [['type' => 'text', 'text' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]], 'structuredContent' => $d, 'isError' => false]);
    default: return $ko(-32601, 'Méthode inconnue.');
  }
}

/* ---------------------------- aiguillage ---------------------------- */
function traiter(): void {
  $o = $_SERVER['HTTP_ORIGIN'] ?? '';
  $chemin = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
  $action = isset($_GET['a']) ? (string) $_GET['a'] : preg_replace('#^.*/api/?#', '', $chemin);
  $action = trim((string) $action, '/');
  if ($o && in_array($o, ORIGINES, true)) { header("Access-Control-Allow-Origin: $o"); header('Vary: Origin'); header('Access-Control-Allow-Methods: POST, OPTIONS'); header('Access-Control-Allow-Headers: content-type, x-prof'); header('Access-Control-Max-Age: 86400'); }
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
  if (preg_match('#^mcp/([A-Za-z0-9_-]{1,64})$#', $action, $mm)) { try { mcp($mm[1]); } catch (Throwable $e) { error_log('labo mcp: ' . $e->getMessage()); erreur('Erreur du serveur.', 500); } }
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') erreur('Méthode non permise.', 405);
  $brut = (string) file_get_contents('php://input');
  if (strlen($brut) > 30 * 1024 * 1024) erreur('Envoi trop volumineux.', 413);
  $body = json_decode($brut, true); if (!is_array($body)) $body = [];
  try {
    if (strpos($action, 'prof/') === 0) {
      $mdp = (string) (config()['prof_mdp'] ?? '');
      $donne = (string) ($_SERVER['HTTP_X_PROF'] ?? '');
      if ($mdp === '' || !hash_equals(hash('sha256', $mdp), hash('sha256', $donne))) { usleep(400000); erreur('Mot de passe enseignant incorrect.', 403); }
      prof(substr($action, 5), $body);
    }
    eleve($action, $body);
  } catch (Throwable $e) {
    error_log('labo: ' . $e->getMessage() . ' @' . $e->getFile() . ':' . $e->getLine());
    erreur('Erreur du serveur.', 500);
  }
}
traiter();

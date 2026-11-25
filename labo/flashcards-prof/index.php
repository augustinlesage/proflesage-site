<?php
/* Flashcards, version professeur (appli complète : éditeur, tous les lots, exports), réservée à l'enseignant (23/11/2026).
   Ouverte depuis l'espace professeur du Laboratoire : un formulaire envoie le mot de passe enseignant (POST mdp),
   on pose un cookie de session signé (valable 12 h, seulement pour /labo/flashcards-prof/) puis on redirige ici en GET.
   La page elle-même (page.html.gz) n'est jamais servie directement (.htaccess). */
declare(strict_types=1);
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: same-origin');
$conf = @include __DIR__ . '/../api/config.php';
$mdp = is_array($conf) ? (string) ($conf['prof_mdp'] ?? '') : '';
const DUREE = 12 * 3600;
$chemin = '/labo/flashcards-prof/';
function signeFc(string $mdp, int $exp): string { return hash_hmac('sha256', "flashcards-prof|$exp", hash('sha256', 'fcprof' . $mdp)); }
function poser(string $v, int $exp, string $chemin): void {
  setcookie('fcprof', $v, ['expires' => $exp, 'path' => $chemin, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
}
function refus(string $m, int $code = 403): void {
  http_response_code($code); header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store');
  echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Flashcards · version professeur</title>'
    . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;font:16px/1.5 system-ui,sans-serif;background:#eceff5;color:#2f3a52}main{max-width:460px;padding:24px;border:1px solid #dce0ea;border-radius:12px;background:#fff;margin:16px}a{color:#5d6890}'
    . '@media (prefers-color-scheme:dark){body{background:#161a24;color:#e3e7f0}main{background:#222837;border-color:#363d50}a{color:#aab6dc}}</style></head>'
    . '<body><main><h1 style="font-size:1.2rem;margin:0 0 8px">Flashcards, version professeur</h1><p>' . $m . '</p><p><a href="/labo/prof.html">Aller à l’espace professeur</a></p></main></body></html>';
  exit;
}
if (isset($_GET['sortir'])) { poser('', 1, $chemin); http_response_code(204); exit; }
if ($mdp === '') refus('L’espace professeur n’est pas configuré sur ce serveur.', 503);
/* onglet « Réglages » de l'appli (25/11/2026) : POST ?reglages (JSON), cookie enseignant exigé ; même stockage que le serveur du Laboratoire */
function cookieOk(string $mdp): bool {
  $c = (string) ($_COOKIE['fcprof'] ?? '');
  return preg_match('/^(\d{10})\.([0-9a-f]{64})$/', $c, $m) === 1 && (int) $m[1] > time() && hash_equals(signeFc($mdp, (int) $m[1]), $m[2]);
}
if (isset($_GET['reglages'])) {
  header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo '{"erreur":"Méthode non permise."}'; exit; }
  if (!cookieOk($mdp)) { http_response_code(403); echo '{"erreur":"Session enseignant expirée : rouvre les flashcards depuis l’espace professeur."}'; exit; }
  define('LABO_SANS_AIGUILLAGE', true);
  require __DIR__ . '/../api/index.php';
  $body = json_decode((string) file_get_contents('php://input'), true);
  try { repondre(majReglages(is_array($body) ? $body : [])); }
  catch (Throwable $e) { error_log('fcprof reglages: ' . $e->getMessage()); erreur('Erreur du serveur.', 500); }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  $donne = (string) ($_POST['mdp'] ?? '');
  if (!hash_equals(hash('sha256', $mdp), hash('sha256', $donne))) { usleep(400000); refus('Mot de passe enseignant incorrect. Connectez-vous d’abord à l’espace professeur, puis cliquez sur « Flashcards (version prof) ».'); }
  $exp = time() + DUREE;
  poser($exp . '.' . signeFc($mdp, $exp), $exp, $chemin);
  header('Location: ' . $chemin, true, 303); exit;
}
if (!cookieOk($mdp)) refus('Cette page est réservée à l’enseignant. Ouvrez-la depuis l’espace professeur du Laboratoire (onglet « Flashcards (version prof) »).');
$f = __DIR__ . '/page.html.gz';
if (!is_file($f)) refus('Les flashcards version professeur ne sont pas encore publiées.', 404);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: private, no-cache');
if (strpos((string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''), 'gzip') !== false && !ini_get('zlib.output_compression')) {
  header('Content-Encoding: gzip'); header('Vary: Accept-Encoding'); header('Content-Length: ' . filesize($f)); readfile($f);
} else echo gzdecode((string) file_get_contents($f));

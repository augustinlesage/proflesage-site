/* Barre latérale de « La classe de M. Lesage », pour les pages du site hors accueil.
   Fichier construit par site-classe/build.py : ne pas modifier à la main. Usage : <script src="/classe-nav.js"></script> en fin de <body>. */
(function(){
if(window.__classeNav) return; window.__classeNav = 1;
window.CLASSE_BASE = "/"; window.CLASSE_CONTENUS = ["p-ma-classe-badges", "p-ma-classe-cahier", "p-ma-classe-cahier--legende", "p-ma-classe-cahier--materiel", "p-ma-classe-parents", "p-ma-classe-parents--tutoriel-pronote", "p-ma-classe-regles", "p-ma-classe-rentree", "p-ma-classe-textes-officiels", "p-ma-classe-vie-de-classe", "p-ma-classe-vie-de-classe--responsabilites", "p-ma-classe-vie-de-classe--sequences"];
var st = document.createElement("style"); st.textContent = "aside.side{position:sticky;top:0;height:100vh;overflow-y:auto;background:var(--side);border-right:1px solid var(--line);\n  display:flex;flex-direction:column;padding:20px 14px 16px max(14px,env(safe-area-inset-left,0px))}\naside.side .marque{display:flex;gap:10px;align-items:center;text-decoration:none;padding:4px 8px 16px}\naside.side .marque .ardoise{width:38px;height:38px;border-radius:9px;background:var(--board);border:3px solid var(--clay);color:var(--chalk);\n  display:grid;place-items:center;font:700 15px var(--f-titre)}\naside.side .marque b{display:block;font:700 1.02rem/1.2 var(--f-titre)}\naside.side .marque small{color:var(--muted);font-size:.8rem}\naside.side nav ul{list-style:none;margin:0;padding:0}\naside.side .lien{display:flex;align-items:center;gap:10px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:.93rem}\naside.side a.lien:hover{background:var(--line)}\naside.side .lien[aria-current=\"page\"]{background:var(--ink);color:var(--paper)}\naside.side .lien .ext{margin-left:auto;opacity:.55;width:14px;height:14px}\naside.side .puce{width:28px;height:28px;border-radius:8px;display:grid;place-items:center;flex:none;color:var(--g);background:color-mix(in srgb,var(--g) 14%,transparent)}\naside.side .puce svg.i{width:16px;height:16px}\naside.side details.grp{margin-top:2px}\naside.side details.grp>summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:10px;padding:5px 8px;border-radius:8px;font-weight:650;font-size:.95rem}\naside.side details.grp>summary::-webkit-details-marker{display:none}\naside.side details.grp>summary:hover{background:var(--line)}\naside.side details.grp>summary .chev{margin-left:auto;width:15px;height:15px;transition:transform .15s;opacity:.6}\naside.side details.grp[open]>summary .chev{transform:rotate(90deg)}\naside.side details.grp>ul{margin:2px 0 8px 21px;padding-left:12px;border-left:2px solid color-mix(in srgb,var(--g) 45%,transparent)}\naside.side details.grp ul .lien{padding:4px 10px;font-size:.89rem}\naside.side details.grp ul ul{margin:0 0 2px 10px}\naside.side details.grp ul ul .lien{font-size:.84rem;padding:3px 10px}\naside.side .nav-seul{display:flex;align-items:center;gap:10px;padding:5px 8px;border-radius:8px;font-weight:650;font-size:.95rem;text-decoration:none}\naside.side .nav-seul:hover{background:var(--line)}\naside.side .nav-seul[aria-current=\"page\"]{background:var(--ink);color:var(--paper)}\naside.side .nav-seul[aria-current=\"page\"] .puce{background:var(--paper)}\naside.side .bientot{color:var(--muted)}\naside.side .pt{width:8px;height:8px;border-radius:50%;display:inline-block;flex:none}\naside.side .pt.on{background:var(--ok)}\naside.side .pt.off{background:transparent;border:1.5px solid var(--muted);opacity:.7}\naside.side .lien .pt{margin-left:auto}\naside.side .bas{margin-top:auto;padding-top:14px;border-top:1px solid var(--line)}\naside.side .rail-b{display:flex;align-items:center;gap:10px;width:100%;margin-top:8px;padding:6px 10px;border:0;border-radius:8px;background:none;color:var(--muted);font:inherit;font-size:.85rem;cursor:pointer}\naside.side .rail-b:hover{background:var(--line);color:var(--ink)}\naside.side .rail-b svg.i{transition:transform .15s}\nhtml.cn-rail aside.side:not(.deploye){width:68px;padding-left:10px;padding-right:10px;overflow-x:hidden}\nhtml.cn-rail aside.side:not(.deploye) .marque{justify-content:center;padding:4px 0 16px}\nhtml.cn-rail aside.side:not(.deploye) .t,html.cn-rail aside.side:not(.deploye) .chev,html.cn-rail aside.side:not(.deploye) details.grp>ul,html.cn-rail aside.side:not(.deploye) .marque>span:last-child{display:none}\nhtml.cn-rail aside.side:not(.deploye) details.grp>summary,html.cn-rail aside.side:not(.deploye) .nav-seul,html.cn-rail aside.side:not(.deploye) .lien,html.cn-rail aside.side:not(.deploye) .rail-b{justify-content:center;padding-left:0;padding-right:0}\nhtml.cn-rail aside.side:not(.deploye) .rail-b svg.i{transform:rotate(180deg)}\nhtml.cn-rail aside.side.deploye{position:fixed;z-index:45;top:0;left:0;bottom:0;width:280px;height:100vh;box-shadow:0 10px 40px rgb(0 0 0 / .25)}\naside.side{\n  --paper:#fbf5ee; --card:#fffdf9; --side:#f4ebe0; --line:#eadfd1;\n  --ink:#373e56; --muted:#6a6f78; --clay:#b27560; --sun:#f7cc73;\n  --board:#5f7d6d; --chalk:#fbf8f1; --ok:#3d7d58;\n  /* code couleur des parties */\n  --c-ardoise:#373e56; --c-bois:#b27560; --c-terracotta:#b25751; --c-violet:#7c659e; --c-sarcelle:#4f9b8f;\n  --c-bleu:#4a76a8; --c-vert:#6b8f47; --c-ocre:#a87a22; --c-rose:#b5577f; --c-prune:#7a4d6e;\n  --f-titre:ui-rounded,\"SF Pro Rounded\",\"Nunito\",\"Segoe UI Rounded\",system-ui,sans-serif;\n  --f-texte:system-ui,-apple-system,\"Segoe UI\",Roboto,sans-serif;\n  color-scheme:light;\n}\naside.side,aside.side *{box-sizing:border-box}\naside.side{position:fixed!important;top:0;left:0;bottom:0;width:280px;z-index:40;font:16px/1.55 var(--f-texte);color:var(--ink);text-align:left}\naside.side a{color:inherit}\naside.side svg.i{width:18px;height:18px;flex:none;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}\naside.side [data-g]{--g:var(--c-ardoise)}\nhtml.cn-std body{padding-left:280px}html.cn-std.cn-rail body{padding-left:68px}\n.cn-menu-b,.cn-voile{display:none}\n@media (max-width:600px){html.cn-std body{padding-left:0}aside.side{width:min(300px,86vw);transform:translateX(-102%);transition:transform .2s}html.cn-menu aside.side{transform:none}.cn-menu-b{display:grid;place-items:center;position:fixed;left:14px;bottom:14px;z-index:39;width:46px;height:46px;border-radius:50%;border:1px solid #eadfd1;background:#373e56;color:#fbf5ee;box-shadow:0 4px 14px rgb(0 0 0 / .2);cursor:pointer}.cn-menu-b svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}html.cn-menu .cn-voile{display:block;position:fixed;inset:0;z-index:38;background:rgb(0 0 0 / .35)}}\n@media print{aside.side,.cn-menu-b{display:none!important}html.cn-std body{padding-left:0}}\n"; document.head.appendChild(st);
var pt = document.createElement("script"); pt.type = "text/plain"; pt.id = "plan-txt"; pt.textContent = "# Accueil | ardoise\n# Ma classe | bois\n- *Programme du jour | Le déroulé des dernières séances et la séance du jour en fonction de la classe\n- *Emploi du temps | Les heures de français et les salles\n- Règles | Ce qu’on fait, ce qu’on évite, les sanctions\n- Badges | Présentation du fonctionnement\n- Cahier | Pages de garde, mots nouveaux, phrase du jour\n\t- Matériel\n\t- Légende | Le code de pictogrammes\n- Rentrée | Accueil et séquences de rentrée\n- Vie de classe | Délégués, professeur principal\n\t- Séquences\n\t- Responsabilités\n\t- Orientation\n\t- Ressources\n- Parents | Pronote, réunions des parents, sorties\n\t- Tutoriel Pronote | Comment utiliser Pronote\n- Textes officiels | Cycle 3, cycle 4, français et latin\n- *Progressions [prof] | Progressions annuelles\n# Littérature | sarcelle\n- 6ᵉ | L’Odyssée, contes et monstres, pirates, géants…\n- 5ᵉ | Contes merveilleux, héroïsme, Molière, poésie…\n- 4ᵉ | Le Cid, Les Misérables…\n- 3ᵉ | Séquences et préparation au brevet\n# Langue | terracotta\n- Grammaire | Leçons, homophones, tri de mots\n- Orthographe | Accords et règles\n- Conjugaison | Leçons et tableaux\n- Vocabulaire | Formation des mots, sens, registres\n- Dictées | Dictées préparées et d’entraînement\n- Chantiers\n- Laboratoire | Parcours, entraînements et ceintures | /labo/\n- Flashcards | Grammaire, conjugaison, homophones, vocabulaire | /flashcards/\n# Latin et Antiquité | ocre\n- Cours | Séquences de latin par niveaux\n- Vocabulaire | Les lots de la 5ᵉ à la 3ᵉ dans les flashcards | /flashcards/\n- Langues et cultures | Les mots venus d’ailleurs\n# Lecture | bleu\n- Lire au collège | Un guide pour trouver le livre qui te plaira | /lire/\n- Lectures de vacances | Des listes par niveau et par envie\n- Bibliothèque de la classe\n- Carnet de lecture | Le carnet et les fiches de lecture\n- Comptes-rendus et exposés | Dépliants, exemples, exposés\n- Quart d’heure lecture | Les textes du quart d’heure\n# Écriture | vert\n- Ateliers d’écriture | Sujets et consignes\n- Joggings d’écriture | Écrire un peu chaque jour\n- Images d’inspiration | Des images pour lancer l’écriture\n- Outils pour la rédaction | Grilles, listes de mots, aides\n# Méthode | violet\n- Méthodes | Consignes, brouillon, réécriture, portrait, champ lexical, soin de la copie\n- Connaissances | La poésie, les figures de style\n- Fiches, affiches et cartes mentales | Les mémos à imprimer\n# Brevet | prune\n- Présentation de l’épreuve\n- Sujets et annales | Sujets, dictées et corrigés\n- Entraînements\n# Prolongements | rose\n- Jeux et leçons à manipuler | Les Conjugouillons, Orthodingo, Le village des natures\n- Égalité filles-garçons\n- Activités créatives | Marque-pages…\n- Éducation à la vie sexuelle et affective\n- Histoire des arts\n- Médias\n\t- Revues de presse | L’actualité racontée par la classe\n\t- S’informer | Vérifier une information, lire la presse\n# En bas de la barre latérale\n- Outils | Les outils de la classe\n- Plan du site | Toutes les pages, rubrique par rubrique\n- À propos | Licence et crédits"; document.body.appendChild(pt);
document.documentElement.classList.add("cn-std");
var side = document.createElement("aside"); side.className = "side"; side.id = "side"; side.setAttribute("aria-label", "Plan du site");
side.innerHTML = '<a class="marque" href="/#accueil"><span class="ardoise">L</span><span><b>La classe de M.&nbsp;Lesage</b><small>Français · 2026-2027</small></span></a><nav id="nav"></nav><div class="bas"><ul id="nav-bas" style="list-style:none;margin:0;padding:0"></ul></div>';
document.body.insertBefore(side, document.body.firstChild);
var mb = document.createElement("button"); mb.type = "button"; mb.className = "cn-menu-b"; mb.setAttribute("aria-label", "Ouvrir le plan du site");
mb.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
var vo = document.createElement("div"); vo.className = "cn-voile";
document.body.appendChild(vo); document.body.appendChild(mb);
mb.addEventListener("click", function(){ document.documentElement.classList.add("cn-menu"); });
vo.addEventListener("click", function(){ document.documentElement.classList.remove("cn-menu"); });
(function(){

const NB = " ";
const BASE = window.CLASSE_BASE || "";
const SITE = "https://proflesage.fr";
const SUR_SITE = location.hostname.endsWith("proflesage.fr");

const ICO = {
  fleche:'<path d="M4 12h15M13 6l6 6-6 6"/>',
  horloge:'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  bonjour:'<path d="M21 12a8 8 0 0 1-11.8 7L3 21l2-5.4A8 8 0 1 1 21 12z"/><path d="M8.5 9.5v5M8.5 12h3.5M12 9.5v5M15.5 9.5v5"/>',
  cintre:'<path d="M10 5a2 2 0 1 1 2 2v1.5"/><path d="M12 8.5 3.4 15.6A1.4 1.4 0 0 0 4.3 18h15.4a1.4 1.4 0 0 0 .9-2.4z"/>',
  fournitures:'<path d="M4 4h4v16H4zM9 4h4v16H9z"/><path d="M14.5 5.5l3.8-1 3.4 14.6-3.8 1z"/>',
  bavarder:'<path d="M14 9a2 2 0 0 1-2 2H6l-3 3V4a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2z"/><path d="M18 9h1a2 2 0 0 1 2 2v10l-3-3h-6a2 2 0 0 1-2-2v-1"/>',
  pomme:'<path d="M12 7.5c-1.6-1-5.2-1.4-6.6 1.6S5 16.2 7.5 19.2c1.4 1.8 3 1.4 4.5.7 1.5.7 3.1 1.1 4.5-.7 2.5-3 3.5-7.1 2.1-10.1S13.6 6.5 12 7.5z"/><path d="M12 7.5c0-2 1-3.6 3-4.5"/>',
  cartable:'<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 13h18M11 13v2h2v-2"/>',
  poubelle:'<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M5 6l1 15h12l1-15"/><path d="M10 10v7M14 10v7"/>',
  devoir:'<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/><path d="M14 3v5h5"/><path d="M8 12h6M8 16h3"/><path d="M19.5 13.5a1.5 1.5 0 0 1 2 2L16 21l-3 .5.5-3z"/>',
  concentre:'<rect x="10" y="3" width="11" height="9" rx="1"/><circle cx="5.5" cy="8" r="2"/><path d="M2.5 21v-5a3 3 0 0 1 6 0v5"/><path d="M8 13.5l4-2.5"/><path d="M13 6h5M13 9h3"/>',
  question:'<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14"/><path d="M12 17h.01"/>',
  oeil:'<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
  marque:'<path d="M6 3h12v18l-6-4-6 4z"/>',
  crayon:'<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
  verifier:'<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 11l2 2 4-4"/>',
  haltere:'<path d="M6.5 6.5v11M17.5 6.5v11M3 9.5v5M21 9.5v5M6.5 12h11"/>',
  ramasse:'<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14l2 2 4-4"/>',
  note:'<circle cx="12" cy="12" r="9"/><path d="M8 16 16 8"/><circle cx="8.5" cy="8.5" r="1"/><circle cx="15.5" cy="15.5" r="1"/>',
  journal:'<path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/><path d="M2 6h4M2 10h4M2 14h4M2 18h4"/><path d="M18.4 2.6a2 2 0 1 1 3 3L16 11l-4 1 1-4z"/>',
  bonus:'<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
  plus:'<path d="M12 5v14M5 12h14"/>',
  moins:'<path d="M5 12h14"/>',
  porte:'<path d="M4 21h16"/><path d="M6 21V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17"/><path d="M14 12h.01"/>',
  main:'<path d="M18 11V6a2 2 0 0 0-4 0v5"/><path d="M14 10V4a2 2 0 0 0-4 0v6"/><path d="M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.9-6-2.4L2.4 16a2 2 0 0 1 2.8-2.8L7 15"/>',
  cocher:'<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
  ceinture:'<path d="M2 10h20v4H2z"/><rect x="9" y="8" width="6" height="8" rx="1"/><path d="M11 16l-2 6M13 16l2 6"/>',
  dossier:'<path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
  groupe:'<circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><path d="M16 4a4 4 0 0 1 0 8M22 21v-1a6 6 0 0 0-4-5.6"/>',
  idee:'<path d="M9 18h6M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.2 1 2V17h6v-.3c0-.8.4-1.5 1-2A7 7 0 0 0 12 2z"/>',
  grille:'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
  coeur:'<path d="M12 21s-8-5.2-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 5.8-8 11-8 11z"/>',
  video:'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M10 9l5 3-5 3z"/>',
  message:'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
  fichier:'<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>',
  maison:'<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
  classe:'<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M7 20h10M12 16v4"/><path d="M7 9h5"/>',
  livre:'<path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2.5z"/><path d="M8 7h7M8 11h5"/>',
  langue:'<path d="M9 3h6M10 3v6L4.5 18.5A1.7 1.7 0 0 0 6 21h12a1.7 1.7 0 0 0 1.5-2.5L14 9V3"/><path d="M7.5 15h9"/>',
  lire:'<path d="M2 5.5A1.5 1.5 0 0 1 3.5 4H9a3 3 0 0 1 3 3v13a2.5 2.5 0 0 0-2.5-2.5h-6A1.5 1.5 0 0 1 2 16z"/><path d="M22 5.5A1.5 1.5 0 0 0 20.5 4H15a3 3 0 0 0-3 3v13a2.5 2.5 0 0 1 2.5-2.5h6a1.5 1.5 0 0 0 1.5-1.5z"/>',
  plume:'<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/><path d="M14 20h6"/>',
  methode:'<path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9"/>',
  brevet:'<circle cx="12" cy="9" r="6"/><path d="M8.5 13.8 7 22l5-3 5 3-1.5-8.2"/>',
  colonne:'<path d="M3 21h18M4 7h16M12 3 4 7M12 3l8 4"/><path d="M6 7v14M10 7v14M14 7v14M18 7v14"/>',
  etoile:'<path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.4 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/>',
  outils:'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/>',
  plan:'<path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/>',
  info:'<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/>',
  cartes:'<rect x="3" y="6" width="14" height="14" rx="2"/><path d="M7 6V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-2"/>',
  chev:'<path d="M9 6l6 6-6 6"/>',
  replier:'<path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/>',
  ext:'<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
  menu:'<path d="M4 6h16M4 12h16M4 18h16"/>',
  soleil:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
  lune:'<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>'
};
const ic = (n, cls) => '<svg class="i'+(cls?' '+cls:'')+'" viewBox="0 0 24 24" aria-hidden="true">'+(ICO[n]||ICO.etoile)+'</svg>';
const ICO_RUB = {"accueil":"maison","ma classe":"classe","littérature":"livre","langue":"langue","latin et antiquité":"colonne","lecture":"lire","écriture":"plume","méthode":"methode","brevet":"brevet","prolongements":"etoile"};
const COULEURS = ["ardoise","bois","terracotta","violet","sarcelle","bleu","vert","ocre","rose","prune"];
const esc = s => s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
const slug = s => s.normalize("NFD").replace(/[̀-ͯ]/g,"").toLowerCase().replace(/[^a-z0-9]+/g,"-").replace(/^-|-$/g,"");

/* Lecture du fichier texte : « # Rubrique | couleur », « - Page | description | lien », tabulation = sous-page, [prof], « > » = note */
function lirePlan(txt){
  const rubs = []; let bas = null, rub = null, pile = [];
  for(const brute of txt.split(/\r?\n/)){
    if(!brute.trim()) continue;
    const ind = (brute.match(/^[\t ]*/)[0].replace(/ {2,4}/g,"\t").match(/\t/g)||[]).length;
    const l = brute.trim();
    if(l.startsWith("# ")){
      const [t, c] = l.slice(2).split("|").map(s=>s.trim());
      rub = {t, id:slug(t), col:COULEURS.includes((c||"").toLowerCase())?(c||"").toLowerCase():"ardoise", items:[]};
      if(/barre latérale/i.test(t)) bas = rub; else rubs.push(rub);
      pile = []; continue;
    }
    if(!rub || l.startsWith(">")) continue;
    if(l.startsWith("- ")){
      const parts = l.slice(2).split("|").map(s=>s.trim());
      let t = parts[0], prof = /\[prof\]/i.test(t), connecte = t.startsWith("*");
      t = t.replace(/^\*\s*/,"").replace(/\s*\[prof\]\s*/i,"").trim();
      const it = {t, d:parts[1]||"", url:parts[2]||"", prof, connecte, enfants:[]};
      pile.length = Math.min(pile.length, ind);
      const parent = ind>0 ? pile[ind-1] : null;
      (parent ? parent.enfants : rub.items).push(it);
      pile[ind] = it;
    }
  }
  return {rubs, bas};
}
const {rubs:PLAN, bas:BAS} = lirePlan(document.getElementById("plan-txt").textContent);
/* Connexion : la même que le Laboratoire (proflesage.fr/labo), partagée par le navigateur.
   Élève : localStorage « labo.compte » ; enseignant : sessionStorage « labo.prof ». */
const lireJ = k => { try{ return JSON.parse(localStorage.getItem(k)) || null; }catch(e){ return null; } };
const ecrireJ = (k,v) => { try{ localStorage.setItem(k, JSON.stringify(v)); }catch(e){} };
let COMPTE = lireJ("labo.compte"), PROF = false;
try{ PROF = !!sessionStorage.getItem("labo.prof"); }catch(e){}
try{ const sim = sessionStorage.getItem("classe.apercu"); if(!SUR_SITE && sim==="eleve") COMPTE = {prenom:"Prénom", classe:"6ᵉ 6 (aperçu)"}; if(!SUR_SITE && sim==="prof") PROF = true; }catch(e){}
const visible = it => it.prof ? PROF : it.connecte ? (PROF || !!COMPTE) : true;

const VUES_BAS = {"outils":"outils","plan du site":"plan","à propos":"apropos"};
const ICO_BAS = {outils:"outils",plan:"plan",apropos:"info"};

const href = it => it.url ? (/^https?:/.test(it.url) ? it.url : (SUR_SITE ? it.url : SITE + it.url)) : null;
const horsSite = it => !!it.url && (!SUR_SITE || /^https?:/.test(it.url));
/* Chaque page sans lien externe a sa vue dans le site : #p-<rubrique>-<page>, onglet : #p-<rubrique>-<page>--<sous-page> */
const PAGES = {};
PLAN.forEach(r=>r.items.forEach(it=>{ it.rub=r; it.vue="p-"+r.id+"-"+slug(it.t); PAGES[it.vue]=it; it.enfants.forEach(e=>{ e.vue=it.vue+"--"+slug(e.t); }); }));
const lienDe = it => it.url ? href(it) : BASE+"#"+it.vue;
const cible = it => horsSite(it) ? ' target="_blank" rel="noopener"' : '';

/* Une page est en ligne quand elle a un contenu (contenus.html), elle ou l’un de ses onglets */
const aTpl = k => window.CLASSE_CONTENUS ? window.CLASSE_CONTENUS.includes(k) : !!document.querySelector('template[data-page="'+k+'"]');
const aContenu = it => aTpl(it.vue) || (it.enfants||[]).some(e=>aTpl(e.vue));

function lienNav(it){
  if(!visible(it)) return "";
  const h = href(it);
  const tete = h ? '<a class="lien" href="'+esc(h)+'"'+cible(it)+'>'+esc(it.t)+(horsSite(it)?ic("ext","ext"):'<i class="pt on" title="En ligne"></i>')+'</a>'
                 : aContenu(it) ? '<a class="lien" data-vue="'+it.vue+'" href="'+BASE+'#'+it.vue+'">'+esc(it.t)+'<i class="pt on" title="En ligne"></i></a>'
                 : '<a class="lien bientot" data-vue="'+it.vue+'" href="'+BASE+'#'+it.vue+'">'+esc(it.t)+'<i class="pt off" title="En cours de construction"></i></a>';
  return '<li>'+tete+'</li>';
}

function lireOuverts(){ try{ return JSON.parse(localStorage.getItem("classe.ouverts")||"null"); }catch(e){ return null; } }
function garderOuverts(){ try{ localStorage.setItem("classe.ouverts", JSON.stringify([...document.querySelectorAll("details.grp[open]")].map(d=>d.dataset.id))); }catch(e){} }

function construireNav(){
  const ouverts = lireOuverts() || ["langue"];
  let h = "";
  for(const r of PLAN){
    const g = ' data-g style="--g:var(--c-'+r.col+')"';
    if(r.id==="accueil"){
      h += '<a class="nav-seul" data-vue="accueil" href="'+BASE+'#accueil" title="Accueil"'+g+'><span class="puce">'+ic("maison")+'</span><span class="t">Accueil</span></a>';
      continue;
    }
    h += '<details class="grp" data-id="'+r.id+'"'+(ouverts.includes(r.id)?' open':'')+g+'><summary title="'+esc(r.t)+'"><span class="puce">'+ic(ICO_RUB[r.t.toLowerCase()])+'</span><span class="t">'+esc(r.t)+'</span>'+ic("chev","chev")+'</summary><ul>'+r.items.map(lienNav).join("")+'</ul></details>';
  }
  document.getElementById("nav").innerHTML = h;
  const bas = (BAS ? BAS.items : [{t:"Plan du site"},{t:"À propos"}]);
  document.getElementById("nav-bas").innerHTML = bas.map(b=>{
    const v = VUES_BAS[b.t.toLowerCase()] || "plan";
    return '<li><a class="lien" data-vue="'+v+'" href="'+BASE+'#'+v+'" title="'+esc(b.t)+'">'+ic(ICO_BAS[v]||"plan")+'<span class="t">'+esc(b.t)+'</span></a></li>';
  }).join("");
  document.querySelectorAll("details.grp").forEach(d=>d.addEventListener("toggle", garderOuverts));
}

/* Barre réduite : automatique quand la fenêtre est étroite (1180 px ou moins), ou au choix avec le bouton (mémorisé) */
const RAIL_AUTO = 1180, TIROIR = 600;
function etatRail(){ let v=null; try{ v = localStorage.getItem("classe.rail"); }catch(e){} return v; }
function appliquerRail(){
  const w = innerWidth, v = etatRail(), side = document.getElementById("side");
  const on = w > TIROIR && (v==="1" || (v===null && w <= RAIL_AUTO));
  document.documentElement.classList.toggle("cn-rail", on);
  if(!on && side) side.classList.remove("deploye");
  const b = document.getElementById("rail-b");
  if(b){ b.title = on ? "Agrandir le menu" : "Réduire le menu"; b.setAttribute("aria-label", b.title); b.querySelector(".t").textContent = on ? "Agrandir" : "Réduire le menu"; }
}
function initRail(){
  const side = document.getElementById("side"), bas = side.querySelector(".bas");
  const b = document.createElement("button"); b.type = "button"; b.className = "rail-b"; b.id = "rail-b";
  b.innerHTML = ic("replier")+'<span class="t"></span>'; bas.appendChild(b);
  b.addEventListener("click", ev=>{ ev.stopPropagation(); const on = document.documentElement.classList.contains("cn-rail");
    try{ localStorage.setItem("classe.rail", on ? "0" : "1"); }catch(e){} side.classList.remove("deploye"); appliquerRail(); });
  /* en mode réduit, un clic sur une partie déplie le menu par-dessus la page */
  side.addEventListener("click", ev=>{
    if(!document.documentElement.classList.contains("cn-rail")) return;
    const s = ev.target.closest("summary");
    if(s && !side.classList.contains("deploye")){ ev.preventDefault(); side.classList.add("deploye"); s.parentElement.open = true; return; }
    if(ev.target.closest("a")) side.classList.remove("deploye");
  });
  document.addEventListener("click", ev=>{ if(side.classList.contains("deploye") && !ev.target.closest("#side")) side.classList.remove("deploye"); });
  document.addEventListener("keydown", ev=>{ if(ev.key==="Escape") side.classList.remove("deploye"); });
  addEventListener("resize", appliquerRail);
  appliquerRail();
}

construireNav(); initRail();
})();
var ici = location.pathname;
side.querySelectorAll("a.lien").forEach(function(a){ var u = new URL(a.href, location.href); if(u.pathname === ici && !u.hash && u.host === location.host){ a.setAttribute("aria-current", "page"); var d = a.closest("details"); if(d) d.open = true; } });
})();

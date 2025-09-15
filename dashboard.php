<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'], $_SESSION['school_class'], $_SESSION['school_track'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$school_class = $_SESSION['school_class'];
$school_track = $_SESSION['school_track'];

// Dati utente
$stmt = $conn->prepare("SELECT username, user_image FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$username = $user_res['username'];
$profile_image = $user_res['user_image'];

// Preferenze utente
$stmt = $conn->prepare("SELECT subject, type, description FROM user_preferences WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();

$know = [];
$want = [];
while ($r = $res->fetch_assoc()) {
    if ($r['type'] === 'know') {
        $know[$r['subject']] = $r['description'];
    } else {
        $want[$r['subject']] = $r['description'];
    }
}
$stmt->close();

// Media recensioni dell’utente loggato
$stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE reviewed_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rating_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$my_avg_rating = round($rating_data['avg_rating'], 1);
$my_total_reviews = $rating_data['total_reviews'];

// Matching logica
$matches = [];

if (!empty($know) && !empty($want)) {
    $inWant = "'" . implode("','", array_map([$conn,'real_escape_string'], array_keys($want))) . "'";
    $inKnow = "'" . implode("','", array_map([$conn,'real_escape_string'], array_keys($know))) . "'";

    $sql = "
      SELECT DISTINCT u.id, u.username, u.user_image
        FROM users u
        JOIN user_preferences up1 
          ON up1.user_id = u.id 
         AND up1.type = 'know'
         AND up1.subject IN ($inWant)
        JOIN user_preferences up2 
          ON up2.user_id = u.id 
         AND up2.type = 'want'
         AND up2.subject IN ($inKnow)
       WHERE u.id != ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $matches_res = $stmt->get_result();
    while ($row = $matches_res->fetch_assoc()) {
        $matches[$row['id']] = $row;
    }
    $stmt->close();

    $sql2 = "
      SELECT DISTINCT u.id, u.username, u.user_image
        FROM users u
        JOIN user_preferences up1 
          ON up1.user_id = u.id 
         AND up1.type = 'know'
         AND up1.subject IN ($inWant)
       WHERE u.id != ?
    ";
    $stmt = $conn->prepare($sql2);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $extra_res = $stmt->get_result();
    while ($row = $extra_res->fetch_assoc()) {
        if (!isset($matches[$row['id']])) {
            $matches[$row['id']] = $row;
        }
    }
    $stmt->close();
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $ini = '';
    foreach ($parts as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return mb_substr($ini, 0, 2);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Utente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
/* ===========================
   Refined Modern UI
   =========================== */

/* Dark base */
:root{
  --bg:#0a0d13;
  --bg-2:#0c1118;
  --surface:#0f1520;
  --surface-2:#121a26;
  --text:#eef3ff;
  --muted:#9aa6bf;
  --border:rgba(255,255,255,.10);
  --border-strong:rgba(255,255,255,.18);
  --accent:#5d79ff;
  --accent-2:#21b1ff;
  --radius:16px;
  --radius-lg:24px;
  --blur:14px;
  --speed:180ms;
  --shadow-1:0 20px 60px rgba(0,0,0,.45);
  --shadow-2:0 12px 32px rgba(0,0,0,.28);
}

/* Light override */
html[data-theme="light"]{
  --bg:#f6f8fd; --bg-2:#ffffff;
  --surface:#ffffff; --surface-2:#f2f5fb;
  --text:#0b1220; --muted:#4b5568;
  --border:rgba(10,20,40,.10); --border-strong:rgba(10,20,40,.16);
  --accent:#4d6bff; --accent-2:#0aaee8;
  --shadow-1:0 16px 48px rgba(10,20,40,.16);
  --shadow-2:0 10px 28px rgba(10,20,40,.12);
}

/* Reset & base */
html,body{height:100%}
*{box-sizing:border-box}
body{
  margin:0; color:var(--text);
  font: 500 16px/1.55 Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
  background: linear-gradient(180deg, var(--bg), var(--bg-2));
  overflow-x:hidden;
}
::selection{ background: rgba(93,121,255,.35); color:#fff }

/* Layout */
.main-content{ padding:24px; min-height:100vh }
@media (min-width: 980px){ .main-content{ margin-left: 288px } }

/* -------- Sidebar -------- */
.sidebar{
  position: fixed; top:20px; left:20px; bottom:20px; width:260px;
  background: var(--surface);
  border:1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-1);
  padding: 14px 10px; overflow: hidden;
  transition: left 0.3s ease;
  z-index: 10000;
}
.sidebar ul{ list-style:none; margin:6px 0 0; padding:6px; display:grid; gap:6px }
.sidebar a{
  position:relative;
  display:flex; align-items:center; gap:10px; padding:11px 12px 11px 18px;
  color:var(--text); text-decoration:none; border-radius:12px;
  border:1px solid transparent;
  transition: transform var(--speed), border-color var(--speed), background var(--speed), color var(--speed);
}
.sidebar a::before{
  content:""; position:absolute; left:8px; top:10px; bottom:10px; width:4px;
  background: transparent; border-radius:4px; transition: all var(--speed);
}
.sidebar a .icon{ font-size:18px; color: var(--muted) }
.sidebar a:hover{
  transform: translateY(-1px);
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
  border-color: var(--border-strong);
}
.sidebar a.active,
.sidebar a[aria-current="page"]{
  background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
  border-color: var(--border-strong);
  font-weight: 700;
}
.sidebar a.active::before,
.sidebar a[aria-current="page"]::before{
  background: var(--accent);
  width:6px;
}
.sidebar a#logout-link{
  color:#fff;
  background: linear-gradient(180deg, rgba(239,68,68,.96), rgba(239,68,68,.78));
  border-color: rgba(239,68,68,.84);
}

/* Pulsante hamburger */
.hamburger {
  display: none;
  flex-direction: column;
  gap: 5px;
  cursor: pointer;
}
.hamburger span {
  display: block;
  width: 24px;
  height: 3px;
  background: var(--text);
  border-radius: 2px;
  transition: all 0.3s ease;
}

/* Overlay mobile */
.sidebar-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  z-index: 9998;
  display: none;
}
.sidebar-overlay.show {
  display: block;
}

@media (max-width: 980px) {
  .hamburger {
    display: flex;
  }
  .sidebar {
    top: 0;
    left: -260px;
    height: 100%;
    border-radius: 0;
    padding-top: 60px;
    border-right: 1px solid var(--border);
  }
  .sidebar.open {
    left: 0;
  }
  .main-content {
    margin-left: 0;
    padding: 18px;
  }
}

/* Topbar */
.topbar{
  position: sticky; top: 20px; z-index:6; margin: 0 20px 18px;
  background: var(--surface);
  border:1px solid var(--border); border-radius: var(--radius-lg);
  box-shadow: var(--shadow-1);
  display:flex; align-items:center; gap:14px;
  padding: 14px 18px;
}
.topbar h1{ margin:0; font-weight:800; font-size: clamp(20px, 2.2vw, 30px) }
.right-topbar{ display:flex; align-items:center; gap:14px }

/* Theme switch */
.theme-switch{ --w:64px; --h:36px; position:relative; display:inline-flex; align-items:center; cursor:pointer }
.theme-switch .knob{
  width: var(--w); height: var(--h); border-radius:999px; border:1px solid var(--border);
  background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04));
  display:inline-grid; grid-template-columns: 1fr 1fr; align-items:center;
  padding:6px; gap:6px;
}
.theme-switch .icon{ font-size:14px; opacity:.7; }
.theme-switch .moon{ justify-self:start }
.theme-switch .sun{ justify-self:end }

/* Cards */
.cards{ display:grid; gap:18px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin: 0 20px }
.card{
  display:flex; gap:14px; align-items:flex-start;
  padding: 16px; border-radius: var(--radius-lg);
  background: var(--surface);
  border:1px solid var(--border);
}
.avatar{
  width:52px; height:52px; border-radius:14px; overflow:hidden; flex: 0 0 auto;
  background: linear-gradient(120deg, rgba(93,121,255,.45), rgba(33,177,255,.30));
  display:grid; place-items:center; font-weight:800; color:#0b0d12;
  border:1px solid var(--border-strong);
}
.avatar img{ width:100%; height:100%; object-fit:cover; display:block }
.card-info h3{ margin:0 0 6px 0; font-size:17px; font-weight:800 }
.card-info p{ margin:4px 0; color: var(--muted) }

  </style>
</head>
<body>

<div class="sidebar">
  <ul>
    <li><a href="#"><span class="icon">🤝</span>Matching</a></li>
    <li><a href="preferences.php"><span class="icon">⚙️</span>Preferenze</a></li>
    <li><a href="feedback.php"><span class="icon">📝</span>Feedback</a></li>
    <li><a href="test.php"><span class="icon">📊</span>Test</a></li>
    <li><a href="call.php"><span class="icom">☎️</span>Call</a></li>
    <li><a href="tutor.html"><span class="icon">✅</span>Tutor</a></li>
    <li><a href="logout.php" id="logout-link"><span class="icon">🚪</span>Logout</a></li>
  </ul>
</div>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="main-content">
  <div class="topbar">
    <div class="hamburger" id="hamburger-btn">
      <span></span><span></span><span></span>
    </div>
    <h1>Dashboard Utente</h1>
    <div class="right-topbar">
      <label class="theme-switch" title="Toggle Light/Dark">
        <input id="theme-toggle" type="checkbox" hidden>
        <span class="knob" aria-hidden="true">
          <span class="icon moon">☾</span>
          <span class="icon sun">☀</span>
        </span>
       
      </label>
    </div>
  </div>

  <div class="matching-section">
    <h2>Matching</h2>
<div class="cards">
<?php if (!empty($matches)): ?>
    <?php foreach ($matches as $match): 
        // Calcolo media recensioni per l'utente match
        $stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE reviewed_id = ?");
        $stmt->bind_param("i", $match['id']);
        $stmt->execute();
        $rating_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : '-';
        $total_reviews = $rating_data['total_reviews'] ?? 0;
    ?>
        <div class="card">
            <div class="avatar">
                <?php if ($match['user_image']): ?>
                    <img src="<?= htmlspecialchars($match['user_image']) ?>" alt="<?= htmlspecialchars($match['username']) ?>">
                <?php else: ?>
                    <?= initials($match['username']) ?>
                <?php endif; ?>
            </div>
            <div class="card-info">
                <h3>
                  <!-- modifica CON IP O DOMINIO -->
                    <a href="http://192.168.0.160/link4schooll-main44/link4schooll-main444/link4schooll-main/chat.php?with=<?= $match['id'] ?>" target="_blank" 
                       style="color:var(--accent); text-decoration:none;">
                        <?= htmlspecialchars($match['username']) ?>
                    </a>
                </h3>
                <p><strong></strong> <?= $avg_rating ?> ⭐ (<?= $total_reviews ?>)</p>
                <?php if (!empty($know) || !empty($want)): ?>
                    <p><strong>Conoscenze:</strong> 
                        <?= !empty($know) ? htmlspecialchars(implode(', ', array_keys($know))) : '-' ?>
                    </p>
                    <p><strong>Interessi:</strong> 
                        <?= !empty($want) ? htmlspecialchars(implode(', ', array_keys($want))) : '-' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nessun compagno trovato.</p>
<?php endif; ?>
</div>



    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const logoutLink = document.getElementById('logout-link');
  if (logoutLink) {
    logoutLink.addEventListener('click', function(e) {
      if (!confirm('Sei sicuro di voler uscire?')) e.preventDefault();
    });
  }

  const hamburgerBtn = document.getElementById('hamburger-btn');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebar-overlay');

  hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
  });
});
</script>

</body>
</html>

<script>
/* Theme toggle persistente (non modifica quello esistente, lo integra se assente) */
(function(){
  const toggle = document.getElementById('theme-toggle');
  if (!toggle) return;
  const root = document.documentElement;
  const saved = localStorage.getItem('theme');
  const prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
  const initial = saved || (prefersLight ? 'light' : 'dark');
  root.setAttribute('data-theme', initial);
  toggle.checked = (initial === 'light');

  toggle.addEventListener('change', () => {
    const mode = toggle.checked ? 'light' : 'dark';
    root.setAttribute('data-theme', mode);
    localStorage.setItem('theme', mode);
  });
})();

/* Interattività sidebar:
   - evidenzia link corrente (active/aria-current)
   - ripple click
   - supporto tastiera (freccia su/giù per scorrere voci) */
(function(){
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;
  const links = Array.from(sidebar.querySelectorAll('a[href]'));

  // Attivo in base al path corrente
  const path = location.pathname.split('/').pop();
  links.forEach(a => {
    const href = a.getAttribute('href');
    if (!href || href === '#' ) return;
    const file = href.split('/').pop();
    if (file === path) {
      a.classList.add('active');
      a.setAttribute('aria-current','page');
    }
  });

  // Ripple su click
  links.forEach(a => {
    a.addEventListener('click', (e) => {
      const rect = a.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const ripple = document.createElement('span');
      ripple.className = 'ripple';
      ripple.style.left = (x - 10) + 'px';
      ripple.style.top  = (y - 10) + 'px';
      ripple.style.width = ripple.style.height = '20px';
      a.appendChild(ripple);
      setTimeout(() => ripple.remove(), 520);
    });
  });

  // Navigazione tastiera
  sidebar.addEventListener('keydown', (e) => {
    if (!['ArrowDown','ArrowUp'].includes(e.key)) return;
    e.preventDefault();
    const focusables = links.filter(a => a.offsetParent !== null);
    const idx = focusables.indexOf(document.activeElement);
    let next = 0;
    if (e.key === 'ArrowDown') next = idx < 0 ? 0 : Math.min(idx+1, focusables.length-1);
    if (e.key === 'ArrowUp')   next = idx <= 0 ? 0 : idx-1;
    focusables[next].focus();
  });
})();
</script>

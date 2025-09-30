<?php
// Connessione al database
$mysqli = new mysqli("localhost", "root", "", "my_website");
if ($mysqli->connect_errno) { 
    die("Errore connessione: ".$mysqli->connect_error); 
}

// Funzione per ottenere i top 10 tutor
function getLeaders($mysqli) {
    $sql = "
        SELECT u.id, u.username, u.user_image, AVG(r.rating) AS avg_rating, COUNT(r.rating) AS total_reviews
        FROM users u
        JOIN reviews r ON u.id = r.reviewed_id
        GROUP BY u.id
        ORDER BY avg_rating DESC, total_reviews DESC
        LIMIT 10
    ";
    $result = $mysqli->query($sql);

    if (!$result) {
        die("Errore query SQL: " . $mysqli->error);
    }

    $leaders = [];
    while($row = $result->fetch_assoc()) {
        // Se non c'è immagine, usa fallback
        $row['avatar_url'] = !empty($row['user_image']) ? $row['user_image'] : 'uploads/profile_default.jpg';
        $leaders[] = $row;
    }
    return $leaders;
}

// Gestione richiesta AJAX
if(isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode(getLeaders($mysqli));
    exit;
}

// Recupera i leader iniziali
$leaders = getLeaders($mysqli);
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Classifica Tutor</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{--bg:#0a0d13;--bg-2:#0c1118;--surface:#0f1520;--surface-2:#121a26;--text:#eef3ff;--muted:#9aa6bf;--border:rgba(255,255,255,.10);--border-strong:rgba(255,255,255,.18);--accent:#5d79ff;--accent-2:#21b1ff;--radius:16px;--radius-lg:24px;--blur:14px;--speed:180ms;--shadow-1:0 20px 60px rgba(0,0,0,.45);--shadow-2:0 12px 32px rgba(0,0,0,.28);}
html[data-theme="light"]{--bg:#f6f8fd;--bg-2:#ffffff;--surface:#ffffff;--surface-2:#f2f5fb;--text:#0b1220;--muted:#4b5568;--border:rgba(10,20,40,.10);--border-strong:rgba(10,20,40,.16);--accent:#4d6bff;--accent-2:#0aaee8;--shadow-1:0 16px 48px rgba(10,20,40,.16);--shadow-2:0 10px 28px rgba(10,20,40,.12);}
html,body{height:100%;margin:0;color:var(--text);font:500 16px/1.55 Inter,sans-serif;background: linear-gradient(180deg,var(--bg),var(--bg-2));}*{box-sizing:border-box;}::selection{ background: rgba(93,121,255,.35); color:#fff }
.main-content{ padding:24px; min-height:100vh; }@media (min-width:980px){ .main-content{ margin-left:288px; } }
.sidebar{ position:fixed; top:20px; left:20px; bottom:20px; width:260px; background: var(--surface); border:1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-1); padding:14px 10px; overflow:hidden; transition:left 0.3s ease; z-index:10000; }
.sidebar ul{ list-style:none; margin:6px 0 0; padding:6px; display:grid; gap:6px; }
.sidebar a{ position:relative; display:flex; align-items:center; gap:10px; padding:11px 12px 11px 18px; color:var(--text); text-decoration:none; border-radius:12px; border:1px solid transparent; transition: transform var(--speed), border-color var(--speed), background var(--speed), color var(--speed);}
.sidebar a::before{ content:""; position:absolute; left:8px; top:10px; bottom:10px; width:4px; background: transparent; border-radius:4px; transition: all var(--speed);}
.sidebar a .icon{ font-size:18px; color: var(--muted);}
.sidebar a:hover{ transform: translateY(-1px); background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03)); border-color: var(--border-strong);}
.sidebar a.active::before{ background: var(--accent); width:6px;}
.sidebar a#logout-link{ color:#fff; background: linear-gradient(180deg, rgba(239,68,68,.96), rgba(239,68,68,.78)); border-color: rgba(239,68,68,.84); }
.hamburger{ display:none; flex-direction:column; gap:5px; cursor:pointer;}
.hamburger span{ display:block; width:24px; height:3px; background: var(--text); border-radius:2px; transition: all 0.3s ease; }
.sidebar-overlay{ position:fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); z-index:9998; display:none; }
.sidebar-overlay.show{ display:block; }
@media (max-width:980px){ .hamburger{ display:flex; } .sidebar{ top:0; left:-260px; height:100%; border-radius:0; padding-top:60px; border-right:1px solid var(--border); } .sidebar.open{ left:0; } .main-content{ margin-left:0; padding:18px; } }
.topbar{ position: sticky; top:20px; z-index:6; margin:0 20px 18px; background: var(--surface); border:1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-1); display:flex; align-items:center; gap:14px; padding:14px 18px;}
.topbar h1{ margin:0; font-weight:800; font-size: clamp(20px,2.2vw,30px);}
.right-topbar{ display:flex; align-items:center; gap:14px;}
.cards{ display:flex; flex-direction:column; gap:16px; margin:0 auto; max-width:600px; }
.card{ display:flex; gap:14px; align-items:center; padding:16px; border-radius: var(--radius-lg); background: var(--surface); border:1px solid var(--border); transition: transform var(--speed), box-shadow var(--speed); justify-content:flex-start; }
.card:hover{ transform: translateY(-2px); box-shadow: var(--shadow-2); }
.avatar{ width:52px; height:52px; border-radius:14px; overflow:hidden; background: linear-gradient(120deg, rgba(93,121,255,.45), rgba(33,177,255,.30)); display:grid; place-items:center; font-weight:800; color:var(--text); border:1px solid var(--border-strong);}
.avatar img{ width:100%; height:100%; object-fit:cover; display:block;}
.card-info{ flex:1; }
.card-info h3{ margin:0 0 6px 0; font-size:17px; font-weight:800; display:flex; align-items:center; gap:6px; }
.card-info p{ margin:0; color: var(--muted); font-size:0.9em;}
.podium { display:flex; justify-content:center; align-items:flex-end; gap:12px; margin-bottom:24px; }
.podium .card{ flex-direction:column; align-items:center; width:140px; }
.podium .avatar{ width:72px; height:72px; }
.podium .card-info h3{ justify-content:center; font-size:16px; text-align:center; }
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="profile.php"><span class="icon"></span>Profilo</a></li>
    <li><a href="dashboard.php"><span class="icon">🤝</span>Matching</a></li>
    <li><a href="preferences.php"><span class="icon">⚙️</span>Preferenze</a></li>
    <li><a href="feedback.php"><span class="icon">📝</span>Feedback</a></li>
    <li><a href="test.php"><span class="icon">📊</span>Test</a></li>
    <li><a href="call.php"><span class="icon">☎️</span>Call</a></li>
    <li><a href="leaderboard.php" class="active"><span class="icon">🏆</span>Classifica</a></li>
    <li><a href="logout.php" id="logout-link"><span class="icon">🚪</span>Logout</a></li>
  </ul>
</div>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="main-content">
  <div class="topbar">
    <div class="hamburger" id="hamburger-btn"><span></span><span></span><span></span></div>
    <h1>Classifica Tutor</h1>
    <div class="right-topbar">
      <label class="theme-switch" title="Toggle Light/Dark">
        <input id="theme-toggle" type="checkbox" hidden>
        <span class="knob" aria-hidden="true"><span class="icon moon">☾</span><span class="icon sun">☀</span></span>
      </label>
    </div>
  </div>
  <div class="podium"></div>
  <div class="cards"></div>
</div>

<script>
const hamburgerBtn = document.getElementById('hamburger-btn');
const sidebar = document.querySelector('.sidebar');
const overlay = document.getElementById('sidebar-overlay');
const themeToggle = document.getElementById('theme-toggle');

hamburgerBtn.addEventListener('click', ()=>{ sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
overlay.addEventListener('click', ()=>{ sidebar.classList.remove('open'); overlay.classList.remove('show'); });
themeToggle.addEventListener('change',()=>{
  if(themeToggle.checked){ document.documentElement.setAttribute('data-theme','light'); } 
  else{ document.documentElement.removeAttribute('data-theme'); }
});

const updateLeaderboard = async () => {
  try {
    const res = await fetch('?ajax=1');
    const leaders = await res.json();

    const podium = document.querySelector('.podium');
    podium.innerHTML = '';
    leaders.slice(0,3).forEach(tutor => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <div class="avatar"><img src="${tutor.avatar_url}" alt="${tutor.username}"></div>
        <div class="card-info">
          <h3>${tutor.username}</h3>
          <p>${parseFloat(tutor.avg_rating).toFixed(1)} ★ | ${tutor.total_reviews} recensioni</p>
        </div>
      `;
      podium.appendChild(card);
    });

    const cards = document.querySelector('.cards');
    cards.innerHTML = '';
    leaders.slice(3).forEach(tutor => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <div class="avatar"><img src="${tutor.avatar_url}" alt="${tutor.username}"></div>
        <div class="card-info">
          <h3>${tutor.username}</h3>
          <p>${parseFloat(tutor.avg_rating).toFixed(1)} ★ | ${tutor.total_reviews} recensioni</p>
        </div>
      `;
      cards.appendChild(card);
    });
  } catch(e) {
    console.error('Errore aggiornamento leaderboard', e);
  }
};

updateLeaderboard();
setInterval(updateLeaderboard, 10000);
</script>
</body>
</html>


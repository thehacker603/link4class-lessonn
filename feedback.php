<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Devi essere loggato per lasciare una recensione.");
}

$reviewer_id = $_SESSION['user_id'];
$success     = '';
$error       = '';

$RATE_LIMIT_SECONDS = 60;
$COMMENT_MAXLEN     = 1000;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_token)) {
        $error = "Sessione scaduta. Ricarica la pagina e riprova.";
    } else {
        $reviewed_id = (int)($_POST['reviewed_id'] ?? 0);
        $rating      = (int)($_POST['rating'] ?? 0);
        $comment     = trim($_POST['comment'] ?? '');

        if ($reviewer_id === $reviewed_id) {
            $error = "Non puoi recensire te stesso.";
        } elseif ($rating < 1 || $rating > 5) {
            $error = "Valutazione non valida.";
        } elseif (mb_strlen($comment) > $COMMENT_MAXLEN) {
            $error = "Commento troppo lungo (max {$COMMENT_MAXLEN} caratteri).";
        } else {
            $stmt = $conn->prepare("SELECT created_at FROM reviews WHERE reviewer_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("i", $reviewer_id);
            $stmt->execute();
            $last = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($last && !empty($last['created_at'])) {
                $seconds_since_last = time() - strtotime($last['created_at']);
                if ($seconds_since_last < $RATE_LIMIT_SECONDS) {
                    $wait = $RATE_LIMIT_SECONDS - $seconds_since_last;
                    $error = "Stai andando troppo veloce. Riprova tra {$wait} secondi.";
                }
            }

            if (!$error) {
                $stmt = $conn->prepare("INSERT INTO reviews (reviewer_id, reviewed_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $reviewer_id, $reviewed_id, $rating, $comment);
                $stmt->execute();
                $stmt->close();

                $success = "Recensione inviata con successo.";
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $csrf_token = $_SESSION['csrf_token'];
            }
        }
    }
}

// Fetch users for select
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

// Per filtrare recensioni in base all'utente scelto
$selected_id = (int)($_POST['reviewed_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recensioni</title>
<style>
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
body {
  margin:0; font-family:Inter, sans-serif; background: linear-gradient(180deg,var(--bg),var(--bg-2)); color:var(--text);
  min-height:100vh; display:flex;
}
.sidebar{
  position: fixed; top:20px; left:20px; bottom:20px; width:260px;
  background: var(--surface);
  border:1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-1);
  padding: 14px 10px;
  overflow: hidden;
  transition: transform var(--speed) ease;
  z-index: 1000;
}
.sidebar ul{ list-style:none; padding:0; margin:0; display:grid; gap:6px }
.sidebar a{
  display:flex; align-items:center; gap:10px; padding:11px 12px 11px 18px;
  color:var(--text); text-decoration:none; border-radius:12px;
  border:1px solid transparent;
  transition: transform var(--speed), border-color var(--speed), background var(--speed), color var(--speed);
}
.sidebar a.active{ background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04)); border-color: var(--border-strong); font-weight:700; }
.sidebar a.active::before{ content:""; position:absolute; left:8px; top:10px; bottom:10px; width:6px; background: var(--accent); border-radius:4px; }

.main-content{ flex:1; padding:24px; margin-left:280px; transition: margin-left var(--speed); }

/* Hamburger button */
.hamburger {
  display:none;
  position:fixed;
  top:20px;
  left:20px;

  border:1px solid var(--border);
  border-radius:12px;
  padding:10px;
  cursor:pointer;
  
}
.hamburger span {
  display:block;
  width:22px;
  height:3px;
  margin:4px;
  background:var(--text);
  border-radius:2px;
  transition:0.3s;
}

/* Overlay */
.overlay {
  display:none;
  position:fixed;
  inset:0;
  z-index:1100; /* sopra sidebar, sotto hamburger */
}
.overlay.show {
  display:block;
}

/* Mobile styles */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-110%);
  }
  .sidebar.open {
    transform: translateX(0);
  }
  .main-content {
    margin-left:0;
  }
  .hamburger {
    display:inline;
  }
}
 h1 {
  margin-top:0; margin-bottom:20px; font-size:28px; font-weight:800; margin-left:36px
 }
form {
  width:100%; max-width:500px; background: var(--surface-2); padding:20px; border-radius:var(--radius-lg);
  backdrop-filter: blur(var(--blur)); margin-bottom:40px; box-shadow: var(--shadow-2);
}
label {display:block; margin:10px 0 4px;}
select, textarea {width:100%; padding:10px; border-radius:12px; border:none; font-size:14px; margin-bottom:10px; background:rgba(255,255,255,0.05); color:var(--text);}
button {padding:10px 16px; border:none; border-radius:12px; background:var(--accent); color:#fff; font-weight:600; cursor:pointer; transition:0.3s;}
button:hover {background:var(--accent-2);}
.alert {padding:10px; border-radius:12px; margin-bottom:20px; font-weight:600;}
.alert--error {background:#e11d48;}
.alert--success {background:#16a34a;}

.tickets{display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:18px; margin-top:24px;}
.ticket{
  display:flex; gap:14px; align-items:flex-start;
  padding:16px; border-radius: var(--radius-lg);
  background: var(--surface);
  border:1px solid var(--border);
  box-shadow: var(--shadow-2);
}
.ticket .avatar{
  width:52px; height:52px; border-radius:14px; overflow:hidden; flex:0 0 auto;
  background: linear-gradient(120deg, rgba(93,121,255,.45), rgba(33,177,255,.30));
  display:grid; place-items:center; font-weight:800; color:#0b0d12;
  border:1px solid var(--border-strong);
}
.ticket-content h3{ margin:0 0 6px 0; font-size:17px; font-weight:800; }
.ticket-content p{ margin:4px 0; color:var(--muted); }
.ticket-content .rating{ color:#f5c518; margin-top:4px; }
</style>
</head>
<body>

<!-- Hamburger button -->
<div class="hamburger" id="hamburger">
  <span></span>
  <span></span>
  <span></span>
</div>

<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="dashboard.php"><span class="icon">🤝</span>Matching</a></li>
    <li><a href="preferences.php"><span class="icon">⚙️</span>Preferenze</a></li>
    <li><a href="feedback.php"><span class="icon">📝</span>Feedback</a></li>
    <li><a href="test.php"><span class="icon">📊</span>Test</a></li>
    <li><a href="call.php"><span class="icon">☎️</span>Call</a></li>
    <li><a href="leaderboard.php"><span class="icon">🏆</span>Classifica</a></li>
    <li><a href="logout.php" id="logout-link"><span class="icon">🚪</span>Logout</a></li>
  </ul>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<div class="main-content">
  <h1>Lascia una recensione</h1>

<?php if ($error): ?>
    <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
    <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <label for="reviewed_id">Seleziona utente</label>
    <select name="reviewed_id" id="reviewed_id" required>
        <option value="">-- Seleziona --</option>
        <?php while($user = $users->fetch_assoc()): ?>
            <option value="<?= $user['id'] ?>" <?= $selected_id === (int)$user['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['username']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Valutazione</label>
    <select name="rating" required>
        <option value="5">5 ⭐</option>
        <option value="4">4 ⭐</option>
        <option value="3">3 ⭐</option>
        <option value="2">2 ⭐</option>
        <option value="1">1 ⭐</option>
    </select>

    <label for="comment">Commento</label>
    <textarea name="comment" id="comment" rows="4" maxlength="<?= (int)$COMMENT_MAXLEN ?>"></textarea>

    <button type="submit">Invia recensione</button>
</form>

<div class="tickets">
<?php
if ($selected_id > 0) {
    $stmt = $conn->prepare("
        SELECT r.rating, r.comment, u.username 
        FROM reviews r 
        JOIN users u ON r.reviewed_id=u.id 
        WHERE r.reviewed_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $selected_id);
} else {
    $stmt = $conn->prepare("
        SELECT r.rating, r.comment, u.username 
        FROM reviews r 
        JOIN users u ON r.reviewed_id=u.id 
        WHERE 1=0
    ");
}
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

while($row = $reviews->fetch_assoc()):
?>
    <div class="ticket">
        <div class="avatar"><?= strtoupper(substr($row['username'],0,1)) ?></div>
        <div class="ticket-content">
            <h3><?= htmlspecialchars($row['username']) ?></h3>
            <div class="rating"><?= str_repeat("⭐", (int)$row['rating']) ?></div>
            <p><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
        </div>
    </div>
<?php endwhile; ?>
</div>
</div>

<script>
const hamburger = document.getElementById('hamburger');
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');

hamburger.addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
});

overlay.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('show');
});
</script>
</body>
</html>

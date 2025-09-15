<?php
// select_class.php
session_start();
require 'config.php';

// Se già scelta, vai direttamente a preferences.php
if (isset($_SESSION['school_class'], $_SESSION['school_track'])) {
    header('Location: preferences.php');
    exit;
}

// Gestione POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prendi i valori dal form
    $class = intval($_POST['school_class']);
    $track = $_POST['school_track'];

    // Salvali in sessione
    $_SESSION['school_class'] = $class;
    $_SESSION['school_track'] = $track;

    // Aggiorna anche nella tabella users
    $stmt = $conn->prepare("
      UPDATE users
         SET school_class = ?, school_track = ?
       WHERE id = ?
    ");
    $stmt->bind_param('isi', $class, $track, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Vai a preferences
    header('Location: preferences.php');
    exit;
}

// Classi e indirizzi disponibili
$classes = [1, 2, 3, 4, 5];
$tracks = [
    'Istituto Tecnico – Informatica',
    'Istituto Tecnico – Meccatronica',
    'Istituto Tecnico – Elettrotecnica',
    'Istituto Tecnico – Automazione',
    'Liceo Scientifico opzione Scienze Applicate',
    'Istituto Professionale Socio-Sanitario'
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Seleziona Classe e Indirizzo</title>
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
      --shadow-1:0 20px 60px rgba(0,0,0,.45);
      --shadow-2:0 12px 32px rgba(0,0,0,.28);
    }

    body {
      margin:0;
      font-family:Inter, sans-serif;
      background: linear-gradient(180deg,var(--bg),var(--bg-2));
      color:var(--text);
      min-height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      padding:20px;
    }

    h1 {
      font-size:26px;
      font-weight:800;
      margin-bottom:24px;
      text-align:center;
    }

    form {
      background:var(--surface-2);
      padding:28px 24px;
      border-radius:var(--radius-lg);
      box-shadow:var(--shadow-2);
      backdrop-filter:blur(var(--blur));
      width:100%;
      max-width:420px;
    }

    label {
      font-weight:600;
      font-size:14px;
      margin-bottom:6px;
      display:block;
      color:var(--muted);
    }

    select {
      width:100%;
      padding:12px;
      margin-bottom:18px;
      border-radius:12px;
      border:1px solid var(--border);
      background:rgba(255,255,255,0.05);
      color:var(--text);
      font-size:15px;
      cursor:pointer;
    }

    option {
      background:var(--surface-2);
      color:var(--text);
    }

    button {
      width:100%;
      padding:12px 16px;
      border:none;
      border-radius:12px;
      background:var(--accent);
      color:#fff;
      font-weight:600;
      cursor:pointer;
      font-size:15px;
      transition:0.3s;
      margin-top:10px;
    }

    button:hover {
      background:var(--accent-2);
    }
  </style>
</head>
<body>
  <form method="POST" action="">
    <h1>In quale classe e indirizzo sei?</h1>

    <label for="school_class">Classe</label>
    <select name="school_class" id="school_class" required>
      <option value="">-- Seleziona classe --</option>
      <?php foreach ($classes as $c): ?>
        <option value="<?= $c ?>"><?= $c ?>ª</option>
      <?php endforeach; ?>
    </select>

    <label for="school_track">Indirizzo</label>
    <select name="school_track" id="school_track" required>
      <option value="">-- Seleziona indirizzo --</option>
      <?php foreach ($tracks as $t): ?>
        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Avanti</button>
  </form>
</body>
</html>

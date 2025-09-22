<?php
// preferences.php
session_start();
require 'config.php';

// Se non hai già scelto classe/indirizzo, torna a select_class.php
if (!isset($_SESSION['school_class'], $_SESSION['school_track'])) {
    header('Location: select_class.php');
    exit;
}

// Funzione per normalizzare i nomi degli indirizzi
function normalize_track($str) {
    $str = mb_strtolower(trim($str), 'UTF-8');       // tutto minuscolo
    $str = str_replace(['–','—'], '-', $str);        // trattini strani → trattino normale
    $str = preg_replace('/\s+/', ' ', $str);         // spazi multipli → singolo spazio
    return $str;
}

// Normalizza valori dalla sessione
$school_class = (int) $_SESSION['school_class'];
$school_track = normalize_track($_SESSION['school_track']);

// Mappa indirizzo → (classe → materie)
$subjects_map = [
    'Istituto Tecnico - Informatica' => [
        1 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        2 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        3 => ['italiano', 'storia', 'matematica', 'informatica','inglese', 'telecomunicazioni', 'sistemi e reti','tecnologie informatiche'],
        4 => ['italiano', 'storia', 'matematica', 'informatica','inglese', 'telecomunicazioni', 'sistemi e reti','tecnologie informatiche'],
        5 => ['italiano', 'storia', 'matematica', 'informatica','inglese', 'gestione progetto', 'sistemi e reti','tecnologie informatiche'],
    ],
    'Istituto Tecnico - Meccatronica' => [
        1 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        2 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        3 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'sistemi ed automazione', 'disegno tecnico'],
        4 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'sistemi ed automazione', 'disegno tecnico'],
        5 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'sistemi ed automazione', 'disegno tecnico'],
    ],
    'Istituto Tecnico - Elettrotecnica' => [
        1 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        2 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        3 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
        4 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
        5 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
    ],
    'Istituto Tecnico - Automazione' => [
        1 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        2 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'geografia', 'scienze','disegno tecnico','chimica','fisica'],
        3 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
        4 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
        5 => ['italiano', 'storia', 'inglese', 'matematica', 'elettrotecnica', 'sistemi automatici', 'sistemi elettrici'],
    ],
    'Liceo Scienze Applicate' => [
        1 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'scienze naturali','arte','chimica','fisica'],
        2 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'scienze naturali','arte','chimica','fisica'],
        3 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'scienze naturali','chimica','fisica','filosofia'],
        4 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'scienze naturali','chimica','fisica','filosofia'],
        5 => ['italiano', 'storia', 'inglese', 'matematica', 'informatica', 'scienze naturali','chimica','fisica','filosofia'],
    ],
    'Istituto Socio-Sanitario' => [
        1 => ['italiano','storia','inglese','matematica','informatica','geografia','scienze umane','spagnolo','metodologie operative'],
        2 => ['italiano','storia','inglese','matematica','informatica','geografia','scienze umane','spagnolo','metodologie operative'],
        3 => ['italiano','storia','inglese','matematica','spagnolo','scienze umane','diritto','igiene','psicologia','metodologie operative'],
        4 => ['italiano','storia','inglese','matematica','spagnolo','scienze umane','diritto','igiene','psicologia','metodologie operative'],
        5 => ['italiano','storia','inglese','matematica','spagnolo','scienze umane','diritto','igiene','psicologia','metodologie operative'],
    ],
];

// Costruisci una mappa normalizzata
$normalized_map = [];
foreach ($subjects_map as $track => $classes) {
    $normalized_map[normalize_track($track)] = $classes;
}

// Prendi le materie (se non trova, cade nel fallback)
$subjects = $normalized_map[$school_track][$school_class] ?? ['italiano','storia','inglese','matematica','spagnolo','scienze umane','diritto','igiene','psicologia','metodologie operative','informatica','geografia',];

// Carica preferenze esistenti
$stmt = $conn->prepare("
    SELECT subject, type, description
    FROM user_preferences
    WHERE user_id = ?
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$existing = ['know'=>[], 'want'=>[]];
while ($row = $res->fetch_assoc()) {
    $existing[$row['type']][$row['subject']] = $row['description'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Preferenze Utente</title>
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
      padding:40px 20px;
    }
    h1 { margin:0 0 24px 0; font-size:28px; font-weight:800; text-align:center; }
    h2 { margin:0 0 16px 0; font-size:20px; font-weight:700; }
    .step-box {
      width:100%; max-width:600px; background:var(--surface-2);
      padding:24px; border-radius:var(--radius-lg);
      box-shadow:var(--shadow-2); backdrop-filter:blur(var(--blur));
      margin-bottom:30px;
    }
    ul { list-style:none; padding:0; margin:0; }
    li { margin-bottom:18px; }
    label { display:flex; align-items:center; gap:10px; font-weight:600; cursor:pointer; }
    input[type="checkbox"] { width:18px; height:18px; accent-color:var(--accent); cursor:pointer; }
    textarea {
      width:100%; min-height:80px; margin-top:8px; padding:12px;
      border-radius:12px; border:1px solid var(--border);
      background:rgba(255,255,255,0.05); color:var(--text);
      font-size:14px; resize:vertical;
    }
    .btn {
      padding:12px 20px; border:none; border-radius:12px;
      background:var(--accent); color:#fff; font-weight:600;
      cursor:pointer; transition:0.3s; margin-top:10px;
    }
    .btn:hover { background:var(--accent-2); }
    .hidden { display:none; }
  </style>
</head>
<body>
  <div style="width:100%; max-width:600px;">
    <h1>Preferenze per <?= htmlspecialchars("{$school_class}ª {$_SESSION['school_track']}") ?></h1>

    <!-- Step 1: cosa vuoi imparare -->
    <div id="step-want" class="step-box">
      <h2>Materie che vuoi imparare</h2>
      <ul>
        <?php foreach ($subjects as $sub): ?>
          <li>
            <label>
              <input type="checkbox"
                    class="want-cb"
                    data-sub="<?= htmlspecialchars($sub) ?>"
                    <?= isset($existing['want'][$sub]) ? 'checked' : '' ?>>
              <?= htmlspecialchars($sub) ?>
            </label>
            <div class="<?= isset($existing['want'][$sub]) ? '' : 'hidden' ?>">
              <textarea
                placeholder="Descrivi cosa vuoi imparare in <?= htmlspecialchars($sub) ?>..."
                data-type="want"
                data-sub="<?= htmlspecialchars($sub) ?>"><?= htmlspecialchars($existing['want'][$sub] ?? '') ?></textarea>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <button id="to-know" class="btn">Prossimo</button>
    </div>

    <!-- Step 2: cosa conosci -->
    <form id="form-prefs" method="POST" action="save_preferences.php" class="step-box hidden">
      <div id="step-know">
        <h2>Materie che conosci</h2>
        <ul>
          <?php foreach ($subjects as $sub): ?>
            <li>
              <label>
                <input type="checkbox"
                      class="know-cb"
                      data-sub="<?= htmlspecialchars($sub) ?>"
                      <?= isset($existing['know'][$sub]) ? 'checked' : '' ?>>
                <?= htmlspecialchars($sub) ?>
              </label>
              <div class="<?= isset($existing['know'][$sub]) ? '' : 'hidden' ?>">
                <textarea
                  placeholder="Descrivi cosa sai fare in <?= htmlspecialchars($sub) ?>..."
                  data-type="know"
                  data-sub="<?= htmlspecialchars($sub) ?>"><?= htmlspecialchars($existing['know'][$sub] ?? '') ?></textarea>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <input type="hidden" name="prefs_json" id="prefs_json">
        <button type="submit" class="btn">Salva Preferenze</button>
      </div>
    </form>
  </div>

  <script>
    document.querySelectorAll('.want-cb').forEach(cb =>
      cb.addEventListener('change', e =>
        e.target.closest('li').querySelector('div').classList.toggle('hidden', !e.target.checked)
      )
    );
    document.querySelectorAll('.know-cb').forEach(cb =>
      cb.addEventListener('change', e =>
        e.target.closest('li').querySelector('div').classList.toggle('hidden', !e.target.checked)
      )
    );
    document.getElementById('to-know').onclick = () => {
      document.getElementById('step-want').classList.add('hidden');
      document.getElementById('form-prefs').classList.remove('hidden');
    };
    document.getElementById('form-prefs').onsubmit = () => {
      const prefs = [];
      document.querySelectorAll('textarea').forEach(ta => {
        const li = ta.closest('li'),
              cb = li.querySelector('input[type=checkbox]');
        if (!cb.checked) return;
        prefs.push({
          subject: cb.dataset.sub,
          type: ta.dataset.type,
          description: ta.value.trim()
        });
      });
      document.getElementById('prefs_json').value = JSON.stringify(prefs);
    };
  </script>
</body>
</html>

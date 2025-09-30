<?php
// --- connessione al DB ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "my_website";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

session_start();
$logged_user_id = $_SESSION['user_id'] ?? 1; // utente loggato
$user_id = $_GET['user_id'] ?? $logged_user_id; // profilo visitato (via ?user_id=)

// --- aggiornamento profilo (solo se è il proprio) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $logged_user_id == $user_id) {
    $bio = $_POST['bio'] ?? "";
    $hashtags = $_POST['hashtags'] ?? "";
    $user_image = null;

    if (!empty($_FILES['user_image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES["user_image"]["name"]);
        if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file)) {
            $user_image = $target_file;
        }
    }

    if ($user_image) {
        $stmt = $conn->prepare("UPDATE users SET bio=?, hashtags=?, user_image=? WHERE id=?");
        $stmt->bind_param("sssi", $bio, $hashtags, $user_image, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET bio=?, hashtags=? WHERE id=?");
        $stmt->bind_param("ssi", $bio, $hashtags, $user_id);
    }
    $stmt->execute();
    header("Location: profile.php?user_id=" . $user_id);
    exit;
}

// --- info utente ---
$stmt = $conn->prepare("SELECT username, bio, hashtags, user_image FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("Utente non trovato.");
$user = $result->fetch_assoc();
$stmt->close();

// fallback foto
$user_image = !empty($user['user_image']) ? $user['user_image'] : "uploads/profile_default.jpg";

// --- media recensioni ---
$stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE reviewed_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rating_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : '-';
$total_reviews = $rating_data['total_reviews'] ?? 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profilo Utente</title>
<style>
:root {
    --bg: linear-gradient(135deg, #0f172a, #1e293b);
    --glass-bg: rgba(255, 255, 255, 0.08);
    --glass-border: rgba(255, 255, 255, 0.2);
    --glass-blur: 18px;
    --accent: #5d79ff;
    --accent-2: #21b1ff;
    --text: #f8fafc;
    --muted: #94a3b8;
    --radius: 20px;
    --shadow-soft: 0 12px 48px rgba(0, 0, 0, 0.6);
    --speed: 220ms;
}
body {
    margin: 0; font-family: "Inter", sans-serif;
    background: var(--bg); color: var(--text);
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    padding: 2rem;
}
.profile-card {
    width: 100%; max-width: 550px;
    padding: 2rem; border-radius: var(--radius);
    background: var(--glass-bg); backdrop-filter: blur(var(--glass-blur)) saturate(1.2);
    border: 1px solid var(--glass-border); box-shadow: var(--shadow-soft);
    display: flex; flex-direction: column; align-items: center; gap: 1.2rem;
}
.profile-pic {
    width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
    border: 3px solid var(--accent-2); box-shadow: 0 4px 20px rgba(33,177,255,0.4);
}
.username { font-size: 1.6rem; font-weight: 800; }
.bio { text-align: center; font-size: 1rem; color: var(--muted); max-width: 400px; }
.hashtags { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
.hashtags span { background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border);
    border-radius: 12px; padding: .35rem .8rem; font-size: .9rem; font-weight: 600; color: var(--accent-2); }
.rating { display: flex; align-items: center; gap: .4rem; font-size: 1.1rem; font-weight: 600; }
.stars { color: gold; font-size: 1.3rem; }

/* Form */
.edit-form { margin-top: 2rem; width: 100%; }
.edit-form form { display: flex; flex-direction: column; gap: 1rem; }
.edit-form input, .edit-form textarea { padding: 10px; border-radius: 12px; border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.05); color: var(--text); resize: vertical; }
.edit-form button { padding: 12px; border-radius: 12px; border: none; font-weight: 700; color: #fff;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); cursor: pointer; transition: transform var(--speed), opacity var(--speed); }
.edit-form button:hover { transform: scale(1.05); opacity: 0.9; }
</style>
</head>
<body>
<div class="profile-card">
    <img src="<?= htmlspecialchars($user_image) ?>" alt="Foto profilo" class="profile-pic">
    <h2 class="username"><?= htmlspecialchars($user['username']) ?></h2>
    <div class="rating"><?= $avg_rating ?> ⭐ (<?= $total_reviews ?>)</div>
    <p class="bio"><?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : "Nessuna bio disponibile." ?></p>
    <div class="hashtags">
    <?php
        if (!empty($user['hashtags'])) {
            foreach (explode(",", $user['hashtags']) as $tag) {
                echo "<span>" . htmlspecialchars(trim($tag)) . "</span>";
            }
        } else {
            echo "<span>#nessunhashtag</span>";
        }
    ?>
    </div>

    <?php if ($logged_user_id == $user_id): ?>
    <div class="edit-form">
        <h3>Modifica Profilo</h3>
        <form method="POST" enctype="multipart/form-data">
            <label>Bio:</label>
            <textarea name="bio" rows="3"><?= htmlspecialchars($user['bio']) ?></textarea>
            <label>Hashtag (separati da virgola):</label>
            <input type="text" name="hashtags" value="<?= htmlspecialchars($user['hashtags']) ?>">
            <label>Foto profilo:</label>
            <input type="file" name="user_image" accept="image/*">
            <button type="submit">Salva Modifiche</button>
        </form>
    </div>
    <?php endif; ?>
    <!-- ...dopo la sezione hashtags... -->
<div class="chat-btn">
    <a href="http://localhost/link4schooll-main44/chat.php?user_id=<?= $user_id ?>" 
       target="_blank" 
       style="display:inline-block; margin-top:1rem; padding:12px 20px; 
              border-radius:12px; background: linear-gradient(135deg, var(--accent), var(--accent-2));
              color:#fff; font-weight:700; text-decoration:none; transition: transform var(--speed);">
        💬 Chat con <?= htmlspecialchars($user['username']) ?>
    </a>
</div>

</div>
</body>
</html>

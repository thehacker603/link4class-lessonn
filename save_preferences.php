<?php
session_start();
require 'config.php';

// Solo POST e utente loggato
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: preferences.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Decodifica JSON inviato da preferences.php
$prefs = json_decode($_POST['prefs_json'] ?? '[]', true);
if (!is_array($prefs)) {
    // JSON malformato
    header('Location: preferences.php');
    exit;
}

// Elimina vecchie preferenze
$stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->close();

// Prepara l'INSERT
$ins = $conn->prepare("
    INSERT INTO user_preferences (user_id, subject, type, description)
    VALUES (?, ?, ?, ?)
");

// Inserisci ogni preferenza dall'array
foreach ($prefs as $p) {
    // Assicurati che i campi esistano
    if (!isset($p['subject'], $p['type'], $p['description'])) {
        continue;
    }
    $subject     = $p['subject'];
    $type        = $p['type'];         // 'know' o 'want'
    $description = $p['description'];
    $ins->bind_param('isss', $user_id, $subject, $type, $description);
    $ins->execute();
}

// Redirect alla dashboard
header('Location: dashboard.php');
exit;

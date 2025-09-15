<?php
$test_generato = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prendo i dati dal form
    $materia = $_POST['materia'] ?? '';
    $argomento = $_POST['argomento'] ?? '';
    $tipo_test = $_POST['tipo_test'] ?? 'domande aperte';
    $numero_domande = intval($_POST['numero_domande'] ?? 5);
    $livello_difficolta = $_POST['livello_difficolta'] ?? 'medio';

    // API NLP Cloud
    $apiKey = '7';
    $baseUrl = 'https://api.nlpcloud.io/v1/bart-large-cnn/summarizatio';

    $prompt = "Genera un test di {$numero_domande} domande su {$argomento} in {$materia} con livello di difficoltà {$livello_difficolta} e tipo {$tipo_test}.";

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Token ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'input' => $prompt,
        'parameters' => [
            'temperature' => 0.7,
            'max_length' => 1500
        ]
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['generated_text'])) {
            $test_generato = $result['generated_text'];
        } else {
            $test_generato = "Errore nella generazione del test: " . $response;
        }
    } else {
        $test_generato = "Errore nella richiesta API.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Generatore di Test con NLP Cloud</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 300px; padding: 5px; margin-top: 5px; }
        textarea { width: 100%; height: 400px; margin-top: 20px; padding: 10px; }
        button { margin-top: 20px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Generatore di Test Educativi</h1>
    <form method="POST">
        <label>Materia:
            <input type="text" name="materia" required>
        </label>
        <label>Argomento:
            <input type="text" name="argomento" required>
        </label>
        <label>Tipo di test:
            <select name="tipo_test">
                <option value="domande aperte">Domande aperte</option>
                <option value="scelta multipla">Scelta multipla</option>
                <option value="vero/falso">Vero/Falso</option>
            </select>
        </label>
        <label>Numero di domande:
            <input type="number" name="numero_domande" value="5" min="1" max="50">
        </label>
        <label>Livello di difficoltà:
            <select name="livello_difficolta">
                <option value="facile">Facile</option>
                <option value="medio" selected>Medio</option>
                <option value="difficile">Difficile</option>
            </select>
        </label>
        <button type="submit">Genera Test</button>
    </form>

    <?php if ($test_generato): ?>
        <h2>Test Generato:</h2>
        <textarea readonly><?php echo htmlspecialchars($test_generato); ?></textarea>
    <?php endif; ?>
</body>
</html>

<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: select_class.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'config.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: select_class.php");
            exit();
        } else {
            $error = "Password errata.";
        }
    } else {
        $error = "Utente non trovato.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Link4Class - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #0d0d12;
      color: white;
      overflow-x: hidden;
    }
    .container {
      max-width: 1200px;
      margin: auto;
      padding: 60px 20px;
    }
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
    }
    nav a {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-weight: 500;
    }
    .hero {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 30px;
      margin-top: 80px;
    }
    .hero h1 {
      font-size: 60px;
      line-height: 1.1;
    }
    .highlight {
      color: #3d8bff;
    }
    .hero p {
      max-width: 600px;
      font-size: 18px;
      color: #ccc;
    }
    .btn,
    button[type="submit"] {
      padding: 14px 28px;
      background-color: #3d8bff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      color: white;
      transition: 0.3s;
    }
    .btn:hover,
    button[type="submit"]:hover {
      background-color: #2d6fd1;
    }
    .cta-buttons {
      margin-top: 30px;
      display: flex;
      gap: 20px;
    }

    .form-container {
      max-width: 400px;
      margin: 6rem auto;
      background: #1a1a1f;
      padding: 3rem 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      position: relative;
    }

    #theme-toggle {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: transparent;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: white;
      transition: 0.3s;
    }

    .form-container h1 {
      text-align: center;
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 1rem;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .input-group {
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    input[type="text"],
    input[type="password"] {
      padding: 1rem;
      border: 1px solid #3d8bff;
      background: transparent;
      border-radius: 8px;
      font-size: 1rem;
      color: white;
      transition: 0.3s;
    }

    input:focus {
      border-color: #3d8bff;
      outline: none;
    }

    .error {
      margin-top: 1rem;
      color: red;
      text-align: center;
    }

    .cyber-footer {
      text-align: center;
      margin-top: 2rem;
    }

    .nav-btn {
      display: inline-block;
      margin: 0 1rem;
      color: #3d8bff;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    .nav-btn:hover {
      color: #fff;
    }

    footer {
      margin-top: 2rem;
      text-align: center;
      padding: 0;
    }
  </style>
</head>
<body>

  <div class="form-container">


    <h1>Login</h1>
    <form method="POST">
      <div class="input-group">
        <label for="login-username">Username</label>
        <input type="text" id="login-username" name="username" placeholder="Inserisci il tuo username" required>
      </div>
      <div class="input-group" style="position: relative;">
        <label for="login-password">Password</label>
        <input type="password" id="login-password" name="password" placeholder="Inserisci la password" required>
        <button type="button" id="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer;">👁</button>
      </div>
      <button type="submit">Accedi</button>
      <?php if(isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
      <?php endif; ?>
    </form>

    <footer class="cyber-footer">
      <a href="send_reset.php">password dimenticata ?</a>
      <a href="index.php" class="nav-btn">Home</a>
      <a href="register.php" class="nav-btn">Registrati</a>
    </footer>
  </div>

  <script>
    const toggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    const passwordField = document.getElementById('login-password');
    const togglePasswordBtn = document.getElementById('toggle-password');

    toggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark-theme');
    });

    togglePasswordBtn.addEventListener('click', () => {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
    });
  </script>
</body>
</html>

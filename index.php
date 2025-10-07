<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="link4school.jpg" type="image/png">
  <title>Link4Class Lessons</title>
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
    .btn {
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
    .btn:hover {
      background-color: #2d6fd1;
    }
    .cta-buttons {
      margin-top: 30px;
      display: flex;
      gap: 20px;
    }
  </style>
</head>
<body>

  <div class="container">
    <nav>
      <div style="font-weight: bold; font-size: 20px;">Link4Class</div>
      <div>
        <a href="login.php" class="btn">Accedi</a>
      </div>
    </nav>

    <section class="hero">
      <h1>La piattaforma definitiva per lo <span class="highlight">scambio di videolezioni</span></h1>
      <p>
        Link4Class Lessons ti permette di connetterti con altri studenti per condividere conoscenze in modo equo.
        Tu insegni ciò che sai, loro ti insegnano ciò che ti serve. Tutto in modo organizzato, affidabile e gratuito.
      </p>
      <div class="cta-buttons">
        <a href="register.php" class="btn">Registrati ora</a>
        <a href="login.php" class="btn" style="background-color: transparent; border: 1px solid #3d8bff;">Accedi</a>
      </div>
    </section>
  </div>

</body>
</html>
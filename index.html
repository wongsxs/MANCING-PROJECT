<?php
session_start();
require 'config.php'; // koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT id, password FROM players WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $hashedPassword);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {
      $_SESSION['user_id'] = $id;
      $_SESSION['username'] = $username;
      header("Location: game.php");
      exit;
    } else {
      $error = "❌ Password salah!";
    }
  } else {
    $error = "❌ Username tidak ditemukan!";
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>🎣 Login Pemancing</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Press Start 2P', cursive;
      background: url('https://i.imgur.com/fk2CiaL.jpg') no-repeat center center fixed;
      background-size: cover;
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    form {
      background: rgba(0, 0, 50, 0.8);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px #00f0ff;
      width: 360px;
      text-align: center;
    }
    h2 {
      margin-bottom: 20px;
      color: #00f0ff;
    }
    input {
      display: block;
      margin: 15px auto;
      padding: 12px;
      width: 90%;
      border: none;
      border-radius: 8px;
      font-family: monospace;
    }
    button {
      padding: 12px;
      background: #28a745;
      color: white;
      border: none;
      cursor: pointer;
      width: 100%;
      border-radius: 8px;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #218838;
    }
    .error {
      color: #ff4d4d;
      margin-top: 15px;
      font-size: 0.8em;
      font-family: monospace;
    }
    .logo {
      font-size: 2em;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <form method="POST">
    <div class="logo">🎣 FisherBoy</div>
    <h2>LOGIN</h2>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">🎮 LOGIN</button>
    <div style="margin-top: 15px;">
  <a href="register.php" style="color:#00f0ff; font-size:0.75em; text-decoration:underline;">Belum punya akun? Daftar di sini</a>
</div>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </form>
</body>
</html>

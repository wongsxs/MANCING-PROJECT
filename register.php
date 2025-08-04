<?php
require 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password_plain = $_POST['password'] ?? '';

  if (empty($username) || empty($password_plain)) {
    $message = "❌ Username dan password wajib diisi.";
  } else {
    $stmt = $conn->prepare("SELECT id FROM players WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $message = "❌ Username sudah digunakan!";
    } else {
      $password = password_hash($password_plain, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO players (username, password) VALUES (?, ?)");
      $stmt->bind_param("ss", $username, $password);
      $stmt->execute();
      $player_id = $stmt->insert_id;

      $stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah) VALUES (?, 'ember', 'kecil', 1)");
$stmt->bind_param("i", $player_id);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah) VALUES (?, 'kail', 'biasa', 1)");
$stmt->bind_param("i", $player_id);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah) VALUES (?, 'umpan', 'biasa', 1)");
$stmt->bind_param("i", $player_id);
$stmt->execute();


      $message = "✅ Berhasil daftar! Silakan login.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>🎣 Daftar Akun</title>
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
      background: #007bff;
      color: white;
      border: none;
      cursor: pointer;
      width: 100%;
      border-radius: 8px;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #0056b3;
    }
    .message {
      margin-top: 15px;
      font-size: 0.8em;
      font-family: monospace;
    }
    .success {
      color: #00ff99;
    }
    .error {
      color: #ff4d4d;
    }
    .logo {
      font-size: 2em;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <form method="POST">
    <div class="logo">🎣 Mancing Legend</div>
    <h2>Daftar Akun</h2>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">📝 Daftar</button>
    <div style="margin-top: 15px;">
  <a href="index.php" style="color:#00f0ff; font-size:0.75em; text-decoration:underline;">Sudah punya akun? Login di sini</a>
</div>

    <?php if (!empty($message)): ?>
      <div class="message <?= str_starts_with($message, '✅') ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
  </form>
</body>
</html>

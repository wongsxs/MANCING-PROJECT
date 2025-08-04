<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Ambil saldo pemain
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

// Ambil inventory
$stmt = $conn->prepare("SELECT tipe, level, jumlah FROM inventories WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$inventory = [];
while ($row = $result->fetch_assoc()) {
  $key = $row['tipe'] . '-' . $row['level'];
  $inventory[$key] = $row['jumlah'];
}
$stmt->close();

$emberKecil = $inventory['ember-kecil'] ?? 0;
$emberBesar = $inventory['ember-besar'] ?? 0;
$kapasitasTotal = ($emberKecil * 5) + ($emberBesar * 10); 


// Hitung jumlah ikan berdasarkan ukuran
$stmt = $conn->prepare("SELECT ukuran, COUNT(*) as total FROM ikan WHERE player_id = ? GROUP BY ukuran");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$jumlahIkan = ['kecil' => 0, 'besar' => 0, 'super besar' => 0];
while ($row = $result->fetch_assoc()) {
  $jumlahIkan[$row['ukuran']] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Game Memancing</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
#status-box {
  position: fixed;
  top: 60%;
  left: 20px;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.95);
  border-radius: 10px;
  padding: 15px;
  font-size: 14px;
  color: #333;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  z-index: 10;
}


    #status-box h3 {
      margin-top: 0;
      font-size: 16px;
    }

    #status-box ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    #status-box li {
      margin: 4px 0;
    }

  #saldo-badge {
  position: fixed;
  top: 50%;
  left: 150px;
  transform: translateY(-50%);
  background: linear-gradient(to right, #ffce00, #ffaa00);
  color: #000;
  padding: 10px 16px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 14px;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  z-index: 11;
}

    .action-button {
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      transition: transform 0.1s ease, background 0.2s ease;
    }

    .action-button:hover {
      transform: scale(1.05);
    }

    .shop-btn { background: #28a745; }
    .inv-btn { background: #2196F3; }
    .logout-btn { background: #dc3545; }
    .fish-btn { background: #ff9800; color: white; margin-top: 20px; }

    #controls {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      z-index: 10;
    }

    #top-right-controls {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 12px;
      z-index: 12;
    }
  </style>
</head>
<body>
    <audio id="bg" src="mp3/sound.mp3" autoplay loop></audio>

    <audio id="bg-music" src="mp3/sosund.mp3" loop></audio>
  <!-- Status Pemain -->
  <div id="status-box">
  <h3>Status Pemain</h3>
  <ul>
    <li>👤 <?= htmlspecialchars($_SESSION['username']) ?></li>
    <li>🪣 Ember: <?= $emberKecil ?> kecil, <?= $emberBesar ?> besar</li>
    <li>📦 Kapasitas Maksimum: <?= $kapasitasTotal ?> ikan</li>
    <li>🐟 Ikan dimiliki:</li>
    <ul>
      <li>🔹 Kecil: <?= $jumlahIkan['kecil'] ?> ekor</li>
      <li>🔸 Besar: <?= $jumlahIkan['besar'] ?> ekor</li>
      <li>🌟 Super Besar: <?= $jumlahIkan['super besar'] ?> ekor</li>
    </ul>
  </ul>
</div>


  <!-- Saldo -->
  <div id="saldo-badge">💰 $<?= number_format($saldo) ?></div>

  <!-- Langit -->
  <div id="sky">
    <div id="sun"></div>
    <img src="img/gunung.png" id="mountain" alt="Gunung">
    <div class="cloud" style="left: 20%;"></div>
    <div class="cloud" style="left: 60%;"></div>
  </div>

  <!-- Danau -->
  <div id="lake">
    <div class="wave"></div>
    <div id="person">
      <img src="img/person.gif" alt="Pemancing" />
    </div>
    <div id="rod-line"></div>
    <div id="boat"></div>
  </div>

  <!-- Kontrol Atas Kanan -->
  <div id="top-right-controls">
    <a href="inv.php">
      <button class="action-button inv-btn">📦 </button>
    </a>
    <a href="shop.php">
      <button class="action-button shop-btn">🛒 </button>
    </a>
    <form action="logout.php" method="POST" style="margin: 0;">
      <button type="submit" class="action-button logout-btn">🚪 </button>
    </form>

    <a href="trade_center.php">
  <button class="action-button" style="background: #ffc107;">🔁 Trade</button>
</a>

  </div>

  <!-- Tombol Pancing -->
  <div id="controls">
    <button id="fishButton" class="action-button fish-btn" onclick="startFishing()">🎣 FISH</button>
    <p id="catch-result"></p>
  </div>

  <script src="script.js"></script>
  <script>
document.addEventListener("DOMContentLoaded", function () {
  const bgMusic = document.getElementById("bg-music");

  // Autoplay saat halaman dibuka jika user berinteraksi
  function enableMusic() {
    bgMusic.volume = 0.3;
    bgMusic.play().catch(() => {});
    document.removeEventListener("click", enableMusic);
  }

  // Tunggu klik pertama user agar aman dari blokir browser
  document.addEventListener("click", enableMusic);
});
</script>

</body>
</html>

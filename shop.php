<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Ambil saldo pemain dari database
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Toko Pancing 🎣</title>
  <style>
    body {
      margin: 0;
      padding: 20px;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #d0f0ff, #ffffff);
      color: #333;
    }

    #saldo {
      position: fixed;
      top: 20px;
      left: 20px;
      background: #ffffffcc;
      padding: 10px 15px;
      border-radius: 10px;
      font-weight: bold;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 40px;
      color: #2c3e50;
    }

    #shop {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
    }

    .item {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      width: 260px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
      transition: transform 0.2s;
    }

    .item:hover {
      transform: scale(1.05);
    }

    .item p {
      font-size: 16px;
      margin-bottom: 15px;
    }

    button {
      padding: 10px 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: bold;
      transition: background 0.2s;
    }

    .item button {
      background-color: #007bff;
      color: white;
    }

    .item button:hover {
      background-color: #0056b3;
    }

    #message {
      text-align: center;
      margin-top: 30px;
      font-size: 18px;
      font-weight: bold;
    }

    .top-buttons {
      position: fixed;
      top: 20px;
      right: 20px;
    }

    .top-buttons a button {
      background-color: #28a745;
      color: white;
      margin-left: 10px;
    }

    .top-buttons a button:hover {
      background-color: #218838;
    }

    @media (max-width: 768px) {
      #shop {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
</head>
<body>

  <!-- Saldo Player -->
  <div id="saldo">💰 Saldo: $<?= htmlspecialchars($saldo) ?></div>

  <!-- Tombol kembali ke game -->
  <div class="top-buttons">
    <a href="game.php">
      <button>🎮 Kembali ke Game</button>
    </a>
  </div>

  <h2>🛍️ Toko Pancing Ceria 🎣</h2>

  <div id="shop">
    <div class="item">
      <p>🪣 <strong>Ember Besar</strong><br>Menambah kapasitas penampungan ikan.</p>
      <button onclick="buyItem('ember', 'besar', 100)">Beli ($100)</button>
    </div>

    <div class="item">
      <p>🎣 <strong>Kail Pro</strong><br>Meningkatkan peluang dapat ikan besar.</p>
      <button onclick="buyItem('kail', 'pro', 150)">Beli ($150)</button>
    </div>

    <div class="item">
      <p>🪱 <strong>Umpan Super</strong><br>Mempercepat waktu menangkap ikan.</p>
      <button onclick="buyItem('umpan', 'super', 80)">Beli ($80)</button>
    </div>

     <div class="item">
      <p>🪱 <strong>Umpan Biasa</strong><br>Standar</p>
      <button onclick="buyItem('umpan', 'biasa', 10)">Beli ($10)</button>
    </div>

    <div class="item">
      <p>🪱 <strong>Umpan Pro</strong><br>Lumayan cepat</p>
      <button onclick="buyItem('umpan', 'pro', 30)">Beli ($30)</button>
    </div>
  </div>

  <div id="message"></div>

  <script>
    function buyItem(tipe, level, harga) {
      fetch('buy_item.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ tipe, level })
      })
      .then(res => res.json())
      .then(data => {
        const msgBox = document.getElementById("message");
        msgBox.innerText = data.message;
        msgBox.style.color = data.message.includes("✅") ? "green" : "red";

        // Optional: reload untuk update saldo
        if (data.message.includes("✅")) {
          setTimeout(() => location.reload(), 1000);
        }
      })
      .catch(err => {
        console.error("Gagal beli item:", err);
        const msgBox = document.getElementById("message");
        msgBox.innerText = "Terjadi kesalahan saat membeli item.";
        msgBox.style.color = "red";
      });
    }
  </script>

</body>
</html>

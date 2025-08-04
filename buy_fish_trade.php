<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo "❌ Belum login";
  exit;
}

$user_id = $_SESSION['user_id'];
$trade_id = $_POST['trade_id'] ?? null;

if (!$trade_id) {
  http_response_code(400);
  echo "❌ ID trade tidak valid.";
  exit;
}

// Ambil trade
$stmt = $conn->prepare("SELECT * FROM fish_trades WHERE id = ? AND status = 'open'");
$stmt->bind_param("i", $trade_id);
$stmt->execute();
$result = $stmt->get_result();
$trade = $result->fetch_assoc();

if (!$trade) {
  echo "❌ Trade tidak tersedia.";
  exit;
}

if ($trade['seller_id'] == $user_id) {
  echo "❌ Tidak bisa beli ikan sendiri.";
  exit;
}

// Cek saldo
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

if ($saldo < $trade['price']) {
  echo "❌ Saldo tidak cukup.";
  exit;
}

// Transfer saldo
$stmt = $conn->prepare("UPDATE players SET saldo = saldo - ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $user_id);
$stmt->execute();

$stmt = $conn->prepare("UPDATE players SET saldo = saldo + ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $trade['seller_id']);
$stmt->execute();

// Alihkan kepemilikan ikan
$stmt = $conn->prepare("UPDATE ikan SET player_id = ? WHERE id = ?");
$stmt->bind_param("ii", $user_id, $trade['fish_id']);
$stmt->execute();

// Update status trade
$stmt = $conn->prepare("UPDATE fish_trades SET status = 'completed', buyer_id = ?, traded_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $user_id, $trade_id);
$stmt->execute();

echo "✅ Pembelian ikan berhasil!";
?>

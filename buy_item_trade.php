<?php
session_start();
header('Content-Type: application/json');
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['message' => '❌ Silakan login terlebih dahulu.']);
  exit;
}

$user_id = $_SESSION['user_id'];
$trade_id = $_POST['trade_id'] ?? null;

if (!$trade_id) {
  echo json_encode(['message' => '❌ Data tidak lengkap.']);
  exit;
}

// Ambil data trade
$stmt = $conn->prepare("SELECT * FROM item_trades WHERE id = ?");
$stmt->bind_param("i", $trade_id);
$stmt->execute();
$result = $stmt->get_result();
$trade = $result->fetch_assoc();
$stmt->close();

if (!$trade) {
  echo json_encode(['message' => '❌ Trade tidak ditemukan.']);
  exit;
}

if ($trade['seller_id'] == $user_id) {
  echo json_encode(['message' => '❌ Tidak bisa beli item sendiri.']);
  exit;
}

// Ambil saldo pembeli
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

if ($saldo < $trade['price']) {
  echo json_encode(['message' => '❌ Saldo tidak cukup.']);
  exit;
}

// Kurangi saldo pembeli
$stmt = $conn->prepare("UPDATE players SET saldo = saldo - ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $user_id);
$stmt->execute();

// Tambahkan saldo ke penjual
$stmt = $conn->prepare("UPDATE players SET saldo = saldo + ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $trade['seller_id']);
$stmt->execute();

// Tambahkan item ke inventory pembeli
$stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah)
  VALUES (?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah)");
$stmt->bind_param("issi", $user_id, $trade['tipe'], $trade['level'], $trade['jumlah']);
$stmt->execute();

// Hapus trade
$stmt = $conn->prepare("DELETE FROM item_trades WHERE id = ?");
$stmt->bind_param("i", $trade_id);
$stmt->execute();

echo json_encode(['message' => '✅ Berhasil membeli item dari pemain lain!']);
?>

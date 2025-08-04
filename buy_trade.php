<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  die("❌ Belum login");
}

$user_id = $_SESSION['user_id'];
$trade_id = $_POST['trade_id'] ?? null;

if (!$trade_id) {
  die("❌ ID trade tidak valid.");
}

// Ambil detail trade
$stmt = $conn->prepare("SELECT * FROM item_trades WHERE id = ? AND status = 'open'");
$stmt->bind_param("i", $trade_id);
$stmt->execute();
$result = $stmt->get_result();
$trade = $result->fetch_assoc();

if (!$trade) {
  die("❌ Trade tidak ditemukan atau sudah ditutup.");
}

if ($trade['seller_id'] == $user_id) {
  die("❌ Tidak bisa beli trade milik sendiri.");
}

// Cek saldo pembeli
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

if ($saldo < $trade['price']) {
  die("❌ Saldo tidak cukup.");
}

// Kurangi saldo pembeli
$stmt = $conn->prepare("UPDATE players SET saldo = saldo - ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $user_id);
$stmt->execute();

// Tambahkan saldo ke penjual
$stmt = $conn->prepare("UPDATE players SET saldo = saldo + ? WHERE id = ?");
$stmt->bind_param("ii", $trade['price'], $trade['seller_id']);
$stmt->execute();

// Tambah item ke pembeli
// Cek apakah item sudah ada
$stmt = $conn->prepare("SELECT id, jumlah FROM inventories WHERE user_id = ? AND tipe = ? AND level = ?");
$stmt->bind_param("iss", $user_id, $trade['tipe'], $trade['level']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  $new_jumlah = $row['jumlah'] + $trade['jumlah'];
  $stmt = $conn->prepare("UPDATE inventories SET jumlah = ? WHERE id = ?");
  $stmt->bind_param("ii", $new_jumlah, $row['id']);
  $stmt->execute();
} else {
  $stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("issi", $user_id, $trade['tipe'], $trade['level'], $trade['jumlah']);
  $stmt->execute();
}

// Tandai trade selesai
$stmt = $conn->prepare("UPDATE item_trades SET status = 'completed', buyer_id = ?, traded_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $user_id, $trade_id);
$stmt->execute();

echo "✅ Pembelian berhasil!";
?>

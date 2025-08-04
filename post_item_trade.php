<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo "Belum login.";
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$tipe = $data['tipe'] ?? '';
$level = $data['level'] ?? '';
$jumlah = (int)($data['jumlah'] ?? 0);
$price = (int)($data['price'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($tipe && $level && $jumlah > 0 && $price >= 0) {
  // Cek apakah player punya item
  $stmt = $conn->prepare("SELECT jumlah FROM inventories WHERE user_id = ? AND tipe = ? AND level = ?");
  $stmt->bind_param("iss", $user_id, $tipe, $level);
  $stmt->execute();
  $stmt->bind_result($jumlah_stok);
  $stmt->fetch();
  $stmt->close();

  if ($jumlah_stok >= $jumlah) {
    // Kurangi inventory
    $stmt = $conn->prepare("UPDATE inventories SET jumlah = jumlah - ? WHERE user_id = ? AND tipe = ? AND level = ?");
    $stmt->bind_param("iiss", $jumlah, $user_id, $tipe, $level);
    $stmt->execute();

    // Tambah ke item_trades
    $stmt = $conn->prepare("INSERT INTO item_trades (seller_id, tipe, level, jumlah, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $tipe, $level, $jumlah, $price);
    $stmt->execute();

    echo "✅ Item berhasil dijual.";
  } else {
    echo "❌ Jumlah item tidak mencukupi.";
  }
} else {
  echo "❌ Data tidak lengkap.";
}
?>

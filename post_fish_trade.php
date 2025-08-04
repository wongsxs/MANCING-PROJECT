<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo "Belum login.";
  exit;
}

$fish_id = $_POST['fish_id'] ?? null;
$price = (int)($_POST['price'] ?? 0);
$user_id = $_SESSION['user_id'];

// Pastikan ikan milik user dan belum ditradingkan
$stmt = $conn->prepare("SELECT id FROM ikan WHERE id = ? AND player_id = ?");
$stmt->bind_param("ii", $fish_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0 && $price >= 0) {
  $stmt = $conn->prepare("INSERT INTO fish_trades (fish_id, seller_id, price) VALUES (?, ?, ?)");
  $stmt->bind_param("iii", $fish_id, $user_id, $price);
  $stmt->execute();

  echo "✅ Ikan berhasil dijual.";
} else {
  echo "❌ Ikan tidak valid atau bukan milik kamu.";
}
?>

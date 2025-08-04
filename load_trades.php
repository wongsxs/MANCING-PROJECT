<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["error" => "Belum login"]);
  exit;
}

$user_id = $_SESSION['user_id'];
$all_trades = [];

// === Ambil item trades ===
$stmt = $conn->prepare("SELECT t.id, p.username AS penjual, t.tipe, t.level, t.jumlah, t.price 
                        FROM item_trades t 
                        JOIN players p ON t.seller_id = p.id
                        WHERE t.status = 'open' AND t.seller_id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $row['jenis'] = 'item';
  $all_trades[] = $row;
}

// === Ambil fish trades ===
$stmt = $conn->prepare("SELECT f.id, p.username AS penjual, i.jenis, i.ukuran, f.price 
                        FROM fish_trades f 
                        JOIN ikan i ON f.fish_id = i.id
                        JOIN players p ON f.seller_id = p.id
                        WHERE f.status = 'open' AND f.seller_id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $row['jenis'] = 'ikan';
  $all_trades[] = $row;
}

header("Content-Type: application/json");
echo json_encode($all_trades);
?>

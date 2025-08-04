<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["error" => "Belum login"]);
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT tipe, level, jumlah FROM inventories WHERE user_id = ? AND jumlah > 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
  $key = $row['tipe'] . '-' . $row['level'];
  $items[$key] = $row;
}

echo json_encode($items);
?>

<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['jumlah' => 0]);
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
  SELECT SUM(jumlah) as total 
  FROM inventories 
  WHERE user_id = ? AND tipe = 'umpan'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

echo json_encode(['jumlah' => (int)$total]);


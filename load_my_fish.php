<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["error" => "Belum login"]);
  exit;
}

$user_id = $_SESSION['user_id'];

// Ambil ikan milik pemain yang belum ditradingkan
$stmt = $conn->prepare("SELECT i.id, i.jenis, i.ukuran
                        FROM ikan i
                        LEFT JOIN fish_trades f ON i.id = f.fish_id AND f.status = 'open'
                        WHERE i.player_id = ? AND f.id IS NULL");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$ikan = [];
while ($row = $result->fetch_assoc()) {
  $ikan[] = $row;
}

echo json_encode($ikan);
?>

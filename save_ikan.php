<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(["status" => "error", "message" => "❌ Belum login."]);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$jenis = $input['jenis'] ?? null;
$ukuran = $input['ukuran'] ?? null;
$player_id = $_SESSION['user_id'];

if (!$jenis || !$ukuran) {
  echo json_encode(["status" => "error", "message" => "❌ Data tidak lengkap."]);
  exit;
}

// 🔻 Cari umpan terbaik yang tersedia (prioritas: super > pro > biasa)
$stmt = $conn->prepare("
  SELECT level FROM inventories
  WHERE user_id = ? AND tipe = 'umpan' AND jumlah > 0
  ORDER BY 
    CASE level
      WHEN 'super' THEN 3
      WHEN 'pro' THEN 2
      WHEN 'biasa' THEN 1
      ELSE 0
    END DESC
  LIMIT 1
");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$umpan = $result->fetch_assoc();
$stmt->close();


// Hitung kapasitas maksimal ember
$stmt = $conn->prepare("SELECT tipe, level, jumlah FROM inventories WHERE user_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

$kapasitas = 0;
while ($item = $result->fetch_assoc()) {
  if ($item['tipe'] === 'ember') {
    if ($item['level'] === 'kecil') $kapasitas += $item['jumlah'] * 5;
    elseif ($item['level'] === 'besar') $kapasitas += $item['jumlah'] * 10;
  }
}
$stmt->close();

// Hitung jumlah ikan yang dimiliki
$stmt = $conn->prepare("SELECT COUNT(*) FROM ikan WHERE player_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$stmt->bind_result($totalIkan);
$stmt->fetch();
$stmt->close();

if ($totalIkan >= $kapasitas) {
  echo json_encode(["status" => "error", "message" => "❌ Ember penuh! Tidak bisa simpan ikan."]);
  exit;
}

if (!$umpan) {
  echo json_encode(["status" => "error", "message" => "❌ Tidak ada umpan untuk memancing."]);
  exit;
}



// 🔻 Kurangi 1 dari umpan yang dipilih
$stmt = $conn->prepare("
  UPDATE inventories
  SET jumlah = jumlah - 1
  WHERE user_id = ? AND tipe = 'umpan' AND level = ?
");
$stmt->bind_param("is", $player_id, $umpan['level']);
$stmt->execute();
$stmt->close();



// 🔻 Simpan ikan ke database
$stmt = $conn->prepare("INSERT INTO ikan (player_id, jenis, ukuran) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $player_id, $jenis, $ukuran);
$stmt->execute();
$stmt->close();

echo json_encode(["status" => "success", "message" => "✅ Kamu mendapatkan $jenis ($ukuran)! 1 umpan telah digunakan."]);

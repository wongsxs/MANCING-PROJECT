<?php
session_start();
header('Content-Type: application/json');
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['message' => '❌ Silakan login terlebih dahulu.']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (!isset($data['tipe'], $data['level'])) {
  echo json_encode(['message' => '❌ Data tidak lengkap.']);
  exit;
}

$tipe = $data['tipe'];
$level = $data['level'];
$user_id = $_SESSION['user_id'];

// Harga item berdasarkan tipe dan level
$hargaItem = [
  'ember_besar' => 100,
  'kail_pro' => 150,
  'umpan_super' => 80,
  'umpan_biasa' => 10,
  'umpan_pro' => 30
];

$key = "{$tipe}_{$level}";
$harga = $hargaItem[$key] ?? null;

if ($harga === null) {
  echo json_encode(['message' => '❌ Item tidak valid.']);
  exit;
}

// Ambil saldo player
$stmt = $conn->prepare("SELECT saldo FROM players WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

if ($saldo === null) {
  echo json_encode(['message' => '❌ Gagal mengambil saldo.']);
  exit;
}

// Cek saldo cukup
if ($saldo < $harga) {
  echo json_encode(['message' => '❌ Saldo tidak cukup.']);
  exit;
}

// Kurangi saldo
$newSaldo = $saldo - $harga;
$stmt = $conn->prepare("UPDATE players SET saldo = ? WHERE id = ?");
$stmt->bind_param("ii", $newSaldo, $user_id);
$stmt->execute();
$stmt->close();

// Simpan ke inventori
$stmt = $conn->prepare("SELECT jumlah FROM inventories WHERE user_id = ? AND tipe = ? AND level = ?");
$stmt->bind_param("iss", $user_id, $tipe, $level);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  $stmt->bind_result($jumlah);
  $stmt->fetch();
  $stmt->close();

  $newJumlah = $jumlah + 1;
  $stmt = $conn->prepare("UPDATE inventories SET jumlah = ? WHERE user_id = ? AND tipe = ? AND level = ?");
  $stmt->bind_param("iiss", $newJumlah, $user_id, $tipe, $level);
  $stmt->execute();
  $stmt->close();
} else {
  $stmt->close();

  $stmt = $conn->prepare("INSERT INTO inventories (user_id, tipe, level, jumlah) VALUES (?, ?, ?, 1)");
  $stmt->bind_param("iss", $user_id, $tipe, $level);
  $stmt->execute();
  $stmt->close();
}

echo json_encode(['message' => '✅ Berhasil membeli item!']);

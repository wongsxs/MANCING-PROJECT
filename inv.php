<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Get inventory items
$stmt = $conn->prepare("SELECT tipe, level, jumlah FROM inventories WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>🎒 Your Inventory</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
      padding: 20px;
      background: linear-gradient(to bottom, #b3ecff, #e6faff);
      color: #333;
    }

    h2 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 20px;
      color: #0077b6;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    th {
      background: #00b4d8;
      color: white;
      padding: 12px;
      font-size: 16px;
    }

    td {
      padding: 10px;
      border-bottom: 1px solid #e0e0e0;
      text-align: center;
      font-size: 14px;
    }

    tr:hover {
      background-color: #f1fcff;
    }

    .back-btn {
      display: inline-block;
      margin-top: 20px;
      background: linear-gradient(to right, #2196f3, #21cbf3);
      color: white;
      padding: 10px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    }

    .back-btn:hover {
      background: linear-gradient(to right, #1a73e8, #00bcd4);
    }
  </style>
</head>
<body>

<h2>🎣 Your Inventory</h2>

<table>
  <thead>
    <tr>
      <th>Type</th>
      <th>Level</th>
      <th>Quantity</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['tipe']) ?></td>
        <td><?= htmlspecialchars($row['level']) ?></td>
        <td><?= $row['jumlah'] ?></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<a class="back-btn" href="game.php">⛵ Back to Game</a>

</body>
</html>

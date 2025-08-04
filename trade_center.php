<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>🎣 Pusat Perdagangan Pemain</title>
<style>
  * {
    box-sizing: border-box;
  }

  html, body {
    margin: 0;
    padding: 0;
    height: 100vh;
    overflow: hidden;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: linear-gradient(to bottom, #b3ecff, #e6faff);
    color: #333;
  }

  h2 {
    text-align: center;
    font-size: 28px;
    margin: 10px 0;
    color: #0077b6;
  }

  .container {
    display: flex;
    gap: 20px;
    height: calc(100vh - 80px);
    padding: 20px;
    overflow: hidden;
  }

  .left, .right {
    flex: 1;
    overflow-y: auto;
    min-width: 360px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }

  th {
    background: #00b4d8;
    color: white;
    padding: 12px;
  }

  td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: center;
  }

  .buy-btn {
    background: linear-gradient(to right, #38b000, #70e000);
    color: white;
    border: none;
    border-radius: 5px;
    padding: 6px 12px;
    cursor: pointer;
    transition: transform 0.1s;
  }

  .buy-btn:hover {
    transform: scale(1.05);
  }

 .form-box {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 16px;
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.1);
  max-width: 320px;      /* batasi lebarnya */
  font-size: 14px;
}

.form-row {
  display: flex;
  gap: 20px;
  justify-content: flex-start;
  flex-wrap: wrap; /* agar tetap responsif di layar kecil */
}

  .form-box h3 {
    margin-top: 0;
    color: #023e8a;
    margin-bottom: 10px;
  }

  label {
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
  }

  select, input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-top: 4px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }

  button[type="submit"] {
    margin-top: 12px;
    width: 100%;
    background: linear-gradient(to right, #ff9100, #ffd60a);
    color: black;
    font-weight: bold;
    border: none;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.1s ease;
  }

  button[type="submit"]:hover {
    transform: scale(1.03);
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

<body>
<body>
<h2>🎣 Pusat Perdagangan</h2>
<div class="container">
  <div class="left">
    <table id="tradeTable">
      <thead>
        <tr>
          <th>Penjual</th>
          <th>Jenis</th>
          <th>Detail</th>
          <th>Harga</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="right">
   <div class="form-row">
  <div class="form-box">
    <h3>🐟 Jual Ikan</h3>
    <form id="fishForm">
      <label>Ikan:
        <select name="fish_id" id="fishSelect" required></select>
      </label>
      <label>Harga ($):
        <input type="number" name="price" min="0" required>
      </label>
      <button type="submit">💸 Jual Ikan</button>
    </form>
  </div>

  <div class="form-box">
    <h3>📦 Jual Item</h3>
    <form id="itemForm">
      <label>Item:
        <select name="item_key" id="itemSelect" required></select>
      </label>
      <label>Jumlah:
        <input type="number" name="jumlah" min="1" required>
      </label>
      <label>Harga ($):
        <input type="number" name="price" min="0" required>
      </label>
      <button type="submit">💸 Jual Item</button>
    </form>
  </div>
</div>

<a class="back-btn" href="game.php">⛵ Back to Game</a>


<script>
function loadTrades() {
  fetch("load_trades.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tradeTable tbody");
      tbody.innerHTML = "";
      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = "<tr><td colspan='5'>Tidak ada trade tersedia.</td></tr>";
        return;
      }

      data.forEach(trade => {
        const row = document.createElement("tr");
        let detail = trade.jenis === "item"
          ? `${trade.tipe} (${trade.level}) x${trade.jumlah}`
          : `${trade.jenis} (${trade.ukuran})`;

        row.innerHTML = `
          <td>${trade.penjual}</td>
          <td>${trade.jenis}</td>
          <td>${detail}</td>
          <td>$${trade.price}</td>
          <td><button class='buy-btn' onclick="buyTrade('${trade.jenis}', ${trade.id})">Beli</button></td>
        `;
        tbody.appendChild(row);
      });
    });
}

function buyTrade(jenis, id) {
  const form = new FormData();
  form.append("trade_id", id);
  fetch(jenis === 'ikan' ? 'buy_fish_trade.php' : 'buy_item_trade.php', {
    method: "POST",
    body: form
  })
  .then(res => res.text())
  .then(alert)
  .then(() => {
    loadTrades();
    loadMyInventory();
    loadMyFish();
  });
}

function loadMyInventory() {
  fetch("load_my_inventory.php")
    .then(res => res.json())
    .then(items => {
      const select = document.getElementById("itemSelect");
      select.innerHTML = "";
      for (let key in items) {
        const i = items[key];
        const opt = document.createElement("option");
        opt.value = key;
        opt.textContent = `${i.tipe} (${i.level}) x${i.jumlah}`;
        select.appendChild(opt);
      }
    });
}

function loadMyFish() {
  fetch("load_my_fish.php")
    .then(res => res.json())
    .then(fish => {
      const select = document.getElementById("fishSelect");
      select.innerHTML = "";
      fish.forEach(i => {
        const opt = document.createElement("option");
        opt.value = i.id;
        opt.textContent = `${i.jenis} (${i.ukuran})`;
        select.appendChild(opt);
      });
    });
}

document.getElementById("itemForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const [tipe, level] = document.getElementById("itemSelect").value.split("-");
  const jumlah = this.jumlah.value;
  const price = this.price.value;
  fetch("post_item_trade.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ tipe, level, jumlah, price })
  })
  .then(res => res.text())
  .then(alert)
  .then(() => {
    loadTrades();
    loadMyInventory();
  });
});

document.getElementById("fishForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const form = new FormData(this);
  fetch("post_fish_trade.php", {
    method: "POST",
    body: form
  })
  .then(res => res.text())
  .then(alert)
  .then(() => {
    loadTrades();
    loadMyFish();
  });
});

loadTrades();
loadMyInventory();
loadMyFish();
</script>

</body>
</html>

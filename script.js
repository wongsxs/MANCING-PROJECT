const fishEmojis = ["🐟", "🐠", "🐡", "🦈", "🐬", "🦑", "🦀", "🦐", "🐙", "🦞", "🐉", "🧜‍♂️"];
const fishNames = [
  "Ikan Mujair", "Ikan Nila", "Ikan Lele", "Ikan Tuna",
  "Ikan Kakap", "Ikan Hiu", "Ikan Salmon", "Ikan Mas",
  "Ikan Cupang", "Ikan Gurame", "Ikan Dewa", "Ikan Legendaris"
];

const rodLine = document.getElementById("rod-line");
const resultText = document.getElementById("catch-result");
const fishButton = document.getElementById("fishButton");
const bgMusic = document.getElementById("bg-music");

let isFishing = false;

function randomInt(max) {
  return Math.floor(Math.random() * max);
}

function getUkuranIkan(wait) {
  if (wait >= 300000) return "super besar"; // >5 menit
  if (wait >= 180000) return "besar";       // >3 menit
  return "kecil";
}

function startFishing() {
  if (isFishing) return;

  fetch("cek_umpan.php")
    .then(res => res.json())
    .then(data => {
      if (!data || data.jumlah === undefined || data.jumlah <= 0) {
        resultText.innerHTML = "❌ Tidak bisa memancing. Umpan kamu 0!";
        return;
      }

      isFishing = true;
      fishButton.disabled = true;
      rodLine.style.height = "120px";
      resultText.innerHTML = "🎣 Menunggu ikan menggigit...";

      // Backsound mulai
      bgMusic.volume = 0.3;
      bgMusic.play().catch(() => {});

      const waitTime = 1000 + Math.floor(Math.random() * 300000); // 1s–5m

      setTimeout(() => {
        let i = randomInt(fishNames.length);
        const ukuran = getUkuranIkan(waitTime);

        // Tambah peluang dapat ikan langka kalau nunggu lama
        if (waitTime >= 180000 && Math.random() < 0.4) {
          i = fishNames.length - 1; // Ikan Legendaris
        }

        const jenis = fishNames[i];
        const emoji = fishEmojis[i];

        saveIkan(jenis, ukuran, (status, message) => {
          if (status === "success") {
            resultText.innerHTML = `✅ Kamu mendapat ${emoji} <strong>${jenis}</strong> (${ukuran})!<br><em>${message}</em>`;
          } else {
            resultText.innerHTML = `❌ Gagal memancing: ${message}`;
          }

          rodLine.style.height = "0px";
          isFishing = false;
          fishButton.disabled = false;
        });
      }, waitTime);
    })
    .catch(err => {
      console.error("Gagal cek umpan:", err);
      resultText.innerHTML = "❌ Terjadi kesalahan saat mengecek umpan.";
      isFishing = false;
      fishButton.disabled = false;
    });
}

function saveIkan(jenis, ukuran, callback) {
  fetch("save_ikan.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ jenis, ukuran })
  })
    .then(res => res.json())
    .then(data => {
      callback(data.status, data.message);
    })
    .catch(err => {
      console.error("Gagal menyimpan ikan:", err);
      callback("error", "❌ Gagal menyimpan ke server.");
    });
}

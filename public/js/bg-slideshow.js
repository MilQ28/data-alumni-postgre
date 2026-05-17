/**
 * bg-slideshow.js
 * Script slideshow background otomatis untuk Portal Alumni SMK Telkom Lampung
 *
 * Cara kerja:
 * - Script ini mengganti background halaman secara otomatis setiap beberapa detik
 * - Efek transisi fade (crossfade) agar perpindahan gambar terasa halus
 * - Bisa dipakai di halaman login, register, dashboard admin, dan dashboard user
 *
 * CARA KUSTOMISASI UNTUK PEMULA:
 * 1. Tambah/hapus foto di array "images" di bawah
 * 2. Ubah kecepatan ganti foto di "intervalMs" (dalam milidetik, 1000 = 1 detik)
 * 3. Ubah durasi animasi fade di "fadeDuration" (dalam milidetik)
 */

(function () {
  // ============================================================
  // KONFIGURASI SLIDESHOW
  // Ubah nilai di sini sesuai kebutuhan
  // ============================================================

  // Daftar foto background. Tambah path foto baru di sini.
  var images = [
    "/assets/bg-sekolah.jpg",   // Foto 1: Gedung sekolah
    "/assets/bg-sekolah-2.jpg", // Foto 2: Lab komputer
    "/assets/bg-sekolah-3.jpg", // Foto 3: Wisuda
    "/assets/bg-sekolah-4.jpg", // Foto 4: Aerial kampus
  ];

  // Berapa lama setiap foto ditampilkan (dalam milidetik)
  // 5000 = 5 detik, 8000 = 8 detik
  var intervalMs = 6000;

  // Durasi animasi crossfade antar foto (dalam milidetik)
  // Disarankan: 1000-2000 ms
  var fadeDuration = 1200;

  // ============================================================
  // LOGIKA SLIDESHOW (Tidak perlu diubah)
  // ============================================================

  var currentIndex = 0;

  // Buat 2 layer div sebagai background (untuk efek crossfade)
  var layerA = document.createElement("div");
  var layerB = document.createElement("div");

  // Style dasar untuk kedua layer
  var baseStyle = {
    position: "fixed",
    inset: "0",
    backgroundSize: "cover",
    backgroundPosition: "center",
    backgroundRepeat: "no-repeat",
    zIndex: "-2",
    transition: "opacity " + fadeDuration + "ms ease-in-out",
  };

  Object.assign(layerA.style, baseStyle, { opacity: "1" });
  Object.assign(layerB.style, baseStyle, { opacity: "0" });

  // Overlay gelap agar teks di atas foto tetap terbaca
  var overlay = document.createElement("div");
  Object.assign(overlay.style, {
    position: "fixed",
    inset: "0",
    zIndex: "-1",
    // Ubah angka terakhir untuk kegelapan overlay: 0.0 = terang, 1.0 = hitam
    background: "rgba(0,0,0,0.55)",
    pointerEvents: "none",
  });

  // Pasang layer ke dokumen setelah halaman siap
  document.addEventListener("DOMContentLoaded", function () {
    document.body.appendChild(layerA);
    document.body.appendChild(layerB);
    document.body.appendChild(overlay);

    // Pasang foto pertama
    layerA.style.backgroundImage = "url('" + images[0] + "')";

    // Preload semua gambar agar transisi mulus
    images.forEach(function (src) {
      var img = new Image();
      img.src = src;
    });

    // Mulai slideshow
    setInterval(nextSlide, intervalMs);
  });

  // Fungsi untuk pindah ke foto berikutnya
  function nextSlide() {
    currentIndex = (currentIndex + 1) % images.length;
    var nextSrc = "url('" + images[currentIndex] + "')";

    // Tentukan layer mana yang aktif dan mana yang menunggu
    var active  = layerA.style.opacity === "1" ? layerA : layerB;
    var waiting = layerA.style.opacity === "1" ? layerB : layerA;

    // Pasang foto baru di layer yang menunggu, lalu fade
    waiting.style.backgroundImage = nextSrc;
    waiting.style.opacity = "1";
    active.style.opacity  = "0";
  }
})();

/**
 * bg-slideshow-dashboard.js
 * Versi slideshow khusus untuk halaman Dashboard
 *
 * Perbedaan dari versi login/register:
 * - Overlay lebih terang (transparan lebih banyak) → konten dashboard tetap jelas dibaca
 * - Foto tetap berganti otomatis setiap beberapa detik
 *
 * CARA KUSTOMISASI UNTUK PEMULA:
 * 1. Tambah/hapus foto di array "images" di bawah
 * 2. Ubah kecepatan ganti foto di "intervalMs"
 * 3. Ubah "overlayOpacity" untuk mengatur seberapa samar foto di belakang konten
 *    Semakin besar angkanya (mendekati 0.9) → semakin samar/buram
 *    Semakin kecil (mendekati 0.5) → foto lebih kelihatan tapi teks kurang terbaca
 */

(function () {
  // ============================================================
  // KONFIGURASI (Ubah di sini)
  // ============================================================

  const scriptUrl = document.currentScript.src;
  const baseUrl = scriptUrl.substring(0, scriptUrl.lastIndexOf('/js/'));

  var images = [
    baseUrl + "/assets/bg-sekolah.jpg",
    baseUrl + "/assets/bg-sekolah-2.jpg",
    baseUrl + "/assets/bg-sekolah-3.jpg",
    baseUrl + "/assets/bg-sekolah-4.jpg",
    baseUrl + "/assets/bg-sekolah-5.png",
    baseUrl + "/assets/bg-sekolah-6.png",
  ];

  // Kecepatan ganti foto (milidetik): 7000 = 7 detik
  var intervalMs = 7000;

  // Durasi animasi fade antar foto (milidetik)
  var fadeDuration = 1500;

  // Seberapa samar overlay di atas foto (0.0 = tidak ada, 1.0 = hitam penuh)
  // Untuk dashboard disarankan 0.80 - 0.88 agar konten tetap nyaman dibaca
  var overlayOpacity = 0.4;

  // ============================================================
  // LOGIKA SLIDESHOW (Tidak perlu diubah)
  // ============================================================

  var currentIndex = 0;

  var layerA = document.createElement("div");
  var layerB = document.createElement("div");

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

  var overlay = document.createElement("div");
  Object.assign(overlay.style, {
    position: "fixed",
    inset: "0",
    zIndex: "-1",
    background: "rgba(248,249,250," + overlayOpacity + ")",
    pointerEvents: "none",
  });

  document.addEventListener("DOMContentLoaded", function () {
    document.body.appendChild(layerA);
    document.body.appendChild(layerB);
    document.body.appendChild(overlay);

    layerA.style.backgroundImage = "url('" + images[0] + "')";

    images.forEach(function (src) {
      var img = new Image();
      img.src = src;
    });

    setInterval(nextSlide, intervalMs);
  });

  function nextSlide() {
    currentIndex = (currentIndex + 1) % images.length;
    var nextSrc = "url('" + images[currentIndex] + "')";

    var active  = layerA.style.opacity === "1" ? layerA : layerB;
    var waiting = layerA.style.opacity === "1" ? layerB : layerA;

    waiting.style.backgroundImage = nextSrc;
    waiting.style.opacity = "1";
    active.style.opacity  = "0";
  }
})();

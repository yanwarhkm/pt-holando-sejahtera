/* =========================================================
   QRScanner — pemindai QR kamera yang dipakai bersama
   oleh scanner publik (index.html) dan modal admin "Tambah
   Verifikasi" (dashboard.html). Satu implementasi, satu instance
   kamera (singleton) sehingga tidak ada stream yang bentrok.

   Tugas modul ini HANYA membaca isi kode dari kamera — tidak
   membuat/generate QR, tidak menentukan valid/tidaknya kode.

   Pemakaian:
     QRScanner.start({
        mountEl,               // elemen tempat menaruh <video>
        onReady()   {},        // kamera aktif, mulai memindai
        onStatus(teks, jenis){},// pesan status ('active'|'success'|'error'|'')
        onResult(teks){},      // kode terbaca (kamera sudah dihentikan)
        onEnd(alasan){},       // berhenti sendiri tanpa hasil ('timeout'|'kosong'|
                               // 'dihentikan' = halaman disembunyikan/ditinggalkan)
        onError(jenis){}       // gagal memulai (kamera sudah dihentikan)
     });
     QRScanner.stop();         // lepas kamera & hentikan pemindaian (tanpa callback)
     QRScanner.aktif();        // boolean
========================================================= */
window.QRScanner = (function () {
    const BATAS_PINDAI_MS = 30000; // berhenti otomatis bila 30 dtk tanpa kode
    let stream = null, video = null, canvas = null, rafId = 0;
    let batasWaktu = 0, sedangMulai = false, frameTerakhir = 0;
    // Setiap start()/stop() menaikkan nomor sesi sehingga start lama yang masih
    // menunggu izin kamera tidak menyalakan kamera lagi setelah dibatalkan.
    let sesi = 0;
    let callbackAktif = null; // { onStatus, onEnd } milik sesi yang sedang berjalan

    function tersedia() {
        return window.isSecureContext && navigator.mediaDevices && !!navigator.mediaDevices.getUserMedia;
    }

    async function izinDitolak() {
        try {
            return !!navigator.permissions && (await navigator.permissions.query({ name: 'camera' })).state === 'denied';
        } catch {
            return false; // Permissions API kamera tidak didukung semua browser
        }
    }

    function muatJsQR() {
        if (window.jsQR) return Promise.resolve();
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'js/vendor/jsQR.js';
            s.onload = () => window.jsQR ? resolve() : reject(new Error('jsQR'));
            s.onerror = () => { s.remove(); reject(new Error('jsQR')); };
            document.head.appendChild(s);
        });
    }

    function aktif() { return !!stream || sedangMulai; }

    function stop() {
        sesi++;
        sedangMulai = false;
        callbackAktif = null;
        if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
        if (stream) { stream.getTracks().forEach(t => t.stop()); }
        stream = null;
        if (video) { video.srcObject = null; video = null; }
    }

    // Dihentikan oleh halaman (bukan oleh pemanggil): beri tahu pemanggil
    // agar tombol/status ikut kembali ke keadaan awal.
    function hentikanOtomatis() {
        if (!aktif()) return;
        const cb = callbackAktif;
        stop();
        if (cb) {
            cb.onStatus('Pemindaian dihentikan karena halaman tidak aktif.', '');
            cb.onEnd('dihentikan');
        }
    }

    function pesanError(jenis) {
        return {
            'tidak-tersedia': 'Kamera tidak tersedia. Pemindai membutuhkan browser modern dan koneksi HTTPS.',
            'izin-ditolak':   'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini di pengaturan browser, lalu coba lagi.',
            'tidak-ada-kamera': 'Kamera tidak ditemukan pada perangkat ini.',
            'dipakai-lain':   'Kamera sedang dipakai aplikasi lain atau tidak dapat diakses.',
            'komponen':       'Komponen pemindai gagal dimuat. Periksa koneksi lalu coba lagi.',
            'pratinjau':      'Pratinjau kamera gagal ditampilkan.',
            'gagal':          'Kamera tidak tersedia.'
        }[jenis] || 'Kamera tidak tersedia.';
    }

    async function start(opts) {
        const {
            mountEl,
            onReady = () => {}, onStatus = () => {},
            onResult = () => {}, onEnd = () => {}, onError = () => {}
        } = opts || {};

        if (aktif()) return;

        const gagal = (jenis) => { stop(); onStatus(pesanError(jenis), 'error'); onError(jenis); };

        if (!tersedia()) { onStatus(pesanError('tidak-tersedia'), 'error'); onError('tidak-tersedia'); return; }
        if (await izinDitolak()) { onStatus(pesanError('izin-ditolak'), 'error'); onError('izin-ditolak'); return; }
        if (aktif()) return; // start lain sudah berjalan selama pengecekan izin

        const id = ++sesi;
        sedangMulai = true;
        callbackAktif = { onStatus, onEnd };
        onStatus('Meminta izin kamera...', 'active');

        try {
            await muatJsQR();
        } catch { if (id === sesi) gagal('komponen'); return; }
        if (id !== sesi) return; // dibatalkan saat memuat komponen

        let s;
        try {
            s = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
        } catch (err) {
            if (id !== sesi) return;
            const n = err && err.name;
            const jenis = (n === 'NotAllowedError' || n === 'SecurityError') ? 'izin-ditolak'
                : (n === 'NotFoundError' || n === 'OverconstrainedError') ? 'tidak-ada-kamera'
                : (n === 'NotReadableError' || n === 'AbortError') ? 'dipakai-lain' : 'gagal';
            gagal(jenis);
            return;
        }

        if (id !== sesi) { s.getTracks().forEach(t => t.stop()); return; } // dibatalkan saat menunggu izin
        sedangMulai = false;
        stream = s;

        video = document.createElement('video');
        video.setAttribute('playsinline', ''); // wajib di iOS agar tidak fullscreen
        video.muted = true;
        video.style.cssText = 'width:100%; height:100%; min-height:260px; object-fit:cover; display:block;';
        video.srcObject = stream;
        mountEl.replaceChildren(video);
        canvas = canvas || document.createElement('canvas');

        try {
            await video.play();
        } catch {
            if (id !== sesi) return;
            stop(); onStatus(pesanError('pratinjau'), 'error'); onError('pratinjau'); return;
        }
        if (id !== sesi) return;

        onReady();
        onStatus('Kamera aktif. Arahkan kamera ke kode QR pada label.', 'active');
        batasWaktu = Date.now() + BATAS_PINDAI_MS;
        rafId = requestAnimationFrame(w => loop(w, onResult, onStatus, onEnd));
    }

    function loop(waktu, onResult, onStatus, onEnd) {
        if (!stream) return;
        rafId = requestAnimationFrame(w => loop(w, onResult, onStatus, onEnd));
        if (waktu - frameTerakhir < 150) return; // ±6 fps cukup, hemat baterai
        frameTerakhir = waktu;

        if (Date.now() > batasWaktu) {
            stop();
            onStatus('QR gagal dibaca. Pastikan kode terang, fokus, dan berada di dalam bingkai kamera.', 'error');
            onEnd('timeout');
            return;
        }

        if (video.readyState < video.HAVE_ENOUGH_DATA || !video.videoWidth) return;
        const skala = Math.min(1, 640 / Math.max(video.videoWidth, video.videoHeight));
        const w = Math.round(video.videoWidth * skala);
        const h = Math.round(video.videoHeight * skala);
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, 0, 0, w, h);
        const kode = jsQR(ctx.getImageData(0, 0, w, h).data, w, h, { inversionAttempts: 'attemptBoth' });
        if (!kode) return;

        stop(); // hentikan agar kode yang sama tidak terbaca berulang
        if (!kode.data) {
            onStatus('QR terdeteksi, tetapi isinya kosong atau tidak dapat dibaca sebagai teks.', 'error');
            onEnd('kosong');
            return;
        }
        onStatus('QR berhasil dibaca.', 'success');
        onResult(kode.data);
    }

    // Jangan biarkan kamera tetap menyala saat halaman ditinggalkan/disembunyikan
    window.addEventListener('pagehide', hentikanOtomatis);
    document.addEventListener('visibilitychange', () => { if (document.hidden) hentikanOtomatis(); });

    return { start, stop, aktif, tersedia };
})();

/* =========================================================
   VerifikasiQR — mengklasifikasi isi QR/input TANPA mengubahnya.
   - URL resmi: HANYA https:// dengan host TEPAT benih.pertanian.go.id,
     tanpa userinfo/port. javascript:, data:, file:, localhost, IP, dan
     domain lain ditolak. Aturannya sama dengan url_resmi_valid() di
     api/verifikasi.php (server tetap memvalidasi ulang).
   - Kode internal: pola kode/nomor seri yang sama dengan api/verifikasi.php.
   Dikenali sebagai URL resmi ≠ label sah; keabsahan hanya dapat dilihat
   pengguna sendiri di situs resmi.
========================================================= */
window.VerifikasiQR = (function () {
    const HOST_RESMI = 'benih.pertanian.go.id';
    const POLA_URL = /^https:\/\/benih\.pertanian\.go\.id(?:[/?#][A-Za-z0-9\-._~:/?#[\]@!$&'()*+,;=%]*)?$/i;
    const POLA_KODE = /^[A-Z0-9][A-Z0-9\-\/]{1,49}$/;

    function adalahUrlResmi(teks) {
        if (typeof teks !== 'string' || teks.length > 255 || !POLA_URL.test(teks)) return false;
        try {
            const u = new URL(teks);
            return u.protocol === 'https:' && u.hostname === HOST_RESMI && !u.username && !u.password && u.port === '';
        } catch {
            return false;
        }
    }

    function adalahKode(teks) {
        return typeof teks === 'string' && POLA_KODE.test(teks.toUpperCase());
    }

    // 'url-resmi' | 'kode' | 'lain'
    function jenis(teks) {
        if (adalahUrlResmi(teks)) return 'url-resmi';
        if (adalahKode(teks)) return 'kode';
        return 'lain';
    }

    return { HOST_RESMI, adalahUrlResmi, adalahKode, jenis };
})();

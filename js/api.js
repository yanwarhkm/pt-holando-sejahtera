/* =========================================================
   HELPER KOMUNIKASI KE PHP API
   Semua akses database lewat fetch() → api/*.php → MySQL.
========================================================= */

/**
 * Kirim request ke API dan kembalikan JSON { success, message, data }.
 * Melempar Error berisi pesan yang aman ditampilkan ke user bila gagal.
 */
async function apiRequest(url, { method = 'GET', body } = {}) {
    const options = {
        method,
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    };
    if (body !== undefined) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(url, options);
    } catch (e) {
        throw new Error('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
    }

    let json;
    try {
        json = await response.json();
    } catch (e) {
        throw new Error('Respon server tidak valid. Pastikan halaman dibuka melalui server PHP.');
    }

    if (!response.ok || !json.success) {
        const error = new Error(json.message || 'Terjadi kesalahan. Silakan coba lagi.');
        error.status = response.status;
        error.errors = json.errors || null;
        throw error;
    }
    return json;
}

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[ch]);
}

function formatRupiah(value) {
    return 'Rp ' + (Number(value) || 0).toLocaleString('id-ID');
}

/** "2026-09-20" atau "2026-09-20 10:15:00" → Date lokal */
function parseTanggal(value) {
    return new Date(String(value).replace(' ', 'T'));
}

function formatTanggal(value, withTime = false) {
    const date = parseTanggal(value);
    if (isNaN(date)) return '-';
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    if (withTime) Object.assign(options, { hour: '2-digit', minute: '2-digit' });
    return date.toLocaleString('id-ID', options);
}

/* ---------- Validasi frontend (dicek ulang di backend) ---------- */
const POLA_WA = /^(\+62|62|0)8\d{7,12}$/;

function normalisasiWA(value) {
    return String(value || '').replace(/[\s\-().]/g, '');
}

function waValid(value) {
    return POLA_WA.test(normalisasiWA(value));
}

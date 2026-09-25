<?php
/**
 * Isi langkah panduan yang TERKUNCI.
 * Hanya dikirim oleh api/akses_panduan.php kepada sesi yang sudah
 * memverifikasi pesanan valid — tidak ditulis di HTML agar tidak bisa
 * dibaca lewat "View Source".
 */
declare(strict_types=1);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

return [
    // index.html → section #panduan (langkah 1–3 tampil gratis)
    'beranda' => [
        [
            'no'    => 4,
            'judul' => 'Pemupukan',
            'isi'   => 'Pupuk rutin setiap 2 minggu sekali. Gunakan pupuk organik cair agar hasil lebih sehat dan ramah lingkungan. Tambahkan pupuk kandang saat tanaman mulai tumbuh besar. Hindari pemupukan berlebih.',
        ],
        [
            'no'    => 5,
            'judul' => 'Pengendalian Hama',
            'isi'   => 'Cek tanaman secara rutin pagi dan sore. Gunakan pestisida nabati dari daun pepaya, bawang putih, atau cabai untuk mengusir hama tanpa merusak lingkungan. Pisahkan tanaman yang terserang agar tidak menular.',
        ],
        [
            'no'    => 6,
            'judul' => 'Panen & Pascapanen',
            'isi'   => 'Panen saat buah/sayur sudah matang sempurna namun belum terlalu tua. Lakukan pagi hari setelah embun kering. Pisahkan berdasarkan ukuran dan kualitas. Simpan di tempat sejuk dan bersih agar tahan lama.',
        ],
    ],

    // panduan-kentang.html (langkah 1–3 tampil gratis)
    'panduan-kentang' => [
        [
            'no'    => 4,
            'judul' => 'Penyiraman & Pemupukan',
            'isi'   => 'Siram tanaman secara teratur, jaga tanah tetap lembap tapi tidak becek. Gunakan pupuk kandang saat tanam dan pupuk organik cair setiap 2 minggu sekali agar pertumbuhan umbi maksimal. Hindari pupuk kimia berlebih agar rasa kentang tetap enak dan sehat.',
        ],
        [
            'no'    => 5,
            'judul' => 'Penggemburan Tanah',
            'isi'   => 'Timbun tanah ke pangkal batang setinggi 15–20 cm saat tanaman mencapai tinggi 20–30 cm. Ini penting agar umbi yang tumbuh tidak terkena sinar matahari dan berubah warna menjadi hijau (beracun). Lakukan penggemburan 2–3 kali selama masa pertumbuhan.',
        ],
        [
            'no'    => 6,
            'judul' => 'Pencegahan Hama & Penyakit',
            'isi'   => 'Hama utama kentang adalah ulat, kutu daun, dan kumbang. Cegah dengan menanam bawang merah di sekitarnya sebagai tanaman pengusir hama. Cabut tanaman yang sakit segera agar tidak menular. Gunakan pestisida alami dari air rendaman tembakau atau bawang putih.',
        ],
        [
            'no'    => 7,
            'judul' => 'Masa Panen',
            'isi'   => 'Kentang siap dipanen saat daun mulai menguning dan mengering, biasanya 90–120 hari setelah tanam. Cek umbi di tanah, jika kulit sudah kuat dan tidak mudah lepas, berarti sudah siap. Cabut tanaman perlahan dan biarkan kentang di tanah 1–2 hari untuk mengeringkan kulit sebelum disimpan.',
        ],
        [
            'no'    => 8,
            'judul' => 'Penyimpanan',
            'isi'   => 'Simpan kentang di tempat yang sejuk, gelap, dan berventilasi baik. Suhu ideal 4–10°C. Jangan simpan di tempat lembap atau terkena cahaya karena akan tumbuh tunas dan berubah warna menjadi hijau. Pisahkan kentang yang rusak agar tidak membusukkan yang lain.',
        ],
    ],
];

<?php
/**
 * Isi panduan yang TERKUNCI: Bab 4 dokumen
 * "Standar Teknologi Produksi G2–G3 Petani"
 * PT. Kentang Hollando Sejahtera — No. 005/KHS/SP.PRD- Rev.2 (20 Februari 2026).
 *
 * Hanya dikirim oleh api/akses_panduan.php kepada sesi yang sudah
 * memverifikasi pesanan valid — tidak ditulis di HTML agar tidak bisa
 * dibaca lewat "View Source". Bab 1–3 (publik) ada di panduan-kentang.html.
 *
 * Aturan isi:
 * - Teks mengikuti PDF asli; yang dirapikan hanya typo ejaan murni.
 * - Angka, dosis, satuan, bahan aktif, nama organisme, nama produk, dan
 *   teks terpotong TIDAK diubah; bagian yang meragukan diberi blok
 *   'review' (perlu konfirmasi client).
 * - Kolom Cara Kerja & Dosis tabel jadwal OPT (hal. 6) sengaja tidak
 *   dimuat sampai client mengonfirmasi tabel aslinya.
 *
 * Jenis blok (dirender oleh panduan-kentang.html, semua teks di-escape):
 *   p, h, list, hst, param, tabel, callout, review, organisme
 */
declare(strict_types=1);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

const CATATAN_KIMIA = 'Informasi bahan kimia/pestisida pada bagian ini adalah referensi dari dokumen standar perusahaan, bukan rekomendasi penggunaan. Untuk penggunaan aktual, ikuti label resmi produk, ketentuan penggunaan yang berlaku, dan arahan petugas/tenaga yang berwenang.';
const CATATAN_DOSIS_MERK = 'Dosis menyesuaikan anjuran dari setiap merk dagang (sesuai dokumen).';

return [
    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-1',
        'no'    => '4.1',
        'judul' => 'Persiapan Lahan',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Hal-hal yang harus diperhatikan terkait dengan pemilihan lahan:'],
            ['t' => 'list', 'item' => [
                ['judul' => 'Rotasi tanaman', 'teks' => 'Rotasi tanaman yaitu pergiliran tanaman yang bertujuan untuk memutus siklus hidup hama dan penyakit tanaman, minimal 2 kali tanaman non-solanaceae dan 3 bulan bera bersih yaitu bera yang diolah (Bera merupakan tanah yang dibiarkan tidak ditanami agar kembali kesuburannya).'],
                ['judul' => 'Pengelolaan lahan', 'teks' => 'Setelah lahan diberakan selama 3 bulan, lahan diolah kembali untuk digemburkan, rumput (gulma) dibuang jauh dari lahan yang akan ditanami.'],
                ['judul' => 'Pengecekan kondisi tanah', 'teks' => 'Melakukan pengecekan pH. Pastikan pH di antara 6–6,5. Selanjutnya melakukan pengambilan sampel tanah untuk uji lab; memastikan bahwa lahan bebas dari Nematoda Sista Kentang (NSK).'],
            ]],
            ['t' => 'review', 'teks' => 'Ketentuan rotasi di sini ("minimal 2 kali tanaman non-solanaceae dan 3 bulan bera bersih") berbeda dengan bagian 3.2 ("dirotasi 2 musim dengan tanaman non-solanaceae atau lahan diberakan minimal 9 bulan").'],

            ['t' => 'h', 'teks' => 'Tahapan Pengolahan Lahan'],
            ['t' => 'h', 'teks' => 'a) Pembersihan lahan'],
            ['t' => 'p', 'teks' => 'Pembersihan lahan bertujuan untuk mempermudah proses pengelolaan lahan & membersihkan lahan dari segala sesuatu yang dapat mengganggu pertumbuhan tanaman, baik itu fisik (batu-batuan, sampah, dll.) maupun biologis (gulma/sisa-sisa tanaman).'],

            ['t' => 'h', 'teks' => 'b) Pengolahan tanah tahap 1 (satu)'],
            ['t' => 'p', 'teks' => 'Pengolahan tanah meliputi beberapa tahapan, yaitu:'],
            ['t' => 'list', 'item' => [
                'Lahan dibajak 2 kali dengan selisih waktu pembajakan minimal 7 hari.',
                'Dibuat saluran drainase atau got keliling sedalam ± 10 cm, untuk menghindari terjadinya genangan air.',
                'Lahan dikerjakan dengan membuat guludan kasar atau alur tanam.',
                'Guludan kasar atau alur tanam untuk Single Row yaitu lebar alur lubang tanam single row antar baris 85 cm ± 5 cm, ukuran dapat berbeda atau lebih lebar tergantung kemiringan lahan dan ukuran bahan tanah.',
                'Guludan kasar untuk Double Row yaitu lebar guludan double row 90 cm dan lebar saluran/lorong 40 cm.',
            ]],
            ['t' => 'param', 'item' => [
                ['label' => 'Single Row', 'nilai' => '85 cm ± 5 cm', 'ket' => 'Lebar alur lubang tanam antar baris'],
                ['label' => 'Double Row', 'nilai' => '90 cm', 'ket' => 'Lebar guludan; saluran/lorong 40 cm'],
            ]],
            ['t' => 'review', 'teks' => 'Frasa "ukuran bahan tanah" pada ketentuan Single Row — mohon konfirmasi apakah yang dimaksud "bahan tanam".'],
            ['t' => 'list', 'item' => [
                'Jika kondisi tanah terlalu asam (pH < 5) upayakan untuk mencari lokasi lain, akan tetapi jika tidak memungkinkan bisa dilakukan pengapuran dengan dolomit (kapur pertanian) sebanyak 300 Kg/ 0,1 ha (berdasarkan kondisi pH tanah).',
                'Campuran/cacah tanah guludan yang sudah ditaburi dolomit agar tercampur, siram dengan air supaya segera bereaksi dengan tanah.',
                'Tabur guludan kasar dengan pupuk organik (pupuk kandang, bokasi atau petroganik) sebanyak 8 ton/HA minimal 7 hari sebelum tanam.',
                'Aplikasi Soil Treatment (Asam Humat, Nematisida, Tricoderma dan Bacillus) 4 hari sebelum tanam. Dosis sesuai dengan anjuran pabrik.',
            ]],
            ['t' => 'review', 'teks' => 'Satuan dolomit "300 Kg/ 0,1 ha" dan takaran pupuk organik "8 ton/HA" (bandingkan dengan 4.2: "postal 20 ton atau batre 8 ton per Ha") perlu dikonfirmasi.'],
            ['t' => 'callout', 'jenis' => 'kimia', 'teks' => CATATAN_KIMIA],

            ['t' => 'h', 'teks' => 'c) Pengolahan tanah tahap 2 (dua)'],
            ['t' => 'p', 'teks' => 'Bentuk guludan kasar menjadi guludan siap tanam dengan ketinggian guludan 10–20 cm, tergantung kondisi air, jenis tanah dan lokasi.'],
            ['t' => 'param', 'item' => [
                ['label' => 'Musim kemarau', 'nilai' => '10–20 cm', 'ket' => 'Juga untuk tanah ringan/pasir dan lahan terasering — guludan dibuat pendek'],
                ['label' => 'Musim hujan', 'nilai' => '15–25 cm', 'ket' => 'Guludan lebih tinggi untuk mencegah genangan air'],
            ]],
            ['t' => 'list', 'item' => [
                'Tinggi guludan akan berbeda tergantung kondisi lahan dan kemiringan lahan.',
            ]],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-2',
        'no'    => '4.2',
        'judul' => 'Pemupukan Dasar',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Pembuatan alur tanam dan penaburan pupuk kandang yang sudah matang (postal 20 ton atau batre 8 ton per Ha) minimal 7 hari sebelum tanam dan aplikasi soil treatment (asam humat, nematisida, tricoderma dan bacillus) 4 hari sebelum tanam. Pengaplikasian pupuk dasar kimia 2 hari sebelum tanam kemudian ditutup tipis dengan tanah.'],
            ['t' => 'hst', 'item' => [
                ['hst' => '≥ 7 hari sebelum tanam', 'teks' => 'Pembuatan alur tanam dan penaburan pupuk kandang yang sudah matang.'],
                ['hst' => '4 hari sebelum tanam', 'teks' => 'Aplikasi soil treatment (asam humat, nematisida, tricoderma dan bacillus).'],
                ['hst' => '2 hari sebelum tanam', 'teks' => 'Pengaplikasian pupuk dasar kimia, kemudian ditutup tipis dengan tanah.'],
            ]],
            ['t' => 'callout', 'jenis' => 'kimia', 'teks' => CATATAN_KIMIA],
            ['t' => 'tabel', 'judul' => 'Rincian pupuk dasar kimia (sesuai dokumen)', 'kolom' => ['No.', 'Pupuk', 'Kebutuhan/Ha', 'Unit'], 'baris' => [
                ['1.', 'NPK 16:16:16', '700', 'Kg/ha'],
                ['2.', 'Sp36', '280', 'Kg/ha'],
                ['3.', 'MOP / KCL 60%', '200', 'Kg/ha'],
                ['4.', 'Furadan/Diazinon', '20', 'Kg/ha'],
            ]],
            ['t' => 'p', 'teks' => 'Aplikasi pupuk dasar kimia untuk pola tanam double row diberikan bersamaan dengan penanaman dengan cara ditugal, kedalaman ± 10 cm di antara penanaman bibit kentang.'],
            ['t' => 'tabel', 'judul' => 'Rincian pupuk dasar kimia — pola tanam double row (sesuai dokumen)', 'kolom' => ['No.', 'Pupuk', 'Kebutuhan/Ha', 'Unit'], 'baris' => [
                ['1.', 'NPK 16:16:16', '900', 'Kg/ha'],
                ['2.', 'Sp36', '350', 'Kg/ha'],
                ['3.', 'MOP / KCL 60%', '250', 'Kg/ha'],
                ['4.', 'Furadan/Diazinon', '25', 'Kg/ha'],
            ]],
            ['t' => 'p', 'teks' => 'Dosis pupuk berikut dicampur secara merata untuk kemudian diaplikasikan pada masing-masing lubang tanam dengan asumsi per lubang tanam adalah sebanyak 12–15 gr.'],
            ['t' => 'review', 'teks' => 'Mohon konfirmasi: (1) Furadan/Diazinon adalah insektisida namun tercantum di tabel "Pupuk"; (2) arti istilah "postal" dan "batre"; (3) asumsi 12–15 gr per lubang berlaku untuk tabel yang mana — jarak tanam double row tidak disebutkan di dokumen.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-3',
        'no'    => '4.3',
        'judul' => 'Cara Tanam',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Proses penanaman benih kentang dilakukan dengan beberapa cara, meliputi:'],
            ['t' => 'list', 'item' => [
                ['judul' => 'a)', 'teks' => 'Benih kentang ditanam dengan cara dibenamkan ke dalam tanah. Pada waktu tanam, posisi kentang jangan sampai terbalik titik tumbuhnya, titik tumbuh harus berada di atas.'],
                ['judul' => 'b)', 'teks' => 'Buat alur pada baris yang akan ditanami dengan kedalaman ± 10 cm.'],
                ['judul' => 'c)', 'teks' => 'Atur jarak tanam kentang dengan menggunakan bantuan bambu atau tali rafia (menyesuaikan di lapang) atau dengan jarak tanam 85x25 cm single row sesuai ukuran generasi benih (ukuran bahan tanam yang besar jarak tanam menjadi 85x30 cm).'],
                ['judul' => 'd)', 'teks' => 'Setelah benih tertata sesuai dengan jarak tanam, kemudian tutup dengan tanah sesuai dengan alurnya (pembumbunan/hilling).'],
                ['judul' => 'e)', 'teks' => 'Penanaman dilakukan pada pagi hari dan/atau sore hari, setelah penanaman dipastikan dilakukan penyiraman pada musim kemarau.'],
            ]],
            ['t' => 'param', 'item' => [
                ['label' => 'Kedalaman alur', 'nilai' => '± 10 cm', 'ket' => 'Titik tumbuh menghadap ke atas'],
                ['label' => 'Jarak tanam single row', 'nilai' => '85 × 25 cm', 'ket' => 'Sesuai ukuran generasi benih'],
                ['label' => 'Bahan tanam besar', 'nilai' => '85 × 30 cm', 'ket' => 'Jarak tanam disesuaikan'],
            ]],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-4',
        'no'    => '4.4',
        'judul' => 'Pembumbunan',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Proses pembumbunan (hilling) adalah salah satu proses penting dalam penanaman kentang. Pembumbunan berfungsi untuk memperkokoh tanaman. Pembumbunan akan lebih efisien jika dilakukan bersamaan dengan penyiangan yang bertujuan untuk memperkokoh posisi batang, sehingga tanaman tidak mudah rebah. Selain itu juga untuk menutup akar tanaman yang bermunculan di atas permukaan tanah karena adanya aerasi pada tanah.'],
            ['t' => 'h', 'teks' => 'Waktu pembumbunan'],
            ['t' => 'hst', 'item' => [
                ['hst' => '25–30 HST', 'judul' => 'Pembumbunan I', 'teks' => '± 25% canopy sudah terbentuk.'],
                ['hst' => '35–40 HST', 'judul' => 'Pembumbunan II', 'teks' => '± 50% canopy sudah terbentuk.'],
            ]],
            ['t' => 'list', 'item' => [
                'Pembumbunan dilakukan dengan cara mencangkul tanah di antara guludan (parit) kemudian dinaikkan ke atas guludan sebelah kanan & kiri parit.',
            ]],
            ['t' => 'h', 'teks' => 'Tujuan pembumbunan'],
            ['t' => 'list', 'item' => [
                'Memperkokoh tanaman dan posisi batang, sehingga tanaman tidak mudah rebah.',
                'Menutup akar tanaman yang bermunculan di atas permukaan tanah karena adanya aerasi pada tanah.',
            ]],
            ['t' => 'review', 'teks' => 'Dokumen menulis "proses pembumbunan yang harus dilakukan pada saat tanam", sedangkan waktu yang tercantum adalah 25–30 HST dan 35–40 HST.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-5',
        'no'    => '4.5',
        'judul' => 'Penyiangan',
        'blok'  => [
            ['t' => 'hst', 'item' => [
                ['hst' => '20–30 HST', 'judul' => 'Waktu penyiangan', 'teks' => 'Biasanya dilakukan bersamaan dengan perbaikan guludan, saat tanaman berumur sekitar satu bulan setelah tanam.'],
            ]],
            ['t' => 'p', 'teks' => 'Penyiangan dilakukan dengan membersihkan areal pertanaman dari gulma, tanaman pengganggu lainnya dan tanaman yang sakit. Proses penyiangan penting untuk dilakukan agar pertumbuhan tanaman bisa tumbuh dengan baik serta mempunyai penutupan canopy daun yang optimal, sehingga proses inisiasi umbi juga akan optimal.'],
            ['t' => 'h', 'teks' => 'Tujuan penyiangan'],
            ['t' => 'list', 'item' => [
                ['judul' => 'a)', 'teks' => 'Untuk memperbaiki aerasi tanah dan mempertahankan kadar lengas tanah.'],
                ['judul' => 'b)', 'teks' => 'Untuk mengurangi persaingan unsur hara oleh gulma agar dapat dimanfaatkan optimal oleh tanaman.'],
                ['judul' => 'c)', 'teks' => 'Untuk menggemburkan tanah agar penetrasi akar tanaman pokok lebih mudah.'],
            ]],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-6',
        'no'    => '4.6',
        'judul' => 'Pemupukan Susulan',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Pupuk susulan diberikan setelah tanaman ditanam di lahan tersebut yang bertujuan untuk senantiasa mensuplai kebutuhan nutrisi selama tanaman tumbuh dan berkembang.'],
            ['t' => 'callout', 'jenis' => 'penting', 'judul' => 'Prinsip', 'teks' => 'Pupuk susulan pada produksi benih kentang sifatnya hanya jika diperlukan saja, jika kondisi pertumbuhan tanaman terkena serangan & kurang optimal.'],
            ['t' => 'p', 'teks' => 'Cara pengaplikasian pupuk susulan menurut dokumen:'],
            ['t' => 'list', 'item' => [
                'Jenis pupuk yang dipakai hanya NPK (mutiara dst) bukan pupuk N tunggal; jika hanya menggunakan pupuk tunggal N akan berakibat tanaman cenderung vigor & mengganggu proses inisiasi umbi.',
                'Dosis yang digunakan adalah 5–10 gr/tanaman.',
                'Aplikasi pupuk dengan cara ditabur di sekitar tanaman pada umur 25–30 HST kemudian dilakukan dengan proses pembumbunan II.',
            ]],
            ['t' => 'review', 'teks' => 'Pemupukan susulan disebut pada 25–30 HST "kemudian dilakukan dengan proses pembumbunan II", sedangkan bagian 4.4 menempatkan pembumbunan II pada 35–40 HST.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-7',
        'no'    => '4.7',
        'judul' => 'Penyiraman',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Penyiraman pada tanaman kentang merupakan salah satu proses perawatan tanaman untuk mempertahankan kadar air tanah sebagai sumber makanan tumbuhan. Pengairan atau penyiraman menjadi salah satu faktor penting untuk pertumbuhan tanaman kentang, oleh karena itu hindari menanam kentang pada lahan yang sulit kondisi air. Selain itu perlu memperhatikan musim tanam agar mendapatkan supply air yang optimal selama produksi benih kentang.'],
            ['t' => 'h', 'teks' => 'Waktu penting pengairan'],
            ['t' => 'hst', 'item' => [
                ['hst' => 'Setelah tanam', 'teks' => 'Saat setelah tanam atau maksimal 3 hari setelah tanam.'],
                ['hst' => 'Pemupukan susulan', 'teks' => 'Saat pemupukan susulan.'],
                ['hst' => 'Pembungaan', 'teks' => 'Pembungaan (pembentukan umbi).'],
            ]],
            ['t' => 'param', 'item' => [
                ['label' => 'Interval pengairan', 'nilai' => 'Setiap 7–10 hari', 'ket' => 'Tergantung jenis tanah, fase tanaman dan kondisi lahan'],
            ]],
            ['t' => 'list', 'item' => [
                'Jangan biarkan lahan terlalu kering, karena dapat mengakibatkan stress pada tanaman sehingga pertumbuhan tanaman bisa terhambat (tanaman kerdil atau layu dan akhirnya mati).',
                'Pengairan bisa menggunakan sistem sprinkler.',
            ]],
            ['t' => 'callout', 'jenis' => 'penting', 'teks' => 'Hindari pemberian air berlebihan. Tanaman kentang sangat peka terhadap air yang berlebih, terutama air yang menggenang.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-8',
        'no'    => '4.8',
        'judul' => 'Pengendalian OPT',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Point penting dalam proses produksi benih kentang adalah kesehatan tanaman & kesehatan umbi, karena sertifikasi atau standar kelulusan benih kentang adalah kesehatan umbi atau benih yang dihasilkan. Oleh karena itu pengendalian OPT menjadi salah satu faktor penting dalam keberhasilan proses produksi benih kentang.'],
            ['t' => 'callout', 'jenis' => 'penting', 'teks' => 'Pengendalian OPT pada tanaman kentang seharusnya dilakukan sejak dini, karena jika sudah terlanjur parah akan sulit untuk dikendalikan.'],
            ['t' => 'h', 'teks' => 'Jadwal pengendalian OPT (referensi dokumen)'],
            ['t' => 'callout', 'jenis' => 'kimia', 'teks' => CATATAN_KIMIA],
            ['t' => 'tabel', 'judul' => 'Anjuran pengendalian OPT untuk budidaya produksi benih kentang', 'kolom' => ['Umur Tanam (HST)', 'Pestisida', 'Type', 'Hama Sasaran'], 'baris' => [
                ['3', 'Daconil', 'Fungisida', 'LB'],
                ['3', 'Agrimec', 'Insektisida', 'Mites, trips'],
                ['8', 'Cymoxanil', 'Fungisida', 'LB'],
                ['8', 'Rampage', 'Insektisida', 'Cut worms, broad spec'],
                ['15', 'Ridomil G', 'Fungisida', 'LB'],
                ['15', 'Trigard', 'Insektisida', 'LMF, aphids, thrips'],
                ['22', 'Acrobat', 'Fungisida', 'LB'],
                ['22', 'Actara', 'Insektisida', 'Thrips, aphids'],
                ['29', 'Revus opti', 'Fungisida', 'LB'],
                ['29', 'Karate', 'Insektisida', 'broad'],
                ['36', 'Ridomil G', 'Fungisida', 'LB'],
                ['36', 'Profenofos', 'Insektisida', 'broad'],
                ['42', 'Dhitane', 'Fungisida', 'LB'],
                ['42', 'Enduro', 'Insektisida', 'Aphids, thrips, broad'],
                ['49', 'Acrobat', 'Fungisida', 'LB'],
                ['49', 'Rampage', 'Insektisida', 'Cut worms, broad'],
                ['56', 'Curzate', 'Fungisida', 'LB'],
                ['56', 'Trigard', 'Insektisida', 'Aphids, thrips, broad'],
                ['62', 'Daconil', 'Fungisida', 'LB'],
                ['62', 'Rampage', 'Insektisida', 'broad'],
                ['69', 'Revus Opt', 'Fungisida', 'LB'],
                ['69', 'Actara', 'Insektisida', 'Thrips, sucking insects'],
                ['76', 'Dhitane', 'Fungisida', 'LB'],
                ['76', 'Decis', 'Insektisida', 'Broad'],
                ['85', 'Acrobat', 'Fungisida', 'LB'],
                ['85', 'Karate', 'Insektisida', 'Broad'],
                ['92', 'Antracol', 'Fungisida', 'LB'],
                ['92', 'Trigard', 'Insektisida', 'Broad'],
                ['109', 'Daconil', 'Fungisida', 'LB'],
                ['109', 'Profenofos', 'Insektisida', 'Broad'],
                ['116', 'Curzate', 'Fungisida', 'LB'],
                ['116', 'Enduro', 'Insektisida', 'Broad'],
            ], 'catatan' => 'Kolom "Cara Kerja" dan "Dosis" pada tabel asli tidak ditampilkan sampai dikonfirmasi client. Untuk dosis dan cara aplikasi, ikuti label resmi produk dan arahan petugas/tenaga yang berwenang.'],
            ['t' => 'review', 'teks' => 'Mohon konfirmasi: (1) jadwal berlanjut sampai 116 HST, sedangkan panen pada 85–90 HST (4.10); (2) jarak 92 → 109 HST tidak mengikuti pola ± 7 hari; (3) singkatan "LB" dan "LMF" tidak dijelaskan di dokumen; (4) penulisan "Dhitane", "Revus opti/Revus Opt" dan "trips" ditampilkan apa adanya; (5) judul bagian di dokumen "Pengendalian OPT (Hama)" namun jadwal juga memuat fungisida.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-9',
        'no'    => '4.9',
        'judul' => 'Rouging',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Rouging merupakan salah satu proses kegiatan yang penting untuk mengidentifikasi dan menghilangkan tanaman yang menyimpang. Tujuan dilakukannya rouging itu sendiri adalah untuk mempertahankan kemurnian dan mutu genetik suatu varietas. Roguing dilakukan pada semua tanaman dengan melakukan seleksi tanaman yang terserang OPT.'],
            ['t' => 'hst', 'item' => [
                ['hst' => 'Mulai 25–30 HST', 'judul' => 'Waktu pelaksanaan', 'teks' => 'Dimulai saat tanaman berumur 25–30 HST (± 25% canopy sudah terbentuk) sampai dengan sebelum tanaman dimatikan pada saat menjelang panen.'],
            ]],
            ['t' => 'list', 'item' => [
                ['judul' => 'Tujuan', 'teks' => 'Mempertahankan kemurnian dan mutu genetik suatu varietas.'],
                ['judul' => 'Identifikasi', 'teks' => 'Mengidentifikasi dan menghilangkan tanaman yang menyimpang, serta menyeleksi tanaman yang terserang OPT.'],
                ['judul' => 'Tanaman terserang OPT', 'teks' => 'Rouging dilakukan dengan mencabut tanaman yang terserang OPT beserta umbinya.'],
                ['judul' => 'Penanganan', 'teks' => 'Tanaman dan umbi yang terserang OPT harus dikumpulkan dan selanjutnya dibuang atau dikubur sedalam mungkin di lokasi lain, agar tidak menjadikan sumber infeksi pada lahan tersebut.'],
            ]],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-10',
        'no'    => '4.10',
        'judul' => 'Panen',
        'blok'  => [
            ['t' => 'param', 'item' => [
                ['label' => 'Umur panen', 'nilai' => '85–90 HST', 'ket' => 'Atau 7–10 hari setelah tanaman dimatikan'],
                ['label' => 'Cuaca', 'nilai' => 'Cerah', 'ket' => 'Hindari panen saat hujan'],
            ]],
            ['t' => 'p', 'teks' => 'Pemanenan dilakukan pada umur tanaman sekitar umur 85–90 HST atau 7–10 hari setelah tanaman dimatikan. Beberapa hal yang perlu diperhatikan dalam panen yaitu:'],
            ['t' => 'list', 'item' => [
                ['judul' => 'Cara panen', 'teks' => 'Penggemburan garitan dilakukan dengan mencangkul pinggiran garitan lalu mengangkatnya. Pencangkulan hanya dilakukan satu kali pada setiap tempat untuk menghindari kerusakan umbi oleh cangkul. Setelah garitan gembur penggalian dan pengumpulan umbi dilakukan tangan dengan cermat dan hati-hati, umbi yang sudah terkumpul dibiarkan beberapa saat di permukaan tanah sampai tanah yang menempel pada kulit umbi kering dan terlepas.'],
                ['judul' => 'Waktu panen', 'teks' => 'Panen dilakukan pada saat cuaca cerah, hindari waktu panen kena hujan, karena apabila panen kena hujan umbi akan mudah rusak/busuk pada saat disimpan di gudang.'],
                ['judul' => 'Sortasi/seleksi di lapangan', 'teks' => 'Setelah tanah tidak menempel di permukaan umbi, segera dilakukan pengumpulan dan dilaksanakan seleksi dan grading. Seleksi yaitu untuk memisahkan umbi yang sehat dan umbi yang rusak oleh hama dan penyakit atau yang rusak oleh mekanis. Sisa umbi yang tidak terpilih atau yang terkena hama dan penyakit harus dikumpulkan dan selanjutnya dibuang atau dikubur sedalam mungkin di lokasi lain, agar tidak menjadikan sumber infeksi pada lahan tersebut.'],
                ['judul' => 'Peralatan panen', 'teks' => 'Jangan menggunakan peralatan panen yang berbahan logam (cangkul (hanya untuk mencangkul pinggiran garitan yang memadat), cetok, sekop dll) karena akan merusak kulit umbi kentang. Gunakan peralatan untuk panen dari bahan dasar plastik (Entong, dll) sehingga tidak melukai kulit umbi. Bilamana kulit umbi luka maka penyakit akan mudah menginfeksi ke dalam umbi kentang.'],
            ]],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-11',
        'no'    => '4.11',
        'judul' => 'Penanganan Pasca Panen',
        'blok'  => [
            ['t' => 'p', 'teks' => 'Penanganan pasca panen bertujuan agar mutu benih baik seperti pada awal dipanen.'],
            ['t' => 'hst', 'item' => [
                ['hst' => '3–7 hari di gudang', 'judul' => 'Sortasi', 'teks' => 'Sortir dilakukan setelah 3–7 hari umbi di gudang, berdasarkan kriteria umbi yang telah ditentukan oleh QC.'],
                ['hst' => 'H+1 setelah sortasi', 'judul' => 'Form QC', 'teks' => 'FI mengajukan Form QC ke QC.'],
                ['hst' => 'Hasil QC', 'judul' => 'Penentuan kelayakan', 'teks' => 'Hasil pengecekan QC akan menentukan status umbi layak masuk gudang atau tidak.'],
                ['hst' => 'Lolos QC', 'judul' => 'Penyimpanan', 'teks' => 'Umbi yang sudah lolos pengecekan QC segera diangkut ke gudang oleh logistik.'],
            ]],
            ['t' => 'review', 'teks' => 'Dokumen hanya menulis singkatan "FI" tanpa kepanjangan. Mohon konfirmasi kepanjangan singkatan tersebut.'],
        ],
    ],

    // ---------------------------------------------------------------
    [
        'id'    => 'bab-4-12',
        'no'    => '4.12',
        'judul' => 'Hama dan Penyakit',
        'blok'  => [
            ['t' => 'callout', 'jenis' => 'kimia', 'teks' => CATATAN_KIMIA],

            ['t' => 'organisme', 'judul' => 'A. Hama pada Tanaman Kentang', 'item' => [
                [
                    'nama'    => 'Whitefly (kutu putih/kutu kebul)',
                    'latin'   => 'Bemisia tabaci',
                    'rincian' => [
                        ['Deskripsi umum', 'Serangga dewasa bersayap putih dengan tubuh kuning berukuran sekitar 1mm.'],
                        ['Tanaman inang', 'Rosela, kenaf, kapas, kacang tanah, buncis, kapri, cabe, tomat, terong, tembakau, ubi jalar, tanaman timun-timunan, singkong, kubis, sawi, jambu biji, wijen, kembang sepatu, dan gulma babandotan.'],
                        ['Gejala kerusakan', 'Serangga aktif berpindah dan bersembunyi di bawah daun. Tipe mulutnya yang menusuk dan menghisap mengakibatkan bercak klorotik di permukaan daun. Whitefly merupakan pembawa virus.'],
                        ['Teknik pengendalian', 'Sanitasi dari gulma dan tanaman inang. Penggunaan perangkap lekat berwarna kuning.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan neonicotinoid: Tiamektosam, Imidakloprid',
                        'Golongan organofosfat: Klorpirifos, Metidation',
                        'Golongan Avermektin: Abamektin',
                        'Golongan Piretoid: permetrin',
                        'Golongan Siromazin: Siromazin',
                        'Golongan diafentiuron: diafentiuron',
                        'Penyemprotan di permukaan bawah daun. Aplikasi rotasi bahan aktif berdasarkan golongan setiap 3 minggu sekali.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                    'review' => 'Daftar golongan diafentiuron di dokumen terpotong ("diafentiuron, , dan"); penulisan "Tiamektosam" ditampilkan apa adanya.',
                ],
                [
                    'nama'    => 'Leaf miner (Peneropong daun/hama Tulis/hama Batik)',
                    'latin'   => 'Liriomyza spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Serangga dewasa berupa lalat kecil berwarna kuning dengan sayap hitam dan transparan. Larva berupa ulat berwarna kuning transparan dengan ukuran sangat kecil dalam daging daun.'],
                        ['Tanaman inang', 'Cabe, tomat, terong, tanaman timun-timunan.'],
                        ['Gejala kerusakan', 'Serangga dewasa dengan tipe mulutnya yang menusuk dan menghisap mengakibatkan bercak klorotik di permukaan daun. Serangga ini aktif berpindah dan meletakkan telurnya pada daging daun melalui permukaan daun bagian atas, sehingga larva membuat terowongan yang menyebabkan daging daun menjadi rusak, mengering dan akhirnya gugur.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Sanitasi dari gulma dan tanaman inang. Penggunaan perangkap lekat berwarna kuning.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Avermektin: Abamektin',
                        'Golongan Siromazin: Siromazin',
                        'Aplikasi rotasi bahan aktif berdasarkan golongan setiap 3 minggu sekali.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Aphid (kutu daun)',
                    'latin'   => 'Myzus persicae',
                    'rincian' => [
                        ['Deskripsi umum', 'Myzus persicae berwarna kuning kehijau-hijauan terang. Aphid ini memiliki 2 mata merah dan 2 shipunculi (seperti tongkat terdapat di bagian belakang tubuhnya), bentuk tubuh seperti buah pir.'],
                        ['Tanaman inang', 'Buncis, kapri, cabe, terong, tembakau, ubi jalar, timun, labu, kubis, dan sawi.'],
                        ['Gejala kerusakan', 'Serangga aktif dan bersembunyi di bawah permukaan daun. Tipe mulut penusuk penghisap cairan daun yang menyebabkan daun menjadi keriting dan melengkung ke atas, menyebabkan luka-luka kecil coklat kekeringan pada bagian sudut tulang daun sekunder. Aphid pembawa virus Mozaik, PLRV dan PVY.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Sanitasi dari gulma dan tanaman inang. Penggunaan perangkap lekat berwarna kuning.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Neonikotinoid: Imidakloprid',
                        'Golongan Avermektin: Abamektin',
                        'Golongan Piretoid: Deltametrin',
                        'Penyemprotan di permukaan bawah daun. Aplikasi rotasi bahan aktif berdasarkan golongan setiap 3 minggu sekali.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Thrips',
                    'latin'   => 'Thrips spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Thrips berukuran sangat kecil, tubuhnya ramping. Thrips muda berwarna kekuningan. Dewasa berwarna lebih gelap coklat kehitaman, dengan dua garis sejajar pada punggungnya.'],
                        ['Tanaman inang', 'Kacang tanah, buncis, kapri, cabe, tomat, terong, tembakau, ubi jalar, tanaman timun-timunan, singkong dan kubis.'],
                        ['Gejala kerusakan', 'Kerusakan daun mudah diamati pada bagian permukaan bawah di sekitar tulang daun. Tampak di sepanjang tulang daun, bekas tusukan Thrips berwarna coklat keperakan dan kering. Daun menjadi keriting, melengkung ke atas seperti mangkuk.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Sanitasi dari gulma dan tanaman inang.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Organofosfat: Profenofos',
                        'Golongan Neonikotinoid: Imidakloprid',
                        'Golongan Avermektin: Abamektin',
                        'Golongan Piretoid: Alfa Sipermetrin',
                        'Penyemprotan di permukaan bawah daun. Aplikasi rotasi bahan aktif berdasarkan golongan setiap 3 minggu sekali.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Potato tuber moths/PTM (Hama penggerek umbi kentang)',
                    'latin'   => 'Phthorimaea operculella',
                    'rincian' => [
                        ['Deskripsi umum', 'PTM menyerang di lahan dan di gudang. Ngengat dewasa berwarna abu-abu kecoklatan dengan panjang sekitar 10 mm. Larva berwarna putih pucat dengan garis-garis kehijauan atau kemerahan. PTM menyerang di area yang hangat dengan kondisi kering. PTM hidup baik pada suhu 27–35°C. Siklus PTM berkisar 20–25 hari.'],
                        ['Tanaman inang', 'Terong, tomat dan tembakau.'],
                        ['Gejala kerusakan', 'Larva PTM membuat lubang di batang dan daun, serta umbi di lahan. Kerusakan parah dapat terjadi di gudang dalam waktu yang relatif singkat. Umbi yang terinfestasi biasanya menunjukkan kotoran larva di lubang masuk.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Sanitasi dari gulma dan tanaman inang. Melakukan pembumbunan (menjaga umbi kentang selalu tertutup tanah), dan penyiraman untuk mencegah tanah retak-retak yang akan menjadi jalan masuk PTM ke umbi. Di gudang penyimpanan melakukan sanitasi termasuk lantai, dinding dan atap.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Metilkarbamat: metomil',
                        'Golongan Nereistoksin: kartap hidroklorida, … (teks terpotong di dokumen)',
                        'Golongan Avermektin: Abamektin',
                        'Aplikasi rotasi bahan aktif berdasarkan golongan setiap 3 minggu sekali.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                    'review' => 'Daftar bahan aktif golongan Nereistoksin terpotong di dokumen (setelah "kartap hidroklorida"), sehingga bagian yang terpotong tidak dimasukkan.',
                ],
                [
                    'nama'    => 'Cutworm (Ulat tanah)',
                    'latin'   => 'Agrotis spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Ulat tanah berwarna kehitaman, berbintik-bintik atau bergaris. Badannya lunak dengan panjang 3–5 cm. Ulat tanah memiliki pupa berwarna coklat.'],
                        ['Tanaman inang', 'Tomat, padi, jagung, tembakau, tebu, bawang, kubis, kentang.'],
                        ['Gejala kerusakan', 'Ulat tanah merusak pada fase vegetatif. Larva muda biasanya merusak bagian atas tanaman menyebabkan lubang-lubang pada daun. Larva dewasa bersembunyi di dalam tanah pada siang hari dan aktif merusak pada malam hari. Larva dewasa dapat merusak tanaman dengan memotong pada pangkal tanaman hingga terputus.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Pengolahan tanah yang baik untuk membunuh pupa yang ada di dalam tanah. Sanitasi dari gulma dan tanaman inang.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan piretoid: Sipermetrin pada tanah di sekeliling tanaman',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Uret',
                    'latin'   => 'Phyllophaga spp. dan spesies Scarabaeidae',
                    'rincian' => [
                        ['Deskripsi umum', 'Larva kumbang yang relatif besar, berwarna putih dengan ukuran hingga 5 cm. Uret ini memiliki tubuh melengkung dengan kaki di dada.'],
                        ['Tanaman inang', 'Tebu, jagung, padi gogo, singkong, kopi, tumbuhan semak.'],
                        ['Gejala kerusakan', 'Uret hidup di perakaran tanaman dan memakan akar dan umbi kentang. Sehingga daun menjadi layu, menguning, tanaman mudah roboh. Selain itu uret ini juga memakan umbi kentang sehingga umbi menjadi berlubang besar.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Pengolahan tanah yang dalam untuk membunuh larva, tanah dibalik dan terkena sinar matahari dan adanya predator lain akan mengurangi larva tersebut. Sanitasi dari gulma dan tanaman inang.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Metilkarbamat: karbofuran',
                        'Golongan Fenilfirazol: fipronil',
                        'Ditabur saat tanam di sekitar benih.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Anjing tanah (orong-orong)',
                    'latin'   => 'Gryllotalpa sp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Serangga berwarna coklat kehitaman menyerupai jangkrik dengan sepasang kaki depan yang kuat dan bergerigi, sifatnya sangat polifag, memakan akar, umbi, dan tanaman muda.'],
                        ['Tanaman inang', 'Cabai, tomat, terong, bayam, kangkung, paria, kacang panjang dan kentang.'],
                        ['Gejala kerusakan', 'Tanaman atau tangkai daun rebah, karena pangkalnya dipotong. Pada umbi kentang terdapat lubang-lubang.'],
                        ['Teknik pengendalian', 'Monitoring secara intensif untuk mencegah penyebaran. Pengolahan tanah yang dalam untuk membunuh larva yang ada di dalam tanah. Sanitasi dari gulma dan tanaman inang.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Insektisida', 'baris' => [
                        'Golongan Metilkarbamat: karbofuran',
                        'Golongan Fenilfirazol: fipronil',
                        'Ditabur saat tanam di sekitar benih.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
            ]],

            ['t' => 'organisme', 'judul' => 'B. Penyakit pada Tanaman Kentang — a) Disebabkan Bakteri', 'item' => [
                [
                    'nama'    => 'Bacterial wilt (Layu Bakteri)',
                    'latin'   => 'Ralstonia solanacearum',
                    'rincian' => [
                        ['Deskripsi umum', 'Tanaman layu secara mendadak tanpa diawali gejala kekuningan pada daun. Batang utama tampak hijau dan tegak, sedangkan tangkai dan helaian daun layu, namun masih berwarna hijau. Pada batang yang dibelah melintang tampak pembuluh angkut berwarna kecoklatan, bila dicelupkan dalam air jernih tampak masa bakteri seperti asap keluar dari potongan batang. Bakteri ini dapat bertahan di tanah dalam jangka waktu 2 tahun tanpa inang. Kondisi yang mendukung perkembangbiakan dan penyebaran adalah temperatur hangat dan kelembaban yang tinggi dan curah hujan yang banyak.'],
                        ['Tanaman inang', 'Cabai, tomat, terong, paria, buncis bunga marigold dan kentang.'],
                        ['Teknik pengendalian', 'Rotasi tanaman selain tanaman inang. Tindakan pencegahan dengan aplikasi belerang pada saat persiapan lahan. Pemakaian pupuk nitrogen secara berimbang, agar tanaman tidak terlalu sukulen. Melakukan sanitasi terhadap gulma atau tanaman sekunder yang dapat menjadi inang alternatif. Perbaikan drainase supaya lahan tidak tergenang. Mencabut dan memusnahkan tanaman yang layu (Roguing).'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Bakterisida', 'baris' => [
                        'dazomet, streptomycine sulfat, asam oksolinik, hidroklorida dan oksiterasiklin',
                        'Hanya untuk menghambat perkembangan bakteri.',
                    ]],
                    'review' => 'Daftar bakterisida memuat "hidroklorida" berdiri sendiri — mohon konfirmasi nama bahan aktif lengkapnya.',
                ],
                [
                    'nama'    => 'Blackleg (Busuk batang) & Soft rot (busuk lunak)',
                    'latin'   => 'Pectobacterium spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Blackleg mulai muncul di pangkal batang tanaman dan tampak berair, kemudian membesar dan berubah warna menjadi lebih gelap. Ketika kondisi basah, bintik-bintik ini akan berlendir. Ketika kondisi lebih kering, batangnya menjadi kering. Bakteri ini dapat menginfeksi lentisel ketika permukaan umbi basah. Busuk ini akan menyebar dengan cepat saat umbi dalam perjalanan dan penyimpanan. Umbi yang mengalami kerusakan mekanis, dan luka akibat hama lebih mudah terserang bakteri busuk lunak. Kondisi yang mendukung perkembangbiakan dan penyebaran adalah temperatur hangat dan kelembaban yang tinggi dan curah hujan yang tinggi.'],
                        ['Tanaman inang', 'Cabai, tomat, terong, dan kentang.'],
                        ['Teknik pengendalian', 'Rotasi tanaman selain tanaman inang. Tindakan pencegahan dengan aplikasi belerang pada saat persiapan lahan. Pemakaian pupuk nitrogen secara berimbang, agar tanaman tidak terlalu sukulen. Melakukan sanitasi terhadap gulma atau tanaman sekunder yang dapat menjadi inang alternatif. Perbaikan drainase supaya lahan tidak tergenang. Mencabut dan memusnahkan tanaman yang layu (Roguing). Umbi harus dalam kondisi kering di penyimpanan.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Bakterisida', 'baris' => [
                        'Dazomet, streptomycine sulfat, asam oksolinik, hidroklorida dan oksiterasiklin',
                        'Hanya untuk menghambat perkembangan bakteri.',
                    ]],
                ],
                [
                    'nama'    => 'Ring rot (busuk cincin)',
                    'latin'   => 'Clavibacter michiganensis ssp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Diawali tanaman layu di pertengahan musim. Daun bagian bawah menjadi lembek dengan warna kuning pucat di antara tulang daun, dan daun menggulung ke atas. Bagian batang dan umbi apabila dibelah menunjukkan lingkaran cincin pembuluh berwarna coklat, apabila diperas dapat memancarkan cairan bakteri berwarna putih susu. Ring rot ini ditularkan oleh umbi benih.'],
                        ['Tanaman inang', 'Cabai, tomat, terong, dan kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit dan benih tidak dipotong-potong. Rotasi tanaman selain tanaman inang. Melakukan sanitasi terhadap gulma atau tanaman sekunder yang dapat menjadi inang alternatif. Perbaikan drainase supaya lahan tidak tergenang. Mencabut dan memusnahkan tanaman yang layu (Roguing).'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Bakterisida', 'baris' => [
                        'dazomet, streptomycine sulfat, asam oksolinik, hidroklorida dan oksiterasiklin',
                        'Hanya untuk menghambat perkembangan bakteri.',
                    ]],
                ],
                [
                    'nama'    => 'Common scab (kudis bakteri)',
                    'latin'   => 'Streptomyces scabies',
                    'rincian' => [
                        ['Deskripsi umum', 'Pada permukaan umbi pertama-tama menampakkan spot seperti bengkak yang berwarna coklat kemerahan. Ini akan membesar dan berubah warna menjadi coklat, coklat muda dan keabu-abuan. Sekelilingnya membengkak dan tengahnya melekuk ke dalam, lalu menjadi luka yang bulat seperti bopeng. Jaringan permukaan luka bergabus, banyak timbul tonjolan kecil dan retakan kecil. Sampai saat ini belum muncul gejala di permukaan atas tanaman. Sumber utama penyakit ini adalah tanah yang terinfeksi. Itu ada sebagai organisme hidup yang menginfeksi jaringan melalui lentisel (pori-pori di permukaan umbi). Kondisi yang menguntungkan untuk perkembangan adalah kondisi kering, hangat, tanah alkali ringan.'],
                        ['Tanaman inang', 'Wortel dan kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Rotasi tanaman padi-padian dan kacang-kacangan. Hindari penggunaan tanah alkali dan pupuk alkali, jaga pH 5–5,2 dengan sulfur. Jaga kelembaban tanah yang tinggi selama 4–6 minggu di awal pembentukan umbi.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Bakterisida', 'baris' => ['— (tidak dicantumkan di dokumen)']],
                    'review' => 'Anjuran "jaga pH 5–5,2 dengan sulfur" berbeda dengan syarat pH tanah 6–6,5 pada bagian 3.1 dan 4.1.',
                ],
            ]],

            ['t' => 'organisme', 'judul' => 'B. Penyakit pada Tanaman Kentang — b) Disebabkan Jamur', 'item' => [
                [
                    'nama'    => 'Late Blight (hawar daun)',
                    'latin'   => 'Phytophthora infestans',
                    'rincian' => [
                        ['Deskripsi umum', 'Phytophthora infestans dapat menyerang daun, batang dan umbi. Tanaman yang terinfeksi ditandai dengan munculnya bercak lebar pada daun yang awalnya berupa bercak hijau pucat lama-kelamaan akan berwarna gelap. Dalam kondisi lembab, jamur putih seperti sporulasi terlihat, terutama di bawah permukaan daun. Umbi yang terinfeksi menunjukkan gejala bercak yang agak cekung berwarna coklat atau hitam-ungu. Penyakit ini dapat berkembang dengan baik pada suhu 10–25°C, disertai dengan kabut atau hujan.'],
                        ['Tanaman inang', 'Cabe, terong, tomat, semangka, mentimun, labu dan kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Penggunaan pupuk nitrogen yang tidak berlebihan. Rotasi tanaman yang bukan inangnya. Monitoring secara intensif untuk mencegah penyebaran. Memperbaiki drainase agar lahan tidak tergenang, memusnahkan bagian tanaman yang terinfeksi.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Fungisida', 'baris' => [
                        'Kode cara kerja M3: mancozeb, Ziram, Propineb',
                        'Kode cara kerja 40: dimetomorf',
                        'Kode cara kerja M5: klorotalonil',
                        'Kode cara kerja 27: simoksanil dll.',
                        'Rotasi berdasarkan kode cara kerja.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Early Blight (Alternaria)',
                    'latin'   => 'Alternaria solani',
                    'rincian' => [
                        ['Deskripsi umum', 'Pada umumnya timbul di daun, dimulai munculnya spot warna coklat tua-coklat kehitaman. Kemudian membesar dengan diameter 4–5 mm. Pada spot tersebut terdapat garis-garis berwarna coklat kehitaman yang berbentuk lingkaran konsentris dan ring spot, timbul jamur berbentuk bulu-bulu halus berwarna hitam.'],
                        ['Tanaman inang', 'Cabe, terong, tomat, semangka, mentimun, labu dan kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Penggunaan pupuk nitrogen yang tidak berlebihan. Rotasi tanaman yang bukan inangnya. Monitoring secara intensif untuk mencegah penyebaran. Memperbaiki drainase agar lahan tidak tergenang, memusnahkan bagian tanaman yang terinfeksi.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Fungisida', 'baris' => [
                        'Kode cara kerja 40: Dimetomoerf',
                        'Kode cara kerja 11: Piraklostrobin, Azoksistrobin',
                        'Rotasi berdasarkan kode cara kerja.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                    'review' => 'Penulisan bahan aktif "Dimetomoerf" ditampilkan apa adanya (di bagian Late Blight ditulis "dimetomorf").',
                ],
                [
                    'nama'    => 'Layu fusarium',
                    'latin'   => 'Fusarium spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Tanaman tiba-tiba tampak layu, dimulai dari daun-daun tua. Tulang-tulang daun berubah warna menjadi kuning. Apabila dibelah jaringan batang dan akar berwarna coklat. Kemudian tanaman layu secara keseluruhan, mengering lalu mati. Umbi yang terserang menunjukkan perubahan warna coklat cekung, nekrosis pada stolon atau mata tunas dan area yang busuk berbentuk coklat bundar.'],
                        ['Tanaman inang', 'Cabe, terong, tomat, paria, semangka, melon, kacang panjang dan kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Rotasi tanaman yang bukan inangnya. Monitoring secara intensif untuk mencegah penyebaran. Melakukan sanitasi terhadap gulma. Memperbaiki drainase agar lahan tidak tergenang, memusnahkan tanaman yang terinfeksi (Roguing).'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Fungisida', 'baris' => [
                        'Kode cara kerja 1: benomil',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Powdery scab',
                    'latin'   => 'Spongospora subterranea',
                    'rincian' => [
                        ['Deskripsi umum', 'Gejala tidak muncul di atas permukaan tanah. Spongospora subterranea menyerang umbi dengan gejala awal kecil, berwarna terang, terjadi pembengkakan di permukaan umbi. Semakin lama akan menjadi gelap, terbuka dengan diameter 2–10 mm atau lebih besar, mengandung spora berwarna coklat. Bentuk luka ini bervariasi, kebanyakan berbentuk bulat, dibatasi oleh kulit yang rusak. Kondisi yang menguntungkan untuk perkembangan adalah kondisi dingin, basah, tanah alkali dan irigasi berlebihan.'],
                        ['Tanaman inang', 'Kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Melakukan sanitasi terhadap gulma. Rotasi dengan tanaman panjang (5–6 tahun) seperti rumput dapat mengurangi penyakit. Meningkatkan drainase. Hindari penggunaan tanah alkali dan pupuk alkali, jaga pH 5–5,2 dengan sulfur.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Fungisida', 'baris' => [
                        'Fumigasi dengan metana natrium.',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                    'review' => 'Istilah "metana natrium" ditampilkan apa adanya — mohon konfirmasi nama bahan yang dimaksud. Anjuran pH 5–5,2 juga berbeda dengan syarat pH 6–6,5 (3.1/4.1).',
                ],
                [
                    'nama'    => 'Kanker batang dan busuk hitam',
                    'latin'   => 'Rhizoctonia solani',
                    'rincian' => [
                        ['Deskripsi umum', 'Jamur ini menyebabkan luka coklat berkarat pada batang bawah dan sclerotia hitam (jamur) pada umbi.'],
                        ['Tanaman inang', 'Kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas penyakit. Melakukan sanitasi terhadap gulma. Rotasi dengan tanaman 4 tahun. Memusnahkan tanaman yang terinfeksi (roguing). Tanam benih yang bersih tanpa kotoran. Hindari tanam yang terlalu dalam.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Fungisida', 'baris' => [
                        'Kode cara kerja 3: Triadimenol',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
            ]],

            ['t' => 'organisme', 'judul' => 'C. Virus', 'item' => [
                [
                    'nama'    => 'Potato leaf roll viruses (PLRV)',
                    'rincian' => [
                        ['Deskripsi umum', 'Gejalanya muncul sekitar 6 minggu setelah infeksi. Umbi dari tanaman yang terinfeksi tidak menunjukkan gejala tetapi akan menghasilkan tanaman yang sakit ketika ditanam. Gejala tanamannya yaitu daun menggulung ke atas, dari tepi ke tengah tulang daun, kadang-kadang menyerupai tabung. Daun terasa lebih kaku. Virus ini dapat menurunkan produksi hingga 90%.'],
                        ['Identifikasi', 'Identifikasi dapat dilakukan secara akurat dengan tes ELISA di laboratorium.'],
                        ['Penularan', 'Virus ini bersifat terbawa benih dan dapat ditularkan melalui kutu daun Aphids (Myzuz persicae) terutama bulan Juli dan Agustus. PLRV adalah virus persisten dan hanya dapat ditularkan oleh kutu yang memakan tanaman yang terinfeksi selama beberapa jam. Kemudian melewati sistem pencernaan sampai bereplikasi di kelenjar ludah. Aphid tetap menjadi pembawa selama sisa hidupnya.'],
                        ['Tanaman inang', 'Kentang.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas virus. Melakukan sanitasi terhadap gulma. Memusnahkan tanaman yang terinfeksi (Roguing). Pengendalian terhadap kutu daun yang merupakan pembawa virus.'],
                    ],
                    'review' => 'Nama Latin vektor ditulis "Myzuz persicae" di bagian ini (bagian Aphid menulis "Myzus persicae") — ditampilkan apa adanya.',
                ],
                [
                    'nama'    => 'Potato viruses Y and A (PVY and PVA)',
                    'rincian' => [
                        ['Deskripsi umum', 'Gejala penyakit yang disebabkan oleh virus ini sangat beragam, pada umumnya penyakit ini menyebabkan tanaman kerdil. Timbul belang-belang kekuningan (mozaik) pada helaian daun. Selanjutnya tulang daunnya menggulung, juga diikuti adanya garis-garis coklat pada batang dan tangkai daun, daun mudah gugur dan akhirnya mati. Virus ini dapat menurunkan produksi hingga 80%. PVA mirip dengan PVY, juga menyebabkan mozaik, kerutan pada daun. Gejala PVA lebih ringan dibandingkan PVY namun susah dibedakan. Kerugian bisa mencapai 40%.'],
                        ['Identifikasi', 'Identifikasi dapat dilakukan secara akurat dengan tes ELISA di laboratorium.'],
                        ['Penularan', 'Virus ini bersifat terbawa benih dan dapat ditularkan melalui kutu daun terutama bulan Juli - Agustus. Virus ini ditularkan secara non-persistant (dapat dilekatkan pada stylet kutu dan segera ditransfer ke tanaman berikutnya yang diberi kutu).'],
                        ['Tanaman inang', 'Kentang, tembakau, tomat, lada.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas virus. Melakukan sanitasi terhadap gulma. Memusnahkan tanaman yang terinfeksi (Roguing). Pengendalian terhadap kutu daun yang merupakan pembawa virus.'],
                    ],
                ],
                [
                    'nama'    => 'Mozaik',
                    'rincian' => [
                        ['Deskripsi umum', 'Gejala penyakit yang disebabkan oleh virus ini sangat beragam, belang-belang kekuningan, daun mengeriting, terjadi garis-garis nekrotik, permukaan daun agak kasar dan tidak rata serta daun gugur. Virus ini dapat menurunkan produksi hingga 10% lebih.'],
                        ['Identifikasi', 'Identifikasi dapat dilakukan secara akurat dengan tes ELISA di laboratorium.'],
                        ['Penularan', 'Virus ini bersifat terbawa benih dan dapat ditularkan melalui kutu daun dan secara mekanik melalui persinggungan tanaman sehat dengan yang sakit.'],
                        ['Tanaman inang', 'Kentang, tembakau, tomat, lada.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas virus. Melakukan sanitasi terhadap gulma. Memusnahkan tanaman yang terinfeksi (Roguing). Pengendalian terhadap kutu daun yang merupakan pembawa virus.'],
                    ],
                ],
            ]],

            ['t' => 'organisme', 'judul' => 'D. Nematoda', 'item' => [
                [
                    'nama'    => 'Nematoda Bintil Akar',
                    'latin'   => 'Meloidogyne spp.',
                    'rincian' => [
                        ['Deskripsi umum', 'Larva Meloidogyne menginfeksi akar dan umbi, akan menguras fotosintat tanaman dan nutrisi. Nematoda ini ditularkan melalui tanah yang telah terinfeksi, pupuk kandang, dan umbi bibit yang telah terinfeksi.'],
                        ['Tanaman inang', 'Tomat, terong, wortel, kentang.'],
                        ['Gejala kerusakan', 'Serangan nematoda ini mengakibatkan terjadinya benjolan-benjolan seperti jerawat pada permukaan umbi kentang. Jika serangan berat, pada perakaran terbentuk benjolan-benjolan yang tidak beraturan.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas nematoda. Melakukan sanitasi terhadap gulma. Memusnahkan tanaman yang terinfeksi.'],
                    ],
                    'bahan' => ['label' => 'Aplikasi Nematisida', 'baris' => [
                        'fluopyram disemprot ke benih dan karbofuran pada saat tanam',
                    ], 'catatan' => CATATAN_DOSIS_MERK],
                ],
                [
                    'nama'    => 'Nematoda Sista Kentang/NSK',
                    'latin'   => 'Globodera rostochiensis',
                    'rincian' => [
                        ['Deskripsi umum', 'Daur hidup NSK ini 5–7 minggu dan dapat menghasilkan 200–500 butir telur sepanjang hidupnya. Dalam kondisi tidak ada inang, suhu sangat rendah/tinggi atau kekeringan, NSK membentuk sista. NSK akan aktif kembali jika lingkungan sesuai. Sista dapat bertahan lebih dari 10 tahun.'],
                        ['Tanaman inang', 'Kentang.'],
                        ['Gejala kerusakan', 'Gejala diawali dengan pertumbuhan tanaman kerdil secara spot-spot, lama kelamaan spot tersebut meluas. Tanaman layu, pertumbuhan kerdil dan perkembangan akar terhambat.'],
                        ['Teknik pengendalian', 'Menggunakan benih bebas nematoda. Melakukan sanitasi terhadap gulma. Memusnahkan tanaman yang terinfeksi. Sebelum tanam dilakukan uji NSK pada lahan tersebut, dan jika sudah positif NSK maka lahan tersebut tidak ditanami.'],
                    ],
                ],
            ]],
        ],
    ],
];

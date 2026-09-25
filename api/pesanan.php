<?php
/* =========================================================
   API PESANAN
   POST          → buat pesanan dari keranjang (publik); stok dipotong
                   dalam transaksi yang sama. Idempoten per checkout_token.
   GET           → daftar pesanan + item (admin)
   PUT ?id=      → ubah status pesanan (admin): baru→diproses/batal,
                   diproses→selesai/batal; batal mengembalikan stok
                   (sekali saja, ditandai kolom stok_dipotong)
                   (status diproses/selesai membuka akses panduan)
   DELETE ?id=   → hapus pesanan (admin)
========================================================= */
declare(strict_types=1);
require __DIR__ . '/helpers.php';

const METODE_BAYAR = ['Transfer Bank', 'COD (Bayar di Tempat)', 'Bayar via WhatsApp'];
const STATUS_PESANAN = ['baru', 'diproses', 'selesai', 'batal'];
// Transisi status yang diizinkan; selesai & batal adalah status akhir
const TRANSISI_STATUS = [
    'baru'     => ['diproses', 'batal'],
    'diproses' => ['selesai', 'batal'],
    'selesai'  => [],
    'batal'    => [],
];
const MAKS_ITEM = 50;
const MAKS_JUMLAH = 1000;
const STOK_MAKS = 4294967295; // batas kolom produk.stok (INT UNSIGNED)

/** Deadlock / lock wait timeout: tidak ada yang tersimpan, aman diulang oleh client. */
function db_sibuk(PDOException $e): bool
{
    return in_array((int) ($e->errorInfo[1] ?? 0), [1205, 1213], true);
}

/** Pesanan yang sudah tersimpan dengan token checkout ini (untuk request yang diulang). */
function pesanan_dari_token(PDO $pdo, string $token): ?array
{
    $stmt = $pdo->prepare('SELECT id, kode_pesanan, total FROM pesanan WHERE checkout_token = ?');
    $stmt->execute([$token]);
    $pesanan = $stmt->fetch();
    if (!$pesanan) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT produk_id, nama_produk, harga, jumlah, subtotal FROM pesanan_item WHERE pesanan_id = ? ORDER BY id');
    $stmt->execute([$pesanan['id']]);
    return [
        'id'           => (int) $pesanan['id'],
        'kode_pesanan' => $pesanan['kode_pesanan'],
        'total'        => (int) $pesanan['total'],
        'items'        => array_map(fn($i) => [
            'produk_id' => $i['produk_id'] === null ? null : (int) $i['produk_id'],
            'nama'      => $i['nama_produk'],
            'harga'     => (int) $i['harga'],
            'jumlah'    => (int) $i['jumlah'],
            'subtotal'  => (int) $i['subtotal'],
        ], $stmt->fetchAll()),
    ];
}

$method = allow_methods('GET', 'POST', 'PUT', 'DELETE');
$pdo = db();

if ($method === 'POST') {
    $input = read_json();
    $v = new Validator($input);
    $nama   = $v->string('nama', 'Nama penerima', 100, true, 3);
    $alamat = $v->string('alamat', 'Alamat pengiriman', 255, true, 10);
    $wa     = $v->phone('wa');
    $metode = $v->in('metode', 'Metode pembayaran', METODE_BAYAR);

    // Gabungkan item dengan produk yang sama; harga TIDAK diambil dari frontend
    $jumlahPerProduk = [];
    $items = $input['items'] ?? null;
    if (!is_array($items) || count($items) === 0) {
        $v->addError('items', 'Keranjang masih kosong.');
    } elseif (count($items) > MAKS_ITEM) {
        $v->addError('items', 'Jumlah jenis produk terlalu banyak.');
    } else {
        foreach ($items as $item) {
            $produkId = is_array($item) ? ($item['produk_id'] ?? null) : null;
            $jumlah   = is_array($item) ? ($item['jumlah'] ?? null) : null;
            if (!is_int($produkId) || $produkId < 1 || !is_int($jumlah) || $jumlah < 1) {
                $v->addError('items', 'Data item keranjang tidak valid.');
                break;
            }
            $jumlahPerProduk[$produkId] = ($jumlahPerProduk[$produkId] ?? 0) + $jumlah;
            if ($jumlahPerProduk[$produkId] > MAKS_JUMLAH) {
                $v->addError('items', 'Jumlah per produk maksimal ' . MAKS_JUMLAH . '.');
                break;
            }
        }
    }
    // Token acak dari browser per percobaan checkout (lihat pesanSekarang di index.html)
    $token = $input['checkout_token'] ?? null;
    if (!is_string($token) || !preg_match('/^[0-9a-f]{32}$/', $token)) {
        $v->addError('checkout_token', 'Sesi checkout tidak valid. Silakan muat ulang halaman.');
    }
    $v->check();

    // Request ulang dengan token yang sama (double-click, retry setelah timeout)
    // mengembalikan pesanan yang sudah tersimpan, bukan membuat pesanan baru.
    if ($sudahAda = pesanan_dari_token($pdo, $token)) {
        ok('Pesanan sudah tersimpan sebelumnya.', $sudahAda);
    }

    ksort($jumlahPerProduk);
    $ids = array_keys($jumlahPerProduk);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $kode = 'PSN-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    $pdo->beginTransaction();
    try {
        // Kunci baris produk, urut id agar dua checkout tidak saling deadlock.
        // Checkout lain untuk produk yang sama menunggu sampai transaksi ini selesai,
        // sehingga stok & harga yang dibaca di bawah tidak bisa berubah sebelum commit.
        $stmt = $pdo->prepare("SELECT id, nama, harga, satuan, stok, aktif FROM produk WHERE id IN ($placeholders) ORDER BY id FOR UPDATE");
        $stmt->execute($ids);
        $produkMap = [];
        foreach ($stmt->fetchAll() as $row) {
            $produkMap[(int) $row['id']] = $row;
        }

        // Cek ulang setelah lock: request kembar yang tadi menunggu lock
        // sekarang melihat pesanan yang baru saja di-commit request pertama.
        if ($sudahAda = pesanan_dari_token($pdo, $token)) {
            $pdo->rollBack();
            ok('Pesanan sudah tersimpan sebelumnya.', $sudahAda);
        }

        // Validasi SEMUA item dulu; satu item gagal → tidak ada yang disimpan
        foreach ($jumlahPerProduk as $produkId => $jumlah) {
            $produk = $produkMap[$produkId] ?? null;
            if (!$produk || (int) $produk['aktif'] !== 1) {
                $pdo->rollBack();
                fail('Sebagian produk di keranjang sudah tidak tersedia. Silakan muat ulang halaman.', 422);
            }
            $stok = (int) $produk['stok'];
            if ($jumlah > $stok) {
                $pdo->rollBack();
                fail($stok === 0
                    ? "Stok {$produk['nama']} sedang habis. Silakan hapus dari keranjang."
                    : "Stok {$produk['nama']} tidak mencukupi (tersisa {$stok} {$produk['satuan']}).", 422);
            }
        }

        // Harga dari baris yang sedang dikunci, bukan dari frontend
        $total = 0;
        $detail = [];
        foreach ($jumlahPerProduk as $produkId => $jumlah) {
            $produk = $produkMap[$produkId];
            $subtotal = (int) $produk['harga'] * $jumlah;
            $total += $subtotal;
            $detail[] = [
                'produk_id' => $produkId,
                'nama'      => $produk['nama'],
                'harga'     => (int) $produk['harga'],
                'jumlah'    => $jumlah,
                'subtotal'  => $subtotal,
            ];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO pesanan (kode_pesanan, nama_penerima, alamat, no_wa, metode_bayar, total, stok_dipotong, checkout_token)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->execute([$kode, $nama, $alamat, $wa, $metode, $total, $token]);
        $pesananId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO pesanan_item (pesanan_id, produk_id, nama_produk, harga, jumlah, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($detail as $d) {
            $stmt->execute([$pesananId, $d['produk_id'], $d['nama'], $d['harga'], $d['jumlah'], $d['subtotal']]);
        }

        // Syarat stok >= jumlah menjamin stok tidak pernah < 0
        // (sql_mode tidak strict, jadi jangan mengandalkan error dari MySQL)
        $potong = $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?');
        foreach ($jumlahPerProduk as $produkId => $jumlah) {
            $potong->execute([$jumlah, $produkId, $jumlah]);
            if ($potong->rowCount() !== 1) {
                throw new RuntimeException("Stok produk #{$produkId} gagal dipotong.");
            }
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // 1062: token sudah dipakai request kembar yang commit lebih dulu
        $duplikat = (int) ($e->errorInfo[1] ?? 0) === 1062;
        if ($duplikat && ($sudahAda = pesanan_dari_token($pdo, $token))) {
            ok('Pesanan sudah tersimpan sebelumnya.', $sudahAda);
        }
        // Tabrakan kode pesanan / lock: tidak ada yang tersimpan, aman diulang dengan token yang sama
        if ($duplikat || db_sibuk($e)) {
            fail('Sistem sedang sibuk. Silakan coba lagi.', 409);
        }
        throw $e;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    ok('Pesanan berhasil disimpan.', [
        'id'           => $pesananId,
        'kode_pesanan' => $kode,
        'total'        => $total,
        'items'        => $detail,
    ], 201);
}

require_admin();

if ($method === 'GET') {
    $pesanan = $pdo->query(
        'SELECT id, kode_pesanan, nama_penerima, alamat, no_wa, metode_bayar, total, status, created_at
         FROM pesanan ORDER BY created_at DESC, id DESC LIMIT 500'
    )->fetchAll();

    $itemsByPesanan = [];
    if ($pesanan) {
        $ids = array_column($pesanan, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT pesanan_id, produk_id, nama_produk, harga, jumlah, subtotal
             FROM pesanan_item WHERE pesanan_id IN ($placeholders) ORDER BY id"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $item) {
            $itemsByPesanan[(int) $item['pesanan_id']][] = [
                'produk_id' => $item['produk_id'] === null ? null : (int) $item['produk_id'],
                'nama'      => $item['nama_produk'],
                'harga'     => (int) $item['harga'],
                'jumlah'    => (int) $item['jumlah'],
                'subtotal'  => (int) $item['subtotal'],
            ];
        }
    }

    ok('Data pesanan berhasil dimuat.', array_map(fn($p) => [
        'id'           => (int) $p['id'],
        'kode_pesanan' => $p['kode_pesanan'],
        'nama'         => $p['nama_penerima'],
        'alamat'       => $p['alamat'],
        'wa'           => $p['no_wa'],
        'metode'       => $p['metode_bayar'],
        'total'        => (int) $p['total'],
        'status'       => $p['status'],
        'created_at'   => $p['created_at'],
        'items'        => $itemsByPesanan[(int) $p['id']] ?? [],
    ], $pesanan));
}

if ($method === 'PUT') {
    $id = query_id();
    $v = new Validator(read_json());
    $status = $v->in('status', 'Status pesanan', STATUS_PESANAN);
    $v->check();

    $pdo->beginTransaction();
    try {
        // Kunci baris pesanan: request lain untuk pesanan ini (mis. dua pembatalan
        // bersamaan) menunggu di sini, lalu membaca status & stok_dipotong terbaru.
        $stmt = $pdo->prepare('SELECT status, stok_dipotong FROM pesanan WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $pesanan = $stmt->fetch();
        if (!$pesanan) {
            $pdo->rollBack();
            fail('Pesanan tidak ditemukan.', 404);
        }
        $statusLama = $pesanan['status'];
        if ($statusLama === $status) {
            // Request yang diulang (retry setelah timeout, klik ganda): tidak ada perubahan
            $pdo->rollBack();
            ok('Pesanan sudah berstatus ' . ucfirst($status) . '.', [
                'id' => $id, 'status' => $status, 'status_sebelumnya' => $statusLama, 'stok_dikembalikan' => [],
            ]);
        }
        if (!in_array($status, TRANSISI_STATUS[$statusLama], true)) {
            $pdo->rollBack();
            fail(sprintf('Status pesanan tidak dapat diubah dari %s menjadi %s.', ucfirst($statusLama), ucfirst($status)), 409);
        }

        // Stok hanya dikembalikan jika memang pernah dipotong dan belum dikembalikan.
        // Pesanan lama (sebelum checkout memotong stok) bernilai 0 → tidak ada yang ditambahkan.
        $kembalikanStok = $status === 'batal' && (int) $pesanan['stok_dipotong'] === 1;
        $dikembalikan = [];
        if ($kembalikanStok) {
            $stmt = $pdo->prepare(
                'SELECT produk_id, SUM(jumlah) AS jumlah FROM pesanan_item
                 WHERE pesanan_id = ? AND produk_id IS NOT NULL GROUP BY produk_id ORDER BY produk_id'
            );
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
            if ($items) {
                $ids = array_map(fn($i) => (int) $i['produk_id'], $items);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                // Urutan lock sama dengan checkout (id naik) agar tidak deadlock
                $stmt = $pdo->prepare("SELECT id FROM produk WHERE id IN ($placeholders) ORDER BY id FOR UPDATE");
                $stmt->execute($ids);
                $ada = array_flip(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));

                // Batas atas dicek manual karena sql_mode tidak strict (nilai berlebih akan dipotong diam-diam)
                $tambah = $pdo->prepare('UPDATE produk SET stok = stok + ? WHERE id = ? AND stok <= ? - ?');
                foreach ($items as $item) {
                    $produkId = (int) $item['produk_id'];
                    $jumlah = (int) $item['jumlah'];
                    if (!isset($ada[$produkId])) {
                        continue; // produk sudah dihapus, tidak ada stok untuk dikembalikan
                    }
                    $tambah->execute([$jumlah, $produkId, STOK_MAKS, $jumlah]);
                    if ($tambah->rowCount() !== 1) {
                        // Satu gagal → rollback semua, status pesanan tetap seperti semula
                        throw new RuntimeException("Stok produk #{$produkId} gagal dikembalikan.");
                    }
                    $dikembalikan[] = ['produk_id' => $produkId, 'jumlah' => $jumlah];
                }
            }
        }

        // Status & penanda stok berubah dalam transaksi yang sama dengan pengembalian stok
        $stmt = $pdo->prepare('UPDATE pesanan SET status = ?, stok_dipotong = ? WHERE id = ?');
        $stmt->execute([$status, $kembalikanStok ? 0 : (int) $pesanan['stok_dipotong'], $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof PDOException && db_sibuk($e)) {
            fail('Pesanan sedang diproses oleh request lain. Silakan muat ulang data.', 409);
        }
        throw $e;
    }
    ok('Status pesanan berhasil diperbarui.', [
        'id' => $id, 'status' => $status, 'status_sebelumnya' => $statusLama, 'stok_dikembalikan' => $dikembalikan,
    ]);
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM pesanan WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Pesanan tidak ditemukan.', 404);
    }
    ok('Pesanan berhasil dihapus.');
}

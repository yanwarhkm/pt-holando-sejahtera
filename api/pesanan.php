<?php
/* =========================================================
   API PESANAN
   POST          → buat pesanan dari keranjang (publik)
   GET           → daftar pesanan + item (admin)
   PUT ?id=      → ubah status pesanan (admin): baru→diproses/batal,
                   diproses→selesai/batal
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
    $v->check();

    $ids = array_keys($jumlahPerProduk);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nama, harga, satuan, stok FROM produk WHERE aktif = 1 AND id IN ($placeholders)");
    $stmt->execute($ids);
    $produkMap = [];
    foreach ($stmt->fetchAll() as $row) {
        $produkMap[(int) $row['id']] = $row;
    }
    if (count($produkMap) !== count($ids)) {
        fail('Sebagian produk di keranjang sudah tidak tersedia. Silakan muat ulang halaman.', 422);
    }

    // Stok dicek dari database, bukan dari keranjang di browser
    foreach ($jumlahPerProduk as $produkId => $jumlah) {
        $produk = $produkMap[$produkId];
        $stok = (int) $produk['stok'];
        if ($jumlah > $stok) {
            fail($stok === 0
                ? "Stok {$produk['nama']} sedang habis. Silakan hapus dari keranjang."
                : "Stok {$produk['nama']} tidak mencukupi (tersisa {$stok} {$produk['satuan']}).", 422);
        }
    }

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

    $kode = 'PSN-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO pesanan (kode_pesanan, nama_penerima, alamat, no_wa, metode_bayar, total)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$kode, $nama, $alamat, $wa, $metode, $total]);
        $pesananId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO pesanan_item (pesanan_id, produk_id, nama_produk, harga, jumlah, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($detail as $d) {
            $stmt->execute([$pesananId, $d['produk_id'], $d['nama'], $d['harga'], $d['jumlah'], $d['subtotal']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
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

    $stmt = $pdo->prepare('SELECT status FROM pesanan WHERE id = ?');
    $stmt->execute([$id]);
    $statusLama = $stmt->fetchColumn();
    if ($statusLama === false) {
        fail('Pesanan tidak ditemukan.', 404);
    }
    if (!in_array($status, TRANSISI_STATUS[$statusLama], true)) {
        fail(sprintf('Status pesanan tidak dapat diubah dari %s menjadi %s.', ucfirst($statusLama), ucfirst($status)), 409);
    }

    // Syarat status lama di WHERE mencegah dua admin menimpa perubahan satu sama lain
    $stmt = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ? AND status = ?');
    $stmt->execute([$status, $id, $statusLama]);
    if ($stmt->rowCount() === 0) {
        fail('Status pesanan baru saja berubah. Silakan muat ulang data.', 409);
    }
    ok('Status pesanan berhasil diperbarui.', ['id' => $id, 'status' => $status, 'status_sebelumnya' => $statusLama]);
}

if ($method === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM pesanan WHERE id = ?');
    $stmt->execute([query_id()]);
    if ($stmt->rowCount() === 0) {
        fail('Pesanan tidak ditemukan.', 404);
    }
    ok('Pesanan berhasil dihapus.');
}

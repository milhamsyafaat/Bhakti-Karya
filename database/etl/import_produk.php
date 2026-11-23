<?php
/**
 * ETL: Impor produk dari Database_Bhaktikarya.xlsx (sheet "Produk") ke tabel `produk`.
 *
 * Jalankan dari root project:
 *   php database/etl/import_produk.php
 *
 * Tanpa dependensi eksternal — membaca .xlsx sebagai arsip ZIP + parsing XML.
 * Idempoten: aman dijalankan berulang (INSERT ... ON DUPLICATE KEY UPDATE).
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

// ---------------------------------------------------------------------
// Muat .env sederhana
// ---------------------------------------------------------------------
function loadEnv(string $file): array
{
    $env = [];
    if (!is_file($file)) {
        return $env;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $v = trim($v);
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'")) {
            $v = substr($v, 1, -1);
        }
        $env[trim($k)] = $v;
    }
    return $env;
}

$env = loadEnv($root . '/.env');
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_PORT'] ?? '3306',
    $env['DB_DATABASE'] ?? 'bhaktikarya_grosir'
);

try {
    $pdo = new PDO($dsn, $env['DB_USERNAME'] ?? 'root', $env['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Koneksi DB gagal: {$e->getMessage()}\n");
    exit(1);
}

// ---------------------------------------------------------------------
// Baca XLSX
// ---------------------------------------------------------------------
$xlsx = $root . '/Database_Bhaktikarya.xlsx';
if (!is_file($xlsx)) {
    fwrite(STDERR, "File tidak ditemukan: {$xlsx}\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($xlsx) !== true) {
    fwrite(STDERR, "Gagal membuka xlsx.\n");
    exit(1);
}

/** @return array<int,string> daftar shared string */
function parseSharedStrings(ZipArchive $zip): array
{
    $out = [];
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
        return $out;
    }
    $sx = new SimpleXMLElement($xml);
    foreach ($sx->si as $si) {
        $text = '';
        if (isset($si->t)) {
            $text = (string) $si->t;
        } elseif (isset($si->r)) {
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
        }
        $out[] = $text;
    }
    return $out;
}

/** Konversi "AB12" -> index kolom 0-based */
function colToIndex(string $ref): int
{
    $letters = preg_replace('/[0-9]/', '', $ref);
    $idx = 0;
    foreach (str_split($letters) as $ch) {
        $idx = $idx * 26 + (ord($ch) - 64);
    }
    return $idx - 1;
}

/** @return array<int, array<int,string>> baris sheet (0-based kolom) */
function parseSheet(ZipArchive $zip, string $sheetPath, array $shared): array
{
    $xml = $zip->getFromName($sheetPath);
    if ($xml === false) {
        return [];
    }
    $sx = new SimpleXMLElement($xml);
    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $c) {
            $ref = (string) $c['r'];
            $ci = colToIndex($ref);
            $type = (string) $c['t'];
            $val = '';
            if ($type === 's') {
                $val = $shared[(int) $c->v] ?? '';
            } elseif ($type === 'inlineStr') {
                $val = (string) $c->is->t;
            } else {
                $val = isset($c->v) ? (string) $c->v : '';
            }
            $cells[$ci] = $val;
        }
        if (!empty($cells)) {
            $max = max(array_keys($cells));
            $line = [];
            for ($i = 0; $i <= $max; $i++) {
                $line[$i] = $cells[$i] ?? '';
            }
            $rows[] = $line;
        }
    }
    return $rows;
}

$shared = parseSharedStrings($zip);
$rows = parseSheet($zip, 'xl/worksheets/sheet1.xml', $shared);
$zip->close();

if (count($rows) < 2) {
    fwrite(STDERR, "Sheet Produk kosong.\n");
    exit(1);
}

// ---------------------------------------------------------------------
// Pemetaan header -> index
// ---------------------------------------------------------------------
$header = array_map('trim', $rows[0]);
$col = array_flip($header);
$get = function (array $row, string $name, $default = '') use ($col) {
    if (!isset($col[$name])) {
        return $default;
    }
    $v = $row[$col[$name]] ?? '';
    return $v === '' ? $default : $v;
};

$toInt   = fn ($v, $d = 0) => is_numeric($v) ? (int) round((float) $v) : $d;
$toFloat = fn ($v, $d = 0.0) => is_numeric($v) ? (float) $v : $d;

$badges = ['Standar', 'Terlaris', 'Komoditas', 'Promo'];

// Validasi kategori yang ada
$validKat = array_map('intval', $pdo->query('SELECT id FROM kategori')->fetchAll(PDO::FETCH_COLUMN));
$validKat = array_flip($validKat);

$sql = 'INSERT INTO `produk`
    (`id`,`nama_produk`,`kategori_id`,`harga`,`harga_lama`,`stok`,`badge_status`,`deskripsi`,
     `gambar`,`is_favorit`,`harga_beli`,`harga_reseller`,`penjualan`,`alias`,`satuan`,`min_order`,`aktif`)
    VALUES (:id,:nama,:kat,:harga,:hargalama,:stok,:badge,:deskripsi,:gambar,:favorit,
            :beli,:reseller,:jual,:alias,:satuan,:minorder,:aktif)
    ON DUPLICATE KEY UPDATE
        `nama_produk`=VALUES(`nama_produk`),`kategori_id`=VALUES(`kategori_id`),
        `harga`=VALUES(`harga`),`harga_lama`=VALUES(`harga_lama`),`stok`=VALUES(`stok`),
        `badge_status`=VALUES(`badge_status`),`deskripsi`=VALUES(`deskripsi`),
        `gambar`=VALUES(`gambar`),`is_favorit`=VALUES(`is_favorit`),
        `satuan`=VALUES(`satuan`),`min_order`=VALUES(`min_order`),`aktif`=VALUES(`aktif`)';
$stmt = $pdo->prepare($sql);

$inserted = 0;
$skipped = 0;
$pdo->beginTransaction();
try {
    foreach (array_slice($rows, 1) as $row) {
        $nama = trim((string) $get($row, 'nama_produk'));
        if ($nama === '') {
            $skipped++;
            continue;
        }

        $kat = $toInt($get($row, 'kategori_id'), 0);
        if (!isset($validKat[$kat])) {
            $kat = null;
        }

        $badge = (string) $get($row, 'badge_status', 'Standar');
        if (!in_array($badge, $badges, true)) {
            $badge = 'Standar';
        }

        // satuan: abaikan nilai numerik (data kotor dari Excel)
        $satuan = (string) $get($row, 'satuan', 'pcs');
        if ($satuan === '' || is_numeric($satuan)) {
            $satuan = 'pcs';
        }

        $stmt->execute([
            ':id'        => $toInt($get($row, 'id'), 0) ?: null,
            ':nama'      => $nama,
            ':kat'       => $kat,
            ':harga'     => $toFloat($get($row, 'harga'), 0),
            ':hargalama' => $toFloat($get($row, 'harga_lama'), 0) ?: null,
            ':stok'      => $toInt($get($row, 'stok'), 0),
            ':badge'     => $badge,
            ':deskripsi' => (string) $get($row, 'deskripsi', ''),
            ':gambar'    => (string) $get($row, 'gambar', ''),
            ':favorit'   => $toInt($get($row, 'is_favorit'), 0) ? 1 : 0,
            ':beli'      => $toFloat($get($row, 'harga_beli'), 0),
            ':reseller'  => $toFloat($get($row, 'harga_reseller'), 0),
            ':jual'      => $toInt($get($row, 'penjualan'), 0),
            ':alias'     => (string) $get($row, 'alias', ''),
            ':satuan'    => $satuan,
            ':minorder'  => max(1, $toInt($get($row, 'min_order'), 1)),
            ':aktif'     => $toInt($get($row, 'aktif'), 1) ? 1 : 0,
        ]);
        $inserted++;
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Gagal impor: {$e->getMessage()}\n");
    exit(1);
}

$total = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
echo "ETL selesai.\n";
echo "  Baris diproses : {$inserted}\n";
echo "  Dilewati       : {$skipped}\n";
echo "  Total produk   : {$total}\n";

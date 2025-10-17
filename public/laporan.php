<?php
require_once __DIR__ . '/../vendor/autoload.php';
include '../src/config.php';

include '../template/navbar.php';

// Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Get filters from GET
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';

// SQL with joins to get related data and base_id (id_transaksi without last char)
$sql = "SELECT 
            t.id_transaksi,
            LEFT(t.id_transaksi, LENGTH(t.id_transaksi) - 1) AS base_id,
            t.id_pembeli,
            p.nama_pembeli,
            t.id_barang,
            b.nama_barang,
            b.harga,
            t.jumlah,
            t.total_harga,
            t.tanggal
        FROM transaksi t
        LEFT JOIN pembeli p ON t.id_pembeli = p.id_pembeli
        LEFT JOIN barang b ON t.id_barang = b.id_barang";

// Conditions array and params for prepared statement
$conditions = [];
$params = [];
$types = '';

// Keyword filter: search buyer or item name
if (!empty($keyword)) {
    $conditions[] = "(p.nama_pembeli LIKE ? OR b.nama_barang LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types .= "ss";
}

// Date filter
if (!empty($filter_date)) {
    $conditions[] = "DATE(t.tanggal) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

// Add WHERE clause if needed
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Order by newest transaction date
$sql .= " ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Group transactions by base_id
$grouped = [];

while ($row = $result->fetch_assoc()) {
    $base_id = $row['base_id'];

    if (!isset($grouped[$base_id])) {
        // Fetch pembayaran & kembalian from detail_transaksi where id_detail = base_id
        $pembayaran = 0;
        $kembalian = 0;

        $detail_stmt = $conn->prepare("SELECT pembayaran, kembalian FROM detail_transaksi WHERE id_detail = ?");
        $detail_stmt->bind_param("s", $base_id);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();
        if ($detail_row = $detail_result->fetch_assoc()) {
            $pembayaran = $detail_row['pembayaran'];
            $kembalian = $detail_row['kembalian'];
        }
        $detail_stmt->close();

        $grouped[$base_id] = [
            'id_pembeli' => $row['id_pembeli'],
            'nama_pembeli' => $row['nama_pembeli'],
            'items' => [],
            'total_harga' => 0,
            'pembayaran' => $pembayaran,
            'kembalian' => $kembalian,
            'tanggal' => $row['tanggal'],
        ];
    }

    $grouped[$base_id]['items'][] = [
        'id_barang' => $row['id_barang'],
        'nama_barang' => $row['nama_barang'],
        'harga' => $row['harga'],
        'jumlah' => $row['jumlah'],
        'subtotal' => $row['total_harga'],
    ];

    $grouped[$base_id]['total_harga'] += $row['total_harga'];

    // Optional: update date if newer
    if ($row['tanggal'] > $grouped[$base_id]['tanggal']) {
        $grouped[$base_id]['tanggal'] = $row['tanggal'];
    }
}


// Generate PDF if requested
// Generate PDF if requested
if (isset($_GET['print']) && $_GET['print'] == 1) {
    ob_start();
    ?>
    <html lang="id">
    <head>
        <meta charset="UTF-8" />
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid black;
                padding: 5px;
            }
            th {
                background-color: #f2f2f2;
            }
            td {
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <h2 style="text-align: center;">Laporan Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>ID Pembeli</th>
                    <th>Nama Pembeli</th>
                    <th>Items (ID - Nama - Harga x Jumlah = Subtotal)</th>
                    <th>Total Harga</th>
                    <th>Pembayaran</th>
                    <th>Kembalian</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grouped)): ?>
                    <?php foreach ($grouped as $base_id => $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($base_id) ?></td>
                            <td><?= htmlspecialchars($data['id_pembeli']) ?></td>
                            <td><?= htmlspecialchars($data['nama_pembeli']) ?></td>
                            <td>
                                <?php
                                    foreach ($data['items'] as $item) {
                                        echo htmlspecialchars($item['id_barang']) . " - " . htmlspecialchars($item['nama_barang']) .
                                            " = Rp " . number_format($item['harga'], 0, ',', '.') . " x " . $item['jumlah'] .
                                            " = Rp " . number_format($item['subtotal'], 0, ',', '.') . "<br>";
                                    }
                                ?>
                            </td>
                            <td>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($data['pembayaran'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($data['kembalian'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">Belum ada data transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans'); // for better character support

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');

    // Clean any previous output buffer before rendering PDF
    if (ob_get_length()) {
        ob_end_clean();
    }

    $dompdf->render();

    // Stream PDF inline in browser, attachment false
    $dompdf->stream('laporan_transaksi.pdf', ['Attachment' => false]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Laporan Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">

<!-- Search and filter form -->
<form method="GET" class="d-flex mb-3" role="search">
    <input class="form-control me-2" type="text" name="keyword" placeholder="Cari Nama Pembeli atau Barang..." value="<?= htmlspecialchars($keyword) ?>" />
    <button class="btn btn-primary me-2" type="submit">Cari</button>

    <input class="form-control me-2" type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>" />
    <button class="btn btn-success me-2" type="submit">Filter by Date</button>

    <a href="laporan.php?print=1&keyword=<?= urlencode($keyword) ?>&filter_date=<?= urlencode($filter_date) ?>" class="btn btn-danger mb-3">Cetak PDF</a>

    <?php if ($keyword || $filter_date): ?>
        <a href="laporan.php" class="btn btn-secondary mb-3">Reset</a>
    <?php endif; ?>
</form>

<!-- Transactions table -->
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID Transaksi</th>
            <th>ID Pembeli</th>
            <th>Nama Pembeli</th>
            <th>Items (ID - Nama - Harga x Jumlah = Subtotal)</th>
            <th>Total Harga</th>
            <th>Tanggal Transaksi</th>
            <th>Pembayaran</th>
            <th>Kembalian</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($grouped)): ?>
            <?php foreach ($grouped as $base_id => $data): ?>
                <tr>
                    <td><?= htmlspecialchars($base_id) ?></td>
                    <td><?= htmlspecialchars($data['id_pembeli']) ?></td>
                    <td><?= htmlspecialchars($data['nama_pembeli']) ?></td>
                    <td>
                        <?php
                            foreach ($data['items'] as $item) {
                                echo htmlspecialchars($item['id_barang']) . " - " . htmlspecialchars($item['nama_barang']) . " = Rp " . number_format($item['harga'], 0, ',', '.') . " x " . $item['jumlah'] . " = Rp " . number_format($item['subtotal'], 0, ',', '.') . "<br>";
                            }
                        ?>
                    </td>
                    <td>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($data['tanggal']) ?></td>
                    <td>Rp <?= number_format($data['pembayaran'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($data['kembalian'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center">Belum ada data transaksi</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</div>
</body>
</html>

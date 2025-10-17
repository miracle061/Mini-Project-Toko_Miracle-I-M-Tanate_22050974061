<?php
include '../src/config.php';      // DB connection
include '../template/header.php';
include '../template/navbar.php';

// --- Fetch summary metrics ---

// Total Barang
$sql = "SELECT COUNT(*) AS total_barang FROM barang";
$result = $conn->query($sql);
$total_barang = $result->fetch_assoc()['total_barang'];

// Total Pembeli
$sql = "SELECT COUNT(*) AS total_pembeli FROM pembeli";
$result = $conn->query($sql);
$total_pembeli = $result->fetch_assoc()['total_pembeli'];

// Total Transaksi
$sql = "SELECT COUNT(*) AS total_transaksi FROM transaksi";
$result = $conn->query($sql);
$total_transaksi = $result->fetch_assoc()['total_transaksi'];

// Total Pendapatan
$sql = "SELECT SUM(total_harga) AS total_pendapatan FROM transaksi";
$result = $conn->query($sql);
$total_pendapatan = $result->fetch_assoc()['total_pendapatan'];

// Barang Terlaris
$sql = "SELECT b.nama_barang, SUM(t.jumlah) AS total_sold
        FROM transaksi t
        JOIN barang b ON t.id_barang = b.id_barang
        GROUP BY t.id_barang
        ORDER BY total_sold DESC
        LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$top_barang = $row['nama_barang'] ?? '-';
$top_sold = $row['total_sold'] ?? 0;
?>

<!-- DASHBOARD CARDS -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Barang</h5>
                    <p class="card-text"><?php echo $total_barang; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Pembeli</h5>
                    <p class="card-text"><?php echo $total_pembeli; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Transaksi</h5>
                    <p class="card-text"><?php echo $total_transaksi; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan</h5>
                    <p class="card-text"><?php echo $total_pendapatan; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Barang Terlaris</h5>
                    <p class="card-text"><?php echo $top_barang; ?> (<?php echo $top_sold; ?> sold)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BARANG TABLE -->
<div class="container mt-4">
    <h2>Daftar Barang</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM barang";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$row['id_barang']."</td>";
                        echo "<td>".$row['nama_barang']."</td>";
                        echo "<td>".$row['harga']."</td>";
                        echo "<td>".$row['stok']."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PEMBELI TABLE -->
<div class="container mt-4">
    <h2>Daftar Pembeli</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID Pembeli</th>
                    <th>Nama Pembeli</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM pembeli";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$row['id_pembeli']."</td>";
                        echo "<td>".$row['nama_pembeli']."</td>";
                        echo "<td>".$row['alamat']."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Tidak ada data</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- TRANSAKSI TABLE -->
<div class="container mt-4">
    <h2>Daftar Transaksi</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID Transaksi</th>
                    <th>Nama Pembeli</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT t.id_transaksi, p.nama_pembeli, b.nama_barang, t.jumlah, t.total_harga, t.tanggal
                        FROM transaksi t
                        JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                        JOIN barang b ON t.id_barang = b.id_barang";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$row['id_transaksi']."</td>";
                        echo "<td>".$row['nama_pembeli']."</td>";
                        echo "<td>".$row['nama_barang']."</td>";
                        echo "<td>".$row['jumlah']."</td>";
                        echo "<td>".$row['total_harga']."</td>";
                        echo "<td>".$row['tanggal']."</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Tidak ada data</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../template/footer.php'; ?>


<?php
include '../src/config.php';


// Fungsi AGGREGATE


// 1️⃣ Total Transaksi
$sql_transaksi = "SELECT COUNT(*) AS total_transaksi FROM transaksi";
$total_transaksi = $conn->query($sql_transaksi)->fetch_assoc()['total_transaksi'];

// 2️⃣ Total Pendapatan
$sql_pendapatan = "SELECT COALESCE(SUM(total_harga), 0) AS total_pendapatan FROM transaksi";
$total_pendapatan = $conn->query($sql_pendapatan)->fetch_assoc()['total_pendapatan'];

// 3️⃣ Barang Terlaris
$sql_terlaris = "
    SELECT b.nama_barang, SUM(t.jumlah) AS total_terjual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY b.id_barang
    ORDER BY total_terjual DESC
    LIMIT 1
";
$result_terlaris = $conn->query($sql_terlaris);

if ($result_terlaris->num_rows > 0) {
    $barang_terlaris = $result_terlaris->fetch_assoc()['nama_barang'];
} else {
    $barang_terlaris = "-";
}
?>

<h2>📊 Dashboard Penjualan</h2>

<form>
    <label>Total Transaksi:</label><br>
    <input type="text" value="<?php echo $total_transaksi; ?>" readonly><br><br>

    <label>Total Pendapatan:</label><br>
    <input type="text" value="Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?>" readonly><br><br>

    <label>Barang Terlaris:</label><br>
    <input type="text" value="<?php echo $barang_terlaris; ?>" readonly><br><br>
</form>

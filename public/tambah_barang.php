<?php
include '../src/config.php';
include '../template/header.php';
include '../template/navbar.php';

// Handle form submission
if (isset($_POST['simpan'])) {
    $id_barang = $_POST['id_barang'];
    $nama_barang = strtoupper($_POST['nama_barang']); // uppercase
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

     if ($harga < 0 || $stok < 0) {
        echo "<div class='alert alert-warning'>Harga dan stok tidak boleh kurang dari 0!</div>";
    } else {
        $sql = "INSERT INTO barang (id_barang, nama_barang, harga, stok) 
                VALUES ('$id_barang', '$nama_barang', $harga, $stok)";

        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>Barang berhasil ditambahkan!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<h2>Daftar Barang</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nama Barang</th>
        <th>Harga</th>
        <th>Stok</th>
    </tr>
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
</table>

<h2>Tambah Barang Baru</h2>
<form method="POST">
    <input type="text" name="id_barang" placeholder="ID Barang (misal: BR01)" required>
    <input type="text" name="nama_barang" placeholder="Nama Barang" required>
    <input type="number" name="harga" placeholder="Harga" required>
    <input type="number" name="stok" placeholder="Stok" required>
    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
</form>

<?php include '../template/footer.php'; ?>

<?php
include '../src/config.php';
include '../template/header.php';
include '../template/navbar.php';

// Handle form submission
if (isset($_POST['simpan'])) {
    $id_pembeli= $_POST['id_pembeli'];
    $nama_pembeli = strtoupper($_POST['nama_pembeli']); // uppercase
    $alamat = strtoupper($_POST['alamat']); // make uppercase

    $sql = "INSERT INTO pembeli (id_pembeli, nama_pembeli, alamat) VALUES ('$id_pembeli','$nama_pembeli', '$alamat')";
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>pembeli berhasil ditambahkan!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<h2>Daftar pembeli</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nama pembeli</th>
        <th>alamat</th>
    </tr>
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
        echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
    }
    ?>
</table>

<h2>Tambah pembeli Baru</h2>
<form method="POST">
    <input type="text" name="id_pembeli" placeholder="ID pembeli" required>
    <input type="text" name="nama_pembeli" placeholder="Nama pembeli" required>
    <input type="text" name="alamat" placeholder="alamat" required>
    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
</form>

<?php include '../template/footer.php'; ?>

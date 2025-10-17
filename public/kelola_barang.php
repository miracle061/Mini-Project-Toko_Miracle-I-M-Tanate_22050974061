<?php include '../template/header.php'; ?>
<?php include '../template/navbar.php'; ?>

<?php // include database connection
include '../src/config.php';
?>

<div class="container mt-4">
<?php

if(isset($_POST['update'])){
    $errors = []; // <== initialize error array

    foreach($_POST['nama_barang'] as $id => $nama){
        $harga = $_POST['harga'][$id];
        $stok  = $_POST['stok'][$id];

        // Validation
        $nama = strtoupper(trim($nama));  // nama barang uppercase + trim
        if($stok < 0) $stok = 0;          // stok cannot be negative

        if ($nama === '' || $harga === '' || $stok === '') {
            $errors[] = "Data kosong pada ID: <strong>$id</strong>. Pastikan nama, harga, dan stok tidak kosong.";
            continue;
        }

        // Escape values
        $id_escaped = $conn->real_escape_string($id);
        $nama_escaped = $conn->real_escape_string($nama);
        $harga_escaped = $conn->real_escape_string($harga);
        $stok_escaped = $conn->real_escape_string($stok);

        $sql_update = "UPDATE barang 
                       SET nama_barang='$nama_escaped', harga='$harga_escaped', stok='$stok_escaped' 
                       WHERE id_barang='$id_escaped'";
        $conn->query($sql_update);
    }

    // Show error messages if any
    if (!empty($errors)) {
        echo "<div class='alert alert-danger'>";
        echo "<ul>";
        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>Semua perubahan berhasil diperbarui!</div>";
    }
}


if (isset($_POST['hapus'])) {
    if (!empty($_POST['delete_ids'])) {
        // Convert array of IDs into comma-separated string
        $ids = '"' . implode('","', $_POST['delete_ids']) . '"';

        // Check if any selected barang has transaksi
        $sql_check = "SELECT COUNT(*) AS total FROM transaksi WHERE id_barang IN ($ids)";
        $result = $conn->query($sql_check);
        $row = $result->fetch_assoc();

        if ($row['total'] > 0) {
            // Block deletion if any transaksi exists
            echo "<div class='alert alert-warning'>Barang yang dipilih tidak bisa dihapus karena sudah memiliki transaksi!</div>";
        } else {
            // Safe to delete
            $sql_delete = "DELETE FROM barang WHERE id_barang IN ($ids)";
            if ($conn->query($sql_delete)) {
                echo "<div class='alert alert-success'>Data berhasil dihapus!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }
    } else {
        echo "<div class='alert alert-warning'>Pilih minimal satu data untuk dihapus.</div>";
    }
}
?>


<a href="tambah_barang.php" class="btn btn-success mb-3">Tambah barang</a>


<!-- BARANG TABLE -->
<form method="POST" action="">
    <table class="table table-striped table-bordered">
   <?php
        $sql = "SELECT * FROM barang";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $id = $row['id_barang'];
                echo "<tr>";
                echo "<td><input type='checkbox' name='delete_ids[]' value='$id'></td>";
                echo "<td>$id</td>";
                echo "<td><input type='text' name='nama_barang[$id]' value='".$row['nama_barang']."' class='form-control'></td>";
                echo "<td><input type='number' step='0.01' name='harga[$id]' value='".$row['harga']."' class='form-control'></td>";
                echo "<td><input type='number' name='stok[$id]' value='".$row['stok']."' class='form-control'></td>";
                echo "</tr>";
            }
        }
        ?>
    </table>


    <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
    <button type="submit" name="update" class="btn btn-primary">Update</button>
</form>
</div>

<?php include '../template/footer.php'; ?>




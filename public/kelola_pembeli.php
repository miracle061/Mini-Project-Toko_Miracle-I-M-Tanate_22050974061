<?php include '../template/header.php'; ?>
<?php include '../template/navbar.php'; ?>

<?php // include database connection
include '../src/config.php';
?>

<div class="container mt-4">
<?php

if(isset($_POST['update'])){
    $errors = []; // collect validation errors

    foreach($_POST['nama_pembeli'] as $id => $nama){
        $alamat = strtoupper(trim($_POST['alamat'][$id])); // uppercase + trim
        $nama = strtoupper(trim($nama));

        // Check for empty fields
        if ($nama === '' || $alamat === '') {
            $errors[] = "Data kosong pada ID: <strong>$id</strong>. Nama dan alamat tidak boleh kosong.";
            continue;
        }

        // Sanitize
        $id_escaped = $conn->real_escape_string($id);
        $nama_escaped = $conn->real_escape_string($nama);
        $alamat_escaped = $conn->real_escape_string($alamat);

        // Update query
        $sql_update = "UPDATE pembeli SET nama_pembeli='$nama_escaped', alamat='$alamat_escaped' WHERE id_pembeli='$id_escaped'";
        $conn->query($sql_update);
    }

    // Show messages
    if (!empty($errors)) {
        echo "<div class='alert alert-danger'><ul>";
        foreach($errors as $err){
            echo "<li>$err</li>";
        }
        echo "</ul></div>";
    } else {
        echo "<div class='alert alert-success'>Semua perubahan berhasil diperbarui!</div>";
    }
}


if (isset($_POST['hapus'])) { 
if (!empty($_POST['delete_ids'])) {
    // Escape and quote each id_pembeli properly
    $ids_array = array_map(function($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'";
    }, $_POST['delete_ids']);

    $ids = implode(',', $ids_array); // comma separated with quotes

    $sql_check = "SELECT COUNT(*) AS total FROM transaksi WHERE id_pembeli IN ($ids)";
    $result = $conn->query($sql_check);

    if (!$result) {
        die("Query error: " . $conn->error);
    }

    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        echo "<div class='alert alert-warning'>pembeli yang dipilih tidak bisa dihapus karena sudah memiliki transaksi!</div>";
    } else {
        $sql_delete = "DELETE FROM pembeli WHERE id_pembeli IN ($ids)";
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


<a href="tambah_pembeli.php" class="btn btn-success mb-3">Tambah pembeli</a>


<!-- pembeli TABLE -->
<form method="POST" action="">
    <table class="table table-striped table-bordered">
   <?php
        $sql = "SELECT * FROM pembeli";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $id = $row['id_pembeli'];
                echo "<tr>";
                echo "<td><input type='checkbox' name='delete_ids[]' value='$id'></td>";
                echo "<td>$id</td>";
                echo "<td><input type='text' name='nama_pembeli[$id]' value='".$row['nama_pembeli']."' class='form-control'></td>";
                echo "<td><input type='text' name='alamat[$id]' value='".$row['alamat']."' class='form-control'></td>";
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




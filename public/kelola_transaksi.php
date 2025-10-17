<?php include '../template/header.php'; ?>
<?php include '../template/navbar.php'; ?>

<?php // include database connection
include '../src/config.php';
?>

<div class="container mt-4">
<?php

if(isset($_POST['update'])){
    foreach($_POST['jumlah'] as $id => $jumlah){
        $id_pembeli = $_POST['id_pembeli'][$id];
        $id_barang  = $_POST['id_barang'][$id];
        $tanggal    = $_POST['tanggal'][$id]; // ✅ now defined

        // Fetch harga of the barang
        $res = $conn->query("SELECT harga FROM barang WHERE id_barang='$id_barang'");
        $harga = $res->fetch_assoc()['harga'];
        $total_harga = $harga * $jumlah;

        // Update query
        $sql_update = "UPDATE transaksi
                       SET id_pembeli='$id_pembeli',
                           id_barang='$id_barang',
                           jumlah=$jumlah,
                           total_harga=$total_harga,
                           tanggal='$tanggal'
                       WHERE id_transaksi='$id'";
        
        $conn->query($sql_update);
    }
    echo "<div class='alert alert-success'>Semua perubahan transaksi berhasil diperbarui!</div>";
}

if (isset($_POST['hapus'])) {
    if (!empty($_POST['delete_ids'])) {
        foreach ($_POST['delete_ids'] as $id) {
            // Match format like TR07A, TR23B, etc.
            if (preg_match('/^(TR\d{2})[A-Z]$/', $id, $matches)) {
                $base = $matches[1]; // e.g., TR07

                // Generate TR07A to TR07Z
                $letters = range('A', 'Z');
                $ids_to_delete = array_map(fn($l) => $base . $l, $letters);
                $id_list = '"' . implode('","', $ids_to_delete) . '"';

                // Delete all matching transaksi
                $conn->query("DELETE FROM transaksi WHERE id_transaksi IN ($id_list)");

                // Optional: also delete from detail_transaksi if needed
                $conn->query("DELETE FROM detail_transaksi WHERE id_detail = '$base'");
            } else {
                // Fallback: not a TRxxA format, just delete the specific one
                $conn->query("DELETE FROM transaksi WHERE id_transaksi = '$id'");
            }
        }

        echo "<div class='alert alert-success'>Data transaksi berhasil dihapus!</div>";
    } else {
        echo "<div class='alert alert-warning'>Pilih minimal satu transaksi untuk dihapus.</div>";
    }
}
?>

<form method="POST" action="">
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>ID Transaksi</th>
            <th>Pembeli</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
        </tr>
    </thead>

<?php
$sql = "SELECT t.*, b.nama_barang, p.nama_pembeli, b.harga
        FROM transaksi t
        JOIN barang b ON t.id_barang=b.id_barang
        JOIN pembeli p ON t.id_pembeli=p.id_pembeli";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $id = $row['id_transaksi'];

    // Calculate total_harga (optional if trigger exists)
    $total_harga = $row['harga'] * $row['jumlah'];

    echo "<tr>";
    echo "<td><input type='checkbox' name='delete_ids[]' value='$id'></td>";
    echo "<td>$id</td>";

    // Pembeli select
    echo "<td><select name='id_pembeli[$id]' class='form-select'>";
    $sql_pembeli = "SELECT * FROM pembeli";
    $res_p = $conn->query($sql_pembeli);
    while($p = $res_p->fetch_assoc()){
        $selected = ($p['id_pembeli']==$row['id_pembeli']) ? 'selected' : '';
        echo "<option value='".$p['id_pembeli']."' $selected>".$p['nama_pembeli']."</option>";
    }
    echo "</select></td>";

    // Barang select
    echo "<td><select name='id_barang[$id]' class='form-select'>";
    $sql_barang = "SELECT * FROM barang";
    $res_b = $conn->query($sql_barang);
    while($b = $res_b->fetch_assoc()){
        $selected = ($b['id_barang']==$row['id_barang']) ? 'selected' : '';
        echo "<option value='".$b['id_barang']."' $selected>".$b['nama_barang']."</option>";
    }
    echo "</select></td>";

    // Jumlah editable
    echo "<td><input type='number' name='jumlah[$id]' value='".$row['jumlah']."' min='1' class='form-control'></td>";

    echo "<td>$total_harga</td>"; // readonly, auto-calculated
    echo "<td><input type='date' name='tanggal[$id]' value='".$row['tanggal']."' class='form-control'></td>";

    echo "</tr>";
}
?>
</table>

<button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
<button type="submit" name="update" class="btn btn-primary">Update</button>
</form>
</div>

<?php include '../template/footer.php'; ?>

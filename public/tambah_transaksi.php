<?php
session_start();
include '../template/header.php'; 
include '../template/navbar.php';

include '../src/config.php';

// =======================
// INITIAL SETUP
// ========================
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['current_pembeli'])) $_SESSION['current_pembeli'] = null;

// ========================
// STEP 1: PILIH PEMBELI
// ========================
if (isset($_POST['pilih_pembeli'])) {
    $_SESSION['current_pembeli'] = $_POST['id_pembeli'];
    $_SESSION['cart'] = []; // reset cart if pembeli changes
}

if (isset($_POST['reset_pembeli'])) {
    $_SESSION['current_pembeli'] = null;
    $_SESSION['cart'] = [];
    header("Location: tambah_transaksi.php");
    exit;
}

// ========================
// STEP 2: TAMBAH BARANG
// ========================
$barang_result = $conn->query("SELECT * FROM barang");
if (isset($_POST['tambah'])) {
    if (!$_SESSION['current_pembeli']) {
        echo "<div style='color:red;'>⚠️ Pilih pembeli terlebih dahulu!</div>";
    } else {
        $id_pembeli = $_SESSION['current_pembeli'];
        $id_barang = $_POST['id_barang'];
        $jumlah = $_POST['jumlah'];

        // Ambil data barang dari DB
        $barang = $conn->query("SELECT nama_barang, harga, stok FROM barang WHERE id_barang = '$id_barang'")->fetch_assoc();

        // Hitung jumlah barang yang sama dalam cart
        $jumlah_dalam_cart = 0;
        foreach ($_SESSION['cart'] as $item) {
            if ($item['id_barang'] == $id_barang) {
                $jumlah_dalam_cart += $item['jumlah'];
            }
        }

        // Hitung stok tersisa
        $stok_tersisa = $barang['stok'] - $jumlah_dalam_cart;

        if ($jumlah > $stok_tersisa) {
            echo "<div style='color:red;'>❌ Jumlah melebihi stok tersedia ($stok_tersisa)!</div>";
        } else {
            $total = $barang['harga'] * $jumlah;

            $found = false;
            
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id_barang'] == $id_barang) {
        // Update jumlah & total
        $item['jumlah'] += $jumlah;
        $item['total'] = $item['jumlah'] * $item['harga'];
        $found = true;
        break;
    }
}
unset($item); 

if (!$found) {
    // Add new item
    $_SESSION['cart'][] = [
        'id_pembeli' => $id_pembeli,
        'id_barang'  => $id_barang,
        'nama_barang'=> $barang['nama_barang'],
        'jumlah'     => $jumlah,
        'harga'      => $barang['harga'],
        'total'      => $total
    ];
}
        }
    }
}

// ========================
// STEP 3: HAPUS ITEM
// ========================
if (isset($_POST['hapus_item'])) {
    $index = $_POST['hapus_index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
    }
}

// ========================
// STEP 4: FINALISASI (BAYAR)
// ========================
if (isset($_POST['bayar'])) {
    if ($_SESSION['current_pembeli'] && !empty($_SESSION['cart'])) {

        $pembayaran = $_POST['pembayaran'];
        $total_semua = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_semua += $item['total'];
        }
        $kembalian = $pembayaran - $total_semua;

        if ($pembayaran < $total_semua) {
            echo "<div style='color:red;'>❌ Pembayaran kurang!</div>";
        } else {

            // ===== Generate New Base Transaction ID (e.g. TR01, TR02) =====
            $sql_last = "SELECT id_transaksi FROM transaksi ORDER BY id_transaksi DESC LIMIT 1";
            $result_last = $conn->query($sql_last);

            if ($result_last && $row_last = $result_last->fetch_assoc()) {
                // Extract number part (ignore letters like A, B)
                preg_match('/TR(\d+)/', $row_last['id_transaksi'], $matches);
                $last_num = isset($matches[1]) ? (int)$matches[1] : 0;
                $next_num = $last_num + 1;
            } else {
                $next_num = 1; // start from TR01
            }

            $kode_base = "TR" . str_pad($next_num, 2, "0", STR_PAD_LEFT);
            $alphabet = 'A';

            foreach ($_SESSION['cart'] as $item) {
                $id_transaksi = $kode_base . $alphabet; // TR07A, TR07B, etc.
                $id_pembeli = $_SESSION['current_pembeli'];
                $id_barang  = $item['id_barang'];
                $jumlah     = $item['jumlah'];
                $total      = $item['total'];
                $tanggal = isset($_POST['tanggal_transaksi']) && !empty($_POST['tanggal_transaksi']) ? $_POST['tanggal_transaksi'] : date('Y-m-d');


                $conn->query("UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
               $conn->query("INSERT INTO transaksi (id_transaksi, id_pembeli, id_barang, jumlah, total_harga, tanggal)
               VALUES ('$id_transaksi', '$id_pembeli', '$id_barang', $jumlah, $total, '$tanggal')");

                $alphabet++;

                // Save summary in detail_transaksi
            $id_detail = $kode_base;
            $tanggal = date('Y-m-d');
            // 2. Insert into detail_transaksi (item-level detail, based on your table)
            $conn->query("INSERT INTO detail_transaksi (id_detail, id_transaksi, id_barang, jumlah, total_harga, pembayaran, kembalian, tanggal)
                  VALUES ('$kode_base', '$id_transaksi', '$id_barang', $jumlah, $total, $pembayaran, $kembalian, '$tanggal')");


            }
            $_SESSION['cart'] = [];
            $_SESSION['current_pembeli'] = null;
            echo "<div style='color:green;'>✅ Transaksi berhasil disimpan!</div>";
        }

    } else {
        echo "<div style='color:red;'>⚠️ Tidak ada transaksi untuk disimpan!</div>";
    }
}

?>

<!-- ======================== -->
<!-- FORM PILIH PEMBELI -->
<!-- ======================== -->
<h2>Tambah Transaksi</h2>

<form method="POST" style="margin-bottom: 20px;">
    <?php if (!$_SESSION['current_pembeli']): ?>
        <label>Pilih Pembeli:</label>
        <select name="id_pembeli" required>
            <option value="">-- Pilih Pembeli --</option>
            <?php
            $pembeli = $conn->query("SELECT * FROM pembeli");
            while ($p = $pembeli->fetch_assoc()) {
                echo "<option value='{$p['id_pembeli']}'>{$p['id_pembeli']} - {$p['nama_pembeli']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="pilih_pembeli">Pilih</button>

    <?php else: ?>
    <?php
    if ($_SESSION['current_pembeli']) {
        $id = $conn->real_escape_string($_SESSION['current_pembeli']); // safe string
$result = $conn->query("SELECT nama_pembeli FROM pembeli WHERE id_pembeli = '$id'");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<strong>Pembeli saat ini:</strong> {$row['nama_pembeli']}";
        } else {
            echo "<div style='color:red;'>⚠️ Pembeli tidak ditemukan!</div>";
            $_SESSION['current_pembeli'] = null;
        }
    }
    ?>
    <button type="submit" name="reset_pembeli">Ganti Pembeli</button>
<?php endif; ?>

</form>

<!-- ======================== -->
<!-- FORM TAMBAH BARANG -->
<!-- ======================== -->
<?php if ($_SESSION['current_pembeli']): ?>
<form method="POST">
    <label>Barang:</label>
    <select name="id_barang" id="id_barang" required>
        <option value="">-- Pilih Barang --</option>
        <?php
        
        while ($b = $barang_result->fetch_assoc()) {
            // Hitung stok tersisa dari cart
            $jumlah_dalam_cart = 0;
            foreach ($_SESSION['cart'] as $item) {
                if ($item['id_barang'] == $b['id_barang']) {
                    $jumlah_dalam_cart += $item['jumlah'];
                }
            }

            $stok_tersisa = $b['stok'] - $jumlah_dalam_cart;
            if ($stok_tersisa < 0) $stok_tersisa = 0;

            echo "<option value='{$b['id_barang']}' data-stok='{$stok_tersisa}'>
                    {$b['id_barang']} - {$b['nama_barang']} (Stok: {$stok_tersisa})
                  </option>";
        }
        ?>
    </select>

    <label>Stok Tersedia:</label>
    <input type="text" id="stok" readonly value="">

    <label>Jumlah:</label>
    <input type="number" name="jumlah" min="1" required>

    <button type="submit" name="tambah">Tambah</button>
</form>
<?php endif; ?>

<hr>

<!-- ======================== -->
<!-- TABEL TRANSAKSI SEMENTARA -->
<!-- ======================== -->
<h2>Daftar Transaksi Sementara</h2>

<form method="POST">
    <table border="1" cellpadding="5">
        <tr>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>

        <?php
        $total_semua = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $index => $item) {
                echo "<tr>
                        <td>{$item['nama_barang']}</td>
                        <td>{$item['jumlah']}</td>
                        <td>{$item['harga']}</td>
                        <td>{$item['total']}</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='hapus_index' value='$index'>
                                <button type='submit' name='hapus_item' onclick=\"return confirm('Hapus item ini?')\">🗑️</button>
                            </form>
                        </td>
                      </tr>";
                $total_semua += $item['total'];
            }
            echo "<tr>
                    <td colspan='3'><strong>Total</strong></td>
                    <td colspan='2'><strong>$total_semua</strong></td>
                  </tr>";
                  echo "<tr>
        <td colspan='3'><strong>Pembayaran</strong></td>
        <td colspan='2'><input type='number' name='pembayaran' id='pembayaran' min='$total_semua' required></td>
         </tr>";
        echo "<tr>
        <td colspan='3'><strong>Kembalian</strong></td>
        <td colspan='2'><input type='text' id='kembalian' readonly></td>
      </tr>";

        } else {
            echo "<tr><td colspan='5'>Belum ada transaksi sementara</td></tr>";
        }
        ?>
    </table>

    <br>
    <tr>
    <td colspan="3"><strong>Tanggal Transaksi</strong></td>
    <td colspan="2">
        <input type="date" name="tanggal_transaksi" 
               value="<?= date('Y-m-d') ?>" required 
               max="<?= date('Y-m-d') ?>" />
    </td>
</tr>

    <button type="submit" name="bayar" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
        Bayar
    </button>
</form>
<?php include '../template/footer.php'; ?>

<!-- ======================== -->
<!-- SCRIPT: UPDATE STOK -->
<!-- ======================== -->
<script>
document.getElementById('id_barang')?.addEventListener('change', function(){
    var stok = this.options[this.selectedIndex].getAttribute('data-stok');
    document.getElementById('stok').value = stok ? stok : '';
});
document.getElementById('pembayaran')?.addEventListener('input', function(){
    const total = <?php echo $total_semua; ?>;
    const bayar = parseFloat(this.value) || 0;
    const kembali = bayar - total;
    document.getElementById('kembalian').value = kembali >= 0 ? kembali : 0;
});
</script>

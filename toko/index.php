<?php
// Koneksi ke database
$host = 'localhost'; // alamat server database Anda
$user = 'root'; // username database Anda
$password = ''; // password database Anda
$dbname = 'toko';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses penambahan produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_produk'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $gambar = $_POST['gambar'];

    $stmt = $conn->prepare("INSERT INTO produk (nama, harga, gambar) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $nama, $harga, $gambar);
    $stmt->execute();
}

// Proses transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transaksi'])) {
    $grand_total = 0;

    // Ambil produk dan jumlah dari form
    $produk_ids = $_POST['produk_id'];
    $quantities = $_POST['quantity'];

    // Hitung grand total
    foreach ($produk_ids as $key => $produk_id) {
        $quantity = $quantities[$key];

        // Hanya proses jika produk dipilih dan quantity lebih dari 0
        if (!empty($produk_id) && $quantity > 0) {
            $sql = "SELECT * FROM produk WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $produk_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $total = $row['harga'] * $quantity;
                $grand_total += $total;
            }
        }
    }

    // Insert ke tabel transaksi
    $stmt = $conn->prepare("INSERT INTO transaksi (grand_total, tgl_trx) VALUES (?, NOW())");
    $stmt->bind_param("d", $grand_total);
    $stmt->execute();

    // Insert ke tabel produk_terjual
    $last_trx_id = $stmt->insert_id; // Ambil ID transaksi yang baru saja dimasukkan
    foreach ($produk_ids as $key => $produk_id) {
        $quantity = $quantities[$key];

        if (!empty($produk_id) && $quantity > 0) {
            $sql = "SELECT * FROM produk WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $produk_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $total = $row['harga'] * $quantity;

                // Insert ke tabel produk_terjual
                $stmt = $conn->prepare("INSERT INTO produk_terjual (id_trx, nama, harga, quantity, grand_total) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isidd", $last_trx_id, $row['nama'], $row['harga'], $quantity, $total);
                $stmt->execute();
            }
        }
    }

    echo "<p>Transaksi berhasil! Total: Rp " . number_format($grand_total, 2) . "</p>";
}

// Ambil semua transaksi
$transaksi_sql = "SELECT * FROM transaksi";
$transaksi_result = $conn->query($transaksi_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Toko</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Daftar Produk</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Gambar</th>
        </tr>
        <?php
        $produk_sql = "SELECT * FROM produk";
        $produk_result = $conn->query($produk_sql);

        while ($row = $produk_result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nama']}</td>
                <td>Rp " . number_format($row['harga'], 2) . "</td>
                <td><img src='{$row['gambar']}' alt='{$row['nama']}' width='100'></td>
            </tr>";
        }
        ?>
    </table>

    <h2>Tambah Produk</h2>
    <form action="" method="post">
        <label for="nama">Nama Produk:</label>
        <input type="text" name="nama" required>
        <label for="harga">Harga:</label>
        <input type="number" step="0.01" name="harga" required>
        <label for="gambar">URL Gambar:</label>
        <input type="text" name="gambar" required>
        <button type="submit" name="add_produk">Tambah Produk</button>
    </form>

    <h2>Proses Transaksi</h2>
    <form action="" method="post">
        <table>
            <tr>
                <th>Pilih</th>
                <th>ID</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Jumlah</th>
            </tr>
            <?php
            $produk_result->data_seek(0); // Reset pointer hasil produk
            while ($row = $produk_result->fetch_assoc()) {
                echo "<tr>
                    <td><input type='checkbox' name='produk_id[]' value='{$row['id']}'></td>
                    <td>{$row['id']}</td>
                    <td>{$row['nama']}</td>
                    <td>Rp " . number_format($row['harga'], 2) . "</td>
                    <td><input type='number' name='quantity[]' min='1' value='1'></td>
                </tr>";
            }
            ?>
        </table>
        <button type="submit" name="transaksi">Proses Transaksi</button>
    </form>

    <h2>Daftar Transaksi</h2>
    <table>
        <tr>
            <th>ID Transaksi</th>
            <th>Tanggal Transaksi</th>
            <th>Grand Total</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $transaksi_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id_trx']; ?></td>
            <td><?php echo $row['tgl_trx']; ?></td>
            <td>Rp <?php echo number_format($row['grand_total'], 2); ?></td>
            <td>
                <form action="" method="post" target="_blank">
                    <input type="hidden" name="trx_id" value="<?php echo $row['id_trx']; ?>">
                    <button type="submit" name="cetak_struk">Cetak Struk</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <?php
    // Cetak struk
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cetak_struk'])) {
        $trx_id = $_POST['trx_id'];
        echo "<script>
                window.open('cetak_struk.php?id_trx=' + $trx_id, '_blank');
              </script>";
    }
    ?>
</body>
</html>

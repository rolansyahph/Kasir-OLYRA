<?php
include 'db.php';

// Proses penambahan atau pengeditan produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $gambar = $_POST['gambar'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Edit produk
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE produk SET nama = ?, harga = ?, gambar = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $nama, $harga, $gambar, $id);
        $stmt->execute();
    } else {
        // Tambah produk
        $stmt = $conn->prepare("INSERT INTO produk (nama, harga, gambar) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $nama, $harga, $gambar);
        $stmt->execute();
    }
}

// Proses penghapusan produk
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Ambil daftar produk
$produkList = [];
$result = $conn->query("SELECT * FROM produk");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produkList[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Kasir</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Kasir</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="kasir.php">Kasir</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Tambah/Edit Produk</h2>
        <form method="POST">
            <input type="hidden" name="id" id="product-id">
            <div class="form-group">
                <label>Nama:</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Harga:</label>
                <input type="number" name="harga" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Gambar URL:</label>
                <input type="text" name="gambar" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>

        <h2>Daftar Produk</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produkList as $produk): ?>
                <tr>
                    <td><?php echo htmlspecialchars($produk['nama']); ?></td>
                    <td><?php echo $produk['harga']; ?></td>
                    <td><img src="<?php echo htmlspecialchars($produk['gambar']); ?>" alt="<?php echo htmlspecialchars($produk['nama']); ?>" width="50"></td>
                    <td>
                        <button class="btn btn-warning" onclick="editProduct('<?php echo htmlspecialchars($produk['id']); ?>', '<?php echo htmlspecialchars($produk['nama']); ?>', <?php echo $produk['harga']; ?>, '<?php echo htmlspecialchars($produk['gambar']); ?>')">Edit</button>
                        <a href="?delete_id=<?php echo $produk['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function editProduct(id, name, price, image) {
            document.getElementById('product-id').value = id;
            document.querySelector('input[name="nama"]').value = name;
            document.querySelector('input[name="harga"]').value = price;
            document.querySelector('input[name="gambar"]').value = image;
        }
    </script>
</body>

</html>

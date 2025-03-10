<?php
include 'db.php';

// Proses penambahan atau pengeditan produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    // Menghilangkan pemisah ribuan untuk harga
    $harga = str_replace('.', '', $_POST['harga']); 
    $gambar = $_POST['gambar']; // URL gambar

    // Proses upload gambar jika ada file yang diupload
    if (isset($_FILES['gambar_upload']) && $_FILES['gambar_upload']['error'] == 0) {
        // Jika ada file yang di-upload
        $uploadDir = 'img/';
        $fileName = basename($_FILES['gambar_upload']['name']);
        $filePath = $uploadDir . $fileName;

        // Jika ada gambar lama yang di-upload sebelumnya, hapus gambar tersebut
        if ($gambar && !filter_var($gambar, FILTER_VALIDATE_URL)) {
            if (file_exists($gambar)) {
                unlink($gambar); // Hapus gambar lama
            }
        }

        // Upload gambar baru
        if (move_uploaded_file($_FILES['gambar_upload']['tmp_name'], $filePath)) {
            $gambar = $filePath; // Gunakan path gambar yang di-upload
        }
    }

    // Edit produk
    if (isset($_POST['id']) && $_POST['id'] != '') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE produk SET nama = ?, harga = ?, gambar = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $nama, $harga, $gambar, $id);
        $stmt->execute();
    } else {
        // Tambah produk baru
        $stmt = $conn->prepare("INSERT INTO produk (nama, harga, gambar) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $nama, $harga, $gambar);
        $stmt->execute();
    }
}

// Proses penghapusan produk
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $gambar = $row['gambar'];
        // Hapus gambar jika ada dan bukan URL
        if ($gambar && !filter_var($gambar, FILTER_VALIDATE_URL)) {
            // Hapus gambar yang ada di server
            if (file_exists($gambar)) {
                unlink($gambar);
            }
        }
    }

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
    <style>
        .card-img-top {
            object-fit: cover;
            height: 200px;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .product-price {
            font-size: 1.1rem;
            color: #007bff;
        }

        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Kasir</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="kasir.php">Kasir</a></li>
                <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Daftar Produk</h2>

        <!-- Search Box -->
        <input type="text" id="searchBox" class="form-control search-box" placeholder="Cari produk...">

        <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#productModal" onclick="clearForm()">Tambah Produk</button>

        <div class="row" id="productList">
            <?php foreach ($produkList as $produk): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 product-item" data-name="<?php echo htmlspecialchars($produk['nama']); ?>">
                <div class="card product-card">
                    <img src="<?php echo htmlspecialchars($produk['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama']); ?>">
                    <div class="card-body">
                        <h5 class="product-title"><?php echo htmlspecialchars($produk['nama']); ?></h5>
                        <p class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
                        <button class="btn btn-warning" onclick="editProduct('<?php echo htmlspecialchars($produk['id']); ?>', '<?php echo htmlspecialchars($produk['nama']); ?>', <?php echo $produk['harga']; ?>, '<?php echo htmlspecialchars($produk['gambar']); ?>')">Edit</button>
                        <a href="?delete_id=<?php echo $produk['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Tambah/Edit Produk -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Tambah/Edit Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="product-id">
                        <div class="form-group">
                            <label>Nama:</label>
                            <input type="text" name="nama" id="product-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Harga:</label>
                            <input type="text" name="harga" id="product-price" class="form-control" required oninput="formatPrice(this)">
                        </div>
                        <div class="form-group">
                            <label>Gambar URL atau Upload Gambar:</label>
                            <input type="text" name="gambar" id="product-image" class="form-control" placeholder="Masukkan URL Gambar">
                            <div class="mt-2">
                                <label>Atau Upload Gambar:</label>
                                <input type="file" name="gambar_upload" id="product-image-upload" class="form-control-file">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to format price input
        function formatPrice(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Edit product functionality
        function editProduct(id, name, price, image) {
            document.getElementById('product-id').value = id;
            document.getElementById('product-name').value = name;
            document.getElementById('product-price').value = price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            document.getElementById('product-image').value = image;
            $('#productModal').modal('show');
        }

        // Clear form when adding new product
        function clearForm() {
            document.getElementById('product-id').value = '';
            document.getElementById('product-name').value = '';
            document.getElementById('product-price').value = '';
            document.getElementById('product-image').value = '';
            document.getElementById('product-image-upload').value = '';
        }

        // Search functionality
        document.getElementById('searchBox').addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var productItems = document.querySelectorAll('.product-item');

            productItems.forEach(function(item) {
                var productName = item.getAttribute('data-name').toLowerCase();
                if (productName.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

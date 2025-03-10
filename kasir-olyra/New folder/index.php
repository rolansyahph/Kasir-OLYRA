<?php
// Koneksi database
$host = 'localhost';
$db = 'kasir';
$user = 'root'; // Sesuaikan dengan username database kamu
$pass = ''; // Sesuaikan dengan password database kamu

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

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
    <title>Kasir</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .menu-item {
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Kasir</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="#kasir">Kasir</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" id="menu">
        <h1>Daftar Menu</h1>
        <div class="row" id="menu-container">
            <?php foreach ($produkList as $produk): ?>
            <div class="col-md-3 menu-item" onclick="addToOrder('<?php echo htmlspecialchars($produk['nama']); ?>', <?php echo $produk['harga']; ?>)">
                <img src="<?php echo htmlspecialchars($produk['gambar']); ?>" alt="<?php echo htmlspecialchars($produk['nama']); ?>" class="img-fluid">
                <p><?php echo htmlspecialchars($produk['nama']); ?> - $<?php echo $produk['harga']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <h2>Pesanan Anda</h2>
        <table class="table" id="order-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <p>Total: <span id="total-price">0</span></p>
    </div>

    <div class="container mt-4" id="kasir">
        <h2>Kasir</h2>
        <p id="checkout-message" style="display:none;"></p>
        <button id="checkout-btn" class="btn btn-success">Bayar</button>
    </div>

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
        let order = {};

        function addToOrder(name, price) {
            if (order[name]) {
                order[name].quantity++;
            } else {
                order[name] = { price, quantity: 1 };
            }
            renderOrder();
        }

        function renderOrder() {
            const orderTableBody = document.querySelector('#order-table tbody');
            orderTableBody.innerHTML = '';
            let total = 0;
            for (const itemName in order) {
                const item = order[itemName];
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${itemName}</td>
                    <td>$${item.price}</td>
                    <td>
                        <button onclick="changeQuantity('${itemName}', -1)">-</button>
                        ${item.quantity}
                        <button onclick="changeQuantity('${itemName}', 1)">+</button>
                    </td>
                    <td>$${item.price * item.quantity}</td>
                `;
                orderTableBody.appendChild(row);
                total += item.price * item.quantity;
            }
            document.getElementById('total-price').textContent = total;
        }

        function changeQuantity(itemName, change) {
            if (order[itemName]) {
                order[itemName].quantity += change;
                if (order[itemName].quantity <= 0) {
                    delete order[itemName];
                }
                renderOrder();
            }
        }

        document.getElementById('checkout-btn').addEventListener('click', () => {
            const total = Object.values(order).reduce((sum, item) => sum + item.price * item.quantity, 0);
            const amountPaid = prompt("Masukkan jumlah yang dibayar:");
            
            if (amountPaid) {
                const change = amountPaid - total;
                if (change < 0) {
                    alert("Jumlah yang dibayar tidak cukup.");
                } else {
                    const message = `Pembayaran berhasil! Total: $${total}, Kembalian: $${change}`;
                    document.getElementById('checkout-message').textContent = message;
                    document.getElementById('checkout-message').style.display = 'block';
                    order = {};
                    renderOrder();
                }
            }
        });

        function editProduct(id, name, price, image) {
            document.getElementById('product-id').value = id;
            document.querySelector('input[name="nama"]').value = name;
            document.querySelector('input[name="harga"]').value = price;
            document.querySelector('input[name="gambar"]').value = image;
        }
    </script>
</body>

</html>

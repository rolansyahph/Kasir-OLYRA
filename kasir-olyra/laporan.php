<?php
include 'db.php';

// Ambil data penjualan
$result = $conn->query("SELECT p.nama, penjualan.jumlah, penjualan.total, penjualan.waktu FROM penjualan JOIN produk p ON penjualan.produk_id = p.id");
$penjualanList = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $penjualanList[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Kasir</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        <h2>Laporan Penjualan</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penjualanList as $penjualan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($penjualan['nama']); ?></td>
                    <td><?php echo $penjualan['jumlah']; ?></td>
                    <td>$<?php echo $penjualan['total']; ?></td>
                    <td><?php echo $penjualan['waktu']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

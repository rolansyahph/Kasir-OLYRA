<?php
// Koneksi ke database
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$dbname = 'toko';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID transaksi dari parameter URL
$trx_id = $_GET['id_trx'];

// Ambil detail transaksi
$stmt = $conn->prepare("SELECT * FROM transaksi WHERE id_trx = ?");
$stmt->bind_param("i", $trx_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

// Ambil detail produk terjual
$stmt = $conn->prepare("SELECT * FROM produk_terjual WHERE id_trx = ?");
$stmt->bind_param("i", $trx_id);
$stmt->execute();
$produk_terjual = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        body {
            font-family: monospace; 
            margin: 0;
            padding: 10px;
            width: 58mm; 
        }
        .header, .footer {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            border-bottom: 1px solid #000;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        @media print {
            body {
                width: 58mm; 
                margin: 0;
                padding: 0;
                font-size: 12px; 
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STRUK TRANSAKSI</h1>
        <p>No Transaksi: <?php echo $transaksi['id_trx']; ?></p>
        <p>Tanggal: <?php echo date("d-m-Y H:i", strtotime($transaksi['tgl_trx'])); ?></p>
        <hr>
    </div>

    <table>
        <tr>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
        </tr>
        <?php while ($row = $produk_terjual->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['nama']; ?></td>
            <td>Rp <?php echo number_format($row['harga'], 2); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>Rp <?php echo number_format($row['grand_total'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p class="total">Grand Total: Rp <?php echo number_format($transaksi['grand_total'], 2); ?></p>
    <hr>
    <div class="footer">
        <p>Terima Kasih!</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

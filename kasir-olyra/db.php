<?php
$host = 'localhost';
$db = 'kasir'; // Ganti sesuai nama database Anda
$user = 'root'; // Ganti sesuai username database Anda
$pass = ''; // Ganti sesuai password database Anda

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/**
 * Fungsi untuk merekam penjualan ke dalam database
 *
 * @param mysqli $conn Koneksi database
 * @param int $productId ID produk yang dijual
 * @param int $quantity Jumlah produk yang dijual
 * @param float $total Total harga penjualan
 */
function recordSale($conn, $productId, $quantity, $total) {
    $stmt = $conn->prepare("INSERT INTO penjualan (produk_id, jumlah, total) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $productId, $quantity, $total);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Periksa apakah ada permintaan untuk merekam penjualan
if (isset($_POST['record_sale'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $total = $_POST['total'];

    // Panggil fungsi untuk merekam penjualan
    if (recordSale($conn, $product_id, $quantity, $total)) {
        echo "Penjualan berhasil direkam.";
    } else {
        echo "Terjadi kesalahan saat merekam penjualan.";
    }
}
?>

<?php
$host = 'localhost';
$db = 'kasir';
$user = 'root'; // Sesuaikan dengan username database kamu
$pass = ''; // Sesuaikan dengan password database kamu

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

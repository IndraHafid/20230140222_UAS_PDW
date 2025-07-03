<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: praktikum.php");
    exit();
}

$id = intval($_GET['id']);

// Hapus praktikum berdasarkan id
$sql = "DELETE FROM praktikum WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: praktikum.php");
exit();
?>

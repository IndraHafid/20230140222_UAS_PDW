<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: modul.php");
    exit();
}

$id = intval($_GET['id']);

// Hapus file materi jika ada
$sql = "SELECT file_path FROM modul WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($file_path);
$stmt->fetch();
$stmt->close();

if ($file_path && file_exists($file_path)) {
    unlink($file_path);
}

// Hapus modul berdasarkan id
$sql = "DELETE FROM modul WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: modul.php");
exit();
?>

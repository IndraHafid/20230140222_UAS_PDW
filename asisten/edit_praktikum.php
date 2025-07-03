<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Mata Praktikum';
$activePage = 'praktikum';

if (!isset($_GET['id'])) {
    header("Location: praktikum.php");
    exit();
}

$id = intval($_GET['id']);
$message = '';

// Ambil data praktikum berdasarkan id
$sql = "SELECT * FROM praktikum WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

if (!$praktikum) {
    header("Location: praktikum.php");
    exit();
}

// Handle form submission update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_praktikum'])) {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($nama)) {
        $message = "Nama praktikum harus diisi.";
    } else {
        $sql = "UPDATE praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama, $deskripsi, $id);
        if ($stmt->execute()) {
            header("Location: praktikum.php");
            exit();
        } else {
            $message = "Gagal memperbarui praktikum.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <h1 class="text-3xl font-bold mb-6"><?php echo $pageTitle; ?></h1>

    <?php if ($message): ?>
        <div class="mb-4 p-3 bg-red-200 text-red-800 rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" class="max-w-md bg-white p-4 rounded shadow-md">
        <label class="block mb-2">
            <span class="text-gray-700">Nama Praktikum</span>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>" class="mt-1 block w-full border border-gray-300 rounded p-2" required />
        </label>
        <label class="block mb-4">
            <span class="text-gray-700">Deskripsi</span>
            <textarea name="deskripsi" rows="3" class="mt-1 block w-full border border-gray-300 rounded p-2"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></textarea>
        </label>
        <button type="submit" name="update_praktikum" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Perbarui</button>
        <a href="praktikum.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</body>
</html>

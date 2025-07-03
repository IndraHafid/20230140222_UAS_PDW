<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Kelola Mata Praktikum';
$activePage = 'praktikum';

require_once 'templates/header.php';

// Tangani tambah praktikum baru
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_praktikum'], $_POST['deskripsi'])) {
    $nama_praktikum = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);

    if ($nama_praktikum === '') {
        $message = 'Nama praktikum tidak boleh kosong.';
    } else {
        $sql_insert = "INSERT INTO praktikum (nama_praktikum, deskripsi) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ss", $nama_praktikum, $deskripsi);
        if ($stmt_insert->execute()) {
            $message = 'Praktikum berhasil ditambahkan.';
        } else {
            $message = 'Gagal menambahkan praktikum.';
        }
        $stmt_insert->close();
    }
}

// Tangani hapus praktikum
if (isset($_GET['hapus_id'])) {
    $hapus_id = intval($_GET['hapus_id']);
    $sql_delete = "DELETE FROM praktikum WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $hapus_id);
    if ($stmt_delete->execute()) {
        $message = 'Praktikum berhasil dihapus.';
    } else {
        $message = 'Gagal menghapus praktikum.';
    }
    $stmt_delete->close();
}

// Ambil daftar praktikum
$sql = "SELECT * FROM praktikum ORDER BY id DESC";
$result = $conn->query($sql);
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="text-2xl font-bold mb-4">Kelola Praktikum</h2>

<?php if ($message): ?>
    <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mb-6 max-w-md bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">Tambah Praktikum Baru</h3>
    <form method="post" action="praktikum.php">
        <label for="nama_praktikum" class="block mb-2 font-semibold">Nama Praktikum</label>
        <input type="text" id="nama_praktikum" name="nama_praktikum" class="w-full border border-gray-300 rounded p-2 mb-4" required>
        
        <label for="deskripsi" class="block mb-2 font-semibold">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" class="w-full border border-gray-300 rounded p-2 mb-4" rows="3"></textarea>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
    </form>
</div>

<h3 class="text-xl font-semibold mb-4">Daftar Praktikum</h3>

<?php if (count($praktikumList) === 0): ?>
    <p>Belum ada praktikum.</p>
<?php else: ?>
    <table class="table-auto border-collapse border border-gray-300 w-full max-w-4xl">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Nama Praktikum</th>
                <th class="border border-gray-300 px-4 py-2">Deskripsi</th>
                <th class="border border-gray-300 px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($praktikumList as $praktikum): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?php echo $praktikum['id']; ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></td>
                    <td class="border border-gray-300 px-4 py-2">
                        <a href="edit_praktikum.php?id=<?php echo $praktikum['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                        <a href="praktikum.php?hapus_id=<?php echo $praktikum['id']; ?>" onclick="return confirm('Yakin ingin menghapus praktikum ini?');" class="text-red-600 hover:underline mr-2">Hapus</a>
                        <a href="mahasiswa_praktikum.php?praktikum_id=<?php echo $praktikum['id']; ?>" class="text-green-600 hover:underline">Lihat Mahasiswa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>

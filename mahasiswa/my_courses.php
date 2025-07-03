<?php
session_start();
require_once '../config.php';

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Praktikum Saya';
require_once 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Tangani penghapusan pendaftaran praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_praktikum_id'])) {
    $praktikum_id = intval($_POST['hapus_praktikum_id']);
    $sql_delete = "DELETE FROM mahasiswa_praktikum WHERE user_id = ? AND praktikum_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $user_id, $praktikum_id);
    if ($stmt_delete->execute()) {
        $message = "Berhasil menghapus praktikum.";
    } else {
        $message = "Gagal menghapus praktikum.";
    }
    $stmt_delete->close();
}

// Ambil daftar praktikum yang diikuti mahasiswa
$sql = "SELECT p.id, p.nama_praktikum, p.deskripsi FROM praktikum p
        JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id
        WHERE mp.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-7xl mx-auto px-6">
    <h2 class="text-2xl font-bold mb-4">Praktikum Saya</h2>

    <?php if ($message): ?>
        <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (count($praktikumList) === 0): ?>
        <p>Anda belum mendaftar di praktikum apapun.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($praktikumList as $praktikum): ?>
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <a href="detail_praktikum.php?id=<?php echo $praktikum['id']; ?>" class="text-blue-600 hover:underline text-xl font-semibold">
                        <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                    </a>
                    <p class="mt-2 text-gray-700"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                    <form method="post" onsubmit="return confirm('Yakin ingin menghapus praktikum ini?');" class="mt-4">
                        <input type="hidden" name="hapus_praktikum_id" value="<?php echo $praktikum['id']; ?>">
                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>

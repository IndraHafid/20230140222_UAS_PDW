<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

// Definisikan variabel untuk Template
$pageTitle = 'Cari Mata Praktikum';
require_once 'templates/header_mahasiswa.php';

$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
$praktikumList = [];
$message = '';

// Tangani penambahan praktikum ke daftar mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $user_id = $_SESSION['user_id'];

    // Cek apakah praktikum sudah ada di daftar mahasiswa
    $check_sql = "SELECT * FROM mahasiswa_praktikum WHERE user_id = ? AND praktikum_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $praktikum_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Anda sudah terdaftar di praktikum ini.";
    } else {
        // Tambah praktikum ke daftar mahasiswa
        $insert_sql = "INSERT INTO mahasiswa_praktikum (user_id, praktikum_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $praktikum_id);
        if ($insert_stmt->execute()) {
            $message = "Berhasil menambahkan praktikum.";
        } else {
            $message = "Gagal menambahkan praktikum.";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

// Jika ada pencarian
if (isset($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']);
    $sql = "SELECT id, nama_praktikum, deskripsi FROM praktikum WHERE nama_praktikum LIKE ?";
    $searchTerm = "%$keyword%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $praktikumList = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="max-w-7xl mx-auto px-6">
    <h2 class="text-2xl font-bold mb-4">Cari Mata Praktikum</h2>

    <form method="post" class="mb-6">
        <input type="text" name="keyword" placeholder="Masukkan nama praktikum" value="<?php echo htmlspecialchars($keyword); ?>" class="border p-2 rounded w-full" required>
        <button type="submit" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Cari</button>
    </form>

    <?php if ($message): ?>
        <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!empty($praktikumList)): ?>
        <ul class="list-disc pl-5 space-y-2">
            <?php foreach ($praktikumList as $praktikum): ?>
                <li>
                    <form method="post" class="inline">
                        <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                        <button type="submit" class="text-blue-600 hover:underline font-semibold mr-2 bg-green-100 px-2 py-1 rounded hover:bg-green-200">Tambah Mata Kuliah</button>
                    </form>
                    <a href="detail_praktikum.php?id=<?php echo $praktikum['id']; ?>" class="text-blue-600 hover:underline">
                        <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                    </a>
                    <p><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>Tidak ditemukan praktikum dengan nama tersebut.</p>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>

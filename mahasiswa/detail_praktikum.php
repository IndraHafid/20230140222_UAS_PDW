<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Detail Praktikum';
require_once 'templates/header_mahasiswa.php';

if (!isset($_GET['id'])) {
    echo "<p>ID praktikum tidak ditemukan.</p>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}

$praktikum_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil data praktikum
$sql = "SELECT * FROM praktikum WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $praktikum_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

if (!$praktikum) {
    echo "<p>Praktikum tidak ditemukan.</p>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}

// Cek apakah mahasiswa terdaftar di praktikum ini
$sql_check = "SELECT * FROM mahasiswa_praktikum WHERE user_id = ? AND praktikum_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $praktikum_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    echo "<p>Anda belum terdaftar di praktikum ini.</p>";
    require_once 'templates/footer_mahasiswa.php';
    exit();
}

// Ambil daftar modul
$sql_modul = "SELECT * FROM modul WHERE praktikum_id = ? ORDER BY created_at ASC";
$stmt_modul = $conn->prepare($sql_modul);
$stmt_modul->bind_param("i", $praktikum_id);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
$modulList = $result_modul->fetch_all(MYSQLI_ASSOC);

// Tangani upload laporan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_laporan'])) {
    $modul_id = intval($_POST['modul_id']);
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
        $file_type = $_FILES['file_laporan']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $message = "File laporan harus berupa PDF atau DOCX.";
        } else {
            $upload_dir = '../uploads/laporan/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_name = basename($_FILES['file_laporan']['name']);
            $target_file = $upload_dir . time() . '_' . $file_name;
            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_file)) {
                // Simpan data laporan ke database
                $sql_insert = "INSERT INTO laporan (user_id, modul_id, file_path, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), created_at = VALUES(created_at)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iis", $user_id, $modul_id, $target_file);
                if ($stmt_insert->execute()) {
                    $message = "Laporan berhasil diunggah.";
                } else {
                    $message = "Gagal menyimpan laporan.";
                }
                $stmt_insert->close();
            } else {
                $message = "Gagal mengunggah file.";
            }
        }
    } else {
        $message = "File laporan belum dipilih.";
    }
}

// Ambil nilai laporan
$sql_nilai = "SELECT laporan.*, nilai.nilai, nilai.feedback FROM laporan LEFT JOIN nilai ON laporan.id = nilai.laporan_id WHERE laporan.user_id = ? AND laporan.modul_id = ?";
$stmt_nilai = $conn->prepare($sql_nilai);

?>

<div class="max-w-7xl mx-auto px-6 py-6">
    <h2 class="text-3xl font-bold mb-4 text-gray-800"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h2>
    <p class="mb-8 text-gray-700 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded shadow"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h3 class="text-2xl font-semibold mb-6 text-gray-800">Daftar Modul</h3>

    <?php if (count($modulList) === 0): ?>
        <p class="text-gray-600">Belum ada modul untuk praktikum ini.</p>
    <?php else: ?>
        <ul class="space-y-8">
            <?php foreach ($modulList as $modul): ?>
                <li class="bg-white p-6 rounded-lg shadow-md">
                    <h4 class="text-xl font-bold mb-3 text-gray-900"><?php echo htmlspecialchars($modul['judul']); ?></h4>
                    <p class="mb-4 text-gray-700 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($modul['deskripsi'])); ?></p>
                    <?php if ($modul['file_path']): ?>
                        <p class="mb-4"><a href="<?php echo htmlspecialchars($modul['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline font-semibold">Unduh Materi</a></p>
                    <?php endif; ?>

                    <?php
                    // Ambil nilai dan laporan mahasiswa untuk modul ini
                    $stmt_nilai->bind_param("ii", $user_id, $modul['id']);
                    $stmt_nilai->execute();
                    $result_nilai = $stmt_nilai->get_result();
                    $laporan = $result_nilai->fetch_assoc();
                    ?>

                    <form method="post" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                        <label class="block mb-2 font-semibold text-gray-800">Unggah Laporan/Tugas (PDF/DOCX):</label>
                        <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="mb-3 block w-full text-gray-700 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" name="upload_laporan" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">Unggah</button>
                    </form>

                    <?php if ($laporan): ?>
                        <p class="mb-1 text-gray-700">Laporan: <a href="<?php echo htmlspecialchars($laporan['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline font-semibold">Lihat File</a></p>
                        <?php if ($laporan['nilai'] !== null): ?>
                            <p class="text-gray-700">Nilai: <?php echo htmlspecialchars($laporan['nilai']); ?></p>
                            <p class="text-gray-700 whitespace-pre-line">Feedback: <?php echo nl2br(htmlspecialchars($laporan['feedback'])); ?></p>
                        <?php else: ?>
                            <p class="text-gray-700">Nilai: Belum dinilai</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-700">Belum mengunggah laporan.</p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>

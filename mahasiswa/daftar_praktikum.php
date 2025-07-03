<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Daftar Praktikum';
require_once 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Tangani pendaftaran praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = intval($_POST['praktikum_id']);

    // Cek apakah sudah terdaftar
    $sql_check = "SELECT * FROM mahasiswa_praktikum WHERE user_id = ? AND praktikum_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $praktikum_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "Anda sudah terdaftar di praktikum ini.";
    } else {
        // Daftarkan mahasiswa ke praktikum
        $sql_insert = "INSERT INTO mahasiswa_praktikum (user_id, praktikum_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $user_id, $praktikum_id);
        if ($stmt_insert->execute()) {
            $message = "Berhasil mendaftar praktikum.";
        } else {
            $message = "Gagal mendaftar praktikum.";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Ambil daftar praktikum yang belum diikuti
$sql = "SELECT * FROM praktikum WHERE id NOT IN (SELECT praktikum_id FROM mahasiswa_praktikum WHERE user_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="text-2xl font-bold mb-4">Daftar Mata Kuliah yang Tersedia</h2>

<?php if ($message): ?>
    <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (count($praktikumList) === 0): ?>
    <p>Anda sudah terdaftar di semua praktikum.</p>
<?php else: ?>
    <form method="post" class="mb-6">
        <label for="praktikum_id" class="block mb-2 font-semibold">Pilih Mata Kuliah:</label>
        <select name="praktikum_id" id="praktikum_id" required class="border p-2 rounded w-full mb-4">
            <option value="">-- Pilih Mata Kuliah --</option>
            <?php foreach ($praktikumList as $praktikum): ?>
                <option value="<?php echo $praktikum['id']; ?>"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Daftar</button>
    </form>
<?php endif; ?>

<?php
require_once 'templates/footer_mahasiswa.php';
?>

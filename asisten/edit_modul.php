<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Modul';
$activePage = 'modul';

if (!isset($_GET['id'])) {
    header("Location: modul.php");
    exit();
}

$id = intval($_GET['id']);
$message = '';

// Ambil data modul berdasarkan id
$sql = "SELECT * FROM modul WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$modul = $result->fetch_assoc();

if (!$modul) {
    header("Location: modul.php");
    exit();
}

// Ambil daftar praktikum untuk dropdown
$praktikumList = [];
$sql2 = "SELECT id, nama_praktikum FROM praktikum ORDER BY nama_praktikum ASC";
$result2 = $conn->query($sql2);
if ($result2) {
    $praktikumList = $result2->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_modul'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $file_path = $modul['file_path'];

    // Upload file jika ada
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
        $file_type = $_FILES['file_materi']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $message = "File harus berupa PDF atau DOCX.";
        } else {
            $upload_dir = '../uploads/materi/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_name = basename($_FILES['file_materi']['name']);
            $target_file = $upload_dir . time() . '_' . $file_name;
            if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $target_file)) {
                $file_path = $target_file;
            } else {
                $message = "Gagal mengunggah file.";
            }
        }
    }

    if (empty($message)) {
        $sql = "UPDATE modul SET praktikum_id = ?, judul = ?, deskripsi = ?, file_path = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $praktikum_id, $judul, $deskripsi, $file_path, $id);
        if ($stmt->execute()) {
            header("Location: modul.php");
            exit();
        } else {
            $message = "Gagal memperbarui modul.";
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

    <form method="post" enctype="multipart/form-data" class="max-w-lg bg-white p-4 rounded shadow-md">
        <label class="block mb-2">
            <span class="text-gray-700">Mata Praktikum</span>
            <select name="praktikum_id" required class="mt-1 block w-full border border-gray-300 rounded p-2">
                <option value="">Pilih praktikum</option>
                <?php foreach ($praktikumList as $praktikum): ?>
                    <option value="<?php echo $praktikum['id']; ?>" <?php echo ($praktikum['id'] == $modul['praktikum_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block mb-2">
            <span class="text-gray-700">Judul Modul</span>
            <input type="text" name="judul" value="<?php echo htmlspecialchars($modul['judul']); ?>" required class="mt-1 block w-full border border-gray-300 rounded p-2" />
        </label>
        <label class="block mb-2">
            <span class="text-gray-700">Deskripsi</span>
            <textarea name="deskripsi" rows="3" class="mt-1 block w-full border border-gray-300 rounded p-2"><?php echo htmlspecialchars($modul['deskripsi']); ?></textarea>
        </label>
        <label class="block mb-4">
            <span class="text-gray-700">File Materi (PDF/DOCX)</span>
            <?php if ($modul['file_path']): ?>
                <p class="mb-2"><a href="<?php echo htmlspecialchars($modul['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File Saat Ini</a></p>
            <?php endif; ?>
            <input type="file" name="file_materi" accept=".pdf,.doc,.docx" class="mt-1 block w-full" />
        </label>
        <button type="submit" name="update_modul" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Perbarui Modul</button>
        <a href="modul.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</body>
</html>

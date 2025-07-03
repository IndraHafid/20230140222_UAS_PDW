<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';

require_once 'templates/header.php';

$message = '';

// Tangani tambah modul baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'], $_POST['judul'], $_POST['deskripsi'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);

    // Tangani upload file materi
    $file_path = null;
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
        $file_type = $_FILES['file_materi']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $message = "File materi harus berupa PDF atau DOCX.";
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
                $message = "Gagal mengunggah file materi.";
            }
        }
    }

    if ($message === '') {
        $sql_insert = "INSERT INTO modul (praktikum_id, judul, deskripsi, file_path, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isss", $praktikum_id, $judul, $deskripsi, $file_path);
        if ($stmt_insert->execute()) {
            $message = "Modul berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan modul.";
        }
        $stmt_insert->close();
    }
}

// Tangani hapus modul
if (isset($_GET['hapus_id'])) {
    $hapus_id = intval($_GET['hapus_id']);
    $sql_delete = "DELETE FROM modul WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $hapus_id);
    if ($stmt_delete->execute()) {
        $message = "Modul berhasil dihapus.";
    } else {
        $message = "Gagal menghapus modul.";
    }
    $stmt_delete->close();
}

// Ambil daftar praktikum untuk dropdown
$sql_praktikum = "SELECT id, nama_praktikum FROM praktikum ORDER BY nama_praktikum ASC";
$result_praktikum = $conn->query($sql_praktikum);
$praktikumList = $result_praktikum->fetch_all(MYSQLI_ASSOC);

// Ambil daftar modul
$sql = "SELECT modul.id, praktikum.nama_praktikum, modul.judul, modul.deskripsi, modul.file_path
        FROM modul
        JOIN praktikum ON modul.praktikum_id = praktikum.id
        ORDER BY modul.created_at DESC";
$result = $conn->query($sql);
$modulList = $result->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="text-2xl font-bold mb-4">Manajemen Modul</h2>

<?php if ($message): ?>
    <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="flex space-x-6">
    <div class="mb-6 max-w-md bg-white p-6 rounded shadow flex-shrink-0">
        <h3 class="text-xl font-semibold mb-4">Tambah Modul Baru</h3>
        <form method="post" action="modul.php" enctype="multipart/form-data">
            <label for="praktikum_id" class="block mb-2 font-semibold">Mata Praktikum</label>
            <select id="praktikum_id" name="praktikum_id" class="w-full border border-gray-300 rounded p-2 mb-4" required>
                <option value="">Pilih praktikum</option>
                <?php foreach ($praktikumList as $praktikum): ?>
                    <option value="<?php echo $praktikum['id']; ?>"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="judul" class="block mb-2 font-semibold">Judul Modul</label>
            <input type="text" id="judul" name="judul" class="w-full border border-gray-300 rounded p-2 mb-4" required>

            <label for="deskripsi" class="block mb-2 font-semibold">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" class="w-full border border-gray-300 rounded p-2 mb-4" rows="3"></textarea>

            <label for="file_materi" class="block mb-2 font-semibold">File Materi (PDF/DOCX)</label>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx" class="mb-4">

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Modul</button>
        </form>
    </div>

    <div class="flex-1 bg-white p-6 rounded shadow overflow-x-auto">
        <h3 class="text-xl font-semibold mb-4">Daftar Modul</h3>

        <?php if (count($modulList) === 0): ?>
            <p>Belum ada modul.</p>
        <?php else: ?>
            <table class="table-auto border-collapse border border-gray-300 w-full max-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Mata Praktikum</th>
                        <th class="border border-gray-300 px-4 py-2">Judul</th>
                        <th class="border border-gray-300 px-4 py-2">Deskripsi</th>
                        <th class="border border-gray-300 px-4 py-2">File Materi</th>
                        <th class="border border-gray-300 px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modulList as $modul): ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $modul['id']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($modul['nama_praktikum']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($modul['judul']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($modul['deskripsi']); ?></td>
                            <td class="border border-gray-300 px-4 py-2">
                                <?php if ($modul['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($modul['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <a href="edit_modul.php?id=<?php echo $modul['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                                <a href="delete_modul.php?id=<?php echo $modul['id']; ?>" onclick="return confirm('Yakin ingin menghapus modul ini?');" class="text-red-600 hover:underline">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>

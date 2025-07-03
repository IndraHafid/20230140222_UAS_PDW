<?php
require_once '../config.php';
session_start();

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Mahasiswa Praktikum';
require_once 'templates/header.php';

// Ambil daftar praktikum yang dikelola asisten
$sql_praktikum = "SELECT * FROM praktikum";
$result_praktikum = $conn->query($sql_praktikum);
$praktikumList = $result_praktikum->fetch_all(MYSQLI_ASSOC);

$selected_praktikum_id = $_GET['praktikum_id'] ?? null;
$mahasiswaList = [];

// Hitung total modul yang diajarkan
$sql_total_modul = "SELECT COUNT(*) as total_modul FROM modul";
$result_total_modul = $conn->query($sql_total_modul);
$totalModul = 0;
if ($result_total_modul) {
    $row = $result_total_modul->fetch_assoc();
    $totalModul = $row['total_modul'];
}

if ($selected_praktikum_id) {
    // Ambil mahasiswa yang terdaftar di praktikum terpilih tanpa no_hp dan alamat karena kolom tidak ada
    $sql_mahasiswa = "SELECT u.id, u.nama, u.email FROM users u
                      JOIN mahasiswa_praktikum mp ON u.id = mp.user_id
                      WHERE mp.praktikum_id = ?";
    $stmt = $conn->prepare($sql_mahasiswa);
    $stmt->bind_param("i", $selected_praktikum_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mahasiswaList = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<h2 class="text-2xl font-bold mb-4">Mahasiswa Praktikum</h2>

<div class="mb-4 flex items-center space-x-4">
    <div class="bg-gray-100 p-4 rounded shadow">
        <p class="text-gray-600">Total Modul Diajarakan</p>
        <p class="text-2xl font-bold"><?php echo $totalModul; ?></p>
    </div>
    <div>
        <a href="laporan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Lihat Laporan</a>
    </div>
    <div>
        <a href="modul.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Detail Modul</a>
    </div>
</div>

<form method="get" class="mb-6">
    <label for="praktikum_id" class="block mb-2 font-semibold">Pilih Praktikum:</label>
    <select name="praktikum_id" id="praktikum_id" required class="border p-2 rounded w-full max-w-sm">
        <option value="">-- Pilih Praktikum --</option>
        <?php foreach ($praktikumList as $praktikum): ?>
            <option value="<?php echo $praktikum['id']; ?>" <?php if ($praktikum['id'] == $selected_praktikum_id) echo 'selected'; ?>>
                <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
</form>

<?php if ($selected_praktikum_id): ?>
    <h3 class="text-xl font-semibold mb-3">Daftar Mahasiswa Terdaftar</h3>
    <?php if (count($mahasiswaList) === 0): ?>
        <p>Tidak ada mahasiswa yang terdaftar di praktikum ini.</p>
    <?php else: ?>
        <table class="table-auto border-collapse border border-gray-400 w-full max-w-4xl">
            <thead>
                <tr>
                    <th class="border border-gray-400 px-4 py-2">Nama</th>
                    <th class="border border-gray-400 px-4 py-2">Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mahasiswaList as $mahasiswa): ?>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2"><?php echo htmlspecialchars($mahasiswa['nama']); ?></td>
                        <td class="border border-gray-400 px-4 py-2"><?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>

<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Tugas Menunggu';
require_once 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT m.judul, l.created_at, l.file_path, n.nilai, n.feedback
        FROM laporan l
        JOIN modul m ON l.modul_id = m.id
        LEFT JOIN nilai n ON l.id = n.laporan_id
        WHERE l.user_id = ? AND (n.nilai IS NULL OR n.nilai = '')
        ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 class="text-2xl font-bold mb-4">Tugas Menunggu</h2>

<div class="max-w-7xl mx-auto px-6 py-6">
<?php if ($result->num_rows === 0): ?>
    <p>Belum ada tugas menunggu.</p>
<?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">Judul Modul</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Tanggal Pengumpulan</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Laporan</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Nilai</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['judul']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td class="border border-gray-300 px-4 py-2">
                            <?php if ($row['file_path']): ?>
                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['nilai']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo nl2br(htmlspecialchars($row['feedback'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>

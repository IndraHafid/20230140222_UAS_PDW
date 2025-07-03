<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Tangani submit nilai dan feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nilai'])) {
    $laporan_id = intval($_POST['submit_nilai']);
    $nilai = isset($_POST['nilai'][$laporan_id]) ? intval($_POST['nilai'][$laporan_id]) : null;
    $feedback = isset($_POST['feedback'][$laporan_id]) ? $_POST['feedback'][$laporan_id] : null;

    // Cek apakah sudah ada nilai untuk laporan ini
    $sql_check = "SELECT id FROM nilai WHERE laporan_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $laporan_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update nilai dan feedback
        $sql_update = "UPDATE nilai SET nilai = ?, feedback = ? WHERE laporan_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("isi", $nilai, $feedback, $laporan_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Insert nilai dan feedback baru
        $sql_insert = "INSERT INTO nilai (laporan_id, nilai, feedback) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $laporan_id, $nilai, $feedback);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();

    // Redirect untuk menghindari resubmission form
    header("Location: laporan.php");
    exit();
}

$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';

require_once 'templates/header.php';

$sql = "SELECT l.id, u.nama AS mahasiswa_nama, m.judul AS modul_judul, l.file_path, l.created_at, n.nilai, n.feedback
        FROM laporan l
        JOIN users u ON l.user_id = u.id
        JOIN modul m ON l.modul_id = m.id
        LEFT JOIN nilai n ON l.id = n.laporan_id
        ORDER BY l.created_at DESC";

$result = $conn->query($sql);
?>

<div class="p-6">
    <h1 class="text-3xl font-bold mb-4">Laporan Masuk</h1>

    <div class="mb-6 p-4 bg-gray-100 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Aktivitas Laporan Terbaru</h2>
        <ul id="latest-activities" class="list-disc pl-5 space-y-1 text-gray-700">
            <!-- Aktivitas terbaru akan dimuat di sini -->
        </ul>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <form method="post" action="laporan.php">
        <table class="table-auto border-collapse border border-gray-400 w-full max-w-6xl">
            <thead>
                <tr>
                    <th class="border border-gray-400 px-4 py-2">Nama Mahasiswa</th>
                    <th class="border border-gray-400 px-4 py-2">Judul Modul</th>
                    <th class="border border-gray-400 px-4 py-2">Tanggal Pengumpulan</th>
                    <th class="border border-gray-400 px-4 py-2">Laporan</th>
                    <th class="border border-gray-400 px-4 py-2">Nilai</th>
                    <th class="border border-gray-400 px-4 py-2">Feedback</th>
                    <th class="border border-gray-400 px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($laporan = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="border border-gray-400 px-4 py-2"><?php echo htmlspecialchars($laporan['mahasiswa_nama']); ?></td>
                        <td class="border border-gray-400 px-4 py-2"><?php echo htmlspecialchars($laporan['modul_judul']); ?></td>
                        <td class="border border-gray-400 px-4 py-2"><?php echo htmlspecialchars($laporan['created_at']); ?></td>
                        <td class="border border-gray-400 px-4 py-2">
                            <a href="<?php echo htmlspecialchars($laporan['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            <input type="number" name="nilai[<?php echo $laporan['id']; ?>]" value="<?php echo htmlspecialchars($laporan['nilai']); ?>" min="0" max="100" class="w-20 p-1 border rounded" />
                        </td>
                        <td class="border border-gray-400 px-4 py-2">
                            <textarea name="feedback[<?php echo $laporan['id']; ?>]" class="w-full p-1 border rounded" rows="2"><?php echo htmlspecialchars($laporan['feedback']); ?></textarea>
                        </td>
                        <td class="border border-gray-400 px-4 py-2 text-center">
                            <button type="submit" name="submit_nilai" value="<?php echo $laporan['id']; ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Simpan</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </form>
    <?php else: ?>
        <p>Tidak ada laporan masuk.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function loadLatestActivities() {
        fetch('laporan_activities.php')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('latest-activities');
                list.innerHTML = '';
                data.forEach(activity => {
                    const li = document.createElement('li');
                    li.textContent = `${activity.mahasiswa_nama} mengumpulkan laporan untuk ${activity.modul_judul} (${activity.waktu_lapor})`;
                    list.appendChild(li);
                });
            })
            .catch(error => {
                console.error('Error loading latest activities:', error);
            });
    }

    loadLatestActivities();
    setInterval(loadLatestActivities, 60000); // Refresh setiap 60 detik
});
</script>

<?php
require_once 'templates/footer.php';
?>

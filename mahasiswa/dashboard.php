<?php

require_once '../config.php';

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 

$user_id = $_SESSION['user_id'];

// Hitung praktikum yang diikuti
$sql_praktikum = "SELECT COUNT(*) as count_praktikum FROM mahasiswa_praktikum WHERE user_id = ?";
$stmt_praktikum = $conn->prepare($sql_praktikum);
$stmt_praktikum->bind_param("i", $user_id);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result();
$count_praktikum = 0;
if ($row = $result_praktikum->fetch_assoc()) {
    $count_praktikum = $row['count_praktikum'];
}
$stmt_praktikum->close();

// Hitung tugas selesai (laporan dengan nilai tidak null)
$sql_tugas_selesai = "SELECT COUNT(*) as count_tugas_selesai FROM laporan l JOIN nilai n ON l.id = n.laporan_id WHERE l.user_id = ? AND n.nilai IS NOT NULL";
$stmt_tugas_selesai = $conn->prepare($sql_tugas_selesai);
$stmt_tugas_selesai->bind_param("i", $user_id);
$stmt_tugas_selesai->execute();
$result_tugas_selesai = $stmt_tugas_selesai->get_result();
$count_tugas_selesai = 0;
if ($row = $result_tugas_selesai->fetch_assoc()) {
    $count_tugas_selesai = $row['count_tugas_selesai'];
}
$stmt_tugas_selesai->close();

// Hitung tugas menunggu (laporan tanpa nilai)
$sql_tugas_menunggu = "SELECT COUNT(*) as count_tugas_menunggu FROM laporan l LEFT JOIN nilai n ON l.id = n.laporan_id WHERE l.user_id = ? AND (n.nilai IS NULL OR n.nilai = '')";
$stmt_tugas_menunggu = $conn->prepare($sql_tugas_menunggu);
$stmt_tugas_menunggu->bind_param("i", $user_id);
$stmt_tugas_menunggu->execute();
$result_tugas_menunggu = $stmt_tugas_menunggu->get_result();
$count_tugas_menunggu = 0;
if ($row = $result_tugas_menunggu->fetch_assoc()) {
    $count_tugas_menunggu = $row['count_tugas_menunggu'];
}
$stmt_tugas_menunggu->close();

?>

<div class="max-w-7xl mx-auto px-6">
    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
        <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
        <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center cursor-pointer" onclick="window.location.href='my_courses.php'">
            <div class="text-5xl font-extrabold text-blue-600"><?php echo $count_praktikum; ?></div>
            <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center cursor-pointer" onclick="window.location.href='tugas_selesai.php'">
            <div class="text-5xl font-extrabold text-green-500"><?php echo $count_tugas_selesai; ?></div>
            <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center cursor-pointer" onclick="window.location.href='tugas_menunggu.php'">
            <div class="text-5xl font-extrabold text-yellow-500"><?php echo $count_tugas_menunggu; ?></div>
            <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
        </div>
        
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
        <ul class="space-y-4">
            
            <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                <span class="text-xl mr-4">🔔</span>
                <div>
                    Nilai untuk <a href="#" class="font-semibold text-blue-600 hover:underline">Modul 1: HTML & CSS</a> telah diberikan.
                </div>
            </li>

            <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                <span class="text-xl mr-4">⏳</span>
                <div>
                    Batas waktu pengumpulan laporan untuk <a href="#" class="font-semibold text-blue-600 hover:underline">Modul 2: PHP Native</a> adalah besok!
                </div>
            </li>

            <li class="flex items-start p-3">
                <span class="text-xl mr-4">✅</span>
                <div>
                    Anda berhasil mendaftar pada mata praktikum <a href="#" class="font-semibold text-blue-600 hover:underline">Jaringan Komputer</a>.
                </div>
            </li>
            
        </ul>
    </div>
</div>


<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>

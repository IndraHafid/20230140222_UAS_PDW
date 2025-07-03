<?php

// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

require_once '../config.php';

// 2. Panggil Header
require_once 'templates/header.php'; 

// Hitung total modul diajarkan
$sql_modul = "SELECT COUNT(*) as total_modul FROM modul";
$stmt_modul = $conn->prepare($sql_modul);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
$total_modul = 0;
if ($row = $result_modul->fetch_assoc()) {
    $total_modul = $row['total_modul'];
}
$stmt_modul->close();

// Hitung total laporan masuk
$sql_laporan = "SELECT COUNT(*) as total_laporan FROM laporan";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
$total_laporan = 0;
if ($row = $result_laporan->fetch_assoc()) {
    $total_laporan = $row['total_laporan'];
}
$stmt_laporan->close();

// Hitung laporan belum dinilai
$sql_belum_dinilai = "SELECT COUNT(*) as total_belum_dinilai FROM laporan l LEFT JOIN nilai n ON l.id = n.laporan_id WHERE n.nilai IS NULL OR n.nilai = ''";
$stmt_belum_dinilai = $conn->prepare($sql_belum_dinilai);
$stmt_belum_dinilai->execute();
$result_belum_dinilai = $stmt_belum_dinilai->get_result();
$total_belum_dinilai = 0;
if ($row = $result_belum_dinilai->fetch_assoc()) {
    $total_belum_dinilai = $row['total_belum_dinilai'];
}
$stmt_belum_dinilai->close();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_modul; ?></p>
        </div>
        <div>
            <a href="modul.php" class="ml-4 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Detail Modul</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
        <div>
            <a href="laporan.php" class="ml-4 bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Detail Laporan</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_belum_dinilai; ?></p>
        </div>
        <div>
            <a href="laporan.php" class="ml-4 bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">Detail Laporan</a>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div id="latest-activities" class="space-y-4">
        <!-- Aktivitas terbaru akan dimuat di sini -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function getInitials(name) {
        const names = name.split(' ');
        let initials = names[0].charAt(0);
        if (names.length > 1) {
            initials += names[names.length - 1].charAt(0);
        }
        return initials.toUpperCase();
    }

    function loadLatestActivities() {
        fetch('laporan_activities.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('latest-activities');
                container.innerHTML = '';
                data.forEach(activity => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center';

                    const initialsDiv = document.createElement('div');
                    initialsDiv.className = 'w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4';
                    initialsDiv.textContent = getInitials(activity.mahasiswa_nama);

                    const infoDiv = document.createElement('div');
                    const p1 = document.createElement('p');
                    p1.className = 'text-gray-800';
                    p1.innerHTML = `<strong>${activity.mahasiswa_nama}</strong> mengumpulkan laporan untuk <strong>${activity.modul_judul}</strong>`;

                    const p2 = document.createElement('p');
                    p2.className = 'text-sm text-gray-500';
                    p2.textContent = activity.waktu_lapor;

                    infoDiv.appendChild(p1);
                    infoDiv.appendChild(p2);

                    div.appendChild(initialsDiv);
                    div.appendChild(infoDiv);

                    container.appendChild(div);
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
// 3. Panggil Footer
require_once 'templates/footer.php';
?>

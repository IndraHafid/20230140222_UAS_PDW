<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fungsi untuk menghitung waktu relatif dalam bahasa Indonesia
function waktu_lalu($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) {
        return $diff->y . ' tahun lalu';
    } elseif ($diff->m > 0) {
        return $diff->m . ' bulan lalu';
    } elseif ($diff->d > 0) {
        return $diff->d . ' hari lalu';
    } elseif ($diff->h > 0) {
        return $diff->h . ' jam lalu';
    } elseif ($diff->i > 0) {
        return $diff->i . ' menit lalu';
    } else {
        return 'Baru saja';
    }
}

$sql = "SELECT u.nama AS mahasiswa_nama, m.judul AS modul_judul, l.created_at
        FROM laporan l
        JOIN users u ON l.user_id = u.id
        JOIN modul m ON l.modul_id = m.id
        ORDER BY l.created_at DESC
        LIMIT 5";

$result = $conn->query($sql);

$activities = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'mahasiswa_nama' => $row['mahasiswa_nama'],
            'modul_judul' => $row['modul_judul'],
            'waktu_lapor' => waktu_lalu($row['created_at'])
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($activities);
?>

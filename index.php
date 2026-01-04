<?php
// Bagian 1: Inisialisasi dan Konfigurasi
// =================================================
// Harus berada di baris paling atas sebelum output apapun
session_start();

// Konfigurasi Login
define('LOGIN_USERNAME', 'pentester');
define('LOGIN_PASSWORD_HASH', '$2a$12$Z35KQh11M9KfVPXGXMird.HUEW29qtelS7fwcQkzrk/2MT3Y.rP4e');

// Bagian 2: Definisi Fungsi-fungsi Utama
// =================================================

/**
 * Menghasilkan gambar CAPTCHA dan menyimpannya di session.
 * Mengembalikan gambar sebagai data URI (Base64).
 * @return string Data URI dari gambar CAPTCHA.
 */
function generate_captcha_image() {
    $captcha_text = '';
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i = 0; $i < 5; $i++) {
        $captcha_text .= $characters[rand(0, strlen($characters) - 1)];
    }

    // Simpan teks CAPTCHA di session
    $_SESSION['captcha'] = $captcha_text;

    // Buat gambar
    $width = 150;
    $height = 40;
    $image = imagecreatetruecolor($width, $height);

    // Alokasi warna
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 50, 50, 50);
    $noise_color = imagecolorallocate($image, 150, 150, 150);

    // Isi background
    imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

    // Tambahkan beberapa garis acak (noise)
    for ($i = 0; $i < 5; $i++) {
        imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
    }

    // Tambahkan teks CAPTCHA ke gambar
    $font_size = 5;
    $x = ($width - imagefontwidth($font_size) * strlen($captcha_text)) / 2;
    $y = ($height - imagefontheight($font_size)) / 2;
    imagestring($image, $font_size, $x, $y, $captcha_text, $text_color);

    // Tangkap output gambar ke buffer
    ob_start();
    imagepng($image);
    $image_data = ob_get_contents();
    ob_end_clean();
    imagedestroy($image);

    // Kembalikan sebagai data URI
    return 'data:image/png;base64,' . base64_encode($image_data);
}

/**
 * Menampilkan halaman login.
 * @param string $error_message Pesan error yang akan ditampilkan.
 */
function show_login_page($error_message = "") {
    $captcha_src = generate_captcha_image();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MUDAH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">MUDAH - Monitoring Unified Detection for Application Holes</h1>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" id="username" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="captcha" class="block text-gray-700 text-sm font-bold mb-2">CAPTCHA</label>
                <div class="flex items-center">
                    <img src="<?php echo $captcha_src; ?>" alt="CAPTCHA Image" class="rounded-l-lg">
                    <input type="text" name="captcha" id="captcha" required maxlength="5"
                           class="shadow appearance-none border rounded-r-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Log In
                </button>
            </div>
        </form>
    </div>
    <footer class="absolute bottom-0 w-full text-center p-4 text-gray-500 text-xs">
        copy left <?php echo date('Y'); ?> c0ded by Xsan-Lahci Thx To Kang Ali
    </footer>
</body>
</html>
<?php
}

/**
 * Menampilkan halaman dashboard utama.
 */
function show_dashboard() {
    // --- AWAL LOGIKA DINAMIS PEMUATAN FILE JSON ---
    $all_json_files = glob('*.json');
    $json_file_to_load = null;
    $error_message = '';
    $data = null;

    if (empty($all_json_files)) {
        $error_message = "Tidak ada file laporan (.json) yang ditemukan di direktori ini.";
    } else {
        $latest_file = '';
        $latest_time = 0;
        foreach ($all_json_files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latest_time) {
                $latest_time = $mtime;
                $latest_file = $file;
            }
        }
        $json_file_to_load = $latest_file;

        if (isset($_GET['file']) && !empty($_GET['file'])) {
            $requested_file = basename($_GET['file']);
            if (in_array($requested_file, $all_json_files)) {
                $json_file_to_load = $requested_file;
            } else {
                $error_message = "Peringatan: File '{$requested_file}' tidak ditemukan. Memuat laporan terbaru sebagai gantinya.";
            }
        }
    }

    if ($json_file_to_load) {
        $json_content = file_get_contents($json_file_to_load);
        if ($json_content === false) {
            $error_message = "Gagal membaca file JSON '{$json_file_to_load}'.";
        } else {
            $data = json_decode($json_content, true);
            if ($data === null) {
                $error_message = "Gagal menguraikan JSON dari file '{$json_file_to_load}'. Pastikan format JSON valid.";
            }
        }
    }
    // --- AKHIR LOGIKA DINAMIS ---

    // Fungsi untuk menghitung ringkasan kerentanan berdasarkan Severity
    function summarize_vulnerabilities($vulnerabilities) {
        $summary = [
            'High' => 0, 'Medium' => 0, 'Low' => 0, 'Critical' => 0, 'Informational' => 0, 'Total' => count($vulnerabilities)
        ];
        $type_counts = [];
        foreach ($vulnerabilities as $v) {
            $severity = ucfirst(strtolower($v['severity'] ?? 'Low'));
            $vulnerability_type = $v['VulnerabilityType'] ?? 'Unknown Type';
            if (isset($summary[$severity])) {
                $summary[$severity]++;
            } else {
                $summary['Low']++;
            }
            if (!isset($type_counts[$vulnerability_type])) {
                $type_counts[$vulnerability_type] = 0;
            }
            $type_counts[$vulnerability_type]++;
        }
        $summary['Low'] += $summary['Informational'];
        unset($summary['Informational']);
        arsort($type_counts);
        $top_types = array_slice($type_counts, 0, 5, true);
        return ['summary' => $summary, 'top_types' => $top_types];
    }

    $summary_data = ['summary' => [], 'top_types' => []];
    $scan_summary = [];
    $vulnerabilities = [];

    if ($data && isset($data['vulnerabilities'])) {
        $vulnerabilities = $data['vulnerabilities'];
        $summary_data = summarize_vulnerabilities($vulnerabilities);
        $scan_summary = $data['scan_summary'] ?? [];
    }

    $summary = $summary_data['summary'];
    $top_types = $summary_data['top_types'];

    function get_severity_color($severity) {
        switch (strtolower($severity)) {
            case 'high': return 'bg-red-600';
            case 'medium': return 'bg-yellow-600';
            case 'low': case 'informational': return 'bg-blue-600';
            case 'critical': return 'bg-red-900';
            default: return 'bg-gray-500';
        }
    }

    function get_severity_icon($severity) {
        switch (strtolower($severity)) {
            case 'high': case 'medium': case 'critical': return '&#9888;';
            case 'low': case 'informational': default: return '&#x2139;';
        }
    }

    $vulnerabilities_json = json_encode($vulnerabilities);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUDAH - Monitoring Unified Detection for Application Holes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f7f7f9; }
        .info-card { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .info-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); }
        .table-container { max-height: 60vh; overflow-y: auto; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        .sticky-header th { position: sticky; top: 0; background-color: #374151; color: white; z-index: 10; }
        .severity-low { background-color: #3b82f6; }
        .severity-medium { background-color: #f59e0b; }
        .severity-high { background-color: #ef4444; }
        .severity-critical { background-color: #8b0000; }
        .data-row:nth-child(even) { background-color: #f3f4f6; }
        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 800px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .close-button { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close-button:hover, .close-button:focus { color: #000; text-decoration: none; cursor: pointer; }
    </style>
</head>
<body class="p-4 sm:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800">
                🛡️ MUDAH
            </h1>
            <a href="?action=logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150">
                Logout
            </a>
        </div>
        <P class="text-blue-500 mb-5">Monitoring Unified Detection for Application Holes</P>
        <p class="text-gray-500 mb-4">Ganti laporan yang ingin dianalisis menggunakan menu dropdown di bawah ini.</p>
        <?php if (!empty($all_json_files)): ?>
        <div class="mb-8 bg-white p-4 rounded-lg shadow-sm border">
            <form action="index.php" method="GET" class="flex items-center gap-4">
                <label for="file_select" class="text-sm font-bold text-gray-700">Pilih Laporan:</label>
                <select name="file" id="file_select" onchange="this.form.submit()" class="flex-grow px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-white focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                    <?php foreach ($all_json_files as $file): ?>
                        <option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file === $json_file_to_load) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($file); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>
        <p class="text-gray-500 mb-2">Menampilkan laporan dari file: <span class="font-medium text-green-600"><?php echo htmlspecialchars($json_file_to_load ?? 'Tidak ada laporan yang dimuat'); ?></span></p>
        <p class="text-gray-500 mb-8">Target Pemindaian: <span class="font-medium text-blue-600"><?php echo $scan_summary['target_url'] ?? 'Target Tidak Diketahui'; ?></span></p>
        <?php if ($error_message && !$json_file_to_load): ?>
            <div role="alert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <p class="font-bold">Kesalahan Fatal:</p>
                <p><?php echo $error_message; ?></p>
            </div>
        <?php else: ?>
            <?php if ($error_message): ?>
            <div role="alert" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg">
                <p class="font-bold">Peringatan:</p>
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>
            <!-- KONTEN DASHBOARD LAINNYA -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="info-card bg-white p-6 rounded-xl shadow-lg border-b-4 border-gray-400"><p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Kerentanan Ditemukan</p><p class="text-4xl font-bold text-gray-800 mt-1"><?php echo number_format($summary['Total'] ?? 0); ?></p></div>
                <div class="info-card severity-high text-white p-6 rounded-xl shadow-lg border-b-4 border-red-800"><p class="text-sm font-semibold uppercase tracking-wider">Kerentanan Tinggi (HIGH)</p><p class="text-4xl font-bold mt-1"><?php echo number_format($summary['High'] ?? 0); ?></p></div>
                <div class="info-card severity-medium text-white p-6 rounded-xl shadow-lg border-b-4 border-yellow-800"><p class="text-sm font-semibold uppercase tracking-wider">Kerentanan Sedang (MEDIUM)</p><p class="text-4xl font-bold mt-1"><?php echo number_format($summary['Medium'] ?? 0); ?></p></div>
                <div class="info-card severity-low text-white p-6 rounded-xl shadow-lg border-b-4 border-blue-800"><p class="text-sm font-semibold uppercase tracking-wider">Kerentanan Rendah/Informasional (LOW)</p><p class="text-4xl font-bold mt-1"><?php echo number_format($summary['Low'] ?? 0); ?></p></div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Visualisasi Risiko</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-lg"><h3 class="font-semibold text-lg text-gray-700 mb-4 border-b pb-2">Distribusi Tingkat Keparahan</h3><div class="h-64 flex justify-center items-center"><canvas id="severityPieChart"></canvas></div></div>
                <div class="bg-white p-6 rounded-xl shadow-lg"><h3 class="font-semibold text-lg text-gray-700 mb-4 border-b pb-2">Top 5 Tipe Kerentanan</h3><div class="h-64"><canvas id="typeBarChart"></canvas></div></div>
            </div>
            <div class="bg-gray-100 p-4 rounded-xl shadow-inner mb-8 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <p class="font-medium text-gray-700">Scan Mulai: <span class="font-normal text-gray-600"><?php echo $scan_summary['scan_start_time'] ?? '-'; ?></span></p>
                <p class="font-medium text-gray-700">Durasi: <span class="font-normal text-gray-600"><?php echo $scan_summary['total_duration'] ?? '-'; ?></span></p>
                <p class="font-medium text-gray-700 col-span-2 md:col-span-1">URLs Ditemukan: <span class="font-normal text-gray-600"><?php echo $scan_summary['total_urls_discovered'] ?? '-'; ?></span></p>
                <p class="font-medium text-gray-700 col-span-2 md:col-span-1">Teknologi: <span class="font-normal text-gray-600"><?php echo implode(', ', array_keys($scan_summary['technologies_detected'] ?? [])) ?: '-'; ?></span></p>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Detail Kerentanan (Actionable Items)</h2>
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari Kerentanan, URL, atau Detail..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                <select id="severityFilter" onchange="filterTable()" class="sm:w-1/4 px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-white focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                    <option value="ALL">Semua Tingkat Keparahan</option>
                    <option value="HIGH" class="text-red-600">HIGH</option>
                    <option value="MEDIUM" class="text-yellow-600">MEDIUM</option>
                    <option value="LOW" class="text-blue-600">LOW / INFORMATIONAL</option>
                    <option value="CRITICAL" class="text-red-900">CRITICAL</option>
                </select>
            </div>
            <div class="table-container bg-white">
                <table class="min-w-full divide-y divide-gray-200" id="vulnerabilityTable">
                    <thead class="sticky-header">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider w-1/12">No.</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider w-1/6">Level</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider w-1/4">Tipe Kerentanan</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider w-1/4">URL Terdampak</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider w-1/4">Detail Singkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm" id="tableBody">
                        <?php if (empty($vulnerabilities)): ?>
                            <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500 italic">Tidak ada detail kerentanan untuk ditampilkan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($vulnerabilities as $counter => $vuln):
                                $severity = $vuln['severity'] ?? 'Low';
                                $severity_uc = strtoupper($severity);
                                $color_class = get_severity_color($severity);
                                $icon = get_severity_icon($severity);
                                $detail = $vuln['Details'] ?? $vuln['Payload'] ?? 'Tidak ada deskripsi.';
                            ?>
                            <tr class="data-row hover:bg-gray-100 transition duration-150 cursor-pointer" data-severity="<?php echo $severity_uc; ?>" data-index="<?php echo $counter; ?>" onclick="openModal(<?php echo $counter; ?>)">
                                <td class="px-3 py-2 whitespace-nowrap font-mono text-xs"><?php echo $counter + 1; ?></td>
                                <td class="px-3 py-2 whitespace-nowrap"><span class="inline-flex items-center px-3 py-1 text-xs font-bold leading-none text-white rounded-full <?php echo $color_class; ?>"><?php echo $icon; ?> <?php echo $severity_uc; ?></span></td>
                                <td class="px-3 py-2 font-medium text-gray-900 break-words max-w-xs"><?php echo $vuln['VulnerabilityType'] ?? 'Tipe Tidak Diketahui'; ?></td>
                                <td class="px-3 py-2 text-xs text-blue-600 break-words max-w-xs truncate" title="<?php echo htmlspecialchars($vuln['URL'] ?? '-'); ?>"><?php echo htmlspecialchars(substr($vuln['URL'] ?? '-', 0, 70)) . (strlen($vuln['URL'] ?? '') > 70 ? '...' : ''); ?></td>
                                <td class="px-3 py-2 text-xs text-gray-600 break-words max-w-md"><span title="<?php echo htmlspecialchars($detail); ?>"><?php echo htmlspecialchars(substr($detail, 0, 100)) . (strlen($detail) > 100 ? '...' : ''); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="noResults" class="px-3 py-4 text-center text-gray-500 italic hidden">Tidak ada hasil yang cocok dengan kriteria pencarian/filter.</div>
            </div>
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Rekomendasi Tindak Lanjut</h3>
                <p class="text-gray-600">Berikut adalah rekomendasi prioritas untuk penanganan temuan kerentanan berdasarkan tingkat risikonya.</p>
                <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                    <li class="font-medium text-red-600">Kerentanan HIGH: Sangat direkomendasikan untuk segera ditangani karena memiliki dampak risiko tinggi terhadap keamanan aplikasi. Target waktu perbaikan ideal: 1-3 hari kerja.</li>
                    <li class="font-medium text-yellow-600">Kerentanan MEDIUM: Direkomendasikan untuk divalidasi dan dijadwalkan untuk perbaikan dalam siklus pengembangan berikutnya. Target waktu perbaikan ideal: dalam 14 hari kerja.</li>
                    <li class="font-medium text-blue-600">Kerentanan LOW / Informational: Temuan dengan risiko rendah. Dapat diperbaiki sebagai bagian dari praktik security hardening untuk meningkatkan postur keamanan secara keseluruhan.</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <div id="vulnerabilityModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h2 class="text-2xl font-bold text-gray-800 mb-4" id="modalTitle">Detail Kerentanan</h2>
            <div class="space-y-4 text-sm">
                <div class="p-3 rounded-lg bg-gray-100"><p class="font-bold text-gray-600 uppercase text-xs">Level Keparahan</p><p class="text-base font-semibold" id="modalSeverity"></p></div>
                <div><p class="font-bold text-gray-600 uppercase text-xs">Tipe Kerentanan</p><p class="font-medium text-gray-800" id="modalVulnerabilityType"></p></div>
                <div><p class="font-bold text-gray-600 uppercase text-xs">URL Terdampak</p><code class="text-blue-700 break-all" id="modalURL"></code></div>
                <div><p class="font-bold text-gray-600 uppercase text-xs">Deskripsi Detail</p><p class="text-gray-700" id="modalDetails"></p></div>
                <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200"><p class="font-bold text-gray-600 uppercase text-xs">Payload (Input Penyerang)</p><code class="text-red-700 break-all text-xs block whitespace-pre-wrap" id="modalPayload"></code></div>
                <div class="bg-green-50 p-3 rounded-lg border border-green-200"><p class="font-bold text-gray-600 uppercase text-xs">Saran Remediasi (Tindakan Perbaikan)</p><p class="text-green-800 font-medium" id="modalRemediation"></p></div>
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200"><p class="font-bold text-gray-600 uppercase text-xs">Scanner</p><p class="text-blue-800" id="modalScannerName"></p></div>
                <button onclick="copyVulnerabilityDetails()" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150">📋 Salin Semua Detail untuk Ticketing</button>
            </div>
        </div>
    </div>
    
            <footer class="w-full text-center p-4 text-gray-500 text-sm mt-8">
                copy left <?php echo date('Y'); ?> c0ded by Xsan-Lahci Thx to Kang Ali
            </footer>
    
    <script>
        const vulnerabilities = <?php echo $vulnerabilities_json; ?>;
        const severitySummary = { labels: ['HIGH', 'MEDIUM', 'LOW'], data: [<?php echo $summary['High'] ?? 0; ?>, <?php echo $summary['Medium'] ?? 0; ?>, <?php echo $summary['Low'] ?? 0; ?>], colors: ['#ef4444', '#f59e0b', '#3b82f6'] };
        const topTypes = { labels: <?php echo json_encode(array_keys($top_types)); ?>, data: [<?php echo implode(',', $top_types); ?>], colors: '#10b981' };
        function initCharts() {
            if (document.getElementById('severityPieChart')) {
                const ctxPie = document.getElementById('severityPieChart').getContext('2d');
                new Chart(ctxPie, { type: 'doughnut', data: { labels: severitySummary.labels.filter((l, i) => severitySummary.data[i] > 0), datasets: [{ data: severitySummary.data.filter(d => d > 0), backgroundColor: severitySummary.colors.filter((c, i) => severitySummary.data[i] > 0), borderColor: '#f7f7f9', borderWidth: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } } });
            }
            if (document.getElementById('typeBarChart')) {
                const ctxBar = document.getElementById('typeBarChart').getContext('2d');
                new Chart(ctxBar, { type: 'bar', data: { labels: topTypes.labels, datasets: [{ label: 'Jumlah Kerentanan', data: topTypes.data, backgroundColor: topTypes.colors }] }, options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } } });
            }
        }
        function filterTable() {
            const s = document.getElementById('searchInput').value.toLowerCase(), f = document.getElementById('severityFilter').value.toUpperCase(), rs = document.getElementById('tableBody').getElementsByTagName('tr'), n = document.getElementById('noResults');
            let c = 0;
            for (let i = 0; i < rs.length; i++) {
                const r = rs[i];
                if (!r.getAttribute('data-severity')) continue;
                const sev = r.getAttribute('data-severity'), t = r.cells[2]?.textContent.toLowerCase() || '', u = r.cells[3]?.title.toLowerCase() || '', d = r.cells[4]?.title.toLowerCase() || '';
                let sm = (f === 'ALL') || (f === 'LOW' ? (sev === 'LOW' || sev === 'INFORMATIONAL') : (sev === f));
                let sc = !s || t.includes(s) || u.includes(s) || d.includes(s);
                if (sm && sc) { r.style.display = ''; c++; } else { r.style.display = 'none'; }
            }
            n.classList.toggle('hidden', c > 0);
        }
        const modal = document.getElementById('vulnerabilityModal');
        let currentVulnerability = null;
        function getSeverityColorClass(sev) { const l = sev.toLowerCase(); if (l === 'high') return 'severity-high'; if (l === 'medium') return 'severity-medium'; if (l === 'low' || l === 'informational') return 'severity-low'; if (l === 'critical') return 'severity-critical'; return 'bg-gray-500'; }
        function openModal(index) {
            currentVulnerability = vulnerabilities[index];
            if (!currentVulnerability) return;
            const sev = currentVulnerability.severity || 'Low', sev_uc = sev.toUpperCase(), c = getSeverityColorClass(sev);
            const ms = document.getElementById('modalSeverity');
            ms.textContent = sev_uc;
            ms.className = 'text-lg font-semibold inline-block px-3 py-1 text-white rounded-full ' + c;
            document.getElementById('modalVulnerabilityType').textContent = currentVulnerability.VulnerabilityType || '-';
            document.getElementById('modalURL').textContent = currentVulnerability.URL || '-';
            document.getElementById('modalDetails').textContent = currentVulnerability.Details || '-';
            document.getElementById('modalPayload').textContent = currentVulnerability.Payload || 'Tidak Ada Payload Ditemukan';
            document.getElementById('modalRemediation').textContent = currentVulnerability.remediation || 'Tidak Ada Saran Remediasi';
            document.getElementById('modalScannerName').textContent = currentVulnerability.scanner_name || '-';
            modal.style.display = 'block';
        }
        function closeModal() { modal.style.display = 'none'; currentVulnerability = null; }
        window.onclick = function(e) { if (e.target === modal) closeModal(); }
        function copyVulnerabilityDetails() {
            if (!currentVulnerability) return;
            const sev = currentVulnerability.severity || 'Low';
            const d = `--- Detail Kerentanan untuk Ticketing ---\n\nLEVEL KEPARAHAN: ${sev.toUpperCase()}\nTIPE KERENTANAN: ${currentVulnerability.VulnerabilityType || '-'}\nURL TERDAMPAK: ${currentVulnerability.URL || '-'}\nPARAM/LOKASI: ${currentVulnerability.Parameter || '-'} (${currentVulnerability.Location || 'N/A'})\nSCANNER: ${currentVulnerability.scanner_name || '-'}\nWAKTU (Opsional): ${currentVulnerability.scan_start_time || '-'}\n\nDESKRIPSI:\n${currentVulnerability.Details || 'Tidak ada deskripsi.'}\n\nPAYLOAD/EVIDENCE:\n${currentVulnerability.Payload || currentVulnerability.evidence || 'Tidak Ada Payload/Evidence Tambahan.'}\n\nSARAN REMEDIASI:\n${currentVulnerability.remediation || 'Tidak ada saran perbaikan.'}\n\n--- END ---`.trim();
            const el = document.createElement('textarea');
            el.value = d;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Detail kerentanan telah disalin ke clipboard! Siap untuk ticketing.');
        }
        window.onload = function() { if (typeof filterTable === 'function') { filterTable(); initCharts(); } };
    </script>
</body>
</html>
<?php
}

// Bagian 3: Kontroler Utama (Routing Logic)
// =================================================

// 1. Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// 2. Handle Percobaan Login (method POST)
$login_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha_input = strtoupper($_POST['captcha'] ?? '');
    $captcha_session = strtoupper($_SESSION['captcha'] ?? '');

    if (empty($username) || empty($password) || empty($captcha_input)) {
        $login_error = "Semua field wajib diisi.";
    } elseif ($captcha_input !== $captcha_session) {
        $login_error = "CAPTCHA tidak sesuai.";
    } elseif ($username !== LOGIN_USERNAME || !password_verify($password, LOGIN_PASSWORD_HASH)) {
        $login_error = "Username atau password salah.";
    } else {
        // Login berhasil
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        session_regenerate_id(true); // Mencegah session fixation
        header('Location: index.php');
        exit;
    }
}

// 3. Tentukan Halaman yang Akan Ditampilkan
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    show_dashboard();
} else {
    show_login_page($login_error);
}
?>

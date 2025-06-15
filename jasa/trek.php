<?php
include 'function.php';

// Fungsi untuk mengekstrak kota dari alamat
function extractCity($alamat) {
    $kota_known = ['Bandung', 'Jakarta', 'Bogor', 'Garut', 'Tasik', 'Tsak'];
    foreach ($kota_known as $kota) {
        if (stripos($alamat, $kota) !== false) {
            // Mapping untuk singkatan
            if (stripos($alamat, 'Tsak') !== false) return 'Tasik';
            return $kota;
        }
    }
    return 'Bandung'; // Default jika tidak ditemukan
}

// Handle automatic tracking input form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'input_trek') {
    $idpengiriman = trim($_POST['idpengiriman']);

    // Dapatkan data order dan pelanggan
    $sql_order = "SELECT * FROM view_order_customer 
              WHERE idpengiriman = " . (int)$idpengiriman;
    $result_order = mysqli_query($conn, $sql_order);
    
    if (!$result_order || mysqli_num_rows($result_order) === 0) {
        $msg = "ID Pengiriman tidak ditemukan!";
    } else {
        $order = mysqli_fetch_assoc($result_order);
        
        // Extract kota pengirim dari alamat
        $kota_pengirim = extractCity($order['alamat_pengirim']);
        
        // Extract kota pelanggan dari alamat pelanggan
        $kota_pelanggan = extractCity($order['alamat_pelanggan']);
        
        // Cari gudang di kota pengirim
        $sql_gudang_pengirim = "SELECT idgudang, kota, namagudang FROM gudang WHERE kota = '" . mysqli_real_escape_string($conn, $kota_pengirim) . "' LIMIT 1";
        $result_gudang_pengirim = mysqli_query($conn, $sql_gudang_pengirim);
        $gudang_pengirim = mysqli_fetch_assoc($result_gudang_pengirim);
        
        // Cari gudang di kota pelanggan
        $sql_gudang_pelanggan = "SELECT idgudang, kota, namagudang FROM gudang WHERE kota = '" . mysqli_real_escape_string($conn, $kota_pelanggan) . "' LIMIT 1";
        $result_gudang_pelanggan = mysqli_query($conn, $sql_gudang_pelanggan);
        $gudang_pelanggan = mysqli_fetch_assoc($result_gudang_pelanggan);
        
        if (!$gudang_pengirim) {
            // Jika tidak ada gudang di kota pengirim, cari yang terdekat
            $sql_gudang = "SELECT idgudang, kota, namagudang FROM gudang ORDER BY 
                          CASE kota
                              WHEN 'Bandung' THEN 1
                              WHEN 'Garut' THEN 2
                              WHEN 'Tasik' THEN 3
                              WHEN 'Bogor' THEN 4
                              WHEN 'Jakarta' THEN 5
                              ELSE 6
                          END LIMIT 1";
            $result_gudang = mysqli_query($conn, $sql_gudang);
            $gudang_pengirim = mysqli_fetch_assoc($result_gudang);
        }
        
        if (!$gudang_pelanggan) {
            // Jika tidak ada gudang di kota pelanggan, gunakan gudang pengirim
            $gudang_pelanggan = $gudang_pengirim;
        }
        
        // Hapus tracking lama
        mysqli_query($conn, "DELETE FROM trek WHERE idpengiriman = '" . mysqli_real_escape_string($conn, $idpengiriman) . "'");
        
        // Update gudang di tabel order (gunakan gudang pengirim)
        mysqli_query($conn, "UPDATE `order` SET idgudang = '" . mysqli_real_escape_string($conn, $gudang_pengirim['idgudang']) . "' 
                           WHERE idpengiriman = '" . mysqli_real_escape_string($conn, $idpengiriman) . "'");
        
        // Buat timeline pengiriman
        $waktu_sekarang = date('Y-m-d H:i:s');
        
        // 1. Barang diterima dari pengirim
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, $order['alamat_pengirim'] . " (Pengirim)") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_sekarang) . "', 
                                   'Barang diterima dari pengirim')");
        
        // 2. Barang dikirim ke gudang pengirim
        $waktu_kirim_gudang = date('Y-m-d H:i:s', strtotime($waktu_sekarang) + 3600);
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, "Dalam perjalanan ke " . $gudang_pengirim['namagudang']) . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_kirim_gudang) . "', 
                                   'Dalam perjalanan ke gudang pengirim')");
        
        // 3. Barang tiba di gudang pengirim
        $waktu_tiba_gudang = date('Y-m-d H:i:s', strtotime($waktu_kirim_gudang) + 3600);
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, $gudang_pengirim['namagudang'] . " (" . $gudang_pengirim['kota'] . ")") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_tiba_gudang) . "', 
                                   'Barang tiba di gudang pengirim')");
        
        // 4. Barang diproses di gudang pengirim
        $waktu_proses = date('Y-m-d H:i:s', strtotime($waktu_tiba_gudang) + 7200);
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, $gudang_pengirim['namagudang'] . " (" . $gudang_pengirim['kota'] . ")") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_proses) . "', 
                                   'Barang diproses di gudang pengirim')");
        
        // 5. Barang dikirim ke gudang pelanggan (jika berbeda)
        if ($gudang_pengirim['idgudang'] != $gudang_pelanggan['idgudang']) {
            $waktu_kirim_gudang_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_proses) + 3600);
            mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                               VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                       '" . mysqli_real_escape_string($conn, "Dalam perjalanan ke " . $gudang_pelanggan['namagudang']) . "', 
                                       '" . mysqli_real_escape_string($conn, $waktu_kirim_gudang_pelanggan) . "', 
                                       'Dalam perjalanan ke gudang penerima')");
            
            $waktu_tiba_gudang_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_kirim_gudang_pelanggan) + 3600);
            mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                               VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                       '" . mysqli_real_escape_string($conn, $gudang_pelanggan['namagudang'] . " (" . $gudang_pelanggan['kota'] . ")") . "', 
                                       '" . mysqli_real_escape_string($conn, $waktu_tiba_gudang_pelanggan) . "', 
                                       'Barang tiba di gudang penerima')");
            
            $waktu_proses_gudang_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_tiba_gudang_pelanggan) + 7200);
            mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                               VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                       '" . mysqli_real_escape_string($conn, $gudang_pelanggan['namagudang'] . " (" . $gudang_pelanggan['kota'] . ")") . "', 
                                       '" . mysqli_real_escape_string($conn, $waktu_proses_gudang_pelanggan) . "', 
                                       'Barang diproses di gudang penerima')");
            
            $waktu_kirim_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_proses_gudang_pelanggan) + 3600);
        } else {
            $waktu_kirim_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_proses) + 3600);
        }
        
        // 6. Barang dikirim ke pelanggan
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, "Dalam perjalanan ke " . $order['nama_pelanggan'] . " (" . $kota_pelanggan . ")") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_kirim_pelanggan) . "', 
                                   'Dalam perjalanan ke pelanggan')");
        
        // 7. Barang tiba di pelanggan
        $waktu_tiba_pelanggan = date('Y-m-d H:i:s', strtotime($waktu_kirim_pelanggan) + 10800);
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, $order['alamat_pelanggan'] . " (" . $order['nama_pelanggan'] . ")") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_tiba_pelanggan) . "', 
                                   'Barang tiba di pelanggan')");
        
        // 8. Pengiriman selesai
        $waktu_selesai = date('Y-m-d H:i:s', strtotime($waktu_tiba_pelanggan) + 3600);
        mysqli_query($conn, "INSERT INTO trek (idpengiriman, lokasiterakhir, waktuupdate, status) 
                           VALUES ('" . mysqli_real_escape_string($conn, $idpengiriman) . "', 
                                   '" . mysqli_real_escape_string($conn, $order['alamat_pelanggan'] . " (" . $order['nama_pelanggan'] . ")") . "', 
                                   '" . mysqli_real_escape_string($conn, $waktu_selesai) . "', 
                                   'Pengiriman selesai')");
        
        $msg = "Trek otomatis berhasil diinput dengan rute yang benar!";
    }
}

// Get the tracking ID from the form submission
$idpengiriman = isset($_GET['idpengiriman']) ? $_GET['idpengiriman'] : '';

if ($idpengiriman != '') {
    // Get order data
    $sql_order = "SELECT * FROM view_order_detail 
              WHERE idpengiriman = '" . mysqli_real_escape_string($conn, $idpengiriman) . "'";
    $result_order = mysqli_query($conn, $sql_order);
    $data_order = mysqli_fetch_assoc($result_order);

    // Get tracking history
    $sql_tracking = "SELECT 
                        t.*,
                        DATE_FORMAT(t.waktuupdate, '%e %b %Y %H:%i') AS waktu_format
                    FROM trek t
                    WHERE t.idpengiriman = '" . mysqli_real_escape_string($conn, $idpengiriman) . "'
                    ORDER BY t.waktuupdate ASC";
    $result_tracking = mysqli_query($conn, $sql_tracking);
    
    // Extract kota pelanggan dari alamat untuk ditampilkan
    if ($data_order) {
        $data_order['kota_pelanggan'] = extractCity($data_order['alamat_pelanggan']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Tracking Pengiriman</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-dalam-perjalanan { color: orange; font-weight: bold; }
        .status-diterima { color: blue; font-weight: bold; }
        .status-gudang { color: purple; font-weight: bold; }
        .status-diproses { color: #6c757d; font-weight: bold; }
        .status-tiba { color: #17a2b8; font-weight: bold; }
        .status-selesai { color: green; font-weight: bold; }
        .tracking-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .tracking-timeline {
            position: relative;
            padding-left: 30px;
            margin-bottom: 30px;
        }
        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #0d6efd;
            border: 2px solid white;
        }
        .timeline-item.active::before {
            background-color: #198754;
        }
        .timeline-item.completed::before {
            background-color: #6c757d;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">Jasa Pengiriman Barang</a>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="order.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck"></i></div>
                            Data Pengiriman
                        </a>
                        <a class="nav-link" href="customer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Data Pelanggan
                        </a>
                        <a class="nav-link" href="kurir.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-motorcycle"></i></div>
                            Data Kurir
                        </a>
                        <a class="nav-link" href="gudang.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>
                            Data Gudang
                        </a>
                        <a class="nav-link" href="barang.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Data Barang
                        </a>
                        <a class="nav-link" href="penyimpanan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                            Penyimpanan Barang
                        </a>
                        <a class="nav-link active" href="trek.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-map-marked-alt"></i></div>
                            Riwayat Tracking
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Admin
                </div>
            </nav>
        </div>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4" style="margin-top: 25px">
                    <h1 class="mt-4">Riwayat Tracking</h1>
                    
                    <!-- Form Input Trek Otomatis -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Input Trek Otomatis</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($msg)): ?>
                                <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
                            <?php endif; ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="input_trek">
                                <div class="mb-3">
                                    <label for="input_idpengiriman" class="form-label">Masukkan ID Pengiriman:</label>
                                    <input type="text" name="idpengiriman" id="input_idpengiriman" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Input Trek Otomatis</button>
                            </form>
                        </div>
                    </div>

                    <!-- Form Pencarian Tracking -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Lacak Riwayat Pengiriman</h4>
                        </div>
                        <div class="card-body">
                            <form method="get" action="">
                                <div class="mb-3">
                                    <label for="idpengiriman" class="form-label">Masukkan ID Pengiriman:</label>
                                    <input type="text" name="idpengiriman" id="idpengiriman" class="form-control" value="<?= htmlspecialchars($idpengiriman) ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Lacak</button>
                            </form>
                        </div>
                    </div>

                    <!-- Hasil Tracking -->
                    <?php if ($idpengiriman != ''): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4>Hasil Tracking untuk ID Pengiriman: <?= htmlspecialchars($idpengiriman) ?></h4>
                                <?php if ($data_order): ?>
                                    <div class="tracking-header">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Informasi Pengirim</h5>
                                                <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($data_order['nama_pengirim']) ?></p>
                                                <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($data_order['alamat_pengirim']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Informasi Penerima</h5>
                                                <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($data_order['nama_pelanggan']) ?></p>
                                                <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($data_order['alamat_pelanggan']) ?></p>
                                                <p class="mb-1"><strong>Kota:</strong> <?= htmlspecialchars($data_order['kota_pelanggan']) ?></p>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <?php if ($data_order['estimasi_sampai']): ?>
                                                    <p class="mb-0"><strong>Estimasi Sampai:</strong> <?= date('d M Y H:i', strtotime($data_order['estimasi_sampai'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if ($data_order): ?>
                                    <?php if (mysqli_num_rows($result_tracking) > 0): ?>
                                        <div class="tracking-timeline">
                                            <?php 
                                            $current_status = '';
                                            while($row = mysqli_fetch_assoc($result_tracking)): 
                                                $status_class = '';
                                                if (strpos($row['status'], 'dalam perjalanan') !== false) {
                                                    $status_class = 'status-dalam-perjalanan';
                                                } elseif (strpos($row['status'], 'diterima') !== false) {
                                                    $status_class = 'status-diterima';
                                                } elseif (strpos($row['status'], 'gudang') !== false) {
                                                    $status_class = 'status-gudang';
                                                } elseif (strpos($row['status'], 'diproses') !== false) {
                                                    $status_class = 'status-diproses';
                                                } elseif (strpos($row['status'], 'tiba') !== false) {
                                                    $status_class = 'status-tiba';
                                                } elseif (strpos($row['status'], 'selesai') !== false) {
                                                    $status_class = 'status-selesai';
                                                }
                                            ?>
                                            <div class="timeline-item <?= $status_class ?>">
                                                <h5><?= htmlspecialchars(ucfirst($row['status'])) ?></h5>
                                                <p class="mb-1"><strong>Waktu:</strong> <?= htmlspecialchars($row['waktu_format']) ?></p>
                                                <p class="mb-0"><strong>Lokasi:</strong> <?= htmlspecialchars($row['lokasiterakhir']) ?></p>
                                            </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">Belum ada data tracking untuk pengiriman ini</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-danger">Data pengiriman tidak ditemukan!</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>
<?php 
mysqli_close($conn); 
?>
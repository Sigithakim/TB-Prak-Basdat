<?php
require 'function.php';
require 'cek.php';

// Process completion of shipment
if(isset($_GET['selesai'])) {
    $idpengiriman = $_GET['selesai'];
    
    // Get related data first
    $orderData = mysqli_query($conn, "SELECT * FROM `order` WHERE idpengiriman = '$idpengiriman'");
    $order = mysqli_fetch_assoc($orderData);
    $idpelanggan = $order['idpelanggan'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Delete tracking history
        mysqli_query($conn, "DELETE FROM trek WHERE idpengiriman = '$idpengiriman'");
        
        // 2. Delete storage records
        $barangData = mysqli_query($conn, "SELECT idbarang FROM barang WHERE idpengiriman = '$idpengiriman'");
        while($barang = mysqli_fetch_assoc($barangData)) {
            mysqli_query($conn, "DELETE FROM penyimpanan_barang WHERE idbarang = '{$barang['idbarang']}'");
        }
        
        // 3. Delete items
        mysqli_query($conn, "DELETE FROM barang WHERE idpengiriman = '$idpengiriman'");
        
        // 4. Delete the order
        mysqli_query($conn, "DELETE FROM `order` WHERE idpengiriman = '$idpengiriman'");
        
        // 5. Check if customer has other orders
        $customerOrders = mysqli_query($conn, "SELECT COUNT(*) as total FROM `order` WHERE idpelanggan = '$idpelanggan'");
        $count = mysqli_fetch_assoc($customerOrders)['total'];
        
        // 6. Delete customer if no other orders exist
        if($count == 0) {
            mysqli_query($conn, "DELETE FROM customer WHERE idpelanggan = '$idpelanggan'");
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        echo "<script>alert('Error completing shipment: ".$e->getMessage()."');</script>";
    }
}

// Process receipt generation if requested
if(isset($_GET['generate_struk'])) {
    $idpengiriman = $_GET['generate_struk'];
    
    // Query to get shipment details
    $query = "SELECT 
                o.idpengiriman,
                o.nama_pengirim,
                o.no_hp_pengirim,
                o.alamat_pengirim,
                c.nama AS nama_penerima,
                c.alamat AS alamat_penerima,
                c.notelepon AS telepon_penerima,
                g.namagudang,
                k.nama AS nama_kurir,
                o.tanggalpengiriman,
                o.estimasi_sampai,
                o.statuspengiriman,
                b.nama_barang,
                b.berat
              FROM `order` o
              JOIN customer c ON o.idpelanggan = c.idpelanggan
              JOIN gudang g ON o.idgudang = g.idgudang
              JOIN kurir k ON o.idkurir = k.idkurir
              JOIN barang b ON o.idpengiriman = b.idpengiriman
              WHERE o.idpengiriman = '$idpengiriman'";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    // Generate HTML for receipt
    $receipt_html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Struk Pengiriman #'.$idpengiriman.'</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .receipt { width: 100%; max-width: 500px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; box-sizing: border-box; }
            .header { text-align: center; margin-bottom: 20px; }
            .header h2 { margin: 0; color: #333; }
            .header p { margin: 5px 0; color: #666; }
            .divider { border-top: 1px dashed #000; margin: 15px 0; }
            .section { margin-bottom: 15px; }
            .section-title { font-weight: bold; margin-bottom: 5px; }
            .row { display: flex; margin-bottom: 5px; }
            .col { flex: 1; }
            .item-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            .item-table th, .item-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            @media print {
                body { padding: 0; }
                .no-print { display: none !important; }
                .receipt { border: none; }
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <h2>JASA PENGIRIMAN BARANG</h2>
                <p>Jl. Contoh No. 123, Kota Bandung</p>
                <p>Telp: (022) 1234567</p>
            </div>
            
            <div class="divider"></div>
            
            <div style="text-align: center; margin-bottom: 15px;">
                <h3>STRUK PENGIRIMAN</h3>
                <p>No: #'.$idpengiriman.'</p>
            </div>
            
            <div class="divider"></div>
            
            <div class="section">
                <div class="row">
                    <div class="col">
                        <div class="section-title">PENGIRIM:</div>
                        <div>'.$data['nama_pengirim'].'</div>
                        <div>'.$data['no_hp_pengirim'].'</div>
                        <div>'.$data['alamat_pengirim'].'</div>
                    </div>
                    <div class="col">
                        <div class="section-title">PENERIMA:</div>
                        <div>'.$data['nama_penerima'].'</div>
                        <div>'.$data['telepon_penerima'].'</div>
                        <div>'.$data['alamat_penerima'].'</div>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Berat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>'.$data['nama_barang'].'</td>
                        <td>'.$data['berat'].' kg</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="divider"></div>
            
            <div class="section">
                <div class="row">
                    <div class="col">
                        <div class="section-title">GUDANG:</div>
                        <div>'.$data['namagudang'].'</div>
                    </div>
                    <div class="col">
                        <div class="section-title">KURIR:</div>
                        <div>'.$data['nama_kurir'].'</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="section-title">TGL KIRIM:</div>
                        <div>'.date('d/m/Y', strtotime($data['tanggalpengiriman'])).'</div>
                    </div>
                    <div class="col">
                        <div class="section-title">ESTIMASI:</div>
                        <div>'.date('d/m/Y', strtotime($data['estimasi_sampai'])).'</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="section-title">STATUS:</div>
                        <div>'.$data['statuspengiriman'].'</div>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="footer">
                <p>Terima kasih telah menggunakan layanan kami</p>
                <p>Struk ini sah tanpa tanda tangan</p>
                <p>www.jasapengirimanbarang.com</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Cetak Struk
                </button>
                <button onclick="window.close()" style="padding: 10px 20px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                    Tutup
                </button>
            </div>
        </div>
        
        <script>
            // Auto-print if opened in new window
            if(window.opener === null) {
                window.download();
            }
        </script>
    </body>
    </html>';

    // Output the receipt
    echo $receipt_html;
    exit();
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
        <title>Jasa Pengiriman Barang</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="index.php">Jasa Pengiriman Barang</a>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="logout.php">Logout</a></li>
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
                            <a class="nav-link" href="trek.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-map-marked-alt"></i></div>
                                Riwayat Tracking
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer" style="display: flex; align-items: center; gap: 8px;">
                        <img src="assets/img/gay.jpg" alt="User Icon" style="width: 50px; height: 50px; border-radius: 50%;">
                        <div>
                            <div class="small">Logged in as:</div>
                            Admin
                        </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard</h1>
                        
                        <!-- Summary Cards -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Total Pengiriman</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <div class="h4">
                                            <?php 
                                            $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM `order`");
                                            echo mysqli_fetch_assoc($query)['total'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">Jumlah Barang</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <div class="h4">
                                            <?php 
                                            $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
                                            echo mysqli_fetch_assoc($query)['total'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">Jumlah Customer</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <div class="h4">
                                            <?php 
                                            $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM customer");
                                            echo mysqli_fetch_assoc($query)['total'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body">Jumlah Kurir</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <div class="h4">
                                            <?php 
                                            $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM kurir");
                                            echo mysqli_fetch_assoc($query)['total'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Daftar Pengiriman Terbaru
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pelanggan</th>
                                            <th>Kurir</th>
                                            <th>Gudang</th>
                                            <th>Tanggal Kirim</th>
                                            <th>Estimasi Sampai</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                            <th>Struk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $data = mysqli_query($conn, "CALL OrderDetail()");
                                            while ($row = mysqli_fetch_assoc($data)) {
                                                echo "<tr>
                                                    <td>{$row['idpengiriman']}</td>
                                                    <td>{$row['nama_pelanggan']}</td>
                                                    <td>{$row['nama_kurir']}</td>
                                                    <td>{$row['nama_gudang']}</td>
                                                    <td>".date('d/m/Y', strtotime($row['tanggalpengiriman']))."</td>
                                                    <td>".date('d/m/Y', strtotime($row['estimasi_sampai']))."</td>
                                                    <td><span class='badge bg-".($row['statuspengiriman'] == 'Selesai' ? 'success' : 'warning')."'>{$row['statuspengiriman']}</span></td>
                                                    <td>
                                                        <a href='index.php?selesai={$row['idpengiriman']}' class='btn btn-sm btn-success' onclick='return confirm(\"Apakah Anda yakin menyelesaikan pengiriman ini?\\n\\nSemua data terkait termasuk pelanggan akan dihapus permanen.\")'>
                                                            <i class='fas fa-check'></i> Selesai
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href='index.php?generate_struk={$row['idpengiriman']}' class='btn btn-sm btn-info' target='_blank'>
                                                            <i class='fas fa-print'></i> Cetak
                                                        </a>
                                                    </td>
                                                </tr>";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
<?php
include 'function.php';

// Tambah data pengiriman
if (isset($_POST['tambah'])) {
    $idpelanggan = $_POST['idpelanggan'];
    $idkurir = $_POST['idkurir'];
    $idgudang = $_POST['idgudang'];
    $nama_pengirim = $_POST['nama_pengirim'];
    $no_hp_pengirim = $_POST['no_hp_pengirim'];
    $alamat_pengirim = $_POST['alamat_pengirim'];
    $estimasi_sampai = $_POST['estimasi_sampai'];
    $tanggalpengiriman = $_POST['tanggalpengiriman'];

    mysqli_query($conn, "INSERT INTO `order` 
        (idpelanggan, idkurir, idgudang, nama_pengirim, no_hp_pengirim, alamat_pengirim, estimasi_sampai, tanggalpengiriman)
        VALUES ('$idpelanggan', '$idkurir', '$idgudang', '$nama_pengirim', '$no_hp_pengirim', '$alamat_pengirim', '$estimasi_sampai', '$tanggalpengiriman')");
    header("Location: order.php");
}

// Edit data pengiriman
if (isset($_POST['edit'])) {
    $idpengiriman = $_POST['idpengiriman'];
    $idpelanggan = $_POST['idpelanggan'];
    $idkurir = $_POST['idkurir'];
    $idgudang = $_POST['idgudang'];
    $nama_pengirim = $_POST['nama_pengirim'];
    $no_hp_pengirim = $_POST['no_hp_pengirim'];
    $alamat_pengirim = $_POST['alamat_pengirim'];
    $estimasi_sampai = $_POST['estimasi_sampai'];
    $tanggalpengiriman = $_POST['tanggalpengiriman'];

    mysqli_query($conn, "UPDATE `order` SET 
        idpelanggan='$idpelanggan', 
        idkurir='$idkurir',
        idgudang='$idgudang',
        nama_pengirim='$nama_pengirim',
        no_hp_pengirim='$no_hp_pengirim',
        alamat_pengirim='$alamat_pengirim',
        estimasi_sampai='$estimasi_sampai',
        tanggalpengiriman='$tanggalpengiriman'
        WHERE idpengiriman='$idpengiriman'");
    header("Location: order.php");
}

// Hapus data pengiriman
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM `order` WHERE idpengiriman='$id'");
    header("Location: order.php");
}

// Ambil data untuk mode edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM `order` WHERE idpengiriman='$id'");
    $edit_data = mysqli_fetch_assoc($result);
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
        <title>Orders</title><meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
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
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4" style="margin-top: 25px">
                        <h1 class="mt-4" style="margin-left: 30px; margin-top: 30px">Data Pengiriman</h1>
                        
                        <!-- Form Tambah/Edit Data -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header">
                                <h4><?= isset($edit_data) ? 'Edit' : 'Tambah' ?> Data Pengiriman</h4>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="idpengiriman" value="<?= $edit_data['idpengiriman'] ?? '' ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Pelanggan</label>
                                        <select name="idpelanggan" class="form-select" required>
                                            <option value="">-- Pilih Pelanggan --</option>
                                            <?php
                                            $pelanggan = mysqli_query($conn, "SELECT * FROM customer");
                                            while ($p = mysqli_fetch_assoc($pelanggan)) {
                                                $selected = (isset($edit_data) && $edit_data['idpelanggan'] == $p['idpelanggan']) ? 'selected' : '';
                                                echo "<option value='{$p['idpelanggan']}' $selected>{$p['nama']} - {$p['alamat']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Kurir</label>
                                        <select name="idkurir" class="form-select" required>
                                            <option value="">-- Pilih Kurir --</option>
                                            <?php
                                            $kurir = mysqli_query($conn, "SELECT * FROM kurir");
                                            while ($k = mysqli_fetch_assoc($kurir)) {
                                                $selected = (isset($edit_data) && $edit_data['idkurir'] == $k['idkurir']) ? 'selected' : '';
                                                echo "<option value='{$k['idkurir']}' $selected>{$k['nama']} ({$k['kendaraan']})</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Gudang</label>
                                        <select name="idgudang" class="form-select" required>
                                            <option value="">-- Pilih Gudang --</option>
                                            <?php
                                            $gudang = mysqli_query($conn, "SELECT * FROM gudang");
                                            while ($g = mysqli_fetch_assoc($gudang)) {
                                                $selected = (isset($edit_data) && $edit_data['idgudang'] == $g['idgudang']) ? 'selected' : '';
                                                echo "<option value='{$g['idgudang']}' $selected>{$g['namagudang']} - {$g['kota']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Pengirim</label>
                                        <input type="text" name="nama_pengirim" class="form-control" required value="<?= $edit_data['nama_pengirim'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No HP Pengirim</label>
                                        <input type="text" name="no_hp_pengirim" class="form-control" required value="<?= $edit_data['no_hp_pengirim'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat Pengirim</label>
                                        <textarea name="alamat_pengirim" class="form-control" required><?= $edit_data['alamat_pengirim'] ?? '' ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Estimasi Sampai</label>
                                        <input type="date" name="estimasi_sampai" class="form-control" required value="<?= $edit_data['estimasi_sampai'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Pengiriman</label>
                                        <input type="date" name="tanggalpengiriman" class="form-control" required value="<?= $edit_data['tanggalpengiriman'] ?? '' ?>">
                                    </div>
                                    
                                    <button type="submit" name="<?= isset($edit_data) ? 'edit' : 'tambah' ?>" class="btn btn-primary">
                                        <?= isset($edit_data) ? 'Update' : 'Simpan' ?>
                                    </button>
                                    <?php if (isset($edit_data)): ?>
                                        <a href="order.php" class="btn btn-secondary">Batal</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Data Pengiriman -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Daftar Pengiriman
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pelanggan</th>
                                            <th>Kurir</th>
                                            <th>Gudang</th>
                                            <th>Nama Pengirim</th>
                                            <th>No HP</th>
                                            <th>Alamat</th>
                                            <th>Estimasi</th>
                                            <th>Tanggal Kirim</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $data = mysqli_query($conn, "CALL OrderDetail");
                                        while ($row = mysqli_fetch_assoc($data)) {
                                            echo "<tr>
                                                <td>{$row['idpengiriman']}</td>
                                                <td>{$row['nama_pelanggan']}</td>
                                                <td>{$row['nama_kurir']}</td>
                                                <td>{$row['nama_gudang']}</td>
                                                <td>{$row['nama_pengirim']}</td>
                                                <td>{$row['no_hp_pengirim']}</td>
                                                <td>{$row['alamat_pengirim']}</td>
                                                <td>{$row['estimasi_sampai']}</td>
                                                <td>{$row['tanggalpengiriman']}</td>
                                                <td>
                                                    <a href='order.php?edit={$row['idpengiriman']}' class='btn btn-sm btn-warning'>Edit</a>
                                                    <a href='order.php?hapus={$row['idpengiriman']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
</html>
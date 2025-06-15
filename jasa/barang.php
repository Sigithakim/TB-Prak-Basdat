<?php
include 'function.php';
// Simpan barang baru
if (isset($_POST['tambah'])) {
    $idpengiriman = $_POST['idpengiriman'];
    $nama_barang = $_POST['nama_barang'];
    $berat = $_POST['berat'];

    mysqli_query($conn, "INSERT INTO barang (idpengiriman, nama_barang, berat)
                         VALUES ('$idpengiriman', '$nama_barang', '$berat')");
    header("Location: barang.php");
}

// Update data barang
if (isset($_POST['edit'])) {
    $idbarang = $_POST['idbarang'];
    $idpengiriman = $_POST['idpengiriman'];
    $nama_barang = $_POST['nama_barang'];
    $berat = $_POST['berat'];

    mysqli_query($conn, "UPDATE barang SET 
        idpengiriman='$idpengiriman', 
        nama_barang='$nama_barang', 
        berat='$berat'
        WHERE idbarang='$idbarang'");
    header("Location: barang.php");
}

// Hapus data barang
if (isset($_GET['hapus'])) {
    $idbarang = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM barang WHERE idbarang='$idbarang'");
    header("Location: barang.php");
}

// Ambil data untuk mode edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM barang WHERE idbarang='$id'");
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
        <title>Data Barang</title><meta charset="UTF-8">
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
                        <h1 class="mt-4" style="margin-left: 30px; margin-top: 30px">Data Barang</h1>
                        
                        <!-- Form Tambah/Edit Data -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header">
                                <h4><?= isset($edit_data) ? 'Edit' : 'Tambah' ?> Data Barang</h4>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="idbarang" value="<?= $edit_data['idbarang'] ?? '' ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Pengiriman</label>
                                        <select name="idpengiriman" class="form-select" required>
                                            <option value="">-- Pilih Pengiriman --</option>
                                            <?php
                                            $order = mysqli_query($conn, "
                                                SELECT o.idpengiriman, c.nama AS pelanggan 
                                                FROM `order` o
                                                JOIN customer c ON o.idpelanggan = c.idpelanggan
                                            ");
                                            while ($o = mysqli_fetch_assoc($order)) {
                                                $selected = (isset($edit_data) && $edit_data['idpengiriman'] == $o['idpengiriman']) ? 'selected' : '';
                                                echo "<option value='{$o['idpengiriman']}' $selected>ID {$o['idpengiriman']} - {$o['pelanggan']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Barang</label>
                                        <input type="text" name="nama_barang" class="form-control" required value="<?= $edit_data['nama_barang'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Berat Barang (kg)</label>
                                        <input type="number" step="0.01" name="berat" class="form-control" required value="<?= $edit_data['berat'] ?? '' ?>">
                                    </div>
                                    
                                    <button type="submit" name="<?= isset($edit_data) ? 'edit' : 'tambah' ?>" class="btn btn-primary">
                                        <?= isset($edit_data) ? 'Update' : 'Simpan' ?>
                                    </button>
                                    <?php if (isset($edit_data)): ?>
                                        <a href="barang.php" class="btn btn-secondary">Batal</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Data Barang -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Daftar Barang
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID Barang</th>
                                            <th>ID Pengiriman</th>
                                            <th>Pelanggan</th>
                                            <th>Nama Barang</th>
                                            <th>Berat (kg)</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $data = mysqli_query($conn, "CALL BarangPelanggan()");
                                        while ($row = mysqli_fetch_assoc($data)) {
                                            echo "<tr>
                                                <td>{$row['idbarang']}</td>
                                                <td>{$row['idpengiriman']}</td>
                                                <td>{$row['pelanggan']}</td>
                                                <td>{$row['nama_barang']}</td>
                                                <td>{$row['berat']}</td>
                                                <td>
                                                    <a href='barang.php?edit={$row['idbarang']}' class='btn btn-sm btn-warning'>Edit</a>
                                                    <a href='barang.php?hapus={$row['idbarang']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
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
<?php
include 'function.php';

// Simpan penyimpanan barang
if (isset($_POST['tambah_penyimpanan'])) {
    $idbarang = $_POST['idbarang'];
    $idgudang = $_POST['idgudang'];
    $waktu_masuk = $_POST['waktu_masuk'];
    $waktu_keluar = $_POST['waktu_keluar'];

    mysqli_query($conn, "INSERT INTO penyimpanan_barang (idbarang, idgudang, waktu_masuk, waktu_keluar)
                         VALUES ('$idbarang', '$idgudang', '$waktu_masuk', '$waktu_keluar')");
    header("Location: penyimpanan.php");
}

// Hapus penyimpanan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM penyimpanan_barang WHERE idpenyimpanan='$id'");
    header("Location: penyimpanan.php");
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
        <title>Penyimpanan Barang</title><meta charset="UTF-8">
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
                        <h1 class="mt-4" style="margin-left: 30px; margin-top: 30px">Penyimpanan Barang</h1>
                        
                        <!-- Form Penyimpanan Barang -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header">
                                <h4>Tambah Penyimpanan Barang</h4>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Barang</label>
                                        <select name="idbarang" class="form-select" required>
                                            <option value="">-- Pilih Barang --</option>
                                            <?php
                                            $barang = mysqli_query($conn, "SELECT b.idbarang, b.nama_barang, c.nama AS pelanggan 
                                                                          FROM barang b
                                                                          JOIN `order` o ON b.idpengiriman = o.idpengiriman
                                                                          JOIN customer c ON o.idpelanggan = c.idpelanggan");
                                            while ($b = mysqli_fetch_assoc($barang)) {
                                                echo "<option value='{$b['idbarang']}'>ID {$b['idbarang']} - {$b['nama_barang']} ({$b['pelanggan']})</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Pilih Gudang</label>
                                        <select name="idgudang" class="form-select" required>
                                            <option value="">-- Pilih Gudang --</option>
                                            <?php
                                            $gudang = mysqli_query($conn, "SELECT * FROM gudang");
                                            while ($g = mysqli_fetch_assoc($gudang)) {
                                                echo "<option value='{$g['idgudang']}'>{$g['namagudang']} - {$g['kota']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Waktu Masuk</label>
                                        <input type="datetime-local" name="waktu_masuk" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Waktu Keluar</label>
                                        <input type="datetime-local" name="waktu_keluar" class="form-control" required>
                                    </div>

                                    <button type="submit" name="tambah_penyimpanan" class="btn btn-primary">Simpan</button>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Data Penyimpanan -->
                        <div class="card mb-4" style="width: 100%; margin-left: 15px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Daftar Penyimpanan Barang
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID Penyimpanan</th>
                                            <th>Barang</th>
                                            <th>Gudang</th>
                                            <th>Waktu Masuk</th>
                                            <th>Waktu Keluar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $data = mysqli_query($conn, "CALL Penyimpanan()");
                                        while ($row = mysqli_fetch_assoc($data)) {
                                            echo "<tr>
                                                <td>{$row['idpenyimpanan']}</td>
                                                <td>{$row['nama_barang']}</td>
                                                <td>{$row['namagudang']}</td>
                                                <td>{$row['waktu_masuk']}</td>
                                                <td>{$row['waktu_keluar']}</td>
                                                <td>
                                                    <a href='penyimpanan.php?hapus={$row['idpenyimpanan']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
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
<?php
include 'function.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM kurir WHERE idkurir = $id");
    header("Location: kurir.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $notelepon = $_POST['notelepon'];
    $kendaraan = $_POST['kendaraan'];
    $conn->query("UPDATE kurir SET nama='$nama', notelepon='$notelepon', kendaraan='$kendaraan' WHERE idkurir=$id");
    header("Location: kurir.php");
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $notelepon = $_POST['notelepon'];
    $kendaraan = $_POST['kendaraan'];
    $conn->query("INSERT INTO kurir (nama, notelepon, kendaraan) VALUES ('$nama', '$notelepon', '$kendaraan')");
    header("Location: kurir.php");
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
        <title>Kurir</title><meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <main>
            <div class="container-fluid px-4" style="margin-top: 80px">
                <h1 class="mt-4" style="margin-left: 250px; margin-top: 50px" >Kurir</h1>
            
            <form action="kurir.php" method="post">
            <div class="modal fade" id="addModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="kurir.php">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah Kurir</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nama">Nama:</label>
                                    <input type="text" name="nama" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="notelepon">No. Telepon:</label>
                                    <input type="text" name="notelepon" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="kendaraan">Kendaraan:</label>
                                    <input type="text" name="kendaraan" class="form-control" required>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </form>
        </div>
        </main>
        <div class="card mb-4" style="width: 80%; margin-left: 250px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Kurir
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                    Tambah
                </button>
            </div>
            <div class="card-body">
                <table  class="table table-bordered table-striped text-center w-100">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No. Telepon</th>
                            <th>Kendaraan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM kurir");
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $row['nama']; ?></td>
                            <td><?= $row['notelepon']; ?></td>
                            <td><?= $row['kendaraan']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="<?= $row['idkurir'] ?>"
                                    data-nama="<?= $row['nama'] ?>"
                                    data-notelepon="<?= $row['notelepon'] ?>"
                                    data-kendaraan="<?= $row['kendaraan'] ?>"
                                    data-toggle="modal" data-target="#editModal">
                                    Edit
                                </button>|
                                <a href="?delete=<?= $row['idkurir'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="editModal">
            <div class="modal-dialog">
                <form method="post" class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Kurir</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label>Nama:</label>
                            <input type="text" name="nama" id="edit-nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>No. Telepon:</label>
                            <input type="text" name="notelepon" id="edit-notelepon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Kendaraan:</label>
                            <input type="text" name="kendaraan" id="edit-kendaraan" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit_edit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
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
        <script>
            // Auto isi modal edit
            $('.btn-edit').click(function () {
                $('#edit-id').val($(this).data('id'));
                $('#edit-nama').val($(this).data('nama'));
                $('#edit-notelepon').val($(this).data('notelepon'));
                $('#edit-kendaraan').val($(this).data('kendaraan'));
            });
        </script>

    </body>
</html>

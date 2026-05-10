<?php
$role = 'admin'; 
$active_menu = $active_menu ?? '';

$nama_user = $nama_user ?? $this->session->userdata('nama');
if (empty($nama_user)) {
    $nama_user = $this->session->userdata('username') ?? $this->session->userdata('email');
}
if (empty($nama_user)) {
    $nama_user = 'Administrator';
}

$title = $title ?? 'SPK SAW';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | <?= ucfirst($role) ?> Panel</title>

    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/compiled/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/compiled/css/app-dark.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/mazer/dist/assets/extensions/apexcharts/apexcharts.css') ?>">

    <style>
        .summary-card {
            transition: transform .2s, box-shadow .2s;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        }

        .sidebar-link .badge {
            font-size: 10px;
            padding: 3px 7px;
        }

        .avatar-initials {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #435ebe 0%, #5a75c4 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-top {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 1.5rem;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .nav-link.dropdown-toggle {
            padding: 0;
        }
    </style>
</head>

<body>
    <div id="app">

        <!-- SIDEBAR -->
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-brand d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart-fill text-primary fs-4"></i>
                                <span style="font-weight:700;font-size:1.1rem;">SPK SAW</span>
                            </a>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block">
                                <i class="bi bi-x bi-middle"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu Utama</li>
                        <li class="sidebar-item <?= ($active_menu == 'dashboard') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Master Data</li>
                        <li class="sidebar-item <?= ($active_menu == 'kriteria') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/kriteria') ?>" class="sidebar-link">
                                <i class="bi bi-sliders"></i>
                                <span>Kriteria & Bobot</span>
                            </a>
                        </li>
                        <li class="sidebar-item <?= ($active_menu == 'alternatif') ? 'active' : '' ?>">
                            <a href="<?= base_url('admin/alternatif') ?>" class="sidebar-link">
                                <i class="bi bi-diagram-3"></i>
                                <span>Alternatif</span>
                            </a>
                        </li>

                        <li class="sidebar-title">SAW</li>
                        <li class="sidebar-item <?= ($active_menu == 'penilaian') ? 'active' : '' ?>">
                            <a href="<?= base_url('saw/penilaian') ?>" class="sidebar-link">
                                <i class="bi bi-pencil-square"></i>
                                <span>Proses Hitung SAW</span>
                            </a>
                        </li>
                       
                        <li class="sidebar-item <?= ($active_menu == 'hasil') ? 'active' : '' ?>">
                            <a href="<?= base_url('saw/hasil') ?>" class="sidebar-link">
                                <i class="bi bi-bar-chart-steps"></i>
                                <span>Hasil SAW</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main">
            <header>
                <nav class="navbar navbar-expand navbar-light navbar-top">
                    <div class="container-fluid">
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                        <div class="navbar-nav ms-auto">
                            <div class="dropdown">
                                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-initials">
                                            <?= strtoupper(substr($nama_user, 0, 1)) ?>
                                        </div>
                                        <div class="d-none d-md-block text-end">
                                            <span class="fw-semibold d-block"><?= htmlspecialchars($nama_user) ?></span>
                                            <small class="text-muted">Admin</small>
                                        </div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <h6 class="dropdown-header">Admin Panel</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/profil') ?>">
                                            <i class="bi bi-person me-2"></i> Profil
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>">
                                            <i class="bi bi-box-arrow-left me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <?php
            // Inisialisasi variabel untuk menghindari error undefined di Text Editor
            $content = isset($content) ? $content : '';
            ?>

            <div id="main-content">
                <?= $content ?>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p><?= date('Y') ?> &copy; 1922041 - 19221061</p>
                    </div>
                    <div class="float-end">
                        <p>Metode Simple Additive Weighting</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="<?= base_url('assets/mazer/dist/assets/compiled/js/app.js') ?>"></script>
    <script src="<?= base_url('assets/mazer/dist/assets/extensions/apexcharts/apexcharts.min.js') ?>"></script>
    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>

</html>
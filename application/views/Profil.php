<?php
$user = isset($user) ? $user : (object)[];
$role = isset($role) ? $role : '';
?>

<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Profil Saya</h3>
        <!-- Teks deskripsi disesuaikan karena form ubah nama dihapus -->
        <p class="text-subtitle text-muted">Informasi akun dan ubah kata sandi</p>
    </div>

    <?php if (isset($this->session) && $this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if (isset($this->session) && $this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Informasi Akun -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent">
                    <h4 class="card-title mb-0">Informasi Akun</h4>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 30%">Username</th>
                            <td><?= isset($user->username) ? htmlspecialchars($user->username) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= isset($user->email) ? htmlspecialchars($user->email) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?= isset($user->role) ? ucfirst($user->role) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td><?= isset($user->created_at) ? date('d M Y', strtotime($user->created_at)) : '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Ubah Kata Sandi -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                    <h4 class="card-title mb-0">Ubah Kata Sandi</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url($role == 'admin' ? 'admin/profil_update' : 'user/profil_update') ?>" method="post">
                        <div class="mb-3">
                            <label class="form-label">Kata Sandi Lama</label>
                            <input type="password" name="old_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" name="new_password" class="form-control" minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Perbarui Kata Sandi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
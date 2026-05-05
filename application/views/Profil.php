<?php
$user = isset($user) ? $user : (object)[];
$role = isset($role) ? $role : '';
?>

<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Profil Saya</h3>
        <p class="text-subtitle text-muted">Informasi akun dan ubah password</p>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0">
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
                            <th>Dibuat</th> <!-- Bug tag penutup </th> yang hilang sudah diperbaiki di sini -->
                            <td><?= isset($user->created_at) ? date('d M Y', strtotime($user->created_at)) : '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                    <h4 class="card-title mb-0">Ubah Password</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($this->session) && $this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if (isset($this->session) && $this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url($role == 'admin' ? 'admin/profil_update' : 'user/profil_update') ?>" method="post">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
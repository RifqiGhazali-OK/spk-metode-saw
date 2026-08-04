<?php
$nama_user = $nama_user ?? 'Manajer/Direktur';
$list      = $list ?? [];
?>

<div class="page-heading mb-2">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="mb-1">Periode Penilaian</h3>
                <p class="text-subtitle text-muted mb-0">Daftar periode yang sudah memiliki hasil perhitungan SAW</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($list)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">Belum ada periode dengan hasil penilaian.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($list as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('user/detail_perhitungan/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-calculator"></i> Detail Perhitungan
                                        </a>
                                        <a href="<?= base_url('user/hasil_rekomendasi/' . $p['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-clipboard-check"></i> Hasil Rekomendasi
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="page-heading">
    <div class="page-title mb-2">
        <div class="row align-items-end">
            <div class="col-12 col-md-12">
                <h3 class="mb-0">Dashboard</h3>
                <p class="text-subtitle text-muted small">Ringkasan data Sistem Pendukung Keputusan SAW</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Kriteria -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3 bg-primary bg-opacity-10 rounded-3 p-2">
                            <i class="bi bi-ui-checks fs-5 text-primary"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-0">Total Kriteria</p>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_kriteria ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Alternatif -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3 bg-success bg-opacity-10 rounded-3 p-2">
                            <i class="bi bi-diagram-3 fs-5 text-success"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-0">Total Alternatif</p>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_alternatif ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Hasil SAW -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3 bg-info bg-opacity-10 rounded-3 p-2">
                            <i class="bi bi-bar-chart-line-fill fs-5 text-info"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-0">Total Hasil SAW</p>
                            <h3 class="mb-0 fw-bold"><?= number_format($total_hasil ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart and Info -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h4 class="card-title mb-0">Grafik Skor SAW — Top 10</h4>
                    <a href="<?= base_url('saw/hasil') ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Semua Hasil
                    </a>
                </div>
                <div class="card-body pt-0">
                    <div id="chart-saw" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3">
                    <h4 class="card-title mb-0">Informasi Sistem</h4>
                </div>
                <div class="card-body pt-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center px-0 py-2">
                            <div class="bg-light-primary rounded-3 p-2 me-3"><i class="bi bi-cpu-fill text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">Metode</h6><small class="text-muted">Simple Additive Weighting (SAW)</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0 py-2">
                            <div class="bg-light-success rounded-3 p-2 me-3"><i class="bi bi-trophy-fill text-success"></i></div>
                            <div>
                                <h6 class="mb-0">Nilai Tertinggi</h6><small class="text-muted fw-medium"><?= number_format($top_nilai ?? 0, 6) ?></small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0 py-2">
                            <div class="bg-light-warning rounded-3 p-2 me-3"><i class="bi bi-calendar-check-fill text-warning"></i></div>
                            <div>
                                <h6 class="mb-0">Waktu Saat Ini</h6><small class="text-muted" id="liveClockAdmin"></small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0 py-2">
                            <div class="bg-light-danger rounded-3 p-2 me-3"><i class="bi bi-shield-fill-check text-danger"></i></div>
                            <div>
                                <h6 class="mb-0">Status</h6><small class="text-success fw-semibold">● Sistem Aktif</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ranking Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h4 class="card-title mb-0">Ranking Terbaru — Top 10</h4>
                    <a href="<?= base_url('saw/hasil') ?>" class="btn btn-sm btn-primary rounded-pill">
                        <i class="bi bi-eye me-1"></i> Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="white-space: nowrap;">Rank</th>
                                    <th style="white-space: nowrap;">Kode</th>
                                    <th style="white-space: nowrap;">Nama Alternatif</th>
                                    <th style="white-space: nowrap;">Jabatan</th>
                                    <th style="white-space: nowrap;">Nilai Akhir</th>
                                    <th style="white-space: nowrap;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($hasil_ranking)): ?>
                                    <?php foreach ($hasil_ranking as $row): ?>
                                        <tr>
                                            <td style="white-space: nowrap;">
                                                <?php if ($row['ranking'] == 1): ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-trophy-fill me-1"></i> #1</span>
                                                <?php elseif ($row['ranking'] <= 3): ?>
                                                    <span class="badge bg-light-primary text-primary px-3 py-2 rounded-pill">#<?= $row['ranking'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-pill">#<?= $row['ranking'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="white-space: nowrap;"><span class="badge bg-light text-dark px-3 py-2 rounded-pill"><?= htmlspecialchars($row['kode']) ?></span></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($row['nama_alternatif']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                                            <td style="white-space: nowrap;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height:8px; min-width: 80px;">
                                                        <div class="progress-bar bg-primary" style="width: <?= $row['nilai_akhir'] * 100 ?>%"></div>
                                                    </div>
                                                    <span class="small fw-semibold"><?= number_format($row['nilai_akhir'], 4) ?></span>
                                                </div>
                                            </td>
                                            <td style="white-space: nowrap;"><span class="badge <?= $row['status'] == 'Layak' ? 'bg-success' : 'bg-danger' ?> px-3 py-2 rounded-pill"><?= $row['status'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada data hasil SAW.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $labels = [];
    $scores = [];
    if (!empty($hasil_ranking)) {
        foreach ($hasil_ranking as $r) {
            $labels[] = $r['kode'] . ' - ' . $r['nama_alternatif'];
            $scores[] = (float) $r['nilai_akhir'];
        }
    }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateClock() {
                var now = new Date();
                var days = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                var day = now.getDate();
                var month = days[now.getMonth()];
                var year = now.getFullYear();
                var hours = now.getHours().toString().padStart(2, '0');
                var minutes = now.getMinutes().toString().padStart(2, '0');
                var seconds = now.getSeconds().toString().padStart(2, '0');
                document.getElementById('liveClockAdmin').innerText = day + ' ' + month + ' ' + year + ', ' + hours + ':' + minutes + ':' + seconds;
            }
            setInterval(updateClock, 1000);
            updateClock();

            var labels = <?= json_encode($labels) ?>;
            var scores = <?= json_encode($scores) ?>;
            if (labels.length === 0) {
                document.getElementById('chart-saw').innerHTML = '<div class="text-center text-muted py-5">Tidak ada data untuk ditampilkan</div>';
                return;
            }
            var options = {
                series: [{
                    name: 'Nilai Akhir SAW',
                    data: scores
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    fontFamily: '"Inter", system-ui'
                },
                colors: ['#435ebe'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '55%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    formatter: (val) => val.toFixed(4),
                    style: {
                        fontSize: '11px',
                        fontWeight: 600
                    }
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        rotate: -15,
                        rotateAlways: true,
                        trim: false,
                        style: {
                            fontSize: '11px',
                            fontWeight: 500,
                            fontStyle: 'italic'
                        }
                    },
                    title: {
                        text: 'Alternatif'
                    }
                },
                yaxis: {
                    min: 0,
                    max: 1,
                    labels: {
                        formatter: (val) => val.toFixed(2)
                    },
                    title: {
                        text: 'Nilai Akhir'
                    }
                },
                grid: {
                    borderColor: '#e9ecef',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: (val) => val.toFixed(4)
                    }
                }
            };
            var style = document.createElement('style');
            style.innerHTML = '.apexcharts-xaxis text { white-space: normal !important; word-break: break-word; }';
            document.head.appendChild(style);
            new ApexCharts(document.querySelector("#chart-saw"), options).render();
        });
    </script>
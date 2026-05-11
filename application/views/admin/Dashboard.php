<?php
// Setup untuk menghindari error 'undefined variable'
$nama_user        = $nama_user ?? 'Admin';
$total_kriteria   = $total_kriteria ?? 0;
$total_alternatif = $total_alternatif ?? 0;
$total_hasil      = $total_hasil ?? 0;
?>

<!-- Header Dashboard -->
<div class="page-heading mb-2">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="mb-1">Dashboard Admin</h3>
                <p class="text-subtitle text-muted mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($nama_user) ?></strong>!</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Row: Kartu Ringkasan Data -->
    <div class="row mb-2">
        <div class="col-12 col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Kriteria</p>
                        <h4 class="fw-bolder mb-0 text-dark"><?= $total_kriteria ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Karyawan</p>
                        <h4 class="fw-bolder mb-0 text-dark"><?= $total_alternatif ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="bi bi-clipboard-data-fill"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Telah Dinilai</p>
                        <h4 class="fw-bolder mb-0 text-dark"><?= $total_hasil ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row: Tampilan Grafik Utama -->
    <div class="row">
        <!-- Kolom Chart Doughnut -->
        <div class="col-12 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 text-center">
                    <h5 class="card-title mb-1">Status Per Departement</h5>
                    <p class="text-sm text-muted mb-2">Arahkan kursor untuk detail rincian</p>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center pb-4 pt-3">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="deptStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Chart Bar (Ranking) -->
        <div class="col-12 col-xl-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center pb-3">
                    <h4 class="card-title mb-0">Ranking Alternatif (Top 10)</h4>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="rankingBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Elemen penampung untuk custom tooltip Doughnut -->
<div id="chartTooltip" style="position:absolute; pointer-events:none; display:none; z-index:999;"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Parsing data dari controller PHP ke array JavaScript
        const labelsArr = <?= $chart_labels ?? '[]' ?>;
        const totalArr = <?= $chart_data ?? '[]' ?>;
        const layakArr = <?= $chart_layak ?? '[]' ?>;
        const tundaArr = <?= $chart_pertimbangkan ?? '[]' ?>;
        const totalSemua = totalArr.reduce((a, b) => a + b, 0);

        const barLabelsArr = <?= $bar_labels ?? '[]' ?>;
        const barValuesArr = <?= $bar_values ?? '[]' ?>;
        const barDeptArr = <?= $bar_dept ?? '[]' ?>;
        const barStatusArr = <?= $bar_status ?? '[]' ?>;

        const palette = ['#435ebe', '#55c6e8', '#ffc107', '#dc3545', '#198754', '#6f42c1', '#fd7e14'];

        /* -------------------------------------------------------------
         * Inisialisasi Doughnut Chart (Status Departemen)
         * ------------------------------------------------------------- */
        const ctxDoughnut = document.getElementById('deptStatusChart').getContext('2d');

        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: labelsArr,
                datasets: [{
                    data: totalArr,
                    backgroundColor: palette.slice(0, labelsArr.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                layout: {
                    padding: {
                        bottom: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 16,
                            font: {
                                size: 11
                            }
                        }
                    },
                    // Mengganti tooltip bawaan dengan elemen HTML terpisah
                    tooltip: {
                        enabled: false,
                        external: function(context) {
                            const tooltipEl = document.getElementById('chartTooltip');
                            const tooltip = context.tooltip;

                            if (tooltip.opacity === 0) {
                                tooltipEl.style.display = 'none';
                                return;
                            }

                            const idx = tooltip.dataPoints[0].dataIndex;
                            const label = tooltip.dataPoints[0].label;
                            const layak = layakArr[idx];
                            const tunda = tundaArr[idx];
                            const color = palette[idx % palette.length];

                            // Styling inline untuk menjaga komponen tetap ringkas
                            tooltipEl.innerHTML = `
                                <div style="background: rgba(33,37,41,0.95); color: #fff; padding: 12px 16px; border-radius: 8px; font-family: sans-serif; font-size: 13px; min-width: 210px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                                        <span style="display:inline-block; width:12px; height:12px; background:${color}; border-radius:2px; flex-shrink:0;"></span>
                                        <strong style="font-size:14px;">${label}</strong>
                                    </div>
                                    <div style="font-family:'Courier New',monospace; font-size:13px; line-height:1.9;">
                                        <div>Layak         : ${layak} Orang</div>
                                        <div>Pertimbangkan : ${tunda} Orang</div>
                                    </div>
                                </div>
                            `;

                            const canvas = context.chart.canvas;
                            const rect = canvas.getBoundingClientRect();
                            tooltipEl.style.display = 'block';
                            tooltipEl.style.position = 'absolute';
                            tooltipEl.style.left = rect.left + window.scrollX + tooltip.caretX + 12 + 'px';
                            tooltipEl.style.top = rect.top + window.scrollY + tooltip.caretY - 10 + 'px';
                        }
                    }
                }
            },
            plugins: [{
                // Custom plugin untuk merender teks di titik pusat canvas
                id: 'centerText',
                beforeDraw: function(chart) {
                    const {
                        width,
                        height,
                        ctx
                    } = chart;
                    ctx.restore();

                    const meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || meta.data.length === 0) return;

                    // Koordinat tengah dinamis berdasarkan render meta data donat
                    const centerX = meta.data[0].x;
                    const centerY = meta.data[0].y;

                    const fontSizeNum = Math.min(width, height) / 5;
                    ctx.font = `bold ${fontSizeNum}px sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#25396f';
                    ctx.fillText(String(totalSemua), centerX, centerY - (fontSizeNum * 0.15));

                    const fontSizeLabel = Math.min(width, height) / 15;
                    ctx.font = `bold ${fontSizeLabel}px sans-serif`;
                    ctx.fillStyle = '#7c8db5';
                    ctx.fillText('TOTAL DATA', centerX, centerY + (fontSizeNum * 0.55));

                    ctx.save();
                }
            }]
        });

        /* -------------------------------------------------------------
         * Inisialisasi Bar Chart (Ranking Alternatif)
         * ------------------------------------------------------------- */
        const canvasBar = document.getElementById('rankingBarChart');
        if (canvasBar && barLabelsArr.length > 0) {

            // Pewarnaan dinamis berdasarkan status kelayakan
            const barColors = barStatusArr.map(status => status === 'Layak' ? '#198754' : '#dc3545');
            const ctxBar = canvasBar.getContext('2d');

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: barLabelsArr,
                    datasets: [{
                        label: 'Nilai Akhir',
                        data: barValuesArr,
                        backgroundColor: barColors,
                        borderRadius: 4,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1,
                            ticks: {
                                stepSize: 0.2
                            },
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(33,37,41,0.95)',
                            padding: 14,
                            displayColors: false,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: "'Courier New', Courier, monospace",
                                size: 13
                            },
                            callbacks: {
                                title: function(items) {
                                    const idx = items[0].dataIndex;
                                    return barDeptArr[idx] || 'Tanpa Divisi';
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const status = barStatusArr[idx] || '-';
                                    const nilai = context.raw.toFixed(3);

                                    // Spasi padEnd untuk menyejajarkan titik dua
                                    const lblStatus = "Status".padEnd(11, ' ');
                                    const lblNilai = "Nilai Akhir".padEnd(11, ' ');

                                    return [
                                        `${lblStatus}: ${status}`,
                                        `${lblNilai}: ${nilai}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
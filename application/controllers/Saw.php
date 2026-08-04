<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Saw extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load library dan model yang dibutuhkan
        $this->load->library('form_validation');
        $this->load->model([
            'Alternatif_model',
            'Kriteria_model',
            'Hasil_model',
            'User_model',
            'Periode_model',
            'Nilai_model',
            'Saw_model'
        ]);

        // Proteksi Halaman (Auth)
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            redirect('auth');
        }

        // Set nama user ke session jika belum ada
        if (!$this->session->userdata('nama')) {
            $user = $this->User_model->get_by_id((int)$this->session->userdata('id'));
            if ($user) {
                $this->session->set_userdata('nama', $user->username ?? $user->email);
            }
        }
    }

    /* ====================================================
     * FUNGSI HELPER
     * ==================================================== */
    private function _get_nama_user(): string
    {
        return $this->session->userdata('nama')
            ?? $this->session->userdata('username')
            ?? $this->session->userdata('email')
            ?? 'Administrator';
    }

    private function _get_active_periode(): int
    {
        $periode = $this->Periode_model->get_active_by_month();
        return $periode ? (int)$periode->id : 1;
    }

    /* ====================================================
     * HALAMAN PENILAIAN & PROSES SAW
     * ==================================================== */
    public function penilaian(): void
    {
        $user_id = (int)$this->session->userdata('id');
        $periode_id = (int)(
            $this->input->post('periode_id')
            ?: $this->input->get('periode_id')
            ?: $this->_get_active_periode()
        );

        $alternatif     = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria       = $this->Kriteria_model->get_all();
        $nilai_existing = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);
        $periode_list   = $this->Periode_model->get_all();

        // Mapping nilai untuk ditampilkan di view
        $nilai_map = [];
        foreach ($nilai_existing as $n) {
            $nilai_map[$n['alternatif_id']][$n['kriteria_id']] = $n['nilai'];
        }

        // Validasi kelengkapan data penilaian
        $total_required    = count($alternatif) * count($kriteria);
        $jumlah_penilaian  = $this->Nilai_model->count_by_user_and_periode($user_id, $periode_id);
        $penilaian_lengkap = ($total_required > 0 && $jumlah_penilaian >= $total_required);

        // Parameter kontrol tampilan
        $force_edit    = (bool)$this->input->get('force_edit');
        $tombol_proses = (bool)$this->input->post('proses_hitung');

        // Cek apakah hasil sudah pernah disimpan sebelumnya
        $cek_hasil = $this->Hasil_model->exists($user_id, $periode_id);

        // Setup default nilai awal
        $show_hasil = false;
        $saw = [
            'matrix'     => [],
            'normalized' => [],
            'weighted'   => [],
            'final'      => [],
            'kriteria'   => $kriteria,
            'alternatif' => $alternatif,
        ];

        /* KONDISI TAMPILAN HALAMAN */
        // Kondisi 1: Hasil sudah tersimpan & lengkap -> Langsung tampil hasil perhitungan
        if ($cek_hasil && $penilaian_lengkap && !$force_edit && !$tombol_proses) {
            $saw        = $this->Saw_model->calculate_saw($user_id, $periode_id);
            $show_hasil = true;
        }
        // Kondisi 2: Tombol "Proses Hitung" diklik
        elseif ($tombol_proses) {
            if (!$penilaian_lengkap) {
                $this->session->set_flashdata(
                    'error',
                    'Penilaian belum lengkap! Semua nilai harus diisi terlebih dahulu.'
                );
                redirect('saw/penilaian?periode_id=' . $periode_id . '&force_edit=1');
                return;
            }
            $saw        = $this->Saw_model->calculate_saw($user_id, $periode_id);
            $show_hasil = true;
        }
        // Kondisi 3: force_edit=1 atau belum ada hasil sama sekali -> Form input penilaian otomatis tampil

        $data = [
            // Page Setup
            'title'               => 'Penilaian & Proses SAW',
            'active_menu'         => 'penilaian',

            // User Info
            'role'                => $this->session->userdata('role'),
            'nama_user'           => $this->_get_nama_user(),

            // Data Master
            'alternatif'          => $alternatif,
            'kriteria'            => $kriteria,
            'periode_list'        => $periode_list,

            // Status Penilaian
            'nilai_map'           => $nilai_map,
            'periode_id_selected' => $periode_id,
            'penilaian_lengkap'   => $penilaian_lengkap,
            'total_required'      => $total_required,
            'jumlah_penilaian'    => $jumlah_penilaian,

            // Hasil SAW (jika ditampilkan)
            'show_hasil'          => $show_hasil,
            'saw'                 => $saw,
            'matrix'              => $saw['matrix'],
            'normalized'          => $saw['normalized'],
            'weighted'            => $saw['weighted'],
            'final'               => $saw['final'],
        ];

        $data['content'] = $this->load->view('saw/penilaian_saw', $data, true);
        $this->load->view('layout/template', $data);
    }

    /* ====================================================
     * AJAX: SIMPAN NILAI SATUAN
     * ==================================================== */
    public function penilaian_save(): void
    {
        $user_id       = (int)$this->session->userdata('id');
        $alternatif_id = (int)$this->input->post('alternatif_id');
        $kriteria_id   = (int)$this->input->post('kriteria_id');
        $periode_id    = (int)($this->input->post('periode_id') ?: $this->_get_active_periode());

        // Konversi format angka
        $nilai_input = str_replace(',', '.', $this->input->post('nilai'));
        $nilai       = (float)$nilai_input;
        if (!$alternatif_id || !$kriteria_id || $nilai <= 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Data tidak valid.'
            ]);
            return;
        }

        // Hapus nilai lama, lalu insert yang baru (replace)
        $this->Nilai_model->delete_nilai($user_id, $alternatif_id, $kriteria_id, $periode_id);
        $this->Nilai_model->insert_nilai([
            'user_id'       => $user_id,
            'periode_id'    => $periode_id,
            'alternatif_id' => $alternatif_id,
            'kriteria_id'   => $kriteria_id,
            'nilai'         => $nilai,
        ]);

        // Cek kembali kelengkapan untuk di-return ke AJAX
        $alternatif       = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria         = $this->Kriteria_model->get_all();
        $total_required   = count($alternatif) * count($kriteria);
        $jumlah_penilaian = $this->Nilai_model->count_by_user_and_periode($user_id, $periode_id);

        echo json_encode([
            'status'            => 'success',
            'penilaian_lengkap' => ($total_required > 0 && $jumlah_penilaian >= $total_required),
            'jumlah'            => $jumlah_penilaian,
            'total'             => $total_required,
        ]);
    }

    /* ====================================================
     * SIMPAN HASIL AKHIR KE DATABASE
     * ==================================================== */
    public function simpan_hasil(): void
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)$this->input->post('periode_id');
        $final      = json_decode((string)$this->input->post('final'), true);

        if (empty($final) || !$periode_id) {
            $this->session->set_flashdata('error', 'Data tidak valid.');
            redirect('saw/penilaian');
            return;
        }

        // Urutkan nilai dari yang tertinggi ke terendah
        arsort($final);
        $ranking    = 1;
        $data_hasil = [];

        foreach ($final as $alt_id => $nilai_akhir) {
            $data_hasil[] = [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => (int)$alt_id,
                'nilai_akhir'   => (float)$nilai_akhir,
                'ranking'       => $ranking++,
                'status'        => $nilai_akhir >= 0.70 ? 'Layak' : 'Pertimbangkan',
            ];
        }

        if ($this->Hasil_model->replace_hasil($user_id, $periode_id, $data_hasil)) {
            $this->session->set_flashdata('success', 'Hasil SAW berhasil disimpan.');
        }

        redirect('saw/hasil?periode_id=' . $periode_id);
    }

    /* ====================================================
     * HALAMAN HASIL / RANKING
     * ==================================================== */
    public function hasil(): void
    {
        $user_id      = (int)$this->session->userdata('id');
        $periode_id   = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());
        $periode_list = $this->Periode_model->get_all();

        // Ambil data ranking dari model
        $hasil = $this->Hasil_model->get_ranking(100, $user_id, $periode_id);

        $data = [
            'title'               => 'Hasil SAW',
            'active_menu'         => 'hasil',
            'periode_list'        => $periode_list,
            'periode_id_selected' => $periode_id,
            'hasil'               => $hasil,
            'role'                => $this->session->userdata('role'),
            'nama_user'           => $this->_get_nama_user(),
        ];
        $data['content'] = $this->load->view('saw/hasil', $data, true);
        $this->load->view('layout/template', $data);
    }


    /*Upload data hasil query metabase format*/
    public function penilaian_upload(): void
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)$this->input->post('periode_id');

        if (empty($periode_id)) {
            $this->session->set_flashdata('error', 'Pilih periode terlebih dahulu sebelum upload.');
            redirect('saw/penilaian');
        }

        if (empty($_FILES['file_excel']['name'])) {
            $this->session->set_flashdata('error', 'Silakan pilih file Excel untuk diupload.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        $file = $_FILES['file_excel'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx, .xls, atau .csv.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        require_once APPPATH . '../vendor/autoload.php';

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file Excel. Pastikan file tidak rusak.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        if (empty($rows) || count($rows) < 2) {
            $this->session->set_flashdata('error', 'File Excel kosong atau tidak memiliki baris data.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        /* ---------- 1. Deteksi kolom dari header SECARA OTOMATIS ---------- */
        $headerRow = -1;
        $header = [];

        $col_kode    = null;
        $col_nama    = null;
        $col_jabatan = null;

        foreach ($rows as $index => $row) {
            foreach ($row as $idx => $val) {
                $h = strtolower(trim((string)$val));
                if ($h === '') continue;

                if ($col_kode === null && strpos($h, 'kode') !== false) {
                    $col_kode = $idx;
                }
                if ($col_nama === null && (strpos($h, 'nama') !== false || strpos($h, 'karyawan') !== false)) {
                    $col_nama = $idx;
                }
                if ($col_jabatan === null && (strpos($h, 'jabatan') !== false || strpos($h, 'departemen') !== false)) {
                    $col_jabatan = $idx;
                }
            }

            if ($col_nama !== null) {
                $headerRow = $index;
                $header = $row;
                break;
            }
        }

        if ($headerRow === -1 || $col_nama === null) {
            $this->session->set_flashdata('error', 'Kolom "Nama" (atau "Karyawan") tidak ditemukan di file Excel. Pastikan header tersedia.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        /* ---------- 2. Cocokkan kolom kriteria berdasarkan kode ATAU nama ---------- */
        $kriteria_list = $this->Kriteria_model->get_all();

        $kriteria_by_kode = [];
        $kriteria_by_nama = [];
        foreach ($kriteria_list as $k) {
            $kriteria_by_kode[strtoupper(trim($k['kode']))] = $k['id'];
            $kriteria_by_nama[$this->_normalisasi_teks($k['nama'])] = $k['id'];
        }

        $kolom_kriteria       = []; // index_kolom_excel => kriteria_id
        $kolom_kriteria_label = []; // index_kolom_excel => teks header asli (untuk pesan error)

        foreach ($header as $idx => $val) {
            if ($idx === $col_kode || $idx === $col_nama || $idx === $col_jabatan) continue;

            $val = trim((string)$val);
            if ($val === '') continue;

            $kode_candidate = null;
            if (preg_match('/^([A-Za-z0-9]+)/', $val, $m)) {
                $kode_candidate = strtoupper($m[1]);
            }

            if ($kode_candidate !== null && isset($kriteria_by_kode[$kode_candidate])) {
                $kolom_kriteria[$idx]       = $kriteria_by_kode[$kode_candidate];
                $kolom_kriteria_label[$idx] = $val;
                continue;
            }

            $normal = $this->_normalisasi_teks($val);
            $match  = false;
            foreach ($kriteria_by_nama as $nama_norm => $kid) {
                if ($normal === $nama_norm || strpos($normal, $nama_norm) !== false) {
                    $kolom_kriteria[$idx]       = $kid;
                    $kolom_kriteria_label[$idx] = $val;
                    $match = true;
                    break;
                }
            }

            // Header ada isinya tapi tidak cocok kriteria manapun -> catat sebagai kolom tak dikenal
            if (!$match) {
                $kolom_tidak_dikenal[] = $val;
            }
        }

        if (empty($kolom_kriteria)) {
            $this->session->set_flashdata('error', 'Tidak ada kolom kriteria yang cocok terdeteksi pada file. Pastikan header kolom kriteria mengandung kode (mis. C1) atau nama kriteria (mis. Produktivitas).');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        /* ---------- 3. Proses baris data ---------- */
        $baris_diproses   = 0;
        $nilai_diperbarui = 0;
        $skip_notfound    = 0; // kode/nama sama sekali tidak ditemukan
        $skip_ambigu      = 0; // nama ganda tanpa kode/jabatan pembeda
        $errors           = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // header

            $kode_alt    = ($col_kode !== null && isset($row[$col_kode])) ? trim((string)$row[$col_kode]) : '';
            $nama_alt    = ($col_nama !== null && isset($row[$col_nama])) ? trim((string)$row[$col_nama]) : '';
            $jabatan_alt = ($col_jabatan !== null && isset($row[$col_jabatan])) ? trim((string)$row[$col_jabatan]) : '';

            if ($kode_alt === '' && $nama_alt === '') continue; // baris kosong, lewati diam-diam

            $baris_no = $i + 1;
            $alt      = null;

            /* ===== ATURAN 1: Ada kode -> match langsung, paling aman ===== */
            if ($kode_alt !== '') {
                $alt = $this->Alternatif_model->get_by_kode_and_periode($kode_alt, $periode_id);

                if (!$alt) {
                    $skip_notfound++;
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'tidak_ditemukan',
                        'pesan'  => "kode \"{$kode_alt}\" tidak ditemukan pada periode ini",
                    ];
                    continue;
                }
            }
            /* ===== ATURAN 2: Tidak ada kode, tapi ada nama + jabatan -> match spesifik ===== */
            elseif ($nama_alt !== '' && $jabatan_alt !== '') {
                $alt = $this->Alternatif_model->get_by_nama_jabatan_and_periode($nama_alt, $jabatan_alt, $periode_id);

                if (!$alt) {
                    $skip_notfound++;
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'tidak_ditemukan',
                        'pesan'  => "nama \"{$nama_alt}\" dengan jabatan \"{$jabatan_alt}\" tidak ditemukan pada periode ini",
                    ];
                    continue;
                }
            }
            /* ===== ATURAN 3 & 4: Tidak ada kode & jabatan -> cek keunikan nama ===== */
            elseif ($nama_alt !== '') {
                $kandidat        = $this->Alternatif_model->get_all_by_nama_and_periode($nama_alt, $periode_id);
                $jumlah_kandidat = count($kandidat);

                if ($jumlah_kandidat === 0) {
                    $skip_notfound++;
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'tidak_ditemukan',
                        'pesan'  => "nama \"{$nama_alt}\" tidak ditemukan pada periode ini",
                    ];
                    continue;
                }

                if ($jumlah_kandidat > 1) {
                    $skip_ambigu++;
                    $daftar_jabatan = implode(', ', array_map(
                        fn($k) => !empty($k->jabatan) ? $k->jabatan : '(tanpa jabatan)',
                        $kandidat
                    ));
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'ambigu',
                        'pesan'  => "nama \"{$nama_alt}\" ditemukan {$jumlah_kandidat} kandidat ({$daftar_jabatan}) tanpa kode/jabatan pembeda — tambahkan kolom Kode atau Jabatan di file untuk memastikan baris ini tertaut ke orang yang benar",
                    ];
                    continue;
                }

                $alt = $kandidat[0];
            } else {
                continue;
            }

            $baris_diproses++;
            $alt_id = $alt->id;

            foreach ($kolom_kriteria as $col_idx => $kriteria_id) {
                if (!isset($row[$col_idx])) continue;

                $nilai_raw = trim(str_replace(',', '.', (string)$row[$col_idx]));

                if ($nilai_raw === '') continue; // sel kosong, lewati diam-diam

                if (!is_numeric($nilai_raw)) {
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'nilai_invalid',
                        'pesan'  => "kolom \"{$kolom_kriteria_label[$col_idx]}\": nilai \"{$nilai_raw}\" bukan angka",
                    ];
                    continue;
                }

                $nilai = (float)$nilai_raw;
                if ($nilai < 0.1 || $nilai > 100) {
                    $errors[] = [
                        'baris'  => $baris_no,
                        'alasan' => 'nilai_invalid',
                        'pesan'  => "kolom \"{$kolom_kriteria_label[$col_idx]}\": nilai {$nilai} di luar rentang 0.1–100",
                    ];
                    continue;
                }

                $this->Nilai_model->delete_nilai($user_id, $alt_id, $kriteria_id, $periode_id);
                $this->Nilai_model->insert_nilai([
                    'user_id'       => $user_id,
                    'periode_id'    => $periode_id,
                    'alternatif_id' => $alt_id,
                    'kriteria_id'   => $kriteria_id,
                    'nilai'         => $nilai,
                ]);
                $nilai_diperbarui++;
            }
        }

        // Ringkasan: sukses (hijau) jika semua berhasil, error (merah) jika ada yang dilewati
        $jumlah_nilai_invalid = count(array_filter($errors, fn($e) => $e['alasan'] === 'nilai_invalid'));
        $total_dilewati = $skip_notfound + $skip_ambigu + $jumlah_nilai_invalid;

        if (empty($errors)) {
            $summary = "{$baris_diproses} data nilai pada masing-masing karyawan berhasil diupload.";
            $this->session->set_flashdata('success', $summary);
        } else {
            if ($baris_diproses === 0) {
                $summary = 'Data nilai tidak dapat diproses.';
            } else {
                $summary = "{$baris_diproses} data berhasil diupload, {$total_dilewati} dilewati.";
            }
            if (!empty($kolom_tidak_dikenal ?? [])) {
                $summary .= ' Kolom tidak dikenali: ' . implode(', ', $kolom_tidak_dikenal) . '.';
            }
            $this->session->set_flashdata('upload_errors', $errors);
            $this->session->set_flashdata('error', $summary);
        }

        redirect('saw/penilaian?periode_id=' . $periode_id . '&force_edit=1');
    }

    /*Normalisasi teks untuk pencocokan longgar (case-insensitive, spasi rapi)*/
    private function _normalisasi_teks(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
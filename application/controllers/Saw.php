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
            'Nilai_model'
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
        $year  = date('Y');
        $month = date('m');

        // Cek periode bulan ini
        $this->db->where('YEAR(tanggal_mulai)', $year);
        $this->db->where('MONTH(tanggal_mulai)', $month);
        $periode = $this->db->get('periode')->row();
        if ($periode) {
            return (int)$periode->id;
        }

        // Jika tidak ada, ambil periode pertama di tahun ini
        $this->db->where('YEAR(tanggal_mulai)', $year);
        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode = $this->db->get('periode')->row();
        return $periode ? (int)$periode->id : 1;
    }

    /* ====================================================
     * PROSES PERHITUNGAN METODE SAW
     * ==================================================== */
    private function _calculate_saw(int $user_id, int $periode_id): array
    {
        // 1. Ambil data master dan nilai
        $kriteria   = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $penilaian  = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        // 2. Bentuk Matriks Keputusan (X)
        $matrix = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
            }
        }

        foreach ($penilaian as $p) {
            $matrix[$p['alternatif_id']][$p['kriteria_id']] = (float)$p['nilai'];
        }

        // 3. Proses Normalisasi (R) dan Pembobotan (V)
        $normalized = [];
        $weighted   = [];
        $final      = [];

        foreach ($kriteria as $krit) {
            $kid   = (int)$krit['id'];
            $bobot = (float)$krit['bobot'];
            $tipe  = $krit['tipe'];
            $kolom = array_column($matrix, $kid);

            if ($tipe === 'benefit') {
                // Kriteria Benefit (Maksimum)
                $max = !empty($kolom) ? max($kolom) : 1;
                if ($max <= 0) $max = 1;

                foreach ($alternatif as $alt) {
                    $aid  = (int)$alt['id'];
                    $norm = (float)$matrix[$aid][$kid] / $max;

                    $normalized[$aid][$kid] = $norm;
                    $weighted[$aid][$kid]   = $norm * $bobot;
                    $final[$aid]            = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
                }
            } else {
                // Kriteria Cost (Minimum)
                $valid = array_filter($kolom, fn($v) => $v > 0);
                $min   = !empty($valid) ? min($valid) : 1;

                foreach ($alternatif as $alt) {
                    $aid   = (int)$alt['id'];
                    $nilai = (float)$matrix[$aid][$kid];
                    $norm  = $nilai > 0 ? $min / $nilai : 0;

                    $normalized[$aid][$kid] = $norm;
                    $weighted[$aid][$kid]   = $norm * $bobot;
                    $final[$aid]            = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
                }
            }
        }

        return [
            'matrix'     => $matrix,
            'normalized' => $normalized,
            'weighted'   => $weighted,
            'final'      => $final,
            'kriteria'   => $kriteria,
            'alternatif' => $alternatif,
        ];
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
        $periode_list   = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();

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
        $cek_hasil = $this->db
            ->where('user_id', $user_id)
            ->where('periode_id', $periode_id)
            ->get('saw_hasil')
            ->num_rows();

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
        if ($cek_hasil > 0 && $penilaian_lengkap && !$force_edit && !$tombol_proses) {
            $saw        = $this->_calculate_saw($user_id, $periode_id);
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
            $saw        = $this->_calculate_saw($user_id, $periode_id);
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

        // Refresh tabel hasil untuk periode yang dipilih
        $this->db->delete('saw_hasil', [
            'user_id'    => $user_id,
            'periode_id' => $periode_id
        ]);

        if (!empty($data_hasil)) {
            $this->db->insert_batch('saw_hasil', $data_hasil);
            $this->session->set_flashdata('success', 'Hasil SAW berhasil disimpan.');
        }

        redirect('saw/hasil?periode_id=' . $periode_id);
    }

    /* ====================================================
     * HALAMAN HASIL / RANKING
     * ==================================================== */
    public function hasil(): void
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());
        $periode_list = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();

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
}
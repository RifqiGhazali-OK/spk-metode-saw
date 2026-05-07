<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Saw extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Alternatif_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Hasil_model');
        $this->load->model('User_model');
        $this->load->model('Periode_model');
        $this->load->model('Nilai_model');

        // Hanya admin yang boleh mengakses
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') != 'admin') {
            redirect('auth');
        }

        // Set session nama jika kosong
        if (empty($this->session->userdata('nama'))) {
            $user = $this->User_model->get_by_id($this->session->userdata('id'));
            if ($user) {
                $nama = !empty($user->username) ? $user->username : $user->email;
                $this->session->set_userdata('nama', $nama);
            }
        }
    }

    // ------------------------------------------------------------------------
    // PRIVATE METHODS (Helpers)
    // ------------------------------------------------------------------------

    /**
     * Mendapatkan nama user dari session.
     */
    private function _get_nama_user(): string
    {
        $nama = $this->session->userdata('nama');
        if (empty($nama)) {
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'Administrator';
        }
        return $nama;
    }

    /**
     * Mendapatkan ID periode aktif berdasarkan tahun dan bulan berjalan.
     */
    private function _get_active_periode(): int
    {
        $current_year  = date('Y');
        $current_month = date('m');
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->where('MONTH(tanggal_mulai)', $current_month);
        $periode = $this->db->get('periode')->row();
        if ($periode) return (int)$periode->id;

        // Fallback: ambil periode pertama di tahun ini
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode = $this->db->get('periode')->row();
        return $periode ? (int)$periode->id : 1;
    }

    /**
     * Melakukan perhitungan SAW (normalisasi + weighted sum).
     * Mengembalikan array berisi matrix, normalized, weighted, final.
     */
    private function _calculate_saw(int $user_id, int $periode_id): array
    {
        // Ambil data
        $kriteria   = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $penilaian  = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        // Inisialisasi matriks nilai mentah (default 0)
        $matrix = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
            }
        }
        // Isi nilai yang ada
        foreach ($penilaian as $p) {
            $matrix[$p['alternatif_id']][$p['kriteria_id']] = $p['nilai'];
        }

        $normalized = [];
        $weighted   = [];
        $final      = [];

        foreach ($kriteria as $krit) {
            $krit_id = $krit['id'];
            $tipe    = $krit['tipe'];
            $bobot   = (float)$krit['bobot'];

            // Kumpulkan nilai semua alternatif untuk kriteria ini
            $nilai_kolom = array_column($matrix, $krit_id);

            if ($tipe === 'benefit') {
                $max = max($nilai_kolom);
                if ($max == 0) $max = 1; // hindari pembagian nol

                foreach ($alternatif as $alt) {
                    $alt_id = $alt['id'];
                    $val    = $matrix[$alt_id][$krit_id];
                    $norm   = $val / $max;

                    $normalized[$alt_id][$krit_id] = $norm;
                    $weighted[$alt_id][$krit_id]   = $norm * $bobot;
                    $final[$alt_id] = ($final[$alt_id] ?? 0) + $weighted[$alt_id][$krit_id];
                }
            } else { // cost
                // Filter nilai > 0 untuk mencari minimum
                $nilai_valid = array_filter($nilai_kolom, fn($v) => $v > 0);
                $min = !empty($nilai_valid) ? min($nilai_valid) : 1;

                foreach ($alternatif as $alt) {
                    $alt_id = $alt['id'];
                    $val    = $matrix[$alt_id][$krit_id];
                    $norm   = ($val > 0) ? ($min / $val) : 0;

                    $normalized[$alt_id][$krit_id] = $norm;
                    $weighted[$alt_id][$krit_id]   = $norm * $bobot;
                    $final[$alt_id] = ($final[$alt_id] ?? 0) + $weighted[$alt_id][$krit_id];
                }
            }
        }

        return [
            'matrix'      => $matrix,
            'normalized'  => $normalized,
            'weighted'    => $weighted,
            'final'       => $final,
            'kriteria'    => $kriteria,
            'alternatif'  => $alternatif,
        ];
    }

    // ------------------------------------------------------------------------
    // PUBLIC METHODS (Actions)
    // ------------------------------------------------------------------------

    /**
     * Halaman input penilaian (form nilai alternatif per kriteria).
     */
    public function penilaian()
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());

        $alternatif   = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria     = $this->Kriteria_model->get_all();
        $nilai_existing = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        // Map nilai existing untuk memudahkan view
        $nilai_map = [];
        foreach ($nilai_existing as $n) {
            $nilai_map[$n['alternatif_id']][$n['kriteria_id']] = $n['nilai'];
        }

        $periode_list = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();

        $data = [
            'title'          => 'Input Penilaian',
            'active_menu'    => 'penilaian',
            'alternatif'     => $alternatif,
            'kriteria'       => $kriteria,
            'nilai_map'      => $nilai_map,
            'periode_list'   => $periode_list,
            'periode_id_selected' => $periode_id,
            'role'           => $this->session->userdata('role'),
            'nama_user'      => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('saw/penilaian', $data, true);
        $this->load->view('layout/template', $data);
    }

    /**
     * Ajax: menyimpan satu nilai penilaian (update atau insert).
     */
    public function penilaian_save()
    {
        $user_id       = (int)$this->session->userdata('id');
        $alternatif_id = (int)$this->input->post('alternatif_id');
        $kriteria_id   = (int)$this->input->post('kriteria_id');
        $nilai         = (float)$this->input->post('nilai');
        $periode_id    = (int)($this->input->post('periode_id') ?: $this->_get_active_periode());

        $exists = $this->Nilai_model->exists($user_id, $alternatif_id, $kriteria_id, $periode_id);
        if ($exists) {
            $this->Nilai_model->update_nilai($exists->id, $nilai);
        } else {
            $this->Nilai_model->insert_nilai([
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alternatif_id,
                'kriteria_id'   => $kriteria_id,
                'nilai'         => $nilai
            ]);
        }

        echo json_encode(['status' => 'success']);
    }

    /**
     * Menampilkan proses perhitungan SAW (matriks, normalisasi, terbobot, final).
     */
    public function proses_saw()
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());

        // Cek kelengkapan data
        $kriteria   = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        if (empty($kriteria)) {
            $this->session->set_flashdata('error', 'Belum ada data kriteria.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }
        if (empty($alternatif)) {
            $this->session->set_flashdata('error', 'Belum ada data alternatif untuk periode ini.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        $total_required = count($alternatif) * count($kriteria);
        $jumlah_penilaian = $this->Nilai_model->count_by_user_and_periode($user_id, $periode_id);
        if ($jumlah_penilaian < $total_required) {
            $this->session->set_flashdata('error', 'Penilaian belum lengkap!');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        // Hitung SAW
        $saw = $this->_calculate_saw($user_id, $periode_id);

        $data = [
            'title'       => 'Proses Perhitungan SAW',
            'active_menu' => 'hitung',
            'periode_id'  => $periode_id,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user(),
            // data SAW
            'alternatif'  => $saw['alternatif'],
            'kriteria'    => $saw['kriteria'],
            'matrix'      => $saw['matrix'],
            'normalized'  => $saw['normalized'],
            'weighted'    => $saw['weighted'],
            'final'       => $saw['final'],
        ];

        $data['content'] = $this->load->view('saw/proses_saw', $data, true);
        $this->load->view('layout/template', $data);
    }

    /**
     * Menyimpan hasil akhir SAW ke database (tabel saw_hasil).
     */
    public function simpan_hasil()
    {
        $user_id     = (int)$this->session->userdata('id');
        $final_json  = $this->input->post('final');
        $periode_id  = (int)$this->input->post('periode_id');

        if (!$final_json || !$periode_id) {
            $this->session->set_flashdata('error', 'Data tidak lengkap.');
            redirect('saw/penilaian');
        }

        $final = json_decode($final_json, true);
        if (!is_array($final)) {
            $this->session->set_flashdata('error', 'Format data final tidak valid.');
            redirect('saw/penilaian');
        }

        arsort($final); // urutkan descending

        $ranking = 1;
        $data_hasil = [];
        foreach ($final as $alt_id => $nilai_akhir) {
            $status = ($nilai_akhir >= 0.70) ? 'Layak' : 'Pertimbangkan';
            $data_hasil[] = [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alt_id,
                'nilai_akhir'   => $nilai_akhir,
                'ranking'       => $ranking++,
                'status'        => $status
            ];
        }

        // Hapus data lama, insert baru
        $this->db->delete('saw_hasil', ['user_id' => $user_id, 'periode_id' => $periode_id]);
        if (!empty($data_hasil)) {
            $this->db->insert_batch('saw_hasil', $data_hasil);
            $this->session->set_flashdata('success', 'Perhitungan SAW berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang diproses.');
        }

        redirect('saw/hasil?periode_id=' . $periode_id);
    }

    /**
     * Menampilkan hasil akhir (ranking) dari database.
     */
    public function hasil()
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());

        $periode_list = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();
        $hasil        = $this->Hasil_model->get_ranking(100, $user_id, $periode_id);

        $data = [
            'title'       => 'Hasil SAW',
            'active_menu' => 'hasil',
            'periode_list' => $periode_list,
            'periode_id_selected' => $periode_id,
            'hasil'       => $hasil,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('saw/hasil', $data, true);
        $this->load->view('layout/template', $data);
    }
}
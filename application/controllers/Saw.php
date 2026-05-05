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

        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') != 'admin') {
            redirect('auth');
        }

        if (empty($this->session->userdata('nama'))) {
            $user = $this->User_model->get_by_id($this->session->userdata('id'));
            if ($user) {
                $nama = !empty($user->username) ? $user->username : $user->email;
                $this->session->set_userdata('nama', $nama);
            }
        }
    }

    private function _get_nama_user()
    {
        $nama = $this->session->userdata('nama');
        if (empty($nama)) {
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'Administrator';
        }
        return $nama;
    }

    private function _get_active_periode()
    {
        $current_year = date('Y');
        $current_month = date('m');
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->where('MONTH(tanggal_mulai)', $current_month);
        $periode = $this->db->get('periode')->row();
        if ($periode) return $periode->id;
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode = $this->db->get('periode')->row();
        return $periode ? $periode->id : 1;
    }

    // ============================================
    // INPUT PENILAIAN
    // ============================================
    public function penilaian()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria   = $this->Kriteria_model->get_all();

        // Gunakan Nilai_model
        $nilai_existing = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

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

        $data['content'] = $this->load->view('saw/penilaian', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function penilaian_save()
    {
        $user_id = $this->session->userdata('id');
        $alternatif_id = $this->input->post('alternatif_id');
        $kriteria_id   = $this->input->post('kriteria_id');
        $nilai         = $this->input->post('nilai');
        $periode_id    = $this->input->post('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

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

    // ============================================
    // PROSES HITUNG SAW
    // ============================================
    public function proses_saw()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $kriteria = $this->Kriteria_model->get_all();
        if (empty($kriteria)) {
            $this->session->set_flashdata('error', 'Belum ada data kriteria.');
            redirect('saw/penilaian?periode_id=' . $periode_id);
        }

        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
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

        $penilaian = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        // Matriks nilai mentah
        $matrix = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
                foreach ($penilaian as $p) {
                    if ($p['alternatif_id'] == $alt['id'] && $p['kriteria_id'] == $krit['id']) {
                        $matrix[$alt['id']][$krit['id']] = $p['nilai'];
                        break;
                    }
                }
            }
        }

        // Normalisasi dan nilai terbobot
        $normalized = [];
        $weighted = [];
        $final = [];

        foreach ($kriteria as $krit) {
            $krit_id = $krit['id'];
            $tipe = $krit['tipe'];
            $nilai_kolom = array_column($matrix, $krit_id);
            if ($tipe == 'benefit') {
                $max = max($nilai_kolom);
                if ($max == 0) $max = 1;
                foreach ($alternatif as $alt) {
                    $norm = $matrix[$alt['id']][$krit_id] / $max;
                    $normalized[$alt['id']][$krit_id] = $norm;
                    $weighted[$alt['id']][$krit_id] = $norm * $krit['bobot'];
                }
            } else {
                $min = min($nilai_kolom);
                if ($min == 0) $min = 1;
                foreach ($alternatif as $alt) {
                    $val = $matrix[$alt['id']][$krit_id];
                    if ($val == 0) $val = 1;
                    $norm = $min / $val;
                    $normalized[$alt['id']][$krit_id] = $norm;
                    $weighted[$alt['id']][$krit_id] = $norm * $krit['bobot'];
                }
            }
        }

        foreach ($alternatif as $alt) {
            $total = 0;
            foreach ($kriteria as $krit) {
                $total += $weighted[$alt['id']][$krit['id']];
            }
            $final[$alt['id']] = $total;
        }

        $data = [
            'title'       => 'Proses Perhitungan SAW',
            'active_menu' => 'hitung',
            'alternatif'  => $alternatif,
            'kriteria'    => $kriteria,
            'matrix'      => $matrix,
            'normalized'  => $normalized,
            'weighted'    => $weighted,
            'final'       => $final,
            'periode_id'  => $periode_id,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('saw/proses_saw', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function simpan_hasil()
    {
        $user_id = $this->session->userdata('id');
        $final_json = $this->input->post('final');
        $periode_id = $this->input->post('periode_id');
        if (!$final_json || !$periode_id) {
            $this->session->set_flashdata('error', 'Data tidak lengkap.');
            redirect('saw/penilaian');
        }
        $final = json_decode($final_json, true);

        arsort($final);
        $ranking = 1;
        $data_hasil = [];
        foreach ($final as $alt_id => $nilai_akhir) {
            $status = ($nilai_akhir >= 0.75) ? 'Layak' : 'Pertimbangkan';
            $data_hasil[] = [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alt_id,
                'nilai_akhir'   => $nilai_akhir,
                'ranking'       => $ranking++,
                'status'        => $status
            ];
        }

        $this->db->delete('saw_hasil', ['user_id' => $user_id, 'periode_id' => $periode_id]);
        if (!empty($data_hasil)) {
            $this->db->insert_batch('saw_hasil', $data_hasil);
            $this->session->set_flashdata('success', 'Perhitungan SAW berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang diproses.');
        }
        redirect('saw/hasil?periode_id=' . $periode_id);
    }

    // ============================================
    // HASIL SAW
    // ============================================
    public function hasil()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $periode_list = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();

        $data = [
            'title'       => 'Hasil SAW',
            'active_menu' => 'hasil',
            'hasil'       => $this->Hasil_model->get_ranking(100, $user_id, $periode_id),
            'periode_list' => $periode_list,
            'periode_id_selected' => $periode_id,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('saw/hasil', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ===================================
    // EXPORT EXCEL PERHITUNGAN DETAIL 
    // ===================================
    public function export_excel()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id') ?: $this->_get_active_periode();

        // 1. Ambil Data Dasar
        $periode = $this->db->get_where('periode', ['id' => $periode_id])->row();
        $kriteria = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $penilaian = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        if (empty($kriteria) || empty($alternatif)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap untuk diexport.');
            redirect('saw/hasil?periode_id=' . $periode_id);
        }

        // Inisialisasi Array
        $matrix = [];
        $normalized = [];
        $weighted = [];
        $final = [];

        // 2. Mapping Nilai Mentah ke Matriks 
        foreach ($penilaian as $p) {
            $matrix[$p['alternatif_id']][$p['kriteria_id']] = $p['nilai'];
        }

        // 3. Proses Normalisasi (R), Terbobot (V), dan Nilai Akhir
        foreach ($kriteria as $krit) {
            $k_id = $krit['id'];
            $tipe = $krit['tipe'];
            $nilai_kolom = array_column($matrix, $k_id);

            // Cari nilai Max / Min untuk kriteria ini
            $max = !empty($nilai_kolom) ? max($nilai_kolom) : 1;
            $min = !empty($nilai_kolom) ? min($nilai_kolom) : 1;

            foreach ($alternatif as $alt) {
                $a_id = $alt['id'];
                $val = $matrix[$a_id][$k_id] ?? 0; // Jika belum dinilai, set 0

                // Hitung Normalisasi
                $norm = ($tipe == 'benefit') ? ($val / ($max ?: 1)) : (($min ?: 1) / ($val ?: 1));
                $normalized[$a_id][$k_id] = $norm;

                // Hitung Terbobot
                $bobot_nilai = $norm * $krit['bobot'];
                $weighted[$a_id][$k_id] = $bobot_nilai;

                // Akumulasi langsung ke Nilai Akhir
                if (!isset($final[$a_id])) $final[$a_id] = 0;
                $final[$a_id] += $bobot_nilai;
            }
        }

        // Urutkan nilai akhir dari terbesar ke terkecil (Ranking)
        arsort($final);

        // 4. Siapkan Data untuk View Excel
        $data = [
            'nama_periode' => $periode ? $periode->nama : 'Semua Periode',
            'kriteria'     => $kriteria,
            'alternatif'   => $alternatif,
            'matrix'       => $matrix,
            'normalized'   => $normalized,
            'weighted'     => $weighted,
            'final'        => $final
        ];

        // 5. Eksekusi Render ke Excel
        $filename = "Laporan SAW " . $data['nama_periode'] . ".xls";
        $html = $this->load->view('saw/export_excel', $data, TRUE);
        
        // Bersihkan output buffer jika ada spasi yang bocor
        if (ob_get_length()) ob_end_clean();

        //browser mendownload sebagai file Excel
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");

        echo $html;
        exit();
    }
}
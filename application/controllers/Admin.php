<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->set_message(
            'greater_than',
            'Kolom {field} harus berisi angka lebih besar dari {param}.'
        );
        $this->load->model('Alternatif_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Hasil_model');
        $this->load->model('User_model');
        $this->load->model('Periode_model');

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

    public function profil()
    {
        $user_id = $this->session->userdata('id');
        $user = $this->User_model->get_by_id($user_id);

        $data = [
            'title'       => 'Profil Saya',
            'user'        => $user,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('profil', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function profil_update()
    {
        $user_id = $this->session->userdata('id');
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm = $this->input->post('confirm_password');

        if ($new_password !== $confirm) {
            $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
            redirect('admin/profil');
        }

        if (strlen($new_password) < 4) {
            $this->session->set_flashdata('error', 'Password baru minimal 4 karakter.');
            redirect('admin/profil');
        }

        $user = $this->User_model->get_by_id($user_id);
        if (md5($old_password) !== $user->password) {
            $this->session->set_flashdata('error', 'Password lama salah.');
            redirect('admin/profil');
        }

        $this->User_model->update($user_id, ['password' => md5($new_password)]);
        $this->session->set_flashdata('success', 'Password berhasil diubah.');
        redirect('admin/profil');
    }

    public function dashboard()
    {
        $semua_hasil = $this->Hasil_model->get_ranking();
        $hasil_ranking = $this->Hasil_model->get_ranking(10);

        $bar_labels = [];
        $bar_values = [];
        $bar_dept   = [];
        $bar_status = [];

        if (!empty($hasil_ranking)) {
            foreach ($hasil_ranking as $row) {
                $bar_labels[] = $row['nama_alternatif'] ?? $row['nama'] ?? '-';
                $nilai        = (float)($row['nilai_akhir'] ?? 0);
                $bar_values[] = $nilai;
                $bar_dept[]   = $row['jabatan'] ?? '-';
                $bar_status[] = ($nilai >= 0.70) ? 'Layak' : 'pertimbangkan';
            }
        }

        $dept_stats = [];
        $total_layak_all        = 0;
        $total_tidak_layak_all  = 0;

        if (!empty($semua_hasil)) {
            foreach ($semua_hasil as $row) {
                $jabatan  = !empty($row['jabatan']) ? trim($row['jabatan']) : 'Lainnya';
                $is_layak = ((float)$row['nilai_akhir'] >= 0.70);

                if (!isset($dept_stats[$jabatan])) {
                    $dept_stats[$jabatan] = ['total' => 0, 'layak' => 0, 'pertimbangkan' => 0];
                }

                $dept_stats[$jabatan]['total']++;
                if ($is_layak) {
                    $dept_stats[$jabatan]['layak']++;
                    $total_layak_all++;
                } else {
                    $dept_stats[$jabatan]['pertimbangkan']++;
                    $total_tidak_layak_all++;
                }
            }
        }

        $chart_labels         = [];
        $chart_data           = [];
        $chart_layak          = [];
        $chart_pertimbangkan  = [];

        foreach ($dept_stats as $dept => $stats) {
            $chart_labels[]        = $dept;
            $chart_data[]          = $stats['total'];
            $chart_layak[]         = $stats['layak'];
            $chart_pertimbangkan[] = $stats['pertimbangkan'];
        }

        $total_dinilai = count($semua_hasil);

        $data = [
            'title'               => 'Dashboard Admin',
            'active_menu'         => 'dashboard',
            'total_kriteria'      => $this->Kriteria_model->count_all(),
            'total_alternatif'    => $this->Alternatif_model->count_all_admin(),
            'total_hasil'         => $this->Hasil_model->count_all(),
            'hasil_ranking'       => $hasil_ranking,
            'chart_labels'        => json_encode($chart_labels),
            'chart_data'          => json_encode($chart_data),
            'chart_layak'         => json_encode($chart_layak),
            'chart_pertimbangkan' => json_encode($chart_pertimbangkan),
            'total_dinilai'       => $total_dinilai,
            'bar_labels'          => json_encode($bar_labels),
            'bar_values'          => json_encode($bar_values),
            'bar_dept'            => json_encode($bar_dept),
            'bar_status'          => json_encode($bar_status),
            'role'                => $this->session->userdata('role'),
            'nama_user'           => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('admin/dashboard', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ============================================
    // KRITERIA
    // ============================================
    public function kriteria()
    {
        $data = [
            'title'       => 'Kriteria & Bobot',
            'active_menu' => 'kriteria',
            'list'        => $this->Kriteria_model->get_all(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];
        $data['content'] = $this->load->view('admin/kriteria', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function kriteria_store()
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Kriteria', 'required|trim');
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[benefit,cost]');
        $this->form_validation->set_rules('bobot', 'Bobot', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/kriteria');
        }

        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        if ($this->Kriteria_model->get_by_kode($kode)) {
            $this->session->set_flashdata('error', 'Kode kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        if ($this->Kriteria_model->get_by_nama($nama)) {
            $this->session->set_flashdata('error', 'Nama kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        $bobot_baru = $this->input->post('bobot');
        $total_existing = $this->Kriteria_model->sum_bobot() * 100;

        if ($total_existing + $bobot_baru > 100) {
            $sisa = 100 - $total_existing;
            $this->session->set_flashdata('error', "Total bobot tidak boleh melebihi 100%. Sisa bobot tersedia: {$sisa}%");
            redirect('admin/kriteria');
        }

        $bobot_desimal = $bobot_baru / 100;
        $this->Kriteria_model->insert([
            'user_id' => $this->session->userdata('id'),
            'kode'    => $kode,
            'nama'    => $nama,
            'tipe'    => $this->input->post('tipe'),
            'bobot'   => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil ditambahkan.');
        redirect('admin/kriteria');
    }

    public function kriteria_update(int $id)
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Kriteria', 'required|trim');
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[benefit,cost]');
        $this->form_validation->set_rules('bobot', 'Bobot', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/kriteria');
        }

        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        if ($this->Kriteria_model->get_by_kode_except_id($kode, $id)) {
            $this->session->set_flashdata('error', 'Kode kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        if ($this->Kriteria_model->get_by_nama_except_id($nama, $id)) {
            $this->session->set_flashdata('error', 'Nama kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        $bobot_baru = $this->input->post('bobot');
        $old = $this->Kriteria_model->get_by_id($id);
        if (!$old) {
            $this->session->set_flashdata('error', 'Data kriteria tidak ditemukan.');
            redirect('admin/kriteria');
        }

        $bobot_lama = $old->bobot * 100;
        $total_existing = ($this->Kriteria_model->sum_bobot() * 100) - $bobot_lama;

        if ($total_existing + $bobot_baru > 100) {
            $sisa = 100 - $total_existing;
            $this->session->set_flashdata('error', "Total bobot tidak boleh melebihi 100%. Sisa bobot tersedia: {$sisa}%");
            redirect('admin/kriteria');
        }

        $bobot_desimal = $bobot_baru / 100;
        $this->Kriteria_model->update($id, [
            'kode'  => $kode,
            'nama'  => $nama,
            'tipe'  => $this->input->post('tipe'),
            'bobot' => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil diperbarui.');
        redirect('admin/kriteria');
    }

    public function kriteria_delete(int $id)
    {
        $this->Kriteria_model->delete($id);
        $this->session->set_flashdata('success', 'Kriteria berhasil dihapus.');
        redirect('admin/kriteria');
    }

    // ============================================
    // ALTERNATIF
    // ============================================
    public function alternatif()
    {
        $periode_id = $this->input->get('periode_id');
        $data['periode_list'] = $this->Periode_model->get_all();
        $data['periode_selected'] = $periode_id;
        $data['list'] = $this->Alternatif_model->get_all_admin($periode_id);
        $data['title'] = 'Data Alternatif';
        $data['active_menu'] = 'alternatif';
        $data['role'] = $this->session->userdata('role');
        $data['nama_user'] = $this->_get_nama_user();

        $data['content'] = $this->load->view('admin/alternatif', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function alternatif_store()
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Alternatif', 'required|trim');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/alternatif');
        }

        $periode_id = $this->input->post('periode_id');
        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        $existing_kode = $this->Alternatif_model->get_by_kode_and_periode($kode, $periode_id);
        if ($existing_kode) {
            $this->session->set_flashdata('error', 'Kode alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $existing_nama = $this->Alternatif_model->get_by_nama_and_periode($nama, $periode_id);
        if ($existing_nama) {
            $this->session->set_flashdata('error', 'Nama alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->insert([
            'periode_id' => $periode_id,
            'user_id'    => 1,
            'kode'       => $kode,
            'nama'       => $nama,
            'jabatan'    => $this->input->post('jabatan')
        ]);

        $this->session->set_flashdata('success', 'Alternatif berhasil ditambahkan.');
        redirect('admin/alternatif');
    }

    public function alternatif_update(int $id)
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Alternatif', 'required|trim');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/alternatif');
        }

        $periode_id = $this->input->post('periode_id');
        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        $existing_kode = $this->Alternatif_model->get_by_kode_and_periode_except_id($kode, $periode_id, $id);
        if ($existing_kode) {
            $this->session->set_flashdata('error', 'Kode alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $existing_nama = $this->Alternatif_model->get_by_nama_and_periode_except_id($nama, $periode_id, $id);
        if ($existing_nama) {
            $this->session->set_flashdata('error', 'Nama alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->update($id, [
            'periode_id' => $periode_id,
            'kode'       => $kode,
            'nama'       => $nama,
            'jabatan'    => $this->input->post('jabatan')
        ]);

        $this->session->set_flashdata('success', 'Alternatif berhasil diperbarui.');
        redirect('admin/alternatif');
    }

    public function alternatif_delete(int $id)
    {
        $this->Alternatif_model->delete($id);
        $this->session->set_flashdata('success', 'Alternatif berhasil dihapus.');
        redirect('admin/alternatif');
    }

    public function alternatif_delete_massal()
    {
        $ids = $this->input->post('ids');
        if (!empty($ids)) {
            $this->db->where_in('id', $ids);
            $this->db->delete('alternatif');
            $this->session->set_flashdata('success', count($ids) . ' data alternatif berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang dipilih.');
        }
        redirect('admin/alternatif');
    }
}
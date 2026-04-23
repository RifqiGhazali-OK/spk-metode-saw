<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
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

    // ============================================
    // PROFIL ADMIN
    // ============================================
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

        // Validasi
        if ($new_password !== $confirm) {
            $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
            redirect('admin/profil');
        }

        if (strlen($new_password) < 4) {
            $this->session->set_flashdata('error', 'Password baru minimal 4 karakter.');
            redirect('admin/profil');
        }

        // Cek password lama
        $user = $this->User_model->get_by_id($user_id);
        if (md5($old_password) !== $user->password) {
            $this->session->set_flashdata('error', 'Password lama salah.');
            redirect('admin/profil');
        }

        // Update password
        $this->User_model->update($user_id, ['password' => md5($new_password)]);
        $this->session->set_flashdata('success', 'Password berhasil diubah.');
        redirect('admin/profil');
    }

    private function _get_nama_user()
    {
        $nama = $this->session->userdata('nama');
        if (empty($nama)) {
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'Administrator';
        }
        return $nama;
    }

    // ============================================
    // DASHBOARD
    // ============================================
    public function dashboard()
    {
        $data = [
            'title'            => 'Dashboard Admin',
            'active_menu'      => 'dashboard',
            'total_kriteria'   => $this->Kriteria_model->count_all(),
            'total_alternatif' => $this->Alternatif_model->count_all_admin(),
            'total_user'       => $this->User_model->count_all(),
            'total_hasil'      => $this->Hasil_model->count_all(),
            'top_nilai'        => $this->Hasil_model->get_top_nilai(),
            'hasil_ranking'    => $this->Hasil_model->get_ranking(10),
            'role'             => $this->session->userdata('role'),
            'nama_user'        => $this->_get_nama_user()
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
            'kode'    => $this->input->post('kode'),
            'nama'    => $this->input->post('nama'),
            'tipe'    => $this->input->post('tipe'),
            'bobot'   => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil ditambahkan.');
        redirect('admin/kriteria');
    }

    public function kriteria_update($id)
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Kriteria', 'required|trim');
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[benefit,cost]');
        $this->form_validation->set_rules('bobot', 'Bobot', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
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
            'kode'  => $this->input->post('kode'),
            'nama'  => $this->input->post('nama'),
            'tipe'  => $this->input->post('tipe'),
            'bobot' => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil diperbarui.');
        redirect('admin/kriteria');
    }

    public function kriteria_delete($id)
    {
        $this->Kriteria_model->delete($id);
        $this->session->set_flashdata('success', 'Kriteria berhasil dihapus.');
        redirect('admin/kriteria');
    }

    // ============================================
    // MASTER DATA: ALTERNATIF
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
        $existing = $this->Alternatif_model->get_by_kode_and_periode($kode, $periode_id);
        if ($existing) {
            $this->session->set_flashdata('error', 'Kode alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->insert([
            'periode_id' => $periode_id,
            'user_id'    => 1,
            'kode'       => $kode,
            'nama'       => $this->input->post('nama'),
            'jabatan'    => $this->input->post('jabatan')
        ]);
        $this->session->set_flashdata('success', 'Alternatif berhasil ditambahkan.');
        redirect('admin/alternatif');
    }

    public function alternatif_update($id)
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
        $existing = $this->Alternatif_model->get_by_kode_and_periode_except_id($kode, $periode_id, $id);
        if ($existing) {
            $this->session->set_flashdata('error', 'Kode alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->update($id, [
            'periode_id' => $periode_id,
            'kode'       => $kode,
            'nama'       => $this->input->post('nama'),
            'jabatan'    => $this->input->post('jabatan')
        ]);
        $this->session->set_flashdata('success', 'Alternatif berhasil diperbarui.');
        redirect('admin/alternatif');
    }

    public function alternatif_delete($id)
    {
        $this->Alternatif_model->delete($id);
        $this->session->set_flashdata('success', 'Alternatif berhasil dihapus.');
        redirect('admin/alternatif');
    }

    // Hapus banyak alternatif sekaligus
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

    // ============================================
    // USER
    // ============================================
    public function user()
    {
        $data = [
            'title'       => 'Kelola User',
            'active_menu' => 'user',
            'list'        => $this->User_model->get_all(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];
        $data['content'] = $this->load->view('admin/user', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function user_store()
    {
        // Ambil input dan trim
        $username = trim($this->input->post('username'));
        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');
        $role = $this->input->post('role');

        $this->form_validation->set_data([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role
        ]);

        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,user]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/user');
        }

        $this->User_model->insert([
            'username' => $username,
            'email'    => $email,
            'password' => md5($password),
            'role'     => $role,
        ]);
        $this->session->set_flashdata('success', 'User berhasil ditambahkan.');
        redirect('admin/user');
    }

    public function user_update($id)
    {
        // Ambil input dan trim
        $username = trim($this->input->post('username'));
        $email = trim($this->input->post('email'));
        $password = $this->input->post('password');
        $role = $this->input->post('role');

        $this->form_validation->set_data([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role
        ]);

        $this->form_validation->set_rules('username', 'Username', 'required|trim|callback_username_check[' . $id . ']');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_email_check[' . $id . ']');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,user]');
        // Password tidak wajib di update

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/user');
        }

        $update = [
            'username' => $username,
            'email'    => $email,
            'role'     => $role,
        ];
        if (!empty($password)) {
            $update['password'] = md5($password);
        }
        $this->User_model->update($id, $update);
        $this->session->set_flashdata('success', 'User berhasil diperbarui.');
        redirect('admin/user');
    }

    public function user_delete($id)
    {
        if ($id == $this->session->userdata('id')) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menghapus akun sendiri.');
            redirect('admin/user');
        }
        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'User berhasil dihapus.');
        redirect('admin/user');
    }

    // ============================================
    // LAPORAN
    // ============================================
    public function laporan()
    {
        $data = [
            'title'       => 'Laporan Hasil SAW',
            'active_menu' => 'laporan',
            'hasil'       => $this->Hasil_model->get_all_with_user(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];
        $data['content'] = $this->load->view('admin/laporan', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ============================================
    // CALLBACK VALIDASI
    // ============================================
    public function username_check($username, $id)
    {
        $this->db->where('username', trim($username));
        $this->db->where('id !=', $id);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('username_check', 'Username sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }

    public function email_check($email, $id)
    {
        $this->db->where('email', trim($email));
        $this->db->where('id !=', $id);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('email_check', 'Email sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }
}

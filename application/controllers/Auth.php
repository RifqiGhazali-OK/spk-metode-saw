<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('User_model');
    }

    public function index()
    {
        if ($this->session->userdata('logged_in')) {
            $this->redirect_by_role($this->session->userdata('role'));
        }
        $this->load->view('auth/login');
    }

    public function process()
    {
        $identity = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);

        $user = $this->User_model->login($identity, $password);

        if ($user) {
            $ses_data = array(
                'id'        => $user->id,
                'nama'      => $user->full_name ?? $user->username ?? $user->email,
                'username'  => $user->username,
                'email'     => $user->email,
                'role'      => $user->role,   // pakai role asli dari database, bukan dipaksa admin
                'logged_in' => TRUE
            );
            $this->session->set_userdata($ses_data);

            $this->redirect_by_role($user->role);
        } else {
            $this->session->set_flashdata('error', 'Email/Username atau password salah.');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }

     /* Arahkan user ke dashboard sesuai role.
     * admin  -> dashboard pengelolaan penuh (input data, kelola master, dsb)
     * user   -> dashboard read-only (Manajer/Direktur Operasional) + approval periode*/
    private function redirect_by_role($role)
    {
        if ($role === 'admin') {
            redirect('admin/dashboard');
        } else {
            redirect('user/dashboard');
        }
    }
}
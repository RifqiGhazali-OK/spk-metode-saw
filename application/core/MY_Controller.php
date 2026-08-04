<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Base controller umum: pastikan user sudah login.
 * Semua controller yang butuh login (admin & user) extend dari sini
 * lewat Admin_Controller atau User_Controller di bawah.
 */
class Auth_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }
}

/**
 * Extend controller ini untuk halaman yang HANYA boleh diakses admin/HR.
 * Contoh: class Kelola_kriteria extends Admin_Controller
 */
class Admin_Controller extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('role') !== 'admin') {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
    }
}

/**
 * Extend controller ini untuk halaman yang HANYA boleh diakses
 * Manajer/Direktur Operasional (role = 'user').
 * Contoh: class User extends User_Controller
 */
class User_Controller extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('role') !== 'user') {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
    }
}
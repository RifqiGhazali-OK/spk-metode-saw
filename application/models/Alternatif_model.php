<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alternatif_model extends CI_Model
{
    protected $table = 'alternatif';

    // Untuk user: ambil alternatif berdasarkan periode_id yang dipilih
    public function get_all_by_periode($periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->result_array();
    }

    public function count_all_by_periode($periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        return $this->db->count_all_results($this->table);
    }

    // Untuk admin: ambil semua alternatif (tanpa filter periode) atau filter periode jika diperlukan
    public function get_all_admin($periode_id = null)
    {
        if ($periode_id) $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->result_array();
    }

    public function count_all_admin($periode_id = null)
    {
        if ($periode_id) $this->db->where('periode_id', $periode_id);
        return $this->db->count_all_results($this->table);
    }

    // Method CRUD standar (tanpa periode, admin akan menentukan periode_id saat insert)
    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    // Cek apakah kode alternatif sudah ada pada periode tertentu (untuk insert)
    public function get_by_kode_and_periode($kode, $periode_id)
    {
        $this->db->where('kode', $kode);
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->row();
    }

    // Cek apakah kode alternatif sudah ada pada periode tertentu, kecuali id yang sedang diedit (untuk update)
    public function get_by_kode_and_periode_except_id($kode, $periode_id, $id)
    {
        $this->db->where('kode', $kode);
        $this->db->where('periode_id', $periode_id);
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }
}   
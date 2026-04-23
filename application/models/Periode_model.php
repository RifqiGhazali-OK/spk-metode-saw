<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Periode_model extends CI_Model
{
    protected $table = 'periode';

    public function get_all()
    {
        return $this->db->order_by('tanggal_mulai', 'ASC')->get($this->table)->result_array();
    }

    public function get_active()
    {
        return $this->db->where('is_active', 1)->get($this->table)->row();
    }

    public function set_active($id)
    {
        $this->db->update($this->table, ['is_active' => 0]); // nonaktifkan semua
        return $this->db->update($this->table, ['is_active' => 1], ['id' => $id]);
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
}
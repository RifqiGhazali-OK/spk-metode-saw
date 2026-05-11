<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kriteria_model extends CI_Model
{
    protected $table = 'kriteria';
    
    public function get_all()
    {
        $this->db->order_by('LENGTH(kode)', 'ASC');
        $this->db->order_by('kode', 'ASC');
        return $this->db->get($this->table)->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    public function sum_bobot()
    {
        $this->db->select_sum('bobot');
        $query = $this->db->get($this->table);
        $row = $query->row();
        return $row->bobot ?? 0;
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
}